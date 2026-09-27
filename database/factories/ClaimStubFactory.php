<?php

namespace Database\Factories;

use App\Models\ClaimStub;
use App\Models\DocumentRequest;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<ClaimStub> */
class ClaimStubFactory extends Factory
{
    protected $model = ClaimStub::class;

    public function definition(): array
    {
        return ['request_id' => DocumentRequest::factory(), 'reference' => Str::uuid()->toString()];
    }
}
