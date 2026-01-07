# Database Tables Summary - VaxForSure

## 📊 Complete Database Structure

Based on frontend screen analysis, here are **ALL 6 TABLES** needed for the VaxForSure application.

---

## 📋 Tables Overview

### **Total Tables: 6**

1. ✅ **users** - User accounts (parents/guardians)
2. ✅ **children** - Child profiles
3. ✅ **health_details** - Child health information
4. ✅ **vaccinations** - Vaccination records
5. ✅ **reminders** - Reminder information
6. ✅ **notifications** - Notification information

---

## 🗄️ Detailed Table Structure

### **1. users** Table
**Purpose:** Stores parent/guardian account information

| Field | Type | Constraints | Description |
|-------|------|-------------|-------------|
| id | int(11) | PRIMARY KEY, AUTO_INCREMENT | Unique user ID |
| full_name | varchar(255) | NOT NULL | User's full name |
| email | varchar(255) | NOT NULL, UNIQUE | User's email address |
| phone | varchar(20) | NULL | User's phone number |
| password | varchar(255) | NULL | Hashed password (NULL for Google users) |
| google_id | varchar(255) | NULL, UNIQUE | Google Sign-In ID |
| email_verified | tinyint(1) | DEFAULT 0 | Email verification status |
| created_at | timestamp | DEFAULT CURRENT_TIMESTAMP | Account creation date |
| updated_at | timestamp | ON UPDATE CURRENT_TIMESTAMP | Last update date |

**Foreign Keys:** None (Parent table)

---

### **2. children** Table
**Purpose:** Stores child profile information

| Field | Type | Constraints | Description |
|-------|------|-------------|-------------|
| id | int(11) | PRIMARY KEY, AUTO_INCREMENT | Unique child ID |
| user_id | int(11) | NOT NULL, FOREIGN KEY | Parent/guardian user ID |
| name | varchar(255) | NOT NULL | Child's name |
| date_of_birth | date | NOT NULL | Child's date of birth |
| gender | enum('male','female','other') | NOT NULL | Child's gender |
| created_at | timestamp | DEFAULT CURRENT_TIMESTAMP | Profile creation date |
| updated_at | timestamp | ON UPDATE CURRENT_TIMESTAMP | Last update date |

**Foreign Keys:**
- `user_id` → `users.id` (ON DELETE CASCADE)

**Used in screens:**
- AddChildProfileScreen
- ProfileDetails
- EditProfileScreen
- Dashboard
- RecordsScreen

---

### **3. health_details** Table
**Purpose:** Stores child health information (one-to-one with children)

| Field | Type | Constraints | Description |
|-------|------|-------------|-------------|
| id | int(11) | PRIMARY KEY, AUTO_INCREMENT | Unique health detail ID |
| child_id | int(11) | NOT NULL, UNIQUE, FOREIGN KEY | Child ID (one-to-one) |
| birth_weight | decimal(5,2) | NULL | Birth weight in kg |
| birth_height | decimal(5,2) | NULL | Birth height in cm |
| blood_group | varchar(10) | NULL | Blood group (A+, B-, etc.) |
| allergies | text | NULL | Known allergies |
| medical_conditions | text | NULL | Medical conditions |
| created_at | timestamp | DEFAULT CURRENT_TIMESTAMP | Record creation date |
| updated_at | timestamp | ON UPDATE CURRENT_TIMESTAMP | Last update date |

**Foreign Keys:**
- `child_id` → `children.id` (ON DELETE CASCADE)

**Used in screens:**
- HealthDetailsScreen
- EditProfileScreen

---

### **4. vaccinations** Table
**Purpose:** Stores vaccination records and completion details

| Field | Type | Constraints | Description |
|-------|------|-------------|-------------|
| id | int(11) | PRIMARY KEY, AUTO_INCREMENT | Unique vaccination ID |
| child_id | int(11) | NOT NULL, FOREIGN KEY | Child ID |
| vaccine_name | varchar(255) | NOT NULL | Name of vaccine (e.g., "BCG") |
| dose_number | int(11) | DEFAULT 1 | Dose number (1, 2, 3, etc.) |
| scheduled_date | date | NULL | Scheduled vaccination date |
| completed_date | date | NULL | Actual completion date |
| status | enum('pending','completed','missed') | DEFAULT 'pending' | Vaccination status |
| healthcare_facility | varchar(255) | NULL | Facility name where vaccinated |
| batch_lot_number | varchar(100) | NULL | Vaccine batch/lot number |
| notes | text | NULL | Additional notes |
| created_at | timestamp | DEFAULT CURRENT_TIMESTAMP | Record creation date |
| updated_at | timestamp | ON UPDATE CURRENT_TIMESTAMP | Last update date |

