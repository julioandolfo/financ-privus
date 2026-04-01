# 📚 Documentação da API REST

> **🆕 Novidade:** Agora você pode criar **Vendas com Produtos** via API, incluindo cálculo automático de **Lucro** e **Margem de Lucro**! Veja a seção [Contas a Receber](#-contas-a-receber) e [Pedidos Vinculados](#-pedidos-vinculados).

## 📋 Índice

- [🔐 Autenticação](#-autenticação)
- [💰 Contas a Pagar](#-contas-a-pagar)
- [💵 Contas a Receber](#-contas-a-receber) ⭐ *COM PEDIDOS E PRODUTOS*
- [📦 Pedidos Vinculados](#-pedidos-vinculados) ⭐ *NOVO*
- [📦 Produtos](#-produtos)
- [👥 Clientes](#-clientes)
- [🏭 Fornecedores](#-fornecedores)
- [💸 Movimentações de Caixa](#-movimentações-de-caixa)
- [📂 Categorias Financeiras](#-categorias-financeiras)
- [📊 Centros de Custo](#-centros-de-custo)
- [🏦 Contas Bancárias](#-contas-bancárias)
- [⚠️ Tratamento de Erros](#️-tratamento-de-erros)
- [🔒 Segurança](#-segurança)
- [💡 Exemplos Práticos](#-exemplos-práticos)

---

## 🔐 Autenticação

Todos os endpoints da API REST requerem autenticação via **Bearer Token**.

### Obter um Token

1. Acesse o sistema via navegador
2. Vá em **API Tokens** no menu lateral
3. Clique em **Novo Token**
4. Configure as permissões desejadas
5. Copie o token gerado (ele não será exibido novamente)

### Como Usar o Token

Inclua o token no header `Authorization` de todas as requisições:

```http
Authorization: Bearer SEU_TOKEN_AQUI
```

**Exemplo com cURL:**
```bash
curl -H "Authorization: Bearer seu_token_aqui" \
     https://seudominio.com/api/v1/contas-pagar
```

**Exemplo com JavaScript (Fetch):**
```javascript
fetch('https://seudominio.com/api/v1/contas-pagar', {
    headers: {
        'Authorization': 'Bearer seu_token_aqui',
        'Content-Type': 'application/json'
    }
})
```

---

## 📌 Base URL

```
https://seudominio.com/api/v1
```

---

## 💰 Contas a Pagar

### Listar Contas a Pagar

```http
GET /api/v1/contas-pagar
```

**Resposta:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "fornecedor_id": 5,
            "empresa_id": 1,
            "descricao": "Pagamento fornecedor",
            "valor": 1500.00,
            "data_vencimento": "2025-01-15",
            "status": "pendente"
        }
    ]
}
```

### Buscar Conta Específica

```http
GET /api/v1/contas-pagar/{id}
```

**Resposta:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "fornecedor_id": 5,
        "empresa_id": 1,
        "descricao": "Pagamento fornecedor",
        "valor": 1500.00,
        "data_vencimento": "2025-01-15",
        "status": "pendente"
    }
}
```

### Criar Conta a Pagar

```http
POST /api/v1/contas-pagar
Content-Type: application/json

{
    "fornecedor_id": 5,
    "descricao": "Pagamento fornecedor",
    "valor": 1500.00,
    "data_vencimento": "2025-01-15",
    "data_emissao": "2025-01-01",
    "numero_documento": "NF-123",
    "observacoes": "Primeira parcela"
}
```

**Resposta:**
```json
{
    "success": true,
    "id": 10,
    "message": "Conta criada com sucesso"
}
```

### Atualizar Conta a Pagar

```http
PUT /api/v1/contas-pagar/{id}
Content-Type: application/json

{
    "descricao": "Pagamento fornecedor - ATUALIZADO",
    "valor": 1800.00
}
```

**Resposta:**
```json
{
    "success": true,
    "message": "Conta atualizada com sucesso"
}
```

### Excluir Conta a Pagar

```http
DELETE /api/v1/contas-pagar/{id}
```

**Resposta:**
```json
{
    "success": true,
    "message": "Conta excluída com sucesso"
}
```

---

## 💵 Contas a Receber

### Listar Contas a Receber

```http
GET /api/v1/contas-receber
```

**Parâmetros de Query (opcionais):**
- `empresa_id` - Filtrar por empresa
- `cliente_id` - Filtrar por cliente
- `status` - Filtrar por status (pendente, recebido, vencido, cancelado, parcial)
- `data_inicio` - Data inicial (YYYY-MM-DD)
- `data_fim` - Data final (YYYY-MM-DD)

**Resposta:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "empresa_id": 1,
            "cliente_id": 3,
            "cliente_nome": "João da Silva",
            "pedido_id": 5,
            "categoria_id": 10,
            "centro_custo_id": 2,
            "numero_documento": "NF-456",
            "descricao": "Venda de produtos",
            "valor_total": 2500.00,
            "valor_recebido": 0.00,
            "data_emissao": "2025-01-01",
            "data_vencimento": "2025-01-20",
            "data_recebimento": null,
            "status": "pendente"
        }
    ]
}
```

### Buscar Conta Específica

```http
GET /api/v1/contas-receber/{id}
```

**Resposta:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "empresa_id": 1,
        "cliente_id": 3,
        "cliente_nome": "João da Silva",
        "pedido_id": 5,
        "categoria_id": 10,
        "centro_custo_id": 2,
        "numero_documento": "NF-456",
        "descricao": "Venda de produtos",
        "valor_total": 2500.00,
        "valor_recebido": 0.00,
        "data_emissao": "2025-01-01",
        "data_vencimento": "2025-01-20",
        "status": "pendente",
        "pedido": {
            "id": 5,
            "numero_pedido": "PED-001",
            "valor_total": 2500.00,
            "valor_custo_total": 1500.00,
            "frete": 50.00,
            "desconto": 25.00,
            "bonificado": 0,
            "lucro": 925.00,
            "margem_lucro": 61.67,
            "itens": [
                {
                    "id": 10,
                    "produto_id": 1,
                    "produto_codigo": "PROD-001",
                    "nome_produto": "Produto A",
                    "quantidade": 2,
                    "valor_unitario": 100.00,
                    "valor_total": 200.00,
                    "custo_unitario": 60.00,
                    "custo_total": 120.00,
                    "lucro_item": 80.00,
                    "margem_item": 66.67
                }
            ]
        }
    }
}
```

### Criar Conta a Receber (Simples)

Para contas a receber sem produtos vinculados:

```http
POST /api/v1/contas-receber
Content-Type: application/json

{
    "cliente_id": 3,
    "categoria_id": 10,
    "centro_custo_id": 2,
    "descricao": "Prestação de serviço",
    "valor_total": 1500.00,
    "data_vencimento": "2025-01-20",
    "data_emissao": "2025-01-01",
    "data_competencia": "2025-01-01",
    "numero_documento": "NF-456",
    "observacoes": "Serviço de consultoria"
}
```

**Resposta:**
```json
{
    "success": true,
    "id": 15,
    "message": "Conta a receber criada com sucesso"
}
```

### Criar Conta a Receber com Pedido (Venda com Produtos)

Para vendas com produtos, incluindo cálculo automático de lucro/margem:

```http
POST /api/v1/contas-receber
Content-Type: application/json

{
    "cliente_id": 3,
    "categoria_id": 10,
    "centro_custo_id": 2,
    "descricao": "Venda de produtos",
    "data_vencimento": "2025-01-20",
    "data_emissao": "2025-01-01",
    "data_competencia": "2025-01-01",
    "numero_documento": "NF-456",
    "criar_pedido": true,
    "pedido": {
        "numero_pedido": "PED-001",
        "data_pedido": "2025-01-01",
        "status": "pendente",
        "frete": 15.00,
        "desconto": 5.00,
        "bonificado": 0,
        "produtos": [
            {
                "produto_id": 1,
                "quantidade": 2,
                "valor_unitario": 100.00,
                "custo_unitario": 60.00
            },
            {
                "produto_id": 2,
                "quantidade": 1,
                "valor_unitario": 50.00,
                "custo_unitario": 30.00
            }
        ]
    }
}
```

**Campos do Pedido:**
- ⚪ `frete` (decimal, opcional): Valor do frete - será deduzido do lucro
- ⚪ `desconto` (decimal, opcional): Valor do desconto concedido
- ⚪ `bonificado` (integer, opcional): 1 = pedido bonificado, 0 = não bonificado

**Nota:** O campo `valor_total` é calculado automaticamente pela soma dos produtos. Os campos `custo_unitario` são opcionais - se não informados, o sistema busca do cadastro do produto.

**Resposta:**
```json
{
    "success": true,
    "id": 15,
    "pedido_id": 20,
    "valor_total": 250.00,
    "valor_custo_total": 150.00,
    "lucro": 100.00,
    "margem_lucro": 66.67,
    "message": "Conta a receber criada com sucesso com pedido vinculado"
}
```

### 🆕 Criar Conta a Receber com Auto-Cadastro de Produtos via SKU

**Novo recurso:** Agora você pode enviar produtos usando **SKU** e o sistema irá:
1. ✅ **Buscar** o produto existente pelo SKU
2. ✅ **Criar automaticamente** o produto se não existir
3. ✅ **Vincular** ao pedido

### 🚀 NOVO: Criar Tudo em UMA Requisição (Cliente + Produtos + Pedido + Conta)

**Recurso completo:** Crie uma venda completa com auto-cadastro de cliente e produtos em uma única requisição!

```http
POST /api/v1/contas-receber
Content-Type: application/json

{
    "categoria_id": 10,
    "descricao": "Venda de produtos externos",
    "data_vencimento": "2025-01-20",
    "data_emissao": "2025-01-01",
    "data_competencia": "2025-01-01",
    "cliente": {
        "cpf_cnpj": "12345678901",
        "codigo_cliente": "CLI-001",
        "nome_razao_social": "Cliente Exemplo",
        "tipo": "fisica",
        "email": "cliente@exemplo.com",
        "telefone": "11999999999"
    },
    "criar_pedido": true,
    "pedido": {
        "numero_pedido": "PED-2025-001",
        "frete": 25.00,
        "desconto": 10.00,
        "bonificado": 0,
        "produtos": [
            {
                "sku": "PROD-EXTERNO-001",
                "nome": "Produto Novo da Integração",
                "quantidade": 5,
                "valor_unitario": 150.00,
                "custo_unitario": 90.00,
                "unidade_medida": "UN"
            },
            {
                "sku": "PROD-EXTERNO-002",
                "nome": "Outro Produto",
                "quantidade": 2,
                "valor_unitario": 75.00,
                "custo_unitario": 45.00
            }
        ]
    }
}
```

**Campos do Pedido:**
- ⚪ `frete` (decimal, opcional): Valor do frete - será deduzido do lucro
- ⚪ `desconto` (decimal, opcional): Valor do desconto concedido
- ⚪ `bonificado` (integer, opcional): 1 = pedido bonificado, 0 = não (padrão: 0)

> **Auto-cadastro de Cliente:** O sistema buscará o cliente por `cpf_cnpj` ou `codigo_cliente`. Se encontrar, vincula à conta. Se não encontrar, cria automaticamente com os dados fornecidos.

**Como funciona:**
- Se o produto com `sku = "PROD-EXTERNO-001"` **já existe**: usa o produto cadastrado (ignora nome/valores enviados)
- Se **não existe**: cria automaticamente com os dados fornecidos
- O `codigo` do produto é gerado automaticamente se não fornecido

**Campos aceitos para auto-cadastro:**

| Campo | Tipo | Obrigatório | Descrição |
|-------|------|-------------|-----------|
| `sku` | string | Sim* | Identificador único do produto |
| `produto_id` | int | Sim* | ID do produto (alternativa ao SKU) |
| `nome` | string | Sim** | Nome do produto (para auto-cadastro) |
| `quantidade` | decimal | Sim | Quantidade vendida |
| `valor_unitario` | decimal | Sim | Preço de venda |
| `custo_unitario` | decimal | Não | Custo (padrão: 0) |
| `unidade_medida` | string | Não | UN, KG, L, etc (padrão: UN) |
| `codigo` | string | Não | Código interno (auto-gerado se omitido) |
| `descricao` | text | Não | Descrição do produto |

*Use `sku` OU `produto_id`, não ambos  
**Obrigatório apenas se o produto não existir e você está usando SKU

**Resposta:**
```json
{
    "success": true,
    "id": 25,
    "pedido_id": 30,
    "valor_total": 900.00,
    "valor_custo_total": 540.00,
    "lucro": 360.00,
    "margem_lucro": 66.67,
    "produtos_criados": 2,
    "produtos_vinculados": 2,
    "message": "Conta a receber criada com sucesso. 2 produtos foram criados automaticamente."
}
```

### Estrutura do Cliente (Auto-cadastro)

**Campos do objeto `cliente`:**

| Campo | Tipo | Obrigatório | Descrição |
|-------|------|-------------|-----------|
| `cpf_cnpj` | string | Condicional* | CPF ou CNPJ do cliente |
| `codigo_cliente` | string | Condicional* | Código interno do cliente (ex: código do ERP) |
| `nome_razao_social` | string | Sim** | Nome ou Razão Social |
| `tipo` | string | Não | `fisica` ou `juridica` (detectado automaticamente) |
| `email` | string | Não | E-mail do cliente |
| `telefone` | string | Não | Telefone do cliente |
| `endereco` | object | Não | Objeto com logradouro, numero, bairro, cidade, estado, cep |

*Use `cpf_cnpj` OU `codigo_cliente` (pelo menos um é necessário para identificar/criar o cliente)  
**Obrigatório apenas quando o cliente não existir e será criado automaticamente

> **Importante:** Se o cliente já existir (identificado por `cpf_cnpj` ou `codigo_cliente`), ele será **vinculado** à conta. Se não existir, será **criado automaticamente** com os dados fornecidos.

### Estrutura Completa do Pedido

**Campos do objeto `pedido`:**

| Campo | Tipo | Obrigatório | Descrição |
|-------|------|-------------|-----------|
| `numero_pedido` | string | Não | Número do pedido (se não informado, gera automático) |
| `data_pedido` | date | Sim | Data do pedido (YYYY-MM-DD) |
| `status` | string | Não | Status do pedido (pendente, processando, concluido, cancelado) |
| `frete` | decimal | Não | Valor do frete - será deduzido do lucro |
| `desconto` | decimal | Não | Valor do desconto concedido |
| `bonificado` | integer | Não | 1 = pedido bonificado (grátis), 0 = normal (padrão: 0) |
| `produtos` | array | Sim | Lista de produtos do pedido |

**Campos do objeto `produtos[]`:**

| Campo | Tipo | Obrigatório | Descrição |
|-------|------|-------------|-----------|
| `produto_id` | int | Condicional* | ID do produto cadastrado |
| `sku` | string | Condicional* | SKU do produto (alternativa ao produto_id) |
| `nome` | string | Condicional** | Nome do produto (para auto-cadastro via SKU) |
| `quantidade` | decimal | Sim | Quantidade vendida (aceita decimais para produtos fracionados) |
| `valor_unitario` | decimal | Sim | Valor de venda unitário |
| `custo_unitario` | decimal | Não | Custo unitário do produto (padrão: busca do cadastro ou 0) |
| `unidade_medida` | string | Não | UN, KG, L, etc (padrão: UN para auto-cadastro) |
| `codigo` | string | Não | Código interno (auto-gerado para novos produtos) |
| `descricao` | text | Não | Descrição do produto (para auto-cadastro) |

*Use `produto_id` (se o produto já está cadastrado) OU `sku` (para buscar/criar automaticamente)  
**Obrigatório apenas quando usar `sku` e o produto não existir no sistema

### Atualizar Conta a Receber

```http
PUT /api/v1/contas-receber/{id}
Content-Type: application/json

{
    "descricao": "Venda de produtos - ATUALIZADO",
    "valor_total": 2800.00,
    "data_vencimento": "2025-01-25"
}
```

**Resposta:**
```json
{
    "success": true,
    "message": "Conta a receber atualizada com sucesso"
}
```

### Atualizar Pedido Vinculado

Para atualizar produtos de uma conta que já possui pedido:

```http
PUT /api/v1/contas-receber/{id}
Content-Type: application/json

{
    "pedido": {
        "produtos": [
            {
                "produto_id": 1,
                "quantidade": 3,
                "valor_unitario": 110.00,
                "custo_unitario": 65.00
            }
        ]
    }
}
```

**Nota:** Atualizar o pedido recalcula automaticamente o `valor_total` da conta a receber.

### Excluir Conta a Receber

```http
DELETE /api/v1/contas-receber/{id}
```

**Resposta:**
```json
{
    "success": true,
    "message": "Conta a receber excluída com sucesso"
}
```

**Nota:** Excluir uma conta a receber também exclui o pedido e itens vinculados (CASCADE).

---

## 🔄 Alterar Status de Conta a Receber

```http
PATCH /api/v1/contas-receber/{id}/status
Content-Type: application/json

{
    "status": "cancelado",
    "atualizar_parcelas": 1
}
```

**Campos:**
- 🔴 `status` (string, obrigatório): Novo status. Valores aceitos: `pendente`, `recebido`, `cancelado`
- ⚪ `atualizar_parcelas` (boolean, opcional): Se `1` ou `true`, propaga o status para todas as parcelas da conta. Padrão: `0` (não propaga)

**Comportamento por status:**

| Status | Conta | Parcelas (se `atualizar_parcelas=1`) |
|--------|-------|--------------------------------------|
| `pendente` | Zera valor_recebido e data_recebimento | Zera valor_recebido e data_recebimento de cada parcela |
| `recebido` | Define valor_recebido = valor_total, data_recebimento = hoje | Define valor_recebido = valor_parcela, data_recebimento = hoje |
| `cancelado` | Zera valor_recebido e data_recebimento | Cancela cada parcela e zera valores |

**Resposta de Sucesso:**
```json
{
    "success": true,
    "message": "Status atualizado com sucesso",
    "conta": {
        "id": 10,
        "descricao": "Venda #123",
        "valor_total": 1000.00,
        "valor_recebido": 0.00,
        "status_anterior": "pendente",
        "status_atual": "cancelado",
        "data_recebimento": null
    },
    "parcelas_atualizadas": 3
}
```

**Exemplos de uso:**

Cancelar conta e suas parcelas:
```bash
curl -X PATCH "https://seudominio.com/api/v1/contas-receber/10/status" \
  -H "Authorization: Bearer SEU_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"status": "cancelado", "atualizar_parcelas": 1}'
```

Marcar conta como recebida (sem alterar parcelas individualmente):
```bash
curl -X PATCH "https://seudominio.com/api/v1/contas-receber/10/status" \
  -H "Authorization: Bearer SEU_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"status": "recebido", "atualizar_parcelas": 0}'
```

---

## 📋 Parcelas de Contas a Receber

### Listar Parcelas de uma Conta

```http
GET /api/v1/contas-receber/{id}/parcelas
```

**Resposta:**
```json
{
    "success": true,
    "data": {
        "parcelas": [
            {
                "id": 1,
                "numero_parcela": 1,
                "valor_parcela": 500.00,
                "valor_recebido": 0.00,
                "data_vencimento": "2025-02-15",
                "data_recebimento": null,
                "status": "pendente"
            },
            {
                "id": 2,
                "numero_parcela": 2,
                "valor_parcela": 500.00,
                "valor_recebido": 0.00,
                "data_vencimento": "2025-03-15",
                "data_recebimento": null,
                "status": "pendente"
            }
        ],
        "resumo": {
            "total_parcelas": 2,
            "parcelas_recebidas": 0,
            "total_valor": 1000.00,
            "total_recebido": 0.00
        }
    }
}
```

### Visualizar Parcela Específica

```http
GET /api/v1/parcelas-receber/{id}
```

**Resposta:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "conta_receber_id": 10,
        "numero_parcela": 1,
        "valor_parcela": 500.00,
        "valor_recebido": 0.00,
        "data_vencimento": "2025-02-15",
        "data_recebimento": null,
        "status": "pendente",
        "forma_recebimento_id": null,
        "conta_bancaria_id": null
    }
}
```

### Dar Baixa/Receber Parcela

```http
POST /api/v1/parcelas-receber/{id}/baixar
Content-Type: application/json

{
    "valor_recebido": 500.00,
    "data_recebimento": "2025-02-15",
    "forma_recebimento_id": 1,
    "conta_bancaria_id": 2,
    "observacoes": "Recebido via PIX"
}
```

**Campos:**
- ⚪ `valor_recebido` (decimal, opcional): Valor recebido. Se não informado, usa o saldo restante da parcela
- ⚪ `data_recebimento` (date, opcional): Data do recebimento (padrão: data atual)
- ⚪ `forma_recebimento_id` (integer, opcional): ID da forma de recebimento
- ⚪ `conta_bancaria_id` (integer, opcional): ID da conta bancária
- ⚪ `observacoes` (string, opcional): Observações do recebimento

**Resposta de Sucesso:**
```json
{
    "success": true,
    "message": "Recebimento registrado com sucesso",
    "parcela": {
        "id": 1,
        "numero_parcela": 1,
        "valor_parcela": 500.00,
        "valor_recebido": 500.00,
        "saldo_restante": 0.00,
        "status": "recebido",
        "data_recebimento": "2025-02-15"
    },
    "conta": {
        "id": 10,
        "total_parcelas": 2,
        "parcelas_recebidas": 1,
        "total_recebido": 500.00,
        "total_pendente": 500.00
    }
}
```

**Notas:**
- O valor recebido não pode ser maior que o saldo restante da parcela
- Quando todas as parcelas são pagas, a conta principal é automaticamente marcada como "recebido"
- Se houver recebimento parcial, a conta principal é marcada como "parcial"

---

## 📦 Produtos

### Listar Produtos

```http
GET /api/v1/produtos
```

**Parâmetros de Query (opcionais):**
- `empresa_id` - Filtrar por empresa
- `categoria_id` - Filtrar por categoria
- `busca` - Buscar por código ou nome

**Resposta:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "empresa_id": 1,
            "nome": "Produto Exemplo",
            "codigo": "PROD-001",
            "categoria_id": 5,
            "preco_venda": 150.00,
            "custo_unitario": 80.00,
            "margem_lucro": 87.5,
            "estoque": 50,
            "estoque_minimo": 10,
            "unidade_medida": "UN",
            "ativo": true
        }
    ]
}
```

### Buscar Produto Específico

```http
GET /api/v1/produtos/{id}
```

**Resposta:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "empresa_id": 1,
        "codigo": "PROD-001",
        "codigo_barras": "7891234567890",
        "nome": "Produto Exemplo",
        "descricao": "Descrição detalhada do produto",
        "categoria_id": 5,
        "categoria_nome": "Categoria A",
        "custo_unitario": 80.00,
        "preco_venda": 150.00,
        "margem_lucro": 87.5,
        "unidade_medida": "UN",
        "estoque": 50,
        "estoque_minimo": 10,
        "ativo": true
    }
}
```

### Criar Produto

```http
POST /api/v1/produtos
Content-Type: application/json

{
    "codigo": "PROD-002",
    "sku": "SKU-PROD-002",
    "codigo_barras": "7891234567891",
    "nome": "Novo Produto",
    "descricao": "Descrição do produto",
    "categoria_id": 5,
    "custo_unitario": 100.00,
    "preco_venda": 200.00,
    "unidade_medida": "UN",
    "estoque": 100,
    "estoque_minimo": 10
}
```

**Campos:**

| Campo | Tipo | Obrigatório | Descrição |
|-------|------|-------------|-----------|
| `codigo` | string | Sim | Código interno único do produto |
| `sku` | string | Não | SKU - Identificador único para integração/API |
| `codigo_barras` | string | Não | Código de barras EAN-13 |
| `nome` | string | Sim | Nome do produto |
| `descricao` | text | Não | Descrição detalhada |
| `categoria_id` | int | Não | ID da categoria do produto |
| `custo_unitario` | decimal | Sim | Custo de compra/produção |
| `preco_venda` | decimal | Sim | Preço de venda |
| `unidade_medida` | string | Sim | UN, KG, L, M, CX, etc |
| `estoque` | decimal | Não | Quantidade em estoque (padrão: 0) |
| `estoque_minimo` | decimal | Não | Estoque mínimo para alerta (padrão: 0) |

**⭐ Sobre o SKU:**
- SKU deve ser único por empresa
- Usado para identificar produtos em integrações via API
- Se fornecido em pedidos, o sistema busca/cria automaticamente o produto

**Resposta:**
```json
{
    "success": true,
    "id": 15,
    "margem_lucro": 100.0,
    "message": "Produto criado com sucesso"
}
```

### Atualizar Produto

```http
PUT /api/v1/produtos/{id}
Content-Type: application/json

{
    "preco_venda": 220.00,
    "estoque": 95,
    "custo_unitario": 110.00
}
```

**Resposta:**
```json
{
    "success": true,
    "margem_lucro": 100.0,
    "message": "Produto atualizado com sucesso"
}
```

### Excluir Produto

```http
DELETE /api/v1/produtos/{id}
```

**Resposta:**
```json
{
    "success": true,
    "message": "Produto excluído com sucesso"
}
```

### Buscar Produtos para Autocomplete

Endpoint otimizado para busca rápida (retorna apenas campos essenciais):

```http
GET /api/v1/produtos/buscar?empresa_id=1&q=termo
```

**Resposta:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "codigo": "PROD-001",
            "nome": "Produto A",
            "preco_venda": 100.00,
            "custo_unitario": 60.00,
            "unidade_medida": "UN"
        }
    ]
}
```

---

## 📦 Pedidos Vinculados

Os pedidos são criados automaticamente ao criar contas a receber com produtos, mas também podem ser gerenciados independentemente.

### Listar Pedidos

```http
GET /api/v1/pedidos
```

**Parâmetros de Query (opcionais):**
- `empresa_id` - Filtrar por empresa
- `cliente_id` - Filtrar por cliente
- `status` - Filtrar por status
- `origem` - Filtrar por origem (manual, woocommerce, externo)
- `data_inicio` - Data inicial (YYYY-MM-DD)
- `data_fim` - Data final (YYYY-MM-DD)

**Resposta:**
```json
{
    "success": true,
    "data": [
        {
            "id": 5,
            "empresa_id": 1,
            "numero_pedido": "PED-001",
            "cliente_id": 3,
            "cliente_nome": "João da Silva",
            "origem": "manual",
            "status": "concluido",
            "data_pedido": "2025-01-20",
            "valor_total": 250.00,
            "valor_custo_total": 150.00,
            "frete": 10.00,
            "desconto": 5.00,
            "bonificado": 0,
            "lucro": 90.00,
            "margem_lucro": 60.00,
            "total_itens": 2
        }
    ]
}
```

### Buscar Pedido Específico

```http
GET /api/v1/pedidos/{id}
```

**Resposta:**
```json
{
    "success": true,
    "data": {
        "id": 5,
        "empresa_id": 1,
        "numero_pedido": "PED-001",
        "cliente_id": 3,
        "cliente_nome": "João da Silva",
        "cliente_email": "joao@email.com",
        "origem": "manual",
        "status": "concluido",
        "data_pedido": "2025-01-20 10:30:00",
        "valor_total": 250.00,
        "valor_custo_total": 150.00,
        "frete": 10.00,
        "desconto": 5.00,
        "bonificado": 0,
        "lucro": 90.00,
        "margem_lucro": 60.00,
        "itens": [
            {
                "id": 10,
                "produto_id": 1,
                "produto_codigo": "PROD-001",
                "nome_produto": "Produto A",
                "quantidade": 2,
                "valor_unitario": 100.00,
                "valor_total": 200.00,
                "custo_unitario": 60.00,
                "custo_total": 120.00,
                "lucro_item": 80.00,
                "margem_item": 66.67
            },
            {
                "id": 11,
                "produto_id": 2,
                "produto_codigo": "PROD-002",
                "nome_produto": "Produto B",
                "quantidade": 1,
                "valor_unitario": 50.00,
                "valor_total": 50.00,
                "custo_unitario": 30.00,
                "custo_total": 30.00,
                "lucro_item": 20.00,
                "margem_item": 66.67
            }
        ],
        "conta_receber": {
            "id": 15,
            "numero_documento": "NF-456",
            "status": "pendente"
        }
    }
}
```

### Criar Pedido (Sem Conta a Receber)

Para criar um pedido independente (não vinculado a conta a receber):

```http
POST /api/v1/pedidos
Content-Type: application/json

{
    "numero_pedido": "PED-002",
    "cliente_id": 5,
    "data_pedido": "2025-01-20",
    "status": "pendente",
    "origem": "manual",
    "frete": 20.00,
    "desconto": 5.00,
    "bonificado": 0,
    "produtos": [
        {
            "produto_id": 1,
            "quantidade": 3,
            "valor_unitario": 150.00,
            "custo_unitario": 90.00
        }
    ]
}
```

**Campos do Pedido:**
- ⚪ `frete` (decimal, opcional): Valor do frete - será deduzido do lucro
- ⚪ `desconto` (decimal, opcional): Valor do desconto concedido
- ⚪ `bonificado` (integer, opcional): 1 = pedido bonificado (grátis), 0 = normal (padrão: 0)

**Resposta:**
```json
{
    "success": true,
    "id": 25,
    "valor_total": 450.00,
    "valor_custo_total": 270.00,
    "frete": 20.00,
    "desconto": 5.00,
    "bonificado": 0,
    "lucro": 160.00,
    "margem_lucro": 35.56,
    "message": "Pedido criado com sucesso"
}
```

### Atualizar Status do Pedido

```http
PATCH /api/v1/pedidos/{id}/status
Content-Type: application/json

{
    "status": "concluido"
}
```

**Status possíveis:**
- `pendente` - Pedido criado, aguardando processamento
- `processando` - Em processamento/separação
- `concluido` - Pedido finalizado/entregue
- `cancelado` - Pedido cancelado
- `reembolsado` - Pedido reembolsado

**Resposta:**
```json
{
    "success": true,
    "message": "Status do pedido atualizado com sucesso"
}
```

### Adicionar Item ao Pedido

```http
POST /api/v1/pedidos/{id}/itens
Content-Type: application/json

{
    "produto_id": 3,
    "quantidade": 2,
    "valor_unitario": 75.00,
    "custo_unitario": 45.00
}
```

**Resposta:**
```json
{
    "success": true,
    "item_id": 50,
    "novo_total": 400.00,
    "novo_custo_total": 240.00,
    "message": "Item adicionado ao pedido"
}
```

### Remover Item do Pedido

```http
DELETE /api/v1/pedidos/{id}/itens/{item_id}
```

**Resposta:**
```json
{
    "success": true,
    "novo_total": 250.00,
    "novo_custo_total": 150.00,
    "message": "Item removido do pedido"
}
```

### Análise de Lucro/Margem

Endpoint específico para análise financeira de pedidos:

```http
GET /api/v1/pedidos/{id}/analise
```

**Resposta:**
```json
{
    "success": true,
    "data": {
        "pedido_id": 5,
        "numero_pedido": "PED-001",
        "valor_total": 250.00,
        "valor_custo_total": 150.00,
        "frete": 10.00,
        "desconto": 5.00,
        "bonificado": 0,
        "lucro_bruto": 90.00,
        "margem_lucro_percentual": 60.00,
        "ticket_medio_item": 125.00,
        "itens": [
            {
                "produto": "Produto A",
                "quantidade": 2,
                "valor_venda": 200.00,
                "custo": 120.00,
                "lucro": 80.00,
                "margem": 66.67,
                "contribuicao_percentual": 80.0
            },
            {
                "produto": "Produto B",
                "quantidade": 1,
                "valor_venda": 50.00,
                "custo": 30.00,
                "lucro": 20.00,
                "margem": 66.67,
                "contribuicao_percentual": 20.0
            }
        ]
    }
}
```

---

## 👥 Clientes

### Listar Clientes

```http
GET /api/v1/clientes
```

**Resposta:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "codigo_cliente": "CLI-001",
            "nome_razao_social": "Cliente Exemplo Ltda",
            "tipo": "juridica",
            "cpf_cnpj": "12.345.678/0001-90",
            "email": "contato@cliente.com",
            "telefone": "(11) 98765-4321",
            "ativo": true
        }
    ]
}
```

### Buscar Cliente Específico

```http
GET /api/v1/clientes/{id}
```

**Resposta:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "codigo_cliente": "CLI-001",
        "nome_razao_social": "Cliente Exemplo Ltda",
        "tipo": "juridica",
        "cpf_cnpj": "12.345.678/0001-90",
        "email": "contato@cliente.com",
        "telefone": "(11) 98765-4321",
        "ativo": true,
        "endereco": {
            "logradouro": "Rua Exemplo",
            "numero": "123",
            "bairro": "Centro",
            "cidade": "São Paulo",
            "estado": "SP",
            "cep": "01234-567"
        }
    }
}
```

### Criar Cliente

```http
POST /api/v1/clientes
Content-Type: application/json

{
    "codigo_cliente": "CLI-002",
    "nome_razao_social": "Novo Cliente Ltda",
    "tipo": "juridica",
    "cpf_cnpj": "98.765.432/0001-10",
    "email": "novo@cliente.com",
    "telefone": "(11) 91234-5678",
    "endereco": {
        "logradouro": "Rua Exemplo",
        "numero": "123",
        "bairro": "Centro",
        "cidade": "São Paulo",
        "estado": "SP",
        "cep": "01234-567"
    }
}
```

> **Nota:** O campo `codigo_cliente` é opcional e serve como código interno do cliente no seu sistema (ex: código do ERP).

**Resposta:**
```json
{
    "success": true,
    "id": 20,
    "message": "Cliente criado com sucesso"
}
```

### Atualizar Cliente

```http
PUT /api/v1/clientes/{id}
Content-Type: application/json

{
    "email": "novoemail@cliente.com",
    "telefone": "(11) 99999-8888"
}
```

### Excluir Cliente

```http
DELETE /api/v1/clientes/{id}
```

---

## 🏭 Fornecedores

### Listar Fornecedores

```http
GET /api/v1/fornecedores
```

### Buscar Fornecedor Específico

```http
GET /api/v1/fornecedores/{id}
```

### Criar Fornecedor

```http
POST /api/v1/fornecedores
Content-Type: application/json

{
    "nome_razao_social": "Fornecedor Exemplo S.A.",
    "tipo": "juridica",
    "cpf_cnpj": "11.222.333/0001-44",
    "email": "contato@fornecedor.com",
    "telefone": "(11) 3333-4444"
}
```

### Atualizar Fornecedor

```http
PUT /api/v1/fornecedores/{id}
```

### Excluir Fornecedor

```http
DELETE /api/v1/fornecedores/{id}
```

---

## 💸 Movimentações de Caixa

### Listar Movimentações

```http
GET /api/v1/movimentacoes
```

**Resposta:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "empresa_id": 1,
            "descricao": "Movimentação exemplo",
            "tipo": "entrada",
            "valor": 500.00,
            "data": "2025-01-01",
            "ativo": true
        }
    ]
}
```

### Buscar Movimentação Específica

```http
GET /api/v1/movimentacoes/{id}
```

### Criar Movimentação

```http
POST /api/v1/movimentacoes
Content-Type: application/json

