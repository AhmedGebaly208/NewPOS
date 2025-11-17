# Testing Guide - Phases 1-3

## Server Information
**Server Running**: http://localhost:8001  
**Admin Panel**: http://localhost:8001/admin

---

## Test Credentials

### Super Admin Account
- **Email**: admin@pos.com
- **Password**: password
- **Role**: super-admin
- **Access**: Full system access

---

## Phase 1 & 2 Testing: Foundation & Core Module

### 1. Verify Laravel Installation
```bash
# Check Laravel version
php artisan --version
# Expected: Laravel Framework 12.38.1

# Check modules
php artisan module:list
# Expected: Core [Enabled], User [Enabled]

# Check database connection
php artisan tinker
>>> DB::connection()->getPdo();
# Should show PDO object without error
```

### 2. Test Core Module Functions

#### Test Helper Functions
```bash
php artisan tinker

# Test currency formatting
>>> format_currency(1234.56)
# Expected: "$1,234.56"

# Test date formatting
>>> format_date(now())
# Expected: "2025-11-17 21:23:17" (or similar)

# Test reference number generation
>>> generate_reference_number('TEST')
# Expected: "TEST-20251117212317-ABC123" (format)

# Test discount calculation
>>> apply_discount(100, 10, 'percentage')
# Expected: 90.0

# Test tax calculation
>>> calculate_tax(100, 15)
# Expected: 15.0
```

#### Test Core Configuration
```bash
php artisan tinker

# Test Core config
>>> config('core.currency')
# Expected: "USD"

>>> config('core.pagination.per_page')
# Expected: 15

>>> config('core.date_format')
# Expected: "Y-m-d"
```

---

## Phase 3 Testing: User Module

### 1. Database Verification

```bash
php artisan tinker

# Check roles
>>> \Spatie\Permission\Models\Role::pluck('name')
# Expected: ["super-admin", "admin", "manager", "cashier"]

# Check permissions count
>>> \Spatie\Permission\Models\Permission::count()
# Expected: 39 (27 custom + 12 Shield)

# Check super admin user
>>> App\Models\User::first()
# Should show user with email: admin@pos.com

# Check user has role
>>> App\Models\User::first()->roles->pluck('name')
# Expected: ["super-admin"]

# Check user permissions
>>> App\Models\User::first()->hasPermissionTo('users.view')
# Expected: true
```

### 2. Admin Panel Access Test

**Step 1: Navigate to Login**
1. Open browser: http://localhost:8001/admin
2. You should see Filament login page

**Step 2: Login**
1. Enter email: `admin@pos.com`
2. Enter password: `password`
3. Click "Sign in"
4. Should redirect to admin dashboard

**Step 3: Verify Navigation**
Check that you can see:
- Dashboard
- User Management group
  - Users (with badge showing user count)

### 3. User Management Tests

#### Test 3.1: View Users List
1. Click on "Users" in navigation
2. Should show users table with:
   - Avatar column
   - Name column
   - Email column
   - Phone column
   - Roles badge(s)
   - Active status icon
   - Actions (View, Edit, Delete)

#### Test 3.2: Create New User
1. Click "New user" button
2. Fill in the form:
   - Name: "Test Manager"
   - Email: "manager@pos.com"
   - Phone: "+1234567890"
   - Password: "password"
   - Is Active: ON
   - Roles: Select "manager"
3. Click "Create"
4. Should see success notification
5. New user should appear in table

#### Test 3.3: Edit User
1. Click "Edit" on the test user
2. Modify fields (e.g., change phone number)
3. Click "Save changes"
4. Should see success notification
5. Changes should be reflected in table

#### Test 3.4: View User
1. Click "View" on any user
2. Should see all user details in read-only format
3. Should show Edit and Delete buttons

#### Test 3.5: Role Assignment
1. Edit a user
2. In "Roles & Permissions" section, select multiple roles
3. Save
4. Verify roles show as badges in table

#### Test 3.6: Soft Delete User
1. Click Delete on a user
2. Confirm deletion
3. User should disappear from default view
4. Apply "Trashed" filter
5. Deleted user should appear
6. Test "Restore" action
7. User should be restored

