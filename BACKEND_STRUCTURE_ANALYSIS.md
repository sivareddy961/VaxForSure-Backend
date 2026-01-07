# VaxForSure Backend Structure - Complete Analysis

## 📊 Frontend Screens Analysis & Required Backend Endpoints

Based on analysis of all frontend screens, here is the complete backend structure needed.

---

## 🗄️ Database Tables (Already Defined)

### 1. `users` Table
- Stores user account information
- Fields: id, full_name, email, phone, password, google_id, email_verified, created_at, updated_at

### 2. `children` Table  
- Stores child profile information
- Fields: id, user_id, name, date_of_birth, gender, created_at, updated_at

### 3. `health_details` Table
- Stores child health information
- Fields: id, child_id, blood_group, allergies, medical_conditions, created_at, updated_at

### 4. `vaccinations` Table
- Stores vaccination records
- Fields: id, child_id, vaccine_name, dose_number, scheduled_date, completed_date, status, notes, created_at, updated_at

### 5. `reminders` Table
- Stores reminder information
- Fields: id, user_id, child_id, title, description, reminder_date, is_custom, created_at, updated_at

### 6. `notifications` Table
- Stores notification information
- Fields: id, user_id, title, message, type, is_read, created_at

---

## 🔌 Required Backend API Endpoints

### **📁 AUTHENTICATION (`api/auth/`)**

#### ✅ 1. `login.php` (IMPLEMENTED)
- **Method:** POST
- **Purpose:** User login with email/password
- **Request:** `{ email, password }`
- **Response:** `{ success, message, data: { user } }`

#### ✅ 2. `register.php` (IMPLEMENTED)
- **Method:** POST
- **Purpose:** User registration
- **Request:** `{ fullName, email, phone, password }`
- **Response:** `{ success, message, data: { user } }`

#### ✅ 3. `google_login.php` (IMPLEMENTED)
- **Method:** POST
- **Purpose:** Google Sign-In authentication
- **Request:** `{ googleId, email, fullName, photoUrl, phone }`
- **Response:** `{ success, message, data: { user } }`

#### ❌ 4. `forgot_password.php` (NOT IMPLEMENTED)
- **Method:** POST
- **Purpose:** Send password reset OTP
- **Request:** `{ email }`
- **Response:** `{ success, message }`
- **Screen:** `ForgotPasswordScreen.kt`

#### ❌ 5. `verify_otp.php` (NOT IMPLEMENTED)
- **Method:** POST
- **Purpose:** Verify OTP for password reset
- **Request:** `{ email, otp }`
- **Response:** `{ success, message, data: { token } }`
- **Screen:** `OTPVerificationScreen.kt`

#### ❌ 6. `reset_password.php` (NOT IMPLEMENTED)
- **Method:** POST
- **Purpose:** Reset password after OTP verification
- **Request:** `{ email, otp, newPassword }`
- **Response:** `{ success, message }`
- **Screen:** `ResetConfirmationScreen.kt`

---

### **📁 CHILDREN (`api/children/`)**

#### ✅ 7. `add_child.php` (IMPLEMENTED)
- **Method:** POST
- **Purpose:** Add new child profile
- **Request:** `{ userId, name, dateOfBirth, gender, birthWeight?, birthHeight?, bloodGroup? }`
- **Response:** `{ success, message, data: { child } }`
- **Screen:** `AddChildProfileScreen.kt`

#### ✅ 8. `update_health_details.php` (IMPLEMENTED)
- **Method:** POST
- **Purpose:** Update/add health details for child
- **Request:** `{ childId, birthWeight?, birthHeight?, bloodGroup?, allergies?, medicalConditions? }`
- **Response:** `{ success, message, data: { healthDetails } }`
- **Screen:** `HealthDetailsScreen.kt`

#### ❌ 9. `get_children.php` (NOT IMPLEMENTED)
- **Method:** GET
- **Purpose:** Get all children for a user
- **Request:** `?userId={userId}`
- **Response:** `{ success, message, data: { children: [] } }`
- **Screen:** `ProfileDetails.kt`, `Dashboard.kt`
- **Note:** Currently using local storage (ChildManager)

#### ❌ 10. `get_child.php` (NOT IMPLEMENTED)
- **Method:** GET
- **Purpose:** Get specific child by ID
- **Request:** `?childId={childId}`
- **Response:** `{ success, message, data: { child, healthDetails } }`
- **Screen:** `EditProfileScreen.kt`

