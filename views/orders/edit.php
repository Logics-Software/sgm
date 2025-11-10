<?php
$title = 'Edit Order';
$config = require __DIR__ . '/../../config/app.php';
$baseUrl = rtrim($config['base_url'], '/');
if (empty($baseUrl) || $baseUrl === 'http://' || $baseUrl === 'https://') {
    $baseUrl = '/';
}

$additionalStyles = array_merge($additionalStyles ?? [], [
    'https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css'
]);
$additionalScripts = array_merge($additionalScripts ?? [], [
    'https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js'
]);

require __DIR__ . '/../layouts/header.php';
?>

<div class="row mb-3">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="/orders">Transaksi Order</a></li>
                <li class="breadcrumb-item active">Edit Order</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-white">
                <h4 class="mb-0">Edit Order <?= icon('arrow-right', 'me-0 mb-1', 14)?> <?= htmlspecialchars($order['noorder']) ?></h4>
            </div>
            <div class="card-body">
                <form method="POST" action="" id="orderForm">
                    <div class="row g-3 mb-3">
                        <div class="col-12">
                            <label class="form-label">Customer</label>
                            <select name="kodecustomer" class="form-select js-choice-customer">
                                <option value="">Pilih Customer</option>
                                <?php foreach ($customers as $customer): ?>
                                <?php
                                $alamat = trim($customer['alamatcustomer'] ?? '');
                                $optionLabel = $customer['namacustomer'];
                                if ($alamat !== '') {
                                    $optionLabel .= ' - ' . $alamat;
                                }
                                $optionLabel .= ' (' . $customer['kodecustomer'] . ')';
                                ?>
                                <option value="<?= htmlspecialchars($customer['kodecustomer']) ?>" <?= ($order['kodecustomer'] ?? '') === $customer['kodecustomer'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($optionLabel) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Keterangan</label>
                            <input type="text" name="keterangan" class="form-control" value="<?= htmlspecialchars($order['keterangan'] ?? '') ?>" maxlength="50">
                        </div>
                    </div>
                    <input type="hidden" name="tanggalorder" value="<?= htmlspecialchars($tanggalorder) ?>">
                    <input type="hidden" name="status" value="order">
                    <input type="hidden" name="noorder" value="<?= htmlspecialchars($order['noorder']) ?>">

                    <?php
                    $detailRows = array_values(array_filter($detailItems, static function ($item) {
                        return !empty($item['kodebarang']);
                    }));
                    $barangLookup = [];
                    foreach ($barangs as $barang) {
                        $barangLookup[$barang['kodebarang']] = $barang;
                    }
                    $normalizedDetails = [];
                    foreach ($detailRows as $detail) {
                        $kode = $detail['kodebarang'];
                        $barangInfo = $barangLookup[$kode] ?? null;
                        $namaBarang = $barangInfo['namabarang'] ?? ($detail['namabarang'] ?? $kode);
                        $jumlah = isset($detail['jumlah']) ? (float)$detail['jumlah'] : 0;
                        $hargaBaris = isset($detail['hargajual']) ? (float)$detail['hargajual'] : (isset($detail['harga']) ? (float)$detail['harga'] : 0);
                        $discount = isset($detail['discount']) ? (float)$detail['discount'] : 0;
                        $total = isset($detail['totalharga']) ? (float)$detail['totalharga'] : (($jumlah * $hargaBaris) - $discount);
                        if ($total < 0) {
                            $total = 0;
                        }
                        $normalizedDetails[] = [
                            'kodebarang' => $kode,
                            'namabarang' => $namaBarang,
                            'jumlah' => $jumlah,
                            'harga' => $hargaBaris,
                            'discount' => $discount,
                            'total' => $total,
                            'satuan' => $detail['satuan'] ?? ($barangInfo['satuan'] ?? '')
                        ];
                    }
                    $detailJson = json_encode($normalizedDetails, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
                    ?>

                    <div class="table-responsive order-items-wrapper">
                        <table class="table table-bordered align-middle order-items-table" id="detailTable">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 35%">Barang</th>
                                    <th style="width: 10%" class="text-end">Jumlah</th>
                                    <th style="width: 10%" class="text-end">Satuan</th>
                                    <th style="width: 15%" class="text-end">Harga</th>
                                    <th style="width: 15%" class="text-end">Diskon</th>
                                    <th style="width: 15%" class="text-end">Total</th>
                                    <th style="width: 10%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="detailTableBody"></tbody>
                        </table>
                        <div id="detailEmptyState" class="text-center text-muted py-3 <?= !empty($detailRows) ? 'd-none' : '' ?>">
                            Belum ada barang ditambahkan
                        </div>
                    </div>
                    <div class="d-flex flex-wrap align-items-center justify-content-between mt-3 gap-2">
                        <button type="button" class="btn btn-outline-primary btn-sm" id="addDetailBtn">Tambah Barang</button>
                        <div class="text-end flex-grow-1 flex-md-grow-0">
                            <h6 class="mb-0 px-2"><span class="d-inline d-md-none">Total</span><span class="d-none d-md-inline">Grand Total</span>: <span id="grandTotalDisplay">0</span></h6>
                            <input type="hidden" name="nilaiorder" id="grandTotalHidden" value="0">
                        </div>
                    </div>
                    <div class="mt-4 d-flex justify-content-between align-items-center">
                        <div></div>
                        <div>
                            <a href="/orders" class="btn btn-secondary me-2">Batal</a>
                            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="detailForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailModalLabel">Tambah Barang</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Barang</label>
                        <select class="form-select js-choice-barang" id="modalBarang" required>
                            <option value="">Pilih Barang</option>
                            <?php foreach ($barangs as $barang): ?>
                            <option value="<?= htmlspecialchars($barang['kodebarang']) ?>" data-nama="<?= htmlspecialchars($barang['namabarang']) ?>" data-satuan="<?= htmlspecialchars($barang['satuan'] ?? '-') ?>" data-harga="<?= isset($barang['hargajual']) ? number_format((float)$barang['hargajual'], 2, '.', '') : '0' ?>" data-discount="<?= isset($barang['discountjual']) ? number_format((float)$barang['discountjual'], 2, '.', '') : '0' ?>" data-stok="<?= htmlspecialchars($barang['stokakhir'] ?? '0') ?>">
                                <?= htmlspecialchars($barang['namabarang'] . ' (' . $barang['kodebarang'] . ')') ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3 col-5">
                            <label class="form-label">Stok tersedia</label>
                            <div class="input-group stock-display">
                                <span class="form-control" id="modalStokInfo">0</span>
                            </div>
                        </div>
                        <div class="col-md-3 col-7">
                            <label class="form-label">Jumlah</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="modalJumlah" value="1" required>
                                <span class="input-group-text" id="modalSatuan">-</span>
                            </div>
                        </div>
                        <div class="col-md-3 col-7">
                            <label class="form-label">Harga</label>
                            <input type="text" class="form-control" id="modalHarga" inputmode="numeric" value="0" required>
                        </div>
                        <div class="col-md-3 col-5">
                            <label class="form-label">Diskon</label>
                            <input type="text" class="form-control" id="modalDiscount" inputmode="decimal" value="0,00">
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="form-label">Total</label>
                        <input type="text" class="form-control fw-bold" id="modalTotal" value="0" readonly>
                    </div>
                </div>
                <div class="modal-footer justify-content-between align-items-center">
                    <div class="me-auto">
                        <button type="button" class="btn btn-info btn-sm" id="openStockPriceModal">
                            <?= icon('boxes-stacked', 'me-1', 14) ?> Stok &amp; Harga
                        </button>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="detailModalSubmitBtn">Simpan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="stockPriceModal" tabindex="-1" aria-labelledby="stockPriceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="stockPriceModalLabel">Informasi Stok dan Harga</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3 align-items-center mb-3">
                    <div class="col-md-6">
                        <label for="stockSearchInput" class="form-label mb-1">Cari Barang</label>
                        <input type="text" class="form-control" id="stockSearchInput" placeholder="Ketik nama atau kode barang...">
                    </div>
                    <div class="col-md-3 ms-md-auto">
                        <label for="stockPageSize" class="form-label mb-1">Tampilkan</label>
                        <select class="form-select" id="stockPageSize">
                            <option value="10">10 data</option>
                            <option value="20">20 data</option>
                            <option value="40">40 data</option>
                            <option value="60">60 data</option>
                        </select>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 34%">Nama Barang</th>
                                <th style="width: 18%">Pabrik</th>
                                <th style="width: 12%">Satuan</th>
                                <th style="width: 16%" class="text-end">Harga Jual</th>
                                <th style="width: 10%" class="text-end">Diskon (%)</th>
                                <th style="width: 10%" class="text-end">Stok</th>
                            </tr>
                        </thead>
                        <tbody id="stockTableBody"></tbody>
                    </table>
                </div>
                <div class="mt-3">
                    <nav>
                        <ul class="pagination justify-content-center mb-0" id="stockPagination"></ul>
                    </nav>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kembali</button>
            </div>
        </div>
    </div>
</div>

<script>
const barangList = <?= $barangsJson ?? '[]' ?>;
const barangMap = {};
barangList.forEach((item) => {
    if (item && item.kodebarang) {
        barangMap[item.kodebarang] = item;
    }
});

const initialDetails = <?= $detailJson ?? '[]' ?>;

const integerFormatter = new Intl.NumberFormat('id-ID', {
    maximumFractionDigits: 0,
    minimumFractionDigits: 0
});

const currencyFormatter = new Intl.NumberFormat('id-ID', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
});

function parseDiscountInput(value, formatOnBlur = false) {
    let cleaned = (value ?? '').replace(/[^0-9.,]/g, '').replace(/\./g, ',');
    if (!cleaned) {
        return {
            raw: 0,
            display: formatOnBlur ? '0,00' : ''
        };
    }

    const endsWithComma = cleaned.endsWith(',');
    const segments = cleaned.split(',');
    const rawIntPart = segments.shift() || '';
    const rawFractionPart = segments.join('').replace(/[^0-9]/g, '');

    const intDigits = rawIntPart.replace(/[^0-9]/g, '');
    const numericInt = intDigits === '' ? 0 : parseInt(intDigits, 10);
    const formattedInt = String(Number.isNaN(numericInt) ? 0 : numericInt);

    let fractionDigits = rawFractionPart.slice(0, 2);
    let raw = parseFloat(`${formattedInt}.${fractionDigits || '0'}`);
    if (Number.isNaN(raw)) raw = 0;
    raw = Math.max(raw, 0);

    let display;
    if (formatOnBlur) {
        display = raw.toFixed(2).replace('.', ',');
    } else {
        if (cleaned.includes(',')) {
            if (fractionDigits.length === 0 && endsWithComma) {
                display = `${formattedInt},`;
            } else {
                display = `${formattedInt},${fractionDigits}`;
            }
        } else {
            display = formattedInt;
        }
    }

    return { raw, display };
}

let orderDetails = Array.isArray(initialDetails) ? [...initialDetails] : [];
let detailModalInstance = null;
let barangChoiceInstance = null;
let customerChoiceInstances = [];
let currentEditIndex = null;
let rawHarga = 0;
let rawDiscount = 0;
let currentSelectedStock = null;
let modalStokInfoEl = null;

function parseNumericStock(value) {
    if (value === null || value === undefined) {
        return null;
    }
    let str = String(value).trim();
    if (!str) {
        return null;
    }
    str = str.replace(/\s+/g, '').replace(',', '.');
    const parsed = parseFloat(str);
    return Number.isNaN(parsed) ? null : parsed;
}

function getAvailableStock(option) {
    if (!option) {
        return null;
    }
    return parseNumericStock(option.dataset?.stok ?? null);
}

function updateStokInfoDisplay(stokAkhir) {
    if (!modalStokInfoEl) {
        return;
    }
    modalStokInfoEl.classList.remove('text-danger', 'text-warning', 'text-success', 'text-muted');
    if (stokAkhir === null) {
        modalStokInfoEl.textContent = '-';
        modalStokInfoEl.classList.add('text-danger');
        return;
    }

    const formattedStock = formatQty(stokAkhir);
    modalStokInfoEl.textContent = `${formattedStock}`;

    if (stokAkhir <= 0) {
        modalStokInfoEl.classList.add('text-danger');
    } else if (stokAkhir <= 5) {
        modalStokInfoEl.classList.add('text-warning');
    } else {
        modalStokInfoEl.classList.add('text-success');
    }
}

function resetDetailForm(
    detailForm,
    modalJumlahInput,
    modalHargaInput,
    modalDiscountInput,
    modalTotalInput,
    modalSatuanDisplay
) {
    detailForm?.reset();
    modalJumlahInput.value = '1';
    rawHarga = 0;
    rawDiscount = 0;
    currentSelectedStock = null;
    modalHargaInput.value = '0';
    modalDiscountInput.value = '0,00';
    modalTotalInput.value = integerFormatter.format(0);
    modalTotalInput.dataset.rawTotal = (0).toFixed(2);
    modalSatuanDisplay.textContent = '-';
    updateStokInfoDisplay(null);
    if (barangChoiceInstance) {
        barangChoiceInstance.removeActiveItems();
    } else if (detailForm?.querySelector('#modalBarang')) {
        detailForm.querySelector('#modalBarang').value = '';
    }
}

function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, (char) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;'
    })[char]);
}

