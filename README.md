# Task Management System

A full-stack task management application built with Laravel (backend) and Vue 3 (frontend), featuring modern development practices, security measures, and functionality.

## 🚀 Features

### Backend (Laravel)
- **Authentication**: Laravel Sanctum for SPA authentication
- **Task Management**: Complete CRUD operations with drag-and-drop reordering
- **Admin Dashboard**: User management and system-wide task overview
- **Security**: Input sanitization, request validation, CSRF protection
- **Architecture**: Service Layer and Repository Pattern implementation
- **Testing**: Comprehensive unit and feature tests
- **Scheduled Tasks**: Automated cleanup of old tasks
- **API Documentation**: RESTful API with proper resource serialization

### Frontend (Vue 3)
- **Modern Vue 3**: Composition API with Pinia state management
- **Responsive Design**: TailwindCSS for mobile, tablet, and desktop
- **Drag & Drop**: Task reordering with real-time backend updates
- **Real-time Features**: Socket.io integration for instant updates across admin and user views
- **Authentication**: Secure login/registration with route protection
- **Admin Panel**: User management and system statistics
- **Search & Filtering**: Advanced task filtering and search capabilities
- **Pagination**: Client-side pagination with configurable items per page

## 🛠 Tech Stack

### Backend
- **Framework**: Laravel 11 (latest stable)
- **Database**: MySQL
- **Authentication**: Laravel Sanctum
- **Testing**: PHPUnit
- **Architecture**: PSR-12, SOLID principles
- **Caching**: Laravel Cache for performance optimization

### Frontend
- **Framework**: Vue 3 with Composition API
- **State Management**: Pinia
- **Styling**: TailwindCSS
- **Build Tool**: Vite
- **HTTP Client**: Axios
- **Drag & Drop**: VueDraggable
- **Notifications**: Vue Toastification
- **Real-time**: Socket.io client for bidirectional communication

### Real-time Features
- **Socket.io Server**: Node.js server for WebSocket connections
- **Bidirectional Updates**: Changes reflect instantly across admin and user views
- **Event Broadcasting**: Task creation, updates, deletion, and status changes
- **Room Management**: Separate channels for admin and user communications

## 📋 Prerequisites

- PHP 8.1 or higher
- Composer
- Node.js 16+ and npm
- MySQL 5.7+ or 8.0+
- Git

## 🚀 Installation & Setup

### 1. Clone the Repository
```bash
git clone <repository-url>
cd task-management-system
```

### 2. Backend Setup (Laravel)

```bash
# Navigate to backend directory
cd backend

# Install PHP dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure database in .env file
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=task_management
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Run database migrations
php artisan migrate

# Seed the database with sample data
php artisan db:seed

# Start the Laravel development server
php artisan serve

# Start the socket.io server for real-time updates
node socket-server.js
```

The backend will be available at `http://127.0.0.1:8000`
The socket.io server will be available at `http://127.0.0.1:3000`

### 3. Frontend Setup (Vue 3)

```bash
# Navigate to frontend directory
cd frontend

# Install Node.js dependencies
npm install

# Start the development server
npm run dev
```

The frontend will be available at `http://localhost:3001`

## 🔐 Demo Credentials

### Administrator Account
- **Email**: admin@example.com
- **Password**: password

### Regular User Account
- **Email**: test@example.com
- **Password**: password

## 📚 API Documentation

### Authentication Endpoints
- `POST /api/auth/register` - User registration
- `POST /api/auth/login` - User login
- `POST /api/auth/logout` - User logout
- `GET /api/auth/user` - Get authenticated user

### Task Management Endpoints
- `GET /api/tasks` - Get user's tasks (with filtering)
- `POST /api/tasks` - Create new task
- `GET /api/tasks/{id}` - Get specific task
- `PUT /api/tasks/{id}` - Update task
- `DELETE /api/tasks/{id}` - Delete task
- `POST /api/tasks/{id}/complete` - Mark task as completed
- `POST /api/tasks/{id}/pending` - Mark task as pending
- `POST /api/tasks/reorder` - Reorder tasks

