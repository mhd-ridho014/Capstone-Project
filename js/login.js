const { createApp, ref } = Vue;

createApp({
    setup() {
        if (!localStorage.getItem('pass_admin')) {
            localStorage.setItem('pass_admin', 'butik123');
        }
        if (!localStorage.getItem('pass_kasir')) {
            localStorage.setItem('pass_kasir', 'kasir123');
        }

        const role = ref('admin'); 
        const username = ref('');
        const password = ref('');
        const errorMessage = ref('');
        
        const showSuccess = ref(false);
        const successMessage = ref('');

        const showPassword = ref(false);
        const showNewPassword = ref(false);
        const showConfirmPassword = ref(false);

        const showLupaPassword = ref(false);
        const resetStep = ref(1); 
        const kodeOTP = ref(''); 
        const inputOTP = ref(''); 
        
        const nomorWAOwner = 'isi dengan nomer uji coba'; 

        const resetData = ref({
            username: '',
            newPassword: '',
            confirmPassword: ''
        });

        const handleLogin = () => {
            errorMessage.value = '';
            
            const passAdminAktif = localStorage.getItem('pass_admin');
            const passKasirAktif = localStorage.getItem('pass_kasir');

            if (role.value === 'admin') {
                if (username.value === 'admin' && password.value === passAdminAktif) {
                    prosesLoginSukses('admin', 'Selamat datang, Admin Jelova!');
                } else {
                    errorMessage.value = 'Username atau Password Admin salah.';
                }
            } else if (role.value === 'kasir') {
                if (username.value === 'kasir' && password.value === passKasirAktif) {
                    prosesLoginSukses('kasir', 'Selamat datang, Kasir Jelova! Menyiapkan POS...');
                } else {
                    errorMessage.value = 'Username atau Password Kasir salah.';
                }
            }
        };

        const prosesLoginSukses = (userRole, pesan) => {
            sessionStorage.setItem('userRole', userRole); 
            successMessage.value = pesan;                 
            showSuccess.value = true;                     
            
            setTimeout(() => {
                if (userRole === 'kasir') {
                    window.location.href = 'kasir.php'; // Mengarah ke .php
                } else {
                    window.location.href = 'dashboard.php'; // Mengarah ke .php
                }
            }, 2000);
        };

        const tutupModalReset = () => {
            showLupaPassword.value = false;
            resetStep.value = 1;
            kodeOTP.value = '';
            inputOTP.value = '';
            showPassword.value = false;
            showNewPassword.value = false;
            showConfirmPassword.value = false;
            resetData.value = { username: '', newPassword: '', confirmPassword: '' };
        };

        const kirimWA = () => {
            if (resetData.value.username !== 'admin' && resetData.value.username !== 'kasir') {
                alert('Username tidak terdaftar di sistem kami!');
                return;
            }
            
            kodeOTP.value = Math.floor(100000 + Math.random() * 900000).toString();
            
            const pesan = `Halo Owner Jelova Boutique,%0A%0ASaya pengguna dengan username *${resetData.value.username}* meminta izin untuk mereset password.%0A%0AJika Anda mengizinkan, mohon balas dengan memberikan kode verifikasi ini kepada saya:%0A*${kodeOTP.value}*`;
            
            window.open(`https://wa.me/${nomorWAOwner}?text=${pesan}`, '_blank');
            resetStep.value = 2;
        };

        const verifikasiOTP = () => {
            if (inputOTP.value === kodeOTP.value) {
                resetStep.value = 3; 
                inputOTP.value = '';
            } else {
                alert('Kode verifikasi salah! Silakan periksa kembali pesan dari Owner.');
            }
        };

        const simpanPasswordBaru = () => {
            if (resetData.value.newPassword.length < 6) {
                alert('Password baru harus terdiri dari minimal 6 karakter!');
                return;
            }

            if (resetData.value.newPassword !== resetData.value.confirmPassword) {
                alert('Konfirmasi password tidak cocok dengan password baru!');
                return;
            }

            if (resetData.value.username === 'admin') {
                localStorage.setItem('pass_admin', resetData.value.newPassword);
            } else if (resetData.value.username === 'kasir') {
                localStorage.setItem('pass_kasir', resetData.value.newPassword);
            }

            alert(`Berhasil! Password untuk akun "${resetData.value.username}" telah diubah.\nSilakan login menggunakan password baru Anda.`);
            tutupModalReset();
        };

        return {
            role, username, password, errorMessage,
            showSuccess, successMessage, 
            showPassword, showNewPassword, showConfirmPassword,
            showLupaPassword, resetData, resetStep, inputOTP,
            handleLogin, tutupModalReset, kirimWA, verifikasiOTP, simpanPasswordBaru
        };
    }
}).mount('#app');