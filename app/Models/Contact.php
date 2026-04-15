<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Contact extends Model
{
    use HasFactory;

    // Mass assignable attributes for the contact entity
    protected $fillable = ['name', 'email', 'status'];

    // Define the many-to-many relationship with ContactList
    // A contact can belong to multiple segments/lists
    public function lists(): BelongsToMany
    {
        return $this->belongsToMany(ContactList::class, 'contact_contact_list');
    }
}