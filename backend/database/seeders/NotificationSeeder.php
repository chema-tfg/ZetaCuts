<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Notification;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        Notification::create([
            'type' => 'appointment_created',
            'title' => 'Nueva cita creada',
            'message' => 'Se ha creado una nueva cita para el cliente Juan Pérez',
            'data' => [
                'appointment_id' => 1,
                'client_name' => 'Juan Pérez',
                'date' => '2025-08-05 15:00:00'
            ],
            'read' => false
        ]);

        Notification::create([
            'type' => 'appointment_cancelled',
            'title' => 'Cita cancelada',
            'message' => 'La cita del cliente María García ha sido cancelada',
            'data' => [
                'appointment_id' => 2,
                'client_name' => 'María García',
                'date' => '2025-08-05 16:00:00'
            ],
            'read' => false
        ]);

        Notification::create([
            'type' => 'barbero_available',
            'title' => 'Barbero disponible',
            'message' => 'El barbero Carlos López está disponible para nuevas citas',
            'data' => [
                'barbero_id' => 1,
                'barbero_name' => 'Carlos López'
            ],
            'read' => false
        ]);

        Notification::create([
            'type' => 'system_maintenance',
            'title' => 'Mantenimiento del sistema',
            'message' => 'El sistema estará en mantenimiento el próximo lunes de 2:00 AM a 4:00 AM',
            'data' => [
                'maintenance_date' => '2025-08-11',
                'duration' => '2 horas'
            ],
            'read' => true
        ]);

        $this->command->info('Notificaciones de prueba creadas exitosamente.');
    }
} 