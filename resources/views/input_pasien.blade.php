<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Data Pasien</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            background-image: url('{{ asset('images/HOME2.png') }}');
            background-blend-mode: multiply;
            background-color: rgba(0, 0, 0, 0.7);
            font-family: 'Poppins', sans-serif;
        }
    </style>
    <script src="https://cdn.socket.io/4.0.0/socket.io.min.js"></script>
</head>

<body>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <div class="form-container d-flex justify-content-center align-items-center" style="height: 100vh;">
        <div class="card">
            <div class="card-body">
                <h2 class="text-center mb-4">Silakan Masukan Data Pasien</h2>
                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif
                <form action="/input-pasien" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="nama" class="form-label">Nama</label>
                        <input type="text" class="form-control" id="nama" name="nama" required>
                    </div>
                    <div class="mb-3">
                        <label for="gender" class="form-label">Jenis Kelamin</label>
                        <select class="form-select" id="gender" name="gender" required>
                            <option value="Laki-laki">Laki-laki</option>
                            <option value="Perempuan">Perempuan</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="umur" class="form-label">Umur</label>
                        <input type="number" class="form-control" id="umur" name="umur" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100"
                        style="background-color: #28AE96;">Selanjutnya</button>
                </form>
            </div>
        </div>
    </div>
</body>

</html>
