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

        $served = $queue ? $queue->served_queue_number : 0;
        $total = $queue ? $queue->current_queue_number : 0;
        $currentlyServing = 0;
        $lastCompleted = 0;

        if ($served > 0 && $served <= $total) {
            $currentlyServing = $served;
            $lastCompleted = $served - 1;
        } else if ($served < 0) {
            $currentlyServing = 0;
            $lastCompleted = abs($served);
        } else if ($served > $total && $total > 0) {
            $currentlyServing = 0;
            $lastCompleted = $total;
        } else if ($served == 0 && $total > 0) {
            $currentlyServing = 0;
            $lastCompleted = 0;
        }

        return view('admin.dashboard', [
            'current_queue' => $served,
            'total_queue' => $total,
            'currently_serving' => $currentlyServing,
            'last_completed' => $lastCompleted
        ]);
    }

    public function serveNextQueue()
    {
        $queue = Queue::whereDate('created_at', today())->where('status', 'active')->first();

        if ($queue && $queue->current_queue_number > 0) {
            $currentStep = $this->getCurrentStep($queue);

            if ($currentStep['action'] == 'call_first') {

                $nextNumber = $this->getNextQueueNumber($queue);
                $queue->served_queue_number = $nextNumber;
            } else if ($currentStep['action'] == 'finish_current') {

                $queue->served_queue_number = -$queue->served_queue_number;
            } else if ($currentStep['action'] == 'call_next') {

                $nextNumber = abs($queue->served_queue_number) + 1;
                $queue->served_queue_number = $nextNumber;
            } else if ($currentStep['action'] == 'finish_last') {
                $queue->served_queue_number = -$queue->current_queue_number;
            }

            $queue->save();
        }

        return redirect()->route('admin.dashboard');
    }

    private function getCurrentStep($queue)
    {
        $served = $queue->served_queue_number;
        $total = $queue->current_queue_number;

        if ($served == 0 && $total > 0) {
            return ['action' => 'call_first', 'button' => 'Panggil'];
        } else if ($served > 0 && $served <= $total) {
            if ($served == $total) {
                return ['action' => 'finish_last', 'button' => 'Selesai'];
            } else {
                return ['action' => 'finish_current', 'button' => 'Selesai'];
            }
        } else if ($served < 0) {

            $lastCompleted = abs($served);
            if ($lastCompleted < $total) {

                return ['action' => 'call_next', 'button' => 'Panggil'];
            } else {

                return ['action' => 'none', 'button' => 'Semua Selesai'];
            }
        } else {

            return ['action' => 'none', 'button' => 'Tidak Ada Antrian'];
        }
    }

    private function getNextQueueNumber($queue)
    {
        $served = $queue->served_queue_number;
        $total = $queue->current_queue_number;

        if ($served == 0) {
            if ($total > 0) {
                return 1;
            }
        } else if ($served < 0) {

            $lastCompleted = abs($served);
            if ($lastCompleted < $total) {
                return $lastCompleted + 1;
            }
        }

        return 1;
    }

    public function resetQueue()
    {
        $queue = Queue::whereDate('created_at', today())->where('status', 'active')->first();

        if ($queue) {
            $queue->served_queue_number = 0;
            $queue->current_queue_number = 0;
            $queue->save();

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
                    'reset_flag' => $resetFlag,
                    'currently_serving' => 0,
                    'button_text' => 'Tidak Ada Antrian',
                    'button_enabled' => false,
                    'last_completed' => 0
                ]);
            }

            $step = $this->getCurrentStep($queue);
            $served = $queue->served_queue_number;
            $total = $queue->current_queue_number;

            $currentlyServing = 0;
            $lastCompleted = 0;

            if ($served > 0 && $served <= $total) {
                $currentlyServing = $served;
                $lastCompleted = $served - 1;
            } else if ($served < 0) {
                $currentlyServing = 0;
                $lastCompleted = abs($served);
            } else if ($served == 0 && $total > 0) {
                $currentlyServing = 0;
                $lastCompleted = 0;
            }

            $waitingNumber = 0;
            if ($currentlyServing > 0) {
                $waitingNumber = max(0, $total - $currentlyServing);
            } else if ($lastCompleted < $total) {
                $waitingNumber = $total - $lastCompleted;
            }

            return response()->json([
                'served_number' => $served,
                'total_number' => $total,
                'waiting_number' => $waitingNumber,
                'reset_flag' => $resetFlag,
                'currently_serving' => $currentlyServing,
                'button_text' => $step['button'],
                'button_enabled' => $step['action'] != 'none',
                'last_completed' => $lastCompleted
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching queue data: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch queue data'], 500);
        }
    }
}
