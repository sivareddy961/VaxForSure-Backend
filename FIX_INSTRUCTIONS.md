# 🔧 Fix Vaccinations Table Error

## ⚠️ Error: "Unknown column 'healthcare_facility'"

This error means the `vaccinations` table either doesn't exist or has incorrect column names.

## ✅ Quick Fix (Choose One Method)

### Method 1: Run Fix Script (Easiest)
1. Open in browser: **http://localhost:8080/vaxforsure/fix_vaccinations_table.php**
2. This will automatically check and recreate the table with correct structure
3. You should see a success message

### Method 2: Run SQL in phpMyAdmin
1. Go to: **http://localhost:8080/phpmyadmin/db_structure.php?server=1&db=vaxforsure**
2. Click **"SQL"** tab
3. Copy and paste this SQL:

```sql
USE `vaxforsure`;

-- Drop existing table if it has wrong structure
DROP TABLE IF EXISTS `vaccinations`;

-- Create table with correct structure
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

4. Click **"Go"**
5. Verify table appears in the list

## ✅ Verify Fix

After running the fix, test it:
1. Open: **http://localhost:8080/vaxforsure/test_vaccination_table.php**
2. Should show success with table structure
3. Try "Mark as Completed" in the app again

## 📋 Required Columns

The table MUST have these exact column names:
- `id`
- `child_id`
- `vaccine_name`
- `dose_number`
- `scheduled_date`
- `completed_date`
- `status`
- `healthcare_facility` ← This was missing!
- `batch_lot_number`
- `notes`
- `created_at`
- `updated_at`

## 🚀 After Fix

Once the table is created correctly, the "Mark as Completed" feature will work!





