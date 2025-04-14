<?php

namespace App\Http\Controllers;

use App\Models\Queue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class AdminController extends Controller
{
    public function dashboard()
    {
        $queue = Queue::whereDate('created_at', today())->where('status', 'active')->first();

        return view('admin.dashboard', [
            'current_queue' => $queue ? $queue->served_queue_number : 0,
            'total_queue' => $queue ? $queue->current_queue_number : 0
        ]);
    }

    public function serveNextQueue()
    {
        $queue = Queue::whereDate('created_at', today())->where('status', 'active')->first();

        if ($queue && $queue->served_queue_number < $queue->current_queue_number) {
            $queue->served_queue_number++;
            $queue->save();
        }

        return redirect()->route('admin.dashboard');
    }

    public function resetQueue()
    {
        $queue = Queue::whereDate('created_at', today())->where('status', 'active')->first();

        if ($queue) {
            $queue->served_queue_number = 0;
            $queue->current_queue_number = 0;
            $queue->save();

            // Set flag reset di cache untuk 10 menit
            Cache::put('queue_reset_flag', time(), 600);
        }

        return redirect()->route('admin.dashboard')->with('success', 'Antrian berhasil direset!');
    }

    public function getQueueData()
    {
        try {
            $queue = Queue::whereDate('created_at', today())->where('status', 'active')->first();

            $resetFlag = Cache::has('queue_reset_flag') ? Cache::get('queue_reset_flag') : 0;

            if (!$queue) {
                return response()->json([
                    'served_number' => 0,
                    'total_number' => 0,
                    'waiting_number' => 0,
                    'reset_flag' => $resetFlag
                ]);
            }

            return response()->json([
                'served_number' => $queue->served_queue_number,
                'total_number' => $queue->current_queue_number,
                'waiting_number' => $queue->current_queue_number - $queue->served_queue_number,
                'reset_flag' => $resetFlag
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching queue data: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch queue data'], 500);
        }
    }
}
