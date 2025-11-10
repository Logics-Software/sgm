<?php
class MastercustomerController extends Controller {
    public function index() {
        Auth::requireRole(['admin', 'manajemen', 'operator', 'sales']);
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
        $search = $_GET['search'] ?? '';
        $sortBy = $_GET['sort_by'] ?? 'id';
        $sortOrder = $_GET['sort_order'] ?? 'ASC';
        $status = isset($_GET['status']) ? strtolower(trim($_GET['status'])) : '';
        
        $validPerPage = [10, 20, 40, 50, 100, 200, 500];
        if (!in_array($perPage, $validPerPage)) {
            $perPage = 10;
        }
        
        $mastercustomerModel = new Mastercustomer();
        $customers = $mastercustomerModel->getAll($page, $perPage, $search, $sortBy, $sortOrder, $status);
        $total = $mastercustomerModel->count($search, $status);
        $totalPages = ceil($total / $perPage);
        
        $data = [
            'customers' => $customers,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'totalPages' => $totalPages,
            'search' => $search,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder,
            'status' => $status
        ];
        
        $this->view('mastercustomer/index', $data);
    }
    
    public function edit($id) {
        Auth::requireRole(['admin', 'manajemen', 'operator', 'sales']);
        
        $mastercustomerModel = new Mastercustomer();
        $customer = $mastercustomerModel->findById($id);
        
        if (!$customer) {
            Session::flash('error', 'Customer tidak ditemukan');
            $this->redirect('/mastercustomer');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'npwp' => $_POST['npwp'] ?? null,
                'namawp' => $_POST['namawp'] ?? null,
                'alamatwp' => $_POST['alamatwp'] ?? null,
                'statuspkp' => $_POST['statuspkp'] ?? 'nonpkp',
                'namaapoteker' => $_POST['namaapoteker'] ?? null,
                'nosipa' => $_POST['nosipa'] ?? null,
                'tanggaledsipa' => $_POST['tanggaledsipa'] ?? null,
                'noijinusaha' => $_POST['noijinusaha'] ?? null,
                'tanggaledijinusaha' => $_POST['tanggaledijinusaha'] ?? null,
                'nocdob' => $_POST['nocdob'] ?? null,
                'tanggaledcdob' => $_POST['tanggaledcdob'] ?? null,
                'status' => 'updated'
            ];

            $latitudeInput = trim($_POST['latitude'] ?? '');
            $longitudeInput = trim($_POST['longitude'] ?? '');

            $latitudeValue = $latitudeInput === '' ? null : filter_var($latitudeInput, FILTER_VALIDATE_FLOAT);
            $longitudeValue = $longitudeInput === '' ? null : filter_var($longitudeInput, FILTER_VALIDATE_FLOAT);

            if ($latitudeInput !== '' && $latitudeValue === false) {
                Session::flash('error', 'Nilai latitude tidak valid.');
                $this->redirect('/mastercustomer/edit/' . $id);
            }

            if ($longitudeInput !== '' && $longitudeValue === false) {
                Session::flash('error', 'Nilai longitude tidak valid.');
                $this->redirect('/mastercustomer/edit/' . $id);
            }

            $data['latitude'] = $latitudeValue === null ? null : (float)$latitudeValue;
            $data['longitude'] = $longitudeValue === null ? null : (float)$longitudeValue;

            $loggedInUser = Auth::user();
            if ($loggedInUser && ($loggedInUser['role'] ?? '') === 'sales') {
                $data['userid'] = $loggedInUser['kodesales'] ?? null;
            }
            
            $mastercustomerModel->update($id, $data);
            Session::flash('success', 'Customer berhasil diupdate');
            $this->redirect('/mastercustomer');
        }
        
        $data = ['customer' => $customer];
        $this->view('mastercustomer/edit', $data);
    }

    public function map() {
        Auth::requireRole(['admin', 'manajemen', 'operator', 'sales']);

        $config = require __DIR__ . '/../config/app.php';
        $mapboxToken = getenv('MAPBOX_ACCESS_TOKEN') ?: ($config['mapbox_access_token'] ?? null);

        $customerId = isset($_GET['customer_id']) ? (int)$_GET['customer_id'] : null;
        $mastercustomerModel = new Mastercustomer();
        $customer = null;
        $customerError = null;

        if ($customerId) {
            $customer = $mastercustomerModel->findById($customerId);
            if (!$customer) {
                $customerError = 'Customer dengan ID tersebut tidak ditemukan.';
                $customerId = null;
            }
        }

        $data = [
            'mapboxToken' => $mapboxToken,
            'customer' => $customer,
            'customerId' => $customerId,
            'customerError' => $customerError
        ];

        $this->view('mastercustomer/map', $data);
    }
}

