<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('view-users');
        $request->validate(['search' => 'nullable|string|max:100']);
        $query = User::query()->when($request->user()->role === 'sta', fn ($query) => $query->where('role', 'requestor')->where('is_active', true));
        if ($request->filled('search')) {
            $query->where(fn ($query) => $query->where('name', 'like', '%'.$request->input('search').'%')->orWhere('email', 'like', '%'.$request->input('search').'%'));
        }

        return response()->json($query->select('id', 'name', 'email', 'role', 'is_active', 'program')->orderBy('id')->paginate(20));
    }

    public function store(SaveUserRequest $request, UserManager $users): JsonResponse
    {
        return response()->json((new UserResource($users->save($request->user(), null, $request->validated())))->resolve($request), 201);
    }

    public function update(SaveUserRequest $request, User $user, UserManager $users): JsonResponse
    {
        return response()->json((new UserResource($users->save($request->user(), $user, $request->validated())))->resolve($request));
    }
}
