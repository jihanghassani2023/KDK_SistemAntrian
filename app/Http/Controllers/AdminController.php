<?php

namespace App\Http\Controllers;

use App\Models\Queue;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        $queue = Queue::where('status', 'active')->first();

        return view('admin.dashboard', [
            'current_queue' => $queue ? $queue->served_queue_number : 0,
            'total_queue' => $queue ? $queue->current_queue_number : 0
        ]);
    }

    public function serveNextQueue()
    {
        $queue = Queue::where('status', 'active')->first();

        if ($queue && $queue->served_queue_number < $queue->current_queue_number) {
            $queue->served_queue_number++;
            $queue->save();
        }

        return redirect()->route('admin.dashboard');
    }

    public function resetQueue()
    {
        $queue = Queue::where('status', 'active')->first();

        if ($queue) {
            $queue->served_queue_number = 0;
            $queue->current_queue_number = 0;
            $queue->save();
        }

        return redirect()->route('admin.dashboard');
    }
}
