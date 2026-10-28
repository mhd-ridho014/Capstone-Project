<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - Jelova Boutique</title>
    
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .profile-container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        .avatar-upload {
            position: relative;
            width: 130px;
            height: 130px;
            margin: 0 auto 15px auto;
        }
        .avatar-preview {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #c0a080;
            background-color: #f5f5f5;
        }
        .avatar-edit-btn {
            position: absolute;
            bottom: 5px;
            right: 5px;
            background: #c0a080;
            color: #fff;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 2px 6px rgba(0,0,0,0.2);
            transition: background 0.2s;
        }
        .avatar-edit-btn:hover {
            background: #a88868;
        }
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        @media (max-width: 600px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body class="dashboard-page">

    <div id="app" v-cloak>
        <?php 
        $current_page = 'profil.php';
        include 'sidebar.php'; 
        ?>

        <main class="main-content">
            <header class="page-header">
                <h2>Pengaturan Profil</h2>
            </header>

            <div class="dashboard-content">
                <div class="profile-container">
                    
                    <div style="text-align: center; margin-bottom: 25px;">
                        <div class="avatar-upload">
                            <img :src="profilData.foto || 'https://via.placeholder.com/130?text=Profil'" class="avatar-preview">
                            <label for="fotoInput" class="avatar-edit-btn" title="Ganti Foto Profil">
                                <i class="fas fa-camera"></i>
                            </label>
                            <input type="file" id="fotoInput" @change="unggahFoto" accept="image/*" style="display: none;">
                        </div>

                        
                        <div v-if="profilData.foto" style="margin-top: 10px;">
                            <button type="button" @click="hapusFoto" style="background: none; border: none; color: #d9534f; cursor: pointer; font-size: 0.85rem; font-weight: 600;">
                                <i class="fas fa-trash-alt"></i> Hapus Foto Profil
                            </button>
                        </div>

                        <h3 style="margin: 10px 0 0 0; text-transform: capitalize; color: #333;">{{ profilData.nama || userRole }}</h3>
                        <p style="margin: 5px 0 0; color: #888; font-size: 0.9rem; text-transform: uppercase; font-weight: bold;">
                            HAK AKSES: {{ userRole }}
                        </p>
                    </div>

                    
                    <form @submit.prevent="simpanProfil">
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Username (Akun Login)</label>
                                <input type="text" :value="userRole" disabled style="background-color: #f5f5f5; color: #777;">
                            </div>
                            <div class="form-group">
                                <label>Nama Lengkap Pengguna</label>
                                <input type="text" v-model="profilData.nama" placeholder="Masukkan nama Anda" required>
                            </div>
                            <div class="form-group">
                                <label>Nomor Telepon / WA</label>
                                <input type="text" v-model="profilData.telepon" placeholder="Contoh: 08123456789">
                            </div>
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" v-model="profilData.email" placeholder="Contoh: user@jelova.com">
                            </div>
                        </div>

                        <hr style="border: 0; border-top: 1px solid #eee; margin: 25px 0;">

                        
                        <h4 style="margin-bottom: 15px; color: #333;"><i class="fas fa-lock" style="color: #c0a080;"></i> Ganti Password</h4>
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Password Baru</label>
                                <input type="password" v-model="passwordBaru" placeholder="Biarkan kosong jika tidak diubah">
                            </div>
                            <div class="form-group">
                                <label>Konfirmasi Password Baru</label>
                                <input type="password" v-model="konfirmasiPassword" placeholder="Biarkan kosong jika tidak diubah">
                            </div>
                        </div>

                        <div style="text-align: right; margin-top: 25px;">
                            <button type="submit" class="btn-action primary" style="padding: 12px 25px; font-size: 1rem;">
                                <i class="fas fa-save"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script src="js/profil.js"></script>
</body>
</html>