#### ❌ 11. `update_child.php` (NOT IMPLEMENTED)
- **Method:** PUT
- **Purpose:** Update child profile information
- **Request:** `{ childId, name?, dateOfBirth?, gender?, birthWeight?, birthHeight?, bloodGroup? }`
- **Response:** `{ success, message, data: { child } }`
- **Screen:** `EditProfileScreen.kt`
- **Action:** "Save Changes" button

#### ❌ 12. `delete_child.php` (NOT IMPLEMENTED)
- **Method:** DELETE
- **Purpose:** Delete child profile
- **Request:** `?childId={childId}`
- **Response:** `{ success, message }`
- **Screen:** `EditProfileScreen.kt`
- **Action:** "Delete Profile" button

---

### **📁 VACCINATIONS (`api/vaccinations/`)**

#### ❌ 13. `mark_completed.php` (NOT IMPLEMENTED)
- **Method:** POST
- **Purpose:** Mark vaccine as completed
- **Request:** `{ childId, vaccineName, dateAdministered, healthcareFacility, batchLotNumber, notes? }`
- **Response:** `{ success, message, data: { vaccination } }`
- **Screen:** `MarkAsCompletedScreen.kt`
- **Note:** Should update `vaccinations` table status to 'completed'

#### ❌ 14. `get_vaccinations.php` (NOT IMPLEMENTED)
- **Method:** GET
- **Purpose:** Get all vaccinations for a child
- **Request:** `?childId={childId}`
- **Response:** `{ success, message, data: { vaccinations: [] } }`
- **Screen:** `VaccinationScheduleScreen.kt`, `Dashboard.kt`
- **Note:** Currently using VaccineManager (local storage)

#### ❌ 15. `get_vaccination_status.php` (NOT IMPLEMENTED)
- **Method:** GET
- **Purpose:** Get status of specific vaccine for a child
- **Request:** `?childId={childId}&vaccineName={vaccineName}`
- **Response:** `{ success, message, data: { status, completedDate, details } }`
- **Screen:** `VaccineDetailsScreen.kt`, `VaccinationScheduleScreen.kt`

#### ❌ 16. `get_completed_vaccinations.php` (NOT IMPLEMENTED)
- **Method:** GET
- **Purpose:** Get all completed vaccinations (records)
- **Request:** `?userId={userId}` or `?childId={childId}`
- **Response:** `{ success, message, data: { records: [] } }`
- **Screen:** `RecordsScreen.kt`
- **Note:** Currently using VaccineManager.getAllVaccinationRecords()

#### ❌ 17. `get_vaccination_records.php` (NOT IMPLEMENTED)
- **Method:** GET
- **Purpose:** Get detailed vaccination records grouped by child
- **Request:** `?userId={userId}`
- **Response:** `{ success, message, data: { recordsByChild: { childId: [], ... } } }`
- **Screen:** `RecordsScreen.kt`

---

### **📁 REMINDERS (`api/reminders/`)**

#### ❌ 18. `add_reminder.php` (NOT IMPLEMENTED)
- **Method:** POST
- **Purpose:** Add custom reminder
- **Request:** `{ userId, childId?, title, description?, reminderDate, isCustom }`
- **Response:** `{ success, message, data: { reminder } }`
- **Screen:** `AddCustomRemainder.kt`

#### ❌ 19. `get_reminders.php` (NOT IMPLEMENTED)
- **Method:** GET
- **Purpose:** Get all reminders for user
- **Request:** `?userId={userId}&date?={date}`
- **Response:** `{ success, message, data: { reminders: [] } }`
- **Screen:** `TodayAlertsScreen.kt`, `Dashboard.kt`

#### ❌ 20. `update_reminder.php` (NOT IMPLEMENTED)
- **Method:** PUT
- **Purpose:** Update reminder
- **Request:** `{ reminderId, title?, description?, reminderDate? }`
- **Response:** `{ success, message, data: { reminder } }`

#### ❌ 21. `delete_reminder.php` (NOT IMPLEMENTED)
- **Method:** DELETE
- **Purpose:** Delete reminder
- **Request:** `?reminderId={reminderId}`
- **Response:** `{ success, message }`

---

### **📁 NOTIFICATIONS (`api/notifications/`)**

#### ❌ 22. `get_notifications.php` (NOT IMPLEMENTED)
- **Method:** GET
- **Purpose:** Get all notifications for user
- **Request:** `?userId={userId}&isRead?={true/false}`
- **Response:** `{ success, message, data: { notifications: [] } }`
- **Screen:** `NotificationsScreen.kt`

#### ❌ 23. `mark_notification_read.php` (NOT IMPLEMENTED)
- **Method:** PUT
- **Purpose:** Mark notification as read
- **Request:** `{ notificationId }`
- **Response:** `{ success, message }`
- **Screen:** `NotificationsScreen.kt`

