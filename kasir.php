<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Kasir (POS) - Jelova Boutique</title>
    
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>

<body class="dashboard-page">

    <div id="app" v-cloak>
        <?php 
        $current_page = 'kasir.php';
        include 'sidebar.php'; 
        ?>

        <main class="main-content">
            <header class="page-header" style="margin-bottom: 15px;">
                <h2 style="margin: 0;">Sistem Kasir (POS)</h2>
            </header>

            <div class="dashboard-content">
                <div class="pos-container" style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 20px; align-items: start;">
                    
                    <div class="product-list" style="display: flex; flex-direction: column; height: calc(100vh - 170px); padding: 15px; border-radius: 12px; background: #fff; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
                        <div style="flex-shrink: 0; margin-bottom: 15px;">
                            <h3 style="margin: 0; margin-bottom: 10px;">Pilih Produk</h3>
                            <div style="display: flex; gap: 10px; flex-wrap: wrap; background: #fafafa; padding: 12px; border-radius: 8px; border: 1px solid #eee;">
                                <div style="flex: 1; min-width: 140px;">
                                    <label style="display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 4px; color: #555;">Kategori Produk</label>
                                    <select v-model="filterKategori" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 6px; outline: none; background: #fff; font-size: 0.85rem;">
                                        <option value="Semua">Semua Kategori</option>
                                        <option v-for="kat in daftarKategori" :key="kat" :value="kat">{{ kat }}</option>
                                    </select>
                                </div>
                                <div style="flex: 1; min-width: 120px;">
                                    <label style="display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 4px; color: #555;">Ukuran Baju</label>
                                    <select v-model="filterUkuran" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 6px; outline: none; background: #fff; font-size: 0.85rem;">
                                        <option value="Semua">Semua Ukuran</option>
                                        <option v-for="uk in daftarUkuran" :key="uk" :value="uk">{{ uk }}</option>
                                    </select>
                                </div>
                                <div style="display: flex; align-items: flex-end;">
                                    <button type="button" @click="resetFilter" style="padding: 8px 12px; background: #e2e8f0; border: 1px solid #cbd5e1; border-radius: 6px; cursor: pointer; font-size: 0.8rem; font-weight: 600; color: #334155;">
                                        <i class="fas fa-undo"></i> Reset
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div style="flex: 1; overflow-y: auto; padding-right: 5px; min-height: 0;">
                            <div class="product-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 15px; align-items: start;">
                                <div class="product-card" 
                                     v-for="item in inventarisTersaring" :key="item.kode" 
                                     :class="{ habis: item.stok === 0 }"
                                     @click="tambahKeKeranjang(item)"
                                     style="border: 1px solid #e0e0e0; border-radius: 8px; padding: 15px; text-align: center; cursor: pointer; background: #fff; transition: all 0.2s;">
                                    <div style="display: flex; justify-content: center; gap: 5px; margin-bottom: 8px; flex-wrap: wrap;">
                                        <span style="font-size: 0.7rem; background-color: #f5f5f5; border: 1px solid #ddd; padding: 2px 6px; border-radius: 4px; color: #555;">
                                            {{ item.kategori || '-' }}
                                        </span>
                                        <span style="font-size: 0.7rem; background-color: #eef2ff; border: 1px solid #c7d2fe; padding: 2px 6px; border-radius: 4px; color: #3730a3; font-weight: 600;">
                                            {{ item.ukuran || 'All Size' }}
                                        </span>
                                    </div>
                                    <h4 style="font-size: 0.95rem; margin-bottom: 5px; color: #333;">{{ item.nama }}</h4>
                                    <p style="font-size: 0.8rem; color: #888;">Stok: {{ item.stok }}</p>
                                    <p style="color: #c0a080; font-weight: bold; margin-top: 5px; font-size: 1.05rem;">Rp {{ item.harga.toLocaleString('id-ID') }}</p>
                                </div>
                                <div v-if="inventarisTersaring.length === 0" style="grid-column: 1 / -1; text-align: center; color: #888; padding: 30px;">
                                    Tidak ada produk yang sesuai dengan filter Kategori atau Ukuran.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="cart-section" style="display: flex; flex-direction: column; height: calc(100vh - 170px); border-radius: 12px; background: #fff; padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
                        <h3 style="margin-bottom: 15px; flex-shrink: 0;">Keranjang Belanja</h3>
                        <div class="cart-items" style="flex: 1; overflow-y: auto; margin-bottom: 15px; border-bottom: 1px solid #eee; padding-right: 5px; min-height: 0;">
                            <div v-if="keranjang.length === 0" style="text-align: center; color: #888; padding: 20px;">
                                Belum ada produk yang dipilih
                            </div>
                            <div class="cart-item" v-for="(item, index) in keranjang" :key="index" style="background: #fafafa; border: 1px solid #eee; border-radius: 8px; padding: 12px; margin-bottom: 10px;">
                                <div class="cart-item-info">
                                    <h4 style="margin-bottom: 4px; font-size: 0.95rem;">{{ item.nama }}</h4>
                                    <div style="font-size: 0.75rem; color: #777; margin-bottom: 8px;">
                                        <span>{{ item.kategori || '-' }}</span> | <strong style="color: #3730a3;">{{ item.ukuran || 'All Size' }}</strong>
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <input type="number" v-model.number="item.qty" @change="cekStok(item)" min="1" :max="item.stok" style="width: 60px; padding: 6px; border: 1px solid #c0a080; border-radius: 6px; text-align: center; font-weight: bold; outline: none;">
                                        <span style="font-size: 0.85rem; color: #666; font-weight: 500;">@ Rp {{ item.harga.toLocaleString('id-ID') }}</span>
                                    </div>
                                </div>
                                <div style="text-align: right; display: flex; flex-direction: column; justify-content: center; align-items: flex-end;">
                                    <strong style="color: #2e7d32; font-size: 1.05rem;">Rp {{ (item.qty * item.harga).toLocaleString('id-ID') }}</strong>
                                    <button @click="hapusDariKeranjang(index)" style="background: none; border: none; color: #d9534f; cursor: pointer; font-size: 0.85rem; font-weight: 600; margin-top: 8px; padding: 4px 8px; border-radius: 4px; transition: background 0.2s;">
                                        <i class="fas fa-trash-alt"></i> Hapus
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="checkout-section" style="flex-shrink: 0; background-color: #f9f9f9; padding: 20px; border-radius: 8px; border: 1px solid #eee;">
                            <div class="checkout-row" style="color: #666;">
                                <span>Subtotal</span>
                                <span>Rp {{ totalBelanja.toLocaleString('id-ID') }}</span>
                            </div>
                            <div class="checkout-row total" style="font-size: 1.2rem; font-weight: 700; color: #333; margin-top: 5px; margin-bottom: 15px; border-bottom: 1px dashed #ccc; padding-bottom: 15px;">
                                <span>Total Tagihan</span>
                                <span>Rp {{ totalBelanja.toLocaleString('id-ID') }}</span>
                            </div>
                            
                            <div>
                                <label style="font-size: 0.9rem; font-weight: 600; color: #555;">Metode Pembayaran</label>
                                <select v-model="metodePembayaran" style="width: 100%; padding: 10px 12px; border: 1px solid #ccc; border-radius: 6px; margin-top: 5px; font-family: inherit; font-size: 0.95rem; outline: none; background: #fff;">
                                    <option value="Tunai">Tunai (Cash)</option>
                                    <option value="QRIS">QRIS</option>
                                    <option value="Transfer Bank">Transfer Bank</option>
                                    <option value="Kartu Debit/Kredit">Kartu Debit / Kredit</option>
                                    <option value="ShopeePay/GoPay">ShopeePay / GoPay</option>
                                    <option value="OVO/DANA">OVO / DANA</option>
                                    <option value="PayLater">PayLater / Cicilan</option>
                                </select>
                            </div>

                            <div style="margin-top: 15px;" v-if="metodePembayaran === 'Tunai'">
                                <label style="font-size: 0.9rem; font-weight: 600; color: #555;">Input Tunai Pembeli (Rp)</label>
                                <input type="number" v-model="uangTunai" class="input-tunai" style="width: 100%; padding: 10px 12px; margin-top: 5px; border: 1px solid #c0a080; border-radius: 6px; font-size: 1.1rem; text-align: right; font-weight: bold; background: #fff;" placeholder="0" min="0">
                            </div>

                            <div class="checkout-row" style="margin-top: 15px; color: #2e7d32; font-size: 1.1rem; font-weight: 700;" v-if="metodePembayaran === 'Tunai' && uangTunai > 0">
                                <span>Kembalian</span>
                                <span>Rp {{ kembalian >= 0 ? kembalian.toLocaleString('id-ID') : 0 }}</span>
                            </div>

                            <button class="btn-action success" style="width: 100%; margin-top: 20px; padding: 14px; font-size: 1.1rem; font-weight: bold; border-radius: 8px; box-shadow: 0 4px 6px rgba(46,125,50,0.2);" 
                                    @click="prosesPembayaran"
                                    :disabled="keranjang.length === 0 || (metodePembayaran === 'Tunai' && uangTunai < totalBelanja)">
                                <i class="fas fa-check-circle" style="margin-right: 5px;"></i> Selesaikan Pembayaran
                            </button>
                        </div>
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
                    <p style="margin: 8px 0 0; font-size: 0.85rem; font-weight: bold;">--- BUKTI PEMBAYARAN ---</p>
                </div>
                
                <div style="font-size: 0.85rem; margin-bottom: 15px;">
                    <div style="display: flex; justify-content: space-between;"><span>Tgl:</span> <span>{{ strukData.tanggal }}</span></div>
                    <div style="display: flex; justify-content: space-between;"><span>Ref:</span> <span>{{ strukData.ref }}</span></div>
                    <div style="display: flex; justify-content: space-between;"><span>Kasir:</span> <span style="text-transform: capitalize;">{{ userRole }}</span></div>
                </div>

                <div style="border-bottom: 1px dashed #000; margin-bottom: 15px;"></div>

                <div v-for="item in strukData.items" :key="item.kode" style="font-size: 0.85rem; margin-bottom: 10px;">
                    <div style="font-weight: bold;">{{ item.nama }} ({{ item.ukuran || 'All Size' }})</div>
                    <div style="display: flex; justify-content: space-between;">
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
                    <div v-if="strukData.metode === 'Tunai'" style="display: flex; justify-content: space-between; margin-top: 5px;">
                        <span>Tunai:</span>
                        <span>Rp {{ strukData.tunai.toLocaleString('id-ID') }}</span>
                    </div>
                    <div v-if="strukData.metode === 'Tunai'" style="display: flex; justify-content: space-between; margin-top: 5px;">
                        <span>Kembalian:</span>
                        <span>Rp {{ strukData.kembalian.toLocaleString('id-ID') }}</span>
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
    </div>

    <script src="js/kasir.js"></script>
</body>
</html>