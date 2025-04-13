<!-- resources/views/admin/dashboard.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Queue Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title">Antrian Sedang Dilayani</h5>
                        <div class="display-4">{{ $current_queue }}</div>
                        <form action="{{ route('admin.serve-next') }}" method="POST" class="mt-3">
                            @csrf
                            <button type="submit" class="btn btn-primary">Layani Berikutnya</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title">Total Antrian</h5>
                        <div class="display-4">{{ $total_queue }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title">Reset Antrian</h5>
                        <form action="{{ route('admin.reset-queue') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-danger">Reset Antrian</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