{
    "descricao": "Entrada de caixa",
    "tipo": "entrada",
    "valor": 1500.00,
    "data": "2025-01-15",
    "conta_bancaria_id": 5,
    "categoria_id": 10,
    "observacoes": "Pagamento recebido"
}
```

---

## 📂 Categorias Financeiras

### Listar Categorias

```http
GET /api/v1/categorias
```

**Resposta:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "nome": "Receitas",
            "tipo": "receita",
            "codigo": "REC-001",
            "categoria_pai_id": null,
            "ativo": true
        }
    ]
}
```

### Buscar Categoria Específica

```http
GET /api/v1/categorias/{id}
```

### Criar Categoria

```http
POST /api/v1/categorias
Content-Type: application/json

{
    "nome": "Vendas de Produtos",
    "tipo": "receita",
    "codigo": "REC-002",
    "categoria_pai_id": 1,
    "descricao": "Receitas com vendas"
}
```

---

## 📊 Centros de Custo

### Listar Centros de Custo

```http
GET /api/v1/centros-custo
```

**Resposta:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "nome": "Administrativo",
            "codigo": "ADM-001",
            "centro_pai_id": null,
            "ativo": true
        }
    ]
}
```

### Buscar Centro de Custo Específico

```http
GET /api/v1/centros-custo/{id}
```

### Criar Centro de Custo

```http
POST /api/v1/centros-custo
Content-Type: application/json

