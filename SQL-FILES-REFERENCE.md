# SQL Files Reference - FreshTrack

## ✅ ALL FILES NOW UPDATED WITH WORKING PASSWORD HASH

All SQL files have been updated with the correct password hash that works with Laravel.

Password for all accounts: **password**

---

## 📁 SQL Files Available

### 1. COMPLETE-SETUP.sql ⭐ (RECOMMENDED)
**Use this for:** Complete fresh setup

**What it does:**
- Adds role column to users table
- Removes old demo accounts
- Creates 3 new accounts (owner, manager, cashier)
- Verifies everything is ready

**When to use:**
- First time setup
- Complete reset of demo accounts
- Clean installation

---

### 2. create_demo_users.sql
**Use this for:** Creating demo accounts from scratch

**What it does:**
- Same as COMPLETE-SETUP.sql
- Adds role column
- Creates all 3 accounts

**When to use:**
- Initial setup
- When you want to recreate all accounts

---

### 3. INSTANT-FIX.sql
**Use this for:** Quick password fix only

**What it does:**
- ONLY updates passwords for existing accounts
- Does NOT create accounts
- Does NOT add role column

**When to use:**
- Accounts already exist but passwords don't work
- Quick fix when login fails
- After importing database

---

### 4. FIX-PASSWORDS-NOW.sql
**Use this for:** Emergency password fix

**What it does:**
- Same as INSTANT-FIX.sql
- Updates passwords for all 3 accounts

**When to use:**
- Emergency fix when you can't login
- Quick password reset

---

## 🎯 Which File Should You Use?

### Scenario 1: Fresh Setup
Use: **COMPLETE-SETUP.sql**

### Scenario 2: Can't Login (accounts exist)
Use: **INSTANT-FIX.sql** or **FIX-PASSWORDS-NOW.sql**

### Scenario 3: Starting Over
Use: **COMPLETE-SETUP.sql**

### Scenario 4: Need to Share with Team
Use: **COMPLETE-SETUP.sql** (it has everything)

---

## 📋 How to Use Any SQL File

1. Open phpMyAdmin
2. Select database: `capstone_db`
3. Click "SQL" tab
4. Open the SQL file you need
5. Copy ALL the SQL
6. Paste in phpMyAdmin
7. Click "Go"

---

## 🔑 Demo Account Credentials

All accounts use the same password for testing:

| Email | Password | Role | Access Level |
|-------|----------|------|--------------|
| owner@FreshTrack.ph | password | Owner | Full access to everything |
| manager@FreshTrack.ph | password | Manager | POS, Sales, Inventory, Reports, Forecast, Spoilage, Analytics |
| cashier@FreshTrack.ph | password | Cashier | POS, Notifications, Settings only |

---

## ✅ What's Fixed

All SQL files now use the correct password hash:
```
$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
```

This is Laravel's standard test password hash that's guaranteed to work with the password "password".

The old hash that didn't work:
```
$2y$12$LQv3c1yycwMV2SdFxq8oRuSQqhbZ8ggQPJ0xOvAz8cVKZJmZz0RJu
```

---

## 🚀 Quick Start

For easiest setup, just run:
```sql
COMPLETE-SETUP.sql
```

Then login with:
- Email: owner@FreshTrack.ph
- Password: password

---

**All files are now ready to use!** 🎉
