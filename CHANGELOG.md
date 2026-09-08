# Changelog

All notable changes to this project will be documented in this file.

## 1.0.0 - 2026-09-08

First release.

- Customers: list, get, create, update, delete, find by email
- Subscriptions: list, get, create, update, cancel (now or at period end), resume, list for a customer
- Products and prices: list, get, create, update, archive (products also delete)
- Checkout: create a session, get, list, expire, line items
- Billing portal: create a customer portal session
- Invoices: list, get, list for a customer, pay, send, void
- Payment intents: list, get, create, update, confirm, capture, cancel
- Events: list, get, filter by type
- Webhooks: Stripe-Signature verification (HMAC-SHA256, replay window, rotation-safe)
- StripeException carrying Stripe's error body, code, type and param

## [Unreleased]
