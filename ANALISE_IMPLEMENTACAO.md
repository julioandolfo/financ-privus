# 📊 ANÁLISE DE IMPLEMENTAÇÃO - Sistema Financeiro

**Data da Análise**: 30/12/2025  
**Comparação com**: `DOCUMENTACAO_COMPLETA.md`

---

## ✅ O QUE FOI IMPLEMENTADO

### 🏗️ FASE 1 - ESTRUTURA BASE E CORE MVC ✅ **100% COMPLETO**

#### ✅ Arquitetura MVC
- [x] Estrutura de pastas MVC completa (`app/core`, `app/controllers`, `app/models`, `app/views`)
- [x] Classes core implementadas:
  - [x] `App.php` - Classe principal da aplicação
  - [x] `Router.php` - Sistema de rotas com parâmetros dinâmicos
  - [x] `Controller.php` - Classe base para controllers
  - [x] `Model.php` - Classe base para models
  - [x] `Database.php` - Conexão PDO (Singleton)
  - [x] `Request.php` - Manipulação de requisições
  - [x] `Response.php` - Manipulação de respostas
  - [x] `Session.php` - Gerenciamento de sessões

#### ✅ Sistema de Rotas
- [x] Router funcional com suporte a parâmetros dinâmicos (`{id}`)
- [x] Suporte a métodos HTTP (GET, POST, PUT, DELETE)
- [x] Sistema de rotas configurável via `config/routes.php`
- [x] Middleware por rota

#### ✅ Autenticação e Segurança
- [x] `AuthController` - Login e logout
- [x] `AuthMiddleware` - Proteção de rotas
- [x] Sistema de sessões
- [x] Hash de senhas (`password_hash`)
- [x] Validação de inputs
- [x] Prepared statements (PDO) - proteção SQL Injection
- [x] XSS protection (`htmlspecialchars`)

#### ✅ Sistema de Migrations ✅ **100% COMPLETO**
- [x] `Migration.php` - Classe base para migrations
- [x] `MigrationManager.php` - Gerenciador de migrations
- [x] Script `migrate.php` para executar migrations
- [x] **27 migrations criadas** (000 a 026):
  - [x] 000_create_migrations_table.php
  - [x] 001_create_empresas.php
  - [x] 002_create_usuarios.php
  - [x] 003_create_perfis_consolidacao.php
  - [x] 004_create_permissoes.php
  - [x] 005_create_categorias_financeiras.php
  - [x] 006_create_centros_custo.php
  - [x] 007_create_contas_bancarias.php
  - [x] 008_create_formas_pagamento.php
  - [x] 009_create_fornecedores.php
  - [x] 010_create_clientes.php
  - [x] 011_create_produtos.php
  - [x] 012_create_contas_pagar.php
  - [x] 013_create_contas_receber.php
  - [x] 014_create_rateios_pagamentos.php
  - [x] 015_create_rateios_recebimentos.php
  - [x] 016_create_movimentacoes_caixa.php
  - [x] 017_create_formas_pagamento_padroes.php
  - [x] 018_create_conciliacao_bancaria.php
  - [x] 019_create_conciliacao_itens.php
  - [x] 020_create_pedidos_vinculados.php
  - [x] 021_create_pedidos_itens.php
  - [x] 022_create_integracoes_config.php
  - [x] 023_create_integracoes_bancos_dados.php
  - [x] 024_create_integracoes_woocommerce.php
  - [x] 025_create_integracoes_logs.php
  - [x] 026_create_integracoes_sincronizacoes.php

#### ✅ Configurações
- [x] `config/database.php` - Configurações de banco
- [x] `config/config.php` - Configurações gerais
- [x] `config/constants.php` - Constantes do sistema
- [x] `config/routes.php` - Definição de rotas
- [x] Sistema de variáveis de ambiente (`.env` via `EnvLoader.php`)

#### ✅ Interface e Frontend
- [x] Layout responsivo com TailwindCSS
- [x] Layout principal (`layouts/main.php`)
- [x] Layout de autenticação (`layouts/auth.php`)
- [x] Tema claro/escuro/sistema (`components/theme-selector.php`)
- [x] Componentes reutilizáveis
- [x] Design moderno e profissional

---

### 🏢 FASE 2 - MÓDULOS FUNDAMENTAIS ⚠️ **PARCIALMENTE COMPLETO (~30%)**

