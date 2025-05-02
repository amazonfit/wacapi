
# 🌿 Wacapi Integration - WhatsApp Care & Marketing Plugin (WordPress Plugin)

## 🏢 Company
**Abana Homes** – We sell bonsai plants via Amazon and our website. We want to nurture our customers post-purchase through automated WhatsApp care guides and marketing messages.

---

## 🧩 Objective

Create a **WordPress plugin** that:
- Registers customers after purchase (manual input for now)
- Stores their plant details, phone number (WhatsApp), and email
- Sends automated and manual WhatsApp campaigns via **WhatsApp Cloud API**
- Allows inbox functionality for two-way messaging
- Enables campaign scheduling logic
- Logs all sent and received messages
- Allows creation of WhatsApp message templates from the admin panel
- Redirects customers post-registration to a **personalized plant care page**

---

## 🛠️ Plugin Functional Modules

### 1. Registration Form Module

- Accessible via page or shortcode (e.g. `/register-plant`)
- Form Fields:
  - Name
  - Email
  - Phone (WhatsApp opt-in checkbox)
  - Plant Name (dropdown or text)
  - Purchase Platform (Amazon / Website)
  - Order ID (optional)
- Saves data in `wp_wacapi_registrations`
- **After submission:** Redirect user to a dynamic care page for their plant with name-based guidance (e.g. `/plant-care/ficus`)

---

### 2. Admin Dashboard

- Accessible from WP Admin
- Features:
  - View and filter registered users
  - Manual campaign sender
  - Export user data
  - View sent/received logs
  - Create/manage message templates

---

### 3. WhatsApp Cloud API Integration

- Auto + Manual messaging support
- Admin setting for token & sender number
- Message Types:
  - Scheduled Care Campaign
  - Scheduled Cross-sell Campaign
  - Manual Campaigns
  - Replies to user messages
- Template message + media support (PDFs, Images)
- Create templates from plugin dashboard

---

### 4. Campaign Engine

#### a. Care Campaign
- Trigger: Form registration
- Messages at Day 0, 7, 14, 21, 28
- Each message links to personalized plant care content

#### b. Cross-sell Campaign
- Trigger: 15 days after registration
- Message offers related products
- Optional manual repeat purchase tracking

#### c. Manual Campaign
- Filter users
- Send message instantly or schedule

---

### 5. Inbox + Message Logging

#### Inbox
- Receives via webhook (not polling)
- Stores messages in `wp_wacapi_inbox_messages`
- Match users by phone
- Admin can reply from inbox interface

#### Message Logs
- Tracks all outgoing messages
- Stores status, timestamps, templates in `wp_wacapi_message_log`

---

### 6. Database Tables Overview

- `wp_wacapi_registrations` – User and product info
- `wp_wacapi_campaigns` – Campaign metadata
- `wp_wacapi_message_queue` – Queue of scheduled messages
- `wp_wacapi_message_log` – Outgoing messages log
- `wp_wacapi_inbox_messages` – Incoming message log
- `wp_wacapi_templates` – WhatsApp message templates

---

### 7. Extra Notes

- Templates can be created from plugin dashboard via WhatsApp Cloud API
- Use `wp_cron` for scheduled campaigns
- Token & API settings managed via plugin settings
- Admin UI should be clean with tabs for:
  - Registrations
  - Campaigns
  - Inbox
  - Message Logs
  - Templates
  - Settings

---

### 🔁 Post-Registration Redirect

- After form submission, redirect to a dynamic care page
- Page shows care details based on selected plant name
- Care content can be updated from the admin panel or stored as reusable post types
