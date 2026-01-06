# 📦 ENDPOINTS DE PEDIDOS E PRODUTOS - API REST

## ✅ Implementação Completa

Foram criados endpoints **totalmente funcionais** para gerenciar **Pedidos** e **Produtos** via API REST.

---

## 🛍️ PRODUTOS

### Base URL
```
/api/v1/produtos
```

### Endpoints Disponíveis

#### 1. **GET** `/api/v1/produtos`
**Descrição**: Lista todos os produtos da empresa

**Parâmetros Query (opcionais)**:
- `busca` (string): Buscar por código ou nome
- `categoria_id` (integer): Filtrar por categoria

**Exemplo de Requisição**:
```bash
curl -X GET "https://seu-dominio.com/api/v1/produtos?busca=notebook" \
  -H "Authorization: Bearer SEU_TOKEN"
```

**Resposta de Sucesso**:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "empresa_id": 1,
      "nome": "Notebook Dell",
      "codigo": "PROD001",
      "descricao": "Notebook i7 16GB RAM",
      "preco_custo": 2500.00,
      "preco_venda": 3500.00,
      "estoque": 15,
      "estoque_minimo": 5,
      "categoria_id": 2
    }
  ]
}
```

---

#### 2. **GET** `/api/v1/produtos/{id}`
**Descrição**: Busca um produto específico por ID

**Exemplo de Requisição**:
```bash
curl -X GET "https://seu-dominio.com/api/v1/produtos/1" \
  -H "Authorization: Bearer SEU_TOKEN"
```

**Resposta de Sucesso**:
```json
{
  "success": true,
  "data": {
    "id": 1,
    "empresa_id": 1,
    "nome": "Notebook Dell",
    "codigo": "PROD001",
    "preco_venda": 3500.00,
    "estoque": 15
  }
}
```

---

#### 3. **POST** `/api/v1/produtos`
**Descrição**: Cria um novo produto

**Body (JSON)**:
```json
{
  "empresa_id": 1,
  "nome": "Mouse Gamer",
  "codigo": "PROD002",
  "descricao": "Mouse RGB 16000 DPI",
  "preco_custo": 80.00,
  "preco_venda": 150.00,
  "estoque": 50,
  "estoque_minimo": 10,
  "categoria_id": 3
}
```

**Campos**:
- ✅ `empresa_id` (integer, obrigatório): ID da empresa
- ✅ `nome` (string, obrigatório): Nome do produto
- ⚪ `codigo` (string, opcional): Código/SKU
- ⚪ `descricao` (text, opcional): Descrição detalhada
- ⚪ `preco_custo` (decimal, opcional): Preço de custo
- ✅ `preco_venda` (decimal, obrigatório): Preço de venda
- ⚪ `estoque` (integer, opcional): Quantidade em estoque
- ⚪ `estoque_minimo` (integer, opcional): Estoque mínimo
- ⚪ `categoria_id` (integer, opcional): ID da categoria

**Exemplo de Requisição**:
```bash
curl -X POST "https://seu-dominio.com/api/v1/produtos" \
  -H "Authorization: Bearer SEU_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "empresa_id": 1,
    "nome": "Mouse Gamer",
    "preco_venda": 150.00,
    "estoque": 50
  }'
```

**Resposta de Sucesso**:
```json
{
  "success": true,
  "id": 2,
  "message": "Produto criado com sucesso"
}
```

---

#### 4. **PUT** `/api/v1/produtos/{id}`
**Descrição**: Atualiza um produto existente

**Body (JSON)** - Todos os campos são opcionais:
```json
{
  "nome": "Mouse Gamer RGB",
  "preco_venda": 169.90,
  "estoque": 45
}
```

**Exemplo de Requisição**:
```bash
curl -X PUT "https://seu-dominio.com/api/v1/produtos/2" \
  -H "Authorization: Bearer SEU_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "preco_venda": 169.90
  }'
```

---

#### 5. **DELETE** `/api/v1/produtos/{id}`
**Descrição**: Exclui um produto (soft delete)

**Exemplo de Requisição**:
```bash
curl -X DELETE "https://seu-dominio.com/api/v1/produtos/2" \
  -H "Authorization: Bearer SEU_TOKEN"
