# 📦 Sistema de Pedidos Vinculados a Contas a Receber

## 📋 Visão Geral

Este documento descreve como funciona o vínculo entre **Pedidos** e **Contas a Receber** no sistema, permitindo rastrear produtos vendidos, custos e calcular lucro/margem de cada venda.

---

## 🗄️ Estrutura do Banco de Dados

### 1. **contas_receber**
```sql
- id
- empresa_id
- cliente_id
- pedido_id ⭐ NOVO (vincula ao pedido)
- categoria_id
- centro_custo_id
- numero_documento
- descricao
- valor_total
- data_emissao
- data_vencimento
- data_recebimento
- status
```

### 2. **pedidos_vinculados**
```sql
- id
- empresa_id
- origem (manual, woocommerce, externo)
- origem_id
- numero_pedido
- cliente_id
- data_pedido
- status
- valor_total ⭐ (soma dos itens)
- valor_custo_total ⭐ (soma dos custos dos itens)
- dados_origem (JSON)
```

### 3. **pedidos_itens**
```sql
- id
- pedido_id
- produto_id
- codigo_produto_origem
- nome_produto
- quantidade
- valor_unitario ⭐ (preço de venda)
- valor_total ⭐ (quantidade × valor_unitario)
- custo_unitario ⭐ (custo do produto)
- custo_total ⭐ (quantidade × custo_unitario)
```

### 4. **produtos**
```sql
- id
- empresa_id
- codigo
- nome
- descricao
- custo_unitario ⭐ (custo de compra/produção)
- preco_venda ⭐ (preço de venda)
- unidade_medida
```

---

## 📊 Cálculo de Lucro e Margem

### Fórmulas

```
LUCRO = Valor Total - Custo Total
MARGEM (%) = (Lucro / Custo Total) × 100
```

### Exemplo Prático

```
Pedido #001:
├─ Produto A: 2 unidades × R$ 100,00 = R$ 200,00
│  └─ Custo: 2 unidades × R$ 60,00 = R$ 120,00
│
├─ Produto B: 1 unidade × R$ 50,00 = R$ 50,00
│  └─ Custo: 1 unidade × R$ 30,00 = R$ 30,00
│
└─ TOTAIS:
   ├─ Valor Total: R$ 250,00
   ├─ Custo Total: R$ 150,00
   ├─ Lucro: R$ 100,00
   └─ Margem: 66,67%
```

---

## 🔄 Fluxo de Criação

### Opção 1: Criar Conta a Receber COM Pedido

```
1. Usuário cria nova Conta a Receber
2. Marca checkbox "Criar pedido com produtos"
3. Sistema exibe campos:
   ├─ Número do Pedido (auto ou manual)
   ├─ Data do Pedido
   ├─ Status do Pedido
   └─ Grade de Produtos:
      ├─ Produto (select com busca)
      ├─ Quantidade
      ├─ Valor Unitário
      ├─ Custo Unitário (puxado do cadastro)
      └─ Subtotal
4. Sistema salva:
   ├─ conta_receber (com pedido_id)
   ├─ pedido_vinculado
   └─ pedidos_itens (cada produto)
5. Calcula automaticamente:
   ├─ valor_total do pedido
   └─ valor_custo_total do pedido
```

### Opção 2: Criar Conta a Receber SEM Pedido

```
1. Usuário cria nova Conta a Receber
2. NÃO marca checkbox de pedido
3. Fluxo normal (sem produtos)
4. pedido_id fica NULL
```

---

## 🎯 O Que Foi Implementado

### ✅ Estrutura do Banco

- ✅ Tabelas `pedidos_vinculados` e `pedidos_itens` já existiam
- ✅ Campo `pedido_id` adicionado em `contas_receber` (Migration 050)
- ✅ Foreign Key e índice configurados
- ✅ Campos de custo em produtos e itens já existiam

### ✅ Models

- ✅ `PedidoVinculado` model com:
  - `findAll()`, `findById()`, `create()`, `update()`
  - `recalcularTotais()` - recalcula totais baseado nos itens
  - `getEstatisticas()` - retorna métricas com margem de lucro

- ✅ `PedidoItem` model com:
  - `findByPedido()`, `create()`, `update()`, `delete()`
  - `getProdutosMaisVendidos()` - produtos mais vendidos

- ✅ `Produto` model com:
  - Campos `custo_unitario` e `preco_venda`
  - `calcularMargemLucro()` - calcula margem
  - `findForSelect()` - busca produtos para autocomplete

