# Configuração do cPanel

## Método Atual (Implementado)
O sistema está configurado com `index.php` na raiz que carrega `public/index.php`.

**Funciona assim:**
- Document Root: `/home/financprivus/public_html`
- Entry Point: `/home/financprivus/public_html/index.php`
- Sistema real: `/home/financprivus/public_html/public/index.php`

## Método Alternativo (Recomendado para melhor segurança)

Se preferir, você pode configurar o Document Root no cPanel para apontar diretamente para a pasta `public/`:

### Passo a passo no cPanel:

1. Acesse o **cPanel**
2. Vá em **Domínios** (ou **Domains**)
3. Clique no domínio `financeiro.privus.com.br`
4. Edite o **Document Root**
5. Altere de `/home/financprivus/public_html` para `/home/financprivus/public_html/public`
6. Salve as alterações

### Vantagens:
- ✅ Melhor segurança (pastas do sistema ficam fora do Document Root)
- ✅ Não precisa do `index.php` na raiz
- ✅ Estrutura mais limpa

### Após alterar:
- Remova o arquivo `index.php` da raiz (não será mais necessário)
- O `.htaccess` em `public/` cuidará das rotas

## Configuração Atual (Funcionando)

A configuração atual funciona perfeitamente e é segura. Use o método alternativo apenas se preferir uma estrutura ainda mais limpa.