{
    "nome": "Marketing Digital",
    "codigo": "MKT-001",
    "centro_pai_id": null,
    "descricao": "Despesas com marketing online"
}
```

---

## 🏦 Contas Bancárias

### Listar Contas Bancárias

```http
GET /api/v1/contas-bancarias
```

**Resposta:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "banco": "Banco do Brasil",
            "agencia": "1234",
            "conta": "56789-0",
            "tipo": "corrente",
            "saldo_inicial": 10000.00,
            "ativo": true
        }
    ]
}
```

### Buscar Conta Bancária Específica

```http
GET /api/v1/contas-bancarias/{id}
```

### Criar Conta Bancária

```http
POST /api/v1/contas-bancarias
Content-Type: application/json

{
    "banco": "Caixa Econômica",
    "agencia": "9876",
    "conta": "12345-6",
    "tipo": "corrente",
    "saldo_inicial": 5000.00,
    "observacoes": "Conta principal"
}
```

---

## ⚠️ Tratamento de Erros

### Códigos de Status HTTP

- **200** - OK (sucesso)
- **201** - Created (recurso criado)
- **400** - Bad Request (dados inválidos)
- **401** - Unauthorized (token inválido/expirado)
- **404** - Not Found (recurso não encontrado)
- **429** - Too Many Requests (rate limit excedido)
- **500** - Internal Server Error (erro no servidor)

