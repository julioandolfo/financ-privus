<?php
namespace App\Controllers;

use App\Core\Controller;

class HomeController extends Controller
{
    public function index()
    {
        $this->render('home/index', [
            'title' => 'Dashboard',
            'message' => 'Bem-vindo ao Sistema Financeiro Empresarial'
        ]);
    }
}