#### Test 3.7: Filters
1. Test "Filter by Roles" - select a role
2. Should show only users with that role
3. Test "Active" filter
   - Select "Active users only"
   - Should hide inactive users
4. Test "Trashed" filter
   - Should show deleted users

#### Test 3.8: Search
1. Use search bar to search by:
   - Name
   - Email
   - Phone
2. Results should filter instantly

#### Test 3.9: Bulk Actions
1. Select multiple users (checkboxes)
2. Test bulk delete
3. Verify all selected users are deleted

### 4. Authorization Tests

#### Test 4.1: Test Panel Access Control
```bash
php artisan tinker

# Create a cashier user (shouldn't access panel)
>>> $cashier = \App\Models\User::create([
    'name' => 'Test Cashier',
    'email' => 'cashier@pos.com',
    'password' => \Hash::make('password'),
    'is_active' => true
]);
>>> $cashier->assignRole('cashier');

# Test panel access
>>> $cashier->canAccessPanel(app(\Filament\Facades\Filament::class)->getCurrentPanel())
# Expected: false (cashier cannot access admin panel)
```

#### Test 4.2: Test Permissions
```bash
php artisan tinker

# Check manager permissions
>>> $manager = \App\Models\User::where('email', 'manager@pos.com')->first();
>>> $manager->hasPermissionTo('products.view')
# Expected: true

>>> $manager->hasPermissionTo('users.delete')
# Expected: false (managers can't delete users)

>>> $manager->hasPermissionTo('pos.access')
# Expected: true
```

### 5. User Model Tests

```bash
php artisan tinker

# Test isActive method
>>> $user = App\Models\User::first();
>>> $user->isActive()
# Expected: true

# Test active scope
>>> App\Models\User::active()->count()
# Expected: count of active users

# Test soft deletes
>>> $user = App\Models\User::find(2);
>>> $user->delete();
>>> App\Models\User::withTrashed()->find(2)->deleted_at
# Should show deletion timestamp

>>> $user->restore();
# User should be restored
```

---

## Integration Tests

### 1. Test Complete User Workflow
1. Login as super-admin
2. Create a new manager user
3. Logout
4. Try to login as the new manager
5. Verify manager can access admin panel
6. Verify manager sees appropriate navigation items
7. Verify manager cannot see Users management (no permission)

### 2. Test Role Hierarchy
```bash
php artisan tinker

# Super Admin - should have all permissions
>>> $superAdmin = App\Models\User::first();
>>> $superAdmin->getAllPermissions()->count()
# Expected: 39 (all permissions)

# Manager - should have limited permissions
>>> $manager = App\Models\User::where('email', 'manager@pos.com')->first();
>>> $manager->hasPermissionTo('users.delete')
# Expected: false

>>> $manager->hasPermissionTo('sales.create')
# Expected: true
```

### 3. Test Avatar Upload
1. Edit a user
2. Upload an avatar image
3. Save
4. Verify avatar appears in table
5. Check file exists: `storage/app/public/avatars/`

---

## Performance Tests

### 1. Query Performance
```bash
php artisan tinker

# Enable query log
>>> DB::enableQueryLog();

# Load users with roles
>>> App\Models\User::with('roles')->get();

# Check queries
>>> count(DB::getQueryLog())
# Should be minimal (2-3 queries with eager loading)
```

### 2. Page Load Time
1. Open browser developer tools (F12)
2. Navigate to Users page
3. Check Network tab
4. Page load should be < 2 seconds

---

## Error Handling Tests

### 1. Test Validation
1. Try creating user with:
   - Invalid email format → Should show error
   - Duplicate email → Should show "email already taken"
   - Empty required fields → Should show validation errors

### 2. Test Authentication
1. Logout
2. Try accessing http://localhost:8001/admin directly
3. Should redirect to login page
4. Try wrong credentials
5. Should show "These credentials do not match our records"

### 3. Test Inactive User
```bash
php artisan tinker

>>> $user = App\Models\User::where('email', 'manager@pos.com')->first();
>>> $user->update(['is_active' => false]);
```
1. Try logging in as manager@pos.com
2. Should be denied access (inactive user)

---

## Browser Testing Checklist

