<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class LogController extends Controller
{
    /**
     * Display a listing of the activity logs.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        // Only admins can view logs
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $logs = ActivityLog::with('user')
            ->orderBy('logged_at', 'desc')
            ->paginate(20);

        return response()->json([
            'logs' => $logs,
        ]);
    }

    /**
     * Display the specified activity log.
     *
     * @param ActivityLog $log
     * @return JsonResponse
     */
    public function show(ActivityLog $log): JsonResponse
    {
        // Only admins can view logs
        if (!request()->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'log' => $log->load('user'),
        ]);
    }
}
