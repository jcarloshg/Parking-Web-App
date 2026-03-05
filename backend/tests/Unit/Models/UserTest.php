<?php

namespace Tests\Unit\Models;

use App\Models\User;
use App\Models\Ticket;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_has_many_tickets(): void
    {
        $user = User::factory()->create();
        $ticket = Ticket::factory()->for($user, 'user')->create();

        $this->assertTrue($user->tickets->contains($ticket));
    }

    public function test_user_has_many_payments(): void
    {
        $user = User::factory()->create();
        $payment = Payment::factory()->for($user, 'user')->create();

        $this->assertTrue($user->payments->contains($payment));
    }

    public function test_is_admin_returns_true_for_admin(): void
    {
        $admin = User::factory()->admin()->create();

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($admin->isCajero());
        $this->assertFalse($admin->isSupervisor());
    }

    public function test_is_cajero_returns_true_for_cajero(): void
    {
        $cajero = User::factory()->cajero()->create();

        $this->assertTrue($cajero->isCajero());
        $this->assertFalse($cajero->isAdmin());
    }

    public function test_is_supervisor_returns_true_for_supervisor(): void
    {
        $supervisor = User::factory()->supervisor()->create();

        $this->assertTrue($supervisor->isSupervisor());
        $this->assertFalse($supervisor->isAdmin());
    }

    public function test_user_has_correct_fillable_attributes(): void
    {
        $user = new User();
        $fillable = $user->getFillable();

        $this->assertContains('name', $fillable);
        $this->assertContains('email', $fillable);
        $this->assertContains('password', $fillable);
        $this->assertContains('role', $fillable);
    }
}