**Foreign Keys:**
- `child_id` → `children.id` (ON DELETE CASCADE)

**Indexes:**
- `child_id` (for faster child lookups)
- `vaccine_name` (for faster vaccine lookups)
- `status` (for filtering by status)

**Used in screens:**
- MarkAsCompletedScreen
- VaccineDetailsScreen
- RecordsScreen
- VaccinationScheduleScreen
- Dashboard

---

### **5. reminders** Table
**Purpose:** Stores reminder information for users and children

| Field | Type | Constraints | Description |
|-------|------|-------------|-------------|
| id | int(11) | PRIMARY KEY, AUTO_INCREMENT | Unique reminder ID |
| user_id | int(11) | NOT NULL, FOREIGN KEY | User ID |
| child_id | int(11) | NULL, FOREIGN KEY | Child ID (optional) |
| title | varchar(255) | NOT NULL | Reminder title |
| description | text | NULL | Reminder description |
| reminder_date | datetime | NOT NULL | When to remind |
| is_custom | tinyint(1) | DEFAULT 0 | Is custom reminder (1) or system (0) |
| created_at | timestamp | DEFAULT CURRENT_TIMESTAMP | Record creation date |
| updated_at | timestamp | ON UPDATE CURRENT_TIMESTAMP | Last update date |

**Foreign Keys:**
- `user_id` → `users.id` (ON DELETE CASCADE)
- `child_id` → `children.id` (ON DELETE CASCADE)

**Indexes:**
- `user_id` (for faster user lookups)
- `child_id` (for faster child lookups)
- `reminder_date` (for filtering by date)

**Used in screens:**
- AddCustomRemainder
- TodayAlertsScreen
- ReminderSettings

---

### **6. notifications** Table
**Purpose:** Stores notification information for users

| Field | Type | Constraints | Description |
|-------|------|-------------|-------------|
| id | int(11) | PRIMARY KEY, AUTO_INCREMENT | Unique notification ID |
| user_id | int(11) | NOT NULL, FOREIGN KEY | User ID |
| title | varchar(255) | NOT NULL | Notification title |
| message | text | NOT NULL | Notification message |
| type | enum('vaccine_due','reminder','general') | DEFAULT 'general' | Notification type |
| is_read | tinyint(1) | DEFAULT 0 | Read status (0=unread, 1=read) |
| created_at | timestamp | DEFAULT CURRENT_TIMESTAMP | Notification creation date |

**Foreign Keys:**
- `user_id` → `users.id` (ON DELETE CASCADE)

**Indexes:**
- `user_id` (for faster user lookups)
- `is_read` (for filtering read/unread)
- `created_at` (for sorting by date)

**Used in screens:**
- NotificationsScreen
- Dashboard

---

## 🔗 Relationships Diagram

```
users (1) ────< (many) children (1) ────< (one) health_details
  │                                      │
  │                                      │
  │                                      └───< (many) vaccinations
  │
  ├───< (many) reminders
  │
  └───< (many) notifications
```

**Relationships:**
- 1 User → Many Children
- 1 Child → 1 Health Details (One-to-One)
- 1 Child → Many Vaccinations
- 1 User → Many Reminders
- 1 User → Many Notifications
- 1 Child → Many Reminders (optional)

---

## 📝 Key Features

1. **Cascade Deletes:** Deleting a user automatically deletes all related records
2. **Indexes:** Added on frequently queried fields for performance
3. **UTF-8 Support:** All tables use utf8mb4 charset for emoji/special characters
4. **Timestamps:** Automatic timestamp tracking on all tables
5. **Data Integrity:** Foreign keys ensure referential integrity

---

## 📍 File Location

**SQL File:** `C:\xampp\htdocs\vaxforsure\database_complete.sql`

**Import Instructions:** `C:\xampp\htdocs\vaxforsure\IMPORT_DATABASE_INSTRUCTIONS.md`

---

## ✅ Next Steps

1. Import the SQL file into phpMyAdmin
2. Verify all 6 tables were created
3. Check foreign key relationships
4. Ready for backend API implementation!

