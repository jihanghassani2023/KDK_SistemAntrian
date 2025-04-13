<!-- resources/views/user/dashboard.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Dashboard - Queue Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-12 text-end mb-3">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-danger">Logout</button>
                </form>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title">Nomor Antrian Dilayani</h5>
                        <div id="served-number" class="display-4">
                            {{ $served_number ?? 0 }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title">Ambil Nomor Antrian</h5>
                        <form action="{{ route('user.take-queue') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-lg">
                                Ambil Nomor
                            </button>
                        </form>
                        @if(session('queue_number'))
                            <div class="alert alert-success mt-3">
                                Nomor Antrian Anda: {{ session('queue_number') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Real-time update for served number (using polling)
        function updateServedNumber() {
            fetch('{{ route("user.dashboard") }}')
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const servedNumber = doc.getElementById('served-number').textContent;
                    document.getElementById('served-number').textContent = servedNumber;
                });
        }

        // Poll every 5 seconds
        setInterval(updateServedNumber, 5000);
    </script>
</body>
</html>
