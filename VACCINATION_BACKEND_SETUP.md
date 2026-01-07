# Vaccination Backend Implementation Guide

## ✅ Implementation Complete

Backend API endpoints have been created for storing and retrieving vaccination records. When a child takes a vaccine, it is stored in the database under that specific child.

## 📁 Files Created

### Backend Files (C:\xampp\htdocs\vaxforsure\api\vaccinations\):
1. **mark_completed.php** - API endpoint to mark a vaccine as completed
2. **get_vaccinations.php** - API endpoint to get vaccinations (by child or user)
3. **get_vaccination_records.php** - API endpoint to get completed vaccination records grouped by child
4. **get_vaccination_status.php** - API endpoint to check vaccination status

## 🗄️ Database Structure

### Vaccinations Table
The `vaccinations` table stores all vaccination records:

```
Parent (users)
  └── Child (children)
       └── Vaccines (vaccinations) ← Each vaccine is stored separately per child
```

**Table Structure:**
- `id` - Unique vaccination ID
- `child_id` - Foreign key to children table
- `vaccine_name` - Name of vaccine (e.g., "BCG", "Hepatitis B (1st dose)")
- `dose_number` - Dose number (1, 2, 3, etc.)
- `completed_date` - Date when vaccine was administered
- `status` - Status: 'pending', 'completed', 'missed'
- `healthcare_facility` - Facility where vaccine was given
- `batch_lot_number` - Batch/Lot number
- `notes` - Additional notes

## 🔌 API Endpoints

### 1. Mark Vaccine as Completed - `/api/vaccinations/mark_completed.php`

**Method:** POST

**Request Body:**
```json
{
  "childId": 1,
  "vaccineName": "BCG",
  "dateAdministered": "2024-01-15",
  "healthcareFacility": "City Health Center",
  "batchLotNumber": "BATCH123",
  "notes": "No side effects observed"
}
```

**Response (Success - 201):**
```json
{
  "success": true,
  "message": "Vaccination record saved successfully",
  "data": {
    "vaccination": {
      "id": 1,
      "child_id": 1,
      "vaccine_name": "BCG",
      "dose_number": 1,
      "completed_date": "2024-01-15",
      "status": "completed",
      "healthcare_facility": "City Health Center",
      "batch_lot_number": "BATCH123",
      "notes": "No side effects observed"
    }
  }
}
```

### 2. Get Vaccinations - `/api/vaccinations/get_vaccinations.php`

**Method:** GET

**Query Parameters:**
- `childId` (optional) - Get vaccinations for specific child
- `userId` (optional) - Get vaccinations for all children of user
- `status` (optional) - Filter by status: 'all', 'pending', 'completed', 'missed' (default: 'all')

**Example Requests:**
```
GET /api/vaccinations/get_vaccinations.php?childId=1
GET /api/vaccinations/get_vaccinations.php?userId=1&status=completed
```

**Response:**
```json
{
  "success": true,
  "message": "Vaccinations retrieved successfully",
  "data": {
    "vaccinations": [
      {
        "id": 1,
        "child_id": 1,
        "vaccine_name": "BCG",
        "dose_number": 1,
        "completed_date": "2024-01-15",
        "status": "completed",
        "healthcare_facility": "City Health Center",
        "batch_lot_number": "BATCH123",
        "notes": "No side effects"
      }
    ]
  }
}
```

### 3. Get Vaccination Records - `/api/vaccinations/get_vaccination_records.php`

**Method:** GET

**Query Parameters:**
- `userId` (required) - User ID

**Example Request:**
```
GET /api/vaccinations/get_vaccination_records.php?userId=1
```

**Response:**
```json
{
  "success": true,
  "message": "Vaccination records retrieved successfully",
  "data": {
    "recordsByChild": [
      {
        "child": {
          "id": 1,
          "name": "John Doe",
          "date_of_birth": "2020-01-15",
          "gender": "male"
        },
        "vaccinations": [
          {
            "id": 1,
            "vaccine_name": "BCG",
            "completed_date": "2024-01-15",
            "healthcare_facility": "City Health Center",
            "batch_lot_number": "BATCH123",
            "notes": "No side effects",
            "dose_number": 1
          }
        ]
      }
    ]
  }
}
```

### 4. Get Vaccination Status - `/api/vaccinations/get_vaccination_status.php`

**Method:** GET

**Query Parameters:**
- `childId` (required) - Child ID
- `vaccineName` (required) - Vaccine name

