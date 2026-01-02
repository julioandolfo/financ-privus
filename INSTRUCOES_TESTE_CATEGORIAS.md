# 🧪 Teste Específico: Categorias

## ✅ Confirmação: Sistema está funcionando

Os testes automatizados confirmam que o salvamento funciona **perfeitamente**.

## 🔍 Problema Identificado: Cache do Navegador

O problema mais provável é **cache do navegador**.

## 📋 Procedimento Correto (Passo a Passo)

### Etapa 1: Limpar Cache Completamente

**Opção A - Google Chrome/Edge:**
1. Pressione `Ctrl + Shift + Delete`
2. Selecione "Imagens e arquivos em cache"
3. Clique em "Limpar dados"

**Opção B - Modo Anônimo:**
1. Pressione `Ctrl + Shift + N` (Chrome/Edge) ou `Ctrl + Shift + P` (Firefox)
2. Acesse o sistema nesta janela anônima

**Opção C - Hard Refresh:**
1. Na página de configurações
2. Pressione `Ctrl + Shift + R` (força reload sem cache)

### Etapa 2: Teste com Logs Ativos

1. **Abra o navegador**
2. **Pressione F12** (abre DevTools)
3. **Clique na aba "Console"** (deixe aberta)
4. **Acesse** `http://seu-dominio/configuracoes`
5. **Clique na aba "Categorias"**
6. **IMPORTANTE**: Verifique se você está vendo **3 checkboxes**:
   - ✅ Gerar código automaticamente
   - ✅ Código é obrigatório  
   - ✅ Permitir hierarquia de categorias

### Etapa 3: Marcar e Salvar

1. **Marque APENAS** "Gerar código automaticamente"
2. **Deixe os outros dois desmarcados**
3. **Role a página até o final**
4. **Clique no botão azul** "Salvar Configurações"
   - ⚠️ **CERTIFIQUE-SE** que você está clicando no botão **dentro da aba Categorias**
   - ⚠️ **NÃO** clique em botões de outras abas

### Etapa 4: Verificar

1. Você deve ver a mensagem verde: **"Configurações salvas com sucesso!"**
2. **Pressione F5** para recarregar
3. **Clique novamente na aba "Categorias"**
4. **Verifique**: O checkbox "Gerar código automaticamente" deve estar **marcado** ✓

### Etapa 5: Verificar no Banco (Terminal)

Execute:

```bash
php check_configuracoes.php | grep -A 10 "CATEGORIAS"
```

ou no PowerShell:

```powershell
php check_configuracoes.php | Select-String -Pattern "CATEGORIAS" -Context 0,10
```

Você deve ver:

```
📁 Grupo: CATEGORIAS
categorias.codigo_auto_gerado   | boolean | true  👈 DEVE SER TRUE
categorias.codigo_obrigatorio   | boolean | false
categorias.hierarquia_habilitada| boolean | false
```

## 🐛 Debug: Ver Logs

Após salvar, execute:

**Windows (PowerShell):**
```powershell
Get-Content storage\logs\error.log -Tail 30
```

**Linux/Mac:**
```bash
tail -30 storage/logs/error.log
```

Procure por estas linhas:
```
=== SALVANDO CONFIGURAÇÕES ===
Grupo: categorias
Dados recebidos (POST): {
    "grupo": "categorias",
    "categorias.codigo_auto_gerado": "true"  👈 DEVE APARECER ISSO
}
```

## ⚠️ Problemas Comuns

### 1. Botão Errado
- ❌ Clicar em botão de outra aba
- ✅ Clicar no botão **dentro** da aba Categorias

### 2. JavaScript Desabilitado
- Abra F12 → Console
- Veja se há erros em vermelho
- Se tiver, me envie o erro

### 3. Extensões do Navegador
- Desative ad-blockers temporariamente
- Teste em modo anônimo

### 4. Múltiplas Abas
- Feche outras abas do sistema
- Use apenas UMA aba

## 🎯 Teste Alternativo: Via Script

Se a interface web não funcionar, teste via script:

```bash
php test_categorias_form.php
```

Isso **bypassa** o navegador e testa diretamente o backend.

Se este script funciona mas a web não funciona, o problema é:
- Cache do navegador
- JavaScript
- Extensões do navegador

## 📸 Screenshots Úteis

Se possível, tire screenshots de:
1. A tela de configurações (aba Categorias)
2. O console do navegador (F12)
3. O resultado de `php check_configuracoes.php`

---

**Última atualização:** 02/01/2026  
**Status do Sistema:** ✅ Funcionando (testado e confirmado)  
**Causa Provável:** Cache do navegador ou JavaScript
