# Google Sign-In Setup Guide

## ✅ Implementation Complete

Google Sign-In has been successfully implemented for the VaxForSure Android app with PHP backend integration.

## 📁 Files Created/Modified

### Android App Files:
1. **app/build.gradle.kts** - Added Google Sign-In dependency
2. **app/src/main/java/com/example/vaxforsure/utils/GoogleSignInHelper.kt** - Google Sign-In helper utility
3. **app/src/main/java/com/example/vaxforsure/screens/auth/LoginScreen.kt** - Updated to implement Google Sign-In
4. **app/src/main/java/com/example/vaxforsure/models/ApiResponse.kt** - Added GoogleLoginRequest model
5. **app/src/main/java/com/example/vaxforsure/api/ApiService.kt** - Added googleLogin endpoint
6. **app/src/main/java/com/example/vaxforsure/utils/ApiConstants.kt** - Added GOOGLE_LOGIN endpoint

### Backend Files (C:\xampp\htdocs\vaxforsure):
1. **api/auth/google_login.php** - PHP endpoint for Google authentication
2. **database.sql** - Updated users table schema (added google_id column)
3. **database_update_google.sql** - Migration script for existing databases

## 🗄️ Database Changes

### Updated Users Table:
- Added `google_id` column (varchar(255), nullable, unique)
- Made `password` column nullable (for Google users who don't have passwords)
- Added unique constraint on `google_id`

### To Update Existing Database:

Run this SQL in phpMyAdmin:

```sql
USE `vaxforsure`;

-- Add google_id column
ALTER TABLE `users` 
ADD COLUMN `google_id` varchar(255) DEFAULT NULL AFTER `password`;

-- Make password nullable
ALTER TABLE `users` 
MODIFY COLUMN `password` varchar(255) DEFAULT NULL;

-- Add unique constraint
ALTER TABLE `users` 
ADD UNIQUE KEY `google_id` (`google_id`);
```

Or run the migration script:
```bash
# In phpMyAdmin, go to SQL tab and run:
C:\xampp\htdocs\vaxforsure\database_update_google.sql
```

## 🔧 Backend API Endpoint

### Endpoint: `/api/auth/google_login.php`

**Method:** POST

**Request Body:**
```json
{
  "googleId": "1234567890",
  "email": "user@example.com",
  "fullName": "User Name",
  "photoUrl": "https://..." (optional),
  "phone": "+1234567890" (optional)
}
```

**Response (Success):**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1,
      "full_name": "User Name",
      "email": "user@example.com",
      "phone": null,
      "email_verified": 1
    }
  }
}
```

**Response (Error):**
```json
{
  "success": false,
  "message": "Error message here"
}
```

## 📱 Android Implementation

### How It Works:

1. User clicks "Continue with Google" button
2. Google Sign-In dialog appears
3. User selects Google account
4. App receives Google account info (ID, email, name, photo)
5. App sends Google account info to backend API
6. Backend checks if user exists:
   - If exists: Updates Google ID if needed, returns user data
   - If new: Creates new user account with Google ID
7. App saves user session and navigates to dashboard

### Features:

- ✅ Automatic account creation for new Google users
- ✅ Email verification automatically set to 1 for Google users
- ✅ Handles existing users linking Google account
- ✅ Prevents duplicate Google IDs
- ✅ Error handling and user feedback

## 🚀 Testing

### Test the Backend:

1. Start XAMPP (Apache and MySQL)
2. Open browser: `http://localhost:8080/vaxforsure/api/auth/google_login.php`
3. Use Postman or similar tool to test POST request

### Test in Android App:

1. Build and run the app
2. Go to Login screen
3. Click "Continue with Google"
4. Select a Google account
5. Should navigate to dashboard on success

## ⚠️ Important Notes

1. **Google Sign-In Configuration:**
   - For production, you need to configure OAuth 2.0 credentials in Google Cloud Console
   - Add your app's SHA-1 fingerprint to Google Cloud Console
   - For development/testing, the default configuration works

2. **Database:**
   - Make sure to run the database migration if you have existing users
   - The `google_id` column must be unique

3. **Backend:**
   - Backend file is located at: `C:\xampp\htdocs\vaxforsure\api\auth\google_login.php`
   - Make sure Apache is running on port 8080
   - Test the endpoint before using in app

## 📝 Next Steps (Optional)

1. Add Google profile photo storage
2. Implement Google Sign-Out
3. Add account linking (link Google account to existing email/password account)
4. Add error handling for specific Google Sign-In errors

