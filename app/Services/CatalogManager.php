<?php

namespace App\Services;

use App\Models\DocumentType;
use App\Models\Drawer;
use App\Models\Faculty;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CatalogManager
{
    public function __construct(private AuditRecorder $audit) {}

    /** @return class-string<Model> */
    public static function modelClass(string $resource): string
    {
        return match ($resource) {
            'faculty' => Faculty::class,'document_types' => DocumentType::class,'drawers' => Drawer::class,default => abort(404)
        };
    }

    /** @param array<string, mixed> $data */
    public function save(User $actor, string $resource, ?int $id, array $data): Model
    {
        return DB::transaction(function () use ($actor, $resource, $id, $data): Model {
            $class = self::modelClass($resource);
            $record = $id ? $class::query()->findOrFail($id) : new $class;
            $record->fill($data)->save();
            $this->audit->record($actor, $resource.($id ? '_updated' : '_created'), null, ['id' => $record->id]);

            return $record->refresh();
        });
    }
}
