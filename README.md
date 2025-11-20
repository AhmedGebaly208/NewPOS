# POS Application

A modern Point of Sale (POS) application built with Laravel 11, Filament 3, and a modular architecture for retail operations.

## 🚀 Features

- **Modern Admin Panel**: Built with Filament 3.x for intuitive management
- **Modular Architecture**: Using nwidart/laravel-modules for scalable development
- **Role-Based Access Control**: Spatie Laravel Permission for granular permissions
- **Real-Time Updates**: Livewire for reactive user interfaces
- **Enterprise Ready**: MySQL database with proper indexing and optimization

## 📋 Tech Stack

- **Backend**: Laravel 11.x
- **Admin Panel**: Filament 3.3.45
- **Database**: MySQL 8.0+
- **Frontend**: Livewire 3.x, Tailwind CSS
- **Permissions**: Spatie Laravel Permission 6.23
- **Module System**: nwidart/laravel-modules 12.0
- **PHP Version**: 8.2+

## 📦 Installation

1. **Clone the repository**
```bash
git clone https://github.com/AhmedGebaly208/NewPOS.git
cd NewPOS
```

2. **Install dependencies**
```bash
composer install
npm install
```

3. **Environment setup**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Configure database** in `.env`
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pos_laravel
DB_USERNAME=root
DB_PASSWORD=
```

5. **Run migrations**
```bash
php artisan migrate
```

6. **Create admin user**
```bash
php artisan make:filament-user
```

7. **Start development server**
```bash
php artisan serve
```

Access the admin panel at: `http://localhost:8000/admin`

## 🏗️ Project Structure

```
NewPOS/
├── app/
│   ├── Models/
│   ├── Providers/
│   │   └── Filament/
│   │       └── AdminPanelProvider.php
│   └── Http/
├── Modules/              # Modular features
│   ├── Core/            # Foundation module
│   ├── User/            # User management
│   ├── Product/         # Product catalog
│   └── Inventory/       # Stock management
├── config/
│   ├── permission.php
│   └── modules.php
├── database/
│   ├── migrations/
│   └── seeders/
└── tests/
```

## 🔐 Default Roles

- **Super Admin**: Full system access
- **Admin**: All features except system settings
- **Manager**: Sales, inventory, reports, customers
- **Cashier**: POS access and basic operations

## 📝 License

This project is proprietary software. All rights reserved.

---

**Version**: 1.0.0  
**Status**: Development  
**Last Updated**: November 20, 2025
