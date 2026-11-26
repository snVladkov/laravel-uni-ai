<?php

namespace App\Http\Controllers;

use App\Services\DashboardSummary;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private readonly DashboardSummary $summary) {}

    /**
     * Display the role-aware dashboard.
     */
    public function index(Request $request): View
    {
        return view('dashboard', [
            'metrics' => $this->summary->for($request->user()),
        ]);
    }
}
