# Database Import Instructions for phpMyAdmin

## 📍 Location
**phpMyAdmin URL:** http://localhost:8080/phpmyadmin/db_structure.php?server=1&db=vaxforsure

## 📋 Step-by-Step Instructions

### Method 1: Import SQL File (Recommended)

1. **Open phpMyAdmin**
   - Go to: http://localhost:8080/phpmyadmin
   - Login (usually username: `root`, password: blank)

2. **Select Database**
   - Click on `vaxforsure` database in left sidebar
   - If database doesn't exist, click "New" and create `vaxforsure`

3. **Import SQL File**
   - Click on **"Import"** tab at the top
   - Click **"Choose File"** button
   - Navigate to: `C:\xampp\htdocs\vaxforsure\database_complete.sql`
   - Select the file
   - Click **"Go"** button at bottom

4. **Verify Import**
   - Go to **"Structure"** tab
   - You should see 6 tables:
     - ✅ users
     - ✅ children
     - ✅ health_details
     - ✅ vaccinations
     - ✅ reminders
     - ✅ notifications

---

### Method 2: Copy-Paste SQL in SQL Tab

1. **Open phpMyAdmin**
   - Go to: http://localhost:8080/phpmyadmin
   - Login (username: `root`, password: blank)

2. **Select Database**
   - Click on `vaxforsure` database in left sidebar
   - If it doesn't exist, create it first

3. **Open SQL Tab**
   - Click on **"SQL"** tab at the top

4. **Copy SQL Code**
   - Open file: `C:\xampp\htdocs\vaxforsure\database_complete.sql`
   - Copy ALL the SQL code

5. **Paste and Execute**
   - Paste the SQL code into the SQL text area
   - Click **"Go"** button

6. **Verify**
   - Check for success message
   - Go to **"Structure"** tab to see tables

---

## ✅ Verification Steps

After importing, verify all tables were created:

### 1. Check Tables Exist
```sql
SHOW TABLES;
```
Expected output:
- users
- children
- health_details
- vaccinations
- reminders
- notifications

### 2. Check Table Structures
```sql
DESCRIBE users;
DESCRIBE children;
DESCRIBE health_details;
DESCRIBE vaccinations;
DESCRIBE reminders;
DESCRIBE notifications;
```

### 3. Check Foreign Keys
- Click on each table in Structure view
- Go to "Relation view" to see foreign key relationships

---

## 📊 Tables Overview

### 1. **users** Table
- Stores parent/guardian account information
- Fields: id, full_name, email, phone, password, google_id, email_verified, created_at, updated_at

### 2. **children** Table
- Stores child profile information
- Fields: id, user_id, name, date_of_birth, gender, created_at, updated_at
- Foreign Key: user_id → users.id

### 3. **health_details** Table
- Stores child health information
- Fields: id, child_id, birth_weight, birth_height, blood_group, allergies, medical_conditions, created_at, updated_at
- Foreign Key: child_id → children.id (One-to-One relationship)

### 4. **vaccinations** Table
- Stores vaccination records and completion details
- Fields: id, child_id, vaccine_name, dose_number, scheduled_date, completed_date, status, healthcare_facility, batch_lot_number, notes, created_at, updated_at
- Foreign Key: child_id → children.id

### 5. **reminders** Table
- Stores reminder information
- Fields: id, user_id, child_id, title, description, reminder_date, is_custom, created_at, updated_at
- Foreign Keys: user_id → users.id, child_id → children.id

### 6. **notifications** Table
- Stores notification information
- Fields: id, user_id, title, message, type, is_read, created_at
- Foreign Key: user_id → users.id

---

## 🔍 Quick Check Queries

Run these in SQL tab to verify:

```sql
-- Count tables (should return 6)
SELECT COUNT(*) as table_count 
FROM information_schema.tables 
WHERE table_schema = 'vaxforsure';

-- Check users table structure
SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = 'vaxforsure' AND TABLE_NAME = 'users';

-- Check foreign keys
SELECT 
    TABLE_NAME,
    COLUMN_NAME,
    CONSTRAINT_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM information_schema.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = 'vaxforsure'
AND REFERENCED_TABLE_NAME IS NOT NULL;
```

---

## ⚠️ Troubleshooting

### Error: "Table already exists"
**Solution:** Drop existing tables first:
```sql
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS reminders;
DROP TABLE IF EXISTS vaccinations;
DROP TABLE IF EXISTS health_details;
DROP TABLE IF EXISTS children;
DROP TABLE IF EXISTS users;
```
Then run the import again.

### Error: "Cannot add foreign key constraint"
**Solution:** Make sure you're importing in the correct order (users first, then children, etc.)

### Error: "Access denied"
**Solution:** Make sure MySQL user has CREATE and ALTER privileges

---

## ✅ Success Indicators

You'll know it worked if:
1. ✅ All 6 tables appear in Structure view
2. ✅ No error messages shown
3. ✅ Foreign key relationships visible in Relation view
4. ✅ Table structures match the schema

---

## 📝 Notes

- Database charset: `utf8mb4` (supports emojis and special characters)
- All timestamps use `CURRENT_TIMESTAMP` for automatic date tracking
- Foreign keys use `ON DELETE CASCADE` (deleting parent deletes children)
- Indexes added on frequently queried fields for performance

---

**File Location:** `C:\xampp\htdocs\vaxforsure\database_complete.sql`

