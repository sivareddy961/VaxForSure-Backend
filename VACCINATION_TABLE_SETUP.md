# Vaccination Table Setup Guide

## ⚠️ IMPORTANT: Create Vaccinations Table First!

Before the "Mark as Completed" feature can work, you need to create the `vaccinations` table in your database.

## 📋 Steps to Create Table

### Step 1: Open phpMyAdmin
Go to: http://localhost:8080/phpmyadmin/db_structure.php?server=1&db=vaxforsure

### Step 2: Create the Table

**Option A: Use the SQL file (Recommended)**
1. In phpMyAdmin, click on the `vaxforsure` database (left sidebar)
2. Click on the "SQL" tab (top menu)
3. Copy and paste the SQL from `create_vaccinations_table.sql`
4. Click "Go" button

**Option B: Run SQL directly**
1. In phpMyAdmin, click on the `vaxforsure` database
2. Click on the "SQL" tab
3. Run this SQL:

```sql
USE `vaxforsure`;

CREATE TABLE IF NOT EXISTS `vaccinations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `child_id` int(11) NOT NULL,
  `vaccine_name` varchar(255) NOT NULL,
  `dose_number` int(11) DEFAULT 1,
  `scheduled_date` date DEFAULT NULL,
  `completed_date` date DEFAULT NULL,
  `status` enum('pending','completed','missed') DEFAULT 'pending',
  `healthcare_facility` varchar(255) DEFAULT NULL,
  `batch_lot_number` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `child_id` (`child_id`),
  KEY `vaccine_name` (`vaccine_name`),
  KEY `status` (`status`),
  CONSTRAINT `fk_vaccinations_child_id` FOREIGN KEY (`child_id`) REFERENCES `children` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Step 3: Verify Table Creation
1. After running the SQL, you should see a success message
2. Check if `vaccinations` table appears in the table list
3. Verify the structure matches the SQL above

## 🧪 Test Database Connection

### Test 1: Check Table Exists
Open in browser: http://localhost:8080/vaxforsure/test_vaccination_table.php

**Expected Result:**
```json
{
    "success": true,
    "table_exists": true,
    "table_structure": [...],
    "children_count": X,
    "sample_children": [...],
    "vaccinations_count": 0
}
```

### Test 2: Test Mark Completed
Open in browser: http://localhost:8080/vaxforsure/test_mark_completed.php

**Expected Result:**
```json
{
    "success": true,
    "message": "Database connection successful",
    "table_exists": true,
    "sample_child": {
        "id": 1,
        "name": "Child Name"
    }
}
```

## 🔍 Troubleshooting

### Error: "vaccinations table does not exist"
**Solution:** Run the SQL from Step 2 above

### Error: "Child not found"
**Solution:** 
1. Make sure you have added children using the "Add Child" screen
2. Check phpMyAdmin → `children` table to see if children exist
3. Verify the `child_id` being sent matches an existing child

### Error: "Failed to prepare query"
**Solution:**
1. Check if all required tables exist (`children`, `users`)
2. Verify database connection (port 3307)
3. Check PHP error logs in XAMPP

### Error: "Foreign key constraint fails"
**Solution:**
1. Make sure `children` table exists
2. Make sure the `child_id` exists in the `children` table
3. Check that foreign key constraints are enabled in MySQL

## 📁 Files Reference

- **SQL File:** `create_vaccinations_table.sql`
- **Test File:** `test_vaccination_table.php`
- **API Endpoint:** `api/vaccinations/mark_completed.php`
- **Config:** `config.php` (port 3307)

## ✅ Verification Checklist

- [ ] `vaccinations` table created in phpMyAdmin
- [ ] Table structure matches the SQL above
- [ ] `children` table exists and has data
- [ ] Database connection works (port 3307)
- [ ] Test files show success messages
- [ ] API endpoint is accessible

Once all checks pass, the "Mark as Completed" feature should work!

