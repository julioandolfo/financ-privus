# 📘 Regras do Cursor para Sistema Financeiro

Este documento contém as regras e padrões que o Cursor deve seguir ao trabalhar neste projeto.

## 🎯 CONTEXTO DO PROJETO

Sistema financeiro empresarial em PHP puro com arquitetura MVC customizada. Gerencia empresas, usuários, fornecedores, clientes, contas financeiras, relatórios e integrações.

## 🏗️ ARQUITETURA E ESTRUTURA

### Estrutura de Diretórios
- `app/core/` - Classes base (App, Router, Controller, Model, Database, Session)
- `app/controllers/` - Controllers (um por módulo)
- `app/models/` - Models (um por entidade)
- `app/views/` - Views organizadas por módulo
- `config/routes.php` - Todas as rotas do sistema
- `migrations/` - Migrations do banco de dados

### Padrão MVC
- **Models**: Estendem `App\Core\Model`, contêm lógica de negócio
- **Controllers**: Estendem `App\Core\Controller`, recebem `Request` e `Response`
- **Views**: PHP puro com HTML/TailwindCSS

## 📝 CONVENÇÕES DE CÓDIGO

### Nomenclatura
- **Controllers**: `{Nome}Controller.php` (ex: `EmpresaController.php`)
- **Models**: `{Nome}.php` no singular (ex: `Empresa.php`)
- **Migrations**: `{numero}_{descricao}.php` (ex: `001_create_empresas.php`)

### Métodos de Controller
- `index()` - Listagem
- `create()` - Formulário de criação
- `store()` - Processa criação (POST)
- `show($id)` - Detalhes
- `edit($id)` - Formulário de edição
- `update($id)` - Processa edição (POST)
- `destroy($id)` - Exclusão (POST)

### Métodos de Model
- `findAll($empresaId = null)` - Lista todos (sempre filtrar `ativo = 1`)
- `findById($id)` - Busca por ID
- `create($data)` - Cria registro
- `update($id, $data)` - Atualiza registro
- `delete($id)` - Soft delete (marca `ativo = 0`)

### Validação
- **Método**: `protected function validate($data, $id = null)` - **NUNCA** `private`
- **Retorno**: Array de erros `['campo' => 'mensagem']`
- **Armazenamento**: `$this->session->set('errors', $errors)` e `$this->session->set('old', $data)`

## 🎨 PADRÕES DE VIEWS

### Estrutura Base
```php
<div class="max-w-{tamanho} mx-auto">
    <!-- Header com título -->
    <!-- Conteúdo -->
    <!-- Botões de ação -->
</div>
```

### Tema Dark/Light
- **Sempre usar**: Classes Tailwind com variantes `dark:`
- **Exemplo**: `bg-white dark:bg-gray-800`, `text-gray-900 dark:text-gray-100`
- **Background**: Definido no `main.php`, views NÃO devem ter backgrounds próprios
- **Não usar**: `min-h-screen` ou backgrounds nas views individuais

### Formulários
- **Campos obrigatórios**: Marcados com `*` e `required`
- **Validação**: HTML5 (`pattern`, `minlength`) + server-side
- **Máscaras**: `data-mask="cnpj"`, `data-mask="cpf"`, etc.
- **Erros**: `$this->session->get('errors')['campo']`
- **Old values**: `$this->session->get('old')['campo']`
- **Limpar sessão**: Sempre no final com `$this->session->delete('old')` e `$this->session->delete('errors')`

### Output Seguro
- **Sempre usar**: `htmlspecialchars()` para output de dados do usuário
- **Exemplo**: `<?= htmlspecialchars($variavel) ?>`

## 🔐 SEGURANÇA

### Banco de Dados
- **Sempre usar**: Prepared statements (PDO)
- **Nunca**: Concatenar SQL diretamente
- **Soft Delete**: Sempre filtrar `WHERE ativo = 1` em `findAll()`
- **Retorno vazio**: Retornar `?: []` quando não houver resultados

### Validação
- **Client-side**: HTML5 validation + JavaScript (`masks.js`)
- **Server-side**: Método `validate()` no Controller
- **Mensagens**: Armazenar em sessão e exibir na view

### Permissões
- **Model**: `App\Models\Permissao`
- **Estrutura**: `usuario_id`, `modulo`, `acao`, `empresa_id`
- **Módulos**: Definidos em `Permissao::MODULOS` e `Permissao::ACOES`

## 🎯 CHECKLIST PARA NOVOS MÓDULOS

Ao criar um novo módulo:

1. ✅ Criar migration em `migrations/`
2. ✅ Criar Model em `app/models/`
3. ✅ Criar Controller em `app/controllers/`
4. ✅ Criar views: `index.php`, `create.php`, `edit.php`, `show.php`
5. ✅ Adicionar rotas em `config/routes.php` com `AuthMiddleware`
6. ✅ Adicionar link no `sidebar.php` se necessário
7. ✅ Implementar `validate()` como `protected` (não `private`)
8. ✅ Adicionar módulo em `Permissao::MODULOS` se necessário
9. ✅ Usar soft delete (`ativo` campo)
10. ✅ Garantir suporte ao tema dark

## ⚠️ REGRAS CRÍTICAS

1. **NUNCA** usar `private` para `validate()` - sempre `protected`
2. **SEMPRE** usar `htmlspecialchars()` para output
3. **SEMPRE** usar prepared statements (PDO)
4. **SEMPRE** filtrar `ativo = 1` em `findAll()`
5. **SEMPRE** retornar `?: []` quando não houver resultados
6. **SEMPRE** limpar `old` e `errors` após exibir
7. **NUNCA** colocar backgrounds nas views individuais
8. **SEMPRE** usar caminhos absolutos para assets (`/assets/js/...`)
9. **SEMPRE** incluir validação client e server-side
10. **SEMPRE** usar `Request` e `Response` como parâmetros

## 🔄 FLUXO DE DADOS

1. **Request** → Dados via `Request::all()` ou `Request::get()`
2. **Validação** → Controller chama `validate()`
3. **Model** → Controller instancia Model e chama métodos
4. **Database** → Model usa PDO com prepared statements
5. **Response** → Controller redireciona ou renderiza view

## 📦 ASSETS

- **JavaScript**: `/assets/js/{arquivo}.js` (caminho absoluto)
- **Máscaras**: `masks.js` para CPF, CNPJ, telefone, CEP
- **Tema**: `theme.js` gerencia light/dark/system

## 🌳 ESTRUTURAS HIERÁRQUICAS

Para módulos com hierarquia (Categorias, Centros de Custo):
- Campo pai: `{nome}_pai_id` (FK para mesma tabela)
- Métodos: `buildTree()`, `getPath()`, `canBeParent()`
- Views: Suportar visualização `flat` e `tree`

---

**Use estas regras como referência ao trabalhar neste projeto.**