---

## ⚠️ O Que FALTA Implementar

### ❌ Interface/Views

1. **Formulário de Criação de Conta a Receber** (`app/views/contas_receber/create.php`)
   - [ ] Adicionar seção "Pedido" (colapsável)
   - [ ] Checkbox "Esta venda possui produtos?"
   - [ ] Campos do pedido (número, data, status)
   - [ ] Grade de produtos (JavaScript para adicionar/remover linhas)
   - [ ] Autocomplete de produtos
   - [ ] Cálculo automático de totais e margem

2. **Formulário de Edição de Conta a Receber** (`app/views/contas_receber/edit.php`)
   - [ ] Exibir pedido vinculado (se houver)
   - [ ] Permitir editar produtos do pedido
   - [ ] Recalcular totais

3. **Visualização de Conta a Receber** (`app/views/contas_receber/show.php`)
   - [ ] Exibir detalhes do pedido
   - [ ] Tabela de produtos com:
     - Código, Nome, Quantidade, Valor Unit., Total
     - Custo Unit., Custo Total
     - Lucro e Margem por item
   - [ ] Card com resumo financeiro:
     - Valor Total da Venda
     - Custo Total dos Produtos
     - Lucro Bruto
     - Margem (%)

4. **Listagem de Contas a Receber** (`app/views/contas_receber/index.php`)
   - [ ] Ícone indicando se tem pedido vinculado
   - [ ] Coluna com margem (opcional)

### ❌ Controller

**`app/controllers/ContaReceberController.php`**

- [ ] Modificar `store()`:
  ```php
  - Verificar se checkbox de pedido está marcado
  - Criar pedido_vinculado
  - Criar pedidos_itens (loop pelos produtos)
  - Calcular totais do pedido
  - Vincular pedido_id na conta_receber
  ```

- [ ] Modificar `update()`:
  ```php
  - Atualizar pedido (se existir)
  - Atualizar/adicionar/remover itens
  - Recalcular totais
  ```

- [ ] Modificar `show()`:
  ```php
  - Buscar pedido vinculado
  - Buscar itens do pedido
  - Calcular métricas (lucro, margem)
  ```

### ❌ JavaScript

**`public/assets/js/pedido-produtos.js`** (novo arquivo)

- [ ] Adicionar linha de produto na grade
- [ ] Remover linha de produto
- [ ] Autocomplete de produtos (AJAX)
- [ ] Preencher automaticamente:
  - Valor unitário (preco_venda do produto)
  - Custo unitário (custo_unitario do produto)
- [ ] Calcular subtotais (quantidade × valor)
- [ ] Calcular total geral e custo total
- [ ] Calcular lucro e margem em tempo real
- [ ] Validações (quantidade > 0, produto selecionado, etc)

### ❌ API/AJAX

**Endpoint para buscar produtos** (opcional, pode usar o existente)

```php
GET /api/produtos/buscar?empresa_id=X&q=termo
Retorna: [
  {
    id: 1,
    codigo: "PROD001",
    nome: "Produto A",
    preco_venda: 100.00,
    custo_unitario: 60.00,
    unidade_medida: "UN"
  },
  ...
]
```

---

## 🎨 Wireframe da Interface

### Formulário de Criação (Expandido)

```
┌─────────────────────────────────────────────────────────────┐
│ Nova Conta a Receber                                        │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│ [Cliente ▼] [Categoria ▼] [Centro de Custo ▼]            │
│ [Nº Doc] [Valor Total] [Data Emissão] [Data Vencimento]  │
│                                                             │
│ ☑ Esta venda possui produtos (criar pedido)               │
│                                                             │
│ ┌─── Detalhes do Pedido ─────────────────────────────┐   │
│ │ Nº Pedido: [AUTO-001  ▼] Data: [2026-01-20]       │   │
│ │ Status: [Pendente ▼]                                │   │
│ │                                                      │   │
│ │ Produtos:                                            │   │
│ │ ┌──────────────────────────────────────────────┐    │   │
│ │ │ Produto       │ Qtd │ Vl.Unit │ Custo │ Total│    │   │
│ │ ├──────────────────────────────────────────────┤    │   │
│ │ │ PROD001 - A   │  2  │ 100,00  │ 60,00 │200,00│ ✕  │   │
│ │ │ PROD002 - B   │  1  │  50,00  │ 30,00 │ 50,00│ ✕  │   │
│ │ └──────────────────────────────────────────────┘    │   │
│ │ [+ Adicionar Produto]                               │   │
│ │                                                      │   │
│ │ ┌─ Resumo ────────────────────────────────┐         │   │
│ │ │ Valor Total:    R$ 250,00               │         │   │
│ │ │ Custo Total:    R$ 150,00               │         │   │
│ │ │ Lucro:          R$ 100,00 (66,67%)     │         │   │
│ │ └───────────────────────────────────────┘         │   │
│ └─────────────────────────────────────────────────────┘   │
│                                                             │
│ [Salvar] [Cancelar]                                        │
└─────────────────────────────────────────────────────────────┘
```

