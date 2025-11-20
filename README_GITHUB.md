# �� NewPOS - Modern Point of Sale System

A comprehensive Point of Sale (POS) application built with Laravel 11, Filament 3, and a modular architecture designed for scalability and maintainability.

[![Laravel](https://img.shields.io/badge/Laravel-11.x-red.svg)](https://laravel.com)
[![Filament](https://img.shields.io/badge/Filament-3.x-orange.svg)](https://filamentphp.com)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)

## 📋 Project Overview

NewPOS is a feature-rich point of sale system designed for small to medium-sized retail businesses. Built with modern PHP frameworks and best practices, it provides a complete solution for managing products, inventory, sales, and customers through an intuitive admin panel.

### 🎯 Key Features

- **Modular Architecture**: Clean separation of concerns with Laravel Modules
- **Modern Admin Panel**: Beautiful UI powered by Filament 3
- **Role-Based Access Control**: Granular permissions with Spatie Laravel Permission
- **Real-time Inventory Tracking**: Automatic stock movement logging
- **Purchase Order Management**: Advanced PO system with line items
- **Audit Trail**: Complete tracking of who created, updated, or deleted records
- **Soft Deletes**: Safe data management with restore capabilities

## 🏗️ Architecture

### Tech Stack

- **Backend**: Laravel 11.x
- **Admin Panel**: Filament 3.x
- **Database**: MySQL 8.0+
- **CSS Framework**: Tailwind CSS (via Filament)
- **Permissions**: Spatie Laravel Permission
- **Module System**: nwidart/laravel-modules

### Design Patterns

- Repository Pattern
- Service Layer Pattern
- Event-Driven Architecture
- Modular Monolithic Architecture

## 📦 Modules

### ✅ Completed (4/9)

#### 1. Core Module
Foundation module providing base classes and shared functionality:
- BaseModel with UUID support and audit trail
- BaseRepository with full CRUD operations
- BaseService with transaction management
- BaseResource for Filament
- Helper functions (currency, dates, references)
- Exception handling
- Event/Listener infrastructure

#### 2. User Module
Complete authentication and authorization system:
- User management with Filament
- 4 roles: Super Admin, Admin, Manager, Cashier
- 27 granular permissions
- Filament Shield integration
- Role-based panel access control
- User activity tracking

#### 3. Product Module
Product and category management:
- Products with SKU and barcode
- Hierarchical categories
- Multiple product images
- Product variants (size, color)
- Tax rate configuration
- Stock tracking
- Auto-generate SKU and barcodes

#### 4. Inventory Module
Comprehensive inventory management:
- Supplier management
- Purchase orders with line items
- Automatic stock movement tracking
- Real-time inventory calculations
- Stock adjustment capabilities
- Low stock and out-of-stock tracking
- Inventory service with business logic

### 🔄 In Progress

- **Customer Module** - Customer management and purchase history

### 📅 Planned

- **Discount Module** - Discounts, coupons, and promotions
- **Sales Module** - Transaction processing and payment handling
- **POS Module** - Custom Livewire point of sale interface
- **Report Module** - Analytics and comprehensive reporting

## 🚀 Getting Started

### Prerequisites

- PHP 8.2 or higher
- Composer
- MySQL 8.0+
- Node.js & NPM
- Redis (optional, for cache/queues)

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/NewPOS.git
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

4. **Configure database**
   Edit `.env` and set your database credentials:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=pos_laravel
   DB_USERNAME=root
   DB_PASSWORD=your_password
   ```

5. **Run migrations and seeders**
   ```bash
   php artisan migrate
   php artisan db:seed --class='Modules\User\Database\Seeders\RolesAndPermissionsSeeder'
   php artisan db:seed --class='Modules\User\Database\Seeders\SuperAdminSeeder'
   ```

6. **Build assets**
   ```bash
   npm run build
   ```

7. **Create storage link**
   ```bash
   php artisan storage:link
   ```

8. **Start the development server**
   ```bash
   php artisan serve
   ```

9. **Access the admin panel**
   - URL: http://localhost:8000/admin
   - Email: admin@pos.com
   - Password: password

## 📚 Documentation

Detailed documentation for each phase is available in the repository:

- [SETUP.md](SETUP.md) - Complete setup guide
- [TESTING_GUIDE.md](TESTING_GUIDE.md) - Testing instructions
- [ROLE_RESOURCE_GUIDE.md](ROLE_RESOURCE_GUIDE.md) - Permissions guide
- [PHASE1_REPORT.md](PHASE1_REPORT.md) - Foundation & Setup
- [PHASE2_REPORT.md](PHASE2_REPORT.md) - Core Module
- [PHASE3_REPORT.md](PHASE3_REPORT.md) - User Module
- [PHASE5_REPORT.md](PHASE5_REPORT.md) - Inventory Module

## 🎨 Features Showcase

### Purchase Order System
- Dynamic line items with repeater fields
- Live calculation of subtotals, tax, and totals
- Auto-populate product costs from catalog
- Status workflow management
- Quick-create suppliers without leaving form

### Inventory Tracking
- Automatic stock movement logging
- Immutable audit trail
- Support for multiple movement types
- Before/after quantity tracking
- Polymorphic references to source documents

### User Management
- Complete CRUD operations
- Role assignment interface
- Avatar upload support
- Active/inactive status toggle
- Soft delete with restore capability

## 🔐 Security Features

- ✅ Full audit trail (created_by, updated_by, deleted_by)
- ✅ Role-based access control
- ✅ Soft deletes for data safety
- ✅ Transaction safety in critical operations
- ✅ CSRF protection
- ✅ Password hashing with Bcrypt
- ✅ SQL injection prevention via Eloquent ORM

## 🧪 Testing

```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit
```

## 📊 Database Schema

The application uses a modular database design with the following main tables:

- **users** - User accounts and authentication
- **roles** & **permissions** - RBAC system
- **categories** - Hierarchical product categories
- **products** - Product catalog
- **suppliers** - Supplier information
- **purchase_orders** - Purchase orders
- **purchase_order_items** - PO line items
- **stock_movements** - Inventory movement log

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 👥 Authors

- **Your Name** - *Initial work*

## 🙏 Acknowledgments

- Laravel Framework
- Filament PHP
- Spatie Laravel Permission
- nwidart Laravel Modules
- All contributors to the open-source packages used

## 📞 Support

For support, email your-email@example.com or open an issue in the repository.

## 🗺️ Roadmap

- [x] Core Module
- [x] User Module
- [x] Product Module
- [x] Inventory Module
- [ ] Customer Module
- [ ] Discount Module
- [ ] Sales Module
- [ ] POS Module
- [ ] Report Module
- [ ] API Development
- [ ] Mobile App Integration
- [ ] Multi-location Support

---

**Built with ❤️ using Laravel and Filament**
