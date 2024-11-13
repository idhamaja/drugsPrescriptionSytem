<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pengelompokan Resep Obat</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .pagination {
            font-size: 1rem;
            margin-top: 20px;
        }

        .page-item .page-link {
            padding: 8px 12px;
            margin: 0 3px;
            border-radius: 5px;
        }

        .page-item .page-link:hover {
            background-color: #28AE96;
            color: white;
        }

        body {
            background: rgba(0, 0, 0, 0.7) url('{{ asset('images/HOME2.png') }}') center/cover no-repeat fixed;
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <div class="card">
            <div class="card-body" style="background-color: #DCE8E6;">
                <div class="text-center mb-4">
                    <h2>Data Pengelompokkan Resep Obat</h2>
                </div>
                <!-- Tombol kembali ke halaman utama -->
                <div class="text-center">
                    <a href="{{ url('/rekomendasi-obat') }}" class="btn btn-primary mt-4" style="background-color: #28AE96;">Kembali ke Beranda</a>
                </div>
                <div class="container mt-5">
                    <div class="text-center">
                        <h4>Pengelompokan Resep Obat Berdasarkan Jenis Kelamin dan Umur</h4>
                    </div>
                    <!-- Tabel dengan kelas Bootstrap 5 terbaru -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover align-middle" id="pengelompokan-resep-obat-table">
                            <thead class="table-dark">
                                <tr>
                                    <th>Jenis Kelamin</th>
                                    <th>Kategori Umur</th>
                                    <th>Resep Obat</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
                <script>
                    $(document).ready(function() {
                        function fetchPengelompokanResepObat() {
                            $.ajax({
                                url: "http://127.0.0.1:5000/api/pengelompokan_resep_obat", // URL Flask API
                                method: "GET",
                                success: function(data) {
                                    if (data.error) {
                                        console.error(data.error);
                                    } else {
                                        displayPengelompokanResepObat(data);
                                    }
                                },
                                error: function(xhr, status, error) {
                                    console.error("Error fetching grouped data:", error);
                                }
                            });
                        }

                        function displayPengelompokanResepObat(data) {
                            var tableContent = "";
                            data.forEach(function(item) {
                                tableContent +=
                                    `<tr><td>${item.Gender}</td><td>${item['Kategori Umur']}</td><td>${item['Resep Obat']}</td></tr>`;
                            });
                            $("#pengelompokan-resep-obat-table tbody").html(tableContent);
                        }
                        fetchPengelompokanResepObat();
                    });
                </script>
            </div>
        </div>
    </div>
    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
