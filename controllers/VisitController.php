<?php
class VisitController extends Controller {
    private $visitModel;
    private $visitActivityModel;
    private $customerModel;

    public function __construct() {
        $this->visitModel = new Visit();
        $this->visitActivityModel = new VisitActivity();
        $this->customerModel = new Mastercustomer();
    }

    public function index() {
        Auth::requireRole(['sales']);

        $currentUser = Auth::user();
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 20;
        $perPage = in_array($perPage, [10, 20, 40, 50, 100]) ? $perPage : 20;
        $status = $_GET['status'] ?? '';
        $search = $_GET['search'] ?? '';

        $result = $this->visitModel->listByUser($currentUser['id'], $page, $perPage, $status, $search);
        $totalPages = $perPage > 0 ? (int)ceil($result['total'] / $perPage) : 1;

        $activeVisit = $this->visitModel->findActiveByUser($currentUser['id']);

        $data = [
            'visits' => $result['data'],
            'page' => $page,
            'perPage' => $perPage,
            'total' => $result['total'],
            'totalPages' => $totalPages,
            'statusFilter' => $status,
            'search' => $search,
            'activeVisit' => $activeVisit
        ];

        $this->view('visits/index', $data);
    }

    public function checkin() {
        Auth::requireRole(['sales']);
        $currentUser = Auth::user();
        if (empty($currentUser['kodesales'])) {
            Session::flash('error', 'Akun sales Anda belum memiliki kode sales yang terdaftar. Hubungi admin.');
            $this->redirect('/visits');
        }

        $activeVisit = $this->visitModel->findActiveByUser($currentUser['id']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($activeVisit) {
                Session::flash('error', 'Anda masih memiliki kunjungan yang berjalan. Selesaikan terlebih dahulu sebelum check-in baru.');
                $this->redirect('/visits');
            }

            $customerId = (int)($_POST['customer_id'] ?? 0);
            $checkInLatInput = $_POST['check_in_lat'] ?? '';
            $checkInLongInput = $_POST['check_in_long'] ?? '';
            $checkInLat = $checkInLatInput === '' ? null : (float)$checkInLatInput;
            $checkInLong = $checkInLongInput === '' ? null : (float)$checkInLongInput;
            $catatan = $_POST['catatan'] ?? null;

            if (!$customerId || $checkInLat === null || $checkInLong === null) {
                Session::flash('error', 'Pilih customer dan pastikan lokasi Anda terdeteksi.');
                $this->redirect('/visits/check-in');
            }

            $customer = $this->customerModel->findById($customerId);
            if (!$customer) {
                Session::flash('error', 'Customer tidak ditemukan.');
                $this->redirect('/visits/check-in');
            }

            if (empty($customer['latitude']) || empty($customer['longitude']) || (float)$customer['latitude'] == 0.0 || (float)$customer['longitude'] == 0.0) {
                Session::flash('error', 'Koordinat customer belum ditentukan. Silakan tetapkan lokasi customer terlebih dahulu.');
                $this->redirect('/visits/check-in');
            }

            $visitData = [
                'user_id' => $currentUser['id'],
                'kodesales' => $currentUser['kodesales'] ?? '',
                'customer_id' => $customer['id'],
                'kodecustomer' => $customer['kodecustomer'],
                'check_in_time' => date('Y-m-d H:i:s'),
                'check_in_lat' => $checkInLat,
                'check_in_long' => $checkInLong,
                'status_kunjungan' => 'Sedang Berjalan',
                'catatan' => $catatan,
                'jarak_dari_kantor' => $this->calculateDistanceKm($customer['latitude'], $customer['longitude'], $checkInLat, $checkInLong)
            ];

            $visitId = $this->visitModel->create($visitData);

            Session::flash('success', 'Check-in berhasil disimpan.');
            $this->redirect('/visits');
        }

        $appConfig = require __DIR__ . '/../config/app.php';
        $mapboxToken = getenv('MAPBOX_ACCESS_TOKEN') ?: ($appConfig['mapbox_access_token'] ?? null);

        $data = [
            'activeVisit' => $activeVisit,
            'mapboxToken' => $mapboxToken
        ];
        $this->view('visits/checkin', $data);
    }

