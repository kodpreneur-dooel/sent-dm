# Sent DM Laravel package guidelines

- Use `SentDm\Client` from the installed SDK package. Some Sent documentation examples show `SentDM\Client`, but the
  Composer package currently autoloads `SentDm\Client`.
- Resolve the client through Laravel's container instead of manually constructing it in application code.
- Prefer constructor injection of `SentDm\Client` for services, controllers, jobs, and listeners.
- Use `Codepreneur\SentDm\Facades\SentDm` only when a facade is more ergonomic than dependency injection.
- Use Laravel notification channel `sms` as the default way to send user-facing SMS notifications. Existing app
  notifications can append `sms` to `via()` and define `toSms()`.
- Build SMS payloads with `Codepreneur\SentDm\Messages\SentDmSmsMessage`; use `text()` for default-template SMS and
  `forTemplate()` or `templateArray()` for explicit Sent DM templates.
- Use `Codepreneur\SentDm\Notifications\SmsNotification` for one-off text SMS sends.
- Notifiable models should expose `routeNotificationForSms()` unless a `SentDmSmsMessage` explicitly sets recipients
  with `to()`.
- Use `Codepreneur\SentDm\Concerns\InteractsWithSentDmSms` when notifications should include `sms` only if an SMS route
  exists and should use the configured SMS queue/backoff behavior.
- Configure credentials with `SENT_DM_API_KEY`; never hard-code API keys or webhook secrets.
- Configure simple text SMS with `SENT_DM_SMS_TEMPLATE_NAME`, `SENT_DM_SMS_TEMPLATE_PARAMETER`, and optionally
  `SENT_DM_SMS_TEMPLATE_ID`.
- Configure `SENT_DM_SMS_SANDBOX`, `SENT_DM_SMS_PROFILE_ID`, and `SENT_DM_SMS_QUEUE` when app behavior needs them.
- For webhooks, always verify `X-Webhook-ID`, `X-Webhook-Timestamp`, and `X-Webhook-Signature` against the raw request
  body before parsing JSON.
- Return a 2xx response quickly from webhook endpoints and push slow work to queued jobs.
- Use Sent sandbox mode for development and tests when exercising message sends.
- Do not add migrations, views, or routes to this package unless a feature explicitly requires Laravel app surface area.
