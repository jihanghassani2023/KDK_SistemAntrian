<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Queue;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function dashboard()
    {
        $todayQueue = Queue::whereDate('created_at', today())->where('status', 'active')->first();

        return view('user.dashboard', [
            'served_number' => $todayQueue ? $todayQueue->served_queue_number : 0
        ]);
    }

    public function takeQueuePage()
    {
        return view('user.dashboard');
    }

    public function takeQueue(Request $request)
    {
        try {
            // Cek apakah ada antrian aktif hari ini
            $todayQueue = Queue::whereDate('created_at', today())->where('status', 'active')->first();

            if (!$todayQueue) {
                // Buat antrian baru jika belum ada
                $todayQueue = Queue::create([
                    'current_queue_number' => 0,
                    'served_queue_number' => 0,
                    'status' => 'active',
                    'date' => today()
                ]);
            }

            // Tambah nomor antrian
            $queueNumber = $todayQueue->current_queue_number + 1;
            $todayQueue->current_queue_number = $queueNumber;
            $todayQueue->save();

            return redirect()->route('user.dashboard')
                ->with('success', 'Nomor antrian berhasil diambil')
                ->with('queue_number', $queueNumber)
                ->with('queue_date', today()->format('d F Y'));
        } catch (\Exception $e) {
            Log::error('Error taking queue: ' . $e->getMessage());
            return redirect()->route('user.dashboard')
                ->with('error', 'Gagal mengambil nomor antrian. Silakan coba lagi.');
        }
    }

    public function servedNumber()
    {
        try {
            $todayQueue = Queue::whereDate('created_at', today())->where('status', 'active')->first();

            return response()->json([
                'served_number' => $todayQueue ? $todayQueue->served_queue_number : 0,
                'total_number' => $todayQueue ? $todayQueue->current_queue_number : 0
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting served number: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to get served number'], 500);
        }
    }
}
