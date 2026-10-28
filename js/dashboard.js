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

        const inventaris = ref([]);
        const dataLaporan = ref([]);

        const muatDataServer = async () => {
            try {
                const resInv = await fetch('api_inventaris.php');
                inventaris.value = await resInv.json();

                const resLap = await fetch('api_laporan.php');
                dataLaporan.value = await resLap.json();
            } catch (error) {
                console.error('Gagal memuat data dashboard:', error);
            }
        };

        onMounted(() => {
            muatDataServer();
            setInterval(muatDataServer, 5000);
        });

        const filterTanggalMulai = ref('');
        const filterTanggalAkhir = ref('');

        const parseTanggalTrx = (str) => {
            if (!str) return null;
            const [tglPart, waktuPart] = str.split(' ');
            if (!tglPart) return null;
            const [hari, bulan, tahun] = tglPart.split('/');
            const [jam, menit] = (waktuPart || '00:00').split(':');
            return new Date(tahun, bulan - 1, hari, jam || 0, menit || 0);
        };

        const laporanTersaring = computed(() => {
            return dataLaporan.value.filter(trx => {
                const trxDate = parseTanggalTrx(trx.tanggal);
                if (!trxDate) return true;

                let cocokMulai = true;
                let cocokAkhir = true;

                if (filterTanggalMulai.value) {
                    const start = new Date(filterTanggalMulai.value);
                    start.setHours(0, 0, 0, 0);
                    cocokMulai = trxDate >= start;
                }

                if (filterTanggalAkhir.value) {
                    const end = new Date(filterTanggalAkhir.value);
                    end.setHours(23, 59, 59, 999);
                    cocokAkhir = trxDate <= end;
                }

                return cocokMulai && cocokAkhir;
            });
        });

        const resetFilterTanggal = () => {
            filterTanggalMulai.value = '';
            filterTanggalAkhir.value = '';
        };

        const totalPendapatan = computed(() => laporanTersaring.value.reduce((t, trx) => t + trx.total, 0));
        
        const totalSemuaBarangTerjual = computed(() => {
            return laporanTersaring.value.reduce((total, trx) => total + (trx.totalQty || 0), 0);
        });

        const barangMenipis = computed(() => {
            return inventaris.value.filter(item => item.stok <= 10);
        });

        const transaksiTerbaru = computed(() => {
            return [...laporanTersaring.value].slice(0, 5);
        });

        const pendapatanPerMetode = computed(() => {
            const rekap = {};
            laporanTersaring.value.forEach(trx => {
                let metode = 'Tunai (Cash)';
                const produkStr = trx.produk || '';
                if (produkStr.includes('[Via: ')) {
                    metode = produkStr.split('[Via: ')[1].replace(']', '').trim();
                    if (metode === 'Tunai') metode = 'Tunai (Cash)';
                }
                if (!rekap[metode]) {
                    rekap[metode] = 0;
                }
                rekap[metode] += (trx.total || 0);
            });
            return rekap;
        });

        const showModalPendapatan = ref(false);

        const bukaModalPendapatan = () => {
            showModalPendapatan.value = true;
        };

        const tutupModalPendapatan = () => {
            showModalPendapatan.value = false;
        };

        const keHalaman = (url) => {
            window.location.href = url;
        };

        const logout = () => {
            if (confirm('Apakah Anda yakin ingin keluar?')) {
                sessionStorage.removeItem('userRole');
                window.location.href = 'logout.php'; 
            }
        };

        return {
            userRole, profilData, inventaris, dataLaporan,
            filterTanggalMulai, filterTanggalAkhir, resetFilterTanggal,
            totalPendapatan, totalSemuaBarangTerjual, barangMenipis, transaksiTerbaru,
            pendapatanPerMetode, showModalPendapatan, bukaModalPendapatan, tutupModalPendapatan,
            keHalaman, logout
        };
    }
}).mount('#app');