function formatQty(value) {
    const num = parseFloat(value) || 0;
    if (Number.isInteger(num)) {
        return integerFormatter.format(num);
    }
    return currencyFormatter.format(num);
}

function formatCurrency(value) {
    return currencyFormatter.format(parseFloat(value) || 0);
}

function getBarangInfo(kode) {
    return barangMap[kode] || null;
}

function updateGrandTotal(grandTotalDisplay, grandTotalHidden) {
    const total = orderDetails.reduce((sum, item) => sum + (parseFloat(item.total) || 0), 0);
    grandTotalDisplay.innerText = integerFormatter.format(Math.round(total));
    grandTotalHidden.value = total.toFixed(2);
}

function renderDetailTable(detailTableBody, detailEmptyState, grandTotalDisplay, grandTotalHidden) {
    if (!detailTableBody || !detailEmptyState || !grandTotalDisplay || !grandTotalHidden) {
        return;
    }
    detailTableBody.innerHTML = '';
    if (!orderDetails.length) {
        detailEmptyState.classList.remove('d-none');
    } else {
        detailEmptyState.classList.add('d-none');
        orderDetails.forEach((item, index) => {
            const jumlah = parseFloat(item.jumlah) || 0;
            const harga = parseFloat(item.harga) || 0;
            const discountPercent = parseFloat(item.discount) || 0;
            const effectiveHarga = Math.max(harga * (1 - discountPercent / 100), 0);
            const total = parseFloat(item.total) || Math.max(jumlah * effectiveHarga, 0);

            const row = document.createElement('tr');
            row.innerHTML = `
                <td>
                    <div class="fw-semibold">${escapeHtml(item.namabarang)}</div>
                    <input type="hidden" name="detail_kodebarang[]" value="${escapeHtml(item.kodebarang)}">
                    <input type="hidden" name="detail_satuan[]" value="${escapeHtml(item.satuan || '')}">
                </td>
                <td class="text-end">
                    ${formatQty(jumlah)}
                    <input type="hidden" name="detail_jumlah[]" value="${jumlah.toFixed(2)}">
                </td>
                <td>
                    ${escapeHtml(item.satuan || '')}
                </td>
                <td class="text-end">
                    ${integerFormatter.format(Math.round(harga))}
                    <input type="hidden" name="detail_harga[]" value="${harga.toFixed(2)}">
                </td>
                <td class="text-end">
                    ${discountPercent.toFixed(2).replace('.', ',')}
                    <input type="hidden" name="detail_discount[]" value="${discountPercent.toFixed(2)}">
                </td>
                <td class="text-end">
                    ${integerFormatter.format(Math.round(total))}
                    <input type="hidden" name="detail_totalharga[]" value="${total.toFixed(2)}">
                </td>
                <td class="text-center">
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-primary" data-action="edit" data-index="${index}">Edit</button>
                        <button type="button" class="btn btn-outline-danger" data-action="remove" data-index="${index}">Hapus</button>
                    </div>
                </td>
            `;
            detailTableBody.appendChild(row);
        });
    }
    updateGrandTotal(grandTotalDisplay, grandTotalHidden);
}

