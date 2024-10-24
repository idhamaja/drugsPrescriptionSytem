<!DOCTYPE html>
<html>

<head>
    <!-- Tambahkan CSS untuk jQuery UI -->
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <!-- Tambahkan jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Tambahkan jQuery UI -->
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <!-- Socket.IO -->
    <script src="https://cdn.socket.io/4.0.0/socket.io.min.js"></script>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pasien</title>

    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap JS -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

    <!-- Tambahkan Socket.IO -->
    <script src="https://cdn.socket.io/4.0.0/socket.io.min.js"></script>

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
                    <a href="http://127.0.0.1:8000/hasil-rekomendasi/3" class="btn btn-primary"
                        style="background-color: #28AE96;">Hasil Rekomendasi</a>

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
                    <table class="table table-striped table-hover" id="patient-table">
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
                                    <tr id="row-{{ $loop->index }}">
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

                                                                <!-- Tempat untuk menampilkan perhitungan metode content-based filtering -->
                                                                <div class="form-group content-filtering-result mt-4">
                                                                    <h5>Hasil Perhitungan:</h5>
                                                                    <div id="content-filtering-{{ $loop->index }}"
                                                                        style="padding: 10px; background-color: #f7f7f7; border: 1px solid #ddd;">
                                                                        <!-- Perhitungan akan ditampilkan di sini -->
                                                                    </div>
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
                // Menghubungkan ke server SocketIO
                var socket = io.connect('http://127.0.0.1:5000'); // Ganti dengan URL server Flask Anda

                // Mendengarkan event 'patient_deleted' dari backend
                socket.on('patient_deleted', function(data) {
                    alert("Pasien dengan nama " + data.nama + " telah dihapus.");
                    // Hapus baris pasien dari tabel berdasarkan nama pasien
                    $("tr:contains('" + data.nama + "')").remove();
                });

                // Inisialisasi autocomplete untuk diagnosa
                $('.modal').on('shown.bs.modal', function(e) {
                    var modalId = $(this).attr('id');
                    var index = modalId.split('-')[1]; // Mendapatkan index dari modal

                    // Autocomplete untuk diagnosa
                    $("#diagnosa-" + index).autocomplete({
                        source: function(request, response) {
                            $.ajax({
                                url: "/api/diagnosis", // URL API untuk diagnosis
                                dataType: "json",
                                data: {
                                    q: request.term
                                },
                                success: function(data) {
                                    if (data.error || data.length === 0) {
                                        $("#diagnosa-error-" + index).text(
                                            "Diagnosis tidak ditemukan dalam dataset"
                                        ).show();
                                        response([]);
                                    } else {
                                        $("#diagnosa-error-" + index).hide();
                                        response(data);
                                    }
                                },
                                error: function(xhr, status, error) {
                                    console.error("Error fetching diagnosis:", error);
                                    $("#diagnosa-error-" + index).text(
                                            "Terjadi kesalahan dalam memuat diagnosis")
                                        .show();
                                }
                            });
                        },
                        minLength: 1,
                        select: function(event, ui) {
                            var diagnosis = ui.item.value;
                            fetchResepObat(diagnosis, index);
                            fetchContentFiltering(diagnosis,
                                index
                            ); // Panggil fungsi untuk menampilkan hasil content-based filtering
                        }
                    });

                    // Fungsi untuk mendapatkan rekomendasi obat
                    function fetchResepObat(diagnosis, index) {
                        $.ajax({
                            url: "/api/rekomendasi-obat",
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
                                    alert(response.error);
                                } else if (response["Resep Obat"]) {
                                    $("#resep-container-" + index).empty();
                                    response["Resep Obat"].forEach(function(obat, idx) {
                                        var btnHtml =
                                            `<button type="button" class="btn btn-info btn-sm resep-button" id="resep-${index}-${idx}">${obat}</button>`;
                                        $("#resep-container-" + index).append(btnHtml);
                                    });
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

                    // Ambil CSRF token dari meta tag
                    var csrfToken = $('meta[name="csrf-token"]').attr('content');

                    // Pastikan setiap request POST menyertakan CSRF token
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        }
                    });

                    // Fungsi untuk mendapatkan hasil perhitungan content-based filtering
                    function fetchContentFiltering(diagnosis, index) {
                        console.log("Sending diagnosis:", diagnosis); // Log diagnosis yang dikirim

                        $.ajax({
                            url: "/api/cbf", // URL menuju route Laravel
                            method: "POST", // Pastikan method di sini adalah POST
                            contentType: "application/json",
                            headers: {
                                'X-CSRF-TOKEN': csrfToken // Sertakan CSRF token di header
                            },
                            data: JSON.stringify({
                                diagnosis: diagnosis // Diagnosis yang dikirim
                            }),
                            success: function(response) {
                                console.log("Received response:",
                                    response); // Log response dari backend
                                if (response.error) {
                                    $("#content-filtering-" + index).html(
                                        `<p class="text-danger">${response.error}</p>`
                                    );
                                } else {
                                    let hasil = `
                    <p><strong>Diagnosis Input:</strong> ${response.diagnosis}</p>
                    <p><strong>Cosine Similarity:</strong> ${response.cosine_similarity}</p>
                    <p><strong>Top Matches:</strong> ${response.top_matches.join(', ')}</p>
                `;
                                    $("#content-filtering-" + index).html(hasil);
                                }
                            },
                            error: function(xhr, status, error) {
                                console.log("Error response:", xhr
                                    .responseText); // Log jika terjadi error
                                $("#content-filtering-" + index).html(
                                    "<p class='text-danger'>Error fetching content-based filtering results.</p>"
                                );
                            }
                        });
                    }


                    // Bind event handler untuk menghapus tombol resep obat jika diklik
                    function bindResepButtonHandler(index) {
                        $("#resep-container-" + index).off("click", ".resep-button").on("click",
                            ".resep-button",
                            function() {
                                $(this).remove();
                            });
                    }

                    // Ketika form disubmit
                    $(".modal form").off('submit').on('submit', function() {
                        var selectedObat = [];
                        $("#resep-container-" + index + " .resep-button").each(function() {
                            var text = $(this).text().trim();
                            if (text) {
                                selectedObat.push(text);
                            }
                        });

                        // Menambahkan hidden input untuk mengirimkan resep obat
                        $('<input>').attr({
                            type: 'hidden',
                            name: 'resep_obat',
                            value: selectedObat.join(', ')
                        }).appendTo(this);
                    });
                });
            });
        </script>
    </div>
</body>

</html>
