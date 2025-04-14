<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Queue extends Model
{
    protected $fillable = [
        'current_queue_number',
        'served_queue_number',
        'status',
        'date'
    ];

    public function user_queues()
    {
        return $this->hasMany(UserQueue::class);
    }

    // Metode untuk mengecek apakah user sudah punya antrian
    public function hasUserQueue($userId)
    {
        return $this->user_queues()
            ->where('user_id', $userId)
            ->exists();
    }

    // Metode untuk mendapatkan nomor antrian user
    public function getUserQueueNumber($userId)
    {
        $userQueue = $this->user_queues()
            ->where('user_id', $userId)
            ->first();

        return $userQueue ? $userQueue->queue_number : null;
    }
}
