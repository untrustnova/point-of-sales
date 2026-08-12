<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ActivityLogController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $storeId = $user->store_id ?? 1;

        $logs = ActivityLog::where('store_id', $storeId)
            ->with('user')
            ->latest()
            ->paginate(20);

        return Inertia::render('activity-logs/index', [
            'logs' => $logs,
        ]);
    }
}
