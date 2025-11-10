<?php
$title = 'Peta Lokasi Customer';
$config = require __DIR__ . '/../../config/app.php';
$baseUrl = rtrim($config['base_url'], '/');
if (empty($baseUrl) || $baseUrl === 'http://' || $baseUrl === 'https://') {
    $baseUrl = '/';
}

$additionalStyles = $additionalStyles ?? [];
$additionalStyles[] = 'https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.css';
$additionalStyles[] = $baseUrl . '/assets/css/mapbox-gl-geocoder.css';

$additionalScripts = $additionalScripts ?? [];
$additionalScripts[] = 'https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.js';
$additionalScripts[] = $baseUrl . '/assets/js/mapbox-gl-geocoder.min.js';

$mapboxToken = $mapboxToken ?? '';
$hasMapboxToken = !empty($mapboxToken);
$customer = $customer ?? null;
$customerId = $customerId ?? null;
$customerError = $customerError ?? null;
$copyIconUrl = $baseUrl . '/assets/icons/fa-copy.svg';
$locationIconUrl = $baseUrl . '/assets/icons/fa-location-dot.svg';

require __DIR__ . '/../layouts/header.php';
?>

<div class="row mb-3">
    <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h2 class="h4 mb-0">Peta Lokasi Customer</h2>
        <a href="/mastercustomer" class="btn btn-outline-secondary">
            <?= icon('back', 'me-2', 16) ?>Kembali ke Daftar Customer
        </a>
    </div>
</div>

<div class="row mb-3">
    <div class="col-12">
        <div class="alert alert-info mb-0">
            Halaman ini menampilkan peta interaktif Mapbox untuk menentukan koordinat customer. Carilah lokasi dengan pencarian, klik peta atau seret marker, kemudian simpan.
        </div>
    </div>
</div>

<?php if ($customerError): ?>
    <div class="row mb-3">
        <div class="col-12">
            <div class="alert alert-danger mb-0">
                <?= htmlspecialchars($customerError) ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php if ($customer): ?>
    <div class="row mb-3">
        <div class="col-12">
            <div class="card border-success">
                <div class="card-body">
                    <div class="row g-3 align-items-center">
                        <div class="col-md-6">
                            <h3 class="h5 mb-1"><?= htmlspecialchars($customer['namacustomer'] ?? '-') ?></h3>
                            <div class="text-muted small">Kode: <strong><?= htmlspecialchars($customer['kodecustomer'] ?? '-') ?></strong></div>
                            <div class="text-muted small">Alamat: <?= htmlspecialchars(trim(($customer['alamatcustomer'] ?? '') . ' ' . ($customer['kotacustomer'] ?? ''))) ?></div>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <div class="small text-muted">Customer ID: <?= (int)$customer['id'] ?></div>
                            <?php if (!empty($customer['latitude']) && !empty($customer['longitude'])): ?>
                                <div class="small text-success">Koordinat saat ini: <?= number_format((float)$customer['latitude'], 6) ?>, <?= number_format((float)$customer['longitude'], 6) ?></div>
                            <?php else: ?>
                                <div class="small text-danger">Customer belum memiliki koordinat.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h3 class="h5 mb-0">Peta Interaktif</h3>
                </div>
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <div class="d-flex align-items-center gap-1">
                            <span class="text-muted small">Lat</span>
                            <input type="text" id="selectedLatitude" class="form-control form-control-sm" style="width: 140px;" readonly>
                        </div>
                        <div class="d-flex align-items-center gap-1">
                            <span class="text-muted small">Lng</span>
                            <input type="text" id="selectedLongitude" class="form-control form-control-sm" style="width: 140px;" readonly>
                        </div>
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-sm d-flex align-items-center" id="btnCopyCoordinate">
                        <img src="<?= htmlspecialchars($copyIconUrl) ?>" alt="" width="16" height="16" class="me-1 map-action-icon" loading="lazy" aria-hidden="true">Salin
                    </button>
                    <button type="button" class="btn btn-outline-success btn-sm d-flex align-items-center" id="btnUseMyLocation" <?= $hasMapboxToken ? '' : 'disabled' ?>>
                        <img src="<?= htmlspecialchars($locationIconUrl) ?>" alt="" width="16" height="16" class="me-1 map-action-icon" loading="lazy" aria-hidden="true">Lokasi Saya
                    </button>
                    <?php if ($customerId): ?>
                        <button type="button" class="btn btn-success btn-sm" id="btnSaveCoordinate">
                            <?= icon('save', 'me-1', 14) ?>Simpan Koordinat
                        </button>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body">
                <?php if (!$hasMapboxToken): ?>
                    <div class="alert alert-danger mb-0">
                        Mapbox access token belum dikonfigurasi. Tambahkan MAPBOX_ACCESS_TOKEN pada environment server untuk menampilkan peta.
                    </div>
                <?php else: ?>
                    <div id="mapGeocoder" class="mapbox-geocoder-container mb-3"></div>
                    <div id="mapCanvas" style="width: 100%; height: 520px; border-radius: 0.75rem; overflow: hidden;"></div>
                    <div class="small text-muted mt-2" id="mapStatus">Klik peta atau seret marker untuk menentukan koordinat.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
