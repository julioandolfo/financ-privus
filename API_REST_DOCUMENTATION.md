# 📚 Documentação da API REST

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

### Buscar Conta Específica

```http
GET /api/v1/contas-receber/{id}
```

### Criar Conta a Receber

```http
POST /api/v1/contas-receber
Content-Type: application/json

{
    "cliente_id": 3,
    "descricao": "Venda de produtos",
    "valor": 2500.00,
    "data_vencimento": "2025-01-20",
    "data_emissao": "2025-01-01",
    "numero_documento": "NF-456"
}
```

### Atualizar Conta a Receber

```http
PUT /api/v1/contas-receber/{id}
```

### Excluir Conta a Receber

```http
DELETE /api/v1/contas-receber/{id}
```

---

## 📦 Produtos

### Listar Produtos

```http
GET /api/v1/produtos
```

**Resposta:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "nome": "Produto Exemplo",
            "codigo": "PROD-001",
            "preco_venda": 150.00,
            "preco_custo": 80.00,
            "estoque": 50,
            "ativo": true
        }
    ]
}
```

### Buscar Produto Específico

```http
GET /api/v1/produtos/{id}
```

### Criar Produto

```http
POST /api/v1/produtos
Content-Type: application/json

{
    "nome": "Novo Produto",
    "codigo": "PROD-002",
    "preco_venda": 200.00,
    "preco_custo": 100.00,
    "estoque": 100,
    "estoque_minimo": 10,
    "descricao": "Descrição do produto"
}
```

**Resposta:**
```json
{
    "success": true,
    "id": 15,
    "message": "Produto criado com sucesso"
}
```

### Atualizar Produto

```http
PUT /api/v1/produtos/{id}
Content-Type: application/json

{
    "preco_venda": 220.00,
    "estoque": 95
}
```

### Excluir Produto

```http
DELETE /api/v1/produtos/{id}
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

### Criar Cliente

```http
POST /api/v1/clientes
Content-Type: application/json

{
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
**Última atualização:** Dezembro 2025
