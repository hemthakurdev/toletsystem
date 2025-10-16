# SaleMitra - Rental Management SaaS + Marketplace

A comprehensive platform that combines B2B SaaS for property owners/agents to manage rentals, tenants and finances — plus a B2C property discovery marketplace where public users can search and contact owners for rent/sale.

## 🚀 Features

### SaaS Dashboard (B2B)
- **Property Management**: Add, edit, and manage properties with detailed information
- **Tenant Management**: Track tenant information, lease agreements, and rent collection
- **Financial Management**: Generate invoices, track payments, and manage expenses
- **Lead Management**: Handle inquiries from the marketplace
- **Analytics & Reports**: Get insights into property performance
- **Multi-user Support**: Role-based access control for teams
- **Subscription Management**: Flexible pricing plans

### Public Marketplace (B2C)
- **Property Search**: Advanced search and filtering by location, price, amenities
- **Property Listings**: Detailed property pages with images and descriptions
- **Lead Generation**: Contact forms that create leads for property owners
- **Mobile-First Design**: Optimized for mobile users

## 🛠️ Technology Stack

- **Backend**: Laravel 10 (PHP 8.2+)
- **Frontend**: Vue.js 3 + Inertia.js
- **Database**: MySQL 8.0
- **Authentication**: Laravel Sanctum
- **Authorization**: Spatie Laravel Permission
- **File Storage**: Laravel Media Library
- **Activity Logging**: Spatie Laravel Activity Log
- **Payments**: Razorpay Integration
- **Containerization**: Docker

## 📋 Prerequisites

- PHP 8.2 or higher
- Composer
- Node.js 18+ and npm
- MySQL 8.0
- Redis (optional, for caching)

## 🚀 Quick Start

### Option 1: Docker (Recommended)

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd salemitra
   ```

2. **Start the containers**
   ```bash
   docker-compose up -d
   ```

3. **Install dependencies**
   ```bash
   docker-compose exec app composer install
   docker-compose exec app npm install
   ```

4. **Set up the application**
   ```bash
   docker-compose exec app cp .env.example .env
   docker-compose exec app php artisan key:generate
   docker-compose exec app php artisan migrate --seed
   docker-compose exec app npm run build
   ```

5. **Access the application**
   - Web: http://localhost:8080
   - API: http://localhost:8080/api/v1
   - Mailhog: http://localhost:8025

### Option 2: Local Development

1. **Clone and install dependencies**
   ```bash
   git clone <repository-url>
   cd salemitra
   composer install
   npm install
   ```

2. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Database setup**
   ```bash
   # Update .env with your database credentials
   php artisan migrate --seed
   ```

4. **Build assets**
   ```bash
   npm run build
   # or for development
   npm run dev
   ```

5. **Start the server**
   ```bash
   php artisan serve
   ```

## 📊 Database Schema

The application includes comprehensive database migrations for:

- **Organizations**: Multi-tenant organization management
- **Users**: User management with role-based permissions
- **Properties**: Property listings with amenities and availability
- **Tenants**: Tenant information and lease management
- **Invoices**: Invoice generation and tracking
- **Payments**: Payment processing and tracking
- **Leads**: Lead management from marketplace inquiries
- **Expenses**: Expense tracking and approval workflow
- **Subscriptions**: Subscription and billing management
- **Documents**: Document management and storage
- **Notifications**: In-app notification system

## 🔐 Authentication & Authorization

### User Roles
- **Super Admin**: Platform administration
- **Admin**: Organization administration
- **Staff**: Limited access based on permissions
- **Support**: Customer support access

### API Authentication
The API uses Laravel Sanctum for token-based authentication:

```bash
# Register
POST /api/v1/auth/register

# Login
POST /api/v1/auth/login

# Access protected routes
Authorization: Bearer {token}
```

## 📱 API Endpoints

### Public Endpoints
- `GET /api/v1/properties` - List published properties
- `GET /api/v1/properties/{id}` - Get property details
- `POST /api/v1/properties/{id}/contact` - Contact property owner

### Protected Endpoints (Requires Authentication)
- `GET /api/v1/org/properties` - Manage properties
- `GET /api/v1/org/tenants` - Manage tenants
- `GET /api/v1/org/invoices` - Manage invoices
- `GET /api/v1/org/leads` - Manage leads
- `GET /api/v1/org/analytics/dashboard` - View analytics

## 🎨 Frontend Development

The frontend is built with Vue.js 3 and Inertia.js:

```bash
# Development with hot reload
npm run dev

# Build for production
npm run build

# Watch for changes
npm run watch
```

### Component Structure
```
resources/js/
├── Pages/           # Inertia.js pages
├── Components/      # Reusable Vue components
├── Layouts/         # Page layouts
└── Utils/          # Helper functions
```

## 🔧 Configuration

### Environment Variables
Key environment variables to configure:

```env
# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=salemitra
DB_USERNAME=root
DB_PASSWORD=

# Mail
MAIL_MAILER=smtp
MAIL_HOST=localhost
MAIL_PORT=1025

# Payment Gateway
RAZORPAY_KEY_ID=
RAZORPAY_KEY_SECRET=

# File Storage
FILESYSTEM_DISK=local
```

## 🧪 Testing

```bash
# Run PHP tests
php artisan test

# Run frontend tests
npm run test
```

## 📈 Deployment

### Production Checklist
- [ ] Update environment variables
- [ ] Run database migrations
- [ ] Build frontend assets
- [ ] Set up SSL certificates
- [ ] Configure web server (Nginx/Apache)
- [ ] Set up monitoring and logging
- [ ] Configure backup strategy

### Docker Production
```bash
# Build production image
docker build -t salemitra:latest .

# Run with production settings
docker-compose -f docker-compose.prod.yml up -d
```

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 🆘 Support

For support and questions:
- Create an issue on GitHub
- Email: support@salemitra.com
- Documentation: https://docs.salemitra.com

## 🗺️ Roadmap

### Phase 1 (Current)
- [x] Basic property management
- [x] Tenant management
- [x] Invoice generation
- [x] Lead management
- [x] Basic analytics

### Phase 2 (Next)
- [ ] Payment gateway integration
- [ ] Advanced analytics
- [ ] Mobile app
- [ ] API marketplace
- [ ] White-label solutions

### Phase 3 (Future)
- [ ] AI-powered insights
- [ ] IoT integration
- [ ] Blockchain integration
- [ ] International expansion

---

**Built with ❤️ for property managers and landlords**