<?php

namespace App\Policies;

use App\Models\DocumentRequest;
use App\Models\User;

class DocumentRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active && in_array($user->role, ['requestor', 'sta', 'admin'], true);
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function view(User $user, DocumentRequest $documentRequest): bool
    {
        return $this->viewAny($user) && ($user->isStaff() || $documentRequest->requestor_id === $user->id);
    }

    public function process(User $user, DocumentRequest $documentRequest): bool
    {
        return $user->is_active && $user->isStaff();
    }

    public function feedback(User $user, DocumentRequest $documentRequest): bool
    {
        return $this->view($user, $documentRequest) && $documentRequest->requestor_id === $user->id;
    }

    public function upload(User $user, DocumentRequest $documentRequest): bool
    {
        return $this->view($user, $documentRequest);
    }
}
