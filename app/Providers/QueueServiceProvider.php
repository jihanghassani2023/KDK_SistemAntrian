<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Queue;
use Illuminate\Support\Facades\View;

class QueueServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        View::composer(['admin.dashboard', 'user.dashboard'], function ($view) {
            $todayQueue = Queue::whereDate('created_at', today())
                ->where('status', 'active')
                ->first();

            $view->with([
                'served_number' => $todayQueue ? $todayQueue->served_queue_number : 0,
                'total_queue' => $todayQueue ? $todayQueue->current_queue_number : 0,
                'today_date' => today()->format('d F Y')
            ]);
        });
    }
}
