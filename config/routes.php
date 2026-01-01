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
    
    // API (protegido)
    'GET /api/cnpj/{cnpj}' => ['handler' => 'ApiController@buscarCNPJ', 'middleware' => ['AuthMiddleware']],
    
    // Home (protegido)
    'GET /' => ['handler' => 'HomeController@index', 'middleware' => ['AuthMiddleware']],
    'POST /dashboard/filtrar' => ['handler' => 'HomeController@filtrar', 'middleware' => ['AuthMiddleware']],
    'POST /dashboard/limpar-filtro' => ['handler' => 'HomeController@limparFiltro', 'middleware' => ['AuthMiddleware']],
    'GET /dashboard/limpar-filtro' => ['handler' => 'HomeController@limparFiltro', 'middleware' => ['AuthMiddleware']],
    
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
    
    // Minha Conta (protegido)
    'GET /minha-conta' => ['handler' => 'UsuarioController@minhaConta', 'middleware' => ['AuthMiddleware']],
    'POST /minha-conta' => ['handler' => 'UsuarioController@atualizarMinhaConta', 'middleware' => ['AuthMiddleware']],
    
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
    
    // Centros de Custo (protegido)
    'GET /centros-custo' => ['handler' => 'CentroCustoController@index', 'middleware' => ['AuthMiddleware']],
    'GET /centros-custo/create' => ['handler' => 'CentroCustoController@create', 'middleware' => ['AuthMiddleware']],
    'POST /centros-custo' => ['handler' => 'CentroCustoController@store', 'middleware' => ['AuthMiddleware']],
    'GET /centros-custo/{id}' => ['handler' => 'CentroCustoController@show', 'middleware' => ['AuthMiddleware']],
    'GET /centros-custo/{id}/edit' => ['handler' => 'CentroCustoController@edit', 'middleware' => ['AuthMiddleware']],
    'POST /centros-custo/{id}' => ['handler' => 'CentroCustoController@update', 'middleware' => ['AuthMiddleware']],
    'POST /centros-custo/{id}/delete' => ['handler' => 'CentroCustoController@destroy', 'middleware' => ['AuthMiddleware']],
    
    // Formas de Pagamento (protegido)
    'GET /formas-pagamento' => ['handler' => 'FormaPagamentoController@index', 'middleware' => ['AuthMiddleware']],
    'GET /formas-pagamento/create' => ['handler' => 'FormaPagamentoController@create', 'middleware' => ['AuthMiddleware']],
    'POST /formas-pagamento' => ['handler' => 'FormaPagamentoController@store', 'middleware' => ['AuthMiddleware']],
    'GET /formas-pagamento/{id}' => ['handler' => 'FormaPagamentoController@show', 'middleware' => ['AuthMiddleware']],
    'GET /formas-pagamento/{id}/edit' => ['handler' => 'FormaPagamentoController@edit', 'middleware' => ['AuthMiddleware']],
    'POST /formas-pagamento/{id}' => ['handler' => 'FormaPagamentoController@update', 'middleware' => ['AuthMiddleware']],
    'POST /formas-pagamento/{id}/delete' => ['handler' => 'FormaPagamentoController@destroy', 'middleware' => ['AuthMiddleware']],
    
    // Produtos (protegido)
    'GET /produtos' => ['handler' => 'ProdutoController@index', 'middleware' => ['AuthMiddleware']],
    'GET /produtos/relatorio-estoque' => ['handler' => 'ProdutoController@relatorioEstoque', 'middleware' => ['AuthMiddleware']],
    'GET /produtos/gerar-codigo-barras' => ['handler' => 'ProdutoController@gerarCodigoBarras', 'middleware' => ['AuthMiddleware']],
    'GET /produtos/create' => ['handler' => 'ProdutoController@create', 'middleware' => ['AuthMiddleware']],
    'POST /produtos' => ['handler' => 'ProdutoController@store', 'middleware' => ['AuthMiddleware']],
    'GET /produtos/{id}' => ['handler' => 'ProdutoController@show', 'middleware' => ['AuthMiddleware']],
    'GET /produtos/{id}/edit' => ['handler' => 'ProdutoController@edit', 'middleware' => ['AuthMiddleware']],
    'POST /produtos/{id}' => ['handler' => 'ProdutoController@update', 'middleware' => ['AuthMiddleware']],
    'POST /produtos/{id}/delete' => ['handler' => 'ProdutoController@destroy', 'middleware' => ['AuthMiddleware']],
    'POST /produtos/{id}/upload-foto' => ['handler' => 'ProdutoController@uploadFoto', 'middleware' => ['AuthMiddleware']],
    'POST /produtos/fotos/{id}/delete' => ['handler' => 'ProdutoController@deleteFoto', 'middleware' => ['AuthMiddleware']],
    'POST /produtos/fotos/{id}/principal' => ['handler' => 'ProdutoController@setFotoPrincipal', 'middleware' => ['AuthMiddleware']],
    'POST /produtos/{id}/variacoes' => ['handler' => 'ProdutoController@addVariacao', 'middleware' => ['AuthMiddleware']],
    'POST /produtos/variacoes/{id}' => ['handler' => 'ProdutoController@updateVariacao', 'middleware' => ['AuthMiddleware']],
    'POST /produtos/variacoes/{id}/delete' => ['handler' => 'ProdutoController@deleteVariacao', 'middleware' => ['AuthMiddleware']],
    'POST /produtos/{id}/tributos' => ['handler' => 'ProdutoController@updateTributos', 'middleware' => ['AuthMiddleware']],
    
    // Categorias de Produtos (protegido)
    'GET /categorias-produtos' => ['handler' => 'CategoriaProdutoController@index', 'middleware' => ['AuthMiddleware']],
    'GET /categorias-produtos/create' => ['handler' => 'CategoriaProdutoController@create', 'middleware' => ['AuthMiddleware']],
    'POST /categorias-produtos' => ['handler' => 'CategoriaProdutoController@store', 'middleware' => ['AuthMiddleware']],
    'GET /categorias-produtos/{id}/edit' => ['handler' => 'CategoriaProdutoController@edit', 'middleware' => ['AuthMiddleware']],
    'POST /categorias-produtos/{id}' => ['handler' => 'CategoriaProdutoController@update', 'middleware' => ['AuthMiddleware']],
    'POST /categorias-produtos/{id}/delete' => ['handler' => 'CategoriaProdutoController@destroy', 'middleware' => ['AuthMiddleware']],
    
    // Pedidos Vinculados (protegido)
    'GET /pedidos' => ['handler' => 'PedidoVinculadoController@index', 'middleware' => ['AuthMiddleware']],
    'GET /pedidos/create' => ['handler' => 'PedidoVinculadoController@create', 'middleware' => ['AuthMiddleware']],
    'POST /pedidos' => ['handler' => 'PedidoVinculadoController@store', 'middleware' => ['AuthMiddleware']],
    'GET /pedidos/{id}' => ['handler' => 'PedidoVinculadoController@show', 'middleware' => ['AuthMiddleware']],
    'POST /pedidos/{id}/status' => ['handler' => 'PedidoVinculadoController@updateStatus', 'middleware' => ['AuthMiddleware']],
    'POST /pedidos/{id}/delete' => ['handler' => 'PedidoVinculadoController@destroy', 'middleware' => ['AuthMiddleware']],
    
    // Contas Bancárias (protegido)
    'GET /contas-bancarias' => ['handler' => 'ContaBancariaController@index', 'middleware' => ['AuthMiddleware']],
    'GET /contas-bancarias/create' => ['handler' => 'ContaBancariaController@create', 'middleware' => ['AuthMiddleware']],
    'POST /contas-bancarias' => ['handler' => 'ContaBancariaController@store', 'middleware' => ['AuthMiddleware']],
    'GET /contas-bancarias/{id}' => ['handler' => 'ContaBancariaController@show', 'middleware' => ['AuthMiddleware']],
    'GET /contas-bancarias/{id}/edit' => ['handler' => 'ContaBancariaController@edit', 'middleware' => ['AuthMiddleware']],
    'POST /contas-bancarias/{id}' => ['handler' => 'ContaBancariaController@update', 'middleware' => ['AuthMiddleware']],
    'POST /contas-bancarias/{id}/delete' => ['handler' => 'ContaBancariaController@destroy', 'middleware' => ['AuthMiddleware']],
    
    // Contas a Pagar (protegido)
    'GET /contas-pagar' => ['handler' => 'ContaPagarController@index', 'middleware' => ['AuthMiddleware']],
    'GET /contas-pagar/create' => ['handler' => 'ContaPagarController@create', 'middleware' => ['AuthMiddleware']],
    'POST /contas-pagar' => ['handler' => 'ContaPagarController@store', 'middleware' => ['AuthMiddleware']],
    'GET /contas-pagar/{id}' => ['handler' => 'ContaPagarController@show', 'middleware' => ['AuthMiddleware']],
    'GET /contas-pagar/{id}/edit' => ['handler' => 'ContaPagarController@edit', 'middleware' => ['AuthMiddleware']],
    'POST /contas-pagar/{id}' => ['handler' => 'ContaPagarController@update', 'middleware' => ['AuthMiddleware']],
    'POST /contas-pagar/{id}/delete' => ['handler' => 'ContaPagarController@destroy', 'middleware' => ['AuthMiddleware']],
    'GET /contas-pagar/{id}/baixar' => ['handler' => 'ContaPagarController@baixar', 'middleware' => ['AuthMiddleware']],
    'POST /contas-pagar/{id}/baixar' => ['handler' => 'ContaPagarController@efetuarBaixa', 'middleware' => ['AuthMiddleware']],
    
    // Contas a Receber (protegido)
    'GET /contas-receber' => ['handler' => 'ContaReceberController@index', 'middleware' => ['AuthMiddleware']],
    'GET /contas-receber/create' => ['handler' => 'ContaReceberController@create', 'middleware' => ['AuthMiddleware']],
    'POST /contas-receber' => ['handler' => 'ContaReceberController@store', 'middleware' => ['AuthMiddleware']],
    'GET /contas-receber/{id}' => ['handler' => 'ContaReceberController@show', 'middleware' => ['AuthMiddleware']],
    'GET /contas-receber/{id}/edit' => ['handler' => 'ContaReceberController@edit', 'middleware' => ['AuthMiddleware']],
    'POST /contas-receber/{id}' => ['handler' => 'ContaReceberController@update', 'middleware' => ['AuthMiddleware']],
    'POST /contas-receber/{id}/delete' => ['handler' => 'ContaReceberController@destroy', 'middleware' => ['AuthMiddleware']],
    'GET /contas-receber/{id}/baixar' => ['handler' => 'ContaReceberController@baixar', 'middleware' => ['AuthMiddleware']],
    'POST /contas-receber/{id}/baixar' => ['handler' => 'ContaReceberController@efetuarBaixa', 'middleware' => ['AuthMiddleware']],
    
    // Fluxo de Caixa (protegido)
    'GET /fluxo-caixa' => ['handler' => 'FluxoCaixaController@index', 'middleware' => ['AuthMiddleware']],
    
    // DRE (protegido)
    'GET /dre' => ['handler' => 'DREController@index', 'middleware' => ['AuthMiddleware']],
    
    // DFC (protegido)
    'GET /dfc' => ['handler' => 'DFCController@index', 'middleware' => ['AuthMiddleware']],
    'GET /dfc/exportar-pdf' => ['handler' => 'DFCController@exportarPDF', 'middleware' => ['AuthMiddleware']],
    
    // Relatórios Gerais (protegido)
    'GET /relatorios' => ['handler' => 'RelatorioController@index', 'middleware' => ['AuthMiddleware']],
    'GET /relatorios/lucro' => ['handler' => 'RelatorioController@lucro', 'middleware' => ['AuthMiddleware']],
    'GET /relatorios/margem' => ['handler' => 'RelatorioController@margem', 'middleware' => ['AuthMiddleware']],
    'GET /relatorios/inadimplencia' => ['handler' => 'RelatorioController@inadimplencia', 'middleware' => ['AuthMiddleware']],
    
    // Configurações (protegido)
    'GET /configuracoes' => ['handler' => 'ConfiguracaoController@index', 'middleware' => ['AuthMiddleware']],
    'POST /configuracoes/salvar' => ['handler' => 'ConfiguracaoController@salvar', 'middleware' => ['AuthMiddleware']],
    
    // Conciliação Bancária (protegido)
    'GET /conciliacao-bancaria' => ['handler' => 'ConciliacaoBancariaController@index', 'middleware' => ['AuthMiddleware']],
    'GET /conciliacao-bancaria/create' => ['handler' => 'ConciliacaoBancariaController@create', 'middleware' => ['AuthMiddleware']],
    'POST /conciliacao-bancaria/store' => ['handler' => 'ConciliacaoBancariaController@store', 'middleware' => ['AuthMiddleware']],
    'GET /conciliacao-bancaria/{id}' => ['handler' => 'ConciliacaoBancariaController@show', 'middleware' => ['AuthMiddleware']],
    'POST /conciliacao-bancaria/{id}/importar-extrato' => ['handler' => 'ConciliacaoBancariaController@importarExtrato', 'middleware' => ['AuthMiddleware']],
    'POST /conciliacao-bancaria/processar-extrato' => ['handler' => 'ConciliacaoBancariaController@processarExtrato', 'middleware' => ['AuthMiddleware']],
    'POST /conciliacao-bancaria/{id}/analisar-ia' => ['handler' => 'ConciliacaoBancariaController@analisarIA', 'middleware' => ['AuthMiddleware']],
    'POST /conciliacao-bancaria/vincular' => ['handler' => 'ConciliacaoBancariaController@vincular', 'middleware' => ['AuthMiddleware']],
    'POST /conciliacao-bancaria/desvincular' => ['handler' => 'ConciliacaoBancariaController@desvincular', 'middleware' => ['AuthMiddleware']],
    'POST /conciliacao-bancaria/{id}/fechar' => ['handler' => 'ConciliacaoBancariaController@fechar', 'middleware' => ['AuthMiddleware']],
    'POST /conciliacao-bancaria/{id}/reabrir' => ['handler' => 'ConciliacaoBancariaController@reabrir', 'middleware' => ['AuthMiddleware']],
    'POST /conciliacao-bancaria/{id}/delete' => ['handler' => 'ConciliacaoBancariaController@destroy', 'middleware' => ['AuthMiddleware']],
    
    // Movimentações de Caixa (protegido)
    'GET /movimentacoes-caixa' => ['handler' => 'MovimentacaoCaixaController@index', 'middleware' => ['AuthMiddleware']],
    'GET /movimentacoes-caixa/create' => ['handler' => 'MovimentacaoCaixaController@create', 'middleware' => ['AuthMiddleware']],
    'POST /movimentacoes-caixa' => ['handler' => 'MovimentacaoCaixaController@store', 'middleware' => ['AuthMiddleware']],
    'GET /movimentacoes-caixa/{id}' => ['handler' => 'MovimentacaoCaixaController@show', 'middleware' => ['AuthMiddleware']],
    'GET /movimentacoes-caixa/{id}/edit' => ['handler' => 'MovimentacaoCaixaController@edit', 'middleware' => ['AuthMiddleware']],
    'POST /movimentacoes-caixa/{id}' => ['handler' => 'MovimentacaoCaixaController@update', 'middleware' => ['AuthMiddleware']],
    'POST /movimentacoes-caixa/{id}/delete' => ['handler' => 'MovimentacaoCaixaController@destroy', 'middleware' => ['AuthMiddleware']],
    
    // Perfis de Consolidação (protegido)
    'GET /perfis-consolidacao' => ['handler' => 'PerfilConsolidacaoController@index', 'middleware' => ['AuthMiddleware']],
    'GET /perfis-consolidacao/create' => ['handler' => 'PerfilConsolidacaoController@create', 'middleware' => ['AuthMiddleware']],
    'POST /perfis-consolidacao' => ['handler' => 'PerfilConsolidacaoController@store', 'middleware' => ['AuthMiddleware']],
    'GET /perfis-consolidacao/{id}' => ['handler' => 'PerfilConsolidacaoController@show', 'middleware' => ['AuthMiddleware']],
    'GET /perfis-consolidacao/{id}/edit' => ['handler' => 'PerfilConsolidacaoController@edit', 'middleware' => ['AuthMiddleware']],
    'POST /perfis-consolidacao/{id}' => ['handler' => 'PerfilConsolidacaoController@update', 'middleware' => ['AuthMiddleware']],
    'POST /perfis-consolidacao/{id}/delete' => ['handler' => 'PerfilConsolidacaoController@destroy', 'middleware' => ['AuthMiddleware']],
    'GET /perfis-consolidacao/{id}/aplicar' => ['handler' => 'PerfilConsolidacaoController@aplicar', 'middleware' => ['AuthMiddleware']],
    'POST /perfis-consolidacao/aplicar-custom' => ['handler' => 'PerfilConsolidacaoController@aplicarCustom', 'middleware' => ['AuthMiddleware']],
    'GET /perfis-consolidacao/limpar' => ['handler' => 'PerfilConsolidacaoController@limpar', 'middleware' => ['AuthMiddleware']],
    
    // Integrações (protegido)
    'GET /integracoes' => ['handler' => 'IntegracaoController@index', 'middleware' => ['AuthMiddleware']],
    'GET /integracoes/create' => ['handler' => 'IntegracaoController@create', 'middleware' => ['AuthMiddleware']],
    'GET /integracoes/create/{tipo}' => ['handler' => 'IntegracaoController@createTipo', 'middleware' => ['AuthMiddleware']],
    'POST /integracoes/woocommerce' => ['handler' => 'IntegracaoController@storeWooCommerce', 'middleware' => ['AuthMiddleware']],
    'POST /integracoes/banco-dados' => ['handler' => 'IntegracaoController@storeBancoDados', 'middleware' => ['AuthMiddleware']],
    'POST /integracoes/webhook' => ['handler' => 'IntegracaoController@storeWebhook', 'middleware' => ['AuthMiddleware']],
    'POST /integracoes/api' => ['handler' => 'IntegracaoController@storeApi', 'middleware' => ['AuthMiddleware']],
    'POST /integracoes/webmanibr' => ['handler' => 'IntegracaoController@storeWebmaniBR', 'middleware' => ['AuthMiddleware']],
    'GET /integracoes/{id}' => ['handler' => 'IntegracaoController@show', 'middleware' => ['AuthMiddleware']],
    'POST /integracoes/{id}/sincronizar' => ['handler' => 'IntegracaoController@sincronizar', 'middleware' => ['AuthMiddleware']],
    'POST /integracoes/{id}/delete' => ['handler' => 'IntegracaoController@destroy', 'middleware' => ['AuthMiddleware']],
    'POST /integracoes/{id}/toggle' => ['handler' => 'IntegracaoController@toggleStatus', 'middleware' => ['AuthMiddleware']],
    'POST /integracoes/testar-woocommerce' => ['handler' => 'IntegracaoController@testarWooCommerce', 'middleware' => ['AuthMiddleware']],
    'POST /integracoes/testar-banco-dados' => ['handler' => 'IntegracaoController@testarBancoDados', 'middleware' => ['AuthMiddleware']],
    'POST /integracoes/testar-api' => ['handler' => 'IntegracaoController@testarApi', 'middleware' => ['AuthMiddleware']],
    'POST /integracoes/testar-webmanibr' => ['handler' => 'IntegracaoController@testarWebmaniBR', 'middleware' => ['AuthMiddleware']],
    
    // Rotas de NF-e
    'GET /nfes' => ['handler' => 'NFeController@index', 'middleware' => ['AuthMiddleware']],
    'GET /nfes/{id}' => ['handler' => 'NFeController@show', 'middleware' => ['AuthMiddleware']],
    'POST /nfes/emitir/{pedidoId}' => ['handler' => 'NFeController@emitir', 'middleware' => ['AuthMiddleware']],
    'POST /nfes/{id}/cancelar' => ['handler' => 'NFeController@cancelar', 'middleware' => ['AuthMiddleware']],
    'POST /nfes/{id}/consultar' => ['handler' => 'NFeController@consultar', 'middleware' => ['AuthMiddleware']],
    'GET /nfes/{id}/download-xml' => ['handler' => 'NFeController@downloadXML', 'middleware' => ['AuthMiddleware']],
    'GET /nfes/{id}/download-danfe' => ['handler' => 'NFeController@downloadDANFE', 'middleware' => ['AuthMiddleware']],
    
    // Webhook (público - sem middleware)
    'POST /webhook/woocommerce/{integracaoId}' => ['handler' => 'IntegracaoController@webhook'],
    
    // API Tokens (protegido)
    'GET /api-tokens' => ['handler' => 'ApiTokenController@index', 'middleware' => ['AuthMiddleware']],
    'GET /api-tokens/create' => ['handler' => 'ApiTokenController@create', 'middleware' => ['AuthMiddleware']],
    'POST /api-tokens' => ['handler' => 'ApiTokenController@store', 'middleware' => ['AuthMiddleware']],
    'GET /api-tokens/{id}' => ['handler' => 'ApiTokenController@show', 'middleware' => ['AuthMiddleware']],
    'GET /api-tokens/{id}/edit' => ['handler' => 'ApiTokenController@edit', 'middleware' => ['AuthMiddleware']],
    'POST /api-tokens/{id}' => ['handler' => 'ApiTokenController@update', 'middleware' => ['AuthMiddleware']],
    'POST /api-tokens/{id}/delete' => ['handler' => 'ApiTokenController@destroy', 'middleware' => ['AuthMiddleware']],
    'POST /api-tokens/{id}/regenerate' => ['handler' => 'ApiTokenController@regenerate', 'middleware' => ['AuthMiddleware']],
    
    // ========================================
    // API REST - Endpoints Públicos
    // ========================================
    // Autenticação via Bearer Token (ApiAuthMiddleware)
    
    // Contas a Pagar
    'GET /api/v1/contas-pagar' => ['handler' => 'ApiRestController@contasPagarIndex', 'middleware' => ['ApiAuthMiddleware']],
    'GET /api/v1/contas-pagar/{id}' => ['handler' => 'ApiRestController@contasPagarShow', 'middleware' => ['ApiAuthMiddleware']],
    'POST /api/v1/contas-pagar' => ['handler' => 'ApiRestController@contasPagarStore', 'middleware' => ['ApiAuthMiddleware']],
    'PUT /api/v1/contas-pagar/{id}' => ['handler' => 'ApiRestController@contasPagarUpdate', 'middleware' => ['ApiAuthMiddleware']],
    'DELETE /api/v1/contas-pagar/{id}' => ['handler' => 'ApiRestController@contasPagarDelete', 'middleware' => ['ApiAuthMiddleware']],
    
    // Contas a Receber
    'GET /api/v1/contas-receber' => ['handler' => 'ApiRestController@contasReceberIndex', 'middleware' => ['ApiAuthMiddleware']],
    'GET /api/v1/contas-receber/{id}' => ['handler' => 'ApiRestController@contasReceberShow', 'middleware' => ['ApiAuthMiddleware']],
    'POST /api/v1/contas-receber' => ['handler' => 'ApiRestController@contasReceberStore', 'middleware' => ['ApiAuthMiddleware']],
    'PUT /api/v1/contas-receber/{id}' => ['handler' => 'ApiRestController@contasReceberUpdate', 'middleware' => ['ApiAuthMiddleware']],
    'DELETE /api/v1/contas-receber/{id}' => ['handler' => 'ApiRestController@contasReceberDelete', 'middleware' => ['ApiAuthMiddleware']],
    
    // Produtos
    'GET /api/v1/produtos' => ['handler' => 'ApiRestController@produtosIndex', 'middleware' => ['ApiAuthMiddleware']],
    'GET /api/v1/produtos/{id}' => ['handler' => 'ApiRestController@produtosShow', 'middleware' => ['ApiAuthMiddleware']],
    'POST /api/v1/produtos' => ['handler' => 'ApiRestController@produtosStore', 'middleware' => ['ApiAuthMiddleware']],
    'PUT /api/v1/produtos/{id}' => ['handler' => 'ApiRestController@produtosUpdate', 'middleware' => ['ApiAuthMiddleware']],
    'DELETE /api/v1/produtos/{id}' => ['handler' => 'ApiRestController@produtosDelete', 'middleware' => ['ApiAuthMiddleware']],
    
    // Clientes
    'GET /api/v1/clientes' => ['handler' => 'ApiRestController@clientesIndex', 'middleware' => ['ApiAuthMiddleware']],
    'GET /api/v1/clientes/{id}' => ['handler' => 'ApiRestController@clientesShow', 'middleware' => ['ApiAuthMiddleware']],
    'POST /api/v1/clientes' => ['handler' => 'ApiRestController@clientesStore', 'middleware' => ['ApiAuthMiddleware']],
    'PUT /api/v1/clientes/{id}' => ['handler' => 'ApiRestController@clientesUpdate', 'middleware' => ['ApiAuthMiddleware']],
    'DELETE /api/v1/clientes/{id}' => ['handler' => 'ApiRestController@clientesDelete', 'middleware' => ['ApiAuthMiddleware']],
    
    // Fornecedores
    'GET /api/v1/fornecedores' => ['handler' => 'ApiRestController@fornecedoresIndex', 'middleware' => ['ApiAuthMiddleware']],
    'GET /api/v1/fornecedores/{id}' => ['handler' => 'ApiRestController@fornecedoresShow', 'middleware' => ['ApiAuthMiddleware']],
    'POST /api/v1/fornecedores' => ['handler' => 'ApiRestController@fornecedoresStore', 'middleware' => ['ApiAuthMiddleware']],
    'PUT /api/v1/fornecedores/{id}' => ['handler' => 'ApiRestController@fornecedoresUpdate', 'middleware' => ['ApiAuthMiddleware']],
    'DELETE /api/v1/fornecedores/{id}' => ['handler' => 'ApiRestController@fornecedoresDelete', 'middleware' => ['ApiAuthMiddleware']],
    
    // Movimentações de Caixa
    'GET /api/v1/movimentacoes' => ['handler' => 'ApiRestController@movimentacoesIndex', 'middleware' => ['ApiAuthMiddleware']],
    'GET /api/v1/movimentacoes/{id}' => ['handler' => 'ApiRestController@movimentacoesShow', 'middleware' => ['ApiAuthMiddleware']],
    'POST /api/v1/movimentacoes' => ['handler' => 'ApiRestController@movimentacoesStore', 'middleware' => ['ApiAuthMiddleware']],
    
    // Categorias Financeiras
    'GET /api/v1/categorias' => ['handler' => 'ApiRestController@categoriasIndex', 'middleware' => ['ApiAuthMiddleware']],
    'GET /api/v1/categorias/{id}' => ['handler' => 'ApiRestController@categoriasShow', 'middleware' => ['ApiAuthMiddleware']],
    'POST /api/v1/categorias' => ['handler' => 'ApiRestController@categoriasStore', 'middleware' => ['ApiAuthMiddleware']],
    
    // Centros de Custo
    'GET /api/v1/centros-custo' => ['handler' => 'ApiRestController@centrosCustoIndex', 'middleware' => ['ApiAuthMiddleware']],
    'GET /api/v1/centros-custo/{id}' => ['handler' => 'ApiRestController@centrosCustoShow', 'middleware' => ['ApiAuthMiddleware']],
    'POST /api/v1/centros-custo' => ['handler' => 'ApiRestController@centrosCustoStore', 'middleware' => ['ApiAuthMiddleware']],
    
    // Contas Bancárias
    'GET /api/v1/contas-bancarias' => ['handler' => 'ApiRestController@contasBancariasIndex', 'middleware' => ['ApiAuthMiddleware']],
    'GET /api/v1/contas-bancarias/{id}' => ['handler' => 'ApiRestController@contasBancariasShow', 'middleware' => ['ApiAuthMiddleware']],
    'POST /api/v1/contas-bancarias' => ['handler' => 'ApiRestController@contasBancariasStore', 'middleware' => ['ApiAuthMiddleware']],
];