### Formato de Erro

```json
{
    "success": false,
    "error": "Mensagem de erro",
    "errors": {
        "campo1": "Erro específico do campo 1",
        "campo2": "Erro específico do campo 2"
    }
}
```

### Exemplos de Erros

**Token Inválido (401):**
```json
{
    "success": false,
    "error": "Token inválido",
    "timestamp": "2025-01-01 10:30:00"
}
```

**Validação (400):**
```json
{
    "success": false,
    "errors": {
        "nome": "Nome é obrigatório",
        "valor": "Valor deve ser maior que zero"
    }
}
```

**Rate Limit Excedido (429):**
```json
{
    "success": false,
    "error": "Rate limit excedido",
    "timestamp": "2025-01-01 10:30:00"
}
```

---

## 🔒 Segurança

### Rate Limiting

- Cada token possui um limite configurável de requisições por hora
- Padrão: **1000 requisições/hora**
- Após exceder o limite, você receberá erro **429 Too Many Requests**

### IP Whitelist

- Tokens podem ser restritos a IPs específicos
- Configure na criação/edição do token
- Se configurado, apenas IPs na lista poderão usar o token

### Expiração

- Tokens podem ter data de expiração
- Após expirar, receberá erro **401 Unauthorized**
- Configure na criação/edição do token

