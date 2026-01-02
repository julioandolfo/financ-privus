# 🐛 Guia de Debug - Sistema de Configurações

## 🎯 Sistema de Logs Implementado

Agora o sistema tem logs **detalhados** que mostram **exatamente** o que acontece em cada salvamento.

## 📍 Onde Estão os Logs

Os logs ficam em: `storage/logs/configuracoes.log`

## 🖥️ Como Usar (Interface Web)

### 1. Acesse a Página de Configurações
```
http://seu-dominio/configuracoes
```

### 2. Veja o Botão "Ver Logs de Debug"
- Está no canto superior direito
- Botão roxo com ícone de documento

### 3. Faça o Teste
1. Vá para qualquer aba (ex: **Categorias**)
2. **Marque/desmarque** checkboxes
3. Clique em "**Salvar Configurações**"
4. Veja a mensagem de sucesso
5. Clique no botão **"Ver Logs de Debug"**

### 4. Analise os Logs
Os logs mostram:
- ✅ Dados recebidos do formulário
- ✅ Como cada checkbox foi processado
- ✅ Valores enviados para o banco
- ✅ Verificação se foram salvos corretamente

## 📊 O Que Procurar nos Logs

### Exemplo de Log Normal (Sucesso)

```
========================================
NOVA REQUISIÇÃO DE SALVAMENTO
========================================
Grupo recebido: categorias
Método HTTP: POST
URI: /configuracoes/salvar
IP: 127.0.0.1
Dados POST completos:
  - grupo = categorias
  - categorias.codigo_auto_gerado = true
Configurações do grupo 'categorias': 3 itens
PASSO 1: Processando checkboxes (boolean)
  - categorias.codigo_auto_gerado: TRUE (marcado)
  - categorias.codigo_obrigatorio: FALSE (desmarcado)
  - categorias.hierarquia_habilitada: FALSE (desmarcado)
PASSO 2: Processando outros campos (string, number, etc)
  (nenhum campo não-boolean neste grupo)
PASSO 3: Processando uploads de arquivos
  (nenhum arquivo para upload)
RESUMO: Total de 3 configurações para salvar
  → categorias.codigo_auto_gerado = TRUE
  → categorias.codigo_obrigatorio = FALSE
  → categorias.hierarquia_habilitada = FALSE
SALVANDO no banco de dados...
SUCESSO: Configurações salvas no banco!
Cache limpo.
Verificando valores salvos no banco:
  [OK] categorias.codigo_auto_gerado: esperado=TRUE, atual=TRUE
  [OK] categorias.codigo_obrigatorio: esperado=FALSE, atual=FALSE
  [OK] categorias.hierarquia_habilitada: esperado=FALSE, atual=FALSE
========================================
```

### 🔍 Sinais de Problema

#### ❌ Problema 1: Dados não chegam
```
Dados POST completos:
  - grupo = categorias
  (nenhum outro campo)
```
**Causa:** JavaScript não está enviando os dados ou formulário incorreto

#### ❌ Problema 2: Valores divergem
```
Verificando valores salvos no banco:
  [ERRO] categorias.codigo_auto_gerado: esperado=TRUE, atual=FALSE
```
**Causa:** Problema no salvamento do banco ou conversão de tipos

#### ❌ Problema 3: Exceção
```
EXCEÇÃO: SQLSTATE[42S02]: Base table or view not found
Stack trace: ...
```
**Causa:** Tabela não existe ou erro de banco de dados

## 🎮 Funcionalidades da Página de Logs

### Botões Disponíveis

1. **← Voltar** - Volta para configurações
2. **🗑️ Limpar Logs** - Apaga todo o log (requer confirmação)
3. **🔄 Atualizar** - Recarrega a página
4. **Auto-atualizar** - Checkbox que atualiza a cada 5 segundos

### Auto-Refresh

Marque o checkbox "Auto-atualizar a cada 5 segundos" para:
- Ver logs em tempo real
- Útil quando outra pessoa está testando
- Útil para ver requests assíncronos

## 📝 Como Reportar um Problema

Se ainda não funcionar, me envie:

### 1. Copie o LOG completo
```bash
# Windows PowerShell
Get-Content storage\logs\configuracoes.log

# Linux/Mac
cat storage/logs/configuracoes.log
```

### 2. Ou via interface web
1. Acesse `/configuracoes/logs`
2. Selecione todo o texto (Ctrl+A)
3. Copie (Ctrl+C)
4. Cole em um arquivo .txt e me envie

### 3. Informações Adicionais

Me diga também:
- Qual aba você tentou salvar?
- Quais checkboxes você marcou?
- Qual navegador você está usando?
- Tem algum erro no Console do navegador (F12)?

## 🔧 Atalhos para Debug

### Ver últimas 50 linhas do log
```bash
# PowerShell
Get-Content storage\logs\configuracoes.log -Tail 50

# Linux/Mac
tail -50 storage/logs/configuracoes.log
```

### Monitorar log em tempo real
```bash
# PowerShell
Get-Content storage\logs\configuracoes.log -Wait -Tail 10

# Linux/Mac
tail -f storage/logs/configuracoes.log
```

### Limpar log via terminal
```bash
# PowerShell
Clear-Content storage\logs\configuracoes.log

# Linux/Mac
> storage/logs/configuracoes.log
```

## 🎯 Próximos Passos

1. ✅ **Acesse** `/configuracoes`
2. ✅ **Teste** marcar/desmarcar em qualquer aba
3. ✅ **Salve**
4. ✅ **Clique em "Ver Logs de Debug"**
5. ✅ **Analise** o que aconteceu
6. ✅ **Me envie** o log se houver problema

## 💡 Dicas

- Os logs são **cumulativos** (cada salvamento adiciona ao arquivo)
- Use **"Limpar Logs"** periodicamente para não ficar muito grande
- Os logs mostram **tudo**: cada campo, cada passo, cada verificação
- Procure por palavras-chave:
  - `[OK]` = sucesso
  - `[ERRO]` = problema
  - `EXCEÇÃO` = erro crítico
  - `TRUE`/`FALSE` = valores de checkboxes

---

**Status:** Sistema de logs implementado e funcionando ✅  
**Última atualização:** 02/01/2026
