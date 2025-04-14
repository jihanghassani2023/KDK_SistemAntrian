<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserQueue extends Model
{
    protected $fillable = [
        'queue_id',
        'user_id',
        'queue_number',
        'queue_date'
    ];

    public function queue()
    {
        return $this->belongsTo(Queue::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
