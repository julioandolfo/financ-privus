# 🔧 Correções no Sistema de Configurações

## 📋 Problemas Identificados

### 1. **Lógica de salvamento incorreta**
O método `salvar()` no `ConfiguracaoController` só processava campos que continham ponto (`.`) no nome, ignorando todos os outros campos.

### 2. **Grupos inconsistentes no banco**
As migrations 030 e 031 criaram configurações com grupos incorretos:
- Configurações de `backup.*`, `dashboard.*`, `email.*`, `financeiro.*`, `integracao.*` e `relatorios.*` foram todas colocadas no grupo `sistema`
- Isso causava que apenas a aba "Sistema" funcionasse, pois tinha todas essas configurações misturadas

### 3. **Abas faltando na interface**
A view `configuracoes/index.php` não tinha abas para os novos grupos criados na migration 031.

---

## ✅ Correções Aplicadas

### 1. **ConfiguracaoController.php**

#### Antes:
```php
foreach ($data as $chave => $valor) {
    if (strpos($chave, '.') !== false) {
        $configuracoes[$chave] = $valor;
    }
}
```

#### Depois:
```php
foreach ($data as $chave => $valor) {
    if (isset($configsGrupo[$chave])) {
        // Para senha/password/key/token, só salvar se não estiver vazio
        if (strpos($chave, 'senha') !== false || strpos($chave, 'password') !== false || 
            strpos($chave, 'key') !== false || strpos($chave, 'token') !== false) {
            if (!empty(trim($valor))) {
                $configuracoes[$chave] = trim($valor);
            }
        } else {
            // Para outros campos, salvar mesmo se vazio
            $configuracoes[$chave] = is_string($valor) ? trim($valor) : $valor;
        }
    }
}
```

**Melhorias:**
- ✅ Processa TODOS os campos do grupo
- ✅ Valida se o campo existe no grupo
- ✅ Campos de senha só são atualizados se preenchidos (preserva valores existentes)
- ✅ Outros campos podem ser limpos (salva string vazia)
- ✅ Trim automático em strings
- ✅ Tratamento de erros com try-catch
- ✅ Limpeza de cache após salvar

### 2. **Configuracao.php (Model)**

#### Método `setMultiplas()`:
```php
public static function setMultiplas($configuracoes)
{
    if (empty($configuracoes)) {
        return true; // Nada para salvar
    }
    
    $instance = new self();
    $instance->db->beginTransaction();
    
    try {
        foreach ($configuracoes as $chave => $valor) {
            $result = self::set($chave, $valor);
            if (!$result) {
                throw new \Exception("Falha ao salvar configuração: {$chave}");
            }
        }
        $instance->db->commit();
        self::clearCache();
        return true;
    } catch (\Exception $e) {
        $instance->db->rollBack();
        error_log("Erro ao salvar múltiplas configurações: " . $e->getMessage());
        return false;
    }
}
```

**Melhorias:**
- ✅ Validação de array vazio
- ✅ Lança exceção se falhar
- ✅ Logs detalhados de erros
- ✅ Limpa cache após sucesso

#### Método `set()`:
```php
// Verificar se já existe para preservar tipo
$sql = "SELECT id, tipo FROM {$instance->table} WHERE chave = :chave LIMIT 1";
$stmt = $instance->db->prepare($sql);
$stmt->execute(['chave' => $chave]);
$exists = $stmt->fetch(PDO::FETCH_ASSOC);

// Se tipo não foi especificado, usar tipo existente
if ($tipo === null) {
    if ($exists && !empty($exists['tipo'])) {
        $tipo = $exists['tipo'];
    } else {
        $tipo = self::detectType($valor);
    }
}
```

**Melhorias:**
- ✅ Preserva o tipo existente ao atualizar
- ✅ Detecta tipo apenas em novos registros

### 3. **Migration 050 - Correção de Grupos**

Criada migration `050_corrigir_grupos_configuracoes.php` que corrige os grupos:

```sql
-- Mover configurações financeiras para grupo próprio
UPDATE configuracoes SET grupo = 'financeiro' 
WHERE chave IN (
    'financeiro.permitir_data_retroativa',
    'financeiro.dias_retroativos_limite',
    'financeiro.bloquear_edicao_conciliado',
    'financeiro.aprovar_contas_antes_pagar',
    'financeiro.valor_minimo_aprovacao'
);

-- Mover configurações de dashboard para grupo próprio
UPDATE configuracoes SET grupo = 'dashboard' ...

-- E assim por diante para: email, backup, integracoes, relatorios
```

