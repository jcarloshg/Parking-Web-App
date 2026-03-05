<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parking_spaces', function (Blueprint $table) {
            $table->id();
            $table->string('number', 10)->unique();
            $table->enum('type', ['general', 'discapacitado', 'eléctrico']);
            $table->enum('status', ['disponible', 'ocupado', 'fuera_servicio'])->default('disponible');
            $table->timestamps();
        });

        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('plate_number', 20);
            $table->enum('vehicle_type', ['auto', 'moto', 'camioneta']);
            $table->dateTime('entry_time');
            $table->dateTime('exit_time')->nullable();
            $table->foreignId('parking_space_id')->constrained('parking_spaces')->onDelete('restrict');
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            $table->enum('status', ['activo', 'finalizado'])->default('activo');
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('tickets')->onDelete('restrict');
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            $table->decimal('total', 10, 2);
            $table->enum('payment_method', ['efectivo', 'tarjeta']);
            $table->dateTime('paid_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('tickets');
        Schema::dropIfExists('parking_spaces');
    }
};
