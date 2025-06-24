document.addEventListener('DOMContentLoaded', function() {
    // Load produk via AJAX
    loadProduk();

    // Form kontak submit handler
    document.getElementById('formKontak').addEventListener('submit', function(e) {
        e.preventDefault();
        alert('Pesan Anda telah terkirim! Kami akan segera menghubungi Anda.');
        this.reset();
    });
});

function loadProduk() {
    // Simulasi data produk (dalam aplikasi nyata, ini akan diambil dari database via AJAX)
    const produk = [
        {
            id: 1,
            nama: "Kopi Arabica",
            deskripsi: "Kopi berkualitas tinggi dari perkebunan di Jawa Barat",
            harga: "Rp 120.000/kg",
            gambar: "https://images.unsplash.com/photo-1517701550927-30cf4ba1dba5"
        },
        {
            id: 2,
            nama: "Teh Hijau",
            deskripsi: "Teh hijau organik dari perkebunan di Jawa Tengah",
            harga: "Rp 85.000/kg",
            gambar: "https://images.unsplash.com/photo-1534430480872-3498386e7856"
        },
        {
            id: 3,
            nama: "Kelapa Sawit",
            deskripsi: "Kelapa sawit segar langsung dari perkebunan",
            harga: "Rp 10.000/kg",
            gambar: "https://images.unsplash.com/photo-1596704017255-ee7b331c1e63"
        },
        {
            id: 4,
            nama: "Karet Alam",
            deskripsi: "Karet alam kualitas ekspor",
            harga: "Rp 25.000/kg",
            gambar: "https://images.unsplash.com/photo-1581431886217-7d70746986fa"
        }
    ];

    const produkList = document.querySelector('.produk-list');
    produkList.innerHTML = '';

    produk.forEach(item => {
        const produkItem = document.createElement('div');
        produkItem.className = 'produk-item';
        produkItem.innerHTML = `
            <div class="produk-img">
                <img src="${item.gambar}" alt="${item.nama}">
            </div>
            <div class="produk-info">
                <h3>${item.nama}</h3>
                <p>${item.deskripsi}</p>
                <p class="harga">${item.harga}</p>
                <a href="login.php" class="btn-beli">Beli Sekarang</a>
            </div>
        `;
        produkList.appendChild(produkItem);
    });
}
