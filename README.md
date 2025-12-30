# VaxForSure Backend API

## Database Setup

### Step 1: Create Database
1. Open phpMyAdmin: http://localhost:8080/phpmyadmin
2. Click on "New" to create a new database
3. Database name: `vaxforsure`
4. Collation: `utf8mb4_unicode_ci`
5. Click "Create"

### Step 2: Import Database Schema
1. In phpMyAdmin, select the `vaxforsure` database
2. Click on "Import" tab
3. Click "Choose File" and select `database.sql`
4. Click "Go" to import
5. You should see 6 tables created:
   - `users` - User accounts
   - `children` - Child profiles
   - `health_details` - Health information
   - `vaccinations` - Vaccination records
   - `reminders` - Reminder settings
   - `notifications` - User notifications

### Step 3: Verify Database
- Go to: http://localhost:8080/phpmyadmin
- Select `vaxforsure` database
- You should see all 6 tables listed

## API Endpoints

### Base URL
- **Browser/Postman:** `http://localhost:8080/vaxforsure/api/`
- **Android Emulator:** `http://10.0.2.2:8080/vaxforsure/api/`
- **Physical Device:** `http://YOUR_COMPUTER_IP:8080/vaxforsure/api/`

### Authentication Endpoints

#### 1. Register User
- **URL:** `POST /api/auth/register.php`
- **Request Body:**
```json
{
  "fullName": "John Doe",
  "email": "john@example.com",
  "phone": "1234567890",
  "password": "password123"
}
```
- **Response:**
```json
{
  "success": true,
  "message": "Registration successful",
  "data": {
    "user": {
      "id": 1,
      "full_name": "John Doe",
      "email": "john@example.com",
      "phone": "1234567890",
      "email_verified": 0
    }
  }
}
```

#### 2. Login
- **URL:** `POST /api/auth/login.php`
- **Request Body:**
```json
{
  "email": "john@example.com",
  "password": "password123"
}
```
- **Response (Success):**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1,
      "full_name": "John Doe",
      "email": "john@example.com",
      "phone": "1234567890",
      "email_verified": 1
    }
  }
}
```
- **Response (Error):**
```json
{
  "success": false,
  "message": "Invalid email or password"
}
```

## Testing

### Test Registration
```bash
curl -X POST http://localhost:8080/vaxforsure/api/auth/register.php \
  -H "Content-Type: application/json" \
  -d '{
    "fullName": "Test User",
    "email": "test@example.com",
    "phone": "1234567890",
    "password": "password123"
  }'
```

### Test Login
```bash
curl -X POST http://localhost:8080/vaxforsure/api/auth/login.php \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "password123"
  }'
```

## Database Structure

### Users Table
- `id` - Primary key (auto increment)
- `full_name` - User's full name
- `email` - Email address (unique)
- `phone` - Phone number
- `password` - Hashed password
- `email_verified` - Email verification status (0 or 1)
- `created_at` - Account creation timestamp
- `updated_at` - Last update timestamp

## Troubleshooting

### Database Connection Error
- Check if MySQL is running in XAMPP Control Panel
- Verify database name is `vaxforsure`
- Check `config.php` for correct credentials

### API Not Responding
- Check if Apache is running in XAMPP
- Verify URL: http://localhost:8080/vaxforsure/api/auth/login.php
- Check Apache error logs

### Login Fails
- Verify email exists in database
- Check password hash matches
- Ensure email format is correct

