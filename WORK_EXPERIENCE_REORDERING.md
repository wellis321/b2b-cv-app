# Work Experience Reordering Feature

This feature allows users to customize the order of their work experiences while maintaining a default date-based sorting.

## Features

- **Default Date-Based Ordering**: Work experiences are automatically sorted by start date (newest first)
- **Custom Reordering**: Users can drag and drop to reorder experiences manually
- **Persistent Order**: Custom order is saved to the database and maintained across sessions
- **Reset Functionality**: Users can reset back to date-based sorting at any time

## How to Use

### 1. Enable Reorder Mode

- Click the "Reorder" button in the work experience section
- The page will enter reorder mode with visual indicators

### 2. Drag and Drop

- In reorder mode, each work experience becomes draggable
- Drag experiences to new positions to change their order
- The order is automatically saved to the database

### 3. Exit Reorder Mode

- Click "Done Reordering" to exit reorder mode
- Or click "Reorder" again to toggle off

### 4. Reset to Date Order

- While in reorder mode, click "Reset to Date Order"
- This will restore the default date-based sorting

## Technical Implementation

### Database Changes

- Added `sort_order` field to `work_experience` table
- Field defaults to 0 and is used for custom ordering

### Frontend Changes

- Drag and drop functionality using HTML5 Drag API
- Visual feedback during reordering (opacity changes, cursor changes)
- Automatic database updates when order changes

### Sorting Logic

1. **Primary**: Sort by `sort_order` (ascending)
2. **Fallback**: Sort by start date (newest first) when `sort_order` is not set

## Migration

To enable this feature, run the database migration:

```bash
./apply_migration.sh
```

Or manually apply the SQL migration:

```sql
-- Add sort_order field to work_experience table
ALTER TABLE work_experience ADD COLUMN sort_order INTEGER NOT NULL DEFAULT 0;

-- Create index for better performance when sorting
CREATE INDEX idx_work_experience_sort_order ON work_experience(profile_id, sort_order);

-- Update existing records to have sequential sort_order based on start_date (newest first)
UPDATE work_experience
SET sort_order = subquery.rn
FROM (
    SELECT id, ROW_NUMBER() OVER (PARTITION BY profile_id ORDER BY start_date DESC, created_at DESC) as rn
    FROM work_experience
) subquery
WHERE work_experience.id = subquery.id;
```

## User Experience

- **Intuitive**: Drag and drop interface familiar to most users
- **Responsive**: Immediate visual feedback during reordering
- **Safe**: Automatic saving with error handling and rollback
- **Flexible**: Easy to switch between custom and date-based ordering

## Browser Support

- Modern browsers with HTML5 Drag and Drop API support
- Graceful fallback for older browsers (maintains date-based sorting)
