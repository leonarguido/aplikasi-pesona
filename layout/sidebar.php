<div id="wrapper">
    <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

        <a class="sidebar-brand d-flex align-items-center justify-content-center" href="index.php">
            <div class="sidebar-brand-icon rotate-n-15">
                <i class="fas fa-box-open"></i>
            </div>
            <div class="sidebar-brand-text mx-3">APLIKASI PESONA</div>
        </a>

        <hr class="sidebar-divider my-0">

        <li class="nav-item active">
            <a class="nav-link" href="index.php">
                <i class="fas fa-fw fa-tachometer-alt"></i>
                <span>Dashboard</span></a>
        </li>

        <hr class="sidebar-divider">

        <?php if($_SESSION['role'] == 'super_admin'): ?>
        <div class="sidebar-heading">Admin Core</div>
        <li class="nav-item">
            <a class="nav-link" href="data_pengguna.php">
                <i class="fas fa-fw fa-users"></i>
                <span>Kelola Pengguna</span></a>
        </li>
        <?php endif; ?>

        <?php if($_SESSION['role'] == 'admin'): ?>
        <div class="sidebar-heading">Inventaris</div>
        <li class="nav-item">
            <a class="nav-link" href="data_barang.php">
                <i class="fas fa-fw fa-boxes"></i>
                <span>Data Barang</span></a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="persetujuan.php">
                <i class="fas fa-fw fa-check-circle"></i>
                <span>Persetujuan Permintaan</span></a>
        </li>
        <?php endif; ?>

        <?php if($_SESSION['role'] == 'user'): ?>
        <div class="sidebar-heading">Permintaan</div>
        <li class="nav-item">
            <a class="nav-link" href="daftar_barang.php">
                <i class="fas fa-fw fa-search"></i>
                <span>Daftar Barang</span></a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="permintaan_saya.php">
                <i class="fas fa-fw fa-history"></i>
                <span>Riwayat Permintaan</span></a>
        </li>
        <?php endif; ?>

        <?php if($_SESSION['role'] == 'pimpinan'): ?>
        <div class="sidebar-heading">Laporan</div>
        <li class="nav-item">
            <a class="nav-link" href="laporan.php">
                <i class="fas fa-fw fa-chart-area"></i>
                <span>Laporan Stok</span></a>
        </li>
        <?php endif; ?>

        <hr class="sidebar-divider d-none d-md-block">

        <div class="text-center d-none d-md-inline">
            <button class="rounded-circle border-0" id="sidebarToggle"></button>
        </div>

    </ul>