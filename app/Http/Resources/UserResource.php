<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return $this->resource->only(['id', 'name', 'email', 'role', 'is_active', 'program', 'contact_number', 'email_verified_at', 'created_at', 'updated_at']);
    }
}