**Resultado:**
- ✅ Cada módulo tem seu próprio grupo
- ✅ Configurações organizadas logicamente
- ✅ 18 grupos bem definidos

### 4. **View configuracoes/index.php**

Adicionadas 6 novas abas:

```php
'financeiro' => ['nome' => 'Financeiro', 'icon' => '...'],
'dashboard' => ['nome' => 'Dashboard', 'icon' => '...'],
'relatorios' => ['nome' => 'Relatórios', 'icon' => '...'],
'email' => ['nome' => 'Email', 'icon' => '...'],
'backup' => ['nome' => 'Backup', 'icon' => '...'],
'integracoes' => ['nome' => 'Integrações', 'icon' => '...'],
```

**Resultado:**
- ✅ 18 abas no total
- ✅ Todos os grupos acessíveis pela interface

---

## 📊 Situação Atual

### Grupos de Configurações (18 grupos):

1. **empresas** (3 configs)
2. **usuarios** (3 configs)
3. **fornecedores** (4 configs)
4. **clientes** (4 configs)
5. **categorias** (3 configs)
6. **centros_custo** (3 configs)
7. **contas_bancarias** (1 config)
8. **contas_pagar** (4 configs)
9. **contas_receber** (4 configs)
10. **movimentacoes** (1 config)
11. **financeiro** (5 configs) ⭐ NOVO
12. **dashboard** (3 configs) ⭐ NOVO
13. **relatorios** (3 configs) ⭐ NOVO
14. **email** (6 configs) ⭐ NOVO
15. **backup** (4 configs) ⭐ NOVO
16. **integracoes** (3 configs) ⭐ NOVO
17. **api** (12 configs - incluindo IA)
18. **sistema** (16 configs)

**Total: 82 configurações organizadas**

---

## 🧪 Como Testar

1. Acesse `/configuracoes`
2. Verifique se todas as 18 abas aparecem
3. Teste salvar configurações em diferentes abas:
   - **Empresas**: Marque/desmarque checkboxes → Salvar
   - **Email**: Preencha SMTP host, porta, usuário, senha → Salvar
   - **API**: Adicione OpenAI key → Salvar
   - **Sistema**: Altere título, cores → Salvar
4. Recarregue a página e confirme que os valores foram salvos
5. Teste campos de senha: deixe vazio para manter valor atual, ou preencha para alterar

---

## 🛠️ Script de Verificação

Criado `check_configuracoes.php` para diagnóstico:

```bash
php check_configuracoes.php
```

**Output:**
- Lista todos os grupos
- Mostra todas as configurações de cada grupo
- Estatísticas gerais
- Identifica configurações sem grupo
- Compara grupos esperados vs encontrados

---

## 🎯 Benefícios

✅ **Todas as configurações salvam corretamente**  
✅ **Organização lógica por módulo**  
✅ **Interface completa com todas as abas**  
✅ **Campos de senha protegidos (não sobrescreve se vazio)**  
✅ **Logs de erro detalhados**  
✅ **Cache otimizado**  
✅ **Fácil manutenção e debug**  

---

## 📝 Observações Importantes

### Campos de Senha/Token/Key
- Se deixar o campo **vazio**, o valor atual é **mantido**
- Se preencher o campo, o valor é **atualizado**
- Isso evita sobrescrever senhas acidentalmente

### Checkboxes (Boolean)
- Se **marcado**: salva como `true`
- Se **desmarcado**: salva como `false`
- Tratamento automático pelo controller

### Cache
- Limpo automaticamente após salvar
- Use `Configuracao::clearCache()` se necessário

---

## 🔄 Migrations Relacionadas

- **030**: Cria tabela e configurações iniciais
- **031**: Adiciona configurações avançadas (com grupos incorretos)
- **050**: Corrige grupos (NOVA) ✅

---

## 📚 Arquivos Modificados

1. ✅ `app/controllers/ConfiguracaoController.php`
2. ✅ `app/models/Configuracao.php`
3. ✅ `app/views/configuracoes/index.php`
4. ✅ `migrations/050_corrigir_grupos_configuracoes.php` (NOVO)
5. ✅ `check_configuracoes.php` (NOVO)

---

**Data da Correção:** 02/01/2026  
**Status:** ✅ Concluído e Testado
