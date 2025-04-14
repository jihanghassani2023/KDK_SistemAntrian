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
            <a class="nav-link {{ request()->routeIs('user.take-queue.page') ? 'active' : '' }}" href="{{ route('user.take-queue.page') }}">Ambil Antrian</a>
            <form action="{{ route('logout') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-danger btn-sm">Logout</button>
            </form>
        </div>
    </div>
</nav>

<div class="container mt-5">

    @if(session('success'))
        <div class="alert alert-success no-print">{{ session('success') }}</div>
    @elseif(session('error'))
        <div class="alert alert-danger no-print">{{ session('error') }}</div>
    @endif

    @if(!session('queue_number'))
        {{-- Halaman Ambil Antrian --}}
        @if(request()->routeIs('user.take-queue.page'))
            <div class="card text-center no-print">
                <div class="card-header bg-primary text-white">
                    Ambil Nomor Antrian
                </div>
                <div class="card-body">
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
                <a href="{{ route('user.take-queue.page') }}" class="btn btn-primary mt-3">Ambil Antrian</a>
            </div>
        @endif

    @else
        {{-- Jika user sudah memiliki nomor antrian --}}
        <div class="row justify-content-center">
            <div class="col-md-10">

                <div class="row g-4">

                    <div class="col-md-6">
                        <div class="queue-card text-center">
                            <h5>Nomor Antrian Anda</h5>
                            <div class="queue-number user-number">
                                {{ session('queue_number') }}
                            </div>
                            <p class="text-muted">Tanggal {{ session('queue_date') }}</p>
                            <p>Harap tunggu sampai nomor Anda dipanggil.</p>

                            <div class="ticket-card mt-3 d-none d-print-block">
                                <h4>Tiket Antrian</h4>
                                <h1>{{ session('queue_number') }}</h1>
                                <p>Tanggal: {{ session('queue_date') }}</p>
                                <p>Mohon simpan tiket ini dan tunggu nomor Anda dipanggil</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="queue-card text-center">
                            <h5>Nomor Antrian Sedang Dilayani</h5>
                            <div class="queue-number served-number" id="served-number">
                                {{ $served_number > 0 ? $served_number : '-' }}
                            </div>
                            <p class="text-muted">Status Antrian Anda: <span id="queue-status" class="badge
                                @if(session('queue_number') < ($served_number ?? 0))
                                    bg-danger
                                @elseif(session('queue_number') == ($served_number ?? 0))
                                    bg-success
                                @else
                                    bg-primary
                                @endif badge-lg">
                                @if(session('queue_number') < ($served_number ?? 0))
                                    Sudah Lewat
                                @elseif(session('queue_number') == ($served_number ?? 0))
                                    Sedang Dilayani
                                @else
                                    Menunggu
                                @endif
                            </span></p>

                            <div class="progress mt-3 no-print">
                                <div class="progress-bar" role="progressbar" id="queue-progress" style="width:
                                    @if(($served_number ?? 0) == 0 || session('queue_number') <= ($served_number ?? 0))
                                        100%
                                    @else
                                        {{ (($served_number ?? 0) / session('queue_number')) * 100 }}%
                                    @endif
                                " aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>

                            <p class="mt-3">Estimasi waktu tunggu: <span id="wait-time">Menghitung...</span></p>
                        </div>

                        <div class="queue-info no-print mt-3">
                            <h5>Informasi Antrian</h5>
                            <p><strong>Antrian berikutnya:</strong> <span id="next-number">
                                @if($served_number < session('queue_number'))
                                    {{ $served_number + 1 }}
                                @else
                                    -
                                @endif
                            </span></p>
                            <p><strong>Sisa antrian sebelum Anda:</strong> <span id="remaining-before">
                                @if(session('queue_number') > $served_number)
                                    {{ session('queue_number') - $served_number }}
                                @else
                                    0
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
                // Update the displayed served number
                const servedNumberElement = document.getElementById('served-number');
                if (servedNumberElement) {
                    servedNumberElement.textContent = data.served_number > 0 ? data.served_number : '-';
                }

                // Update queue status
                const queueStatusElement = document.getElementById('queue-status');
                const userNumber = {{ session('queue_number') ?? 0 }};

                if (queueStatusElement) {
                    if (userNumber < data.served_number) {
                        queueStatusElement.textContent = 'Sudah Lewat';
                        queueStatusElement.className = 'badge bg-danger badge-lg';
                    } else if (userNumber == data.served_number) {
                        queueStatusElement.textContent = 'Sedang Dilayani';
                        queueStatusElement.className = 'badge bg-success badge-lg';
                    } else {
                        queueStatusElement.textContent = 'Menunggu';
                        queueStatusElement.className = 'badge bg-primary badge-lg';
                    }
                }

                // Update progress bar
                const progressBar = document.getElementById('queue-progress');
                if (progressBar && userNumber > 0) {
                    if (data.served_number == 0 || userNumber <= data.served_number) {
                        progressBar.style.width = '100%';
                    } else {
                        const percentage = (data.served_number / userNumber) * 100;
                        progressBar.style.width = percentage + '%';
                    }
                }

                // Update estimated wait time
                const waitTimeElement = document.getElementById('wait-time');
                if (waitTimeElement && userNumber > data.served_number) {
                    // Estimate 5 minutes per person
                    const peopleAhead = userNumber - data.served_number;
                    const estimatedMinutes = peopleAhead * 5;

                    if (estimatedMinutes < 60) {
                        waitTimeElement.textContent = `± ${estimatedMinutes} menit`;
                    } else {
                        const hours = Math.floor(estimatedMinutes / 60);
                        const minutes = estimatedMinutes % 60;
                        waitTimeElement.textContent = `± ${hours} jam ${minutes} menit`;
                    }
                } else if (waitTimeElement && userNumber <= data.served_number) {
                    waitTimeElement.textContent = 'Segera dilayani';
                }

                // Update next number information
                const nextNumberElement = document.getElementById('next-number');
                if (nextNumberElement) {
                    if (data.served_number < data.total_number) {
                        nextNumberElement.textContent = data.served_number + 1;
                    } else {
                        nextNumberElement.textContent = '-';
                    }
                }

                // Update remaining before you
                const remainingBeforeElement = document.getElementById('remaining-before');
                if (remainingBeforeElement && userNumber > data.served_number) {
                    remainingBeforeElement.textContent = userNumber - data.served_number;
                } else if (remainingBeforeElement) {
                    remainingBeforeElement.textContent = '0';
                }
            })
            .catch(error => console.error('Error fetching served number:', error));
    }

    // Update every 3 seconds
    updateQueueStatus(); // Run immediately
    setInterval(updateQueueStatus, 3000);
</script>
