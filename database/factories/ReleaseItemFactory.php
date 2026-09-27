<?php

namespace Database\Factories;

use App\Models\Drawer;
use App\Models\Release;
use App\Models\ReleaseItem;
use App\Models\RequestDocument;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ReleaseItem> */
class ReleaseItemFactory extends Factory
{
    protected $model = ReleaseItem::class;

    public function definition(): array
    {
        return ['release_id' => Release::factory(), 'request_document_id' => fn (array $attributes): int => RequestDocument::factory()->create(['request_id' => Release::findOrFail($attributes['release_id'])->request_id, 'status' => 'claimed'])->id, 'drawer_id' => Drawer::factory()];
    }
}
