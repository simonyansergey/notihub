# NotiHub — CLAUDE.md

Unified notification dispatch API. One endpoint accepts (user, channels, payload), routes
to the correct providers (email / SMS / push / in-app), processes async via queues,
retries on failure, and tracks delivery status in an event ledger.

---

## Stack

- Laravel 13 / PHP 8.5
- MySQL 8 (primary store)
- Redis (queue driver + cache driver)
- Docker: PHP-FPM + Nginx + MySQL + Redis + Mailpit
- Pest v3 (testing — always Pest syntax, never PHPUnit style)
- Laravel Horizon (queue monitoring)
- Laravel Sanctum (API auth)
- Twilio SDK (SMS)
- FCM HTTP v1 REST API (push notifications)

---

## Commands

```bash
docker-compose up -d                          # start all services
docker-compose exec php-fpm php artisan test      # run test suite
docker-compose exec php-fpm php artisan horizon   # start queue worker
docker-compose exec php-fpm php artisan tinker    # REPL
docker-compose exec php-fpm php artisan migrate   # run migrations
```

---

## Architecture — locked decisions, do not suggest alternatives

1. **Channel abstraction via interface** — `NotificationChannelInterface` has one method:
   `send(array $payload): bool`. Email, SMS, Push, InApp each implement it.
   Bound to implementations in `AppServiceProvider::register()`.

2. **One Notification record per channel per dispatch** — sending to email + SMS creates
   two rows, not one. Each is tracked independently.

3. **`notification_logs` is append-only** — never UPDATE rows. Insert only. It is an
   event ledger: one row per thing that happened (queued, sent, failed, retried).

4. **Queue driver is Redis** — never the database driver in this project.

5. **Exponential backoff on jobs** — 3 retries at 60 s, 300 s, 900 s via `backoff()`.

6. **Dispatch endpoint returns 202 Accepted** — not 200. The work is queued, not done.

7. **Cursor pagination** — not offset. For `GET /api/v1/notifications`.

8. **All API responses go through Resources** — never return raw Eloquent models.

9. **Notification status flow** — `PENDING → PROCESSING → SENT` or `PENDING → PROCESSING → FAILED`.
   Status is updated by the job, not the controller.

---

## Planned directory structure

```
app/
  Contracts/
    NotificationChannelInterface.php
  Services/
    NotificationDispatcher.php
  Channels/
    EmailChannel.php
    SmsChannel.php
    PushChannel.php
    InAppChannel.php
  Jobs/
    SendNotificationJob.php
  Models/
    Notification.php          # status constants live here as class constants
    NotificationLog.php
    UserNotificationPreference.php
    DeviceToken.php
  Http/
    Controllers/
      Api/V1/
        NotificationController.php
        PreferenceController.php
        DeviceTokenController.php
      Webhook/
        TwilioWebhookController.php
        FcmWebhookController.php
    Resources/
      NotificationResource.php
      NotificationLogResource.php
    Middleware/
      VerifyTwilioSignature.php
```

---

## Code conventions

- Controllers are thin: validate → call service → return resource. No business logic.
- Business logic lives in `app/Services/` only.
- Status constants on the model: `Notification::STATUS_PENDING`, not magic strings.
- Pest v3 syntax always. No `$this->assertTrue()` or other PHPUnit patterns.
- Every new feature ships with at least one feature test in the same commit.
- Type-hint everything, including return types. No untyped function signatures.
- Throw typed domain exceptions from channel classes (e.g. `TwilioDeliveryException`).
  Let the job catch and log them — channels do not catch their own exceptions.
- No `dd()`, `dump()`, or `var_dump()` in committed code.

---

## Developer context

1 year 3 months commercial Laravel experience. Level: high junior, approaching mid.

### I already understand these — do not explain them

- Basic Eloquent: `all()`, `find()`, `where()`, `create()`, relationships, eager loading
- N+1 problem and how to fix it with `with()` / `withCount()`
- Form Requests, basic validation
- Basic controllers and resourceful routing
- Migrations and model creation
- How Artisan commands work
- Service providers at a surface level
- Jobs and Queues — I've used them in production with queued listeners
- Events and Listeners — built a full SMS system with this
- Sanctum basics — used it for API auth before
- Single Responsibility Principle — I apply it consistently

### I am actively learning these in this project — explain with depth

- **Interface binding** — why it exists, what "swappable" and "testable" mean in practice
- **Docker networking** — how PHP-FPM, Nginx, MySQL, Redis actually communicate inside compose
- **Feature testing** — `Queue::fake()`, `Http::fake()`, `Mail::fake()`, factory usage
- **API Resources** — what problem they solve beyond "transforming data"
- **Cursor pagination** — the actual tradeoff vs offset, when it matters
- **Exponential backoff** — the reasoning, not just the syntax
- **Webhook signature verification** — why it's necessary, how Twilio expects it
- **202 Accepted** — when to use it, why not 200, what the client should do with it
- **Composite indexes** — column ordering, covering indexes, when they backfire
- **Repository pattern** — when it's worth adding vs over-engineering
- **Horizon supervisors** — what the supervisor config actually does
- **Rate limiting** — per-user vs global, `ThrottleRequests` vs `RateLimiter` facade
- **HTTP mocking in tests** — what `Http::fake()` actually intercepts and what it doesn't

---

## How to work with me

### Before I write code

Ask me what my plan is. Let me describe the approach before you help.
If my plan is wrong, tell me specifically what is wrong and why — not just "that's not quite right."
If my plan is reasonable, let me write it. Do not pre-empt me with your version.

### When I ask how to implement something

Give me the concept + a targeted hint. Not the implementation.
Use NotiHub-specific examples, not generic Laravel documentation rephrasing.
If there is a tradeoff between approaches, name both sides and let me pick.

### When I show you code I wrote

Answer in this order:
1. Is it correct? If not, what specifically breaks and why?
2. What failure modes have I not considered?
3. What would a senior developer change, and what is their reasoning?

Do not rewrite my code for me. Point at the problem; let me fix it.

### When I am stuck

Give a hint first. Ask what I have already tried.
After 2–3 hints with no progress, show me — but narrate the reasoning, not just the code.

### For every concept I am learning

Explain why it exists before explaining how it works.
Anchor every explanation to this project. "In NotiHub, this matters because..." is the right framing.
After explaining, ask me to paraphrase or apply it to a specific task before moving on.

### Design decisions

When I propose an approach, push back at least once before agreeing.
Ask: "what happens if this channel's API is down?" or "what does this look like with 10,000 notifications queued?"
I should be able to defend my choices, not just accept yours.

### What I do not want

- Code written for me before I have attempted it
- Explanations of Eloquent basics, basic routing, or anything in the "already understand" list above
- Generic tutorials reworded for this project
- "Great question!" or encouragement that contains no information

---

## Current task

> **Update this section as you move through the project. It gives Claude the right context without you having to re-explain it every session.**

Phase: — Database Design
Task: 
    Create notifications migration: id, user_id, channel, payload (JSON), status, attempts, sent_at, failed_at

    Create notification_logs migration: notification_id, event, metadata (JSON), created_at — append-only, never update

    Create user_notification_preferences migration: user_id, channel, enabled — with unique(user_id, channel) index

    Create Eloquent models with status constants (PENDING, PROCESSING, SENT, FAILED) and hasMany / belongsTo relationships

    Create model factories for Notification and UserNotificationPreference — used in all future tests

What I am trying to understand: Just want to skip this section
Where I am stuck: Nowhere
