<?php

namespace Database\Seeders;

use App\Models\Course;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        $courses = [
            [
                'title' => 'Fundamentos de Programacion',
                'description' => 'Bases de logica, estructuras de control y buenas practicas para iniciar con desarrollo de software.',
                'price' => 1499,
                'course_cost' => 1499,
                'minimum_payment' => 500,
                'duration_hours' => 24,
            ],
            [
                'title' => 'Laravel para Aplicaciones Web',
                'description' => 'Rutas, controladores, modelos, migraciones y vistas Blade para construir aplicaciones profesionales.',
                'price' => 2499,
                'course_cost' => 2499,
                'minimum_payment' => 900,
                'duration_hours' => 36,
            ],
            [
                'title' => 'Bases de Datos con MySQL',
                'description' => 'Modelado relacional, consultas, indices y administracion basica para proyectos web.',
                'price' => 1899,
                'course_cost' => 1899,
                'minimum_payment' => 700,
                'duration_hours' => 30,
            ],
            [
                'title' => 'Dashboard y Reportes',
                'description' => 'Creacion de paneles administrativos, metricas clave y tablas operativas para seguimiento de negocio.',
                'price' => 2199,
                'course_cost' => 2199,
                'minimum_payment' => 800,
                'duration_hours' => 28,
            ],
        ];

        foreach ($courses as $course) {
            Course::updateOrCreate(
                ['slug' => Str::slug($course['title'])],
                $course + [
                    'start_date' => '2026-05-15',
                    'end_date' => '2026-08-15',
                    'payment_start_date' => '2026-05-15',
                    'payment_end_date' => '2026-07-15',
                    'is_active' => true,
                ],
            );
        }
    }
}
