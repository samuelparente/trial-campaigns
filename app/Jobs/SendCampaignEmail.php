<?php

namespace App\Jobs;

use App\Models\CampaignSend;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendCampaignEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private readonly int $campaignSendId
    ) {}

    public function handle(): void
    {
        // Eager load relationships to prevent N+1 query problems when accessing contact and campaign data
        $send = CampaignSend::with(['contact', 'campaign'])->find($this->campaignSendId);

        if (!$send) {
            return;
        }

        // Strict idempotency check: never send the email if it was already sent
        if ($send->status === 'sent') {
            return;
        }

        try {
            $this->sendEmail($send->contact->email, $send->campaign->subject, $send->campaign->body);

            $send->update([
                'status' => 'sent',
                'error_message' => null
            ]);

        } catch (\Exception $e) {
            $send->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            Log::error('Campaign send failed', ['send_id' => $send->id, 'error' => $e->getMessage()]);

            // Rethrow the exception so Laravel's queue manager knows the job failed and can handle retries
            throw $e;
        }
    }

    private function sendEmail(string $to, string $subject, string $body): void
    {
        Log::info("Sending email to {$to}: {$subject}");
    }
}