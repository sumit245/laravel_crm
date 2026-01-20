# DataTable Bug Report - streetlightTable-11
**URL**: http://localhost:8000/staff/91  
**Component**: datatable-wrapper-streetlightTable-11  
**Date**: $(date)

## Bugs Found

### 1. ✅ FIXED: Page Length Selector Mismatch
**Severity**: Medium  
**Status**: Fixed

**Issue**: 
- The page length dropdown had `200` hardcoded as selected
- The datatable was initialized with `pageLength="25"` 
- The dropdown was missing the `25` option
- This caused a mismatch between the displayed value and actual page length

**Fix Applied**:
- Added `25` option to the dropdown
- Made the selected option dynamic based on `$pageLength` prop
- All options now properly reflect the actual pageLength value

**Location**: `resources/views/components/datatable.blade.php` lines 211-217

---

### 2. FIXME: updatePaginationInfo Not Being Called
**Severity**: Low  
**Status**: Needs Verification

**Issue**:
- FIXME comment on line 909 and 1103 indicates `updatePaginationInfo()` function is not being called
- This function updates the pagination info text ("Showing X to Y of Z entries")

**Location**: `resources/views/components/datatable.blade.php` lines 910-933

**Note**: The function exists but may not be triggered on page load or page length change. Need to verify in browser.

---

### 3. Tab Visibility Initialization
**Severity**: Low  
**Status**: Needs Testing

**Issue**:
- Complex tab visibility logic for datatables inside tab panes
- May cause initialization issues if tab is not active when page loads
- Server-side tables in hidden tabs are deferred until tab is shown

**Location**: `resources/views/components/datatable.blade.php` lines 730-832

**Testing Required**: 
- Verify datatable initializes correctly when switching between project tabs
- Check if data loads properly when tab becomes active

---

## Testing Checklist

### Search Functionality
- [ ] Search input field is visible and functional
- [ ] Typing in search filters rows correctly
- [ ] Search works across all columns
- [ ] Search placeholder text displays correctly ("Search Sites...")
- [ ] Clear search works (if implemented)

### Sorting
- [ ] Clicking column headers sorts ascending
- [ ] Clicking again sorts descending  
- [ ] Sort indicators (arrows) display correctly
- [ ] All sortable columns work (#, State, District, Block, Panchayat, Ward, Total Poles, Surveyed, Installed)
- [ ] Actions column is not sortable

### Pagination
- [ ] Pagination controls are visible
- [ ] "First", "Previous", "Next", "Last" buttons work
- [ ] Page numbers are clickable
- [ ] Current page is highlighted
- [ ] Pagination info displays correctly ("Showing X to Y of Z entries")
- [ ] Page length selector works (25, 50, 100, 200, 500, All)
- [ ] Selected page length matches actual displayed rows

### Export Features
- [ ] Excel export button is visible and clickable
- [ ] Excel export downloads file with correct data
- [ ] PDF export button is visible and clickable
- [ ] PDF export generates file with correct data
- [ ] Print button is visible and clickable
- [ ] Print preview shows table correctly
- [ ] Exported files include all visible columns
- [ ] Exported files exclude hidden columns (Actions column)

### Column Visibility
- [ ] Columns button is visible
- [ ] Clicking Columns button shows dropdown
- [ ] Checkboxes in dropdown toggle column visibility
- [ ] Hidden columns are actually hidden
- [ ] Column visibility persists (localStorage)
- [ ] All columns can be toggled except Actions

### Data Display
- [ ] Table displays all rows correctly
- [ ] Row data matches expected values
- [ ] Ward links are clickable
- [ ] Surveyed count badges are clickable
- [ ] Installed count badges are clickable
- [ ] View button (eye icon) in Actions column works
- [ ] Row hover effect works
- [ ] Table is responsive on mobile

### Performance
- [ ] Table loads without significant delay
- [ ] Sorting is fast
- [ ] Search filtering is responsive
- [ ] Pagination switching is smooth
- [ ] No console errors

### Edge Cases
- [ ] Empty table state (if no data)
- [ ] Single page of data (no pagination needed)
- [ ] Very long text in cells (truncation)
- [ ] Special characters in search
- [ ] Rapid clicking on sort headers
- [ ] Changing page length while on last page

---

## Browser Testing Instructions

1. **Login**:
   - Navigate to http://localhost:8000/login
   - Email: admin@sugslloyd.com
   - Password: password123

2. **Navigate to Staff Page**:
   - Go to http://localhost:8000/staff/91
   - Wait for page to load completely

3. **Find the DataTable**:
   - Scroll to "Project Tabs" section
   - Click on the project tab that contains streetlight data
   - Locate datatable with wrapper ID: `datatable-wrapper-streetlightTable-11`

4. **Test Each Feature**:
   - Follow the checklist above
   - Document any errors in browser console
   - Take screenshots of any visual bugs

---

## Console Commands for Testing

Open browser console (F12) and run:

```javascript
// Check if DataTable is initialized
const table = $('#streetlightTable-11').DataTable();
console.log('Table initialized:', !!table);

// Get current page length
console.log('Page length:', table.page.len());

// Get total records
console.log('Total records:', table.page.info().recordsTotal);

// Check for errors
console.log('DataTable errors:', table.settings()[0].aoDrawCallback);
```

---

## Notes

- The datatable is inside a Bootstrap tab pane, which may affect initialization timing
- Export buttons use DataTables Buttons extension
- Column visibility uses localStorage for persistence
- The table has `pageLength="25"` set in the view