### UI/UX Tests
- [ ] Login page displays correctly
- [ ] Dashboard loads without errors
- [ ] Navigation menu is visible and clickable
- [ ] Users table displays data correctly
- [ ] Create form has all fields
- [ ] Edit form pre-fills data
- [ ] Delete confirmation dialog appears
- [ ] Success notifications show on actions
- [ ] Error messages display for validation
- [ ] Avatars display as circular images
- [ ] Role badges have colors
- [ ] Active/Inactive icons are clear
- [ ] Search works in real-time
- [ ] Filters apply correctly
- [ ] Pagination works (if > 15 users)
- [ ] Responsive design on mobile

### Functionality Tests
- [ ] Login works
- [ ] Logout works
- [ ] Create user works
- [ ] Edit user works
- [ ] Delete user works
- [ ] Restore user works
- [ ] Role assignment works
- [ ] Avatar upload works
- [ ] Password is hidden
- [ ] Password reveal toggle works
- [ ] Search filters results
- [ ] Role filter works
- [ ] Active filter works
- [ ] Trashed filter works
- [ ] Bulk actions work
- [ ] Sorting columns works

---

## Command Line Tests

### Test Artisan Commands
```bash
# Test module commands
php artisan module:list
php artisan module:enable User
php artisan module:disable User
php artisan module:enable User

# Test database
php artisan migrate:status
php artisan db:show

# Test cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize:clear

# Test queue (if using)
php artisan queue:work --once
```

---

## Database Inspection

### Check Tables
```bash
php artisan tinker

# List all tables
>>> DB::select('SHOW TABLES')

# Check users table structure
>>> DB::select('DESCRIBE users')

# Check permissions table
>>> DB::table('permissions')->count()
# Expected: 39

# Check roles table
>>> DB::table('roles')->count()
# Expected: 4

# Check role_has_permissions
>>> DB::table('role_has_permissions')->count()
# Expected: Many (based on assigned permissions)
```

---

## Expected Results Summary

### ✅ What Should Work
1. **Core Module**
   - All helper functions working
   - Configuration loading correctly
   - Module autoloaded properly

2. **User Module**
   - Login/Logout functional
   - User CRUD operations
   - Role assignment
   - Permission checking
   - Soft deletes
   - Avatar uploads
   - Filters and search

3. **Filament Admin**
   - Dashboard accessible
   - Navigation working
   - Forms validating
   - Tables displaying
   - Actions functioning
   - Notifications showing

4. **Security**
   - Passwords hashed
   - Unauthorized access blocked
   - Panel access controlled by role
   - Policies enforcing permissions

### ❌ Known Limitations (Not Yet Implemented)
- No products yet (Phase 4)
- No inventory system (Phase 5)
- No sales transactions (Phase 8)
- No POS interface (Phase 9)
- No reports (Phase 10)

---

## Troubleshooting

### Issue: Can't login
**Solution**: 
```bash
php artisan db:seed --class='Modules\User\Database\Seeders\SuperAdminSeeder'
```

### Issue: Permissions not working
**Solution**:
```bash
php artisan permission:cache-reset
php artisan optimize:clear
```

### Issue: Avatar not uploading
**Solution**:
```bash
php artisan storage:link
```

### Issue: CSS not loading
**Solution**:
```bash
npm run build
php artisan filament:upgrade
```

---

## After Testing

Once you've tested everything:

1. **Stop the server**: Press Ctrl+C in terminal or
```bash
# In another terminal
pkill -f "php artisan serve"
```

2. **Report any issues found**

3. **Ready for Phase 4**: If everything works, we can proceed to Product Module

---

## Quick Test Script

Run this to verify everything quickly:

```bash
# Quick verification
php artisan --version && \
php artisan module:list && \
php artisan route:list --path=admin/users | head -5 && \
php artisan tinker --execute="echo 'Users: ' . App\Models\User::count() . PHP_EOL; echo 'Roles: ' . Spatie\Permission\Models\Role::count() . PHP_EOL; echo 'Permissions: ' . Spatie\Permission\Models\Permission::count() . PHP_EOL;"
```

---

**Server URL**: http://localhost:8001/admin  
**Login**: admin@pos.com / password  
**Status**: ✅ Ready for Testing
