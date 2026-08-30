# ERP SaaS public registration (ops)

Server-to-server flow: browser → `POST /erp-saas/register` (Laravel) → `POST /api/tenant/public/register` (Odoo) → welcome email + Telegram.

## Odoo — xem / copy Registration Key

**Company Tenant → Settings**

- Field **X-Registration-Key** (copy clipboard)
- Daily registration cap
- Welcome email From
- Button **Regenerate key** (nhớ cập nhật Laravel sau khi regenerate)

Hoặc System Parameters: `o4u_tenant.registration_api_key`.

## Laravel (`o4u.nvnhan0810.com`)

Set in `.env`:

```env
ODOO_TENANT_BASE_URL=https://<odoo-host>
ODOO_TENANT_REGISTRATION_KEY=<same as Odoo ir.config_parameter>
ODOO_TENANT_REGISTRATION_DAILY_CAP=50

TURNSTILE_SITE_KEY=
TURNSTILE_SECRET_KEY=
TURNSTILE_BYPASS=false

TELEGRAM_BOT_TOKEN=
TELEGRAM_REGISTRATION_CHAT_ID=
```

- Never set `TURNSTILE_BYPASS=true` in production.
- Never expose `ODOO_TENANT_REGISTRATION_KEY` to Vite/`VITE_*`.
- Rate limits (IP): 3/min, 10/hour, daily cap; email: 3/hour.

## Odoo (`o4u_company_tenant`)

System parameters (`Settings → Technical → Parameters`):

| Key | Purpose |
|---|---|
| `o4u_tenant.registration_api_key` | Shared secret (`X-Registration-Key`) |
| `o4u_tenant.registration_daily_cap` | Max domains with `registration_date=today` (default 50) |
| `o4u_tenant.registration_email_from` | From address for welcome mail |

On first call / post_init, missing API key is auto-generated. Copy it into Laravel `.env`.

After deploying router changes, restart Odoo (or reset FastAPI endpoint apps) so `/api/tenant/public/register` is mounted.

Welcome mail uses the **system** outgoing mail server (not per-tenant SMTP). Configure a global `ir.mail_server` and SPF/DKIM for the From domain.

## Edge / DDoS

Application throttles are not a substitute for L3/L4 protection. Put Cloudflare (or equivalent) in front of `o4u.nvnhan0810.com` with bot fight / rate limiting on `/erp-saas/register`. Do not publish the Odoo registration URL to the browser.

## Smoke test

```bash
# Laravel (with Turnstile bypass only on local)
curl -X POST https://o4u.nvnhan0810.com/erp-saas/register \
  -H 'Accept: application/json' -H 'Content-Type: application/json' \
  -d '{"company_name":"...","contact_name":"...","email":"...","phone":"..."}'

# Odoo direct (server only)
curl -X POST https://<odoo>/api/tenant/public/register \
  -H 'X-Registration-Key: <key>' -H 'Content-Type: application/json' \
  -d '{"company_name":"...","contact_name":"...","email":"...","phone":"..."}'
```
