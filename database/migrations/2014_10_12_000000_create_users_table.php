<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');

            // Documento de identidad y tipo
            $table->string('documento_identidad', 50)->nullable();
            $table->enum('tipo_documento', ['DPI', 'Pasaporte', 'Licencia'])->nullable();

            // Sistema de roles dual (legacy + nuevo)
            // 0: usuario evaluado, 1: usuario empresa, 2: usuario repro, 3: administrador
            $table->tinyInteger('role_as')->default('0')->comment('0: Evaluado, 1: Empresa, 2: Repro, 3: Admin');

            // Relación con empresa (para usuarios tipo empresa)
            $table->unsignedBigInteger('empresa_id')->nullable();

            // Campos de estado y jerarquía
            $table->tinyInteger('principal')->default('0')->comment('1: Usuario principal de empresa');
            $table->tinyInteger('estado')->default('1')->comment('0: Inactivo, 1: Activo');

            // Datos personales
            $table->string('fotografia')->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->string('celular')->nullable();
            $table->string('telefono')->nullable();
            $table->string('direccion')->nullable();

            // Campos adicionales para usuarios de Repro
            $table->string('cargo')->nullable()->comment('Cargo para usuarios Repro');
            $table->json('permisos')->nullable()->comment('Permisos específicos (legacy)');

            // Tracking de cuestionarios
            $table->boolean('cuestionario_completado')->default(false);
            $table->timestamp('cuestionario_completado_at')->nullable();

            $table->rememberToken();
            $table->timestamps();

            // Índices para mejorar performance
            $table->index('role_as');
            $table->index('empresa_id');
            $table->index('estado');
            $table->index('documento_identidad');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}