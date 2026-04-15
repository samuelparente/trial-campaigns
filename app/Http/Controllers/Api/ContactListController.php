<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContactList;
use App\Http\Requests\StoreContactListRequest;
use App\Http\Requests\AddContactToListRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactListController extends Controller
{
    // Retrieves a paginated list of all contact lists. 
    // Uses dynamic 'per_page' sizing to give the client control over the response size.
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->query('per_page', 15);
        $lists = ContactList::paginate($perPage);
        
        return response()->json($lists);
    }

    // Creates a new contact list using the validated data from StoreContactListRequest.
    public function store(StoreContactListRequest $request): JsonResponse
    {
        $contactList = ContactList::create($request->validated());
        
        return response()->json($contactList, 201);
    }

    // Attaches a contact to the specified list. 
    // Uses syncWithoutDetaching to safely ignore the request if the contact is already in the list, preventing duplicate key errors.
    public function addContact(AddContactToListRequest $request, ContactList $contactList): JsonResponse
    {
        $contactList->contacts()->syncWithoutDetaching([$request->contact_id]);
        
        return response()->json([
            'message' => 'Contact successfully added to the list.'
        ]);
    }
}