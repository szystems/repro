<?php

namespace Database\Factories;

use App\Models\Empresa;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
            'role_as' => $this->faker->randomElement([
                '1', // Empresa
                '2', // Repro
                '3', // Admin (menos frecuente)
            ]),
            'principal' => '0',
            'estado' => '1',
            'telefono'=> $this->faker->numberBetween($min = 10000000, $max = 99999999),
            'celular'=> $this->faker->numberBetween($min = 10000000, $max = 99999999),
            'direccion'=> $this->faker->streetAddress,
            'fecha_nacimiento' => $this->faker->dateTimeBetween('-60 years', '-20 years'),
            'fotografia' => $this->faker->randomElement([
                'team-1.jpg',
                'team-2.jpg',
                'team-3.jpg',
                'team-4.jpg',
                'user.png',
                'user1.png',
                'user2.png',
                'user3.png',
                'user4.png',
                'user5.png',
            ]),
            'empresa_id' => function (array $attributes) {
                // Asignar empresa_id solo para usuarios tipo empresa (role_as = 1)
                return $attributes['role_as'] == '1' 
                    ? Empresa::inRandomOrder()->first()->id ?? null
                    : null;
            },
            'cargo' => function (array $attributes) {
                // Asignar cargo para usuarios de empresa (1) y Repro (2)
                if ($attributes['role_as'] == '1') {
                    return $this->faker->randomElement(['Gerente', 'RRHH', 'Supervisor', 'Jefe de Área', 'Coordinador']);
                } elseif ($attributes['role_as'] == '2') {
                    return $this->faker->randomElement(['Poligrafista', 'Técnico', 'Coordinador', 'Analista', 'Entrevistador']);
                }
                return null;
            },
            'permisos' => function (array $attributes) {
                // Asignar permisos específicos para usuarios de Repro
                if ($attributes['role_as'] == '2') {
                    return json_encode($this->faker->randomElements([
                        'evaluaciones', 'empresas', 'reportes', 'usuarios'
                    ], $this->faker->numberBetween(1, 3)));
                }
                return null;
            },
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function unverified()
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => null,
            ];
        });
    }
    
    /**
     * Configure the model factory to create Evaluado users.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function evaluado()
    {
        return $this->state(function (array $attributes) {
            return [
                'role_as' => '0',
                'empresa_id' => null,
                'cargo' => null,
                'permisos' => null,
            ];
        });
    }

    /**
     * Configure the model factory to create Empresa users.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function empresa()
    {
        return $this->state(function (array $attributes) {
            return [
                'role_as' => '1',
                'empresa_id' => Empresa::inRandomOrder()->first()->id ?? null,
                'cargo' => $this->faker->randomElement(['Gerente', 'RRHH', 'Supervisor', 'Jefe de Área']),
                'permisos' => null,
            ];
        });
    }

    /**
     * Configure the model factory to create Repro users.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function repro()
    {
        return $this->state(function (array $attributes) {
            return [
                'role_as' => '2',
                'empresa_id' => null,
                'cargo' => $this->faker->randomElement(['Poligrafista', 'Técnico', 'Coordinador', 'Analista']),
                'permisos' => json_encode($this->faker->randomElements([
                    'evaluaciones', 'empresas', 'reportes', 'usuarios'
                ], $this->faker->numberBetween(1, 3))),
            ];
        });
    }
    
    /**
     * Configure the model factory to create Admin users.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function admin()
    {
        return $this->state(function (array $attributes) {
            return [
                'role_as' => '3',
                'empresa_id' => null,
                'cargo' => 'Administrador',
                'permisos' => null,
            ];
        });
    }
}
