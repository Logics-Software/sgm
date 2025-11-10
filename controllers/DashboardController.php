<?php
class DashboardController extends Controller {
    public function index() {
        Auth::requireAuth();
        
        $user = Auth::user();
        $userModel = new User();
        $totalUsers = $userModel->count();
        
        $data = [
            'user' => $user,
            'totalUsers' => $totalUsers
        ];
        
        $this->view('dashboard/index', $data);
    }
}