### Visualização (com Pedido)

```
┌─────────────────────────────────────────────────────────────┐
│ Conta a Receber #001                         [Editar] [✕]  │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│ Cliente: João da Silva                                      │
│ Valor: R$ 250,00                                           │
│ Status: Pendente                                           │
│                                                             │
│ 📦 Pedido Vinculado: #PED-001                             │
│ ┌──────────────────────────────────────────────────────┐  │
│ │ Produto          │ Qtd │ Vl.Unit │ Total │ Margem   │  │
│ ├──────────────────────────────────────────────────────┤  │
│ │ PROD001 - A      │  2  │ 100,00  │200,00 │ 66,67%  │  │
│ │ PROD002 - B      │  1  │  50,00  │ 50,00 │ 66,67%  │  │
│ ├──────────────────────────────────────────────────────┤  │
│ │ TOTAIS           │  3  │    -    │250,00 │ 66,67%  │  │
│ └──────────────────────────────────────────────────────┘  │
│                                                             │
│ ┌─ Análise Financeira ───────────────────────┐            │
│ │ 💰 Valor Total de Venda    R$   250,00     │            │
│ │ 📉 Custo Total Produtos    R$   150,00     │            │
│ │ ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━   │            │
│ │ 📈 Lucro Bruto             R$   100,00     │            │
│ │ 📊 Margem de Lucro            66,67%       │            │
│ └────────────────────────────────────────────┘            │
└─────────────────────────────────────────────────────────────┘
```

---

## 📝 Próximos Passos

### Prioridade ALTA
1. ✅ Criar migration para adicionar `pedido_id` em `contas_receber`
2. ⏳ Implementar formulário de criação com grade de produtos
3. ⏳ Implementar JavaScript para manipular grade
4. ⏳ Modificar controller `store()` para salvar pedido + itens

### Prioridade MÉDIA
5. ⏳ Implementar visualização de pedido em `show.php`
6. ⏳ Adicionar edição de pedidos
7. ⏳ Exibir indicador na listagem

### Prioridade BAIXA
8. ⏳ Adicionar filtro por "Com Pedido" / "Sem Pedido"
9. ⏳ Relatório de margem de lucro
10. ⏳ Dashboard com métricas de pedidos

---

## 💡 Dicas de Implementação

### JavaScript - Adicionar Produto

```javascript
function adicionarProdutoLinha(produto) {
    const html = `
        <tr class="linha-produto" data-produto-id="${produto.id}">
            <td>
                <input type="hidden" name="produtos[${index}][produto_id]" value="${produto.id}">
                ${produto.codigo} - ${produto.nome}
            </td>
            <td>
                <input type="number" name="produtos[${index}][quantidade]" 
                       min="1" step="0.001" value="1" 
                       class="qtd-produto" data-index="${index}">
            </td>
            <td>
                <input type="number" name="produtos[${index}][valor_unitario]" 
                       step="0.01" value="${produto.preco_venda}" 
                       class="valor-unitario" data-index="${index}">
            </td>
            <td>
                <input type="hidden" name="produtos[${index}][custo_unitario]" 
                       value="${produto.custo_unitario}">
                R$ ${formatMoney(produto.custo_unitario)}
            </td>
            <td class="subtotal" data-index="${index}">
                R$ ${formatMoney(produto.preco_venda)}
            </td>
            <td>
                <button type="button" class="btn-remover" onclick="removerLinha(this)">✕</button>
            </td>
        </tr>
    `;
    
    document.querySelector('#produtos-table tbody').insertAdjacentHTML('beforeend', html);
    recalcularTotais();
}

function recalcularTotais() {
    let valorTotal = 0;
    let custoTotal = 0;
    
    document.querySelectorAll('.linha-produto').forEach(linha => {
        const qtd = parseFloat(linha.querySelector('.qtd-produto').value) || 0;
        const valorUnit = parseFloat(linha.querySelector('.valor-unitario').value) || 0;
        const custoUnit = parseFloat(linha.querySelector('[name*="custo_unitario"]').value) || 0;
        
        const subtotal = qtd * valorUnit;
        const custoProduto = qtd * custoUnit;
        
        linha.querySelector('.subtotal').textContent = `R$ ${formatMoney(subtotal)}`;
        
        valorTotal += subtotal;
        custoTotal += custoProduto;
    });
    
    const lucro = valorTotal - custoTotal;
    const margem = custoTotal > 0 ? (lucro / custoTotal) * 100 : 0;
    
    document.getElementById('valor-total-pedido').textContent = `R$ ${formatMoney(valorTotal)}`;
    document.getElementById('custo-total-pedido').textContent = `R$ ${formatMoney(custoTotal)}`;
    document.getElementById('lucro-pedido').textContent = `R$ ${formatMoney(lucro)} (${margem.toFixed(2)}%)`;
    
    // Atualiza também o campo de valor total da conta a receber
    document.getElementById('valor_total').value = valorTotal.toFixed(2);
}
```

