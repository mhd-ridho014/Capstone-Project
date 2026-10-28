const { createApp, ref, computed, onMounted } = Vue;

createApp({
    setup() {
        const savedRole = sessionStorage.getItem('userRole');
        if (!savedRole) {
            window.location.href = 'index.php';
        } else if (savedRole === 'kasir') {
            window.location.href = 'kasir.php';
        }
        const userRole = ref(savedRole);

        const dbProfilKey = `db_profil_${userRole.value}`;
        const dbProfil = localStorage.getItem(dbProfilKey);
        const profilData = ref(dbProfil ? JSON.parse(dbProfil) : { nama: userRole.value, foto: '' });

        const defaultKategori = ['Kemeja', 'Blouse', 'Cardigan', 'Sweater', 'Crop Top', 'Vest', 'Blazer', 'Jacket', 'Inner Manset', 'Tunik', 'Gamis', 'Outer Brukat', 'Piyama + Dress', 'Celana Jeans', 'Celana Kain', 'Rok', 'Hijab', 'Sandal', '2in1', 'Kaos'];
        const daftarUkuran = ['All Size', 'S', 'M', 'L', 'XL', 'XXL'];
        const opsiWaktu = ['Semua', '24 jam terakhir', 'Minggu ini', 'Bulan ini', '3 bulan terakhir', '6 bulan terakhir'];
        const filterWaktu = ref('Semua');

        const dbKategori = localStorage.getItem('db_kategori');
        const daftarKategori = ref(dbKategori ? JSON.parse(dbKategori) : defaultKategori);

        const simpanKategoriKeDatabase = () => { localStorage.setItem('db_kategori', JSON.stringify(daftarKategori.value)); };
        const showModalKategori = ref(false);
        const kategoriBaru = ref('');

        const tambahKategoriBaru = () => {
            const nama = kategoriBaru.value.trim();
            if (!nama) return;
            if (daftarKategori.value.some(k => k.toLowerCase() === nama.toLowerCase())) return alert('Kategori ini sudah ada!');
            daftarKategori.value.push(nama);
            simpanKategoriKeDatabase();
            kategoriBaru.value = '';
        };

        const hapusKategori = (index) => {
            if (confirm(`Yakin ingin menghapus kategori "${daftarKategori.value[index]}"?`)) {
                daftarKategori.value.splice(index, 1);
                simpanKategoriKeDatabase();
            }
        };

        const filterKategori = ref('Semua');
        const filterUkuran = ref('Semua');
        const urutan = ref('a-z');

        const resetFilter = () => {
            filterWaktu.value = 'Semua';
            filterKategori.value = 'Semua';
            filterUkuran.value = 'Semua';
            urutan.value = 'a-z';
        };

        
        const inventaris = ref([]);

        const muatDataDatabase = async () => {
            try {
                const response = await fetch('api_inventaris.php');
                const data = await response.json();
                inventaris.value = data;
            } catch (error) {
                console.error('Gagal memuat data:', error);
            }
        };

        onMounted(() => {
            muatDataDatabase();
        });

        const getStatusText = (stok) => stok === 0 ? 'Habis' : stok <= 5 ? 'Menipis' : stok <= 15 ? 'Sedang' : 'Aman';
        const getStatusClass = (stok) => stok === 0 ? 'habis' : stok <= 5 ? 'menipis' : stok <= 15 ? 'sedang' : 'aman';

        const barangPeringatan = computed(() => inventaris.value.filter(item => item.stok <= 5));
        const showModalPeringatan = ref(false);

        const inventarisTersaring = computed(() => {
            const currentTime = Date.now();
            const msInDay = 24 * 60 * 60 * 1000;

            let hasil = inventaris.value.filter(item => {
                const cocokKategori = filterKategori.value === 'Semua' || item.kategori === filterKategori.value;
                const cocokUkuran = filterUkuran.value === 'Semua' || (item.ukuran || 'All Size') === filterUkuran.value;
                
                let cocokWaktu = true;
                const selisihWaktu = currentTime - (item.updatedAt || 0);

                if (filterWaktu.value === '24 jam terakhir') cocokWaktu = selisihWaktu <= msInDay;
                else if (filterWaktu.value === 'Minggu ini') cocokWaktu = selisihWaktu <= 7 * msInDay;
                else if (filterWaktu.value === 'Bulan ini') cocokWaktu = selisihWaktu <= 30 * msInDay;
                else if (filterWaktu.value === '3 bulan terakhir') cocokWaktu = selisihWaktu <= 90 * msInDay;
                else if (filterWaktu.value === '6 bulan terakhir') cocokWaktu = selisihWaktu <= 180 * msInDay;

                return cocokKategori && cocokUkuran && cocokWaktu;
            });

            return hasil.sort((a, b) => {
                if (urutan.value === 'a-z') return a.nama.localeCompare(b.nama);
                if (urutan.value === 'z-a') return b.nama.localeCompare(a.nama);
                if (urutan.value === 'harga-asc') return a.harga - b.harga;
                if (urutan.value === 'harga-desc') return b.harga - a.harga;
                if (urutan.value === 'stok-asc') return a.stok - b.stok;
                if (urutan.value === 'stok-desc') return b.stok - a.stok;
                return 0;
            });
        });

        const showModal = ref(false);
        const formBarang = ref({ kode: '', nama: '', kategori: '', ukuran: 'All Size', harga: '', stok: '' });
        
        const showEditModal = ref(false);
        const formEditBarang = ref({ kode: '', nama: '', kategori: '', ukuran: 'All Size', harga: '', stok: '' });

        const tambahBarang = async () => {
            if (inventaris.value.find(i => i.kode === formBarang.value.kode)) {
                return alert('Kode barang sudah ada!');
            }
            await fetch('api_inventaris.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    kode: formBarang.value.kode,
                    nama: formBarang.value.nama,
                    kategori: formBarang.value.kategori,
                    ukuran: formBarang.value.ukuran || 'All Size',
                    harga: parseInt(formBarang.value.harga),
                    stok: parseInt(formBarang.value.stok)
                })
            });
            formBarang.value = { kode: '', nama: '', kategori: '', ukuran: 'All Size', harga: '', stok: '' };
            showModal.value = false;
            muatDataDatabase();
            alert('Barang berhasil ditambahkan ke Database!');
        };

        const bukaEditModal = (item) => {
            formEditBarang.value = { ...item, ukuran: item.ukuran || 'All Size' };
            showEditModal.value = true;
        };

        const bukaEditModalDariWarning = (item) => {
            showModalPeringatan.value = false;
            bukaEditModal(item);
        };

        const simpanEditBarang = async () => {
            await fetch('api_inventaris.php', {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    kode: formEditBarang.value.kode,
                    nama: formEditBarang.value.nama,
                    kategori: formEditBarang.value.kategori,
                    ukuran: formEditBarang.value.ukuran,
                    harga: parseInt(formEditBarang.value.harga),
                    stok: parseInt(formEditBarang.value.stok)
                })
            });
            showEditModal.value = false;
            muatDataDatabase();
            alert('Data berhasil diperbarui di Database!');
        };

        const hapusBarang = async (kode) => {
            const item = inventaris.value.find(p => p.kode === kode);
            if (item && confirm(`Yakin ingin menghapus "${item.nama}"?`)) {
                await fetch(`api_inventaris.php?kode=${kode}`, { method: 'DELETE' });
                muatDataDatabase();
            }
        };

        const keHalaman = (url) => { window.location.href = url; };
        const logout = () => {
            if (confirm('Apakah Anda yakin ingin keluar?')) {
                sessionStorage.removeItem('userRole');
                window.location.href = 'logout.php'; 
            }
        };

        return {
            userRole, profilData, inventaris, barangPeringatan, daftarKategori, daftarUkuran,
            opsiWaktu, filterWaktu, showModalKategori, kategoriBaru, tambahKategoriBaru, hapusKategori,
            filterKategori, filterUkuran, urutan, resetFilter, inventarisTersaring,
            showModalPeringatan, getStatusText, getStatusClass,
            showModal, formBarang, showEditModal, formEditBarang,
            tambahBarang, hapusBarang, bukaEditModal, bukaEditModalDariWarning, simpanEditBarang,
            keHalaman, logout
        };
    }
}).mount('#app');