<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserManager
{
    public function __construct(private AuditRecorder $audit) {}

    /** @param array<string, mixed> $data */
    public function save(User $actor, ?User $user, array $data): User
    {
        return DB::transaction(function () use ($actor, $user, $data): User {
            $administrators = User::query()->where('role', 'admin')->orderBy('id')->lockForUpdate()->get();
            $creating = $user === null;
            $user = $user ? User::query()->lockForUpdate()->findOrFail($user->id) : new User;
            if ($user->id === $actor->id) {
                abort_if($data['role'] !== 'admin' || ! $data['is_active'], 422, 'You cannot disable or demote your own account.');
            }
            if ($user->role === 'admin' && $user->is_active && ($data['role'] !== 'admin' || ! $data['is_active'])) {
                abort_if($administrators->where('is_active', true)->count() <= 1, 422, 'Keep one active administrator.');
            }
            $user->fill(['name' => $data['name'], 'email' => $data['email']]);
            $user->role = $data['role'];
            $user->is_active = $data['is_active'];
            if (! empty($data['password'])) {
                $user->password = $data['password'];
            }
            $user->save();
            $this->audit->record($actor, $creating ? 'user_created' : 'user_updated', null, ['user_id' => $user->id]);

            return $user;
        });
    }
}
