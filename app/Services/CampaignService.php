<?php

namespace App\Services;

use App\Jobs\SendCampaignEmail;
use App\Models\Campaign;
use App\Models\CampaignSend;

class CampaignService
{
    /**
     * Dispatch a campaign to all active contacts in its list.
     */
    public function dispatch(Campaign $campaign): void
    {
        // Update status immediately to prevent overlapping dispatches
        $campaign->update(['status' => 'sending']);

        // Process in chunks of 1000 to prevent memory exhaustion on large lists
        $campaign->contactList->contacts()
            ->where('status', 'active')
            ->chunkById(1000, function ($contacts) use ($campaign) {
                
                foreach ($contacts as $contact) {
                    // Use firstOrCreate to prevent duplicate records if the dispatch process is interrupted and restarted
                    $send = CampaignSend::firstOrCreate(
                        ['campaign_id' => $campaign->id, 'contact_id' => $contact->id],
                        ['status' => 'pending']
                    );

                    // Only push to the queue if the email has not been successfully sent yet
                    if ($send->status !== 'sent') {
                        SendCampaignEmail::dispatch($send->id);
                    }
                }
                
            });
    }

    public function buildPayload(Campaign $campaign, array $extra = []): array
    {
        $base = [
            'subject' => $campaign->subject,
            'body'    => $campaign->body,
        ];

        return [...$base, ...$extra];
    }

    public function resolveReplyTo(Campaign $campaign)
    {
        if (empty($campaign->reply_to)) {
            return null;
        }

        return $campaign->reply_to;
    }
}