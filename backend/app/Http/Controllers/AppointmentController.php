<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Barbero;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        $isBarbero = false;
        $barberoId = null;
        
        if (isset($user->is_barbero) && $user->is_barbero) {
            $isBarbero = true;
            $barberoId = $user->barbero_id ?? null;
        }
        
        if (!$isBarbero && isset($user->email) && str_ends_with(strtolower($user->email), '@barbero.com')) {
            $isBarbero = true;
            $barbero = Barbero::where('name', $user->name)->first();
            if ($barbero) {
                $barberoId = $barbero->id;
                
                if (\Schema::hasColumn('users', 'barbero_id') && !$user->barbero_id) {
                    $user->barbero_id = $barberoId;
                    $user->save();
                }
            }
        }
        
        if ($isBarbero && $barberoId) {
            $appointments = Appointment::where('barbero_id', $barberoId)
                ->with('user', 'barbero')
                ->orderBy('date', 'desc')
                ->orderBy('time', 'desc')
                ->get();
        } else {
            $appointments = $user->appointments()
                ->with('barbero')
                ->orderBy('date', 'desc')
                ->orderBy('time', 'desc')
                ->get();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'appointments' => $appointments
            ]
        ]);
    }

    public function getAvailableSlots(Request $request)
    {
        date_default_timezone_set('Europe/Madrid');
        
        $validator = Validator::make($request->all(), [
            'date' => 'required|date|after_or_equal:today',
            'barbero_id' => 'nullable|exists:barberos,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $date = $request->date;
        $barberoId = $request->barbero_id;

        Log::info('getAvailableSlots called', [
            'date' => $date,
            'barbero_id' => $barberoId
        ]);

        $workingHours = [
            'start' => 9,
            'end' => 19,
            'days' => [1, 2, 3, 4, 5] 
        ];

        $dayOfWeek = date('N', strtotime($date)); 
        
        Log::info('Day of week', ['dayOfWeek' => $dayOfWeek, 'workingDays' => $workingHours['days']]);
        
        if (!in_array($dayOfWeek, $workingHours['days'])) {
            Log::info('Weekend day detected');
            return response()->json([
                'success' => true,
                'data' => [
                    'available_slots' => [],
                    'message' => 'No hay citas disponibles en fin de semana'
                ]
            ]);
        }

        $allSlots = [];
        for ($hour = $workingHours['start']; $hour < $workingHours['end']; $hour++) {
            $timeString = sprintf('%02d:00', $hour);
            $allSlots[] = $timeString;
        }

        Log::info('All slots generated', ['allSlots' => $allSlots]);

        $bookedAppointments = Appointment::where('date', $date)
            ->where('status', '!=', 'cancelled');

        if ($barberoId) {
            $bookedAppointments->where('barbero_id', $barberoId);
        }

        $bookedSlots = $bookedAppointments->pluck('time')->map(function($time) {
            return substr($time, 0, 5); 
        })->toArray();
        
        Log::info('Booked slots found', [
            'bookedSlots' => $bookedSlots,
            'query' => $bookedAppointments->toSql(),
            'bindings' => $bookedAppointments->getBindings()
        ]);

        $availableSlots = array_values(array_diff($allSlots, $bookedSlots));
        
        $today = date('Y-m-d');
        if ($date === $today) {
            $currentHour = (int)date('H');
            $currentMinute = (int)date('i');
            
            Log::info('Filtering today slots', [
                'current_hour' => $currentHour,
                'current_minute' => $currentMinute,
                'current_time' => date('H:i'),
                'all_slots_before_filter' => $availableSlots
            ]);
            
            $availableSlots = array_filter($availableSlots, function($slot) use ($currentHour, $currentMinute) {
                $slotHour = (int)substr($slot, 0, 2);
                
                if ($slotHour > $currentHour) {
                    Log::info("Slot {$slot} (future hour): available");
                    return true;
                }
                
                if ($slotHour === $currentHour) {
                    
                    $isAvailable = false; 
                    Log::info("Slot {$slot} (current hour): not available - current time is {$currentHour}:{$currentMinute}");
                    return $isAvailable;
                }
                Log::info("Slot {$slot} (past hour): not available");
                return false;
            });
            
            $availableSlots = array_values($availableSlots);
            
            Log::info('Available slots after filtering', [
                'available_slots' => $availableSlots
            ]);
        }
        
        Log::info('Available slots calculated', ['availableSlots' => $availableSlots]);

        return response()->json([
            'success' => true,
            'data' => [
                'available_slots' => $availableSlots,
                'booked_slots' => $bookedSlots, 
                'date' => $date,
                'barbero_id' => $barberoId,
                'debug' => [
                    'all_slots' => $allSlots,
                    'booked_slots' => $bookedSlots,
                    'day_of_week' => $dayOfWeek,
                    'is_today' => $date === $today,
                    'current_time' => date('H:i')
                ]
            ]
        ]);
    }

    public function getBarberos()
    {
        $barberos = Barbero::all();

        return response()->json([
            'success' => true,
            'data' => [
                'barberos' => $barberos
            ]
        ]);
    }

    public function store(Request $request)
    {
        date_default_timezone_set('Europe/Madrid');
        
        $validator = Validator::make($request->all(), [
            'date' => 'required|date|after_or_equal:today',
            'time' => 'required|date_format:H:i',
            'barbero_id' => 'required|exists:barberos,id',
            'service_type' => 'required|in:corte,corte_barba,barba,corte_gratis,tinte,corte_tinte,corte_barba_tinte',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        $today = date('Y-m-d');
        if ($request->date === $today) {
            $currentHour = (int)date('H');
            $currentMinute = (int)date('i');
            $requestedHour = (int)substr($request->time, 0, 2);
            
            if ($requestedHour < $currentHour) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pueden reservar citas para horas pasadas'
                ], 400);
            }
            
            if ($requestedHour === $currentHour && $currentMinute > 40) {
                return response()->json([
                    'success' => false,
                    'message' => 'Para reservar en la hora actual, debe ser al menos 20 minutos antes'
                ], 400);
            }
        }

        if ($request->service_type === 'corte_gratis') {
            if (!$user->hasEnoughPointsForFreeHaircut()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes suficientes puntos para un corte gratis. Necesitas 100 puntos.'
                ], 400);
            }
        }

        $existingAppointment = Appointment::where('date', $request->date)
            ->where('time', $request->time)
            ->where('barbero_id', $request->barbero_id)
            ->where('status', '!=', 'cancelled')
            ->first();

        if ($existingAppointment) {
            return response()->json([
                'success' => false,
                'message' => 'Ya existe una cita reservada para esa fecha, hora y barbero'
            ], 400);
        }

        $appointment = Appointment::create([
            'user_id' => $user->id,
            'barbero_id' => $request->barbero_id,
            'date' => $request->date,
            'time' => $request->time,
            'service_type' => $request->service_type,
            'notes' => $request->notes,
            'is_free_haircut' => $request->service_type === 'corte_gratis',
            'status' => 'pending'
        ]);

        if ($request->service_type === 'corte_gratis') {
            $user->usePointsForFreeHaircut();
        }

        try {
            $dateParts = explode('-', $request->date);
            $formattedDate = $dateParts[2] . '-' . $dateParts[1] . '-' . $dateParts[0];
            
            $notification = \App\Models\Notification::create([
                'title' => 'Nueva cita reservada',
                'message' => "Nueva cita reservada por {$user->name} para el {$formattedDate} a las {$request->time}",
                'type' => 'appointment_created',
                'user_id' => null, 
                'read' => false
            ]);
            
            Log::info('Notificación creada exitosamente', [
                'notification_id' => $notification->id,
                'user_name' => $user->name,
                'date' => $request->date,
                'time' => $request->time
            ]);
        } catch (\Exception $e) {
            Log::error('Error creando notificación', [
                'error' => $e->getMessage(),
                'user_name' => $user->name,
                'date' => $request->date,
                'time' => $request->time
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Cita creada exitosamente',
            'data' => [
                'appointment' => $appointment->load('barbero')
            ]
        ], 201);
    }

    public function show(Request $request, Appointment $appointment)
    {
        if ($appointment->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'appointment' => $appointment->load('barbero')
            ]
        ]);
    }

    private function getUserBarberoId($user)
    {
        if (isset($user->is_barbero) && $user->is_barbero) {
            return $user->barbero_id ?? null;
        }
        
        if (isset($user->email) && str_ends_with(strtolower($user->email), '@barbero.com')) {
            $barbero = Barbero::where('name', $user->name)->first();
            if ($barbero) {
                if (Schema::hasColumn('users', 'barbero_id') && !$user->barbero_id) {
                    $user->barbero_id = $barbero->id;
                    $user->save();
                }
                return $barbero->id;
            }
        }
        
        return null;
    }

    public function update(Request $request, Appointment $appointment)
    {
        $user = $request->user();
        
        $isOwner = $appointment->user_id === $user->id;
        $isAdmin = $user->is_admin ?? false;
        $userBarberoId = $this->getUserBarberoId($user);
        $isBarbero = $userBarberoId && $appointment->barbero_id === $userBarberoId;
        
        if (!$isOwner && !$isAdmin && !$isBarbero) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'date' => 'sometimes|required|date|after:today',
            'time' => 'sometimes|required|date_format:H:i',
            'barbero_id' => 'sometimes|required|exists:barberos,id',
            'service_type' => 'sometimes|required|in:corte,corte_barba,barba,corte_gratis,tinte,corte_tinte,corte_barba_tinte',
            'notes' => 'sometimes|nullable|string|max:500',
            'status' => 'sometimes|required|in:pending,confirmed,cancelled,completed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $previousStatus = $appointment->status;
        $newStatus = $request->input('status');

        if ($newStatus === 'cancelled' && $previousStatus !== 'cancelled' && $appointment->is_free_haircut) {
            $user = $appointment->user;
            $user->addPoints(100);
        }

        $appointment->update($request->all());

        if ($newStatus === 'completed' && $previousStatus !== 'completed') {
            $this->awardPointsForCompletedAppointment($appointment);
        }

        return response()->json([
            'success' => true,
            'message' => 'Cita actualizada exitosamente',
            'data' => [
                'appointment' => $appointment->load(['barbero', 'user']),
                'user' => [
                    'points' => $appointment->user->points
                ]
            ]
        ]);
    }

    private function awardPointsForCompletedAppointment(Appointment $appointment)
    {
        $user = $appointment->user;
        $pointsToAward = 0;

        switch ($appointment->service_type) {
            case 'corte':
                $pointsToAward = 10;
                break;
            case 'corte_barba':
                $pointsToAward = 15;
                break;
            case 'corte_tinte':
                $pointsToAward = 10;
                break;
            case 'corte_barba_tinte':
                $pointsToAward = 15;
                break;
            case 'tinte':
                $pointsToAward = 0;
                break;
            case 'barba':
                $pointsToAward = 5; 
                break;
            case 'corte_gratis':
                $pointsToAward = 0; 
                break;
            default:
                $pointsToAward = 0;
                break;
        }

        if ($pointsToAward > 0) {
            $user->addPoints($pointsToAward);
        }
    }

    public function destroy(Request $request, Appointment $appointment)
    {
        $user = $request->user();
        
        $isOwner = $appointment->user_id === $user->id;
        $isAdmin = $user->is_admin ?? false;
        $userBarberoId = $this->getUserBarberoId($user);
        $isBarbero = $userBarberoId && $appointment->barbero_id === $userBarberoId;
        
        if (!$isOwner && !$isAdmin && !$isBarbero) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado'
            ], 403);
        }

        $appointment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Cita eliminada exitosamente'
        ]);
    }
}
