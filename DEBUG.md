# Guia de Debug - Erro 500

## Passos para identificar o erro:

### 1. Acesse o script de teste
Acesse no navegador:
```
http://seudominio.com/test-connection.php
```

Este script vai verificar:
- Se o arquivo `.env` existe e está correto
- Se as variáveis de ambiente estão carregadas
- Se a conexão com o banco funciona
- Se os arquivos principais existem
- Se as permissões estão corretas

### 2. Verifique os logs
Os erros estão sendo salvos em:
- `storage/logs/error.log` - Erros da aplicação
- `storage/logs/php-errors.log` - Erros do PHP (se APP_DEBUG=false)

### 3. Verifique o arquivo .env
Certifique-se de que o arquivo `.env` na raiz do projeto contém:
```env
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=financprivus_financeiro
DB_USERNAME=financprivus_financeiro
DB_PASSWORD=@Ative199
APP_ENV=production
APP_DEBUG=true
```

### 4. Problemas comuns:

#### Erro de conexão com banco:
- Verifique se o usuário tem permissão para acessar o banco
- Verifique se o banco de dados existe
- Verifique se o host está correto (pode ser necessário usar IP ao invés de localhost)

#### Erro de permissões:
- Certifique-se que `storage/logs` tem permissão de escrita (chmod 755)
- Certifique-se que `storage/cache` tem permissão de escrita

#### Erro de caminhos:
- Verifique se o `.htaccess` está funcionando
- Verifique se o servidor está apontando para a pasta `public/`

### 5. Após identificar o erro:
1. Corrija o problema
2. Desative o debug: `APP_DEBUG=false` no `.env`
3. Remova o arquivo `test-connection.php` por segurança