function openDetailModal(modalElements, index = null) {
    const { modal, detailForm, modalBarangSelect, modalJumlahInput, modalHargaInput, modalDiscountInput, modalTotalInput, modalTitle, modalSatuanDisplay } = modalElements;
    currentEditIndex = index;
    const isEdit = index !== null;
    if (modalTitle) {
        modalTitle.textContent = isEdit ? 'Ubah Barang' : 'Tambah Barang';
    }
    detailForm?.reset();

    const selectedDetail = isEdit ? orderDetails[index] : null;
    const kode = selectedDetail?.kodebarang ?? '';
    if (barangChoiceInstance) {
        barangChoiceInstance.removeActiveItems();
        if (kode) {
            barangChoiceInstance.setChoiceByValue(kode);
        }
    } else if (modalBarangSelect) {
        modalBarangSelect.value = kode;
    }

    modalJumlahInput.value = selectedDetail ? String(parseFloat(selectedDetail.jumlah) || 0) : '1';
    rawHarga = selectedDetail ? (parseFloat(selectedDetail.harga) || 0) : 0;
    rawDiscount = selectedDetail ? (parseFloat(selectedDetail.discount) || 0) : 0;
    const initialTotal = selectedDetail ? (parseFloat(selectedDetail.total) || 0) : 0;
    modalHargaInput.value = rawHarga ? integerFormatter.format(Math.round(rawHarga)) : '0';
    modalDiscountInput.value = parseDiscountInput(String(rawDiscount), true).display;
    modalTotalInput.value = integerFormatter.format(Math.round(initialTotal));
    modalTotalInput.dataset.rawTotal = initialTotal.toFixed(2);
    modalSatuanDisplay.textContent = selectedDetail?.satuan || '-';
    const selectedOption = modalBarangSelect && modalBarangSelect.selectedOptions ? modalBarangSelect.selectedOptions[0] : null;
    const stokAkhir = getAvailableStock(selectedOption);
    currentSelectedStock = stokAkhir;
    updateStokInfoDisplay(stokAkhir);

    modal?.show();
}

