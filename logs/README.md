# Pasta de Logs

Esta pasta contém os logs de operações do sistema.

## Tipos de Logs

### Logs de Produtos (`produtos_YYYY-MM-DD.log`)
- **create**: Criação de novos produtos
- **update**: Atualização de dados básicos de produtos
- **update_tributos**: Atualização de informações tributárias
- **delete**: Exclusão (soft delete) de produtos

## Formato dos Logs

Cada linha do log é um JSON com a seguinte estrutura:

```json
{
  "data_hora": "2026-02-08 10:30:00",
  "usuario_id": 1,
  "acao": "update",
  "tabela": "produtos",
  "registro_id": 123,
  "ip": "192.168.1.100",
  "user_agent": "Mozilla/5.0...",
  "dados": {
    "antes": {...},
    "depois": {...}
  }
}
```

## Retenção

Os logs são mantidos por padrão indefinidamente. Configure uma rotina de limpeza periódica se necessário.

## Permissões

Certifique-se de que esta pasta tenha permissões de escrita (0755).
