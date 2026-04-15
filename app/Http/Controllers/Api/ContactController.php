<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Http\Requests\StoreContactRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    // Retrieves a paginated list of contacts. 
    // Allows the client to specify the 'per_page' query parameter (defaults to 15).
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->query('per_page', 15);
        $contacts = Contact::paginate($perPage);
        
        return response()->json($contacts);
    }

    // Creates a new contact in the database. 
    // Only uses data that has passed validation in the StoreContactRequest.
    public function store(StoreContactRequest $request): JsonResponse
    {
        $contact = Contact::create($request->validated());
        
        return response()->json($contact, 201);
    }

    // Updates the status of a specific contact to 'unsubscribed'.
    // This ensures they are excluded from future campaign dispatches.
    public function unsubscribe(Contact $contact): JsonResponse
    {
        $contact->update(['status' => 'unsubscribed']);
        
        return response()->json([
            'message' => 'Contact successfully unsubscribed.',
            'contact' => $contact
        ]);
    }
}