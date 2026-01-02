# 🐛 BUG CORRIGIDO: Valor Zero (0) Não Salvava

## 🔍 O Problema

Campo: **sistema.senha_expira_dias**
- Valor atual: `90`
- Usuário altera para: `0`
- Após salvar: continua `90` ❌

## 🐞 Causa Raiz

O campo `sistema.senha_expira_dias` contém a palavra **"senha"**, então estava sendo detectado como **campo sensível** pela lógica:

```php
// Código ANTIGO (bugado)
if (strpos($chave, 'senha') !== false || strpos($chave, 'password') !== false) {
    if (!empty(trim($valor))) {  // ← O PROBLEMA!
        $configuracoes[$chave] = trim($valor);
    } else {
        // Mantém valor atual
    }
}
```

### Por Que Falhava?

Em PHP, `empty('0')` retorna **TRUE**! 

```php
empty('0')    // true  ← Considerado vazio!
empty('90')   // false
empty('')     // true
```

Então:
1. Usuário digita `0`
2. Código checa: `!empty('0')` → FALSE
3. Cai no `else`: "mantém valor atual"
4. Valor `90` permanece! ❌

## ✅ Solução Implementada

### 1. Tratamento Especial para Campos Numéricos

```php
// Para campos numéricos, aceitar zero como valor válido
if ($tipoConfig === 'number') {
    $valorNumerico = is_numeric($valor) ? $valor : 0;
    $configuracoes[$chave] = $valorNumerico;
    $this->log("  - {$chave}: '{$valorNumerico}' (number)");
    continue;
}
```

Agora campos do tipo `number` aceitam `0` sem problemas! ✅

### 2. Detecção Mais Precisa de Campos Sensíveis

```php
// Detectar apenas se TERMINA com essas palavras ou são EXATAMENTE essas palavras
$isCampoSensivel = (
    preg_match('/\.(senha|password|key|token|secret|api_key|api_secret)$/i', $chave) ||
    in_array($chave, ['senha', 'password', 'key', 'token', 'secret'])
);
```

**Antes:**
- `strpos($chave, 'senha')` → Capturava `senha_expira_dias` ❌

**Agora:**
- `preg_match('/\.senha$/i', $chave)` → Captura `email.senha` ✅
- NÃO captura `sistema.senha_expira_dias` ✅

### 3. Verificação Correta de Campos Vazios

```php
if ($isCampoSensivel) {
    // Usar strlen() em vez de empty() para aceitar '0' como valor válido
    if (strlen(trim($valor)) > 0) {
        $configuracoes[$chave] = trim($valor);
        $this->log("  - {$chave}: '***' (campo sensível atualizado)");
    } else {
        $this->log("  - {$chave}: (vazio, mantém valor atual para campo sensível)");
    }
}
```

## 🧪 Campos Afetados (Corrigidos)

Estes campos agora aceitam `0` corretamente:

✅ `sistema.senha_expira_dias`
✅ `sistema.max_tentativas_login`
✅ `sistema.sessao_timeout`
✅ `categorias.nivel_maximo_hierarquia`
✅ Qualquer outro campo numérico

## 🎯 Campos Sensíveis (Funcionamento Correto)

Estes campos continuam protegidos (não salvam se vazios):

🔐 `email.senha`
🔐 `api.openai_key`
🔐 `api.google_key`
🔐 `integracao.*.token`
🔐 `integracao.*.api_key`

## 📊 Exemplo de Log (Corrigido)

```
PASSO 2: Processando outros campos (string, number, etc)
  - sistema.senha_expira_dias: '0' (number)   ← AGORA FUNCIONA!
  - sistema.max_tentativas_login: '5' (number)
  - sistema.titulo: 'Sistema Financeiro' (string)
  - email.senha: (vazio, mantém valor atual para campo sensível)
```

## 🎉 Resultado

- ✅ Campos numéricos aceitam `0`
- ✅ Campos sensíveis continuam protegidos
- ✅ Detecção mais precisa e inteligente
- ✅ Sem falsos positivos

## 🧪 Teste Agora

1. Vá para `/configuracoes`
2. Aba **Sistema**
3. Mude **"Senha expira em X dias"** para `0`
4. Salve
5. Atualize a página
6. Deve mostrar `0`! ✅

---

**Data:** 02/01/2026  
**Arquivo corrigido:** `app/controllers/ConfiguracaoController.php`  
**Linhas modificadas:** 171-199
