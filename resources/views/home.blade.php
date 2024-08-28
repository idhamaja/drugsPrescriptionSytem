<!DOCTYPE html>
<html>
<head>
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
        }

    </style>
</head>
<body>
    <!-- Google Fonts - Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body, input, button, .card-title, .card-text {
            font-family: 'Poppins', sans-serif;
        }
    </style>
    <style>
        .card {
            margin-top: 100px;
        }
    </style>
    <style>
        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 9999;
        }
        .loading-spinner {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            color: white;
        }
        .spinner-border {
            width: 3rem;
            height: 3rem;
        }
    </style>
    <div class="loading-overlay">
        <div class="loading-spinner">
            <div class="spinner-border" role="status">
                <span class="sr-only">Loading...</span>
            </div>
            <p class="mt-2">Loading...</p>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const loadingOverlay = document.querySelector('.loading-overlay');

            form.addEventListener('submit', function(e) {
                e.preventDefault();
                loadingOverlay.style.display = 'block';

                setTimeout(function() {
                    form.submit();
                }, 1000);
            });
        });
    </script>
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card" style="background-color: #DCE8E6;">
                <div class="card-body">
                    <h1 class="mb-4">Masukkan Diagnosis</h1>
                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif
                    <form action="/recommendations" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="diagnosis">Diagnosis:</label>
                            <input type="text" class="form-control" id="diagnosis" name="diagnosis" required style="background-color: #D9D9D9;">
                        </div>
                        <button type="submit" class="btn" style="background-color: #28AE96; color: white;">Dapatkan Rekomendasi</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
