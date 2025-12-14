# ZetaCuts

Proyecto web con React (Frontend) y Laravel (Backend)

## Estructura del Proyecto

```
ZetaCuts/
├── frontend/           # Aplicación React
├── backend/            # API Laravel
├── docs/              # Documentación
├── docker-compose.yml # Configuración Docker
└── README.md          # Este archivo
```

## Requisitos

- Node.js (v18+)
- PHP (v8.1+)
- Composer
- Docker (opcional)

## Inicio Rápido

### Frontend (React)
```bash
cd frontend
npm install
npm start
```

### Backend (Laravel)
```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan serve
```

## Desarrollo

1. El frontend se ejecuta en `http://localhost:3000`
2. El backend se ejecuta en `http://localhost:8000`

## Documentación

Ver la carpeta `docs/` para más información detallada.