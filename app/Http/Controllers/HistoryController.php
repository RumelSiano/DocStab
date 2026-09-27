<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class HistoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('view-history');
        $page = AuditLog::query()->when($request->user()->role === 'sta', fn ($query) => $query->whereNotNull('request_id'))->orderByDesc('id')->paginate(50);

        return response()->json($page->through(function (AuditLog $log): array {
            $data = $log->toArray();
            $data['details'] = $log->getRawOriginal('details');

            return $data;
        }));
    }
}