$mapboxTokenJs = json_encode($mapboxToken, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
$customerJs = json_encode($customer ? [
    'id' => (int)$customer['id'],
    'name' => $customer['namacustomer'] ?? '',
    'latitude' => $customer['latitude'] !== null ? (float)$customer['latitude'] : null,
    'longitude' => $customer['longitude'] !== null ? (float)$customer['longitude'] : null,
    'kodecustomer' => $customer['kodecustomer'] ?? ''
] : null, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
$saveEndpoint = $customerId ? "/visits/customer/{$customerId}/coordinates" : null;
$saveEndpointJs = json_encode($saveEndpoint, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);

$additionalInlineScripts = $additionalInlineScripts ?? [];
$additionalInlineScripts[] = <<<JS
(function() {
    const mapContainer = document.getElementById('mapCanvas');
    const latField = document.getElementById('selectedLatitude');
    const lngField = document.getElementById('selectedLongitude');
    const statusEl = document.getElementById('mapStatus');
    const copyBtn = document.getElementById('btnCopyCoordinate');
    const saveBtn = document.getElementById('btnSaveCoordinate');
    const useLocationBtn = document.getElementById('btnUseMyLocation');
    const geocoderContainer = document.getElementById('mapGeocoder');

    const token = {$mapboxTokenJs};
    const customerData = {$customerJs};
    const saveEndpoint = {$saveEndpointJs};

    function setStatus(message, type) {
        if (!statusEl) return;
        statusEl.textContent = message;
        statusEl.classList.remove('text-danger', 'text-success', 'text-muted');
        if (type === 'error') {
            statusEl.classList.add('text-danger');
        } else if (type === 'success') {
            statusEl.classList.add('text-success');
        } else {
            statusEl.classList.add('text-muted');
        }
    }

    function copyCoordinate() {
        if (!latField || !lngField) {
            return;
        }
        const text = latField.value && lngField.value ? latField.value + ', ' + lngField.value : '';
        if (!text) {
            window.alert('Tidak ada koordinat yang dapat disalin.');
            return;
        }
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(function() {
                setStatus('Koordinat disalin ke clipboard.', 'success');
            }).catch(function() {
                window.prompt('Salin koordinat secara manual:', text);
            });
        } else {
            window.prompt('Salin koordinat secara manual:', text);
        }
    }

    if (copyBtn) {
        copyBtn.addEventListener('click', copyCoordinate);
    }

    if (!mapContainer || !token) {
        return;
    }

    mapboxgl.accessToken = token;

    const initialLat = customerData && typeof customerData.latitude === 'number' ? customerData.latitude : -6.200000;
    const initialLng = customerData && typeof customerData.longitude === 'number' ? customerData.longitude : 106.816666;
    const hasInitial = customerData && typeof customerData.latitude === 'number' && typeof customerData.longitude === 'number';

    const map = new mapboxgl.Map({
        container: mapContainer,
        style: 'mapbox://styles/mapbox/streets-v12',
        center: [initialLng, initialLat],
        zoom: hasInitial ? 15 : 11
    });

    map.addControl(new mapboxgl.NavigationControl());

    let marker = new mapboxgl.Marker({ draggable: true })
        .setLngLat([initialLng, initialLat])
        .addTo(map);

    function updateFields(lngLat, suppressStatus) {
        if (!lngLat) {
            return;
        }
        if (latField) {
            latField.value = lngLat.lat.toFixed(6);
        }
        if (lngField) {
            lngField.value = lngLat.lng.toFixed(6);
        }
        if (!suppressStatus) {
            setStatus('Koordinat diperbarui: ' + lngLat.lat.toFixed(6) + ', ' + lngLat.lng.toFixed(6), '');
        }
    }

    marker.on('dragend', function() {
        updateFields(marker.getLngLat());
    });

    map.on('click', function(event) {
        marker.setLngLat(event.lngLat);
        updateFields(event.lngLat);
    });

    if (typeof MapboxGeocoder === 'function' && geocoderContainer) {
        const geocoder = new MapboxGeocoder({
            accessToken: token,
            mapboxgl: mapboxgl,
            marker: false,
            placeholder: 'Cari lokasi customer…',
            language: 'id',
            countries: 'id'
        });
        geocoderContainer.innerHTML = '';
        geocoderContainer.appendChild(geocoder.onAdd(map));
        geocoder.on('result', function(ev) {
            if (!ev || !ev.result || !ev.result.center) {
                return;
            }
            const center = ev.result.center;
            map.flyTo({ center: center, zoom: 16 });
            marker.setLngLat(center);
            updateFields({ lng: center[0], lat: center[1] });
            setStatus('Lokasi ditemukan: ' + (ev.result.place_name || ''), 'success');
        });
    }

    if (useLocationBtn) {
        useLocationBtn.addEventListener('click', function() {
            if (!navigator.geolocation) {
                window.alert('Perangkat tidak mendukung geolocation.');
                return;
            }
            setStatus('Mengambil lokasi perangkat…', '');
            navigator.geolocation.getCurrentPosition(function(pos) {
                const coords = { lat: pos.coords.latitude, lng: pos.coords.longitude };
                map.flyTo({ center: [coords.lng, coords.lat], zoom: 16 });
                marker.setLngLat([coords.lng, coords.lat]);
                updateFields(coords);
                setStatus('Koordinat diperoleh dari lokasi perangkat.', 'success');
            }, function(err) {
                setStatus('Tidak dapat memperoleh lokasi: ' + (err && err.message ? err.message : 'akses ditolak'), 'error');
            }, {
                enableHighAccuracy: true,
                timeout: 10000
            });
        });
    }

    if (saveBtn && saveEndpoint) {
        saveBtn.addEventListener('click', function() {
            if (!latField || !lngField || !latField.value || !lngField.value) {
                window.alert('Pilih koordinat terlebih dahulu.');
                return;
            }

            const payload = {
                latitude: parseFloat(latField.value),
                longitude: parseFloat(lngField.value)
            };

            if (Number.isNaN(payload.latitude) || Number.isNaN(payload.longitude)) {
                window.alert('Koordinat tidak valid.');
                return;
            }

            setStatus('Menyimpan koordinat...', '');
            saveBtn.disabled = true;

            fetch(saveEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload)
            })
                .then(function(response) {
                    saveBtn.disabled = false;
                    if (!response.ok) {
                        return response.json().then(function(body) {
                            var message = body && (body.error || body.message) ? body.error || body.message : 'Gagal menyimpan koordinat.';
                            throw new Error(message);
                        }).catch(function() {
                            throw new Error('Gagal menyimpan koordinat.');
                        });
                    }
                    return response.json();
                })
                .then(function() {
                    setStatus('Koordinat berhasil disimpan ke mastercustomer.', 'success');
                })
                .catch(function(error) {
                    saveBtn.disabled = false;
                    setStatus(error.message || 'Gagal menyimpan koordinat.', 'error');
                    window.alert(error.message || 'Gagal menyimpan koordinat.');
                });
        });
    }

    updateFields({ lat: initialLat, lng: initialLng }, true);
})();
JS;

require __DIR__ . '/../layouts/footer.php';
?>

