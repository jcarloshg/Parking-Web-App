<?php

namespace Tests\Unit\Services;

use App\Services\FeeCalculator;
use Carbon\Carbon;
use Tests\TestCase;

class FeeCalculatorTest extends TestCase
{
    private FeeCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new FeeCalculator();
    }

    public function test_calculates_10_minutes_as_zero(): void
    {
        $entryTime = Carbon::now()->subMinutes(5);
        $result = $this->calculator->calculate('auto', $entryTime);

        $this->assertEquals(0, $result['total']);
        $this->assertEquals(0, $result['hours']);
        $this->assertEquals('tolerance', $result['rate_type']);
    }

    public function test_calculates_one_hour_for_auto(): void
    {
        $entryTime = Carbon::now()->subHour();
        $result = $this->calculator->calculate('auto', $entryTime);

        $this->assertEquals(20, $result['total']);
        $this->assertEquals(1, $result['hours']);
        $this->assertEquals('hourly', $result['rate_type']);
    }

    public function test_calculates_two_hours_for_auto(): void
    {
        $entryTime = Carbon::now()->subHours(2);
        $result = $this->calculator->calculate('auto', $entryTime);

        $this->assertEquals(40, $result['total']);
        $this->assertEquals(2, $result['hours']);
    }

    public function test_calculates_three_hours_for_auto(): void
    {
        $entryTime = Carbon::now()->subHours(3);
        $result = $this->calculator->calculate('auto', $entryTime);

        $this->assertEquals(60, $result['total']);
        $this->assertEquals(3, $result['hours']);
    }

    public function test_calculates_daily_rate_after_24h(): void
    {
        $entryTime = Carbon::now()->subHours(25);
        $result = $this->calculator->calculate('auto', $entryTime);

        $this->assertEquals(170, $result['total']); // 150 (1 day) + 20 (1 hour)
        $this->assertEquals(25, $result['hours']);
        $this->assertEquals(1, $result['days']);
        $this->assertEquals('mixed', $result['rate_type']);
    }

    public function test_calculates_two_days(): void
    {
        $entryTime = Carbon::now()->subHours(48);
        $result = $this->calculator->calculate('auto', $entryTime);

        $this->assertEquals(300, $result['total']); // 150 * 2
        $this->assertEquals(2, $result['days']);
    }

    public function test_moto_has_different_rate(): void
    {
        $entryTime = Carbon::now()->subHour();
        $result = $this->calculator->calculate('moto', $entryTime);

        $this->assertEquals(10, $result['total']);
        $this->assertEquals(10, $result['rate_per_hour']);
    }

    public function test_moto_daily_rate(): void
    {
        $entryTime = Carbon::now()->subHours(24);
        $result = $this->calculator->calculate('moto', $entryTime);

        $this->assertEquals(80, $result['total']);
    }

    public function test_camioneta_has_higher_rate(): void
    {
        $entryTime = Carbon::now()->subHour();
        $result = $this->calculator->calculate('camioneta', $entryTime);

        $this->assertEquals(30, $result['total']);
        $this->assertEquals(30, $result['rate_per_hour']);
    }

    public function test_camioneta_daily_rate(): void
    {
        $entryTime = Carbon::now()->subHours(24);
        $result = $this->calculator->calculate('camioneta', $entryTime);

        $this->assertEquals(200, $result['total']);
    }

    public function test_rounds_up_to_next_hour(): void
    {
        $entryTime = Carbon::now()->subMinutes(65);
        $result = $this->calculator->calculate('auto', $entryTime);

        $this->assertEquals(40, $result['total']); // 2 hours
        $this->assertEquals(2, $result['hours']);
    }

    public function test_rounds_up_30_minutes(): void
    {
        $entryTime = Carbon::now()->subMinutes(90);
        $result = $this->calculator->calculate('auto', $entryTime);

        $this->assertEquals(40, $result['total']); // 2 hours
        $this->assertEquals(2, $result['hours']);
    }

    public function test_unknown_vehicle_type_uses_auto_rate(): void
    {
        $entryTime = Carbon::now()->subHour();
        $result = $this->calculator->calculate('unknown', $entryTime);

        $this->assertEquals(20, $result['total']);
        $this->assertEquals(20, $result['rate_per_hour']);
    }

    public function test_returns_all_rate_information(): void
    {
        $entryTime = Carbon::now()->subHours(2);
        $result = $this->calculator->calculate('auto', $entryTime);

        $this->assertArrayHasKey('rate_per_hour', $result);
        $this->assertArrayHasKey('daily_rate', $result);
        $this->assertArrayHasKey('minutes', $result);
        $this->assertEquals(20, $result['rate_per_hour']);
        $this->assertEquals(150, $result['daily_rate']);
    }

    public function test_get_hourly_rate(): void
    {
        $this->assertEquals(20, $this->calculator->getHourlyRate('auto'));
        $this->assertEquals(10, $this->calculator->getHourlyRate('moto'));
        $this->assertEquals(30, $this->calculator->getHourlyRate('camioneta'));
    }

    public function test_get_daily_rate(): void
    {
        $this->assertEquals(150, $this->calculator->getDailyRate('auto'));
        $this->assertEquals(80, $this->calculator->getDailyRate('moto'));
        $this->assertEquals(200, $this->calculator->getDailyRate('camioneta'));
    }

    public function test_get_tolerance_minutes(): void
    {
        $this->assertEquals(10, $this->calculator->getToleranceMinutes());
    }
}
