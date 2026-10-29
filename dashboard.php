<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Jelova Boutique</title>
    
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="dashboard-page">

    <div id="app" v-cloak>
        <?php 
        $current_page = 'dashboard.php';
        include 'sidebar.php'; 
        ?>

        <main class="main-content">
            <header class="page-header">
                <h2>Dashboard Utama</h2>
            </header>

            <div class="dashboard-content">

                <div style="background: white; padding: 15px 20px; border-radius: 12px; margin-bottom: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.03); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-calendar-alt" style="color: #c0a080; font-size: 1.2rem;"></i>
                        <span style="font-weight: 600; color: #333;">Filter Rentang Waktu:</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                        <input type="date" v-model="filterTanggalMulai" style="padding: 8px 12px; border: 1px solid #ccc; border-radius: 8px; outline: none; font-family: inherit;">
                        <span style="color: #888; font-weight: 500;">s/d</span>
                        <input type="date" v-model="filterTanggalAkhir" style="padding: 8px 12px; border: 1px solid #ccc; border-radius: 8px; outline: none; font-family: inherit;">
                        <button @click="resetFilterTanggal" style="padding: 8px 14px; background: #f2f2f2; border: 1px solid #ddd; border-radius: 8px; cursor: pointer; font-size: 0.85rem; font-weight: 500; transition: background 0.2s;">
                            <i class="fas fa-undo" style="margin-right: 4px;"></i> Semua Waktu
                        </button>
                    </div>
                </div>

                <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); margin-bottom: 25px;">
                    
                    <div class="stat-card" @click="keHalaman('inventaris.php')" style="cursor: pointer;">
                        <div class="stat-icon"><i class="fas fa-shopping-bag"></i></div>
                        <div class="stat-details">
                            <h3>{{ inventaris.length }}</h3>
                            <p>Jenis Produk</p>
                        </div>
                    </div>

                    <div class="stat-card" @click="keHalaman('laporan.php')" style="cursor: pointer;">
                        <div class="stat-icon blue"><i class="fas fa-tshirt"></i></div>
                        <div class="stat-details">
                            <h3>{{ totalSemuaBarangTerjual }}</h3>
                            <p>Total Barang Terjual</p>
                        </div>
                    </div>

                    <div class="stat-card" @click="bukaModalPendapatan" style="cursor: pointer;" title="Klik untuk melihat rincian metode pembayaran">
                        <div class="stat-icon green"><i class="fas fa-money-bill-wave"></i></div>
                        <div class="stat-details">
                            <h3>Rp {{ totalPendapatan.toLocaleString('id-ID') }}</h3>
                            <p>Total Pendapatan Keseluruhan <i class="fas fa-info-circle" style="font-size: 0.75rem; color: #888;"></i></p>
                        </div>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 20px; align-items: start;">
                    
                    <div class="data-section" style="margin-top: 0; padding: 20px; display: flex; flex-direction: column; height: 380px;">
                        <div class="data-section-header" style="margin-bottom: 15px; flex-shrink: 0;">
                            <h3 style="font-size: 1.1rem; color: #333;"><i class="fas fa-history" style="margin-right: 8px; color: #c0a080;"></i> Transaksi Terakhir</h3>
                        </div>
                        <div style="flex: 1; overflow-y: auto; border: 1px solid #eee; border-radius: 8px; background: #fff;">
                            <table style="font-size: 0.9rem; box-shadow: none; margin: 0; width: 100%; border-collapse: collapse;">
                                <thead style="position: sticky; top: 0; z-index: 10; background-color: #fcfcfc; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                                    <tr>
                                        <th style="padding: 12px 15px; border-bottom: 1px solid #eee;">Tanggal & Referensi</th>
                                        <th style="padding: 12px 15px; text-align: right; border-bottom: 1px solid #eee;">Total Nilai</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="trx in transaksiTerbaru" :key="trx.ref">
                                        <td style="padding: 12px 15px; border-bottom: 1px solid #eee;">
                                            <strong>{{ trx.tanggal }}</strong><br>
                                            <span style="font-size: 0.8rem; color: #888;">{{ trx.ref }}</span>
                                        </td>
                                        <td style="font-weight: bold; color: #2e7d32; text-align: right; padding: 12px 15px; border-bottom: 1px solid #eee;">
                                            Rp {{ trx.total.toLocaleString('id-ID') }}
                                        </td>
                                    </tr>
                                    <tr v-if="transaksiTerbaru.length === 0">
                                        <td colspan="2" style="text-align: center; color: #888; padding: 20px;">Belum ada transaksi di rentang tanggal ini.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="data-section" style="margin-top: 0; padding: 20px; display: flex; flex-direction: column; height: 380px;">
                        <div class="data-section-header" style="margin-bottom: 15px; flex-shrink: 0;">
                            <h3 style="font-size: 1.1rem; color: #333;"><i class="fas fa-exclamation-circle" style="margin-right: 8px; color: #d9534f;"></i> Peringatan Stok</h3>
                        </div>
                        <div style="flex: 1; overflow-y: auto; border: 1px solid #eee; border-radius: 8px; background: #fff;">
                            <table style="font-size: 0.9rem; box-shadow: none; margin: 0; width: 100%; border-collapse: collapse;">
                                <thead style="position: sticky; top: 0; z-index: 10; background-color: #fcfcfc; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                                    <tr>
                                        <th style="padding: 12px 15px; border-bottom: 1px solid #eee;">Nama Produk</th>
                                        <th style="padding: 12px 15px; text-align: center; border-bottom: 1px solid #eee;">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="item in barangMenipis" :key="item.kode">
                                        <td style="padding: 12px 15px; border-bottom: 1px solid #eee;">{{ item.nama }}</td>
                                        <td style="text-align: center; padding: 12px 15px; border-bottom: 1px solid #eee;">
                                            <span class="status" :class="item.stok === 0 ? 'habis' : 'menipis'">
                                                {{ item.stok === 0 ? 'Habis (0)' : `Menipis (${item.stok})` }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr v-if="barangMenipis.length === 0">
                                        <td colspan="2" style="text-align: center; color: #888; padding: 20px;">Semua stok barang dalam kondisi aman (>5 pcs).</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <div class="modal-overlay" v-if="showModalPendapatan">
            <div class="modal-content" style="max-width: 450px; font-family: inherit;">
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee; padding-bottom: 12px; margin-bottom: 15px;">
                    <h3 style="margin: 0; color: #333; font-size: 1.2rem;"><i class="fas fa-wallet" style="color: #c0a080; margin-right: 8px;"></i> Rincian Pendapatan</h3>
                    <button @click="tutupModalPendapatan" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: #888;"><i class="fas fa-times"></i></button>
                </div>

                <p style="font-size: 0.85rem; color: #666; margin-bottom: 12px;">Akumulasi total pendapatan berdasarkan metode pembayaran (sesuai filter waktu):</p>

                <div style="display: flex; flex-direction: column; gap: 8px; max-height: 250px; overflow-y: auto;">
                    <div v-for="(nominal, metode) in pendapatanPerMetode" :key="metode" style="display: flex; justify-content: space-between; align-items: center; padding: 10px 12px; background: #fafafa; border: 1px solid #eee; border-radius: 6px; font-size: 0.9rem;">
                        <span style="font-weight: 600; color: #444;">{{ metode }}</span>
                        <span style="font-weight: bold; color: #2e7d32;">Rp {{ nominal.toLocaleString('id-ID') }}</span>
                    </div>
                    <div v-if="Object.keys(pendapatanPerMetode).length === 0" style="text-align: center; color: #888; padding: 15px; font-size: 0.9rem;">
                        Belum ada data pendapatan.
                    </div>
                </div>

                <div style="border-top: 1px solid #eee; margin-top: 15px; padding-top: 12px; display: flex; justify-content: space-between; align-items: center; font-weight: bold; font-size: 0.95rem;">
                    <span>TOTAL KESELURUHAN:</span>
                    <span style="color: #c0a080;">Rp {{ totalPendapatan.toLocaleString('id-ID') }}</span>
                </div>

                <div style="margin-top: 20px; text-align: right;">
                    <button @click="tutupModalPendapatan" style="padding: 8px 16px; background-color: #333; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 0.9rem;">
                        Tutup
                    </button>
                </div>
            </div>
        </div>

    </div>
    <script src="js/dashboard.js"></script>
</body>
</html>