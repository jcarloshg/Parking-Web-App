<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'attendant'])->default('attendant')->after('email');
        });

        Schema::create('parking_spaces', function (Blueprint $table) {
            $table->id();
            $table->string('space_number')->unique();
            $table->enum('type', ['standard', 'compact', 'electric', 'disabled']);
            $table->enum('status', ['available', 'occupied', 'maintenance'])->default('available');
            $table->decimal('hourly_rate', 8, 2);
            $table->timestamps();
        });

        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique();
            $table->foreignId('parking_space_id')->constrained();
            $table->string('vehicle_plate', 20);
            $table->string('vehicle_model', 50)->nullable();
            $table->string('vehicle_color', 30)->nullable();
            $table->timestamp('entry_time');
            $table->timestamp('exit_time')->nullable();
            $table->enum('status', ['active', 'completed', 'cancelled'])->default('active');
            $table->decimal('total_amount', 10, 2)->nullable();
            $table->foreignId('user_id')->constrained();
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained();
            $table->decimal('amount', 10, 2);
            $table->enum('method', ['cash', 'card', 'qr']);
            $table->string('transaction_id')->unique();
            $table->timestamp('payment_time');
            $table->foreignId('user_id')->constrained();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('tickets');
        Schema::dropIfExists('parking_spaces');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
