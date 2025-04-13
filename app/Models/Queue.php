<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Queue extends Model
{
    protected $fillable = [
        'current_queue_number',
        'served_queue_number',
        'status'
    ];

    // Method to get the next queue number
    public function getNextQueueNumber()
    {
        $lastQueue = self::orderBy('current_queue_number', 'desc')->first();
        return $lastQueue ? $lastQueue->current_queue_number + 1 : 1;
    }
}
