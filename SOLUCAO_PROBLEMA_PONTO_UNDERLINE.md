# 🎯 PROBLEMA RESOLVIDO: Pontos vs Underscores

## 🐛 O Problema

O log mostrava claramente:

```
Dados POST completos:
  - categorias_codigo_auto_gerado = true   ← UNDERLINE
```

Mas o sistema esperava:

```
categorias.codigo_auto_gerado   ← PONTO
```

E por isso estava sendo **IGNORADO**:

```
- categorias_codigo_auto_gerado: IGNORADO (não existe no grupo)
```

## 🔍 Causa Raiz

**PHP converte automaticamente pontos (`.`) em underscores (`_`) nos nomes de campos POST/GET!**

Isso é um comportamento **documentado** do PHP:
> https://www.php.net/manual/en/language.variables.external.php
> 
> "Dots and spaces in variable names are converted to underscores. 
> For example `<input name="a.b" />` becomes `$_REQUEST["a_b"]`"

### Fluxo do Problema

1. **HTML envia:** `categorias.codigo_auto_gerado=true`
2. **PHP recebe:** `categorias_codigo_auto_gerado=true` (conversão automática)
3. **Controller procura:** `categorias.codigo_auto_gerado` (não encontra!)
4. **Resultado:** Campo ignorado ❌

## ✅ Solução Implementada

Adicionada conversão automática no `ConfiguracaoController`:

```php
// CORREÇÃO: Reverter conversão automática do PHP
$dataCorrigido = [];
$prefixo = $grupo . '_';
$prefixoComPonto = $grupo . '.';

foreach ($data as $key => $value) {
    // Se a chave começa com "grupo_", converter para "grupo."
    if (strpos($key, $prefixo) === 0) {
        $novaChave = str_replace($prefixo, $prefixoComPonto, $key);
        $dataCorrigido[$novaChave] = $value;
        $this->log("  - {$key} → {$novaChave}");
    } else {
        $dataCorrigido[$key] = $value;
    }
}
```

### Como Funciona

1. **Recebe:** `categorias_codigo_auto_gerado`
2. **Detecta:** Começa com `categorias_`
3. **Converte:** Para `categorias.codigo_auto_gerado`
4. **Processa:** Normalmente ✅

## 🧪 Resultado Esperado

Agora o log deve mostrar:

```
Dados POST completos (ANTES da conversão):
  - grupo = categorias
  - categorias_codigo_auto_gerado = true

Convertendo underscores de volta para pontos...
  - categorias_codigo_auto_gerado → categorias.codigo_auto_gerado

Dados POST completos (DEPOIS da conversão):
  - categorias.codigo_auto_gerado = true

PASSO 1: Processando checkboxes (boolean)
  - categorias.codigo_auto_gerado: TRUE (marcado)   ← AGORA FUNCIONA!
```

## 🎯 Teste Agora

1. Vá para `/configuracoes`
2. Clique na aba **Categorias**
3. **Marque** o checkbox "Gerar código automaticamente"
4. Clique em **"Salvar Configurações"**
5. Clique em **"Ver Logs de Debug"**
6. Veja a conversão acontecendo!

## 📊 Por Que Isso Acontece?

Este é um comportamento **legacy** do PHP para garantir que nomes de variáveis sejam válidos em PHP:

- PHP não permite pontos (`.`) em nomes de variáveis: `$a.b` é inválido
- Por isso, converte automaticamente para `$a_b`
- Isso afeta `$_POST`, `$_GET`, `$_REQUEST`, `$_COOKIE`

### Outros Caracteres Afetados

PHP também converte:
- **Espaços** → `_` (underline)
- **Pontos** → `_` (underline)
- **Colchetes iniciais** → removidos

Exemplo:
```html
<input name="nome completo" />      → $_POST["nome_completo"]
<input name="email.address" />      → $_POST["email_address"]
<input name="[array]item" />        → $_POST["array_item"]
```

## 🎉 Status

✅ **Problema identificado**  
✅ **Solução implementada**  
✅ **Logs detalhados adicionados**  
✅ **Pronto para testar**

---

**Data:** 02/01/2026  
**Arquivo corrigido:** `app/controllers/ConfiguracaoController.php`  
**Linhas modificadas:** Após linha onde `$data['grupo']` é removido
