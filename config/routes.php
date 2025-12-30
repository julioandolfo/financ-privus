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
    
    // Usuários (protegido)
    'GET /usuarios' => ['handler' => 'UsuarioController@index', 'middleware' => ['AuthMiddleware']],
    'GET /usuarios/create' => ['handler' => 'UsuarioController@create', 'middleware' => ['AuthMiddleware']],
    'POST /usuarios' => ['handler' => 'UsuarioController@store', 'middleware' => ['AuthMiddleware']],
    'GET /usuarios/{id}' => ['handler' => 'UsuarioController@show', 'middleware' => ['AuthMiddleware']],
    'GET /usuarios/{id}/edit' => ['handler' => 'UsuarioController@edit', 'middleware' => ['AuthMiddleware']],
    'POST /usuarios/{id}' => ['handler' => 'UsuarioController@update', 'middleware' => ['AuthMiddleware']],
    'POST /usuarios/{id}/delete' => ['handler' => 'UsuarioController@destroy', 'middleware' => ['AuthMiddleware']],
    
    // Fornecedores (protegido)
    'GET /fornecedores' => ['handler' => 'FornecedorController@index', 'middleware' => ['AuthMiddleware']],
    'GET /fornecedores/create' => ['handler' => 'FornecedorController@create', 'middleware' => ['AuthMiddleware']],
    'POST /fornecedores' => ['handler' => 'FornecedorController@store', 'middleware' => ['AuthMiddleware']],
    'GET /fornecedores/{id}' => ['handler' => 'FornecedorController@show', 'middleware' => ['AuthMiddleware']],
    'GET /fornecedores/{id}/edit' => ['handler' => 'FornecedorController@edit', 'middleware' => ['AuthMiddleware']],
    'POST /fornecedores/{id}' => ['handler' => 'FornecedorController@update', 'middleware' => ['AuthMiddleware']],
    'POST /fornecedores/{id}/delete' => ['handler' => 'FornecedorController@destroy', 'middleware' => ['AuthMiddleware']],
    
    // Clientes (protegido)
    'GET /clientes' => ['handler' => 'ClienteController@index', 'middleware' => ['AuthMiddleware']],
    'GET /clientes/create' => ['handler' => 'ClienteController@create', 'middleware' => ['AuthMiddleware']],
    'POST /clientes' => ['handler' => 'ClienteController@store', 'middleware' => ['AuthMiddleware']],
    'GET /clientes/{id}' => ['handler' => 'ClienteController@show', 'middleware' => ['AuthMiddleware']],
    'GET /clientes/{id}/edit' => ['handler' => 'ClienteController@edit', 'middleware' => ['AuthMiddleware']],
    'POST /clientes/{id}' => ['handler' => 'ClienteController@update', 'middleware' => ['AuthMiddleware']],
    'POST /clientes/{id}/delete' => ['handler' => 'ClienteController@destroy', 'middleware' => ['AuthMiddleware']],
    
    // Categorias Financeiras (protegido)
    'GET /categorias' => ['handler' => 'CategoriaController@index', 'middleware' => ['AuthMiddleware']],
    'GET /categorias/create' => ['handler' => 'CategoriaController@create', 'middleware' => ['AuthMiddleware']],
    'POST /categorias' => ['handler' => 'CategoriaController@store', 'middleware' => ['AuthMiddleware']],
    'GET /categorias/{id}' => ['handler' => 'CategoriaController@show', 'middleware' => ['AuthMiddleware']],
    'GET /categorias/{id}/edit' => ['handler' => 'CategoriaController@edit', 'middleware' => ['AuthMiddleware']],
    'POST /categorias/{id}' => ['handler' => 'CategoriaController@update', 'middleware' => ['AuthMiddleware']],
    'POST /categorias/{id}/delete' => ['handler' => 'CategoriaController@destroy', 'middleware' => ['AuthMiddleware']],
];

