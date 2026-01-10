# Beauty SaaS API

## Autenticação

A API utiliza token gerado na tabela `beauty_sessions`.

**Gerar token**

`POST /wp-json/beauty/v1/auth/token`

```json
{
  "email": "user@example.com",
  "password": "senha"
}
```

Resposta:

```json
{
  "token": "string",
  "expires_at": "YYYY-MM-DD HH:MM:SS"
}
```

**Envie o token**

Use um destes headers:

- `Authorization: Bearer <token>`
- `X-Beauty-Token: <token>`

## Versionamento

As versões disponíveis são:

- `/wp-json/beauty/v1/...`
- `/wp-json/beauty/v2/...`

## Contratos (v1 e v2)

Todos os endpoints são filtrados por `company_id` automaticamente com base no token.

### Agendamentos

`GET /wp-json/beauty/v1/appointments`

Retorna colunas de `beauty_appointments`:

- `id`, `company_id`, `client_id`, `professional_id`, `service_id`, `start_time`, `end_time`, `status`, `reminder_sent`, `followup_sent`, `created_at`

### Clientes

`GET /wp-json/beauty/v1/clients`

Retorna colunas de `beauty_clients`:

- `id`, `company_id`, `name`, `phone`, `birthday`, `created_at`

### Profissionais

`GET /wp-json/beauty/v1/professionals`

Retorna colunas de `beauty_professionals`:

- `id`, `company_id`, `user_id`, `name`, `phone`, `commission`, `active`, `created_at`

### Serviços

`GET /wp-json/beauty/v1/services`

Retorna colunas de `beauty_services`:

- `id`, `company_id`, `name`, `duration`, `price`, `followup_enabled`, `followup_delay_value`, `followup_delay_unit`, `active`, `created_at`

### Produtos

`GET /wp-json/beauty/v1/products`

Retorna colunas de `beauty_products`:

- `id`, `company_id`, `name`, `price`, `stock`, `active`, `created_at`

### Financeiro

`GET /wp-json/beauty/v1/financial`

Retorna colunas de `beauty_financial`:

- `id`, `company_id`, `appointment_id`, `professional_id`, `amount`, `payment_method`, `created_at`

### Automações

`GET /wp-json/beauty/v1/automations`

Retorna colunas de `beauty_automations`:

- `id`, `company_id`, `event`, `message_id`, `delay_days`, `active`, `created_at`

> Para `v2`, utilize o mesmo contrato mudando o prefixo para `/wp-json/beauty/v2/...`.
