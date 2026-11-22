<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FormSubmission extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'form_type', 'locale', 'name', 'email', 'phone',
        'subject', 'message', 'dob', 'address', 'party_role',
        'consent', 'consent_at', 'ip_hash', 'read_at',
    ];

    protected $casts = [
        'consent'    => 'boolean',
        'dob'        => 'date',
        'consent_at' => 'datetime',
        'read_at'    => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function scopeContact($query)
    {
        return $query->where('form_type', 'contact');
    }

    public function scopeJoin($query)
    {
        return $query->where('form_type', 'join');
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }
}
