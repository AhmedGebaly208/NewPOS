# Role Resource - Location & Customization Guide

## 📍 Current Setup

### Package-Provided Resource
The Role resource is **NOT** in your codebase. It's provided by Filament Shield package.

**Location:** `vendor/bezhansalleh/filament-shield/src/Resources/RoleResource.php`

**Configuration:** `config/filament-shield.php`

---

## ⚙️ Current Configuration

```php
'shield_resource' => [
    'should_register_navigation' => true,
    'slug' => 'shield/roles',
    'navigation_sort' => 2,
    'navigation_badge' => true,
    'navigation_group' => 'User Management',  // ✅ Updated
    'is_globally_searchable' => false,
],

'super_admin' => [
    'enabled' => true,
    'name' => 'super-admin',  // ✅ Updated to match our convention
    'define_via_gate' => false,
    'intercept_gate' => 'before',
],
```

---

## 🎨 Customization Options

### Option 1: Config Changes (Simple) ✅ Recommended

Edit `config/filament-shield.php`:

```php
'shield_resource' => [
    // Show/hide in navigation
    'should_register_navigation' => true,
    
    // URL slug
    'slug' => 'shield/roles',
    
    // Position in navigation
    'navigation_sort' => 2,
    
    // Show badge with count
    'navigation_badge' => true,
    
    // Navigation group
    'navigation_group' => 'User Management',
    
    // Make globally searchable (Cmd+K)
    'is_globally_searchable' => false,
],
```

**Available Quick Changes:**
- Change navigation order
- Change URL slug
- Show/hide navigation item
- Change navigation group
- Enable global search

---

### Option 2: Publish Resource (Full Control)

**Publish the resource to your app:**

```bash
php artisan vendor:publish --tag="filament-shield-resource"
```

This creates:
```
app/Filament/Resources/
└── RoleResource.php
    └── Pages/
        ├── CreateRole.php
        ├── EditRole.php
        ├── ListRoles.php
        └── ViewRole.php
```

**Then you can customize:**
- Navigation icon
- Navigation label
- Form layout
- Table columns
- Filters
- Actions
- Permissions display

**Example Customizations:**

```php
// After publishing: app/Filament/Resources/RoleResource.php

class RoleResource extends Resource
{
    // Change icon
    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    
    // Change label
    protected static ?string $navigationLabel = 'Manage Roles';
    
    // Change model label
    protected static ?string $modelLabel = 'Role';
    protected static ?string $pluralModelLabel = 'Roles';
    
    // Customize table
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Add custom columns
                Tables\Columns\TextColumn::make('name')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('users_count')
                    ->counts('users')
                    ->label('Users'),
                    
                Tables\Columns\TextColumn::make('permissions_count')
                    ->counts('permissions')
                    ->label('Permissions'),
            ])
            ->filters([
                // Add custom filters
            ]);
    }
}
```

---

### Option 3: Extend Resource (Flexible)

Create a new resource that extends Shield's:

```php
// app/Filament/Resources/CustomRoleResource.php

namespace App\Filament\Resources;

use BezhanSalleh\FilamentShield\Resources\RoleResource as ShieldRoleResource;

class CustomRoleResource extends ShieldRoleResource
{
    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    
    protected static ?string $navigationLabel = 'Custom Roles';
    
    // Override only what you need
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('name', '!=', 'super-admin')->count();
    }
}
```

Then disable the original in config:
```php
'shield_resource' => [
    'should_register_navigation' => false,  // Disable Shield's resource
],
```

---

## 🔑 What You Control

### Via Config (No Code Changes)
- ✅ Navigation visibility
- ✅ Navigation group
- ✅ Navigation sort order
- ✅ URL slug
- ✅ Badge display
- ✅ Global search
- ✅ Super admin role name

### Via Published Resource (Full Customization)
- ✅ Navigation icon
- ✅ Navigation label
- ✅ Table columns
- ✅ Form fields
- ✅ Filters
- ✅ Actions
- ✅ Permissions layout
- ✅ Validation rules
- ✅ Authorization

---

## 📋 Current Features (Out of the Box)

✅ **Role Management:**
- Create, edit, delete roles
- Assign permissions to roles
- View users per role
- Protected super-admin role

✅ **Permission Display:**
- Grouped by resource type
- Checkboxes for easy selection
- "Select All" per resource
- Visual feedback

✅ **Table Features:**
- Search by role name
- Sort by columns
- Filter by permissions
- Badge showing user count

✅ **Security:**
- Super admin can't be deleted
- Super admin can't be edited (name)
- Policy-based authorization
- Guard name support

---

## 🎯 Common Customizations

### Change Navigation Icon

**Without Publishing:**
Not available in config (Shield limitation)

**After Publishing:**
```php
protected static ?string $navigationIcon = 'heroicon-o-shield-check';
```

### Change Navigation Label

**After Publishing:**
```php
protected static ?string $navigationLabel = 'System Roles';
```

### Add Custom Column

**After Publishing:**
```php
Tables\Columns\TextColumn::make('created_at')
    ->label('Created')
    ->date()
    ->sortable(),
```

### Hide Super Admin from List

**After Publishing:**
```php
public static function getEloquentQuery(): Builder
{
    return parent::getEloquentQuery()
        ->where('name', '!=', config('filament-shield.super_admin.name'));
}
```

---

## 🚀 Recommended Approach

**For Now:** Use config only (already done ✅)
- Quick
- Easy to maintain
- Survives package updates

**Later (If Needed):** Publish and customize
- Full control
- Custom UI
- Business-specific features

---

## 📝 Files You Control

**Currently:**
```
config/filament-shield.php  ← Configuration
app/Policies/RolePolicy.php ← Authorization
```

**After Publishing:**
```
app/Filament/Resources/RoleResource.php      ← Main resource
app/Filament/Resources/RoleResource/Pages/   ← All pages
app/Policies/RolePolicy.php                  ← Authorization
```

---

## 🎓 Summary

**Role Resource Location:**
- Package: `vendor/bezhansalleh/filament-shield/`
- Config: `config/filament-shield.php` ✅
- Policy: `app/Policies/RolePolicy.php` ✅

**Customization Status:**
- ✅ Navigation group: "User Management"
- ✅ Navigation sort: 2 (after Users)
- ✅ Super admin name: "super-admin"
- ✅ Badge enabled: Shows role count

**To Customize Further:**
Run: `php artisan vendor:publish --tag="filament-shield-resource"`

---

Need help with specific customization? Let me know! 🎨
