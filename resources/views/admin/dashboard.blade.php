<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Queue Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
            font-family: Arial, sans-serif;
        }
        .main-header {
            background-color: #2c3e50;
            color: white;
            padding: 10px 0;
        }
        .main-header .nav-link {
            color: white;
            font-weight: 500;
            padding: 8px 15px;
            border-radius: 4px;
        }
        .main-header .nav-link.active {
            background-color: rgba(255, 255, 255, 0.2);
        }
        .main-header .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        .panel {
            background-color: #ffffff;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .panel-header {
            background-color: #007bff;
            color: white;
            padding: 10px 15px;
            border-radius: 5px 5px 0 0;
        }
        .panel-title {
            margin: 0;
            font-size: 18px;
        }
        .panel-body {
            padding: 15px;
        }
        .status-card {
            text-align: center;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        .status-waiting {
            background-color: #17a2b8;
            color: white;
        }
        .status-serving {
            background-color: #ffc107;
            color: #343a40;
        }
        .status-completed {
            background-color: #28a745;
            color: white;
        }
        .queue-number {
            font-size: 72px;
            font-weight: bold;
            margin: 20px 0;
        }
        .queue-status {
            background-color: #e9f9e7;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        .btn-reset {
            background-color: #dc3545;
            color: white;
            border: none;
        }
        .table-responsive {
            overflow-x: auto;
        }
        .table th {
            background-color: #f8f9fa;
        }
        .alert-info {
            background-color: #e3f2fd;
            border-color: #b9def0;
        }
        .admin-panel {
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <!-- Header Navigation -->
    <header class="main-header">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="text-white mb-0 ps-3">Sistem Antrian</h4>
                <div class="d-flex align-items-center">
                    <ul class="nav">
                        <li class="nav-item">
                            <a class="nav-link" href="#" id="nav-operator">Operator</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" id="nav-admin">Admin</a>
                        </li>
                    </ul>
                    <form action="{{ route('logout') }}" method="POST" class="d-inline me-3">
                        @csrf
                        <button type="submit" class="btn btn-danger">Logout</button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <div class="container mt-4">
        <!-- Operator Panel - Initially Hidden -->
        <div id="operator-panel" style="display: none;">
            <div class="panel">
                <div class="panel-header">
                    <h4 class="panel-title">Panel Operator</h4>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="status-card status-waiting">
                                <h5>Menunggu</h5>
                                <div class="h1" id="waiting-number">
                                    @if($total_queue > $current_queue)
                                        {{ $current_queue + 1 }}
                                    @else
                                        -
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="status-card status-serving">
                                <h5>Sedang Dilayani</h5>
                                <div class="h1" id="serving-number">
                                    {{ $current_queue > 0 ? $current_queue : '-' }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="status-card status-completed">
                                <h5>Terakhir Selesai</h5>
                                <div class="h1" id="completed-number">
                                    {{ $current_queue > 0 ? $current_queue - 1 : '-' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-8">
                    <!-- Daftar nomor yang menunggu -->
                    <div class="panel">
                        <div class="panel-header bg-primary">
                            <h4 class="panel-title">Daftar Nomor Antrian</h4>
                        </div>
                        <div class="panel-body">
                            <h5>Nomor Antrian yang Menunggu:</h5>
                            <div class="d-flex flex-wrap" id="waiting-list">
                                @if($total_queue > $current_queue)
                                    @for($i = $current_queue + 1; $i <= $total_queue; $i++)
                                        <span class="badge bg-info m-1 p-2">{{ $i }}</span>
                                    @endfor
                                @else
                                    <p class="text-muted">Tidak ada antrian yang menunggu</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="panel">
                        <div class="panel-header bg-info">
                            <h4 class="panel-title">Informasi</h4>
                        </div>
                        <div class="panel-body">
                            <p><strong>Tanggal:</strong> <span id="current-date">{{ date('d F Y') }}</span></p>
                            <p><strong>Total Antrian:</strong> <span id="total-number">{{ $total_queue }}</span></p>
                            <p><strong>Total Selesai:</strong> <span id="total-completed">{{ $current_queue }}</span></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Admin Panel - Initially Hidden -->
        <div id="admin-panel" style="display: none;">
            <div class="queue-status">
                <p class="mb-0" id="queue-status-text">
                    @if($current_queue > 0)
                        Antrian nomor {{ $current_queue }} sedang dilayani
                    @else
                        Tidak ada antrian yang sedang dilayani
                    @endif
                </p>
            </div>

            <div class="panel">
                <div class="panel-header">
                    <h4 class="panel-title">Admin Panel Antrian</h4>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="panel">
                                <div class="panel-header bg-success">
                                    <h4 class="panel-title">Sedang Dilayani</h4>
                                </div>
                                <div class="panel-body text-center">
                                    <div class="queue-number" id="current-serving">{{ $current_queue > 0 ? $current_queue : '-' }}</div>
                                    <form action="{{ route('admin.serve-next') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-success" id="btn-complete" {{ $current_queue >= $total_queue ? 'disabled' : '' }}>Selesai</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="panel">
                                <div class="panel-header text-white" style="background-color: #17a2b8;">
                                    <h4 class="panel-title">Antrian Berikutnya</h4>
                                </div>
                                <div class="panel-body">
                                    <p class="text-center" id="next-queue-info">
                                        @if($total_queue > $current_queue)
                                            Antrian berikutnya: {{ $current_queue + 1 }}
                                        @else
                                            Tidak ada antrian yang menunggu
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="panel mt-4">
                        <div class="panel-header bg-secondary">
                            <h4 class="panel-title">Antrian Selesai</h4>
                        </div>
                        <div class="panel-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Nomor Antrian</th>
                                            <th>Waktu Selesai</th>
                                        </tr>
                                    </thead>
                                    <tbody id="completed-queue-list">
                                        <!-- Queue history will appear here when customers are served -->
                                        @if($current_queue == 0)
                                            <tr>
                                                <td colspan="2" class="text-center">Belum ada antrian yang selesai</td>
                                            </tr>
                                        @else
                                            @for($i = 1; $i < $current_queue; $i++)
                                                <tr>
                                                    <td>{{ $i }}</td>
                                                    <td>{{ \Carbon\Carbon::now()->subMinutes(($current_queue - $i) * 5)->format('H:i:s') }}</td>
                                                </tr>
                                            @endfor
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="text-center mt-3">
                        <form action="{{ route('admin.reset-queue') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-danger" id="btn-admin-reset">Reset Antrian</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Navigation functionality
        document.getElementById('nav-operator').addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('operator-panel').style.display = 'block';
            document.getElementById('admin-panel').style.display = 'none';

            // Update active navigation
            document.getElementById('nav-operator').classList.add('active');
            document.getElementById('nav-admin').classList.remove('active');
        });

        document.getElementById('nav-admin').addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('admin-panel').style.display = 'block';
            document.getElementById('operator-panel').style.display = 'none';

            // Update active navigation
            document.getElementById('nav-admin').classList.add('active');
            document.getElementById('nav-operator').classList.remove('active');
        });

        // Set default active tab (Admin)
        document.getElementById('nav-admin').click();

        // Function to fetch queue data and update UI
        function fetchQueueData() {
            fetch('{{ route("admin.queue-data") }}')
                .then(response => response.json())
                .then(data => {
                    // Update operator panel - nomor antrian spesifik, bukan jumlah
                    // Nomor yang sedang menunggu
                    if (data.total_number > data.served_number) {
                        document.getElementById('waiting-number').textContent = (data.served_number + 1);
                    } else {
                        document.getElementById('waiting-number').textContent = '-';
                    }

                    // Nomor yang sedang dilayani
                    document.getElementById('serving-number').textContent =
                        data.served_number > 0 ? data.served_number : '-';

                    // Nomor terakhir yang selesai
                    document.getElementById('completed-number').textContent =
                        data.served_number > 0 ? (data.served_number - 1) : '-';

                    // Update informasi total
                    document.getElementById('total-number').textContent = data.total_number;
                    document.getElementById('total-completed').textContent = data.served_number;

                    // Update admin panel
                    document.getElementById('current-serving').textContent =
                        data.served_number > 0 ? data.served_number : '-';

                    // Update queue status text
                    if (data.served_number > 0) {
                        document.getElementById('queue-status-text').textContent =
                            `Antrian nomor ${data.served_number} sedang dilayani`;
                    } else {
                        document.getElementById('queue-status-text').textContent =
                            'Tidak ada antrian yang sedang dilayani';
                    }

                    // Update next queue info
                    if (data.total_number > data.served_number) {
                        document.getElementById('next-queue-info').textContent =
                            `Antrian berikutnya: ${data.served_number + 1}`;
                    } else {
                        document.getElementById('next-queue-info').textContent =
                            'Tidak ada antrian yang menunggu';
                    }

                    // Update button state
                    document.getElementById('btn-complete').disabled =
                        data.total_number <= data.served_number;

                    // Update waiting list
                    const waitingList = document.getElementById('waiting-list');
                    if (data.total_number <= data.served_number) {
                        waitingList.innerHTML = '<p class="text-muted">Tidak ada antrian yang menunggu</p>';
                    } else {
                        let html = '';
                        for (let i = data.served_number + 1; i <= data.total_number; i++) {
                            html += `<span class="badge bg-info m-1 p-2">${i}</span>`;
                        }
                        waitingList.innerHTML = html;
                    }

                    // Update completed queue list
                    const tbody = document.getElementById('completed-queue-list');
                    if (data.served_number === 0) {
                        tbody.innerHTML = '<tr><td colspan="2" class="text-center">Belum ada antrian yang selesai</td></tr>';
                    } else {
                        let tableContent = '';
                        for (let i = 1; i < data.served_number; i++) {
                            const time = new Date();
                            time.setMinutes(time.getMinutes() - ((data.served_number - i) * 5));
                            const timeString = time.toTimeString().split(' ')[0];

                            tableContent += `<tr>
                                <td>${i}</td>
                                <td>${timeString}</td>
                            </tr>`;
                        }

                        if (tableContent === '') {
                            tbody.innerHTML = '<tr><td colspan="2" class="text-center">Belum ada antrian yang selesai</td></tr>';
                        } else {
                            tbody.innerHTML = tableContent;
                        }
                    }
                })
                .catch(error => console.error('Error fetching queue data:', error));
        }

        // Initialize the current date
        document.getElementById('current-date').textContent = new Date().toLocaleDateString('id-ID', {
            day: '2-digit',
            month: 'long',
            year: 'numeric'
        });

        // Fetch queue data every 5 seconds
        fetchQueueData(); // Run immediately
        setInterval(fetchQueueData, 5000);
    </script>
</body>
</html>
