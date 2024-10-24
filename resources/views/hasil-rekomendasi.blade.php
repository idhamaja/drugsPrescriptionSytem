<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Data Keseluruhan Pasien</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        /* Merapikan ukuran dan margin tombol paginasi */
        .pagination {
            font-size: 1rem;
            margin-top: 20px;
        }

        .page-item .page-link {
            padding: 8px 12px;
            margin: 0 3px;
            border-radius: 5px;
        }

        /* Custom untuk hover */
        .page-item .page-link:hover {
            background-color: #28AE96;
            color: white;
        }

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
</head>

<body>

    <div class="container mt-5">
        <div class="card">
            <div class="card-body" style="background-color: #DCE8E6; font-family: 'Poppins', sans-serif;">
                <div class="text-center mb-4">
                    <h2>Data Keseluruhan Pasien</h2>
                </div>

                <!-- Alert Notifikasi Sukses dengan SweetAlert2 -->
                @if (session('success'))
                    <script>
                        Swal.fire({
                            title: 'Berhasil!',
                            text: "{{ session('success') }}",
                            icon: 'success',
                            confirmButtonText: 'OK'
                        });
                    </script>
                @endif

                @if (session('error'))
                    <script>
                        Swal.fire({
                            title: 'Gagal!',
                            text: "{{ session('error') }}",
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    </script>
                @endif

                <!-- Tampilkan tabel semua data pasien -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th scope="col">Nama</th>
                                <th scope="col">Jenis Kelamin</th>
                                <th scope="col">Umur</th>
                                <th scope="col">Diagnosa</th>
                                <th scope="col">Resep Obat</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($data_pasien as $pasien)
                                <tr>
                                    <td>{{ $pasien->nama }}</td>
                                    <td>{{ $pasien->gender }}</td>
                                    <td>{{ $pasien->umur }} tahun</td>
                                    <td>{{ $pasien->diagnosa }}</td>
                                    <td>
                                        @if ($pasien->resep_obat)
                                            {{ implode(', ', array_map('trim', explode(',', $pasien->resep_obat))) }}
                                        @else
                                            <p>Tidak ada rekomendasi obat untuk diagnosa ini.</p>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Tampilkan paginasi -->
                <div class="pagination-wrapper mt-3">
                    {{ $data_pasien->links('pagination::bootstrap-5') }}
                </div>

                <!-- Tombol kembali ke halaman utama -->
                <div class="text-center">
                    <a href="{{ url('/rekomendasi-obat') }}" class="btn btn-primary mt-4"
                        style="background-color: #28AE96;">Kembali ke Halaman Utama</a>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery harus dimuat sebelum Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</body>

</html>