### Admin Endpoints (Admin only)
- `GET /api/admin/dashboard` - Admin dashboard statistics
- `GET /api/admin/users` - Get all users with task counts
- `GET /api/admin/tasks` - Get all tasks (paginated)
- `GET /api/admin/users/{id}/tasks` - Get specific user's tasks

## 🏗 Architecture

### Backend Architecture
```
backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/Api/     # API Controllers
│   │   ├── Middleware/          # Custom Middleware
│   │   ├── Requests/            # Form Request Validation
│   │   └── Resources/           # API Resources
│   ├── Models/                  # Eloquent Models
│   ├── Repositories/            # Repository Pattern
│   ├── Services/                # Business Logic Layer
│   └── Jobs/                    # Background Jobs
├── database/
│   ├── migrations/              # Database Migrations
│   ├── seeders/                 # Database Seeders
│   └── factories/               # Model Factories
├── tests/                       # Unit & Feature Tests 
```

### Frontend Architecture
```
frontend/
├── src/
│   ├── components/
│   │   ├── layout/              # Layout Components
│   │   └── ui/                  # Reusable UI Components
│   ├── views/
│   │   ├── auth/                # Authentication Views
│   │   └── admin/               # Admin Views
│   ├── stores/                  # Pinia Stores
│   ├── services/                # API Services
│   └── router/                  # Vue Router Configuration
└── public/                      # Static Assets
```

## 🧪 Testing

### Backend Tests
```bash
cd backend

# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit

# Run with coverage
php artisan test --coverage
```

### Test Coverage
- Task CRUD operations
- Task reordering functionality
- Authentication flows
- Admin middleware
- API endpoints validation

## 🔒 Security Features

- **Input Sanitization**: All user inputs are sanitized and validated
- **CSRF Protection**: Cross-Site Request Forgery protection enabled
- **XSS Prevention**: Output escaping and content security policies
- **SQL Injection Prevention**: Eloquent ORM with parameter binding
- **Authentication**: Secure token-based authentication with Sanctum
- **Authorization**: Role-based access control for admin features
- **Password Hashing**: Bcrypt password hashing
- **Rate Limiting**: API rate limiting to prevent abuse

## 📱 Responsive Design

The application is fully responsive and optimized for:
- **Mobile**: 320px - 768px
- **Tablet**: 768px - 1024px
- **Desktop**: 1024px and above

## 🎨 UI/UX Features

- **Drag & Drop**: Intuitive task reordering
- **Real-time Updates**: Instant feedback with toast notifications
- **Loading States**: Smooth loading indicators
- **Form Validation**: Client-side and server-side validation
- **Accessibility**: ARIA labels and keyboard navigation support
- **Dark Mode Ready**: CSS custom properties for easy theming

## 🔧 Development Commands

### Backend Commands
```bash
# Generate new migration
php artisan make:migration create_example_table

# Generate new model with factory and migration
php artisan make:model Example -mf

# Generate new controller
php artisan make:controller Api/ExampleController --api

# Run scheduled tasks manually
php artisan schedule:run

# Clear application cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

### Frontend Commands
```bash
# Build for production
npm run build

# Preview production build
npm run preview

# Lint code
npm run lint

# Type check (if using TypeScript)
npm run type-check
```

## 🚀 Deployment

### Backend Deployment
1. Set up production environment variables
2. Run `composer install --optimize-autoloader --no-dev`
3. Run `php artisan config:cache`
4. Run `php artisan route:cache`
5. Run `php artisan view:cache`
6. Set up cron job for scheduled tasks:
   ```bash
   * * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
   ```

### Frontend Deployment
1. Update API base URL in production
2. Run `npm run build`
3. Deploy `dist/` folder to web server
4. Configure web server for SPA routing





**Built with ❤️ using Laravel and Vue.js Bigboss raphael Kasier Goyena Hire me❤️**
