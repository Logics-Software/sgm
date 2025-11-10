<?php
$title = 'Login';
require __DIR__ . '/../layouts/header.php';
?>

<div class="login-container">
    <div class="login-card">
        <div class="login-card-header text-center">
            <div class="login-logo-wrapper">
                <span class="login-logo">
                    <img src="<?= htmlspecialchars($baseUrl) ?>/assets/images/logo-64.png" alt="PBF Logo" width="56" height="56">
                </span>
            </div>
            <h1 class="login-title mb-1">Login System</h1>
        </div>
        <div class="card-body">
            <form method="POST" action="/login" class="login-form needs-validation" novalidate>
                <div class="form-floating mb-3">
                    <input type="text" class="form-control" id="username" name="username" required autofocus placeholder="Masukkan username">
                    <label for="username">Username</label>
                    <div class="invalid-feedback">Username wajib diisi.</div>
                </div>

                <div class="form-floating mb-3 position-relative password-field">
                    <input type="password" class="form-control" id="password" name="password" required placeholder="Masukkan password">
                    <label for="password">Password</label>
                    <button type="button" class="password-toggle" data-target="password" aria-label="Tampilkan password">
                        <span class="password-toggle-icon-show"><?= icon('eye', '', 18) ?></span>
                        <span class="password-toggle-icon-hide d-none"><?= icon('eye-slash', '', 18) ?></span>
                    </button>
                    <div class="invalid-feedback">Password wajib diisi.</div>
                </div>

                <button type="submit" class="btn btn-gradient w-100 mt-3 mb-3">
                    <?= icon('login', 'me-2', 20) ?> Login
                </button>
            </form>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

