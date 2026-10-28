const { createApp, ref, computed, onMounted } = Vue;

createApp({
    setup() {
        const savedRole = sessionStorage.getItem('userRole');
        
        if (!savedRole) {
            window.location.href = 'index.php';
        } else if (savedRole === 'admin') {
            window.location.href = 'dashboard.php';
        }
        
        const userRole = ref(savedRole);

        const dbProfilKey = `db_profil_${userRole.value}`;
        const dbProfil = localStorage.getItem(dbProfilKey);
        const profilData = ref(dbProfil ? JSON.parse(dbProfil) : { nama: userRole.value, foto: '' });

        const defaultKategori = [
            'Kemeja', 'Blouse', 'Cardigan', 'Sweater', 'Crop Top', 'Vest',
            'Blazer', 'Jacket', 'Inner Manset', 'Tunik', 'Gamis', 'Outer Brukat',
            'Piyama + Dress', 'Celana Jeans', 'Celana Kain', 'Rok', 'Hijab',
            'Sandal', '2in1', 'Kaos'
        ];

        const daftarUkuran = ['All Size', 'S', 'M', 'L', 'XL', 'XXL'];

        const dbKategori = localStorage.getItem('db_kategori');
        const daftarKategori = ref(dbKategori ? JSON.parse(dbKategori) : defaultKategori);

        const inventaris = ref([]);
        const dataLaporan = ref([]);

        const muatDataServer = async () => {
            try {
                const resInv = await fetch('api_inventaris.php');
                inventaris.value = await resInv.json();

                const resLap = await fetch('api_laporan.php');
                dataLaporan.value = await resLap.json();
            } catch (error) {
                console.error('Gagal memuat data:', error);
            }
        };

        onMounted(() => {
            muatDataServer();
        });

        const filterKategori = ref('Semua');
        const filterUkuran = ref('Semua');

        const resetFilter = () => {
            filterKategori.value = 'Semua';
            filterUkuran.value = 'Semua';
        };

        const inventarisTersaring = computed(() => {
            return inventaris.value.filter(item => {
                const cocokKategori = filterKategori.value === 'Semua' || item.kategori === filterKategori.value;
                const cocokUkuran = filterUkuran.value === 'Semua' || (item.ukuran || 'All Size') === filterUkuran.value;
                return cocokKategori && cocokUkuran;
            });
        });

        const keranjang = ref([]);
        const uangTunai = ref(null);
        const metodePembayaran = ref('Tunai');
        
        const showStruk = ref(false);
        const strukData = ref({});

        const totalBelanja = computed(() => keranjang.value.reduce((t, item) => t + (item.harga * item.qty), 0));
        const kembalian = computed(() => uangTunai.value - totalBelanja.value);

        const tambahKeKeranjang = (produk) => {
            if (produk.stok <= 0) return alert('Stok barang habis!');
            const exist = keranjang.value.find(i => i.kode === produk.kode);
            if (exist) {
                if(exist.qty < produk.stok) exist.qty++;
                else alert('Kuantitas melebihi sisa stok!');
            } else keranjang.value.push({ ...produk, qty: 1 });
        };

        const hapusDariKeranjang = (i) => keranjang.value.splice(i, 1);
        
        const cekStok = (item) => {
            if (item.qty > item.stok) {
                alert(`Stok tidak cukup! Sisa stok untuk ${item.nama} hanya ${item.stok} pcs.`);
                item.qty = item.stok; 
            } else if (item.qty < 1 || typeof item.qty !== 'number') {
                item.qty = 1; 
            }
        };

        const prosesPembayaran = async () => {
            for (let item of keranjang.value) {
                const prod = inventaris.value.find(p => p.kode === item.kode);
                if (prod) {
                    prod.stok -= item.qty;
                    await fetch('api_inventaris.php', {
                        method: 'PUT',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            kode: prod.kode,
                            nama: prod.nama,
                            kategori: prod.kategori,
                            ukuran: prod.ukuran,
                            harga: prod.harga,
                            stok: prod.stok
                        })
                    });
                }
            }

            const rincianHtml = keranjang.value.map(i => `${i.nama} (${i.ukuran || 'All Size'})\n${i.qty} x Rp ${i.harga.toLocaleString('id-ID')} \t\t Rp ${(i.qty * i.harga).toLocaleString('id-ID')}`).join('\n\n');
            const rincianLengkap = rincianHtml + `\n[Via: ${metodePembayaran.value}]`;

            const now = new Date();
            const tglStr = now.getDate().toString().padStart(2, '0');
            const blnStr = (now.getMonth() + 1).toString().padStart(2, '0');
            const thnStr = now.getFullYear();
            const jamStr = now.getHours().toString().padStart(2, '0');
            const mntStr = now.getMinutes().toString().padStart(2, '0');
            
            const tanggalFormat = `${tglStr}/${blnStr}/${thnStr} ${jamStr}:${mntStr}`;
            const tanggalKode = `${tglStr}${blnStr}${thnStr}`;
            const jamKode = `${jamStr}${mntStr}`;

            const tanggalHariIni = `${tglStr}/${blnStr}/${thnStr}`;
            const transaksiHariIni = dataLaporan.value.filter(trx => trx.tanggal && trx.tanggal.startsWith(tanggalHariIni));
            const nomorUrutHarian = (transaksiHariIni.length + 1).toString().padStart(2, '0');

            const refBaru = `TRX${nomorUrutHarian}-${tanggalKode}-${jamKode}`;
            const totalQtyTransaksi = keranjang.value.reduce((sum, item) => sum + item.qty, 0);

            const dataBaruTrx = {
                tanggal: tanggalFormat, 
                ref: refBaru, 
                produk: rincianLengkap, 
                totalQty: totalQtyTransaksi, 
                total: totalBelanja.value,
                tunai: metodePembayaran.value === 'Tunai' ? (uangTunai.value || 0) : 0,
                kembalian: metodePembayaran.value === 'Tunai' ? (kembalian.value || 0) : 0
            };

            await fetch('api_laporan.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(dataBaruTrx)
            });

            strukData.value = {
                tanggal: tanggalFormat,
                ref: refBaru,
                items: [...keranjang.value],
                total: totalBelanja.value,
                metode: metodePembayaran.value,
                tunai: uangTunai.value || 0,
                kembalian: kembalian.value || 0
            };
            
            showStruk.value = true;
        };

        const tutupStruk = () => {
            showStruk.value = false;
            keranjang.value = []; 
            uangTunai.value = null;
            metodePembayaran.value = 'Tunai';
            muatDataServer(); 
        };

        const cetakStruk = () => {
            window.print();
        };

        const keHalaman = (url) => { window.location.href = url; };
        
        const logout = () => {
            if(confirm('Apakah Anda yakin ingin keluar?')) {
                sessionStorage.removeItem('userRole');
                window.location.href = 'logout.php'; 
            }
        };

        return {
            userRole, profilData, inventaris, daftarKategori, daftarUkuran,
            filterKategori, filterUkuran, resetFilter, inventarisTersaring,
            keranjang, uangTunai, metodePembayaran,
            totalBelanja, kembalian, showStruk, strukData,
            tambahKeKeranjang, hapusDariKeranjang, cekStok, prosesPembayaran,
            tutupStruk, cetakStruk, keHalaman, logout
        };
    }
}).mount('#app');