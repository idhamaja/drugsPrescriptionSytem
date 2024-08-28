<!DOCTYPE html>
<html>

<head>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <!-- Tambahkan CSS untuk jQuery UI -->
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <!-- Tambahkan jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Tambahkan jQuery UI -->
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekomendasi Obat</title>

    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            background-image: url('{{ asset('images/HOME2.png') }}');
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <div class="card">
            <div class="card-body" style="background-color: #DCE8E6; font-family: 'Poppins', sans-serif;">
                <h2 class="text-center mb-4">Drugs Prescription Sytem</h2>
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Data Pasien</h5>
                                <p class="card-text">Nama: {{ $patient->nama }}</p>
                                <p class="card-text">Jenis Kelamin: {{ $patient->jenis_kelamin }}</p>
                                <p class="card-text">Umur: {{ $patient->umur }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 text-right">
                        <a href="/input-pasien" class="btn"
                            style="background-color: #28AE96; color: white; font-family: 'Poppins', sans-serif;">Kembali
                            ke Input Pasien</a>
                    </div>
                </div>
                <div style="margin-top: 20px;"></div>

                {{-- Diagnosis --}}
                <div class="mb-3">
                    <label for="diagnosis" class="form-label">Diagnosis</label>
                    <input type="text" class="form-control" id="diagnosis" name="diagnosis"
                        value="{{ old('diagnosis') }}" required>
                    @error('diagnosis')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>


                {{-- Resep Obat --}}
                <div class="mb-3">
                    <label for="resep-obat" class="form-label">Resep Obat</label>
                    <input type="text" class="form-control" id="resep-obat" name="resep_obat">
                </div>

                <button type="button" id="add-to-table" class="btn"
                    style="background-color: #28AE96; color: white;">Tambahkan ke Tabel</button>

                {{-- Data Resep Obat --}}
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <h3>Data Resep Obat</h3>
                    <button type="button" id="save-data" class="btn btn-normal"
                        style="background-color: #28AE96; color: white;">Simpan Data</button>
                </div>

                <div class="table-responsive mt-2">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th scope="col">Diagnosis</th>
                                <th scope="col">Resep Obat</th>
                                <th scope="col">Aksi</th> <!-- Kolom baru untuk tombol delete -->
                            </tr>
                        </thead>
                        <tbody id="data-table-body">
                        </tbody>
                    </table>
                </div>


                <!-- Pagination controls -->
                <div class="pagination-wrapper mt-3 text-center">
                    <button class="btn btn-outline-secondary" id="prev-page">&lt;</button>
                    <span id="page-info">Page 1 of 1</span>
                    <button class="btn btn-outline-secondary" id="next-page">&gt;</button>
                </div>

                {{-- SCRIPT JS --}}
                <script nonce="random-nonce-value">
                    $(function() {
                        // Event listener untuk autocomplete diagnosis
                        $("#diagnosis").autocomplete({
                            source: function(request, response) {
                                $.ajax({
                                    url: "http://127.0.0.1:5000/api/diagnosis",
                                    dataType: "json",
                                    data: {
                                        q: request.term
                                    },
                                    success: function(data) {
                                        response(data);
                                    }
                                });
                            },
                            minLength: 2,
                            select: function(event, ui) {
                                fetchResepByDiagnosis(ui.item.value);
                            }
                        });

                        function fetchResepByDiagnosis(diagnosis) {
                            $.ajax({
                                url: "http://127.0.0.1:5000/api/resep-by-diagnosis",
                                dataType: "json",
                                data: {
                                    diagnosis: diagnosis
                                },
                                success: function(data) {
                                    if (data.length > 0) {
                                        $("#resep-obat").val(data.join(", "));
                                    } else {
                                        $("#resep-obat").val("Tidak ada resep untuk diagnosis ini");
                                    }
                                },
                                error: function(xhr, status, error) {
                                    console.error("AJAX error: ", error);
                                    $("#resep-obat").val("Terjadi kesalahan saat mengambil data resep obat");
                                }
                            });
                        }

                        $("#add-to-table").click(function() {
                            var diagnosis = $("#diagnosis").val();
                            var resepObat = $("#resep-obat").val();

                            if (diagnosis && resepObat) {
                                var newRow = `<tr>
            <td>${diagnosis}</td>
            <td>${resepObat}</td>
            <td>
                <button type="button" class="btn btn-danger btn-sm delete-row" style="display: flex; align-items: center;"><i class="material-icons" style="margin-right: 5px;">delete</i>Delete</button>
            </td>
        </tr>`;
                                $("#data-table-body").append(newRow);
                                $("#diagnosis").val('');
                                $("#resep-obat").val('');
                            } else {
                                alert("Harap isi diagnosis dan resep obat terlebih dahulu.");
                            }
                        });

                        // Event listener untuk tombol "Delete"
                        $(document).on('click', '.delete-row', function() {
                            $(this).closest('tr').remove();
                        });

                        $("#save-data").click(function() {
                            var dataToSave = [];

                            $("#data-table-body tr").each(function() {
                                var row = {
                                    diagnosis: $(this).find('td').eq(0).text(),
                                    resep_obat: $(this).find('td').eq(1).text()
                                };
                                dataToSave.push(row);
                            });

                            console.log("Data yang akan dikirim:", dataToSave);

                            $.ajax({
                                url: "{{ route('save-diagnosis-resep') }}",
                                method: "POST",
                                data: {
                                    _token: "{{ csrf_token() }}",
                                    data: dataToSave
                                },
                                success: function(response) {
                                    console.log("Respons dari server:", response);
                                    alert('Data berhasil disimpan!');
                                    $("#data-table-body").empty();
                                },
                                error: function(xhr, status, error) {
                                    console.error("Error:", error);
                                    alert('Terjadi kesalahan saat menyimpan data.');
                                }
                            });
                        });
                    });
                </script>
            </div>
        </div>
    </div>
</body>

</html>