#### ✅ Módulo de Empresas ✅ **100% COMPLETO**
- [x] Model `Empresa.php` com métodos CRUD
- [x] Controller `EmpresaController.php` completo
- [x] Views completas:
  - [x] `empresas/index.php` - Listagem
  - [x] `empresas/create.php` - Formulário de criação
  - [x] `empresas/edit.php` - Formulário de edição
  - [x] `empresas/show.php` - Detalhes
- [x] Validações (CNPJ único, código único)
- [x] Soft delete (marca como inativa)
- [x] Filtros e busca
- [x] Método `findByIds()` para consolidação

#### ✅ Módulo de Usuários ✅ **100% COMPLETO**
- [x] Model `Usuario.php`
- [x] Controller `UsuarioController.php` completo
- [x] Views completas:
  - [x] `usuarios/index.php` - Listagem
  - [x] `usuarios/create.php` - Formulário de criação
  - [x] `usuarios/edit.php` - Formulário de edição
  - [x] `usuarios/show.php` - Detalhes
- [x] Validações (email único, senha forte)
- [x] Vinculação com empresa
- [x] Controle de status (ativo/inativo)
- [x] Script `update-password.php` para atualizar senhas

#### ⚠️ Módulo de Fornecedores ⚠️ **PARCIAL (~50%)**
- [x] Model `Fornecedor.php` existe
- [x] Controller `FornecedorController.php` existe
- [x] View `fornecedores/index.php` existe
- [ ] View `fornecedores/create.php` - **FALTANDO**
- [ ] View `fornecedores/edit.php` - **FALTANDO**
- [ ] View `fornecedores/show.php` - **FALTANDO**
- [ ] Rotas completas no `routes.php` - **FALTANDO**

#### ❌ Módulo de Clientes ❌ **NÃO IMPLEMENTADO**
- [ ] Model `Cliente.php` - **FALTANDO**
- [ ] Controller `ClienteController.php` - **FALTANDO**
- [ ] Views - **FALTANDO**
- [ ] Rotas - **FALTANDO**

#### ❌ Módulo de Categorias Financeiras ❌ **NÃO IMPLEMENTADO**
- [ ] Model `CategoriaFinanceira.php` - **FALTANDO**
- [ ] Controller `CategoriaController.php` - **FALTANDO**
- [ ] Views - **FALTANDO**
- [ ] Rotas - **FALTANDO**
- [ ] Estrutura hierárquica (pai/filho) - **FALTANDO**

#### ❌ Módulo de Centros de Custo ❌ **NÃO IMPLEMENTADO**
- [ ] Model `CentroCusto.php` - **FALTANDO**
- [ ] Controller `CentroCustoController.php` - **FALTANDO**
- [ ] Views - **FALTANDO**
- [ ] Rotas - **FALTANDO**
- [ ] Estrutura hierárquica (pai/filho) - **FALTANDO**

#### ❌ Módulo de Formas de Pagamento ❌ **NÃO IMPLEMENTADO**
- [ ] Model `FormaPagamento.php` - **FALTANDO**
- [ ] Controller `FormaPagamentoController.php` - **FALTANDO**
- [ ] Views - **FALTANDO**
- [ ] Rotas - **FALTANDO**

#### ❌ Módulo de Contas Bancárias ❌ **NÃO IMPLEMENTADO**
- [ ] Model `ContaBancaria.php` - **FALTANDO**
- [ ] Controller `ContaBancariaController.php` - **FALTANDO**
- [ ] Views - **FALTANDO**
- [ ] Rotas - **FALTANDO**

#### ❌ Sistema de Consolidação ❌ **NÃO IMPLEMENTADO**
- [ ] Interface de seleção múltipla de empresas - **FALTANDO**
- [ ] Perfis de consolidação - **FALTANDO**
- [ ] Lógica de consolidação nos relatórios - **FALTANDO**
- [ ] Views com opção de consolidação - **FALTANDO**

---

### 💰 FASE 3 - CONTAS E MOVIMENTAÇÕES ❌ **NÃO IMPLEMENTADO (0%)**

