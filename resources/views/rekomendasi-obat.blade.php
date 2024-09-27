<!DOCTYPE html>
<html>

<head>
    <!-- Tambahkan CSS untuk jQuery UI -->
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <!-- Tambahkan jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Tambahkan jQuery UI -->
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pasien</title>

    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap JS -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

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

        .ui-autocomplete {
            z-index: 2147483647;
        }

        /* Custom styles for resep button */
        .resep-button {
            margin-top: 5px;
            margin-right: 5px;
        }

        .resep-container {
            display: flex;
            flex-wrap: wrap;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <div class="card">
            <div class="card-body" style="background-color: #DCE8E6; font-family: 'Poppins', sans-serif;">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="text-center mb-4">Data Pasien</h2>
                    <a href="{{ url('/input-pasien') }}" class="btn btn-primary"
                        style="background-color: #28AE96;">Input Data Pasien</a>
                </div>

                <!-- Alert Notifikasi Sukses -->
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                {{-- Data Pasien --}}
                <div class="table-responsive mt-2">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th scope="col">Nama</th>
                                <th scope="col">Jenis Kelamin</th>
                                <th scope="col">Umur</th>
                                <th scope="col">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (!empty($data_pasien) && count($data_pasien) > 0)
                                @foreach ($data_pasien as $pasien)
                                    <tr>
                                        <td>{{ $pasien['Nama'] }}</td>
                                        <td>{{ $pasien['Gender'] }}</td>
                                        <td>{{ $pasien['Umur'] }}</td>
                                        <td>
                                            <!-- Tombol untuk membuka modal -->
                                            <button class="btn btn-warning btn-sm" data-toggle="modal"
                                                data-target="#editModal-{{ $loop->index }}">
                                                <i class="fas fa-pen"></i> Diagnosa
                                            </button>

                                            <!-- Modal -->
                                            <div class="modal fade" id="editModal-{{ $loop->index }}" tabindex="-1"
                                                aria-labelledby="exampleModalLabel" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="exampleModalLabel">Edit Data
                                                                Pasien</h5>
                                                            <button type="button" class="close" data-dismiss="modal"
                                                                aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <!-- Form untuk mengedit dan menyimpan data pasien -->
                                                            <form id="form-edit-{{ $loop->index }}" method="POST"
                                                                action="/simpan-data">
                                                                @csrf <!-- Laravel CSRF token -->

                                                                <!-- Nama -->
                                                                <div class="form-group">
                                                                    <label for="nama">Nama</label>
                                                                    <input type="text" class="form-control"
                                                                        id="nama-{{ $loop->index }}" name="nama"
                                                                        value="{{ $pasien['Nama'] }}">
                                                                </div>

                                                                <!-- Jenis Kelamin -->
                                                                <div class="form-group">
                                                                    <label for="gender">Jenis Kelamin</label>
                                                                    <input type="text" class="form-control"
                                                                        id="gender-{{ $loop->index }}" name="gender"
                                                                        value="{{ $pasien['Gender'] }}">
                                                                </div>

                                                                <!-- Umur -->
                                                                <div class="form-group">
                                                                    <label for="umur">Umur</label>
                                                                    <input type="number" class="form-control"
                                                                        id="umur-{{ $loop->index }}" name="umur"
                                                                        value="{{ $pasien['Umur'] }}">
                                                                </div>

                                                                <!-- Diagnosis Autocomplete -->
                                                                <div class="form-group">
                                                                    <label for="diagnosa">Diagnosa</label>
                                                                    <input type="text"
                                                                        class="form-control diagnosa-autocomplete"
                                                                        id="diagnosa-{{ $loop->index }}"
                                                                        name="diagnosa"
                                                                        placeholder="Ketik diagnosis...">
                                                                    <!-- Tempat untuk pesan error -->
                                                                    <div id="diagnosa-error-{{ $loop->index }}"
                                                                        class="text-danger"
                                                                        style="display:none; font-size: 0.9em;"></div>
                                                                </div>


                                                                <!-- Rekomendasi Resep Obat -->
                                                                <div class="form-group">
                                                                    <label for="resep-obat">Resep Obat</label>
                                                                    <div id="resep-container-{{ $loop->index }}"
                                                                        class="resep-container">
                                                                        <!-- Rekomendasi obat akan ditampilkan sebagai tombol di sini -->
                                                                    </div>
                                                                </div>

                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary"
                                                                        data-dismiss="modal">Tutup</button>
                                                                    <button type="submit"
                                                                        class="btn btn-primary">Simpan</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="4" class="text-center">Tidak ada data pasien.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                <!-- Pagination controls -->
                <div class="pagination-wrapper mt-3 text-center" style="background-color: #DCE8E6;">
                    {{ $data_pasien->links('pagination::bootstrap-5') }}
                </div>

            </div>
        </div>

        {{-- JS Script --}}
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <script nonce="random-nonce-value">
            $(document).ready(function() {
                // Inisialisasi tooltip
                $('[data-toggle="tooltip"]').tooltip();

                // Pastikan autocomplete hanya dijalankan ketika modal dibuka
                $('.modal').on('shown.bs.modal', function(e) {
                    var modalId = $(this).attr('id');
                    var index = modalId.split('-')[1]; // Mendapatkan index dari modal

                    // Inisialisasi autocomplete untuk diagnosa
                    $("#diagnosa-" + index).autocomplete({
                        source: function(request, response) {
                            $.ajax({
                                url: "/api/diagnosis", // URL API untuk diagnosis
                                dataType: "json",
                                data: {
                                    q: request.term // Input yang diketik oleh pengguna
                                },
                                success: function(data) {
                                    if (data.error || data.length === 0) {
                                        // Jika diagnosis tidak ditemukan, tampilkan pesan error
                                        $("#diagnosa-error-" + index).text(
                                            "Diagnosis tidak ditemukan dalam dataset"
                                            ).show();
                                        response([]); // Kosongkan pilihan autocomplete
                                    } else {
                                        console.log("Diagnosis data fetched: ", data); // Debug respons dari server
                                        $("#diagnosa-error-" + index).hide
                                        $("#diagnosa-error-" + index).hide(); // Sembunyikan pesan error jika diagnosis ditemukan
                                        $("#resep-container-" + index).empty(); // Kosongkan kontainer resep obat saat diagnosis diubah
                                        response(data); // Kirim data diagnosis ke autocomplete
                                    }
                                },
                                error: function(xhr, status, error) {
                                    console.error("Error fetching diagnosis:", error); // Tampilkan error jika ada
                                    $("#diagnosa-error-" + index).text(
                                            "Terjadi kesalahan dalam memuat diagnosis"
                                        ).show();
                                }
                            });
                        },
                        minLength: 1, // Minimal karakter yang harus diketik sebelum autocomplete muncul
                        select: function(event, ui) {
                            var diagnosis = ui.item.value;
                            fetchResepObat(diagnosis, index); // Memanggil fungsi untuk mendapatkan resep obat
                        },
                        response: function(event, ui) {
                            // Jika tidak ada hasil, tampilkan pesan peringatan
                            if (ui.content.length === 0) {
                                $("#diagnosa-error-" + index).text(
                                    "Diagnosis tidak ditemukan dalam dataset"
                                ).show();
                                $("#resep-container-" + index).empty(); // Kosongkan kontainer resep obat jika diagnosis tidak ditemukan
                            }
                        }
                    });

                    // Fungsi untuk mendapatkan rekomendasi obat
                    function fetchResepObat(diagnosis, index) {
                        $.ajax({
                            url: "/api/rekomendasi-obat", // URL Laravel untuk mengambil rekomendasi obat
                            method: "POST",
                            contentType: "application/json",
                            data: JSON.stringify({
                                diagnosis: diagnosis
                            }),
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(response) {
                                if (response.error) {
                                    alert(response.error); // Tampilkan error jika ada
                                } else if (response["Resep Obat"]) {
                                    $("#resep-container-" + index).empty(); // Kosongkan kontainer sebelum menambah rekomendasi baru

                                    // Tampilkan rekomendasi obat sebagai tombol
                                    response["Resep Obat"].forEach(function(obat, idx) {
                                        var btnHtml = `
                                        <button type="button" class="btn btn-info btn-sm resep-button" id="resep-${index}-${idx}">
                                            ${obat}
                                        </button>`;
                                        $("#resep-container-" + index).append(btnHtml);
                                    });

                                    // Bind event handler untuk menghapus tombol resep obat jika diklik
                                    bindResepButtonHandler(index);
                                } else {
                                    $("#resep-container-" + index).empty().append(
                                        "<p>Diagnosis tidak ada dalam dataset.</p>");
                                }
                            },
                            error: function(xhr, status, error) {
                                console.log("Error fetching recommendation:", error);
                            }
                        });
                    }

                    // Fungsi untuk menghapus tombol resep obat
                    function bindResepButtonHandler(index) {
                        // Pastikan semua tombol resep dapat dihapus ketika diklik
                        $("#resep-container-" + index).off("click", ".resep-button").on("click", ".resep-button", function() {
                            $(this).remove(); // Menghapus tombol resep obat yang diklik
                        });
                    }

                    $(".modal form").off('submit').on('submit', function() {
                        var selectedObat = [];
                        $("#resep-container-" + index + " .resep-button").each(function() {
                            var text = $(this).text().trim(); // Mengambil teks dari tombol resep
                            if (text) { // Hanya masukkan jika teks tidak kosong
                                selectedObat.push(text);
                            }
                        });

                        // Tambahkan hidden input untuk mengirimkan resep obat sebagai teks
                        $('<input>').attr({
                            type: 'hidden',
                            name: 'resep_obat',
                            value: selectedObat.join(', ') // Gabungkan resep menjadi satu string, hanya jika ada elemen valid
                        }).appendTo(this);
                    });
                });
            });
        </script>

    </div>
</body>

</html>
