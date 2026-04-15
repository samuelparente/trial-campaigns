<?php

namespace App\Http\Middleware;

use App\Models\Campaign;
use Closure;
use Illuminate\Http\Request;

class EnsureCampaignIsDraft
{
    public function handle(Request $request, Closure $next)
    {
        // Resolve the campaign from the route.
        // This handles both raw IDs and Laravel's Route Model Binding.
        $routeParam = $request->route('campaign');
        $campaign = $routeParam instanceof Campaign 
            ? $routeParam 
            : Campaign::findOrFail($routeParam);

        // Abort if the campaign is NOT a draft.
        if ($campaign->status !== 'draft') {
            return response()->json([
                'error' => 'Action prohibited. Campaign must be in draft status.'
            ], 422); 
        }

        return $next($request);
    }
}