#### ❌ 24. `create_notification.php` (NOT IMPLEMENTED)
- **Method:** POST
- **Purpose:** Create system notification (internal use)
- **Request:** `{ userId, title, message, type }`
- **Response:** `{ success, message, data: { notification } }`
- **Note:** Usually called by backend when vaccine due dates approach

---

### **📁 DASHBOARD (`api/dashboard/`)**

#### ❌ 25. `get_dashboard_data.php` (NOT IMPLEMENTED)
- **Method:** GET
- **Purpose:** Get all dashboard data in one call
- **Request:** `?userId={userId}`
- **Response:** 
```json
{
  "success": true,
  "message": "Dashboard data retrieved",
  "data": {
    "children": [],
    "upcomingVaccines": [],
    "completedVaccines": [],
    "pendingVaccines": [],
    "notifications": [],
    "reminders": [],
    "summary": {
      "totalChildren": 0,
      "completedCount": 0,
      "pendingCount": 0
    }
  }
}
```
- **Screen:** `Dashboard.kt`

---

### **📁 EXPORT (`api/export/`)**

#### ❌ 26. `export_records.php` (NOT IMPLEMENTED)
- **Method:** GET
- **Purpose:** Export vaccination records (PDF/CSV)
- **Request:** `?userId={userId}&childId?={childId}&format={pdf/csv}`
- **Response:** File download
- **Screen:** `ExportRecords.kt`

---

## 📋 Implementation Priority

### **Phase 1: Critical (Currently Missing)**
1. ✅ Authentication (login, register, google_login) - **DONE**
2. ✅ Add Child - **DONE**
3. ✅ Update Health Details - **DONE**
4. ❌ Get Children List
5. ❌ Update Child Profile
6. ❌ Delete Child Profile
7. ❌ Mark Vaccine Completed
8. ❌ Get Vaccination Records

### **Phase 2: Important**
9. ❌ Get Vaccination Status
10. ❌ Get Completed Vaccinations
11. ❌ Get Notifications
12. ❌ Get Reminders

### **Phase 3: Enhancement**
13. ❌ Add Reminder
14. ❌ Forgot Password Flow
15. ❌ Export Records
16. ❌ Dashboard Aggregated Data

---

## 🔗 API Endpoint Structure

```
C:\xampp\htdocs\vaxforsure\api\
├── auth\
│   ├── login.php ✅
│   ├── register.php ✅
│   ├── google_login.php ✅
│   ├── forgot_password.php ❌
│   ├── verify_otp.php ❌
│   └── reset_password.php ❌
│
├── children\
│   ├── add_child.php ✅
│   ├── update_health_details.php ✅
│   ├── get_children.php ❌
│   ├── get_child.php ❌
│   ├── update_child.php ❌
│   └── delete_child.php ❌
│
├── vaccinations\
│   ├── mark_completed.php ❌
│   ├── get_vaccinations.php ❌
│   ├── get_vaccination_status.php ❌
│   ├── get_completed_vaccinations.php ❌
│   └── get_vaccination_records.php ❌
│
├── reminders\
│   ├── add_reminder.php ❌
│   ├── get_reminders.php ❌
│   ├── update_reminder.php ❌
│   └── delete_reminder.php ❌
│
├── notifications\
│   ├── get_notifications.php ❌
│   ├── mark_notification_read.php ❌
│   └── create_notification.php ❌
│
├── dashboard\
│   └── get_dashboard_data.php ❌
│
└── export\
    └── export_records.php ❌
```

---

## 📝 Notes

### Current Implementation Status:
- ✅ **Authentication:** Login, Register, Google Login - COMPLETE
- ✅ **Children:** Add Child, Update Health Details - COMPLETE
- ❌ **Children Management:** Get, Update, Delete - MISSING
- ❌ **Vaccinations:** All operations - MISSING (using local storage)
- ❌ **Reminders:** All operations - MISSING
- ❌ **Notifications:** All operations - MISSING
- ❌ **Export:** Missing

### Local Storage vs Backend:
- Currently using `ChildManager` (SharedPreferences) for children
- Currently using `VaccineManager` (SharedPreferences) for vaccinations
- Need to migrate these to backend API calls

### Database Connection:
- MySQL Database: `vaxforsure`
- Port: 3307
- Config: `C:\xampp\htdocs\vaxforsure\config.php`
- Base URL: `http://localhost:8080/vaxforsure/api/`

---

## ✅ Summary

**Total Endpoints Needed:** 26
**Implemented:** 5 (19%)
**Missing:** 21 (81%)

**Priority:** Focus on Phase 1 endpoints first, as these are critical for core functionality.

