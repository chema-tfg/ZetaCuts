# ZetaCuts - Database Schema

## Overview
ZetaCuts es una aplicación de gestión de barberías con sistema de puntos de fidelización. La base de datos está diseñada para soportar múltiples barberías, barberos y usuarios.

## Entidades principales

### 1. Users (Usuarios)
```sql
users {
  id: PRIMARY KEY (UUID)
  name: VARCHAR(100) NOT NULL
  email: VARCHAR(255) UNIQUE NOT NULL
  phone: VARCHAR(20)
  password_hash: VARCHAR(255) NOT NULL
  points: INTEGER DEFAULT 0
  created_at: TIMESTAMP DEFAULT NOW()
  updated_at: TIMESTAMP DEFAULT NOW()
}
```

### 2. Barbershops (Barberías)
```sql
barbershops {
  id: PRIMARY KEY (UUID)
  business_name: VARCHAR(200) NOT NULL
  owner_name: VARCHAR(100) NOT NULL
  email: VARCHAR(255) UNIQUE NOT NULL
  phone: VARCHAR(20)
  address: TEXT
  password_hash: VARCHAR(255) NOT NULL
  created_at: TIMESTAMP DEFAULT NOW()
  updated_at: TIMESTAMP DEFAULT NOW()
}
```

### 3. Barbers (Barberos)
```sql
barbers {
  id: PRIMARY KEY (UUID)
  barbershop_id: UUID FOREIGN KEY REFERENCES barbershops(id)
  name: VARCHAR(100) NOT NULL
  email: VARCHAR(255)
  specialty: VARCHAR(255)
  is_active: BOOLEAN DEFAULT true
  created_at: TIMESTAMP DEFAULT NOW()
  updated_at: TIMESTAMP DEFAULT NOW()
}
```

### 4. Services (Servicios)
```sql
services {
  id: PRIMARY KEY (UUID)
  name: VARCHAR(100) NOT NULL
  points_value: INTEGER NOT NULL
  duration_minutes: INTEGER NOT NULL
  description: TEXT
}

-- Servicios predefinidos:
-- 'Corte normal' - 10 puntos - 30 minutos
-- 'Corte con barba' - 15 puntos - 45 minutos
```

### 5. Appointments (Citas)
```sql
appointments {
  id: PRIMARY KEY (UUID)
  user_id: UUID FOREIGN KEY REFERENCES users(id)
  barbershop_id: UUID FOREIGN KEY REFERENCES barbershops(id)
  barber_id: UUID FOREIGN KEY REFERENCES barbers(id) -- NULL si no importa el barbero
  service_id: UUID FOREIGN KEY REFERENCES services(id)
  appointment_date: DATE NOT NULL
  appointment_time: TIME NOT NULL
  status: ENUM('pending', 'confirmed', 'completed', 'cancelled') DEFAULT 'pending'
  points_earned: INTEGER DEFAULT 0
  is_free_cut: BOOLEAN DEFAULT false -- true si se usa puntos para corte gratis
  notes: TEXT
  created_at: TIMESTAMP DEFAULT NOW()
  updated_at: TIMESTAMP DEFAULT NOW()
}
```

### 6. Barber_Schedules (Horarios de barberos)
```sql
barber_schedules {
  id: PRIMARY KEY (UUID)
  barber_id: UUID FOREIGN KEY REFERENCES barbers(id)
  day_of_week: INTEGER NOT NULL (0=domingo, 1=lunes, ..., 6=sábado)
  start_time: TIME NOT NULL
  end_time: TIME NOT NULL
  is_available: BOOLEAN DEFAULT true
  created_at: TIMESTAMP DEFAULT NOW()
}
```

### 7. Barbershop_Hours (Horarios de barbería)
```sql
barbershop_hours {
  id: PRIMARY KEY (UUID)
  barbershop_id: UUID FOREIGN KEY REFERENCES barbershops(id)
  day_of_week: INTEGER NOT NULL (0=domingo, 1=lunes, ..., 6=sábado)
  start_time: TIME NOT NULL
  end_time: TIME NOT NULL
  is_open: BOOLEAN DEFAULT true
  created_at: TIMESTAMP DEFAULT NOW()
}
```

### 8. Points_History (Historial de puntos)
```sql
points_history {
  id: PRIMARY KEY (UUID)
  user_id: UUID FOREIGN KEY REFERENCES users(id)
  appointment_id: UUID FOREIGN KEY REFERENCES appointments(id)
  points_change: INTEGER NOT NULL -- positivo para ganar, negativo para usar
  points_balance: INTEGER NOT NULL -- balance después del cambio
  description: VARCHAR(255)
  created_at: TIMESTAMP DEFAULT NOW()
}
```

## Relaciones

- **Users** → **Appointments** (1:N) - Un usuario puede tener múltiples citas
- **Barbershops** → **Barbers** (1:N) - Una barbería puede tener múltiples barberos
- **Barbershops** → **Appointments** (1:N) - Una barbería puede tener múltiples citas
- **Barbers** → **Appointments** (1:N) - Un barbero puede tener múltiples citas
- **Services** → **Appointments** (1:N) - Un servicio puede ser usado en múltiples citas
- **Barbers** → **Barber_Schedules** (1:N) - Un barbero puede tener múltiples horarios
- **Barbershops** → **Barbershop_Hours** (1:N) - Una barbería puede tener múltiples horarios
- **Users** → **Points_History** (1:N) - Un usuario puede tener múltiple historial de puntos

## Índices importantes

```sql
-- Índices para mejorar performance
CREATE INDEX idx_appointments_date_time ON appointments(appointment_date, appointment_time);
CREATE INDEX idx_appointments_barbershop ON appointments(barbershop_id);
CREATE INDEX idx_appointments_user ON appointments(user_id);
CREATE INDEX idx_appointments_barber ON appointments(barber_id);
CREATE INDEX idx_barbers_barbershop ON barbers(barbershop_id);
CREATE INDEX idx_points_history_user ON points_history(user_id);
CREATE INDEX idx_barber_schedules_barber ON barber_schedules(barber_id);
CREATE INDEX idx_barbershop_hours_barbershop ON barbershop_hours(barbershop_id);
```

## Reglas de negocio

### Sistema de puntos:
- **Corte normal**: 10 puntos
- **Corte con barba**: 15 puntos  
- **100 puntos** = 1 corte gratis
- Los puntos se acumulan cuando la cita se marca como 'completed'
- Para usar puntos, se crea una cita con `is_free_cut = true` y se descuentan 100 puntos

### Disponibilidad de citas:
- Solo se muestran horarios disponibles basados en:
  - Horarios de la barbería (`barbershop_hours`)
  - Horarios del barbero específico (`barber_schedules`) si se elige barbero
  - Citas ya reservadas en esa fecha/hora
  - Si no se especifica barbero, se busca cualquier barbero disponible

### Estados de citas:
- **pending**: Cita creada, esperando confirmación
- **confirmed**: Cita confirmada por la barbería
- **completed**: Cita completada (se otorgan puntos)
- **cancelled**: Cita cancelada

Esta estructura permite escalabilidad y flexibilidad para manejar múltiples barberías con sus propios barberos y horarios, mientras mantiene un sistema de puntos centralizado por usuario.