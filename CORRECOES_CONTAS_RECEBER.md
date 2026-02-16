# Correções Implementadas - Contas a Receber

## Problemas Identificados e Soluções

### 1. Campo de Data de Recebimento Não Editável

**Problema:**
No formulário de edição de contas a receber, não havia campo para editar a data de recebimento após a conta ter sido baixada.

**Solução Implementada:**
- ✅ Adicionado campo "Data de Recebimento" no formulário de edição (`app/views/contas_receber/edit.php`)
- ✅ O campo só aparece quando a conta tem status `recebido` ou `parcial`
- ✅ Atualizado o model `ContaReceber` (`app/models/ContaReceber.php`) para incluir `data_recebimento` no método `update()`
- ✅ O campo permite edição e também aceita valores vazios (NULL) para limpar a data se necessário

**Localização das mudanças:**
- `app/views/contas_receber/edit.php` (linhas 159-174)
- `app/models/ContaReceber.php` (método `update()`)

---

### 2. Auditoria Mostrando Data Incorreta (31/12/1969 21:00)

**Problema:**
A view estava tentando exibir os campos `created_at` e `updated_at` que não existiam na tabela `contas_receber`. Isso resultava em valores NULL sendo convertidos para o timestamp 0 (31/12/1969 21:00).

**Solução Implementada:**

#### A. Migration SQL Criada
- ✅ Criado arquivo de migration: `database/migrations/2026_02_16_add_created_updated_contas_receber.sql`
- Esta migration adiciona os campos `created_at` e `updated_at` na tabela `contas_receber`
- Copia os dados existentes de `data_cadastro` para `created_at`
- Define valores padrão para novos registros

#### B. View Corrigida com Fallback
- ✅ Atualizado `app/views/contas_receber/show.php` para usar `data_cadastro` como fallback
- Agora verifica se `created_at` existe, caso contrário usa `data_cadastro`
- Adicionada verificação para evitar exibir datas inválidas (`0000-00-00 00:00:00`)
- Adicionado campo "Cadastrado por" mostrando o usuário responsável

**Localização das mudanças:**
- `database/migrations/2026_02_16_add_created_updated_contas_receber.sql` (novo arquivo)
- `app/views/contas_receber/show.php` (seção de Auditoria)

---

## Como Aplicar as Correções

### Passo 1: Executar a Migration

**IMPORTANTE:** Execute a migration para adicionar os campos de auditoria:

```bash
# No terminal, na pasta raiz do projeto
php migrate.php
```

Ou execute diretamente o SQL no banco de dados:

```sql
-- Adicionar campo created_at
ALTER TABLE contas_receber
ADD COLUMN created_at TIMESTAMP NULL DEFAULT NULL
COMMENT 'Data e hora de criação do registro'
AFTER observacoes;

-- Adicionar campo updated_at
ALTER TABLE contas_receber
ADD COLUMN updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
COMMENT 'Data e hora da última atualização do registro'
AFTER created_at;

-- Copiar dados existentes
UPDATE contas_receber 
SET created_at = data_cadastro 
WHERE created_at IS NULL;

-- Tornar created_at NOT NULL com default
ALTER TABLE contas_receber
MODIFY COLUMN created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;
```

### Passo 2: Verificar o Funcionamento

1. **Teste de Edição da Data de Recebimento:**
   - Acesse uma conta a receber com status "Recebido" ou "Parcial"
   - Clique em "Editar"
   - Você verá o campo "Data de Recebimento" disponível para edição
   - Altere a data e salve
   - Verifique se a alteração foi salva corretamente

2. **Teste de Auditoria:**
   - Acesse qualquer conta a receber
   - Na seção "Auditoria" no canto inferior direito, verifique se a data está correta
   - Deve mostrar a data real de criação, não mais 31/12/1969

---

## Observações Técnicas

### Campo created_at vs data_cadastro

A tabela `contas_receber` tinha apenas o campo `data_cadastro` (DATETIME), mas muitas outras tabelas no sistema usam `created_at` e `updated_at` (TIMESTAMP).

- `created_at`: Timestamp automático de criação (padrão do sistema)
- `updated_at`: Timestamp automático de atualização
- `data_cadastro`: Campo legado que será mantido por compatibilidade

A migration copia os valores de `data_cadastro` para `created_at`, garantindo que não haja perda de dados históricos.

### Validações

- O campo `data_recebimento` **não é obrigatório** na edição normal
- Pode ser definido como NULL para limpar o valor
- Só aparece no formulário quando o status é `recebido` ou `parcial`

---

## Arquivos Modificados

1. ✅ `app/views/contas_receber/edit.php` 
   - Adicionado campo data_recebimento (editável)
   - Adicionada renderização server-side de categorias, centros de custo e clientes
   
2. ✅ `app/models/ContaReceber.php` 
   - Atualizado método update() para incluir data_recebimento
   
3. ✅ `app/views/contas_receber/show.php` 
   - Corrigida exibição da auditoria com fallback para data_cadastro
   
4. ✅ `app/controllers/ContaReceberController.php`
   - Método edit() agora carrega dados da empresa da conta (não do usuário)
   
5. ✅ `database/migrations/2026_02_16_add_created_updated_contas_receber.sql` 
   - Nova migration para adicionar campos created_at e updated_at

---

---

### 3. Categorias Não Listando no Formulário de Edição

**Problema:**
Ao editar uma conta a receber, o campo de categoria (e outros selects dependentes) não exibiam as opções, ficando apenas com "Carregando..." ou vazio.

**Causa do Problema:**
O formulário dependia 100% do JavaScript/AJAX para popular os campos de categoria, centro de custo e cliente. Se o JavaScript não carregasse ou falhasse, os campos ficavam vazios.

**Solução Implementada:**

#### A. View Corrigida com Renderização Server-Side
- ✅ Alterado `app/views/contas_receber/edit.php` para renderizar as opções diretamente no HTML
- ✅ Campo **Categoria** agora mostra todas as opções da empresa
- ✅ Campo **Centro de Custo** agora mostra todas as opções da empresa  
- ✅ Campo **Cliente** agora mostra todos os clientes disponíveis
- ✅ Os valores atuais são pré-selecionados corretamente
- ✅ Mantém a funcionalidade AJAX para atualizar ao trocar de empresa

#### B. Controller Ajustado
- ✅ Alterado `app/controllers/ContaReceberController.php` no método `edit()`
- ✅ Agora carrega categorias e centros de custo **da empresa da conta** (não do usuário logado)
- ✅ Garante que as opções corretas sejam enviadas para a view

**Localização das mudanças:**
- `app/views/contas_receber/edit.php` (campos Categoria, Centro de Custo e Cliente)
- `app/controllers/ContaReceberController.php` (método `edit()`, linhas 479-488)

**Benefícios:**
- ✅ Campos funcionam mesmo se JavaScript falhar
- ✅ Melhor experiência do usuário (carregamento mais rápido)
- ✅ Ainda permite atualização dinâmica ao trocar de empresa
- ✅ Valores corretos sempre visíveis

---

## Status

- [x] Problema 1: Data de recebimento não editável - **RESOLVIDO**
- [x] Problema 2: Auditoria com data incorreta - **RESOLVIDO** (requer execução da migration)
- [x] Problema 3: Categorias não listando - **RESOLVIDO**

---

Data da correção: 16/02/2026
