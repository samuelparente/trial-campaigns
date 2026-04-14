<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContactList extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    // Link contacts to this specific list via pivot table
    public function contacts(): BelongsToMany
    {
        return $this->belongsToMany(Contact::class, 'contact_contact_list');
    }

    // A list can be targetted by multiple marketing campaigns
    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class);
    }
}