#### ❌ Módulo de Contas a Pagar ❌ **NÃO IMPLEMENTADO**
- [ ] Model `ContaPagar.php` - **FALTANDO**
- [ ] Controller `ContaPagarController.php` - **FALTANDO**
- [ ] Views - **FALTANDO**
- [ ] Rotas - **FALTANDO**
- [ ] CRUD completo - **FALTANDO**
- [ ] Campos de datas (emissão, competência, vencimento, pagamento) - **FALTANDO**
- [ ] Baixa parcial/total - **FALTANDO**
- [ ] Sistema de rateio entre empresas - **FALTANDO**
- [ ] Filtros avançados - **FALTANDO**
- [ ] Sugestão automática de forma de pagamento - **FALTANDO**

#### ❌ Módulo de Contas a Receber ❌ **NÃO IMPLEMENTADO**
- [ ] Model `ContaReceber.php` - **FALTANDO**
- [ ] Controller `ContaReceberController.php` - **FALTANDO**
- [ ] Views - **FALTANDO**
- [ ] Rotas - **FALTANDO**
- [ ] CRUD completo - **FALTANDO**
- [ ] Campos de datas (emissão, competência, vencimento, recebimento) - **FALTANDO**
- [ ] Baixa parcial/total - **FALTANDO**
- [ ] Sistema de rateio entre empresas - **FALTANDO**
- [ ] Filtros avançados - **FALTANDO**
- [ ] Sugestão automática de forma de pagamento - **FALTANDO**

#### ❌ Módulo de Movimentações de Caixa ❌ **NÃO IMPLEMENTADO**
- [ ] Model `MovimentacaoCaixa.php` - **FALTANDO**
- [ ] Controller `MovimentacaoCaixaController.php` - **FALTANDO**
- [ ] Views - **FALTANDO**
- [ ] Rotas - **FALTANDO**
- [ ] CRUD completo - **FALTANDO**

#### ❌ Sistema de Rateio ❌ **NÃO IMPLEMENTADO**
- [ ] Model `RateioPagamento.php` - **FALTANDO**
- [ ] Model `RateioRecebimento.php` - **FALTANDO**
- [ ] Lógica de rateio - **FALTANDO**
- [ ] Interface de rateio - **FALTANDO**
- [ ] Validações de rateio - **FALTANDO**

---

### 📊 FASE 4 - RELATÓRIOS ❌ **NÃO IMPLEMENTADO (0%)**

#### ❌ Módulo de Fluxo de Caixa ❌ **NÃO IMPLEMENTADO**
- [ ] Controller `FluxoCaixaController.php` - **FALTANDO**
- [ ] Views - **FALTANDO**
- [ ] Relatório por competência - **FALTANDO**
- [ ] Relatório por caixa - **FALTANDO**
- [ ] Projeção de fluxo - **FALTANDO**
- [ ] Gráficos - **FALTANDO**
- [ ] Consolidação - **FALTANDO**

#### ❌ Módulo DRE ❌ **NÃO IMPLEMENTADO**
- [ ] Controller `DREController.php` - **FALTANDO**
- [ ] Views - **FALTANDO**
- [ ] Geração por competência - **FALTANDO**
- [ ] Agrupamento por categorias - **FALTANDO**
- [ ] Comparativo entre períodos - **FALTANDO**
- [ ] Consolidação - **FALTANDO**

#### ❌ Módulo DFC ❌ **NÃO IMPLEMENTADO**
- [ ] Controller `DFCController.php` - **FALTANDO**
- [ ] Views - **FALTANDO**
- [ ] Método direto e indireto - **FALTANDO**
- [ ] Consolidação - **FALTANDO**

#### ❌ Dashboard Executivo ❌ **NÃO IMPLEMENTADO**
- [ ] Controller `DashboardController.php` - **FALTANDO**
- [ ] Views - **FALTANDO**
- [ ] KPIs principais - **FALTANDO**
- [ ] Gráficos e visualizações - **FALTANDO**
- [ ] Alertas e notificações - **FALTANDO**
- [ ] Comparativos - **FALTANDO**
- [ ] Consolidação - **FALTANDO**

#### ❌ Relatórios Gerais ❌ **NÃO IMPLEMENTADO**
- [ ] Controller `RelatorioController.php` - **FALTANDO**
- [ ] Exportação Excel/PDF - **FALTANDO**
- [ ] Filtros avançados - **FALTANDO**

---

### 🔄 FASE 5 - CONCILIAÇÃO BANCÁRIA ❌ **NÃO IMPLEMENTADO (0%)**

