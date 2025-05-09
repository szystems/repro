<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class EmpresaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'nombre' => $this->faker->company(),
            'nit' => $this->faker->numerify('#######-#'),
            'direccion' => $this->faker->address(),
            'telefono' => $this->faker->phoneNumber(),
            'email' => $this->faker->companyEmail(),
            'sitio_web' => $this->faker->url(),
            'descripcion' => $this->faker->paragraph(),
            'contacto_nombre' => $this->faker->name(),
            'contacto_cargo' => $this->faker->jobTitle(),
            'contacto_telefono' => $this->faker->phoneNumber(),
            'contacto_email' => $this->faker->email(),
            'notas' => $this->faker->text(200),
            'estado' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
