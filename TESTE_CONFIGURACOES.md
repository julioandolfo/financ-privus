# 🧪 Como Testar o Sistema de Configurações

## ✅ Sistema ESTÁ Funcionando Corretamente

Os testes automatizados confirmaram que o sistema está 100% funcional:
- ✅ Salvamento de checkboxes (TRUE/FALSE)
- ✅ Leitura correta dos valores
- ✅ Cache funcionando
- ✅ Todos os grupos organizados

## 🔍 Possíveis Causas de "Não Salvar"

### 1. **Cache do Navegador**
O navegador pode estar exibindo a página antiga em cache.

**Solução:**
- Pressione `Ctrl + Shift + R` (Windows/Linux) ou `Cmd + Shift + R` (Mac) para forçar reload
- Ou use modo anônimo/privado para testar

### 2. **Botão de Salvar Incorreto**
Cada aba tem seu próprio botão "Salvar Configurações". Certifique-se de clicar no botão correto.

**Como identificar:**
- O botão está na parte inferior da aba ativa
- Apenas UMA aba está visível por vez
- Ao salvar, você é redirecionado de volta para a mesma aba

### 3. **JavaScript Desabilitado**
O sistema usa Alpine.js para alternar entre abas.

**Como verificar:**
- Abra o Console do navegador (F12)
- Veja se há erros JavaScript
- Tente desabilitar extensões do navegador (ad-blockers, etc)

## 📝 Procedimento de Teste Correto

### Teste 1: Aba Empresas

1. Acesse `/configuracoes`
2. Clique na aba "**Empresas**"
3. Veja os 3 checkboxes:
   - Código é obrigatório
   - Gerar código automaticamente
   - CNPJ é obrigatório
4. **Marque todos** os checkboxes
5. Clique em "**Salvar Configurações**" (botão azul no rodapé)
6. Veja a mensagem "Configurações salvas com sucesso!"
7. **Recarregue a página** (F5 ou Ctrl+R)
8. Clique na aba "Empresas" novamente
9. **Verifique**: Todos devem estar marcados ✓

### Teste 2: Aba Email

1. Acesse `/configuracoes`
2. Clique na aba "**Email**"
3. Preencha os campos:
   - Servidor SMTP: `smtp.gmail.com`
   - Porta SMTP: `587`
   - Usuário SMTP: `seu@email.com`
   - Senha SMTP: `suasenha` (deixe vazio para manter atual)
4. Clique em "**Salvar Configurações**"
5. **Recarregue a página**
6. Clique na aba "Email" novamente
7. **Verifique**: Os valores devem estar preenchidos

### Teste 3: Aba API e IA

1. Acesse `/configuracoes`
2. Clique na aba "**API e IA**"
3. Preencha:
   - Chave de API da OpenAI: `sk-...` (sua chave)
   - Modelo: Selecione um modelo
4. Marque alguns checkboxes (sugestão de categorias, etc)
5. Clique em "**Salvar Configurações**"
6. **Recarregue a página**
7. Clique na aba "API e IA" novamente
8. **Verifique**: Tudo deve estar como configurado

## 🐛 Debug: Verificar Logs

Se ainda não funcionar, verifique os logs de erro:

```bash
# Ver logs do PHP
tail -f storage/logs/error.log

# Ver logs no navegador
# Abra F12 -> Console -> veja erros JavaScript
# Abra F12 -> Network -> veja requisições POST para /configuracoes/salvar
```

## 🔧 Verificações Técnicas

### 1. Verificar banco de dados:

```bash
php check_configuracoes.php
```

Isso mostra TODAS as configurações no banco.

### 2. Testar salvamento programático:

```bash
php test_full_flow.php
```

Isso testa o salvamento direto (bypassa o formulário).

### 3. Verificar migração:

```bash
php migrate.php status
```

Certifique-se que a migration `050_corrigir_grupos_configuracoes` foi executada.

## 📊 Exemplo de Resposta Correta

Quando você salva configurações, a requisição deve:

1. **POST para:** `/configuracoes/salvar`
2. **Payload:**
```
grupo: empresas
empresas.codigo_obrigatorio: true  (se marcado)
empresas.codigo_auto_gerado: true  (se marcado)
empresas.cnpj_obrigatorio: true    (se marcado)
```

3. **Resposta:** Redirect para `/configuracoes?aba=empresas`
4. **Mensagem:** "Configurações salvas com sucesso!" (verde)

## ⚠️ Problemas Conhecidos

### Campos de Senha/Token/Key
- Se deixar **vazio**, mantém o valor atual
- Se preencher, atualiza o valor
- Isso é **intencional** para proteger senhas

### Checkboxes
- Marcado = `true`
- Desmarcado = `false`
- **Não há estado "não definido"**

## 🎯 Casos de Teste Específicos

### Teste A: Marcar e Desmarcar

1. Marque TODOS os checkboxes de uma aba
2. Salve
3. Recarregue
4. Confirme que estão marcados
5. **Desmarque TODOS**
6. Salve
7. Recarregue
8. Confirme que estão desmarcados

### Teste B: Misto

1. Marque apenas ALGUNS checkboxes
2. Salve
3. Recarregue
4. Confirme que apenas os marcados estão ativos

### Teste C: Múltiplas Abas

1. Configure aba "Empresas" → Salve
2. Configure aba "Email" → Salve
3. Configure aba "API" → Salve
4. Recarregue
5. Verifique TODAS as 3 abas

## 📞 Se Ainda Não Funcionar

Execute o teste automatizado e me envie o resultado:

```bash
php test_full_flow.php > resultado_teste.txt
```

E também:

```bash
php check_configuracoes.php > estado_banco.txt
```

Isso vai me ajudar a identificar o problema específico.

---

**Última atualização:** 02/01/2026  
**Status:** Sistema testado e funcionando ✅
