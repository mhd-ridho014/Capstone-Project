<nav class="navbar">
    <div class="navbar-brand" style="display: flex; align-items: center; gap: 10px;">
        <img src="img/Logo transparan.png" alt="Logo Jelova" style="height: 40px; border-radius: 5px;">
        <h2 style="margin: 0; color: #c0a080;">Jelova Boutique</h2>
    </div>
    
    <ul class="nav-links">
        <li v-if="userRole === 'admin'" class="<?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>" @click="keHalaman('dashboard.php')">
            <i class="fas fa-home"></i> Dashboard
        </li>
        <li v-if="userRole === 'kasir'" class="<?php echo ($current_page == 'kasir.php') ? 'active' : ''; ?>" @click="keHalaman('kasir.php')">
            <i class="fas fa-calculator"></i> Kasir
        </li>
        <li v-if="userRole === 'admin'" class="<?php echo ($current_page == 'inventaris.php') ? 'active' : ''; ?>" @click="keHalaman('inventaris.php')">
            <i class="fas fa-box"></i> Inventaris
        </li>
        <li class="<?php echo ($current_page == 'laporan.php') ? 'active' : ''; ?>" @click="keHalaman('laporan.php')">
            <i class="fas fa-chart-line"></i> Laporan
        </li>
    </ul>

    <div class="navbar-actions">
        <div class="user-info" @click="keHalaman('profil.php')" style="cursor: pointer;" title="Buka Profil Saya">
            <span style="text-transform: capitalize;">{{ profilData.nama || userRole }}</span>
            <img v-if="profilData.foto" :src="profilData.foto" style="width: 35px; height: 35px; border-radius: 50%; object-fit: cover; border: 2px solid #c0a080;">
            <i v-else class="fas fa-user-circle fa-2x" style="color: #c0a080;"></i>
        </div>
        <div class="logout-btn" @click="logout">
            <i class="fas fa-sign-out-alt"></i> Keluar
        </div>
    </div>
</nav>