<?php
/**
 * Definição de rotas da aplicação
 * Formato: 'METHOD /path' => 'Controller@method'
 */

return [
    // Home
    'GET /' => 'HomeController@index',
    
    // Autenticação
    'GET /login' => 'AuthController@loginForm',
    'POST /login' => 'AuthController@login',
    'POST /logout' => 'AuthController@logout',
    
    // Empresas
    'GET /empresas' => 'EmpresaController@index',
    'GET /empresas/create' => 'EmpresaController@create',
    'POST /empresas' => 'EmpresaController@store',
    'GET /empresas/{id}' => 'EmpresaController@show',
    'GET /empresas/{id}/edit' => 'EmpresaController@edit',
    'POST /empresas/{id}' => 'EmpresaController@update',
    'POST /empresas/{id}/delete' => 'EmpresaController@destroy',
];