#### ❌ Módulo de Conciliação ❌ **NÃO IMPLEMENTADO**
- [ ] Model `ConciliacaoBancaria.php` - **FALTANDO**
- [ ] Controller `ConciliacaoController.php` - **FALTANDO**
- [ ] Views - **FALTANDO**
- [ ] Rotas - **FALTANDO**
- [ ] Importação de extratos (OFX, CSV, TXT) - **FALTANDO**
- [ ] Matching automático - **FALTANDO**
- [ ] Conciliação manual - **FALTANDO**
- [ ] Regras de matching - **FALTANDO**
- [ ] Relatórios - **FALTANDO**

---

### 🤖 FASE 6 - IA DE FORMAS DE PAGAMENTO ❌ **NÃO IMPLEMENTADO (0%)**

#### ❌ Sistema Inteligente ❌ **NÃO IMPLEMENTADO**
- [ ] Model `FormaPagamentoPadrao.php` - **FALTANDO**
- [ ] Service `FormaPagamentoIAService.php` - **FALTANDO**
- [ ] Algoritmo de aprendizado - **FALTANDO**
- [ ] Algoritmo de sugestão - **FALTANDO**
- [ ] Interface de padrões aprendidos - **FALTANDO**
- [ ] Aplicação em conciliação - **FALTANDO**
- [ ] Aplicação em contas pagar/receber - **FALTANDO**

---

### 🔌 FASE 7 - INTEGRAÇÕES ❌ **NÃO IMPLEMENTADO (0%)**

#### ❌ Integração com Bancos de Dados Externos ❌ **NÃO IMPLEMENTADO**
- [ ] Model `Integracao.php` - **FALTANDO**
- [ ] Controller `IntegracaoController.php` - **FALTANDO**
- [ ] Views - **FALTANDO**
- [ ] Interface de configuração - **FALTANDO**
- [ ] Teste de conexão - **FALTANDO**
- [ ] Listagem de tabelas - **FALTANDO**
- [ ] Mapeamento de colunas - **FALTANDO**
- [ ] Agendamento de sincronização - **FALTANDO**
- [ ] Scripts cron - **FALTANDO**
- [ ] Logs de sincronização - **FALTANDO**

#### ❌ Integração WooCommerce ❌ **NÃO IMPLEMENTADO**
- [ ] Configuração de credenciais - **FALTANDO**
- [ ] Webhook handler - **FALTANDO**
- [ ] Validação HMAC - **FALTANDO**
- [ ] Processamento de pedidos - **FALTANDO**
- [ ] Vinculação de produtos - **FALTANDO**
- [ ] Logs de webhooks - **FALTANDO**

---

### 📦 FASE 8 - PRODUTOS E PEDIDOS ❌ **NÃO IMPLEMENTADO (0%)**

#### ❌ Módulo de Produtos ❌ **NÃO IMPLEMENTADO**
- [ ] Model `Produto.php` - **FALTANDO**
- [ ] Controller `ProdutoController.php` - **FALTANDO**
- [ ] Views - **FALTANDO**
- [ ] Rotas - **FALTANDO**
- [ ] CRUD completo - **FALTANDO**
- [ ] Controle de custos - **FALTANDO**
- [ ] Histórico de preços - **FALTANDO**

#### ❌ Módulo de Pedidos Vinculados ❌ **NÃO IMPLEMENTADO**
- [ ] Model `PedidoVinculado.php` - **FALTANDO**
- [ ] Controller `PedidoController.php` - **FALTANDO**
- [ ] Views - **FALTANDO**
- [ ] Rotas - **FALTANDO**
- [ ] Visualização de pedidos - **FALTANDO**
- [ ] Cálculo de custos e margens - **FALTANDO**
- [ ] Relatórios de vendas - **FALTANDO**

---

### 🔐 FASE 9 - PERMISSÕES E SEGURANÇA ⚠️ **PARCIAL (~20%)**

#### ✅ Segurança Básica ✅ **IMPLEMENTADO**
- [x] Hash de senhas
- [x] Prepared statements
- [x] Validação de inputs
- [x] XSS protection
- [x] Middleware de autenticação

