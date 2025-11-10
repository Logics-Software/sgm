<?php
$title = 'Detail Order';
$config = require __DIR__ . '/../../config/app.php';
$baseUrl = rtrim($config['base_url'], '/');
if (empty($baseUrl) || $baseUrl === 'http://' || $baseUrl === 'https://') {
    $baseUrl = '/';
}

$currentUser = Auth::user();

require __DIR__ . '/../layouts/header.php';
?>

<div class="row mb-3">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="/orders">Transaksi Order</a></li>
                <li class="breadcrumb-item active">Detail Order</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-0">Order <?= htmlspecialchars($order['noorder']) ?></h4>
                    <small class="text-muted">Tanggal: <?= date('d/m/Y', strtotime($order['tanggalorder'])) ?></small>
                </div>
                <div>
                    <a href="/orders" class="btn btn-secondary btn-sm">Kembali</a>
                    <?php if ($order['status'] === 'order' && (($currentUser['role'] ?? '') !== 'sales' || ($currentUser['kodesales'] ?? '') === $order['kodesales'])): ?>
                    <a href="/orders/edit/<?= urlencode($order['noorder']) ?>" class="btn btn-warning btn-sm">Edit</a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <h6 class="text-uppercase text-muted">Informasi Order</h6>
                        <dl class="row mb-0">
                            <dt class="col-5">No Order</dt>
                            <dd class="col-7"><?= htmlspecialchars($order['noorder']) ?></dd>
                            <dt class="col-5">Status</dt>
                            <dd class="col-7"><span class="badge bg-<?= ($order['status'] === 'faktur') ? 'success' : 'warning' ?>"><?= ucfirst($order['status']) ?></span></dd>
                            <dt class="col-5">Customer</dt>
                            <dd class="col-7"><?= htmlspecialchars($order['namacustomer'] ?? '-') ?></dd>
                            <dt class="col-5">Keterangan</dt>
                            <dd class="col-7"><?= htmlspecialchars($order['keterangan'] ?? '-') ?></dd>
                        </dl>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-uppercase text-muted">Ringkasan</h6>
                        <dl class="row mb-0">
                            <dt class="col-5">Nilai Order</dt>
                            <dd class="col-7">Rp <?= number_format((float)$order['nilaiorder'], 0, ',', '.') ?></dd>
                            <dt class="col-5">Sales</dt>
                            <dd class="col-7"><?= htmlspecialchars($order['namasales'] ?? '-') ?></dd>
                            <dt class="col-5">No Penjualan</dt>
                            <dd class="col-7"><?= htmlspecialchars($order['nopenjualan'] ?? '-') ?></dd>
                            <dt class="col-5">Terakhir Update</dt>
                            <dd class="col-7">
                                <?php if (!empty($order['updated_at'])): ?>
                                <?= date('d/m/Y H:i', strtotime($order['updated_at'])) ?>
                                <?php else: ?>-
                                <?php endif; ?>
                            </dd>
                        </dl>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Kode Barang</th>
                                <th>Nama Barang</th>
                                <th>Jumlah</th>
                                <th>Satuan</th>
                                <th>Harga</th>
                                <th>Diskon</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($details)): ?>
                            <tr>
                                <td colspan="6" class="text-center">Tidak ada detail order</td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($details as $detail): ?>
                            <tr>
                                <td><?= htmlspecialchars($detail['kodebarang']) ?></td>
                                <td><?= htmlspecialchars($detail['namabarang'] ?? '-') ?></td>
                                <td><?= (int)$detail['jumlah'] ?></td>
                                <td><?= htmlspecialchars($detail['satuan'] ?? '-') ?></td>
                                <td><?= number_format((float)$detail['hargajual'], 0, ',', '.') ?></td>
                                <td><?= number_format((float)$detail['discount'], 2, ',', '.') ?></td>
                                <td><?= number_format((float)$detail['totalharga'], 0, ',', '.') ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
