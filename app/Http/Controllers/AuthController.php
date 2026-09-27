<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate(['name' => 'required|string|max:255', 'email' => 'required|email|max:255|unique:users', 'password' => ['required', 'confirmed', PasswordRule::min(12)]]);
        $user = User::create($data);
        Auth::login($user);
        $request->session()->regenerate();

        return response()->json((new UserResource($user->fresh()))->resolve($request), 201);
    }

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate(['email' => 'required|email', 'password' => 'required|string', 'remember' => 'sometimes|boolean']);
        if (! Auth::attempt(['email' => $data['email'], 'password' => $data['password'], 'is_active' => true], $request->boolean('remember'))) {
            throw ValidationException::withMessages(['email' => 'The credentials are incorrect or the account is disabled.']);
        }
        $request->session()->regenerate();

        return response()->json((new UserResource($request->user()))->resolve($request));
    }

    public function logout(Request $request): Response
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->noContent();
    }

    public function profile(Request $request): JsonResponse
    {
        $data = $request->validate(['name' => 'required|string|max:255', 'program' => 'nullable|string|max:255', 'contact_number' => 'nullable|string|max:40']);
        $request->user()->update($data);

        return response()->json((new UserResource($request->user()))->resolve($request));
    }

    public function forgot(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email']);
        Password::sendResetLink($request->only('email'));

        return response()->json(['message' => 'If the account exists, a password reset link will be sent.']);
    }

    public function reset(Request $request): JsonResponse
    {
        $data = $request->validate(['email' => 'required|email', 'token' => 'required|string', 'password' => ['required', 'confirmed', PasswordRule::min(12)]]);
        $status = Password::reset($data, function (User $user, string $password): void {
            $user->forceFill(['password' => $password, 'remember_token' => Str::random(60)])->save();
            DB::table('sessions')->where('user_id', $user->id)->delete();
        });
        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages(['email' => __($status)]);
        }

        return response()->json(['message' => 'Password reset.']);
    }
}
