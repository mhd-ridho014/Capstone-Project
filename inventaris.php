<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Inventaris - Jelova Boutique</title>
    
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .dashboard-content {
            display: flex;
            flex-direction: column;
            height: calc(100vh - 150px);
        }
        
        .data-section {
            display: flex;
            flex-direction: column;
            flex: 1;
            overflow: hidden;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .table-scroll-container {
            flex: 1;
            overflow-y: auto;
            border: 1px solid #eee;
            border-radius: 8px;
            background: #fff;
            position: relative;
        }

        .table-scroll-container th {
            position: sticky;
            top: 0;
            background-color: #f8f9fa;
            z-index: 10;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .status.sedang { background-color: #e0f2fe; color: #0284c7; }
        .chip-filter { padding: 8px 16px; border-radius: 8px; border: 1px solid #e0e0e0; background-color: #f2f2f2; color: #555; font-size: 0.85rem; font-weight: 500; cursor: pointer; transition: all 0.2s ease; }
        .chip-filter:hover { background-color: #e8e8e8; }
        .chip-filter.active { background-color: #ffffff; border: 2px solid #111111; color: #000000; font-weight: bold; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
    </style>
</head>
<body class="dashboard-page" style="overflow: hidden;">

    <div id="app" v-cloak>
        <?php 
        $current_page = 'inventaris.php';
        include 'sidebar.php'; 
        ?>

        <main class="main-content">
            <header class="page-header">
                <h2>Manajemen Inventaris</h2>
            </header>

            <div class="dashboard-content">
                <div class="data-section">
                    <div class="data-section-header" style="flex-wrap: wrap; gap: 15px; flex-shrink: 0;">
                        <h3>Monitoring Inventaris</h3>
                        <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                            <div v-if="barangPeringatan.length > 0" 
                                 @click="showModalPeringatan = true"
                                 style="background-color: #fff8f8; border: 1px solid #fecdcd; color: #d9534f; padding: 8px 14px; border-radius: 8px; font-weight: 600; font-size: 0.9rem; display: flex; align-items: center; gap: 6px; cursor: pointer;"
                                 title="Klik untuk melihat rincian barang habis/menipis">
                                <i class="fas fa-exclamation-triangle" style="color: #f39c12;"></i>
                                {{ barangPeringatan.length }} (Stok Menipis/Habis)
                            </div>
                            <button class="btn-action" style="background-color: #6c757d; color: white;" @click="showModalKategori = true">
                                <i class="fas fa-tags"></i> Kelola Kategori
                            </button>
                            <button class="btn-action primary" @click="showModal = true">
                                <i class="fas fa-plus"></i> Tambah Barang
                            </button>
                        </div>
                    </div>

                    <div style="background: #fafafa; padding: 15px; border-radius: 8px; border: 1px solid #eee; margin-bottom: 15px; flex-shrink: 0;">
                        <div style="margin-bottom: 15px;">
                            <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #333; font-size: 0.9rem;">Waktu Update / Diunggah</label>
                            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                                <button v-for="waktu in opsiWaktu" :key="waktu" type="button" :class="['chip-filter', { active: filterWaktu === waktu }]" @click="filterWaktu = waktu">
                                    {{ waktu }}
                                </button>
                            </div>
                        </div>

                        <div style="display: flex; gap: 15px; flex-wrap: wrap; align-items: flex-end; border-top: 1px solid #eaeaea; padding-top: 12px;">
                            <div>
                                <label style="display: block; font-weight: 600; margin-bottom: 5px; color: #444; font-size: 0.85rem;">Kategori Produk</label>
                                <select v-model="filterKategori" style="padding: 9px 12px; border: 1px solid #ccc; border-radius: 8px; outline: none; background: #fff; min-width: 170px; font-family: inherit;">
                                    <option value="Semua">Semua Kategori</option>
                                    <option v-for="kat in daftarKategori" :key="kat" :value="kat">{{ kat }}</option>
                                </select>
                            </div>
                            <div>
                                <label style="display: block; font-weight: 600; margin-bottom: 5px; color: #444; font-size: 0.85rem;">Ukuran Baju</label>
                                <select v-model="filterUkuran" style="padding: 9px 12px; border: 1px solid #ccc; border-radius: 8px; outline: none; background: #fff; min-width: 140px; font-family: inherit;">
                                    <option value="Semua">Semua Ukuran</option>
                                    <option v-for="uk in daftarUkuran" :key="uk" :value="uk">{{ uk }}</option>
                                </select>
                            </div>
                            <div>
                                <label style="display: block; font-weight: 600; margin-bottom: 5px; color: #444; font-size: 0.85rem;"><i class="fas fa-sort"></i> Urutkan Berdasarkan</label>
                                <select v-model="urutan" style="padding: 9px 12px; border: 1px solid #c0a080; border-radius: 8px; outline: none; background: #fff; min-width: 180px; font-family: inherit; font-weight: 500;">
                                    <option value="a-z">Nama Produk (A - Z)</option>
                                    <option value="z-a">Nama Produk (Z - A)</option>
                                    <option value="harga-asc">Harga (Termurah)</option>
                                    <option value="harga-desc">Harga (Termahal)</option>
                                    <option value="stok-asc">Stok (Tersedikit)</option>
                                    <option value="stok-desc">Stok (Terbanyak)</option>
                                </select>
                            </div>
                            <div>
                                <button type="button" @click="resetFilter" style="padding: 9px 15px; background-color: #e2e8f0; border: 1px solid #cbd5e1; color: #334155; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 0.85rem; display: flex; align-items: center; gap: 6px; transition: background 0.2s;">
                                    <i class="fas fa-undo"></i> Reset Filter
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="table-scroll-container">
                       <table style="margin: 0; box-shadow: none;">
                            <thead>
                                <tr>
                                    <th>Kode Barang</th>
                                    <th>Nama Produk</th>
                                    <th>Kategori</th>
                                    <th>Ukuran</th>
                                    <th>Harga</th>
                                    <th>Sisa Stok</th>
                                    <th>Status</th>
                                    <th style="text-align: center;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in inventarisTersaring" :key="item.kode">
                                    <td><strong>{{ item.kode }}</strong></td>
                                    <td>{{ item.nama }}</td>
                                    <td><span style="background-color: #f5f5f5; border: 1px solid #ddd; padding: 4px 8px; border-radius: 4px; font-size: 0.85rem; color: #555;">{{ item.kategori || '-' }}</span></td>
                                    <td><span style="background-color: #eef2ff; border: 1px solid #c7d2fe; padding: 4px 8px; border-radius: 4px; font-size: 0.85rem; color: #3730a3; font-weight: 600;">{{ item.ukuran || 'All Size' }}</span></td>
                                    <td>Rp {{ item.harga.toLocaleString('id-ID') }}</td>
                                    <td><strong>{{ item.stok }}</strong></td>
                                    <td><span class="status" :class="getStatusClass(item.stok)">{{ getStatusText(item.stok) }}</span></td>
                                    <td style="text-align: center;">
                                        <button @click="bukaEditModal(item)" style="background: none; border: none; color: #f39c12; cursor: pointer; font-size: 1rem; margin-right: 15px;" title="Edit Stok & Barang"><i class="fas fa-edit"></i></button>
                                        <button @click="hapusBarang(item.kode)" style="background: none; border: none; color: #d9534f; cursor: pointer; font-size: 1rem;" title="Hapus Barang"><i class="fas fa-trash-alt"></i></button>
                                    </td>
                                </tr>
                                <tr v-if="inventarisTersaring.length === 0">
                                    <td colspan="8" style="text-align: center; color: #888; padding: 20px;">Tidak ada barang yang sesuai dengan filter.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>

        <div class="modal-overlay" v-if="showModalPeringatan">
            <div class="modal-content" style="max-width: 720px;">
                <div class="modal-header">
                    <h3 style="color: #d9534f;"><i class="fas fa-exclamation-triangle"></i> Daftar Barang Stok Menipis & Habis</h3>
                    <button class="close-btn" @click="showModalPeringatan = false">&times;</button>
                </div>
                <div style="max-height: 350px; overflow-y: auto; margin-top: 10px;">
                    <table style="font-size: 0.9rem;">
                        <thead>
                            <tr style="background-color: #f9f9f9;">
                                <th>Kode</th>
                                <th>Nama Barang</th>
                                <th>Kategori</th>
                                <th>Ukuran</th>
                                <th>Sisa Stok</th>
                                <th>Status</th>
                                <th style="text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in barangPeringatan" :key="item.kode">
                                <td><strong>{{ item.kode }}</strong></td>
                                <td>{{ item.nama }}</td>
                                <td><span style="background-color: #f5f5f5; border: 1px solid #ddd; padding: 3px 6px; border-radius: 4px; font-size: 0.8rem; color: #555;">{{ item.kategori || '-' }}</span></td>
                                <td><span style="background-color: #eef2ff; border: 1px solid #c7d2fe; padding: 3px 6px; border-radius: 4px; font-size: 0.8rem; color: #3730a3; font-weight: 600;">{{ item.ukuran || 'All Size' }}</span></td>
                                <td><strong>{{ item.stok }}</strong></td>
                                <td><span class="status" :class="getStatusClass(item.stok)">{{ getStatusText(item.stok) }}</span></td>
                                <td style="text-align: center;">
                                    <button @click="bukaEditModalDariWarning(item)" class="btn-action primary" style="padding: 4px 8px; font-size: 0.8rem;"><i class="fas fa-edit"></i> Update Stok</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="modal-overlay" v-if="showModalKategori">
            <div class="modal-content" style="max-width: 450px;">
                <div class="modal-header">
                    <h3>Kelola Kategori Produk</h3>
                    <button class="close-btn" @click="showModalKategori = false">&times;</button>
                </div>
                <form @submit.prevent="tambahKategoriBaru" style="display: flex; gap: 10px; margin-bottom: 20px;">
                    <input type="text" v-model="kategoriBaru" placeholder="Nama kategori baru..." required style="flex: 1; padding: 10px; border: 1px solid #ccc; border-radius: 6px; outline: none;">
                    <button type="submit" class="btn-action primary" style="padding: 10px 15px;"><i class="fas fa-plus"></i> Tambah</button>
                </form>
                <div style="max-height: 250px; overflow-y: auto; border: 1px solid #eee; border-radius: 6px; padding: 10px;">
                    <div v-for="(kat, index) in daftarKategori" :key="index" style="display: flex; justify-content: space-between; align-items: center; padding: 8px 10px; border-bottom: 1px solid #f5f5f5;">
                        <span style="font-weight: 500; color: #333;">{{ kat }}</span>
                        <button @click="hapusKategori(index)" style="background: none; border: none; color: #d9534f; cursor: pointer; font-size: 0.9rem;" title="Hapus Kategori"><i class="fas fa-trash-alt"></i></button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-overlay" v-if="showModal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Tambah Barang Baru</h3>
                    <button class="close-btn" @click="showModal = false">&times;</button>
                </div>
                <form @submit.prevent="tambahBarang">
                    <div class="form-group"><label>Kode Barang</label><input type="text" v-model="formBarang.kode" placeholder="Contoh: BTK-005" required></div>
                    <div class="form-group"><label>Nama Produk</label><input type="text" v-model="formBarang.nama" placeholder="Contoh: Rok Plisket" required></div>
                    <div class="form-group">
                        <label>Kategori Produk</label>
                        <select v-model="formBarang.kategori" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px;" required>
                            <option value="" disabled>-- Pilih Kategori --</option>
                            <option v-for="kat in daftarKategori" :key="kat" :value="kat">{{ kat }}</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Ukuran Baju</label>
                        <select v-model="formBarang.ukuran" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px;" required>
                            <option v-for="uk in daftarUkuran" :key="uk" :value="uk">{{ uk }}</option>
                        </select>
                    </div>
                    <div class="form-group"><label>Harga (Rp)</label><input type="number" v-model="formBarang.harga" min="1000" placeholder="0" required></div>
                    <div class="form-group"><label>Jumlah Stok</label><input type="number" v-model="formBarang.stok" min="0" required></div>
                    <button type="submit" class="btn-submit">Simpan Barang</button>
                </form>
            </div>
        </div>

        <div class="modal-overlay" v-if="showEditModal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Edit Stok & Data Barang</h3>
                    <button class="close-btn" @click="showEditModal = false">&times;</button>
                </div>
                <form @submit.prevent="simpanEditBarang">
                    <div class="form-group"><label>Kode Barang</label><input type="text" v-model="formEditBarang.kode" required></div>
                    <div class="form-group"><label>Nama Produk</label><input type="text" v-model="formEditBarang.nama" required></div>
                    <div class="form-group">
                        <label>Kategori Produk</label>
                        <select v-model="formEditBarang.kategori" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px;" required>
                            <option v-for="kat in daftarKategori" :key="kat" :value="kat">{{ kat }}</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Ukuran Baju</label>
                        <select v-model="formEditBarang.ukuran" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px;" required>
                            <option v-for="uk in daftarUkuran" :key="uk" :value="uk">{{ uk }}</option>
                        </select>
                    </div>
                    <div class="form-group"><label>Harga (Rp)</label><input type="number" v-model="formEditBarang.harga" min="1000" required></div>
                    <div class="form-group"><label>Update Jumlah Stok</label><input type="number" v-model="formEditBarang.stok" min="0" required></div>
                    <button type="submit" class="btn-submit" style="background-color: #f39c12;">Update Data</button>
                </form>
            </div>
        </div>
    </div>
    <script src="js/inventaris.js"></script>
</body>
</html>