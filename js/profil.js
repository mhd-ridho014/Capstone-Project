const { createApp, ref } = Vue;

createApp({
    setup() {
        const savedRole = sessionStorage.getItem('userRole');
        
        if (!savedRole) {
            window.location.href = 'index.php';
        }

        const userRole = ref(savedRole);

        const dbProfilKey = `db_profil_${userRole.value}`;
        const dbPassKey = userRole.value === 'admin' ? 'pass_admin' : 'pass_kasir';

        const profilBawaan = {
            nama: userRole.value === 'admin' ? 'Admin Jelova' : 'Kasir Toko',
            telepon: '085122614007',
            email: `${userRole.value}@jelovaboutique.com`,
            foto: ''
        };

        const dbProfil = localStorage.getItem(dbProfilKey);
        const profilData = ref(dbProfil ? JSON.parse(dbProfil) : profilBawaan);

        const passwordBaru = ref('');
        const konfirmasiPassword = ref('');

        const unggahFoto = (e) => {
            const file = e.target.files[0];
            if (file) {
                if (file.size > 2 * 1024 * 1024) {
                    alert('Ukuran foto terlalu besar! Maksimal 2MB.');
                    return;
                }

                const reader = new FileReader();
                reader.onload = (event) => {
                    profilData.value.foto = event.target.result;
                };
                reader.readAsDataURL(file);
            }
        };

        const hapusFoto = () => {
            if (confirm('Yakin ingin menghapus foto profil?')) {
                profilData.value.foto = '';
                localStorage.setItem(dbProfilKey, JSON.stringify(profilData.value));
                alert('Foto profil berhasil dihapus.');
            }
        };

        const simpanProfil = () => {
            if (passwordBaru.value) {
                if (passwordBaru.value.length < 6) {
                    alert('Password baru minimal 6 karakter!');
                    return;
                }
                if (passwordBaru.value !== konfirmasiPassword.value) {
                    alert('Konfirmasi password baru tidak cocok!');
                    return;
                }
                localStorage.setItem(dbPassKey, passwordBaru.value);
                passwordBaru.value = '';
                konfirmasiPassword.value = '';
            }

            localStorage.setItem(dbProfilKey, JSON.stringify(profilData.value));
            alert('Profil berhasil diperbarui!');
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
            userRole, profilData, passwordBaru, konfirmasiPassword,
            unggahFoto, hapusFoto, simpanProfil, keHalaman, logout
        };
    }
}).mount('#app');