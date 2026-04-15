<?php

namespace Tests\Feature;

use App\Models\Campaign;
use App\Models\Contact;
use App\Models\ContactList;
use App\Models\CampaignSend;
use App\Jobs\SendCampaignEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class CampaignDispatchTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_dispatches_campaign_jobs_to_the_queue()
    {
        // Prevents jobs from actually executing during the test
        Queue::fake();

        // 1. Setup: Create the necessary records
        $contactList = ContactList::create(['name' => 'Test List']);
        
        $contact = Contact::create([
            'name' => 'Samuel Parente',
            'email' => 'samuel.parente@example.com',
            'status' => 'active'
        ]);
        
        // Associate the contact with the list
        $contactList->contacts()->attach($contact->id);

        $campaign = Campaign::create([
            'subject' => 'Test Subject',
            'body' => 'Hello {{name}}',
            'contact_list_id' => $contactList->id,
            'status' => 'draft'
        ]);

        // 2. Action: Execute the dispatch endpoint
        $response = $this->postJson("/api/campaigns/{$campaign->id}/dispatch");

        // 3. Assertions
        $response->assertStatus(200);
        
        // Verify the campaign status was updated in the database
        $this->assertDatabaseHas('campaigns', [
            'id' => $campaign->id,
            'status' => 'sending'
        ]);

        // Verify that a record was created in the campaign_sends table
        $this->assertDatabaseHas('campaign_sends', [
            'campaign_id' => $campaign->id,
            'contact_id' => $contact->id,
            'status' => 'pending'
        ]);

        // Behavioral Check: Ensure the job was pushed to the queue
        Queue::assertPushed(SendCampaignEmail::class);
    }

    #[Test]
    public function it_cannot_dispatch_a_campaign_that_is_already_sending()
    {
        // 1. Setup: Create a campaign that is already in 'sending' status
        $contactList = ContactList::create(['name' => 'Test List']);
        
        $campaign = Campaign::create([
            'subject' => 'Busy Campaign',
            'body' => 'Content',
            'contact_list_id' => $contactList->id,
            'status' => 'sending'
        ]);

        // 2. Action: Attempt to dispatch the campaign again
        $response = $this->postJson("/api/campaigns/{$campaign->id}/dispatch");

        // 3. Assertion: Verify the middleware blocks the request (Idempotency)
        $response->assertStatus(422);
    }

    #[Test]
    public function it_does_not_dispatch_jobs_for_unsubscribed_contacts()
    {
        Queue::fake();

        // 1. Setup: Create a list with one active and one unsubscribed contact
        $contactList = ContactList::create(['name' => 'Mixed List']);
        
        $activeContact = Contact::create([
            'name' => 'Still Active User',
            'email' => 'active@example.com',
            'status' => 'active'
        ]);

        $unsubscribedContact = Contact::create([
            'name' => 'Unsubscribed User',
            'email' => 'unsubscribed@example.com',
            'status' => 'unsubscribed'
        ]);

        $contactList->contacts()->attach([$activeContact->id, $unsubscribedContact->id]);

        $campaign = Campaign::create([
            'subject' => 'Filtering Test',
            'body' => 'Hello {{name}}',
            'contact_list_id' => $contactList->id,
            'status' => 'draft'
        ]);

        // 2. Action: Dispatch campaign
        $this->postJson("/api/campaigns/{$campaign->id}/dispatch");

        // 3. Assertions
        // It should push ONLY 1 job (for the active contact)
        Queue::assertPushed(SendCampaignEmail::class, 1);
        
        // Verify record exists for active contact
        $this->assertDatabaseHas('campaign_sends', [
            'campaign_id' => $campaign->id,
            'contact_id' => $activeContact->id
        ]);

        // Verify NO record was created for the unsubscribed contact
        $this->assertDatabaseMissing('campaign_sends', [
            'campaign_id' => $campaign->id,
            'contact_id' => $unsubscribedContact->id
        ]);
    }
}