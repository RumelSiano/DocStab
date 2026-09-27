<?php

namespace Database\Factories;

use App\Models\Attachment;
use App\Models\DocumentRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Attachment> */
class AttachmentFactory extends Factory
{
    protected $model = Attachment::class;

    public function definition(): array
    {
        return ['request_id' => DocumentRequest::factory(), 'uploaded_by' => User::factory(), 'original_name' => 'reference.pdf', 'path' => 'requests/'.Str::uuid().'.pdf', 'mime_type' => 'application/pdf', 'size' => 100];
    }
}
