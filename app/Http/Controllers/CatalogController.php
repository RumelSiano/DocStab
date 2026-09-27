<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveCatalogRequest;
use App\Services\CatalogManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $catalogs = [];
        foreach (['faculty', 'document_types', 'drawers'] as $resource) {
            $class = CatalogManager::modelClass($resource);
            $catalogs[$resource] = $class::query()->when($request->user()->role === 'requestor', fn ($query) => $query->where('is_active', true))->orderBy($resource === 'drawers' ? 'number' : 'name')->orderBy('id')->get();
        }

        return response()->json($catalogs);
    }

    public function store(SaveCatalogRequest $request, string $resource, CatalogManager $catalogs): JsonResponse
    {
        return response()->json($catalogs->save($request->user(), $resource, null, $request->validated()), 201);
    }

    public function update(SaveCatalogRequest $request, string $resource, int $id, CatalogManager $catalogs): JsonResponse
    {
        return response()->json($catalogs->save($request->user(), $resource, $id, $request->validated()));
    }
}
