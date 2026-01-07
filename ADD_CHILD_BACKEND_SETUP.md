# Add Child Backend Implementation Guide

## ✅ Implementation Complete

Backend API endpoints have been created for adding child information and health details, with full connectivity to XAMPP MySQL database.

## 📁 Files Created

### Backend Files (C:\xampp\htdocs\vaxforsure):
1. **api/children/add_child.php** - API endpoint for adding child basic information
2. **api/children/update_health_details.php** - API endpoint for updating health details

## 🗄️ Database Schema

The implementation uses the existing database tables:

### `children` Table:
- `id` (int, auto_increment, PRIMARY KEY)
- `user_id` (int, FOREIGN KEY to users.id)
- `name` (varchar(255))
- `date_of_birth` (date)
- `gender` (enum: 'male', 'female', 'other')
- `created_at` (timestamp)
- `updated_at` (timestamp)

### `health_details` Table:
- `id` (int, auto_increment, PRIMARY KEY)
- `child_id` (int, FOREIGN KEY to children.id)
- `blood_group` (varchar(10), nullable)
- `allergies` (text, nullable)
- `medical_conditions` (text, nullable)
- `created_at` (timestamp)
- `updated_at` (timestamp)

## 🔌 API Endpoints

### 1. Add Child - `/api/children/add_child.php`

**Method:** POST

**Request Body:**
```json
{
  "userId": 1,
  "name": "John Doe",
  "dateOfBirth": "2020-01-15",
  "gender": "male"
}
```

**Response (Success - 201):**
```json
{
  "success": true,
  "message": "Child added successfully",
  "data": {
    "child": {
      "id": 1,
      "user_id": 1,
      "name": "John Doe",
      "date_of_birth": "2020-01-15",
      "gender": "male",
      "birth_weight": null,
      "birth_height": null,
      "blood_group": null
    }
  }
}
```

**Response (Error - 400):**
```json
{
  "success": false,
  "message": "Child name is required"
}
```

### 2. Update Health Details - `/api/children/update_health_details.php`

**Method:** POST

**Request Body:**
```json
{
  "childId": 1,
  "birthWeight": 3.5,
  "birthHeight": 50.0,
  "bloodGroup": "A+"
}
```

**Response (Success - 200):**
```json
{
  "success": true,
  "message": "Health details updated successfully",
  "data": {
    "healthDetails": {
      "child_id": 1,
      "birth_weight": 3.5,
      "birth_height": 50.0,
      "blood_group": "A+",
      "allergies": null,
      "medical_conditions": null
    }
  }
}
```

## 📱 Android Integration

### Updated Files:
1. **models/ApiResponse.kt** - Added `AddChildRequest`, `ChildResponse`, `AddChildResponse`, `UpdateHealthDetailsRequest`
2. **api/ApiService.kt** - Added `addChild()` and `updateHealthDetails()` methods
3. **utils/ApiConstants.kt** - Added `Children.ADD_CHILD` and `Children.UPDATE_HEALTH_DETAILS` endpoints
4. **screens/Profile/AddChildProfileScreen.kt** - Updated to call backend API
5. **screens/Profile/HealthDetailsScreen.kt** - Updated to call backend API

### Flow:
1. User fills child information (name, DOB, gender) → Calls `add_child.php`
2. Backend saves to `children` table → Returns child ID
3. User fills health details (weight, height, blood group) → Calls `update_health_details.php`
4. Backend saves/updates `health_details` table → Returns success

## 🧪 Testing

### Test Backend Endpoints:

1. **Start XAMPP:**
   - Start Apache (port 8080)
   - Start MySQL (port 3307)

2. **Test Add Child API:**
   ```
   URL: http://localhost:8080/vaxforsure/api/children/add_child.php
   Method: POST
   Headers: Content-Type: application/json
   Body:
   {
     "userId": 1,
     "name": "Test Child",
     "dateOfBirth": "15-01-2020",
     "gender": "male"
   }
   ```

3. **Test Update Health Details API:**
   ```
   URL: http://localhost:8080/vaxforsure/api/children/update_health_details.php
   Method: POST
   Headers: Content-Type: application/json
   Body:
   {
     "childId": 1,
     "birthWeight": 3.5,
     "birthHeight": 50.0,
     "bloodGroup": "A+"
   }
   ```

4. **Verify in phpMyAdmin:**
   - Go to: http://localhost:8080/phpmyadmin/sql.php?server=1&db=vaxforsure&table=children&pos=0
   - Check `children` table for new records
   - Check `health_details` table for health information

## 🔍 Database Verification

### Check Children Table:
```sql
SELECT * FROM children ORDER BY created_at DESC LIMIT 10;
```

### Check Health Details Table:
```sql
SELECT hd.*, c.name, c.date_of_birth 
FROM health_details hd 
JOIN children c ON hd.child_id = c.id 
ORDER BY hd.created_at DESC;
```

## ⚠️ Important Notes

1. **Date Format:**
   - Backend accepts both `dd-mm-yyyy` and `yyyy-mm-dd` formats
   - Automatically converts `dd-mm-yyyy` to `yyyy-mm-dd` for database storage

2. **Gender Values:**
   - Backend accepts: "male", "female", "other" (case-insensitive)
   - Also accepts: "Male", "Female", "Other" (auto-converts to lowercase)

3. **User ID Validation:**
   - Backend validates that user exists before adding child
   - Returns 404 if user not found

4. **Transaction Safety:**
   - Uses database transactions for data integrity
   - Rolls back if any error occurs

5. **Health Details:**
   - Health details are optional
   - Can be added later via `update_health_details.php`
   - If health details exist, they are updated; otherwise inserted

## 🚀 Next Steps

1. Test the endpoints using Postman or similar tool
2. Verify data appears in phpMyAdmin
3. Test the Android app flow:
   - Create account → Add child → Add health details
4. Check database tables to confirm data is saved correctly

## 📝 Error Handling

The backend provides detailed error messages:
- **400 Bad Request:** Missing or invalid input
- **404 Not Found:** User or child not found
- **500 Internal Server Error:** Database or server error

All errors are logged for debugging purposes.

