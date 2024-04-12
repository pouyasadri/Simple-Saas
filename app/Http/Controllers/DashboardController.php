<?php

namespace App\Http\Controllers;

use App\Http\Resources\UsedFeatureResource;
use App\Models\UsedFeature;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     *
     * @return Response
     */
    public function index(): Response
    {
        $usedFeatures = UsedFeature::query()
            ->with(['feature'])
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate();

        return inertia('Dashboard', [
            'usedFeatures' => UsedFeatureResource::collection($usedFeatures)
        ]);

    }
}
