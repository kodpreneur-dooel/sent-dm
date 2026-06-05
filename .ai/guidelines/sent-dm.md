# Sent DM Laravel package guidelines

- Use `SentDm\Client` from the installed SDK package. Some Sent documentation examples show `SentDM\Client`, but the Composer package currently autoloads `SentDm\Client`.
- Resolve the client through Laravel's container instead of manually constructing it in application code.
- Prefer constructor injection of `SentDm\Client` for services, controllers, jobs, and listeners.
- Use `Codepreneur\SentDm\Facades\SentDm` only when a facade is more ergonomic than dependency injection.
- Configure credentials with `SENT_DM_API_KEY`; never hard-code API keys or webhook secrets.
- For webhooks, always verify `X-Webhook-ID`, `X-Webhook-Timestamp`, and `X-Webhook-Signature` against the raw request body before parsing JSON.
- Return a 2xx response quickly from webhook endpoints and push slow work to queued jobs.
- Use Sent sandbox mode for development and tests when exercising message sends.
- Do not add migrations, views, or routes to this package unless a feature explicitly requires Laravel app surface area.