#### ❌ Sistema de Permissões ❌ **NÃO IMPLEMENTADO**
- [ ] Model `Permissao.php` - **FALTANDO**
- [ ] Controller `PermissaoController.php` - **FALTANDO**
- [ ] Middleware `PermissionMiddleware.php` - **FALTANDO**
- [ ] Service `PermissionService.php` - **FALTANDO**
- [ ] Interface de permissões - **FALTANDO**
- [ ] Perfis pré-definidos - **FALTANDO**
- [ ] Permissões por empresa - **FALTANDO**

#### ❌ CSRF Protection ❌ **NÃO IMPLEMENTADO**
- [ ] Middleware `CSRFMiddleware.php` - **FALTANDO**
- [ ] Tokens CSRF em formulários - **FALTANDO**

#### ❌ Logs de Auditoria ❌ **NÃO IMPLEMENTADO**
- [ ] Sistema de logs - **FALTANDO**
- [ ] Logs de ações críticas - **FALTANDO**

---

### 🛠️ FASE 10 - SERVIÇOS E HELPERS ❌ **NÃO IMPLEMENTADO (0%)**

#### ❌ Services ❌ **NÃO IMPLEMENTADO**
- [ ] `includes/services/AuthService.php` - **FALTANDO**
- [ ] `includes/services/PermissionService.php` - **FALTANDO**
- [ ] `includes/services/ConsolidacaoService.php` - **FALTANDO**
- [ ] `includes/services/RateioService.php` - **FALTANDO**
- [ ] `includes/services/ConciliacaoService.php` - **FALTANDO**
- [ ] `includes/services/FormaPagamentoIAService.php` - **FALTANDO**

#### ❌ Repositories ❌ **NÃO IMPLEMENTADO**
- [ ] `includes/repositories/EmpresaRepository.php` - **FALTANDO**
- [ ] `includes/repositories/ContaPagarRepository.php` - **FALTANDO**
- [ ] Outros repositories - **FALTANDO**

#### ❌ Helpers ❌ **NÃO IMPLEMENTADO**
- [ ] `includes/helpers/functions.php` - **FALTANDO**
- [ ] `includes/helpers/validations.php` - **FALTANDO**
- [ ] `includes/helpers/formata_dados.php` - **FALTANDO**

---

## 📈 RESUMO ESTATÍSTICO

### ✅ Implementado
- **Fase 1 (Estrutura Base)**: ✅ **100%** (8/8 itens)
- **Fase 2 (Módulos Fundamentais)**: ⚠️ **~30%** (3/10 módulos)
- **Fase 3 (Contas e Movimentações)**: ❌ **0%** (0/4 módulos)
- **Fase 4 (Relatórios)**: ❌ **0%** (0/5 módulos)
- **Fase 5 (Conciliação)**: ❌ **0%** (0/1 módulo)
- **Fase 6 (IA Formas Pagamento)**: ❌ **0%** (0/1 módulo)
- **Fase 7 (Integrações)**: ❌ **0%** (0/2 módulos)
- **Fase 8 (Produtos e Pedidos)**: ❌ **0%** (0/2 módulos)
- **Fase 9 (Permissões)**: ⚠️ **~20%** (1/5 itens)
- **Fase 10 (Services/Helpers)**: ❌ **0%** (0/3 tipos)

### 📊 Progresso Geral
- **Total de Fases**: 10
- **Fases Completas**: 1 (10%)
- **Fases Parciais**: 2 (20%)
- **Fases Não Iniciadas**: 7 (70%)

### 📦 Componentes Criados
- **Controllers**: 5 (de ~18 planejados) = **28%**
- **Models**: 3 (de ~25 planejados) = **12%**
- **Views**: ~15 (de ~100+ planejadas) = **~15%**
- **Migrations**: 27 (de 27 planejadas) = **100%** ✅
- **Services**: 0 (de ~6 planejados) = **0%**
- **Repositories**: 0 (de ~10 planejados) = **0%**
- **Helpers**: 0 (de ~3 planejados) = **0%**

---

## 🎯 PRÓXIMOS PASSOS RECOMENDADOS

### 🔥 Prioridade ALTA (Fundamentos)
1. **Completar Módulo de Fornecedores**
   - Criar views faltantes (create, edit, show)
   - Completar rotas no `routes.php`
   - Testar CRUD completo

2. **Implementar Módulo de Clientes**
   - Criar Model `Cliente.php`
   - Criar Controller `ClienteController.php`
   - Criar Views completas
   - Adicionar rotas