```

**Resposta de Sucesso**:
```json
{
  "success": true,
  "message": "Produto excluído com sucesso"
}
```

---

## 📋 PEDIDOS

### Base URL
```
/api/v1/pedidos
```

### Endpoints Disponíveis

#### 1. **GET** `/api/v1/pedidos`
**Descrição**: Lista todos os pedidos da empresa

**Parâmetros Query (opcionais)**:
- `status` (string): Filtrar por status (pendente, processando, concluido, cancelado, reembolsado)
- `origem` (string): Filtrar por origem (woocommerce, manual, externo)
- `cliente_id` (integer): Filtrar por cliente
- `data_inicio` (date): Data inicial (YYYY-MM-DD)
- `data_fim` (date): Data final (YYYY-MM-DD)

**Exemplo de Requisição**:
```bash
curl -X GET "https://seu-dominio.com/api/v1/pedidos?status=pendente&data_inicio=2026-01-01" \
  -H "Authorization: Bearer SEU_TOKEN"
```

**Resposta de Sucesso**:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "empresa_id": 1,
      "cliente_id": 10,
      "cliente_nome": "João Silva",
      "numero_pedido": "PED-2026-001",
      "data_pedido": "2026-01-06",
      "total": 299.90,
      "status": "pendente",
      "origem": "manual",
      "total_itens": 3,
      "observacoes": "Entregar pela manhã"
    }
  ],
  "total": 1
}
```

---

#### 2. **GET** `/api/v1/pedidos/{id}`
**Descrição**: Busca um pedido específico **com seus itens**

**Exemplo de Requisição**:
```bash
curl -X GET "https://seu-dominio.com/api/v1/pedidos/1" \
  -H "Authorization: Bearer SEU_TOKEN"
```

**Resposta de Sucesso**:
```json
{
  "success": true,
  "data": {
    "id": 1,
    "empresa_id": 1,
    "cliente_id": 10,
    "cliente_nome": "João Silva",
    "numero_pedido": "PED-2026-001",
    "data_pedido": "2026-01-06",
    "total": 299.90,
    "status": "pendente",
    "origem": "manual",
    "itens": [
      {
        "id": 1,
        "pedido_id": 1,
        "produto_id": 5,
        "produto_nome": "Mouse Gamer",
        "quantidade": 2,
        "preco_unitario": 149.95,
        "subtotal": 299.90
      }
    ]
  }
}
```

---

#### 3. **POST** `/api/v1/pedidos`
**Descrição**: Cria um novo pedido (com ou sem itens)

**Body (JSON)**:
```json
{
  "empresa_id": 1,
  "cliente_id": 10,
  "numero_pedido": "PED-2026-002",
  "data_pedido": "2026-01-06",
  "total": 450.00,
  "status": "pendente",
  "origem": "externo",
  "observacoes": "Cliente preferencial",
  "itens": [
    {
      "produto_id": 5,
      "quantidade": 2,
      "preco_unitario": 150.00,
      "subtotal": 300.00
    },
    {
      "produto_id": 8,
      "quantidade": 1,
      "preco_unitario": 150.00,
      "subtotal": 150.00
    }
  ]
}
```

**Campos**:
- ✅ `empresa_id` (integer, obrigatório): ID da empresa
- ⚪ `cliente_id` (integer, opcional): ID do cliente
- ⚪ `numero_pedido` (string, opcional): Número do pedido (gerado automaticamente se não fornecido)
- ✅ `data_pedido` (date, obrigatório): Data do pedido (YYYY-MM-DD)
- ✅ `total` (decimal, obrigatório): Valor total do pedido
- ⚪ `status` (string, opcional): Status (padrão: pendente)
- ⚪ `origem` (string, opcional): Origem (padrão: externo)
- ⚪ `observacoes` (text, opcional): Observações
- ⚪ `itens` (array, opcional): Array de itens do pedido

**Exemplo de Requisição**:
```bash
curl -X POST "https://seu-dominio.com/api/v1/pedidos" \
  -H "Authorization: Bearer SEU_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "empresa_id": 1,
    "cliente_id": 10,
    "data_pedido": "2026-01-06",
    "total": 299.90,
    "itens": [
      {
        "produto_id": 5,
        "quantidade": 2,
        "preco_unitario": 149.95
      }
    ]
  }'
```

**Resposta de Sucesso**:
```json
{
  "success": true,
  "id": 1,
  "message": "Pedido criado com sucesso"
}
```

---

#### 4. **PUT** `/api/v1/pedidos/{id}`
**Descrição**: Atualiza um pedido existente

**Body (JSON)** - Todos os campos são opcionais:
```json
{
  "status": "processando",
  "total": 320.00,
  "observacoes": "Pedido em separação"
}
```

**Exemplo de Requisição**:
```bash
curl -X PUT "https://seu-dominio.com/api/v1/pedidos/1" \
  -H "Authorization: Bearer SEU_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "status": "concluido"
  }'
```

