<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Jelova Boutique</title>
    
    
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        input::-ms-reveal,
        input::-ms-clear {
            display: none !important;
        }
    </style>
</head>

<body class="login-page">

    <div id="app" v-cloak>
        <div class="login-container">
            <div style="text-align: center; margin-bottom: 15px;">
                <img src="img/Logo transparan.png" alt="Logo Jelova" style="max-width: 120px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            </div>
            
            <h2>Jelova Boutique</h2>
            <p class="subtitle">Silakan masuk sesuai peran Anda</p>

            <form @submit.prevent="handleLogin">
                <div class="form-group">
                    <label for="role">Masuk Sebagai</label>
                    <select id="role" v-model="role" required>
                        <option value="admin">Admin (Pemilik/Pengelola)</option>
                        <option value="kasir">Kasir (Staf Toko)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" v-model="username" placeholder="Masukkan username" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <div style="position: relative;">
                        <input :type="showPassword ? 'text' : 'password'" id="password" v-model="password" placeholder="Masukkan password" required style="padding-right: 40px;">
                        <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'" 
                           @click="showPassword = !showPassword" 
                           style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #888; font-size: 1.1rem;"></i>
                    </div>
                </div>

                <div style="text-align: right; margin-top: -10px; margin-bottom: 20px;">
                    <a href="#" @click.prevent="showLupaPassword = true" style="color: #c0a080; font-size: 0.85rem; text-decoration: none; font-weight: 500;">Lupa Password?</a>
                </div>

                <button type="submit">Masuk</button>

                <div v-if="errorMessage" class="error-message">
                    {{ errorMessage }}
                </div>
            </form>
        </div>

        
        <div class="modal-overlay" v-if="showLupaPassword">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Ganti Password</h3>
                    <button class="close-btn" @click="tutupModalReset">&times;</button>
                </div>

                <div v-if="resetStep === 1">
                    <p style="font-size: 0.9rem; color: #666; margin-bottom: 15px; line-height: 1.5;">
                        Masukkan username Anda. Sistem akan mengarahkan Anda ke WhatsApp Owner untuk meminta kode persetujuan reset password.
                    </p>
                    <form @submit.prevent="kirimWA">
                        <div class="form-group">
                            <label>Username Anda</label>
                            <input type="text" v-model="resetData.username" placeholder="Contoh: admin atau kasir" required>
                        </div>
                        <button type="submit" style="width: 100%; padding: 12px; background-color: #25d366; color: white; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 10px;">
                            <i class="fab fa-whatsapp" style="font-size: 1.2rem;"></i> Minta Kode via WA
                        </button>
                    </form>
                </div>

                <div v-if="resetStep === 2">
                    <p style="font-size: 0.9rem; color: #666; margin-bottom: 15px; line-height: 1.5;">
                        Pesan telah disiapkan di WhatsApp Anda. Jika Owner menyetujui, masukkan 6 digit kode tersebut di bawah ini.
                    </p>
                    <form @submit.prevent="verifikasiOTP">
                        <div class="form-group">
                            <label>Kode Verifikasi</label>
                            <input type="text" v-model="inputOTP" placeholder="Masukkan 6 digit kode" required>
                        </div>
                        <button type="submit" style="width: 100%; padding: 12px; background-color: #333; color: white; border: none; border-radius: 8px; font-weight: bold; cursor: pointer;">
                            Verifikasi Kode
                        </button>
                    </form>
                </div>

                <div v-if="resetStep === 3">
                    <p style="font-size: 0.9rem; color: #2e7d32; margin-bottom: 15px; font-weight: bold;">
                        <i class="fas fa-check-circle"></i> Kode Valid! Silakan buat password baru Anda.
                    </p>
                    <form @submit.prevent="simpanPasswordBaru">
                        <div class="form-group">
                            <label>Password Baru</label>
                            <div style="position: relative;">
                                <input :type="showNewPassword ? 'text' : 'password'" v-model="resetData.newPassword" placeholder="Minimal 6 karakter" required style="padding-right: 40px;">
                                <i :class="showNewPassword ? 'fas fa-eye-slash' : 'fas fa-eye'" 
                                   @click="showNewPassword = !showNewPassword" 
                                   style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #888; font-size: 1.1rem;"></i>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Konfirmasi Password Baru</label>
                            <div style="position: relative;">
                                <input :type="showConfirmPassword ? 'text' : 'password'" v-model="resetData.confirmPassword" placeholder="Ketik ulang password baru" required style="padding-right: 40px;">
                                <i :class="showConfirmPassword ? 'fas fa-eye-slash' : 'fas fa-eye'" 
                                   @click="showConfirmPassword = !showConfirmPassword" 
                                   style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #888; font-size: 1.1rem;"></i>
                            </div>
                        </div>
                        <button type="submit" style="width: 100%; padding: 12px; background-color: #c0a080; color: white; border: none; border-radius: 8px; font-weight: bold; cursor: pointer;">
                            Simpan Password Baru
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="success-overlay" :class="{ 'show': showSuccess }">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="success-text">
                {{ successMessage }}
            </div>
            <div class="logout-spinner">
                <i class="fas fa-circle-notch fa-spin fa-2x"></i>
            </div>
        </div>
    </div>

    
    <script src="js/login.js"></script>
</body>
</html>