# 🔐 Forgot Password OTP System - Complete Setup Guide

## ✅ Setup Complete!

All files have been created and configured for the forgot password OTP system.

---

## 📋 What Was Created

### Backend Files:
1. ✅ `api/auth/forgot_password.php` - Sends OTP to email
2. ✅ `api/auth/verify_otp.php` - Verifies OTP code
3. ✅ `api/auth/reset_password.php` - Resets password after OTP verification
4. ✅ `SETUP_FORGOT_PASSWORD.php` - Automatic setup script
5. ✅ `CREATE_PASSWORD_RESET_TABLE_DIRECT.sql` - SQL script for phpMyAdmin

### Frontend Files:
1. ✅ `ForgotPasswordScreen.kt` - Updated with backend integration
2. ✅ `OTPVerificationScreen.kt` - Updated with backend verification
3. ✅ `ResetPasswordScreen.kt` - New screen for password reset

---

## 🚀 Quick Setup (Choose ONE method)

### Method 1: Automatic Setup (Recommended)

**Step 1:** Open in your browser:
```
http://localhost/vaxforsure/SETUP_FORGOT_PASSWORD.php
```

This will:
- ✅ Create the `password_reset_otps` table automatically
- ✅ Verify all components
- ✅ Show test results
- ✅ Display any errors or warnings

**That's it!** The script does everything for you.

---

### Method 2: Manual Setup via phpMyAdmin

**Step 1:** Open phpMyAdmin:
```
http://localhost:8080/phpmyadmin/db_structure.php?server=1&db=vaxforsure
```

**Step 2:** Click on `vaxforsure` database (left sidebar)

**Step 3:** Click the **"SQL"** tab at the top

**Step 4:** Copy and paste the entire contents of:
```
C:\xampp\htdocs\vaxforsure\CREATE_PASSWORD_RESET_TABLE_DIRECT.sql
```

**Step 5:** Click **"Go"** button

**Step 6:** You should see:
- ✅ "Password Reset OTPs table created successfully!"
- ✅ Table structure displayed

---

## 📊 Database Table Structure

The `password_reset_otps` table has these columns:

| Column | Type | Description |
|--------|------|-------------|
| `id` | INT (Primary Key) | Auto-increment ID |
| `email` | VARCHAR(255) | User's email address |
| `otp_code` | VARCHAR(6) | 6-digit OTP code |
| `expires_at` | DATETIME | When OTP expires (10 minutes) |
| `used` | TINYINT(1) | 0 = not used, 1 = used |
| `created_at` | TIMESTAMP | When OTP was created |

---

## 🧪 Testing the System

### Test 1: Create Table
Run `SETUP_FORGOT_PASSWORD.php` in browser - should show ✅ all green

### Test 2: Test from App
1. Open your Android app
2. Go to Login screen
3. Click "Forgot Password"
4. Enter your registered email: `omkarvinays3104@gmail.com`
5. Click "Send Verification Code"

### Test 3: Check OTP
After clicking "Send Verification Code", the OTP will be:

**Option A:** Shown in Toast message (development mode)
- Look for a long toast message with the OTP code

**Option B:** Check log file:
- Open: `C:\xampp\htdocs\vaxforsure\otp_log.txt`
- You'll see entries like: `2024-01-15 12:30:45 - Email: omkarvinays3104@gmail.com - OTP: 123456`

### Test 4: Complete Flow
1. ✅ Enter email → Get OTP
2. ✅ Enter OTP → Verify
3. ✅ Set new password → Reset
4. ✅ Login with new password

---

## 📝 Important Notes

### Email Sending (XAMPP)
- PHP's `mail()` function may not work in XAMPP by default
- **Solution:** OTP is logged to `otp_log.txt` file
- OTP is also included in API response (development mode)
- For production, configure SMTP in `config.php`

### OTP Expiration
- OTP expires in **10 minutes**
- Each OTP can only be used **once**
- Old unused/expired OTPs are automatically cleaned up

### Security
- The system doesn't reveal if an email exists (security best practice)
- Always returns success message even if email doesn't exist
- OTP codes are 6-digit random numbers

---

## 🔧 Troubleshooting

### Issue: "Database connection failed"
**Solution:** Check `config.php` - make sure port is 3307

### Issue: "Table already exists"
**Solution:** That's fine! The table is already created.

### Issue: "Cannot connect to server" in app
**Solution:** 
1. Check XAMPP Apache is running on port 8080
2. Check `ApiConstants.kt` - BASE_URL should be your computer's IP
3. Phone and computer must be on same Wi-Fi network

### Issue: "OTP not received"
**Solution:**
1. Check `otp_log.txt` file for the OTP code
2. Check Toast message in app (development mode shows OTP)
3. Verify email is registered in `users` table

---

## ✅ Verification Checklist

- [ ] Table `password_reset_otps` exists in database
- [ ] Users table has your email registered
- [ ] XAMPP Apache running on port 8080
- [ ] `ApiConstants.BASE_URL` matches your computer's IP
- [ ] Phone and computer on same network
- [ ] Can access `http://localhost/vaxforsure/api/auth/forgot_password.php`

---

## 🎉 You're All Set!

Once the table is created, the forgot password system is ready to use!

**Next:** Test it from your Android app and check `otp_log.txt` for OTP codes.