### Permissões

- Tokens podem ter permissões granulares por módulo
- Módulos disponíveis: **contas_pagar**, **contas_receber**, **produtos**, **clientes**, **fornecedores**, **pedidos**, **movimentacoes**, **categorias**, **centros_custo**, **contas_bancarias**
- Ações: **read**, **create**, **update**, **delete**
- Se não configurado, token terá acesso total

---

## 📊 Monitoramento

### Logs de API

Todas as requisições são registradas e podem ser visualizadas em:

**API Tokens > Ver Detalhes > Logs**

Informações registradas:
- Método HTTP
- Endpoint acessado
- Parâmetros e body
- Status code
- Tempo de resposta
- IP e User-Agent

### Estatísticas

Para cada token você pode ver:
- Total de requisições
- Taxa de sucesso
- Erros
- Tempo médio de resposta
- Endpoints mais acessados

---

## 💡 Exemplos Práticos

### Criar Conta a Pagar com cURL

```bash
curl -X POST https://seudominio.com/api/v1/contas-pagar \
  -H "Authorization: Bearer seu_token_aqui" \
  -H "Content-Type: application/json" \
  -d '{
    "fornecedor_id": 5,
    "descricao": "Pagamento de serviços",
    "valor": 2500.00,
    "data_vencimento": "2025-02-15",
    "data_emissao": "2025-01-01"
  }'
```

