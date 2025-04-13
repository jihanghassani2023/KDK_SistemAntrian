<?php

namespace App\Http\Controllers;

use App\Models\Queue;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function dashboard()
    {
        // Get the current active queue
        $queue = Queue::where('status', 'active')->first();

        return view('user.dashboard', [
            'served_number' => $queue ? $queue->served_queue_number : 0
        ]);
    }

    public function takeQueueNumber()
    {
        $queue = Queue::where('status', 'active')->first();

        if (!$queue) {
            $queue = new Queue();
            $queue->status = 'active';
        }

        $queue->current_queue_number = $queue->getNextQueueNumber();
        $queue->save();

        return redirect()->route('user.dashboard')
            ->with('queue_number', $queue->current_queue_number);
    }
}
