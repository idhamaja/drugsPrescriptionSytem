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

    <!-- Font Awesome untuk ikon -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

    <!-- Tambahkan Socket.IO -->
    <script src="https://cdn.socket.io/4.0.0/socket.io.min.js"></script>

    <meta name="csrf-token" content="{{ csrf_token() }}">
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

        .modal-lg .modal-content {
            max-width: 900px;
            /* Menambah lebar maksimum modal */
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

        .disabled-button {
            pointer-events: none;
            opacity: 0.5;
        }

        .resep-button .fa-trash {
            margin-left: 5px;
            cursor: pointer;
        }

        .alert {
            margin-top: 20px;
            font-size: 1rem;
        }

        .btn-group a {
            margin-right: 10px;
            /* Memberikan jarak antar tombol */
        }


        pre {
            background-color: #eef;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table th,
        table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
    </style>
</head>

<body>
    <div class="container mt-5">

        <div class="card">

            <div class="card-body" style="background-color: #DCE8E6; font-family: 'Poppins', sans-serif;">
                <h2 class="text-center mb-4">Data Pasien</h2>
                <div class="d-flex flex-wrap justify-content-between align-items-center">
                    <!-- Tombol navigasi dalam grup -->
                    <div class="btn-group" role="group" style="gap: 10px;"> <!-- Menambahkan jarak antar tombol -->
                        <a href="{{ url('/dashboard') }}" class="btn btn-primary"
                            style="background-color: #28AE96;">Dashboard</a>
                        <a href="{{ url('/input-pasien') }}" class="btn btn-primary"
                            style="background-color: #28AE96;">Input Data Pasien</a>
                        <a href="http://127.0.0.1:8000/hasil-rekomendasi/3" class="btn btn-primary"
                            style="background-color: #28AE96;">Hasil Rekomendasi</a>
                        <a href="{{ url('/hasil-pengelompokan') }}" class="btn btn-primary"
                            style="background-color: #28AE96;">Hasil Pengelompokkan</a>
                    </div>


                    <!-- Tombol Logout -->
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="ml-auto">
                        @csrf
                        <button type="submit" class="btn btn-primary"
                            style="background-color: #28AE96; border: none;">Logout</button>
                    </form>

                    <!-- Input Pencarian -->
                    <div class="ml-2">
                        <input type="text" id="search-bar" class="form-control" style="width: 200px;"
                            placeholder="Cari nama pasien...">
                    </div>
                </div>

                <!-- Alert Container -->
                <div class="alert-container">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <strong>Sukses!</strong> {{ session('success') }}

                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-times-circle me-2"></i>
                            <strong>Kesalahan!</strong> {{ session('error') }}

                        </div>
                    @endif
                </div>
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
                                                <div class="modal-dialog modal-lg">
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
                                                                        value="{{ $pasien['Nama'] }}" required>
                                                                </div>

                                                                <!-- Jenis Kelamin -->
                                                                <div class="form-group">
                                                                    <label for="gender">Jenis Kelamin</label>
                                                                    <input type="text" class="form-control"
                                                                        id="gender-{{ $loop->index }}" name="gender"
                                                                        value="{{ $pasien['Gender'] }}" required>
                                                                </div>

                                                                <!-- Umur -->
                                                                <div class="form-group">
                                                                    <label for="umur">Umur</label>
                                                                    <input type="number" class="form-control"
                                                                        id="umur-{{ $loop->index }}" name="umur"
                                                                        value="{{ $pasien['Umur'] }}" required>
                                                                </div>

                                                                <!-- Diagnosis -->
                                                                <div class="form-group">
                                                                    <label for="diagnosa">Diagnosa</label>
                                                                    <input type="text"
                                                                        class="form-control diagnosa-autocomplete"
                                                                        id="diagnosa-{{ $loop->index }}"
                                                                        name="diagnosa"
                                                                        placeholder="Ketik diagnosis..." required>
                                                                    <!-- Tempat untuk pesan error -->
                                                                    <div id="diagnosa-error-{{ $loop->index }}"
                                                                        class="text-danger"
                                                                        style="display:none; font-size: 0.9em;"></div>
                                                                </div>

                                                                <!-- Penjelasan metode -->
                                                                <div class="mt-4">
                                                                    <h5>Langkah-Langkah Metode Content-Based Filtering
                                                                    </h5>

                                                                    <h5>Rumus TF-IDF dan Cosine Similarity</h5>

                                                                    <p>Berikut adalah representasi rumus TF-IDF:</p>
                                                                    <math xmlns="http://www.w3.org/1998/Math/MathML"
                                                                        display="block">
                                                                        <mrow>
                                                                            <mi>TF-IDF</mi>
                                                                            <mo>(</mo>
                                                                            <mi>t</mi>
                                                                            <mo>,</mo>
                                                                            <mi>d</mi>
                                                                            <mo>)</mo>
                                                                            <mo>=</mo>
                                                                            <mi>TF</mi>
                                                                            <mo>(</mo>
                                                                            <mi>t</mi>
                                                                            <mo>,</mo>
                                                                            <mi>d</mi>
                                                                            <mo>)</mo>
                                                                            <mo>&#x00D7;</mo>
                                                                            <mi>IDF</mi>
                                                                            <mo>(</mo>
                                                                            <mi>t</mi>
                                                                            <mo>)</mo>
                                                                        </mrow>
                                                                    </math>

                                                                    <h5>Detail Komponen</h5>
                                                                    <p><strong>1. Term Frequency (TF):</strong></p>
                                                                    <math xmlns="http://www.w3.org/1998/Math/MathML"
                                                                        display="block">
                                                                        <mrow>
                                                                            <mi>TF</mi>
                                                                            <mo>(</mo>
                                                                            <mi>t</mi>
                                                                            <mo>,</mo>
                                                                            <mi>d</mi>
                                                                            <mo>)</mo>
                                                                            <mo>=</mo>
                                                                            <mfrac>
                                                                                <mrow>
                                                                                    <mi>Frekuensi</mi>
                                                                                    <mo>&nbsp;</mo>
                                                                                    <mi>kata</mi>
                                                                                    <mo>&nbsp;</mo>
                                                                                    <mi>t</mi>
                                                                                    <mo>&nbsp;</mo>
                                                                                    <mi>dalam</mi>
                                                                                    <mo>&nbsp;</mo>
                                                                                    <mi>dokumen</mi>
                                                                                    <mo>&nbsp;</mo>
                                                                                    <mi>d</mi>
                                                                                </mrow>
                                                                                <mrow>
                                                                                    <mi>Total</mi>
                                                                                    <mo>&nbsp;</mo>
                                                                                    <mi>kata</mi>
                                                                                    <mo>&nbsp;</mo>
                                                                                    <mi>dalam</mi>
                                                                                    <mo>&nbsp;</mo>
                                                                                    <mi>dokumen</mi>
                                                                                    <mo>&nbsp;</mo>
                                                                                    <mi>d</mi>
                                                                                </mrow>
                                                                            </mfrac>
                                                                        </mrow>
                                                                    </math>

                                                                    <p><strong>2. Inverse Document Frequency
                                                                            (IDF)
                                                                            :</strong></p>
                                                                    <math xmlns="http://www.w3.org/1998/Math/MathML"
                                                                        display="block">
                                                                        <mrow>
                                                                            <mi>IDF</mi>
                                                                            <mo>(</mo>
                                                                            <mi>t</mi>
                                                                            <mo>)</mo>
                                                                            <mo>=</mo>
                                                                            <mi>log</mi>
                                                                            <mo>(</mo>
                                                                            <mfrac>
                                                                                <mrow>
                                                                                    <mi>N</mi>
                                                                                </mrow>
                                                                                <mrow>
                                                                                    <mn>1</mn>
                                                                                    <mo>+</mo>
                                                                                    <mi>DF</mi>
                                                                                    <mo>(</mo>
                                                                                    <mi>t</mi>
                                                                                    <mo>)</mo>
                                                                                </mrow>
                                                                            </mfrac>
                                                                            <mo>)</mo>
                                                                        </mrow>
                                                                    </math>

                                                                    <h5>Keterangan:</h5>
                                                                    <ul>
                                                                        <li><strong>N:</strong> Jumlah total dokumen.
                                                                        </li>
                                                                        <li><strong>DF(t):</strong> Jumlah dokumen yang
                                                                            mengandung istilah <em>t</em>.</li>
                                                                    </ul>

                                                                    <h5>Cosine Similarity Formula</h5>
                                                                    <math xmlns="http://www.w3.org/1998/Math/MathML">
                                                                        <mrow>
                                                                            <mi>Cosine Similarity</mi>
                                                                            <mo>(</mo>
                                                                            <mi>A</mi>
                                                                            <mo>,</mo>
                                                                            <mi>B</mi>
                                                                            <mo>)</mo>
                                                                            <mo>=</mo>
                                                                            <mfrac>
                                                                                <mrow>
                                                                                    <mi>A</mi>
                                                                                    <mo>&#x22C5;</mo>
                                                                                    <mi>B</mi>
                                                                                </mrow>
                                                                                <mrow>
                                                                                    <mo>&#x2225;</mo>
                                                                                    <mi>A</mi>
                                                                                    <mo>&#x2225;</mo>
                                                                                    <mo>&#x00D7;</mo>
                                                                                    <mo>&#x2225;</mo>
                                                                                    <mi>B</mi>
                                                                                    <mo>&#x2225;</mo>
                                                                                </mrow>
                                                                            </mfrac>
                                                                        </mrow>
                                                                    </math>

                                                                    <p><strong>Keterangan:</strong></p>
                                                                    <ul>
                                                                        <li><em>A</em> dan <em>B</em>: Vektor TF-IDF dua
                                                                            dokumen.</li>
                                                                        <li><em>A ⋅ B</em>: Hasil dot product antara
                                                                            vektor <em>A</em> dan <em>B</em>.</li>
                                                                        <li><em>||A||</em>: Panjang atau norma dari
                                                                            vektor <em>A</em>.</li>
                                                                    </ul>

                                                                    <h5>Langkah-Langkah Implementasi</h5>
                                                                    <ol>
                                                                        <li><strong>Data Input:</strong> Pengguna
                                                                            memasukkan teks diagnosis, misalnya "demam
                                                                            tinggi".</li>
                                                                        <li><strong>Preprocessing Data:</strong>
                                                                            <ul>
                                                                                <li>Ubah teks menjadi lowercase.</li>
                                                                                <li>Hilangkan karakter kosong di
                                                                                    awal/akhir teks.</li>
                                                                            </ul>
                                                                        </li>
                                                                        <li><strong>Representasi Data dengan
                                                                                TF-IDF:</strong> Diagnosis dalam dataset
                                                                            diubah menjadi vektor numerik.</li>
                                                                        <li><strong>Normalisasi Vektor:</strong> Semua
                                                                            vektor dinormalisasi agar panjangnya 1.</li>
                                                                        <li><strong>Menghitung Cosine
                                                                                Similarity:</strong> Hitung tingkat
                                                                            kemiripan antara diagnosis input dan
                                                                            dataset.</li>
                                                                        <li><strong>Seleksi dan Threshold:</strong>
                                                                            Diagnosis dengan kemiripan di atas ambang
                                                                            batas (misalnya 0.5) dipilih.</li>
                                                                        <li><strong>Rekomendasi Obat:</strong> Ambil
                                                                            resep obat terkait diagnosis yang relevan.
                                                                        </li>
                                                                    </ol>


                                                                    <h5>Input Diagnosis:</h5>
                                                                    <p><strong>Demam tinggi disertai sakit
                                                                            kepala</strong></p>

                                                                    <h5>Langkah-Langkah Perhitungan:</h5>
                                                                    <table>
                                                                        <thead>
                                                                            <tr>
                                                                                <th>Diagnosis</th>
                                                                                <th>TF-IDF Representasi</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            <tr>
                                                                                <td>Demam tinggi</td>
                                                                                <td>[0.9, 0.1, 0.0]</td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Sakit kepala</td>
                                                                                <td>[0.2, 0.8, 0.1]</td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Demam berdarah</td>
                                                                                <td>[0.6, 0.0, 0.9]</td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>

                                                                    <h5>Cosine Similarity Calculation:</h5>
                                                                    <table>
                                                                        <thead>
                                                                            <tr>
                                                                                <th>Diagnosis</th>
                                                                                <th>Cosine Similarity</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            <tr>
                                                                                <td>Demam tinggi</td>
                                                                                <td>0.85</td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Sakit kepala</td>
                                                                                <td>0.57</td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Demam berdarah</td>
                                                                                <td>0.48</td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>

                                                                    <h5>Hasil Seleksi:</h5>
                                                                    <p>Diagnosis dengan Cosine Similarity > 0.5:</p>
                                                                    <ul>
                                                                        <li>Demam tinggi (0.85)</li>
                                                                        <li>Sakit kepala (0.57)</li>
                                                                    </ul>

                                                                    <h5>Rekomendasi Obat:</h5>
                                                                    <ul>
                                                                        <li>Demam tinggi: Paracetamol</li>
                                                                        <li>Sakit kepala: Ibuprofen</li>
                                                                    </ul>

                                                                    <h5>Hasil Akhir:</h5>
                                                                    <p><strong>Rekomendasi obat:</strong> Paracetamol,
                                                                        Ibuprofen</p>
                                                                </div>


                                                                <!-- Tempat untuk menampilkan perhitungan metode content-based filtering -->
                                                                <div class="form-group content-filtering-result mt-4">
                                                                    <h5>Hasil Perhitungan:</h5>
                                                                    <div id="content-filtering-{{ $loop->index }}"
                                                                        style="padding: 10px; background-color: #f7f7f7; border: 1px solid #ddd;">
                                                                        <!-- Hasil perhitungan akan ditampilkan di sini -->
                                                                    </div>
                                                                    <div id="detail-perhitungan-{{ $loop->index }}"
                                                                        style="padding: 10px; margin-top: 10px; background-color: #eef6f6; border: 1px solid #ddd;">
                                                                        <!-- Detail perhitungan TF-IDF dan Cosine Similarity akan ditampilkan di sini -->
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
            {{-- </div> --}}

            {{-- JS Script --}}
            <meta name="csrf-token" content="{{ csrf_token() }}">

            <script>
                $(document).ready(function() {
                    // Fitur pencarian
                    $("#search-bar").on("keyup", function() {
                        var value = $(this).val().toLowerCase(); // Ambil input pencarian
                        $("#patient-table tbody tr").filter(function() {
                            // Tampilkan atau sembunyikan baris berdasarkan pencarian
                            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
                        });
                    });
                });
            </script>


            <script nonce="random-nonce-value">
                $(document).ready(function() {
                    // Menghubungkan ke server SocketIO
                    var socket = io.connect('http://127.0.0.1:5000'); // Ganti dengan URL server Flask Anda

                    $(document).ready(function() {
                        var socket = io.connect('http://127.0.0.1:5000'); // Sesuaikan URL server Flask Anda

                        // Mendengarkan event 'new_pasien' dari server Flask
                        socket.on('new_pasien', function(data) {
                            // Tambahkan data pasien baru ke tabel secara otomatis
                            var newRow = `
            <tr>
                <td>${data.Nama}</td>
                <td>${data.Gender}</td>
                <td>${data.Umur}</td>
                <td>
                    <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editModal">
                        <i class="fas fa-pen"></i> Diagnosa
                    </button>
                </td>
            </tr>
        `;
                            $('#patient-table tbody').append(newRow);
                        });
                    });


                    // Mendengarkan event 'patient_deleted' dari backend
                    socket.on('patient_deleted', function(data) {
                        alert("Pasien dengan nama " + data.nama + " telah dihapus.");
                        // Hapus baris pasien dari tabel berdasarkan nama pasien
                        $("tr:contains('" + data.nama + "')").remove();
                    });

                    // Mendengarkan event 'diagnosis_deleted' untuk memperbarui autocomplete
                    socket.on('diagnosis_deleted', function(data) {
                        alert("Diagnosis '" + data.diagnosis + "' telah dihapus.");

                        // Memperbarui autocomplete dengan data diagnosis yang baru
                        updateAutocompleteSource();
                    });

                    // Fungsi untuk memperbarui sumber data autocomplete
                    function updateAutocompleteSource() {
                        $.ajax({
                            url: "/api/diagnosis", // URL API untuk mendapatkan diagnosis terbaru
                            method: "GET",
                            success: function(data) {
                                if (!data.error) {
                                    // Perbarui autocomplete dengan diagnosis yang terbaru
                                    $(".diagnosa-autocomplete").autocomplete("option", "source", data);
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error("Error updating autocomplete source:", error);
                            }
                        });
                    }

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
                                            var btnHtml = `
            <button type="button" class="btn btn-info btn-sm resep-button" id="resep-${index}-${idx}">
                ${obat} <i class="fas fa-trash text-danger ml-2"></i>
            </button>`;
                                            $("#resep-container-" + index).append(btnHtml);
                                        });

                                        // Memasukkan semua resep obat yang diterima pada hidden input
                                        $("#form-edit-" + index).find("input[name='resep_obat']")
                                            .remove();
                                        $('<input>').attr({
                                            type: 'hidden',
                                            name: 'resep_obat',
                                            value: response["Resep Obat"].join(', ')
                                        }).appendTo("#form-edit-" + index);

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
                            $.ajax({
                                url: "/api/cbf", // Endpoint untuk perhitungan CBF
                                method: "POST",
                                contentType: "application/json",
                                data: JSON.stringify({
                                    diagnosis: diagnosis
                                }),
                                success: function(response) {
                                    if (response.error) {
                                        $("#content-filtering-" + index).html(
                                            `<p class="text-danger">${response.error}</p>`);
                                    } else {
                                        // Tampilkan hasil utama
                                        let hasil = `
                    <p><strong>Diagnosis Input:</strong> ${response.diagnosis}</p>
                    <p><strong>Top Matches:</strong> ${response.top_matches.join(', ')}</p>
                    <p><strong>Cosine Similarity:</strong> ${response.cosine_similarity.toFixed(3)}</p>
                `;
                                        $("#content-filtering-" + index).html(hasil);

                                        // Tampilkan detail perhitungan
                                        let detailPerhitungan = `
                    <h6><strong>Detail Perhitungan:</strong></h6>
                    <p><strong>Cosine Similarity Scores:</strong></p>
                    <pre>${JSON.stringify(response.cosine_similarity_scores, null, 2)}</pre>
                `;
                                        $("#detail-perhitungan-" + index).html(detailPerhitungan);
                                    }
                                },
                                error: function(xhr, status, error) {
                                    console.error("Error fetching CBF results:", error);
                                    $("#content-filtering-" + index).html(
                                        `<p class="text-danger">Error fetching content-based filtering results.</p>`
                                    );
                                }
                            });
                        }
                        // Bind event handler untuk menghapus tombol resep obat jika diklik
                        function bindResepButtonHandler(index) {
                            $("#resep-container-" + index).off("click", ".resep-button").on("click",
                                ".resep-button",
                                function(e) {
                                    var button = $(this);

                                    // Jika ikon trash (di dalam tombol) diklik
                                    if ($(e.target).hasClass('fa-trash')) {
                                        button.remove(); // Hapus tombol sepenuhnya
                                        return;
                                    }


                                });
                        }


                        // Ketika form disubmit
                        $(".modal form").off('submit').on('submit', function() {
                            var selectedObat = [];
                            $("#resep-container-" + index + " .resep-button:not(.removed)").each(
                                function() {
                                    var text = $(this).text().trim();
                                    if (text) {
                                        selectedObat.push(text);
                                    }
                                });
                            // Menghapus dan menambahkan hidden input untuk resep obat yang dipilih
                            $(this).find("input[name='resep_obat']").remove();
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
        <!-- Bootstrap JS (untuk dismissible alert) -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
