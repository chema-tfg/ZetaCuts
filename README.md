# ZetaCuts

Proyecto web con React (Frontend) y Laravel (Backend)

## ⚠️ IMPORTANTE: Instalación Inicial

**Al descargar el proyecto desde GitHub, es necesario instalar las dependencias primero.**

### ¿Por qué?
- El proyecto NO incluye `node_modules/` ni `vendor/` (son demasiado pesados)
- Estas carpetas se generan automáticamente al instalar las dependencias
- **Siempre debes ejecutar `npm install` y `composer install` después de descargar**

## Estructura del Proyecto

```
ZetaCuts/
├── frontend/           # Aplicación React
├── backend/            # API Laravel
├── docs/              # Documentación
├── docker-compose.yml # Configuración Docker
└── README.md          # Este archivo
```

## Requisitos Previos

- **Node.js** (v18 o superior) - [Descargar](https://nodejs.org/)
- **PHP** (v8.1 o superior) - [Descargar](https://www.php.net/)
- **Composer** - [Descargar](https://getcomposer.org/)
- **MySQL/MariaDB** (para la base de datos)
- **Git** (para clonar el repositorio)

## 🚀 Instalación Paso a Paso

### 1. Clonar o Descargar el Proyecto

```bash
git clone https://github.com/tu-usuario/ZetaCuts.git
cd ZetaCuts
```

### 2. Instalar Dependencias del Frontend

```bash
cd frontend
npm install
```

**Esto instalará todas las dependencias necesarias (react, react-scripts, etc.)**

### 3. Instalar Dependencias del Backend

```bash
cd ../backend
composer install
```

### 4. Configurar el Backend

```bash
# Copiar archivo de configuración
cp .env.example .env

# Generar clave de aplicación
php artisan key:generate

# Configurar la base de datos en el archivo .env
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=zetacuts
# DB_USERNAME=root
# DB_PASSWORD=

# Ejecutar migraciones
php artisan migrate

# (Opcional) Poblar la base de datos con datos de ejemplo
php artisan db:seed
```

### 5. Iniciar los Servidores

**Terminal 1 - Frontend:**
```bash
cd frontend
npm start
```
El frontend estará disponible en: `http://localhost:3000`

**Terminal 2 - Backend:**
```bash
cd backend
php artisan serve
```
El backend estará disponible en: `http://localhost:8000`

## 🔧 Solución de Problemas Comunes

### Error: "react-scripts no se reconoce"
**Solución:** Ejecuta `npm install` en la carpeta `frontend/`

### Error: "Class not found" en Laravel
**Solución:** Ejecuta `composer install` en la carpeta `backend/`

### Error de conexión a la base de datos
**Solución:** Verifica la configuración en `backend/.env` y asegúrate de que MySQL esté corriendo

## 📝 Notas Importantes

- **Nunca subas `node_modules/` o `vendor/` a Git** (están en `.gitignore`)
- **Siempre ejecuta `npm install` después de clonar/descargar**
- **Siempre ejecuta `composer install` después de clonar/descargar**
- El archivo `package-lock.json` SÍ está en el repositorio para garantizar versiones consistentes

## Desarrollo

1. El frontend se ejecuta en `http://localhost:3000`
2. El backend se ejecuta en `http://localhost:8000`
3. Asegúrate de que ambos servidores estén corriendo simultáneamente

## Documentación

Ver la carpeta `docs/` para más información detallada.