function initOrderEditForm() {
    document.querySelectorAll('.js-choice-customer').forEach((select) => {
        if (typeof Choices !== 'undefined' && !select.dataset.choicesInitialized) {
            const choice = new Choices(select, {
                searchEnabled: true,
                searchPlaceholderValue: 'Ketik untuk mencari customer...',
                shouldSort: false,
                itemSelectText: '',
                noResultsText: 'Customer tidak ditemukan'
            });
            customerChoiceInstances.push(choice);
            select.dataset.choicesInitialized = '1';
        }
    });

    const orderForm = document.getElementById('orderForm');
    const customerSelect = document.querySelector('select[name="kodecustomer"]');
    const detailModalElement = document.getElementById('detailModal');
    const modalTitle = document.getElementById('detailModalLabel');
    const detailForm = document.getElementById('detailForm');
    const modalBarangSelect = document.getElementById('modalBarang');
    const modalJumlahInput = document.getElementById('modalJumlah');
    const modalHargaInput = document.getElementById('modalHarga');
    const modalDiscountInput = document.getElementById('modalDiscount');
    const modalTotalInput = document.getElementById('modalTotal');
    const modalSatuanDisplay = document.getElementById('modalSatuan');
    modalStokInfoEl = document.getElementById('modalStokInfo');
    const openStockModalBtn = document.getElementById('openStockPriceModal');
    const stockModalElement = document.getElementById('stockPriceModal');
    const stockSearchInput = document.getElementById('stockSearchInput');
    const stockTableBody = document.getElementById('stockTableBody');
    const stockPagination = document.getElementById('stockPagination');
    const stockPageSizeSelect = document.getElementById('stockPageSize');
    let stockPriceModalInstance = null;
    const stockTableState = {
        filtered: [],
        currentPage: 1,
        pageSize: parseInt(stockPageSizeSelect?.value ?? '10', 10),
        totalPages: 1
    };
    const addDetailBtn = document.getElementById('addDetailBtn');
    const detailTableBody = document.getElementById('detailTableBody');
    const detailEmptyState = document.getElementById('detailEmptyState');
    const grandTotalDisplay = document.getElementById('grandTotalDisplay');
    const grandTotalHidden = document.getElementById('grandTotalHidden');

    if (detailModalElement) {
        detailModalInstance = new bootstrap.Modal(detailModalElement);
    }

    if (typeof Choices !== 'undefined' && modalBarangSelect) {
        barangChoiceInstance = new Choices(modalBarangSelect, {
            searchEnabled: true,
            searchPlaceholderValue: 'Cari barang...',
            shouldSort: false,
            itemSelectText: '',
            noResultsText: 'Barang tidak ditemukan'
        });
    }

    const modalElements = {
        modal: detailModalInstance,
        modalTitle,
        detailForm,
        modalBarangSelect,
        modalJumlahInput,
        modalHargaInput,
        modalDiscountInput,
        modalTotalInput,
        modalSatuanDisplay
    };

    function renderStockTable() {
        if (!stockTableBody) {
            return;
        }
        const pageSize = parseInt(stockPageSizeSelect?.value ?? stockTableState.pageSize ?? '10', 10);
        stockTableState.pageSize = pageSize > 0 ? pageSize : 10;
        const totalItems = Array.isArray(stockTableState.filtered) ? stockTableState.filtered.length : 0;
        const totalPages = Math.max(Math.ceil(totalItems / stockTableState.pageSize) || 1, 1);
        stockTableState.totalPages = totalPages;
        if (stockTableState.currentPage > totalPages) {
            stockTableState.currentPage = totalPages;
        }
        if (stockTableState.currentPage < 1) {
            stockTableState.currentPage = 1;
        }
        const startIndex = (stockTableState.currentPage - 1) * stockTableState.pageSize;
        const pageItems = stockTableState.filtered.slice(startIndex, startIndex + stockTableState.pageSize);
        stockTableBody.innerHTML = pageItems
            .map((item) => {
                const nama = escapeHtml(item.namabarang ?? '');
                const pabrik = escapeHtml(item.namapabrik ?? '');
                const satuan = escapeHtml(item.satuan ?? '-');
                const harga = parseFloat(item.hargajual ?? 0);
                const diskon = parseFloat(item.discountjual ?? 0);
                const stok = item.stokakhir !== undefined && item.stokakhir !== null ? parseFloat(item.stokakhir) : null;
                return `
                    <tr>
                        <td><span class="fw-semibold">${nama}</span></td>
                        <td>${pabrik}</td>
                        <td>${satuan}</td>
                        <td class="text-end">${integerFormatter.format(Math.round(harga))}</td>
                        <td class="text-end">${Number.isFinite(diskon) ? diskon.toFixed(2).replace('.', ',') : '0,00'}</td>
                        <td class="text-end">${stok !== null && Number.isFinite(stok) ? formatQty(stok) : '-'}</td>
                    </tr>
                `;
            })
            .join('');

        if (stockPagination) {
            if (totalPages <= 1) {
                stockPagination.innerHTML = '';
            } else {
                const maxLinks = 5;
                const half = Math.floor(maxLinks / 2);
                let start = Math.max(1, stockTableState.currentPage - half);
                let end = Math.min(totalPages, start + maxLinks - 1);
                if (end - start + 1 < maxLinks) {
                    start = Math.max(1, end - maxLinks + 1);
                }
                let html = '';
                const addLink = (page, label, disabled = false) => {
                    const safePage = Math.min(Math.max(page, 1), totalPages);
                    html += `
                        <li class="page-item ${disabled ? 'disabled' : ''} ${stockTableState.currentPage === page ? 'active' : ''}">
                            <a class="page-link" href="#" data-page="${safePage}">${label}</a>
                        </li>`;
                };
                addLink(stockTableState.currentPage - 1, 'Previous', stockTableState.currentPage <= 1);
                if (start > 1) {
                    addLink(1, '1');
                    if (start > 2) {
                        html += '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
                    }
                }
                for (let i = start; i <= end; i += 1) {
                    addLink(i, String(i));
                }
                if (end < totalPages) {
                    if (end < totalPages - 1) {
                        html += '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
                    }
                    addLink(totalPages, String(totalPages));
                }
                addLink(stockTableState.currentPage + 1, 'Next', stockTableState.currentPage >= totalPages);
                stockPagination.innerHTML = html;
            }
        }
    }

    function openStockPriceModal() {
        if (!stockModalElement || typeof bootstrap === 'undefined') {
            return;
        }
        if (!stockPriceModalInstance) {
            stockPriceModalInstance = new bootstrap.Modal(stockModalElement);
        }
        stockTableState.pageSize = parseInt(stockPageSizeSelect?.value ?? '10', 10);
        stockTableState.currentPage = 1;
        stockTableState.filtered = Array.isArray(barangList) ? [...barangList] : [];
        if (stockSearchInput) {
            stockSearchInput.value = '';
        }
        renderStockTable();
        stockPriceModalInstance.show();
    }

    openStockModalBtn?.addEventListener('click', (event) => {
        event.preventDefault();
        openStockPriceModal();
    });

    stockSearchInput?.addEventListener('input', () => {
        const query = stockSearchInput.value.trim().toLowerCase();
        stockTableState.filtered = Array.isArray(barangList)
            ? barangList.filter((item) => {
                  const kode = (item.kodebarang ?? '').toLowerCase();
                  const nama = (item.namabarang ?? '').toLowerCase();
                  const satuan = (item.satuan ?? '').toLowerCase();
                  const pabrik = (item.namapabrik ?? '').toLowerCase();
                  return (
                      kode.includes(query) ||
                      nama.includes(query) ||
                      satuan.includes(query) ||
                      pabrik.includes(query)
                  );
              })
            : [];
        stockTableState.currentPage = 1;
        renderStockTable();
    });

    stockPageSizeSelect?.addEventListener('change', () => {
        stockTableState.pageSize = parseInt(stockPageSizeSelect.value ?? '10', 10);
        stockTableState.currentPage = 1;
        renderStockTable();
    });

    stockPagination?.addEventListener('click', (event) => {
        const link = event.target.closest('a[data-page]');
        if (!link) {
            return;
        }
        event.preventDefault();
        const li = link.closest('.page-item');
        if (li && li.classList.contains('disabled')) {
            return;
        }
        const targetPage = parseInt(link.dataset.page, 10);
        if (Number.isNaN(targetPage) || targetPage === stockTableState.currentPage) {
            return;
        }
        stockTableState.currentPage = Math.min(
            Math.max(targetPage, 1),
            stockTableState.totalPages
        );
        renderStockTable();
    });

    renderDetailTable(detailTableBody, detailEmptyState, grandTotalDisplay, grandTotalHidden);

    orderForm?.addEventListener('submit', (event) => {
        if (!customerSelect?.value) {
            event.preventDefault();
            showAlert({
                title: 'Validasi Order',
                message: 'Customer harus dipilih sebelum menyimpan order.',
                buttonText: 'Mengerti'
            });
            customerSelect?.focus();
            return;
        }
        if (!orderDetails.length) {
            event.preventDefault();
            showAlert({
                title: 'Validasi Order',
                message: 'Minimal satu barang harus ditambahkan sebelum menyimpan order.',
                buttonText: 'Mengerti'
            });
            addDetailBtn?.focus();
            return;
        }
    });

    addDetailBtn?.addEventListener('click', () => {
        rawHarga = 0;
        rawDiscount = 0;
        openDetailModal(modalElements, null);
    });

    modalBarangSelect?.addEventListener('change', () => {
        const kode = modalBarangSelect.value;
        const info = getBarangInfo(kode);
        const selectedOption = modalBarangSelect.selectedOptions ? modalBarangSelect.selectedOptions[0] : null;
        const isEditingSameItem = currentEditIndex !== null && orderDetails[currentEditIndex]?.kodebarang === kode;
        if (info && info.hargajual && !isEditingSameItem) {
            rawHarga = parseFloat(String(info.hargajual).replace(/[^0-9.]/g, '')) || 0;
        }
        if (info && Object.prototype.hasOwnProperty.call(info, 'discountjual') && !isEditingSameItem) {
            rawDiscount = parseFloat(String(info.discountjual).replace(/[^0-9.]/g, '')) || 0;
        }
        modalSatuanDisplay.textContent = info?.satuan || '-';
        const qtySanitizedSelect = sanitizeIntegerInput(modalJumlahInput.value);
        const qty = qtySanitizedSelect === '' ? 0 : parseFloat(qtySanitizedSelect);
        const harga = rawHarga;
        const discountPercent = rawDiscount;
        const effectiveHarga = Math.max(harga * (1 - discountPercent / 100), 0);
        const total = Math.max(qty * effectiveHarga, 0);
        modalHargaInput.value = rawHarga ? integerFormatter.format(Math.round(rawHarga)) : '0';
        modalDiscountInput.value = discountPercent.toFixed(2).replace('.', ',');
        modalTotalInput.value = integerFormatter.format(Math.round(total));
        modalTotalInput.dataset.rawTotal = total.toFixed(2);
        const stokAkhir = getAvailableStock(selectedOption);
        currentSelectedStock = stokAkhir;
        updateStokInfoDisplay(stokAkhir);
        if (stokAkhir !== null && stokAkhir <= 0) {
            showAlert({
                title: 'Stok Kosong',
                message: `Stok untuk ${escapeHtml(selectedOption?.dataset.nama ?? kode)} kosong.`,
                buttonText: 'Mengerti'
            });
            resetDetailForm(
                detailForm,
                modalJumlahInput,
                modalHargaInput,
                modalDiscountInput,
                modalTotalInput,
                modalSatuanDisplay
            );
            currentEditIndex = null;
            return;
        }
        if (stokAkhir !== null && qty > stokAkhir) {
            showAlert({
                title: 'Validasi Stok',
                message: `Jumlah tidak boleh melebihi stok (${formatQty(stokAkhir)}).`,
                buttonText: 'Mengerti'
            });
            const adjustedQty = Math.max(Math.floor(stokAkhir), 0);
            modalJumlahInput.value = adjustedQty > 0 ? String(adjustedQty) : '0';
            modalJumlahInput.dispatchEvent(new Event('input'));
        }
    });

    function sanitizeIntegerInput(value) {
        const normalized = value.replace(/[^0-9]/g, '');
        if (normalized === '') {
            return '';
        }
        return normalized.replace(/^0+(?=\d)/, '');
    }

    [modalJumlahInput, modalHargaInput, modalDiscountInput].forEach((input) => {
        input?.addEventListener('input', () => {
            const qtySanitized = sanitizeIntegerInput(modalJumlahInput.value);
            let qty = qtySanitized === '' ? 0 : parseFloat(qtySanitized);
            if (input === modalJumlahInput) {
                if (modalJumlahInput === document.activeElement) {
                    modalJumlahInput.value = qtySanitized;
                }
            }
            if (input === modalHargaInput) {
                const sanitizedHarga = sanitizeIntegerInput(modalHargaInput.value);
                if (sanitizedHarga === '') {
                    rawHarga = 0;
                    if (modalHargaInput === document.activeElement) {
                        modalHargaInput.value = '';
                    }
                } else {
                    rawHarga = parseFloat(sanitizedHarga) || 0;
                    if (modalHargaInput === document.activeElement) {
                        modalHargaInput.value = sanitizedHarga;
                    }
                }
            }
            if (input === modalDiscountInput) {
                const { raw, display } = parseDiscountInput(modalDiscountInput.value, false);
                rawDiscount = raw;
                if (modalDiscountInput === document.activeElement) {
                    modalDiscountInput.value = display;
                }
            }
            const harga = rawHarga;
            const discountPercent = rawDiscount;
            if (currentSelectedStock !== null && qty > currentSelectedStock) {
                showAlert({
                    title: 'Validasi Stok',
                    message: `Jumlah tidak boleh melebihi stok (${formatQty(currentSelectedStock)}).`,
                    buttonText: 'Mengerti'
                });
                qty = Math.max(Math.floor(currentSelectedStock), 0);
                modalJumlahInput.value = qty > 0 ? String(qty) : '0';
            }
            const effectiveHarga = Math.max(harga * (1 - discountPercent / 100), 0);
            const total = Math.max(qty * effectiveHarga, 0);
            modalHargaInput.value = rawHarga ? integerFormatter.format(Math.round(rawHarga)) : '0';
            modalTotalInput.value = integerFormatter.format(Math.round(total));
            modalTotalInput.dataset.rawTotal = total.toFixed(2);
            if (modalBarangSelect) {
                const selectedOption = modalBarangSelect.selectedOptions ? modalBarangSelect.selectedOptions[0] : null;
                const stokAkhir = getAvailableStock(selectedOption);
                currentSelectedStock = stokAkhir;
                updateStokInfoDisplay(stokAkhir);
            }
        });
    });

    modalDiscountInput?.addEventListener('blur', () => {
        const parsed = parseDiscountInput(modalDiscountInput.value, true);
        rawDiscount = parsed.raw;
        modalDiscountInput.value = parsed.display;
    });

    modalHargaInput?.addEventListener('blur', () => {
        modalHargaInput.value = rawHarga ? integerFormatter.format(Math.round(rawHarga)) : '0';
    });

    modalJumlahInput?.addEventListener('blur', () => {
        const qtySanitizedBody = sanitizeIntegerInput(modalJumlahInput.value);
        modalJumlahInput.value = qtySanitizedBody === '' ? '0' : qtySanitizedBody;
    });

    modalJumlahInput?.addEventListener('focus', () => {
        modalJumlahInput.select();
    });

    modalHargaInput?.addEventListener('focus', () => {
        modalHargaInput.value = rawHarga ? String(Math.round(rawHarga)) : '';
        modalHargaInput.select();
    });

    modalDiscountInput?.addEventListener('focus', () => {
        const focusDisplay = parseDiscountInput(rawDiscount.toString().replace('.', ','), false).display;
        modalDiscountInput.value = focusDisplay;
        modalDiscountInput.select();
    });

    detailForm?.addEventListener('submit', (event) => {
        event.preventDefault();
        const kode = modalBarangSelect.value.trim();
        if (!kode) {
            modalBarangSelect.focus();
            return;
        }

        const jumlahSanitized = sanitizeIntegerInput(modalJumlahInput.value);
        const jumlah = jumlahSanitized === '' ? 0 : parseFloat(jumlahSanitized);
        if (jumlah <= 0) {
            modalJumlahInput.focus();
            return;
        }

        const harga = rawHarga;
        const discountPercent = rawDiscount;
        const effectiveHarga = Math.max(harga * (1 - discountPercent / 100), 0);
        const total = Math.max(jumlah * effectiveHarga, 0);
        const barangInfo = getBarangInfo(kode);
        const selectedOption = modalBarangSelect.selectedOptions ? modalBarangSelect.selectedOptions[0] : null;
        const namaBarang = barangInfo?.namabarang || selectedOption?.dataset.nama || selectedOption?.textContent || kode;
        const satuanBarang = barangInfo?.satuan || selectedOption?.dataset.satuan || '';
        const stokAkhir = currentSelectedStock !== null ? currentSelectedStock : getAvailableStock(selectedOption);
        if (stokAkhir !== null && jumlah > stokAkhir) {
            showAlert({
                title: 'Jumlah Melebihi Stok',
                message: `Jumlah yang diinput (${formatQty(jumlah)}${satuanBarang ? ' ' + escapeHtml(satuanBarang) : ''}) melebihi stok tersedia (${formatQty(stokAkhir)}).`,
                buttonText: 'Mengerti'
            });
            modalJumlahInput.focus();
            return;
        }

        const rowData = {
            kodebarang: kode,
            namabarang: namaBarang,
            satuan: satuanBarang,
            jumlah,
            harga,
            discount: discountPercent,
            total
        };

        if (currentEditIndex !== null) {
            orderDetails[currentEditIndex] = {
                ...rowData
            };
        } else {
            orderDetails.push({
                ...rowData
            });
        }

        renderDetailTable(detailTableBody, detailEmptyState, grandTotalDisplay, grandTotalHidden);
        detailModalInstance?.hide();
    });

    detailTableBody?.addEventListener('click', (event) => {
        const button = event.target.closest('button[data-action]');
        if (!button) {
            return;
        }
        const index = parseInt(button.dataset.index, 10);
        if (Number.isNaN(index)) {
            return;
        }
        const action = button.dataset.action;
        if (action === 'edit') {
            openDetailModal(modalElements, index);
        } else if (action === 'remove') {
            orderDetails.splice(index, 1);
            renderDetailTable(detailTableBody, detailEmptyState, grandTotalDisplay, grandTotalHidden);
        }
    });

    detailModalElement?.addEventListener('hidden.bs.modal', () => {
        currentEditIndex = null;
        resetDetailForm(
            detailForm,
            modalJumlahInput,
            modalHargaInput,
            modalDiscountInput,
            modalTotalInput,
            modalSatuanDisplay
        );
    });
}

if (document.readyState === 'complete') {
    initOrderEditForm();
} else {
    window.addEventListener('load', initOrderEditForm);
}
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
