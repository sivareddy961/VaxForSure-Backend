# 📧 Quick Email Setup - Send OTP to Real Email

## ✅ Step-by-Step Setup (5 Minutes)

### Step 1: Get Gmail App Password

1. Go to: **https://myaccount.google.com/apppasswords**
2. If prompted, enable **2-Step Verification** first
3. Select:
   - **App:** Mail
   - **Device:** Other (Custom name)
   - **Name:** VaxForSure
4. Click **"Generate"**
5. **Copy the 16-character password** (looks like: `abcd efgh ijkl mnop`)

### Step 2: Update config.php

1. Open: `C:\xampp\htdocs\vaxforsure\config.php`
2. Find the `sendEmail()` function (around line 139)
3. Update these 3 lines:

```php
$smtp_username = 'omkarvinays3104@gmail.com';  // Your Gmail
$smtp_password = 'your-16-char-app-password'; // Paste the app password (remove spaces)
$smtp_from_email = 'omkarvinays3104@gmail.com'; // Same as username
```

**Example:**
```php
$smtp_username = 'omkarvinays3104@gmail.com';
$smtp_password = 'abcdefghijklmnop';  // Your app password (no spaces)
$smtp_from_email = 'omkarvinays3104@gmail.com';
```

4. **Save the file**

### Step 3: Test Email

1. Open: `http://localhost/vaxforsure/CONFIGURE_EMAIL.php`
2. Enter your email: `omkarvinays3104@gmail.com`
3. Click **"Send Test Email"**
4. **Check your inbox!** (also check spam folder)

### Step 4: Test from App

1. Open your Android app
2. Click "Forgot Password"
3. Enter: `omkarvinays3104@gmail.com`
4. Click "Send Verification Code"
5. **Check your email inbox for the OTP code!**

---

## ✅ That's It!

Once configured, OTP codes will be sent to real email addresses automatically!

**No more showing OTP in the app** - it's secure and professional! 🎉

---

## 🔧 Troubleshooting

### "Email not received"
- ✅ Check spam/junk folder
- ✅ Verify App Password is correct (16 characters, no spaces)
- ✅ Make sure 2-Step Verification is enabled
- ✅ Try test email first via CONFIGURE_EMAIL.php

### "SMTP Authentication failed"
- ✅ Use App Password, NOT your regular Gmail password
- ✅ Remove spaces from app password
- ✅ Make sure username is full email address

### "Connection timeout"
- ✅ Check firewall allows port 587
- ✅ Verify internet connection
- ✅ Try port 465 with SSL (change in config.php)

---

## 📝 Important Notes

- **Never use your regular Gmail password** - Always use App Password
- **App Password format:** 16 characters (remove spaces when pasting)
- **OTP is NOT shown in app** - Only sent via email (secure!)
- **Check spam folder** if email doesn't arrive immediately



