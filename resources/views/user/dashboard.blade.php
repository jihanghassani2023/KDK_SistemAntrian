<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard Antrian</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f4f6f9;
        }
        .navbar {
            background-color: #2c3e50;
        }
        .navbar-nav .nav-link {
            color: white !important;
            margin-left: 15px;
        }
        .queue-card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 20px;
        }
        .queue-number {
            font-size: 4rem;
            font-weight: bold;
        }
        .served-number {
            color: #007bff;
        }
        .user-number {
            color: #28a745;
        }
        .ticket-card {
            border: 2px dashed #dee2e6;
            padding: 20px;
            border-radius: 10px;
        }
        .queue-info {
            background-color: #e9f9e7;
            border-radius: 10px;
            padding: 15px;
            margin-top: 20px;
        }
        .badge-lg {
            font-size: 1rem;
            padding: 0.5rem 0.7rem;
        }
        @media print {
            .no-print {
                display: none;
            }
            .ticket-card {
                border: 2px dashed #000;
            }
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg no-print">
    <div class="container-fluid justify-content-between">
        <div class="navbar-brand text-white">Sistem Antrian</div>
        <div class="navbar-nav">
            <a class="nav-link {{ request()->routeIs('user.dashboard') ? 'active' : '' }}" href="{{ route('user.dashboard') }}">Dashboard</a>
            @if(!$user_queue && !session('queue_number'))
                <a class="nav-link {{ request()->routeIs('user.take-queue.page') ? 'active' : '' }}" href="{{ route('user.take-queue.page') }}">Ambil Antrian</a>
            @endif
            <form action="{{ route('logout') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-danger btn-sm">Logout</button>
            </form>
        </div>
    </div>
</nav>

<div class="container mt-5">

    @if(session('success'))
        <div class="alert alert-success no-print">
            {{ session('success') }}
            @if(session('queue_number'))
                <br><strong>Nomor Antrian Anda: {{ session('queue_number') }}</strong>
                <br>Tanggal: {{ session('queue_date') }}
            @endif
        </div>
    @elseif(session('error'))
        <div class="alert alert-danger no-print">{{ session('error') }}</div>
    @elseif(session('info'))
        <div class="alert alert-info no-print">{{ session('info') }}</div>
    @endif

    @if(!$user_queue && !session('queue_number'))
        {{-- Halaman Ambil Antrian --}}
        @if(request()->routeIs('user.take-queue.page'))
            <div class="card text-center no-print">
                <div class="card-header bg-primary text-white">
                    Ambil Nomor Antrian
                </div>
                <div class="card-body">
                    @php
                        $nextQueueNumber = $total_queue + 1;
                    @endphp
                    <p>Nomor antrian Anda: <strong class="text-primary">{{ $nextQueueNumber }}</strong></p>
                    <p>Klik tombol di bawah untuk mengambil nomor antrian:</p>
                    <form action="{{ route('user.take-queue') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-lg">Ambil Antrian</button>
                    </form>
                </div>
            </div>
        @else
            {{-- Dashboard sebelum ambil antrian --}}
            <div class="text-center no-print">
                <h3>Anda belum mengambil nomor antrian!</h3>
                @php
                    $nextQueueNumber = $total_queue + 1;
                @endphp
                <p class="mt-3">Nomor antrian berikutnya: <strong class="text-success">{{ $nextQueueNumber }}</strong></p>
                <a href="{{ route('user.take-queue.page') }}" class="btn btn-primary mt-3">Ambil Antrian</a>
            </div>
        @endif

    @else
        @php
            $currentUserNumber = session('queue_number') ?: ($user_queue ? $user_queue->queue_number : 0);
            $isCompleted = false;
            if ($user_queue && $currentUserNumber > 0) {
                $isCompleted = $currentUserNumber <= $last_completed;
            }
        @endphp

        <div class="row justify-content-center">
            <div class="col-md-10">

                <div class="row g-4">

                    <div class="col-md-6">
                        <div class="queue-card text-center">
                            <h5>Nomor Antrian Anda</h5>
                            <div class="queue-number user-number">
                                @if($isCompleted)
                                    -
                                @else
                                    {{ $currentUserNumber }}
                                @endif
                            </div>
                            <p class="text-muted">
                                @if(session('queue_date'))
                                    Tanggal {{ session('queue_date') }}
                                @elseif($user_queue)
                                    Tanggal {{ \Carbon\Carbon::parse($user_queue->queue_date)->format('d F Y') }}
                                @else
                                    Tanggal {{ today()->format('d F Y') }}
                                @endif
                            </p>
                            <p>
                                @if($isCompleted)
                                    Antrian Anda sudah selesai dilayani. Terima kasih!
                                @else
                                    Harap tunggu sampai nomor Anda dipanggil.
                                @endif
                            </p>

                            <div class="ticket-card mt-3 d-none d-print-block">
                                <h4>Tiket Antrian</h4>
                                <h1>
                                    @if(session('queue_number'))
                                        {{ session('queue_number') }}
                                    @elseif($user_queue)
                                        {{ $user_queue->queue_number }}
                                    @endif
                                </h1>
                                <p>
                                    @if(session('queue_date'))
                                        Tanggal: {{ session('queue_date') }}
                                    @elseif($user_queue)
                                        Tanggal: {{ \Carbon\Carbon::parse($user_queue->queue_date)->format('d F Y') }}
                                    @endif
                                </p>
                                <p>Mohon simpan tiket ini dan tunggu nomor Anda dipanggil</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="queue-card text-center">
                            <h5>Nomor Antrian Sedang Dilayani</h5>
                            <div class="queue-number served-number" id="served-number">
                                {{ $currently_serving > 0 ? $currently_serving : '-' }}
                            </div>
                            <p class="text-muted">Status Antrian Anda: <span id="queue-status" class="badge
                                @if($isCompleted)
                                    bg-success
                                @elseif($currentUserNumber == $currently_serving)
                                    bg-warning
                                @else
                                    bg-primary
                                @endif badge-lg">
                                @if($isCompleted)
                                    Selesai
                                @elseif($currentUserNumber == $currently_serving)
                                    Sedang Dilayani
                                @else
                                    Menunggu
                                @endif
                            </span></p>

                            <div class="progress mt-3 no-print">
                                <div class="progress-bar" role="progressbar" id="queue-progress" style="width:
                                    @if($isCompleted || $currentUserNumber == $currently_serving)
                                        100%
                                    @elseif($currently_serving > 0 && $currentUserNumber > $currently_serving)
                                        {{ ($currently_serving / $currentUserNumber) * 100 }}%
                                    @else
                                        0%
                                    @endif
                                " aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>

                            <p class="mt-3">
                                @if($isCompleted)
                                    Antrian selesai!
                                @elseif($currentUserNumber == $currently_serving)
                                    Nomor Anda sedang dilayani
                                @else
                                    Estimasi waktu tunggu: <span id="wait-time">Menghitung...</span>
                                @endif
                            </p>
                        </div>

                        <div class="queue-info no-print mt-3">
                            <h5>Informasi Antrian</h5>
                            <p><strong>Antrian berikutnya:</strong> <span id="next-number">
                                @if($isCompleted)
                                    -
                                @elseif($currently_serving > 0 && $currently_serving < $currentUserNumber)
                                    {{ $currently_serving + 1 }}
                                @elseif($currently_serving == 0 && $last_completed < $currentUserNumber)
                                    {{ $last_completed + 1 }}
                                @else
                                    -
                                @endif
                            </span></p>
                            <p><strong>Sisa antrian sebelum Anda:</strong> <span id="remaining-before">
                                @if($isCompleted)
                                    0
                                @elseif($currently_serving > 0)
                                    {{ max(0, $currentUserNumber - $currently_serving) }}
                                @else
                                    {{ max(0, $currentUserNumber - $last_completed) }}
                                @endif
                            </span></p>
                        </div>
                    </div>

                </div>

                <div class="text-center mt-4 no-print">
                    <button onclick="window.print()" class="btn btn-secondary me-2">Cetak Tiket</button>
                    <a href="{{ route('user.dashboard') }}" class="btn btn-primary me-2">Refresh</a>
                </div>

            </div>
        </div>
    @endif

</div>

<script>
    function updateQueueStatus() {
        fetch('{{ route("user.served-number") }}')
            .then(response => response.json())
            .then(data => {

                const servedNumberElement = document.getElementById('served-number');
                if (servedNumberElement) {
                    servedNumberElement.textContent = data.currently_serving > 0 ? data.currently_serving : '-';
                }


                const queueStatusElement = document.getElementById('queue-status');
                const userNumber = {{ session('queue_number') ?: ($user_queue ? $user_queue->queue_number : 0) }};
                const hasUserQueue = {{ $user_queue ? 'true' : 'false' }};

                if (queueStatusElement && userNumber > 0) {
                    if (hasUserQueue && userNumber <= data.last_completed) {
                        queueStatusElement.textContent = 'Selesai';
                        queueStatusElement.className = 'badge bg-success badge-lg';

                        const userQueueElement = document.querySelector('.user-number');
                        if (userQueueElement) {
                            userQueueElement.textContent = '-';
                        }

                        const messageElement = userQueueElement?.closest('.queue-card').querySelector('p:last-of-type');
                        if (messageElement) {
                            messageElement.textContent = 'Antrian Anda sudah selesai dilayani. Terima kasih!';
                        }
                    } else if (userNumber == data.currently_serving) {
                        queueStatusElement.textContent = 'Sedang Dilayani';
                        queueStatusElement.className = 'badge bg-warning badge-lg';
                    } else {
                        queueStatusElement.textContent = 'Menunggu';
                        queueStatusElement.className = 'badge bg-primary badge-lg';
                    }
                }

                const progressBar = document.getElementById('queue-progress');
                if (progressBar && userNumber > 0) {
                    const isCompleted = hasUserQueue && userNumber <= data.last_completed;
                    if (isCompleted || userNumber == data.currently_serving) {
                        progressBar.style.width = '100%';
                    } else if (data.currently_serving > 0 && userNumber > data.currently_serving) {
                        const percentage = (data.currently_serving / userNumber) * 100;
                        progressBar.style.width = percentage + '%';
                    } else {
                        progressBar.style.width = '0%';
                    }
                }


                const waitTimeElement = document.getElementById('wait-time');
                if (waitTimeElement) {
                    const isCompleted = hasUserQueue && userNumber <= data.last_completed;
                    if (isCompleted) {
                        waitTimeElement.parentElement.textContent = 'Antrian selesai!';
                    } else if (userNumber == data.currently_serving) {
                        waitTimeElement.parentElement.textContent = 'Nomor Anda sedang dilayani';
                    } else if (userNumber > 0) {

                        let peopleAhead = 0;
                        if (data.currently_serving > 0) {
                            peopleAhead = userNumber - data.currently_serving;
                        } else {
                            peopleAhead = userNumber - data.last_completed;
                        }

                        const estimatedMinutes = Math.max(0, peopleAhead) * 5;

                        if (estimatedMinutes < 60) {
                            waitTimeElement.textContent = `± ${estimatedMinutes} menit`;
                        } else {
                            const hours = Math.floor(estimatedMinutes / 60);
                            const minutes = estimatedMinutes % 60;
                            waitTimeElement.textContent = `± ${hours} jam ${minutes} menit`;
                        }
                    }
                }

                const nextNumberElement = document.getElementById('next-number');
                if (nextNumberElement) {
                    const isCompleted = hasUserQueue && userNumber <= data.last_completed;
                    if (isCompleted) {
                        nextNumberElement.textContent = '-';
                    } else if (data.currently_serving > 0 && data.currently_serving < userNumber) {
                        nextNumberElement.textContent = data.currently_serving + 1;
                    } else if (data.currently_serving === 0 && data.last_completed < userNumber) {
                        nextNumberElement.textContent = data.last_completed + 1;
                    } else {
                        nextNumberElement.textContent = '-';
                    }
                }

                const remainingBeforeElement = document.getElementById('remaining-before');
                if (remainingBeforeElement) {
                    const isCompleted = hasUserQueue && userNumber <= data.last_completed;
                    if (isCompleted) {
                        remainingBeforeElement.textContent = '0';
                    } else if (data.currently_serving > 0) {
                        remainingBeforeElement.textContent = Math.max(0, userNumber - data.currently_serving);
                    } else {
                        remainingBeforeElement.textContent = Math.max(0, userNumber - data.last_completed);
                    }
                }
            })
            .catch(error => console.error('Error fetching served number:', error));
    }

    @if($user_queue || session('queue_number'))
        updateQueueStatus();
        setInterval(updateQueueStatus, 3000);
    @endif
</script>
</body>
</html>