### Criar Venda com Produtos (Conta a Receber + Pedido)

```bash
curl -X POST https://seudominio.com/api/v1/contas-receber \
  -H "Authorization: Bearer seu_token_aqui" \
  -H "Content-Type: application/json" \
  -d '{
    "cliente_id": 10,
    "categoria_id": 15,
    "descricao": "Venda de produtos",
    "data_vencimento": "2025-02-28",
    "data_emissao": "2025-01-20",
    "data_competencia": "2025-01-20",
    "numero_documento": "NF-789",
    "criar_pedido": true,
    "pedido": {
      "numero_pedido": "PED-123",
      "data_pedido": "2025-01-20",
      "status": "concluido",
      "frete": 30.00,
      "desconto": 15.00,
      "bonificado": 0,
      "produtos": [
        {
          "produto_id": 1,
          "quantidade": 5,
          "valor_unitario": 100.00,
          "custo_unitario": 60.00
        },
        {
          "produto_id": 2,
          "quantidade": 2,
          "valor_unitario": 250.00,
          "custo_unitario": 150.00
        }
      ]
    }
  }'
```

**Resposta esperada:**
```json
{
    "success": true,
    "id": 50,
    "pedido_id": 30,
    "valor_total": 1000.00,
    "valor_custo_total": 600.00,
    "lucro": 400.00,
    "margem_lucro": 66.67,
    "message": "Conta a receber criada com sucesso com pedido vinculado"
}
```