3. **Implementar Módulo de Categorias Financeiras**
   - Criar Model `CategoriaFinanceira.php` com estrutura hierárquica
   - Criar Controller `CategoriaController.php`
   - Criar Views com suporte a hierarquia
   - Adicionar rotas

4. **Implementar Módulo de Centros de Custo**
   - Criar Model `CentroCusto.php` com estrutura hierárquica
   - Criar Controller `CentroCustoController.php`
   - Criar Views com suporte a hierarquia
   - Adicionar rotas

5. **Implementar Módulo de Formas de Pagamento**
   - Criar Model `FormaPagamento.php`
   - Criar Controller `FormaPagamentoController.php`
   - Criar Views
   - Adicionar rotas

6. **Implementar Módulo de Contas Bancárias**
   - Criar Model `ContaBancaria.php`
   - Criar Controller `ContaBancariaController.php`
   - Criar Views
   - Adicionar rotas

### 🔶 Prioridade MÉDIA (Funcionalidades Core)
7. **Implementar Módulo de Contas a Pagar**
   - Criar Model `ContaPagar.php` com todos os campos de datas
   - Criar Controller `ContaPagarController.php`
   - Criar Views com formulários completos
   - Implementar baixa parcial/total
   - Implementar sistema de rateio
   - Adicionar rotas

8. **Implementar Módulo de Contas a Receber**
   - Criar Model `ContaReceber.php` com todos os campos de datas
   - Criar Controller `ContaReceberController.php`
   - Criar Views com formulários completos
   - Implementar baixa parcial/total
   - Implementar sistema de rateio
   - Adicionar rotas

9. **Implementar Sistema de Rateio**
   - Criar Models `RateioPagamento.php` e `RateioRecebimento.php`
   - Criar Service `RateioService.php`
   - Implementar validações
   - Criar interface de rateio

10. **Implementar Módulo de Movimentações de Caixa**
    - Criar Model `MovimentacaoCaixa.php`
    - Criar Controller `MovimentacaoCaixaController.php`
    - Criar Views
    - Adicionar rotas

### 🔷 Prioridade BAIXA (Relatórios e Avançado)
11. **Implementar Dashboard Executivo**
12. **Implementar Relatórios (Fluxo de Caixa, DRE, DFC)**
13. **Implementar Sistema de Consolidação**
14. **Implementar Conciliação Bancária**
15. **Implementar IA de Formas de Pagamento**
16. **Implementar Integrações**
17. **Implementar Sistema de Permissões**

---

## 📝 OBSERVAÇÕES IMPORTANTES

### ✅ Pontos Fortes
1. **Arquitetura sólida**: MVC bem estruturado e organizado
2. **Migrations completas**: Todas as 27 migrations foram criadas
3. **Base sólida**: Core do sistema está funcional e bem implementado
4. **Segurança básica**: Implementações de segurança fundamentais estão presentes
5. **Interface moderna**: TailwindCSS bem integrado com tema claro/escuro

### ⚠️ Pontos de Atenção
1. **Falta de Services**: Lógica de negócio está nos Controllers, deveria estar em Services
2. **Falta de Repositories**: Acesso a dados está nos Models, poderia usar Repositories para queries complexas
3. **Falta de Helpers**: Funções auxiliares não foram criadas
4. **Falta de Validações**: Validações estão nos Models, mas poderiam estar em um helper dedicado
5. **Falta de Testes**: Não há testes implementados

### 🎯 Recomendações de Arquitetura
1. **Criar Services**: Mover lógica de negócio complexa para Services
2. **Criar Repositories**: Para queries complexas e reutilizáveis
3. **Criar Helpers**: Para funções auxiliares comuns
4. **Implementar CSRF**: Proteção adicional em formulários
5. **Implementar Logs**: Sistema de auditoria para ações críticas

---

## 📊 CONCLUSÃO

O sistema está na **Fase 1 completa** e **início da Fase 2**. A base está sólida e bem estruturada, mas ainda falta implementar a maior parte das funcionalidades principais do sistema financeiro.

**Progresso Estimado**: **~15-20% do projeto completo**

**Próximo Marco**: Completar Fase 2 (Módulos Fundamentais) antes de partir para Fase 3 (Contas e Movimentações).

---

**Última Atualização**: 30/12/2025

