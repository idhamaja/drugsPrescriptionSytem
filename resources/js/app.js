import './bootstrap';

$(document).ready(function() {
    // Hubungkan ke server SocketIO
    var socket = io.connect('http://127.0.0.1:5000'); // URL ke server Flask

    // Mendengarkan event 'new_pasien' dari server
    socket.on('new_pasien', function(data) {
        // Tambahkan pasien baru ke tabel tanpa reload
        var newRow = `
            <tr>
                <td>${data.Nama}</td>
                <td>${data.Gender}</td>
                <td>${data.Umur}</td>
                <td>
                    <button class="btn btn-warning btn-sm">Diagnosa</button>
                </td>
            </tr>
        `;
        $('#patient-table tbody').append(newRow); // Append row baru ke tabel
    });

    // Mendengarkan event 'patient_deleted' dari server
    socket.on('patient_deleted', function(data) {
        alert("Pasien dengan nama " + data.nama + " telah dihapus.");
        $("tr:contains('" + data.nama + "')").remove(); // Hapus baris pasien dari tabel
    });
});


import Echo from 'laravel-echo';
import io from 'socket.io-client';

window.io = io;

window.Echo = new Echo({
    broadcaster: 'socket.io',
    host: window.location.hostname + ':6001' // Ubah sesuai dengan konfigurasi host server
});


window.Echo.channel('pasien')
    .listen('PasienAdded', (data) => {
        // Tambahkan pasien baru ke tabel
        let newRow = `
            <tr>
                <td>${data.nama}</td>
                <td>${data.gender}</td>
                <td>${data.umur}</td>
                <td>
                    <button class="btn btn-warning btn-sm">Diagnosa</button>
                </td>
            </tr>
        `;
        $('#patient-table tbody').append(newRow);

        // Tampilkan notifikasi
        Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: 'Data Pasien berhasil disimpan!',
            showConfirmButton: false,
            timer: 2000
        });
    });