**Example Request:**
```
GET /api/vaccinations/get_vaccination_status.php?childId=1&vaccineName=BCG
```

**Response:**
```json
{
  "success": true,
  "message": "Vaccination status retrieved successfully",
  "data": {
    "vaccination": {
      "id": 1,
      "child_id": 1,
      "vaccine_name": "BCG",
      "status": "completed",
      "completed_date": "2024-01-15",
      "healthcare_facility": "City Health Center",
      "batch_lot_number": "BATCH123",
      "notes": "No side effects"
    }
  }
}
```

## 📱 Android Integration

### Updated Files:
1. **models/ApiResponse.kt** - Added `MarkVaccineCompletedRequest`, `VaccinationResponse`, `MarkVaccineCompletedResponse`, `GetVaccinationsResponse`
2. **api/ApiService.kt** - Added vaccination API methods
3. **utils/ApiConstants.kt** - Added `Vaccinations` endpoints
4. **screens/Schedule/MarkAsCompletedScreen.kt** - Updated to call backend API

### Flow:
1. User fills vaccination details in `MarkAsCompletedScreen`
2. Selects child
3. Clicks "Save Record"
4. App sends data to `mark_completed.php`
5. Backend saves to `vaccinations` table with `child_id`
6. Each child's vaccines are stored separately
7. App also saves locally for offline access

## 🧪 Testing

### Test Backend Endpoints:

1. **Start XAMPP:**
   - Start Apache (port 8080)
   - Start MySQL (port 3307)

2. **Test Mark Completed API:**
   ```
   URL: http://localhost:8080/vaxforsure/api/vaccinations/mark_completed.php
   Method: POST
   Headers: Content-Type: application/json
   Body:
   {
     "childId": 1,
     "vaccineName": "BCG",
     "dateAdministered": "15-01-2024",
     "healthcareFacility": "City Health Center",
     "batchLotNumber": "BATCH123",
     "notes": "No side effects"
   }
   ```

3. **Test Get Vaccinations:**
   ```
   URL: http://localhost:8080/vaxforsure/api/vaccinations/get_vaccinations.php?childId=1
   Method: GET
   ```

4. **Verify in phpMyAdmin:**
   - Go to: http://localhost:8080/phpmyadmin/db_structure.php?server=1&db=vaxforsure&table=vaccinations&pos=0
   - Check `vaccinations` table for new records
   - Each record should have a `child_id` linking it to a specific child

## 🔍 Database Verification

### Check Vaccinations Table:
```sql
SELECT v.*, c.name as child_name, c.user_id, u.full_name as parent_name
FROM vaccinations v
JOIN children c ON v.child_id = c.id
JOIN users u ON c.user_id = u.id
ORDER BY v.completed_date DESC;
```

### Check Vaccinations by Child:
```sql
SELECT c.name as child_name, v.vaccine_name, v.completed_date, v.status
FROM children c
LEFT JOIN vaccinations v ON c.id = v.child_id
WHERE c.user_id = 1
ORDER BY c.name, v.completed_date DESC;
```

## ⚠️ Important Notes

1. **Date Format:**
   - Backend accepts both `dd-mm-yyyy` and `yyyy-mm-dd` formats
   - Automatically converts `dd-mm-yyyy` to `yyyy-mm-dd` for database storage

2. **Child Association:**
   - Each vaccination is linked to a specific child via `child_id`
   - Multiple children can have the same vaccine
   - Each child's vaccines are stored separately

3. **Dose Number:**
   - Automatically extracted from vaccine name (e.g., "Hepatitis B (1st dose)" → 1)
   - Defaults to 1 if not found in name

4. **Duplicate Handling:**
   - If vaccine already exists for child, it updates the existing record
   - Otherwise, creates a new record

5. **Status:**
   - Automatically set to 'completed' when marked as completed
   - Status field in database: 'pending', 'completed', 'missed'

## 🚀 Next Steps

1. Test the endpoints using Postman or similar tool
2. Verify data appears in phpMyAdmin
3. Test the Android app flow:
   - Mark vaccine as completed → Check database
   - View records → Should show vaccines per child
4. Implement retrieval endpoints in Android app for Records screen

## 📝 Database Connection

- **Port:** 3307 (as specified)
- **Config:** `C:\xampp\htdocs\vaxforsure\config.php`
- **Database:** `vaxforsure`
- **Table:** `vaccinations`

All vaccination records are now stored in the database, organized by parent → child → vaccines structure!

