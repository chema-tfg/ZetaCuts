<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Barbero;
use Illuminate\Support\Facades\DB;

class BarberoSeeder extends Seeder
{
    public function run(): void
    {
        
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('barberos')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        $barberos = [
            ['name' => 'Carlos Rodríguez'],
            ['name' => 'Miguel Ángel López'],
            ['name' => 'David García'],
            ['name' => 'Antonio Martínez'],
            ['name' => 'Javier Fernández'],
        ];

        foreach ($barberos as $barbero) {
            DB::table('barberos')->insert([
                'name' => $barbero['name'],
                'image_url' => '/imagenes/peluquero.png',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
