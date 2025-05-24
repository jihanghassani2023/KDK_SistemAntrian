<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Queue;
use App\Models\UserQueue;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        $todayQueue = Queue::whereDate('created_at', today())->where('status', 'active')->first();

        // Cek apakah user sudah punya antrian hari ini
        $userQueue = UserQueue::where('user_id', $user->id)
                              ->where('queue_date', today())
                              ->first();

        // Menentukan nomor yang sedang dilayani dan terakhir selesai
        $currentlyServing = 0;
        $lastCompleted = 0;

        if ($todayQueue) {
            $served = $todayQueue->served_queue_number;
            $total = $todayQueue->current_queue_number;

            if ($served > 0 && $served <= $total) {
                // Sedang melayani nomor positif
                $currentlyServing = $served;
                $lastCompleted = max(0, $served - 1);
            } else if ($served < 0) {
                // Nilai negatif = nomor abs($served) sudah selesai, belum panggil berikutnya
                $currentlyServing = 0;
                $lastCompleted = abs($served);
            } else if ($served > $total && $total > 0) {
                // Semua sudah selesai - tapi hanya jika memang ada antrian sebelumnya
                $currentlyServing = 0;
                $lastCompleted = $total;
            } else {
                // served = 0, belum ada yang dipanggil
                $currentlyServing = 0;
                $lastCompleted = 0;
            }
        }

        return view('user.dashboard', [
            'served_number' => $todayQueue ? $todayQueue->served_queue_number : 0,
            'total_queue' => $todayQueue ? $todayQueue->current_queue_number : 0,
            'currently_serving' => $currentlyServing,
            'last_completed' => $lastCompleted,
            'user_queue' => $userQueue
        ]);
    }

    public function takeQueuePage()
    {
        $user = Auth::user();
        $todayQueue = Queue::whereDate('created_at', today())->where('status', 'active')->first();

        // Cek apakah user sudah mengambil antrian hari ini
        $userQueue = UserQueue::where('user_id', $user->id)
                              ->where('queue_date', today())
                              ->first();

        if ($userQueue) {
            // Jika sudah ada, redirect ke dashboard dengan data antrian
            return redirect()->route('user.dashboard');
        }

        // Menentukan nomor yang sedang dilayani dan terakhir selesai
        $currentlyServing = 0;
        $lastCompleted = 0;

        if ($todayQueue) {
            $served = $todayQueue->served_queue_number;
            $total = $todayQueue->current_queue_number;

            if ($served > 0 && $served <= $total) {
                // Sedang melayani nomor positif
                $currentlyServing = $served;
                $lastCompleted = max(0, $served - 1);
            } else if ($served < 0) {
                // Nilai negatif = nomor abs($served) sudah selesai, belum panggil berikutnya
                $currentlyServing = 0;
                $lastCompleted = abs($served);
            } else if ($served > $total && $total > 0) {
                // Semua sudah selesai - tapi hanya jika memang ada antrian sebelumnya
                $currentlyServing = 0;
                $lastCompleted = $total;
            } else {
                // served = 0, belum ada yang dipanggil
                $currentlyServing = 0;
                $lastCompleted = 0;
            }
        }

        return view('user.dashboard', [
            'served_number' => $todayQueue ? $todayQueue->served_queue_number : 0,
            'total_queue' => $todayQueue ? $todayQueue->current_queue_number : 0,
            'currently_serving' => $currentlyServing,
            'last_completed' => $lastCompleted,
            'user_queue' => null
        ]);
    }

    public function takeQueue(Request $request)
{
    try {
        $user = Auth::user();

        // Cek apakah user sudah mengambil antrian hari ini
        $existingUserQueue = UserQueue::where('user_id', $user->id)
                                      ->where('queue_date', today())
                                      ->first();

        if ($existingUserQueue) {
            return redirect()->route('user.dashboard')
                ->with('info', 'Anda sudah mengambil nomor antrian hari ini');
        }

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

        // **HAPUS RESET LOGIC - Biarkan served_queue_number apa adanya**
        // Sistem admin yang akan menangani logic pemanggilan antrian

        // Tambah nomor antrian
        $queueNumber = $todayQueue->current_queue_number + 1;
        $todayQueue->current_queue_number = $queueNumber;
        $todayQueue->save();

        // Simpan antrian user ke database
        UserQueue::create([
            'queue_id' => $todayQueue->id,
            'user_id' => $user->id,
            'queue_number' => $queueNumber,
            'queue_date' => today()
        ]);

        return redirect()->route('user.dashboard')
            ->with('success', 'Nomor antrian berhasil diambil');

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

            if (!$todayQueue) {
                return response()->json([
                    'served_number' => 0,
                    'total_number' => 0,
                    'currently_serving' => 0,
                    'last_completed' => 0
                ]);
            }

            $served = $todayQueue->served_queue_number;
            $total = $todayQueue->current_queue_number;

            // Menentukan nomor yang sedang dilayani dan terakhir selesai
            $currentlyServing = 0;
            $lastCompleted = 0;

            if ($served > 0 && $served <= $total) {
                // Sedang melayani nomor positif
                $currentlyServing = $served;
                $lastCompleted = max(0, $served - 1);
            } else if ($served < 0) {
                // Nilai negatif = nomor abs($served) sudah selesai, belum panggil berikutnya
                $currentlyServing = 0;
                $lastCompleted = abs($served);
            } else if ($served > $total && $total > 0) {
                // Semua sudah selesai - tapi hanya jika memang ada antrian sebelumnya
                $currentlyServing = 0;
                $lastCompleted = $total;
            } else {
                // served = 0, belum ada yang dipanggil
                $currentlyServing = 0;
                $lastCompleted = 0;
            }

            return response()->json([
                'served_number' => $served,
                'total_number' => $total,
                'currently_serving' => $currentlyServing,
                'last_completed' => $lastCompleted
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting served number: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to get served number'], 500);
        }
    }
}
