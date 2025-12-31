# 🔗 Guia de Configuração de Webhooks WooCommerce

## 📋 Visão Geral

Os webhooks permitem que o WooCommerce envie atualizações em tempo real para o sistema financeiro, eliminando a necessidade de sincronizações agendadas para eventos específicos.

## 🚀 Como Configurar

### 1. Criar Integração no Sistema

1. Acesse **Integrações** → **Nova Integração**
2. Selecione **WooCommerce**
3. Preencha os dados de conexão:
   - URL do Site
   - Consumer Key
   - Consumer Secret
4. (Opcional) Gere e anote um **Webhook Secret** (chave secreta)
5. Salve a integração
6. **Copie a URL do Webhook** exibida (ex: `https://seusite.com/webhook/woocommerce/123`)

### 2. Configurar no WooCommerce

1. Acesse o painel admin do WooCommerce
2. Vá em **WooCommerce** → **Configurações** → **Avançado** → **Webhooks**
3. Clique em **Adicionar Webhook** para cada evento que deseja monitorar

#### Eventos Suportados

| Evento | Topic | Descrição |
|--------|-------|-----------|
| **Produto Criado** | `product.created` | Disparado quando um novo produto é criado |
| **Produto Atualizado** | `product.updated` | Disparado quando um produto é editado |
| **Produto Excluído** | `product.deleted` | Disparado quando um produto é removido |
| **Pedido Criado** | `order.created` | Disparado quando um novo pedido é criado |
| **Pedido Atualizado** | `order.updated` | Disparado quando um pedido muda de status |
| **Pedido Excluído** | `order.deleted` | Disparado quando um pedido é removido |

#### Configuração de Cada Webhook

Para cada webhook criado no WooCommerce:

1. **Nome**: Escolha um nome descritivo (ex: "Sincronizar Produtos - Sistema Financeiro")
2. **Status**: Ativo
3. **Topic**: Selecione o evento (ex: `Product created`)
4. **Delivery URL**: Cole a URL do webhook do sistema financeiro
5. **Secret**: Cole o Webhook Secret que você gerou (se houver)
6. **API Version**: WooCommerce 3.x.x ou superior
7. Clique em **Salvar Webhook**

### 3. Testar Webhooks

#### Teste Manual

1. No WooCommerce, abra o webhook criado
2. Role até o final da página
3. Clique em **Entregar novamente** em um log existente OU
4. Crie/edite um produto ou pedido para disparar o evento

#### Verificar Logs

1. Acesse **Integrações** → Selecione sua integração WooCommerce
2. Role até a seção **Logs de Sincronização**
3. Verifique se aparecem logs com mensagem tipo "Webhook processado: product.created"

## 🔐 Segurança

### Webhook Secret (Recomendado)

O Webhook Secret é usado para validar que as requisições realmente vêm do WooCommerce:

1. Gere uma chave aleatória forte (ex: `wc_3x4mpl3_s3cr3t_k3y_2024`)
2. Configure no sistema financeiro (campo "Webhook Secret")
3. Configure no WooCommerce (campo "Secret" ao criar webhook)

**Importante**: Use a mesma chave em ambos os sistemas!

### Verificação de Assinatura

O sistema valida automaticamente a assinatura do webhook usando:
```
X-WC-Webhook-Signature = base64_encode(hash_hmac('sha256', payload, secret))
```

Se a assinatura não corresponder, o webhook é rejeitado com erro 401.

## 📊 Monitoramento

### Logs no WooCommerce

- **WooCommerce** → **Status** → **Logs**
- Procure por logs com prefixo `wc-webhook-`
- Status 200 = Sucesso
- Status 4xx/5xx = Erro

### Logs no Sistema Financeiro

- **Integrações** → Ver Integração → **Logs de Sincronização**
- ✓ Verde = Processado com sucesso
- ✗ Vermelho = Erro no processamento
- ⚠️ Amarelo = Processado com avisos

## 🔧 Troubleshooting

### Webhook não dispara

1. Verifique se o webhook está **Ativo** no WooCommerce
2. Confirme que a **Delivery URL** está correta
3. Teste a URL manualmente com uma ferramenta como Postman
4. Verifique se o firewall não está bloqueando requisições do WooCommerce

### Erro 401 (Não Autorizado)

- O Webhook Secret não corresponde
- Verifique se a chave é exatamente a mesma nos dois sistemas
- Certifique-se de não ter espaços extras no início/fim da chave

### Erro 404 (Não Encontrado)

- A URL do webhook está incorreta
- Verifique o ID da integração na URL
- Confirme que a rota está configurada corretamente

### Webhook não sincroniza dados

1. Verifique os logs no sistema financeiro
2. Confirme que os campos obrigatórios estão presentes no payload
3. Verifique se há erros de validação nos logs
4. Teste com sincronização manual para comparar

## ⚙️ Configurações Avançadas

### Retentar em caso de falha

No WooCommerce, você pode configurar:
- **Max Delivery Attempts**: Número de tentativas (padrão: 5)
- **Pending Delivery**: Tempo entre tentativas

### Múltiplos Webhooks

Você pode criar múltiplos webhooks para a mesma integração:
- Um para produtos
- Um para pedidos
- Cada um com sua própria configuração

### Filtragem de Eventos

Para filtrar quais produtos/pedidos são enviados, use plugins do WooCommerce ou personalize via código:

```php
add_filter('woocommerce_webhook_should_deliver', function($should_deliver, $webhook, $arg) {
    // Exemplo: Enviar apenas produtos publicados
    if ($webhook->get_topic() === 'product.created') {
        $product = wc_get_product($arg);
        return $product && $product->get_status() === 'publish';
    }
    return $should_deliver;
}, 10, 3);
```

## 📚 Recursos Adicionais

- [Documentação Oficial WooCommerce Webhooks](https://woocommerce.github.io/woocommerce-rest-api-docs/#webhooks)
- [Testes com RequestBin](https://requestbin.com/)
- [Validador de Webhook](https://webhook.site/)

## 💡 Dicas

1. **Comece pequeno**: Configure apenas um webhook por vez para facilitar troubleshooting
2. **Monitore regularmente**: Verifique os logs semanalmente
3. **Use Secret**: Sempre configure Webhook Secret em produção
4. **Teste antes**: Valide em ambiente de testes antes de configurar em produção
5. **Backup de sincronização**: Mantenha sincronização agendada como backup dos webhooks

---

**Última atualização**: 31/12/2025