---

#### 5. **DELETE** `/api/v1/pedidos/{id}`
**Descrição**: Exclui um pedido

**Exemplo de Requisição**:
```bash
curl -X DELETE "https://seu-dominio.com/api/v1/pedidos/1" \
  -H "Authorization: Bearer SEU_TOKEN"
```

**Resposta de Sucesso**:
```json
{
  "success": true,
  "message": "Pedido excluído com sucesso"
}
```

---

## 🔐 Autenticação

Todos os endpoints requerem autenticação via **Bearer Token**.

**Header obrigatório**:
```
Authorization: Bearer SEU_TOKEN_AQUI
```

**Como obter um token**:
1. Acesse `/api-tokens` no sistema
2. Clique em "Novo Token"
3. Configure as permissões
4. Copie o token gerado

---

## 📊 Status dos Pedidos

Os pedidos podem ter os seguintes status:

- `pendente`: Pedido criado, aguardando processamento
- `processando`: Pedido em separação/preparação
- `concluido`: Pedido finalizado e entregue
- `cancelado`: Pedido cancelado
- `reembolsado`: Pedido reembolsado

---

## 🌍 Origens dos Pedidos

Os pedidos podem ter as seguintes origens:

- `woocommerce`: Importado do WooCommerce
- `manual`: Criado manualmente no sistema
- `externo`: Criado via API externa

---

## ⚠️ Códigos de Erro

### 400 - Bad Request
Requisição inválida ou parâmetros faltando.

```json
{
  "success": false,
  "errors": {
    "nome": "Nome é obrigatório",
    "preco_venda": "Preço de venda é obrigatório"
  }
}
```

### 401 - Unauthorized
Token inválido ou ausente.

```json
{
  "success": false,
  "error": "Unauthorized",
  "message": "Token inválido ou ausente"
}
```

### 404 - Not Found
Recurso não encontrado.

```json
{
  "success": false,
  "error": "Produto não encontrado"
}
```

### 500 - Internal Server Error
Erro interno do servidor.

```json
{
  "success": false,
  "error": "Erro ao criar pedido: [detalhes]"
}
```

---

## 📚 Documentação Completa

Acesse a documentação interativa completa em:

```
https://seu-dominio.com/api/docs
```

A documentação inclui:
- ✅ Exemplos em 4 linguagens (cURL, PHP, JavaScript, Python)
- ✅ Todos os endpoints detalhados
- ✅ Parâmetros e respostas
- ✅ Códigos de erro
- ✅ Botão para copiar código
- ✅ Tema dark/light

---

## 🎯 Exemplos Práticos

### Criar um Produto e Adicionar a um Pedido

**1. Criar o produto**:
```bash
curl -X POST "https://seu-dominio.com/api/v1/produtos" \
  -H "Authorization: Bearer abc123..." \
  -H "Content-Type: application/json" \
  -d '{
    "empresa_id": 1,
    "nome": "Teclado Mecânico",
    "preco_venda": 250.00,
    "estoque": 30
  }'
```

**Resposta**: `{"success": true, "id": 15}`

**2. Criar pedido com o produto**:
```bash
curl -X POST "https://seu-dominio.com/api/v1/pedidos" \
  -H "Authorization: Bearer abc123..." \
  -H "Content-Type: application/json" \
  -d '{
    "empresa_id": 1,
    "cliente_id": 5,
    "data_pedido": "2026-01-06",
    "total": 500.00,
    "itens": [
      {
        "produto_id": 15,
        "quantidade": 2,
        "preco_unitario": 250.00
      }
    ]
  }'
```

**Resposta**: `{"success": true, "id": 20, "message": "Pedido criado com sucesso"}`

---

## ✅ Checklist de Implementação

- [x] Display errors desabilitado (modo produção)
- [x] Endpoints de Produtos (5 métodos: GET, GET/{id}, POST, PUT, DELETE)
- [x] Endpoints de Pedidos (5 métodos: GET, GET/{id}, POST, PUT, DELETE)
- [x] Validação de dados
- [x] Filtros e parâmetros de busca
- [x] Suporte a itens de pedido
- [x] Documentação atualizada
- [x] Autenticação via Bearer Token
- [x] Logs de requisições
- [x] Tratamento de erros
- [x] Respostas JSON padronizadas

---

## 🚀 Pronto para Usar!

Os endpoints estão **100% funcionais** e prontos para integração!

**Teste agora**: Acesse `/api/docs` e veja exemplos práticos em 4 linguagens! 🎉
