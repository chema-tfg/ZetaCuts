# Guía de Configuración - ZetaCuts

## Configuración Inicial

### 1. Configurar Backend (Laravel)

```bash
cd backend

# Instalar Laravel
composer create-project laravel/laravel .

# Configurar entorno
cp .env.example .env
php artisan key:generate

# Configurar base de datos en .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=zetacuts
DB_USERNAME=root
DB_PASSWORD=

# Migrar base de datos
php artisan migrate

# Instalar Sanctum para autenticación
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate

# Configurar CORS
composer require fruitcake/laravel-cors
```

### 2. Configurar Frontend (React)

```bash
cd frontend

# Crear aplicación React con TypeScript
npx create-react-app . --template typescript

# O usar Vite (más rápido)
npm create vite@latest . -- --template react-ts

# Instalar dependencias
npm install axios react-router-dom

# Instalar Tailwind CSS
npm install -D tailwindcss postcss autoprefixer
npx tailwindcss init -p
```

### 3. Configurar Docker (Opcional)

```bash
# En la raíz del proyecto
docker-compose up -d
```

## Configuración de Desarrollo

### Variables de Entorno

#### Backend (.env)
```
APP_URL=http://localhost:8000
FRONTEND_URL=http://localhost:3000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=zetacuts
DB_USERNAME=root
DB_PASSWORD=

SANCTUM_STATEFUL_DOMAINS=localhost:3000
```

#### Frontend (.env)
```
REACT_APP_API_URL=http://localhost:8000/api
```

## Estructura de Base de Datos

### Tablas Principales
- `users` - Usuarios del sistema
- `personal_access_tokens` - Tokens de autenticación (Sanctum)

## Flujo de Desarrollo

1. Iniciar backend: `cd backend && php artisan serve`
2. Iniciar frontend: `cd frontend && npm start`
3. Abrir navegador en `http://localhost:3000`