### Listar Produtos com JavaScript

```javascript
async function listarProdutos() {
    const response = await fetch('https://seudominio.com/api/v1/produtos', {
        headers: {
            'Authorization': 'Bearer seu_token_aqui'
        }
    });
    
    const data = await response.json();
    
    if (data.success) {
        console.log('Produtos:', data.data);
    } else {
        console.error('Erro:', data.error);
    }
}
```

### Criar Cliente com Python

```python
import requests

url = 'https://seudominio.com/api/v1/clientes'
headers = {
    'Authorization': 'Bearer seu_token_aqui',
    'Content-Type': 'application/json'
}
data = {
    'codigo_cliente': 'CLI-PY-001',  # Opcional: código interno do cliente
    'nome_razao_social': 'Cliente Python Ltda',
    'tipo': 'juridica',
    'cpf_cnpj': '12.345.678/0001-90',
    'email': 'python@cliente.com'
}

response = requests.post(url, headers=headers, json=data)
print(response.json())
```

### Atualizar Produto com PHP

```php
<?php
$curl = curl_init();

curl_setopt_array($curl, [
    CURLOPT_URL => 'https://seudominio.com/api/v1/produtos/10',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST => 'PUT',
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer seu_token_aqui',
        'Content-Type: application/json'
    ],
    CURLOPT_POSTFIELDS => json_encode([
        'preco_venda' => 250.00,
        'estoque' => 80
    ])
]);

$response = curl_exec($curl);
curl_close($curl);

echo $response;
?>
```

