<?php
$title = 'Dashboard';
require __DIR__ . '/../layouts/header.php';
?>

<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="text-gradient mb-1">Dashboard</h2>
                <p class="text-muted mb-0">Selamat datang, <strong><?= htmlspecialchars($user['namalengkap']) ?></strong>!</p>
            </div>
            <div>
                <span class="badge bg-info text-dark"><?= ucfirst($user['role']) ?></span>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <div class="card dashboard-card primary">
            <div class="card-body">
                <h5>Total Users</h5>
                <h2 class="text-primary mb-0"><?= $totalUsers ?></h2>
                <small class="text-muted">Pengguna terdaftar</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-3">
        <div class="card dashboard-card info">
            <div class="card-body">
                <h5>Role</h5>
                <h4 class="text-info mb-0"><?= ucfirst($user['role']) ?></h4>
                <small class="text-muted">Hak akses Anda</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-3">
        <div class="card dashboard-card success">
            <div class="card-body">
                <h5>Status</h5>
                <h4 class="text-success mb-0"><?= ucfirst($user['status']) ?></h4>
                <small class="text-muted">Status akun</small>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

