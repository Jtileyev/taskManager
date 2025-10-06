<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;

class DashboardController extends Controller
{
    public function index(): void
    {
        $user = Session::user();

        if (!$user) {
            $this->redirect('/');
        }

        $flashSuccess = Session::flash('success');

        $this->view('dashboard/index', [
            'user' => $user,
            'flashSuccess' => $flashSuccess,
        ]);
    }
}
