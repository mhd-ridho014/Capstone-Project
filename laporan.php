<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan - Jelova Boutique</title>
    
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="dashboard-page">

    <div id="app" v-cloak>
        <?php 
        $current_page = 'laporan.php';
        include 'sidebar.php'; 
        ?>

        <main class="main-content">
            <header class="page-header">
                <h2>Laporan Penjualan</h2>
            </header>

            <div class="dashboard-content">
                
                <div style="background: white; padding: 15px 20px; border-radius: 12px; margin-bottom: 20px; box-shadow: 0 8px 20px rgba(0,0,0,0.04); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-calendar-alt" style="color: #c0a080; font-size: 1.2rem;"></i>
                        <span style="font-weight: bold; color: #333;">Rentang Waktu Laporan:</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                        <input type="date" v-model="filterTanggalMulai" style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 6px; outline: none; color: #555; font-family: inherit;">
                        <span style="font-weight: bold; color: #888;">s/d</span>
                        <input type="date" v-model="filterTanggalAkhir" style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 6px; outline: none; color: #555; font-family: inherit;">
                        
                        <button @click="setHariIni" style="padding: 8px 12px; background: #eef2ff; border: 1px solid #c7d2fe; color: #3730a3; border-radius: 6px; cursor: pointer; font-size: 0.85rem; font-weight: 600;">
                            <i class="fas fa-calendar-day" style="margin-right: 4px;"></i> Hari Ini
                        </button>

                        <button @click="resetFilterTanggal" style="padding: 8px 14px; background: #f2f2f2; border: 1px solid #ddd; color: #333; border-radius: 6px; cursor: pointer; font-size: 0.85rem; font-weight: 600;">
                            <i class="fas fa-undo" style="margin-right: 4px;"></i> Reset 
                        </button>
                    </div>
                </div>

                <div class="stats-grid" style="margin-bottom: 20px;">
                    
                    <div class="stat-card" @click="bukaModalPendapatan" style="cursor: pointer;" title="Klik untuk melihat rincian metode pembayaran">
                        <div class="stat-icon green">
                        <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div class="stat-details">
                            <h3>Rp {{ totalPendapatanHariIni.toLocaleString('id-ID') }}</h3>
                            <p>Pendapatan Rentang Terpilih <i class="fas fa-info-circle" style="font-size: 0.75rem; color: #888;"></i></p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon blue">
                        <i class="fas fa-tshirt"></i>
                        </div>
                        <div class="stat-details">
                            <h3>{{ totalBarangTerjualHariIni }} Pcs</h3>
                            <p>Barang Terjual Rentang Terpilih</p>
                        </div>
                    </div>
                </div>

                <div class="data-section">
                    <h3>Grafik Produk Terjual</h3>
                    <p style="color: #888; font-size: 0.9rem; margin-bottom: 10px;">Total kuantitas barang terjual per produk pada rentang waktu terpilih</p>
                    
                    <div class="chart-container">
                        <div class="chart-bar-group" v-for="(prod, index) in statistikProduk" :key="index" :title="`${prod.nama}: ${prod.totalQty} Pcs`">
                            <div class="chart-bar" :style="{ height: Math.min(prod.totalQty * 18, 180) + 'px' }" style="background-color: #c0a080;"></div>
                            <div class="chart-label" style="font-size: 0.75rem; max-width: 80px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" :title="prod.nama">
                                {{ prod.nama }}
                            </div>
                        </div>
                        <div v-if="statistikProduk.length === 0" style="color: #888; margin: auto;">Belum ada data produk terjual di rentang tanggal ini.</div>
                    </div>
                </div>

                <div class="data-section">
                    <div class="data-section-header" style="flex-wrap: wrap; gap: 15px;">
                        <h3>Rincian Penjualan</h3>
                        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                            <div style="display: flex; align-items: center; gap: 8px; background: #fafafa; padding: 6px 12px; border: 1px solid #ddd; border-radius: 6px;">
                                <label style="font-size: 0.85rem; font-weight: 600; color: #555;"><i class="fas fa-sort"></i> Urutkan:</label>
                                <select v-model="urutanLaporan" style="border: none; outline: none; background: transparent; font-family: inherit; font-size: 0.85rem; font-weight: 600; color: #333; cursor: pointer;">
                                    <option value="terbaru">Terbaru</option>
                                    <option value="terlama">Terlama</option>
                                </select>
                            </div>

                            <button class="btn-action success" @click="exportExcel">
                                <i class="fas fa-file-excel"></i> Export Excel
                            </button>
                            <button v-if="userRole === 'admin'" class="btn-action" style="background-color: #d9534f; color: white;" @click="resetLaporan">
                                <i class="fas fa-trash-alt"></i> Reset Laporan
                            </button>
                        </div>
                    </div>
                    <div class="table-scroll-container">
                        <table style="margin: 0; box-shadow: none;">
                            <thead>
                                <tr>
                                    <th>Tanggal & Waktu</th>
                                    <th>No. Referensi</th>
                                    <th>Produk Terjual</th>
                                    <th>Total Nilai</th>
                                    <th style="text-align: center;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="laporan in laporanHarian" :key="laporan.ref">
                                    <td>{{ laporan.tanggal }}</td>
                                    <td>{{ laporan.ref }}</td>
                                    <td style="line-height: 1.6;">{{ formatRincian(laporan.produk) }}</td>
                                    <td>Rp {{ laporan.total.toLocaleString('id-ID') }}</td>
                                    <td style="text-align: center;">
                                        <button @click="cetakUlang(laporan)" style="background: none; border: none; color: #333; cursor: pointer; font-size: 1.1rem;" title="Cetak Ulang Struk">
                                            <i class="fas fa-print"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="laporanHarian.length === 0">
                                    <td colspan="5" style="text-align: center; color: #888; padding: 20px;">Tidak ada data penjualan pada rentang tanggal ini.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>

        
        <div class="modal-overlay" v-if="showStruk">
            <div class="modal-content" style="max-width: 350px; font-family: 'Courier New', Courier, monospace; color: #000; border-radius: 0;">
                <div style="text-align: center; border-bottom: 1px dashed #000; padding-bottom: 12px; margin-bottom: 15px;">
                    <h2 style="margin: 0; font-size: 1.4rem; color: #000; font-weight: bold;">Jelova Boutique</h2>
                    <p style="margin: 4px 0 0; font-size: 0.8rem; line-height: 1.3;">Jalan Pramuka No 34 Sudagaran Banyumas</p>
                    <p style="margin: 2px 0 0; font-size: 0.8rem;">Telp: 0851-2261-4007</p>
                    <p style="margin: 2px 0 0; font-size: 0.8rem;">IG: @jelova.officiall</p>
                    <p style="margin: 8px 0 0; font-size: 0.85rem; font-weight: bold;">--- BUKTI PEMBAYARAN (COPY) ---</p>
                </div>
                
                <div style="font-size: 0.85rem; margin-bottom: 15px;">
                    <div style="display: flex; justify-content: space-between;"><span>Tgl:</span> <span>{{ strukData.tanggal }}</span></div>
                    <div style="display: flex; justify-content: space-between;"><span>Ref:</span> <span>{{ strukData.ref }}</span></div>
                    <div style="display: flex; justify-content: space-between;"><span>Kasir:</span> <span style="text-transform: capitalize;">{{ userRole }}</span></div>
                </div>

                <div style="border-bottom: 1px dashed #000; margin-bottom: 15px;"></div>

                <div v-for="item in strukData.items" :key="item.kode || item.nama" style="font-size: 0.85rem; margin-bottom: 12px;">
                    <div style="font-weight: bold;">{{ item.nama }} ({{ item.ukuran || 'All Size' }})</div>
                    <div style="display: flex; justify-content: space-between; margin-top: 2px;">
                        <span>{{ item.qty }} x Rp {{ item.harga.toLocaleString('id-ID') }}</span>
                        <span>Rp {{ (item.qty * item.harga).toLocaleString('id-ID') }}</span>
                    </div>
                </div>

                <div style="border-bottom: 1px dashed #000; margin-bottom: 15px;"></div>

                <div style="font-size: 0.85rem;">
                    <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 1rem;">
                        <span>TOTAL</span>
                        <span>Rp {{ strukData.total.toLocaleString('id-ID') }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-top: 5px;">
                        <span>Metode:</span>
                        <span>{{ strukData.metode }}</span>
                    </div>
                    <div v-if="strukData.metode && strukData.metode.toLowerCase().includes('tunai')" style="display: flex; justify-content: space-between; margin-top: 5px;">
                        <span>Tunai:</span>
                        <span>Rp {{ (strukData.tunai || strukData.total).toLocaleString('id-ID') }}</span>
                    </div>
                    <div v-if="strukData.metode && strukData.metode.toLowerCase().includes('tunai')" style="display: flex; justify-content: space-between; margin-top: 5px;">
                        <span>Kembalian:</span>
                        <span>Rp {{ (strukData.kembalian || 0).toLocaleString('id-ID') }}</span>
                    </div>
                </div>

                <div style="text-align: center; border-top: 1px dashed #000; padding-top: 15px; margin-top: 15px;">
                    <p style="margin: 0; font-size: 0.85rem;">Terima Kasih Atas Kunjungan Anda!</p>
                </div>

                <div class="no-print" style="display: flex; gap: 10px; margin-top: 25px;">
                    <button @click="cetakStruk" style="flex: 1; padding: 10px; background-color: #333; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">
                        <i class="fas fa-print"></i> Cetak
                    </button>
                    <button @click="tutupStruk" style="flex: 1; padding: 10px; background-color: #d9534f; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">
                        Selesai
                    </button>
                </div>
            </div>
        </div>

        
        <div class="modal-overlay" v-if="showModalPendapatan">
            <div class="modal-content" style="max-width: 450px; font-family: inherit;">
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee; padding-bottom: 12px; margin-bottom: 15px;">
                    <h3 style="margin: 0; color: #333; font-size: 1.2rem;"><i class="fas fa-wallet" style="color: #c0a080; margin-right: 8px;"></i> Rincian Pendapatan</h3>
                    <button @click="tutupModalPendapatan" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: #888;"><i class="fas fa-times"></i></button>
                </div>

                <p style="font-size: 0.85rem; color: #666; margin-bottom: 12px;">Akumulasi total pendapatan berdasarkan metode pembayaran (sesuai rentang waktu terpilih):</p>

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
                    <span style="color: #c0a080;">Rp {{ totalPendapatanHariIni.toLocaleString('id-ID') }}</span>
                </div>

                <div style="margin-top: 20px; text-align: right;">
                    <button @click="tutupModalPendapatan" style="padding: 8px 16px; background-color: #333; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 0.9rem;">
                        Tutup
                    </button>
                </div>
            </div>
        </div>

    </div>

    <script src="js/laporan.js"></script>
</body>
</html>