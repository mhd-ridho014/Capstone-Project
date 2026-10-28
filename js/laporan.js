const { createApp, ref, computed, onMounted } = Vue;

createApp({
    setup() {
        const savedRole = sessionStorage.getItem('userRole');
        
        if (!savedRole) {
            window.location.href = 'index.php';
        }

        const userRole = ref(savedRole);

        const dbProfilKey = `db_profil_${userRole.value}`;
        const dbProfil = localStorage.getItem(dbProfilKey);
        const profilData = ref(dbProfil ? JSON.parse(dbProfil) : { nama: userRole.value, foto: '' });

        const dataLaporan = ref([]);

        const muatDataLaporan = async () => {
            try {
                const response = await fetch('api_laporan.php');
                dataLaporan.value = await response.json();
            } catch (error) {
                console.error('Gagal memuat laporan:', error);
            }
        };

        onMounted(() => {
            muatDataLaporan();
            setInterval(muatDataLaporan, 5000);
        });

        const filterTanggalMulai = ref('');
        const filterTanggalAkhir = ref('');
        const urutanLaporan = ref('terbaru');

        const setHariIni = () => {
            const today = new Date();
            const yyyy = today.getFullYear();
            const mm = String(today.getMonth() + 1).padStart(2, '0');
            const dd = String(today.getDate()).padStart(2, '0');
            const formattedDate = `${yyyy}-${mm}-${dd}`;
            
            filterTanggalMulai.value = formattedDate;
            filterTanggalAkhir.value = formattedDate;
        };

        const resetFilterTanggal = () => {
            filterTanggalMulai.value = '';
            filterTanggalAkhir.value = '';
        };

        const parseTanggalTrx = (str) => {
            if (!str) return null;
            const [tglPart, waktuPart] = str.split(' ');
            if (!tglPart) return null;
            const [hari, bulan, tahun] = tglPart.split('/');
            const [jam, menit] = (waktuPart || '00:00').split(':');
            return new Date(tahun, bulan - 1, hari, jam || 0, menit || 0);
        };

        const laporanHarian = computed(() => {
            let filtered = dataLaporan.value.filter(trx => {
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

            return filtered.sort((a, b) => {
                const dateA = parseTanggalTrx(a.tanggal) || new Date(0);
                const dateB = parseTanggalTrx(b.tanggal) || new Date(0);

                if (urutanLaporan.value === 'terbaru') {
                    return dateB - dateA;
                } else {
                    return dateA - dateB;
                }
            });
        });

        
        const formatRincian = (str) => {
            if (!str) return '-';
            let rincian = str;
            let via = '';
            
            
            if (rincian.includes('[Via: ')) {
                const parts = rincian.split('[Via: ');
                rincian = parts[0].trim();
                via = ' [Via: ' + parts[1];
            }

            const blocks = rincian.includes('\n\n') ? rincian.split('\n\n') : rincian.split(', ');
            
            const hasilFormatted = blocks.map(block => {
                let text = block;
                if (text.includes('\t\t')) {
                    text = text.split('\t\t')[0].trim();
                } else {
                    const rpMatches = text.match(/Rp\s*[\d\.]+/g);
                    if (rpMatches && rpMatches.length > 1) {
                        const lastRp = rpMatches[rpMatches.length - 1];
                        const lastIndex = text.lastIndexOf(lastRp);
                        if(lastIndex !== -1) {
                            text = text.substring(0, lastIndex) + text.substring(lastIndex + lastRp.length);
                        }
                    }
                }
                return text.replace(/\n/g, ' ').trim();
            });

            return hasilFormatted.join(', ') + via;
        };

        const statistikProduk = computed(() => {
            const map = {};
            laporanHarian.value.forEach(trx => {
                let items = trx.items ? [...trx.items] : [];
                let rincianBersih = trx.produk || '';
                if (rincianBersih.includes('[Via: ')) {
                    rincianBersih = rincianBersih.split('[Via: ')[0].trim();
                }

                if (items.length === 0 && rincianBersih) {
                    const blocks = rincianBersih.includes('\n\n') ? rincianBersih.split('\n\n') : [rincianBersih];
                    items = blocks.map(block => {
                        let nama = block.trim();
                        let qty = 1;

                        const matchQty = nama.match(/(\d+)\s*x\s*Rp/);
                        if (matchQty) {
                            qty = parseInt(matchQty[1]) || 1;
                        }

                        const matchUkuran = nama.match(/\((.*?)\)/);
                        if (matchUkuran) {
                            nama = nama.split('(')[0].trim();
                        }

                        return { nama, qty };
                    });
                }

                items.forEach(i => {
                    const cleanName = (i.nama || 'Produk').trim();
                    if (!map[cleanName]) {
                        map[cleanName] = { nama: cleanName, totalQty: 0 };
                    }
                    map[cleanName].totalQty += (i.qty || 1);
                });
            });

            return Object.values(map);
        });

        const totalPendapatanHariIni = computed(() => laporanHarian.value.reduce((t, trx) => t + trx.total, 0));
        const totalBarangTerjualHariIni = computed(() => laporanHarian.value.reduce((t, trx) => t + (trx.totalQty || 0), 0));

        const pendapatanPerMetode = computed(() => {
            const rekap = {};
            laporanHarian.value.forEach(trx => {
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

        const showStruk = ref(false);
        const strukData = ref({});

        const cetakUlang = (laporan) => {
            let metodeBayar = 'Tunai (Cash)';
            let rincianBersih = laporan.produk;

            if (laporan.produk && laporan.produk.includes('[Via: ')) {
                const parts = laporan.produk.split('[Via: ');
                rincianBersih = parts[0].trim();
                metodeBayar = parts[1].replace(']', '').trim();
                if (metodeBayar === 'Tunai') metodeBayar = 'Tunai (Cash)';
            }

            let itemsList = laporan.items ? [...laporan.items] : [];

            if (itemsList.length === 0 && rincianBersih) {
                const blocks = rincianBersih.includes('\n\n') ? rincianBersih.split('\n\n') : [rincianBersih];

                itemsList = blocks.map(block => {
                    let nama = block.trim();
                    let ukuran = 'All Size';
                    let qty = 1;
                    let harga = 0;

                    const matchQty = nama.match(/(\d+)\s*x\s*Rp/);
                    if (matchQty) {
                        qty = parseInt(matchQty[1]) || 1;
                    }

                    const matchHarga = nama.match(/Rp\s*([\d\.]+)/g);
                    if (matchHarga && matchHarga.length > 0) {
                        harga = parseInt(matchHarga[matchHarga.length - 1].replace(/[^0-9]/g, '')) || 0;
                    }

                    const matchUkuran = nama.match(/\((.*?)\)/);
                    if (matchUkuran) {
                        ukuran = matchUkuran[1];
                        nama = nama.split('(')[0].trim();
                    }

                    if (harga === 0) {
                        harga = Math.round(laporan.total / (qty || 1));
                    }

                    return { nama, ukuran, qty, harga };
                });
            }

            strukData.value = {
                tanggal: laporan.tanggal,
                ref: laporan.ref,
                items: itemsList,
                total: laporan.total,
                metode: metodeBayar,
                tunai: laporan.tunai || laporan.total,
                kembalian: laporan.kembalian || 0
            };
            showStruk.value = true;
        };

        const tutupStruk = () => {
            showStruk.value = false;
        };

        const cetakStruk = () => {
            window.print();
        };

        const resetLaporan = async () => {
            if (confirm('Yakin ingin mereset seluruh laporan penjualan dari database?')) {
                await fetch('api_laporan.php', { method: 'DELETE' });
                muatDataLaporan();
                alert('Laporan berhasil direset.');
            }
        };

        const exportExcel = () => {
            let csvContent = "data:text/csv;charset=utf-8,\uFEFF";
            csvContent += "Tanggal;No Referensi;Produk;Total Nilai\r\n";

            laporanHarian.value.forEach(row => {
                let tanggal = `"${row.tanggal}"`;
                let referensi = `"${row.ref}"`;
                let produk = `"${formatRincian(row.produk).replace(/"/g, '""')}"`;
                let total = row.total;

                csvContent += `${tanggal};${referensi};${produk};${total}\r\n`;
            });

            const encodedUri = encodeURI(csvContent);
            const link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", "laporan_penjualan_jelova.csv");
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        };

        const keHalaman = (url) => { window.location.href = url; };
        
        const logout = () => {
            if(confirm('Apakah Anda yakin ingin keluar?')) {
                sessionStorage.removeItem('userRole');
                window.location.href = 'logout.php'; 
            }
        };

        return {
            userRole, profilData, filterTanggalMulai, filterTanggalAkhir, urutanLaporan,
            setHariIni, resetFilterTanggal, formatRincian,
            laporanHarian, statistikProduk, totalPendapatanHariIni, totalBarangTerjualHariIni,
            pendapatanPerMetode, showModalPendapatan, bukaModalPendapatan, tutupModalPendapatan,
            showStruk, strukData, cetakUlang, tutupStruk, cetakStruk,
            resetLaporan, exportExcel, keHalaman, logout
        };
    }
}).mount('#app');