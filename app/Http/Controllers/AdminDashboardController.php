<?php

namespace App\Http\Controllers;

use App\Models\Cow;
use App\Models\HealthRecord;
use App\Models\WeightLog;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'totalCows' => Cow::count(),
            'availableCows' => Cow::where('status', 'available')->count(),
            'soldCows' => Cow::where('status', 'sold')->count(),
            'weightEntries' => WeightLog::count(),
            'healthEntries' => HealthRecord::count(),
            'recentCows' => Cow::latest()->limit(6)->get(),
        ]);
    }
}