    public function checkout($visitId) {
        Auth::requireRole(['sales']);
        $currentUser = Auth::user();

        $visit = $this->visitModel->findById($visitId);
        if (!$visit || $visit['user_id'] != $currentUser['id']) {
            Session::flash('error', 'Data kunjungan tidak ditemukan.');
            $this->redirect('/visits');
        }

        if ($visit['status_kunjungan'] !== 'Sedang Berjalan') {
            Session::flash('error', 'Kunjungan ini sudah selesai atau dibatalkan.');
            $this->redirect('/visits');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $checkOutLatInput = $_POST['check_out_lat'] ?? '';
            $checkOutLongInput = $_POST['check_out_long'] ?? '';
            $checkOutLat = $checkOutLatInput === '' ? null : (float)$checkOutLatInput;
            $checkOutLong = $checkOutLongInput === '' ? null : (float)$checkOutLongInput;
            $catatan = $_POST['catatan'] ?? null;

            if ($checkOutLat === null || $checkOutLong === null) {
                Session::flash('error', 'Lokasi check-out belum ditentukan.');
                $this->redirect('/visits');
            }

            $updateData = [
                'check_out_time' => date('Y-m-d H:i:s'),
                'check_out_lat' => $checkOutLat,
                'check_out_long' => $checkOutLong,
                'status_kunjungan' => 'Selesai',
                'catatan' => $catatan,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $this->visitModel->update($visitId, $updateData);

            Session::flash('success', 'Check-out berhasil disimpan.');
            $this->redirect('/visits');
        }

        $activities = $this->visitActivityModel->listByVisit($visitId);
        $activityOptions = $this->getActiveTabelAktivitasOptions();
        $appConfig = require __DIR__ . '/../config/app.php';
        $mapboxToken = getenv('MAPBOX_ACCESS_TOKEN') ?: ($appConfig['mapbox_access_token'] ?? null);

        $data = [
            'visit' => $visit,
            'activities' => $activities,
            'mapboxToken' => $mapboxToken,
            'activityOptions' => $activityOptions
        ];

        $this->view('visits/checkout', $data);
    }

    public function createActivity($visitId) {
        Auth::requireRole(['sales']);
        $currentUser = Auth::user();

        $visit = $this->visitModel->findById($visitId);
        if (!$visit || $visit['user_id'] != $currentUser['id']) {
            Session::flash('error', 'Kunjungan tidak ditemukan.');
            $this->redirect('/visits');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $activityType = trim($_POST['activity_type'] ?? '');
            $deskripsi = $_POST['deskripsi'] ?? null;
            if (empty($activityType)) {
                Session::flash('error', 'Jenis aktivitas harus diisi.');
                $this->redirect('/visits/checkout/' . $visitId);
            }

            $this->visitActivityModel->create([
                'visit_id' => $visitId,
                'activity_type' => $activityType,
                'deskripsi' => $deskripsi
            ]);

            Session::flash('success', 'Aktivitas kunjungan ditambahkan.');
        }

        $this->redirect('/visits/checkout/' . $visitId);
    }

    public function nearestCustomers() {
        Auth::requireRole(['sales']);

        $latitude = isset($_GET['lat']) && $_GET['lat'] !== '' ? (float)$_GET['lat'] : null;
        $longitude = isset($_GET['lng']) && $_GET['lng'] !== '' ? (float)$_GET['lng'] : null;
        $search = $_GET['q'] ?? '';
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;

        if ($latitude === null || $longitude === null) {
            if (!empty($search)) {
                $customers = $this->customerModel->findNearest(null, null, $limit, $search);
            } else {
                $customers = [];
            }
        } else {
            $customers = $this->customerModel->findNearest($latitude, $longitude, $limit, $search);
        }

        header('Content-Type: application/json');
        echo json_encode(['data' => $customers]);
    }

    public function updateCustomerCoordinates($customerId) {
        Auth::requireRole(['sales']);
        $currentUser = Auth::user();

        $customer = $this->customerModel->findById($customerId);
        if (!$customer) {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Customer tidak ditemukan']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) {
            $input = $_POST;
        }

        $latitude = isset($input['latitude']) ? (float)$input['latitude'] : null;
        $longitude = isset($input['longitude']) ? (float)$input['longitude'] : null;

        if ($latitude === null || $longitude === null) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Latitude dan longitude harus diisi']);
            return;
        }

        if ($latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
            http_response_code(422);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Koordinat tidak valid']);
            return;
        }

        $this->customerModel->updateCoordinates($customerId, $latitude, $longitude);

        // Update status dan penanda pengguna bila relevan
        $updateData = [
            'status' => 'updated'
        ];
        if (!empty($currentUser['kodesales'])) {
            $updateData['userid'] = $currentUser['kodesales'];
        }
        $this->customerModel->update($customerId, $updateData);

        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
    }

    private function calculateDistanceKm($customerLat, $customerLong, $userLat, $userLong) {
        if ($customerLat === null || $customerLong === null || $userLat === null || $userLong === null) {
            return null;
        }

        $earthRadius = 6371; // km
        $latFrom = deg2rad((float)$customerLat);
        $lonFrom = deg2rad((float)$customerLong);
        $latTo = deg2rad((float)$userLat);
        $lonTo = deg2rad((float)$userLong);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) + cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return round($earthRadius * $angle, 2);
    }

    private function getActiveTabelAktivitasOptions() {
        $model = new Tabelaktivitas();
        $records = $model->getAll(1, 500, '', 'aktivitas', 'ASC');
        $options = [];
        foreach ($records as $row) {
            if (($row['status'] ?? '') === 'aktif' && !empty($row['aktivitas'])) {
                $options[] = $row['aktivitas'];
            }
        }
        return $options;
    }
}