---

## 🆘 Suporte

Para dúvidas ou problemas:

1. Verifique os logs de API no sistema
2. Confirme que o token está ativo e não expirado
3. Verifique as permissões do token
4. Consulte esta documentação
5. Entre em contato com o suporte técnico

---

**Versão da API:** v1  
**Última atualização:** Janeiro 2026

## 🆕 Changelog

### v1.1 - Janeiro 2026
- ✅ Adicionado suporte a **Pedidos Vinculados** em Contas a Receber
- ✅ Adicionado campo `pedido_id` em Contas a Receber
- ✅ Adicionado endpoint `/api/v1/pedidos` para gerenciamento independente
- ✅ Adicionado cálculo automático de **Lucro** e **Margem** em vendas
- ✅ Adicionado campo `custo_unitario` obrigatório em Produtos
- ✅ Adicionado campo **`sku`** em Produtos para identificação única
- ✅ **Auto-cadastro de produtos via SKU**: Produtos são criados automaticamente se não existirem
- ✅ Adicionado endpoint `/api/v1/produtos/buscar` para autocomplete
- ✅ Adicionado endpoint `/api/v1/pedidos/{id}/analise` para análise financeira
- ✅ Melhorado documentação com exemplos práticos de vendas com produtos

### v1.0 - Dezembro 2025
- 🚀 Lançamento inicial da API REST
- ✅ Endpoints básicos para todos os módulos
- ✅ Sistema de autenticação via Bearer Token
- ✅ Rate limiting e controle de permissões
- ✅ Logs detalhados de requisições
