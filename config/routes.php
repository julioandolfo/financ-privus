<?php
/**
 * Definição de rotas da aplicação
 * Formato: 'METHOD /path' => 'Controller@method'
 */

return [
    // Autenticação (sem middleware)
    'GET /login' => ['handler' => 'AuthController@loginForm', 'middleware' => []],
    'POST /login' => ['handler' => 'AuthController@login', 'middleware' => []],
    'POST /logout' => ['handler' => 'AuthController@logout', 'middleware' => ['AuthMiddleware']],
    
    // Home (protegido)
    'GET /' => ['handler' => 'HomeController@index', 'middleware' => ['AuthMiddleware']],
    
    // Empresas (protegido)
    'GET /empresas' => ['handler' => 'EmpresaController@index', 'middleware' => ['AuthMiddleware']],
    'GET /empresas/create' => ['handler' => 'EmpresaController@create', 'middleware' => ['AuthMiddleware']],
    'POST /empresas' => ['handler' => 'EmpresaController@store', 'middleware' => ['AuthMiddleware']],
    'GET /empresas/{id}' => ['handler' => 'EmpresaController@show', 'middleware' => ['AuthMiddleware']],
    'GET /empresas/{id}/edit' => ['handler' => 'EmpresaController@edit', 'middleware' => ['AuthMiddleware']],
    'POST /empresas/{id}' => ['handler' => 'EmpresaController@update', 'middleware' => ['AuthMiddleware']],
    'POST /empresas/{id}/delete' => ['handler' => 'EmpresaController@destroy', 'middleware' => ['AuthMiddleware']],
];

