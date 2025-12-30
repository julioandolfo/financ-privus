# 📦 Instalação no cPanel

## ⚠️ IMPORTANTE: Instalar Composer no Servidor

O sistema precisa do Composer para funcionar. Siga os passos abaixo:

---

## Opção 1: Via Terminal SSH (Recomendado)

### 1. Acesse o servidor via SSH

```bash
ssh financprivus@financeiro.privus.com.br
```

### 2. Navegue até a pasta do projeto

```bash
cd /home/financprivus/public_html
```

### 3. Instale as dependências do Composer

```bash
composer install --no-dev --optimize-autoloader
```

Se o comando `composer` não for encontrado, instale-o primeiro:

```bash
# Download do Composer
curl -sS https://getcomposer.org/installer | php

# Use php composer.phar ao invés de composer
php composer.phar install --no-dev --optimize-autoloader
```

---

## Opção 2: Via cPanel Terminal

1. Acesse o **cPanel**
2. Vá em **Terminal** (Advanced → Terminal)
3. Execute os comandos:

```bash
cd ~/public_html
composer install --no-dev --optimize-autoloader
```

---

## Opção 3: Upload Manual (Menos Recomendado)

Se não conseguir instalar o Composer no servidor:

1. **No seu computador local**, execute:
   ```bash
   composer install --no-dev --optimize-autoloader
   ```

2. **Faça upload da pasta `vendor`** para o servidor via FTP ou Gerenciador de Arquivos do cPanel
   - Pasta local: `C:\laragon\www\financeiro\vendor`
   - Destino no servidor: `/home/financprivus/public_html/vendor`

⚠️ **Atenção**: Esta opção pode causar problemas de compatibilidade se as versões do PHP forem diferentes.

---

## Verificação

Após instalar, acesse novamente:
```
https://financeiro.privus.com.br/test-app.php
```

Se tudo estiver OK, você verá:
- ✓ Autoloader carregado
- ✓ Database.php incluído
- ✓ Usuario instanciado

---

## Próximos Passos

Depois que o Composer estiver instalado:

1. Execute as migrations para criar/atualizar tabelas:
   ```bash
   cd ~/public_html
   php migrations/run.php
   ```

2. Crie o usuário administrador:
   ```bash
   php create-admin.php
   ```

3. Remova os arquivos de teste por segurança:
   ```bash
   rm public/test-db.php
   rm public/test-app.php
   rm public/test-connection.php
   rm public/test-autoloader.php
   rm public/info.php
   ```

4. Acesse o sistema:
   ```
   https://financeiro.privus.com.br/login
   ```

---

## 🔒 Segurança

Após a instalação, certifique-se de:
- [ ] Remover todos os arquivos de teste
- [ ] Configurar `APP_DEBUG=false` no `.env`
- [ ] Verificar permissões do `.env` (644 ou 600)
- [ ] Bloquear acesso ao `.git` (já configurado no `.htaccess`)


