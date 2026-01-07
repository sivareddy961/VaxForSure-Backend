# 📧 Email Setup Instructions for OTP Sending

## ✅ Quick Setup (Gmail - Recommended)

### Step 1: Get Gmail App Password

1. Go to: https://myaccount.google.com/
2. Click **"Security"** on the left
3. Enable **"2-Step Verification"** (if not already enabled)
4. Go back to Security → Scroll down to **"App passwords"**
5. Select app: **"Mail"** and device: **"Other (Custom name)"**
6. Enter name: **"VaxForSure"**
7. Click **"Generate"**
8. **Copy the 16-character password** (you'll need this)

### Step 2: Update config.php

1. Open: `C:\xampp\htdocs\vaxforsure\config.php`
2. Find the `sendEmail()` function
3. Update these lines:

```php
$smtp_username = 'your-email@gmail.com';  // Your Gmail address
$smtp_password = 'xxxx xxxx xxxx xxxx';   // The 16-character app password
$smtp_from_email = 'your-email@gmail.com'; // Same as username
$smtp_from_name = 'VaxForSure';
```

4. Save the file

### Step 3: Test Email

1. Open: `http://localhost/vaxforsure/CONFIGURE_EMAIL.php`
2. Enter your email address
3. Click "Send Test Email"
4. Check your inbox!

---

## 🔧 Alternative: Configure XAMPP Mail

### Step 1: Edit php.ini

1. Open: `C:\xampp\php\php.ini`
2. Find `[mail function]` section
3. Update:

```ini
[mail function]
SMTP = smtp.gmail.com
smtp_port = 587
sendmail_from = your-email@gmail.com
sendmail_path = "\"C:\xampp\sendmail\sendmail.exe\" -t"
```

4. Save and restart Apache

### Step 2: Edit sendmail.ini

1. Open: `C:\xampp\sendmail\sendmail.ini`
2. Update:

```ini
smtp_server=smtp.gmail.com
smtp_port=587
auth_username=your-email@gmail.com
auth_password=your-gmail-app-password
```

3. Save

### Step 3: Restart XAMPP

- Stop Apache
- Start Apache again

---

## ✅ Verification

After setup:

1. ✅ Test email sending via `CONFIGURE_EMAIL.php`
2. ✅ Check your inbox for test email
3. ✅ Test forgot password from app
4. ✅ Check email for OTP code

---

## 📝 Important Notes

- **Never use your regular Gmail password** - Always use App Password
- **App Password is 16 characters** (with spaces: xxxx xxxx xxxx xxxx)
- **Remove spaces** when entering in config.php
- **OTP is NOT shown in app anymore** - Only sent via email
- **Check spam folder** if email doesn't arrive

---

## 🆘 Troubleshooting

### "Email not received"
- Check spam/junk folder
- Verify App Password is correct
- Check Gmail account security settings
- Try test email first

### "SMTP Authentication failed"
- Make sure 2-Step Verification is enabled
- Use App Password, not regular password
- Check username is correct (full email address)

### "Connection timeout"
- Check firewall settings
- Verify port 587 is not blocked
- Try port 465 with SSL instead

---

## 🎉 Once Configured

The OTP will be sent to the user's email automatically when they click "Send Verification Code"!

No more showing OTP in the app - it's secure and professional! ✅



