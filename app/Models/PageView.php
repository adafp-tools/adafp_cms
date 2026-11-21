<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageView extends Model
{
    public $timestamps = false;

    protected $table = 'page_views';

    protected $fillable = ['path', 'ip_hash', 'viewed_at'];

    protected $casts = [
        'viewed_at' => 'datetime',
    ];
}
