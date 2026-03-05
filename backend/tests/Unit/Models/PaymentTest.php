<?php

namespace Tests\Unit\Models;

use App\Models\Payment;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_belongs_to_ticket(): void
    {
        $ticket = Ticket::factory()->create();
        $payment = Payment::factory()->for($ticket, 'ticket')->create();

        $this->assertEquals($ticket->id, $payment->ticket->id);
    }

    public function test_payment_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $payment = Payment::factory()->for($user, 'user')->create();

        $this->assertEquals($user->id, $payment->user->id);
    }

    public function test_is_cash_returns_true_for_efectivo(): void
    {
        $payment = Payment::factory()->cash()->create();

        $this->assertTrue($payment->isCash());
        $this->assertFalse($payment->isCard());
    }

    public function test_is_card_returns_true_for_tarjeta(): void
    {
        $payment = Payment::factory()->card()->create();

        $this->assertTrue($payment->isCard());
        $this->assertFalse($payment->isCash());
    }

    public function test_payment_has_correct_fillable_attributes(): void
    {
        $payment = new Payment();
        $fillable = $payment->getFillable();

        $this->assertContains('ticket_id', $fillable);
        $this->assertContains('total', $fillable);
        $this->assertContains('payment_method', $fillable);
        $this->assertContains('user_id', $fillable);
    }

    public function test_payment_total_is_decimal(): void
    {
        $payment = Payment::factory()->create(['total' => 50.50]);

        $this->assertEquals(50.50, $payment->total);
    }
}
