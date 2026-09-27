<?php

namespace App\Http\Controllers;

use App\Services\DashboardSummary;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request, DashboardSummary $summary): JsonResponse
    {
        return response()->json($summary->forUser($request->user()));
    }
}
