# 📧 Email Setup Guide - Send OTP to Email

## ✅ Quick Setup (3 Steps)

### Step 1: Open the Setup Page
Open in your browser:
```
http://localhost/vaxforsure/QUICK_EMAIL_SETUP.php
```

### Step 2: Get Gmail App Password
1. Go to: https://myaccount.google.com/
2. Click **"Security"** on the left
3. Enable **"2-Step Verification"** (if not already enabled)
4. Go back to Security → Scroll to **"App passwords"**
5. Select app: **"Mail"** → Device: **"Other (Custom name)"**
6. Enter name: **"VaxForSure"**
7. Click **"Generate"**
8. **Copy the 16-character password** (you'll need this)

### Step 3: Configure Email
1. On the setup page, enter:
   - Your Gmail address
   - The App Password (16 characters, no spaces)
   - Test email address (optional)
2. Click **"Save Configuration"**
3. Check your email inbox for the test email!

---

## 🎯 That's It!

Once configured, the OTP verification codes will be sent to users' email addresses automatically when they click "Forgot Password" in the app.

**No more OTP shown in the app - it's secure and professional! ✅**

---

## 🆘 Troubleshooting

### "Test email not received"
- Check spam/junk folder
- Verify App Password is correct (not regular password)
- Make sure 2-Step Verification is enabled on Gmail

### "Configuration saved but email not working"
- Double-check your App Password (16 characters)
- Try testing again using: `http://localhost/vaxforsure/CONFIGURE_EMAIL.php`
- Check error logs in: `C:\xampp\apache\logs\error.log`

### "SMTP Authentication failed"
- Ensure you're using App Password, not regular password
- Verify your Gmail address is correct
- Make sure 2-Step Verification is enabled

---

## 📝 Alternative: Manual Configuration

If you prefer to configure manually:

1. Copy `email_config.php.template` to `email_config.php`
2. Edit `email_config.php` and fill in your credentials
3. Save the file
4. Test using `CONFIGURE_EMAIL.php`

---

## ✅ Verification

After setup, test by:
1. Opening the app
2. Clicking "Forgot Password"
3. Entering your registered email
4. Checking your email inbox for the OTP code

**The OTP will NOT be shown in the app anymore - it's sent directly to email! 🎉**

