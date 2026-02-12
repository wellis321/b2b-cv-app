# Subscription & Trial System (Resume.co-style)

This app uses a **simple 3-plan model** aligned with [Resume.co](https://resume.co/pricing):

1. **Basic access (Free)** — CV builder, templates, limited job tracking & AI. PDF export.
2. **7-day unlimited access** — £1.95. After 7 days, renews to £22/month. Cancel anytime.
3. **3-month unlimited access** — £27.88 one-time. Best value. Save 66%.

Pro Monthly, Pro Annual, and Lifetime exist in the backend for existing users and for trial renewals, but are **not shown** in marketing/pricing.

## How it works

1. **New users** register and start on the **free plan**.
2. **7-day trial**: User pays £1.95 via Stripe. We set `plan` = `pro_trial_7day`, `subscription_status` = `trialing`, `subscription_current_period_end` = now + 7 days. No Stripe subscription.
3. **When the 7 days end**: User is downgraded to free. They can subscribe to Pro Monthly (£22/month) to continue.
4. **3-month access**: One-time payment, `plan` = `pro_3month`, `period_end` = now + 90 days.

## Implementation

- **Marketing plans** (`getMarketingPlanIds()`): `free`, `pro_trial_7day`, `pro_3month` — only these 3 are shown on home, pricing, and subscription pages.
- **Full plans** (`getSubscriptionPlansConfig()`): All plans for backend (existing users, trial renewals).

### Environment

```
STRIPE_PRICE_PRO_TRIAL_7DAY=price_xxx   # £1.95 one-time
STRIPE_PRICE_PRO_3MONTH=price_xxx      # £27.88 one-time
STRIPE_PRICE_PRO_MONTHLY=price_xxx      # £22/month (for trial renewals)
```
