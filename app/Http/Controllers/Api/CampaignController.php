<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Http\Requests\StoreCampaignRequest;
use App\Services\CampaignService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CampaignController extends Controller
{
    // Retrieves a paginated list of campaigns along with their aggregated send statistics (pending, sent, failed).
    // The 'withSendStats' scope processes the count at the database level to prevent memory exhaustion.
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->query('per_page', 15);
        $campaigns = Campaign::withSendStats()->paginate($perPage);
        
        return response()->json($campaigns);
    }

    // Creates a new campaign using the validated data (subject, body, list ID, and schedule).
    public function store(StoreCampaignRequest $request): JsonResponse
    {
        $campaign = Campaign::create($request->validated());
        
        return response()->json($campaign, 201);
    }

    // Retrieves a single campaign by its ID, including its real-time aggregated send statistics.
    public function show(Campaign $campaign): JsonResponse
    {
        $campaignWithStats = Campaign::withSendStats()->findOrFail($campaign->id);
        
        return response()->json($campaignWithStats);
    }

    // Initiates the dispatch process for a draft campaign.
    // Relies on the injected CampaignService to handle chunking and queue dispatching.
    public function dispatch(Campaign $campaign, CampaignService $campaignService): JsonResponse
    {
        $campaignService->dispatch($campaign);

        return response()->json([
            'message'  => 'Campaign dispatch initiated successfully.',
            'campaign' => $campaign->fresh()
        ]);
    }
}