### PHP - Salvar Pedido no Controller

```php
public function store(Request $request, Response $response)
{
    $data = $request->all();
    
    // Se tem pedido marcado
    if (isset($data['criar_pedido']) && $data['criar_pedido'] == '1') {
        // 1. Criar pedido
        $pedidoModel = new PedidoVinculado();
        $pedidoData = [
            'empresa_id' => $data['empresa_id'],
            'origem' => 'manual',
            'origem_id' => uniqid('manual_'),
            'numero_pedido' => $data['numero_pedido'] ?? 'PED-' . date('YmdHis'),
            'cliente_id' => $data['cliente_id'],
            'data_pedido' => $data['data_pedido'] ?? date('Y-m-d H:i:s'),
            'data_atualizacao' => date('Y-m-d H:i:s'),
            'status' => $data['status_pedido'] ?? 'pendente',
            'valor_total' => 0,
            'valor_custo_total' => 0
        ];
        
        $pedidoId = $pedidoModel->create($pedidoData);
        
        // 2. Criar itens do pedido
        if (isset($data['produtos']) && is_array($data['produtos'])) {
            $pedidoItemModel = new PedidoItem();
            $valorTotal = 0;
            $custoTotal = 0;
            
            foreach ($data['produtos'] as $produto) {
                $quantidade = (float)$produto['quantidade'];
                $valorUnitario = (float)$produto['valor_unitario'];
                $custoUnitario = (float)$produto['custo_unitario'];
                
                $itemData = [
                    'pedido_id' => $pedidoId,
                    'produto_id' => $produto['produto_id'],
                    'nome_produto' => $produto['nome_produto'],
                    'quantidade' => $quantidade,
                    'valor_unitario' => $valorUnitario,
                    'valor_total' => $quantidade * $valorUnitario,
                    'custo_unitario' => $custoUnitario,
                    'custo_total' => $quantidade * $custoUnitario
                ];
                
                $pedidoItemModel->create($itemData);
                
                $valorTotal += $itemData['valor_total'];
                $custoTotal += $itemData['custo_total'];
            }
            
            // 3. Atualizar totais do pedido
            $pedidoModel->update($pedidoId, [
                ...pedidoData,
                'valor_total' => $valorTotal,
                'valor_custo_total' => $custoTotal
            ]);
        }
        
        // 4. Vincular pedido à conta a receber
        $data['pedido_id'] = $pedidoId;
    }
    
    // Criar conta a receber normalmente
    $this->contaReceberModel = new ContaReceber();
    $id = $this->contaReceberModel->create($data);
    
    $_SESSION['success'] = 'Conta a receber criada com sucesso!';
    $response->redirect('/contas-receber');
}
```

---

## ✅ Conclusão

O sistema está **estruturado e pronto** para funcionar. Falta apenas implementar a **interface/views** e a **lógica no controller** para criar e editar pedidos vinculados às contas a receber.

Com isso implementado, você poderá:
- ✅ Criar vendas com produtos
- ✅ Rastrear custos por produto
- ✅ Calcular lucro e margem automaticamente
- ✅ Visualizar análise financeira de cada venda
- ✅ Gerar relatórios de lucratividade

**Quer que eu implemente agora a interface completa (formulários + JavaScript)?**
