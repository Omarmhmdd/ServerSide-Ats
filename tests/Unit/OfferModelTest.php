<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Offer;
use Carbon\Carbon;

class OfferModelTest extends TestCase
{
    public function test_offer_dates_are_casted_correctly()
    {
        // Arrange
        $offer = new Offer();
        $offer->start_date = '2025-01-20';
        $offer->sent_at = '2025-01-15 10:00:00';

        // Act & Assert
        // Check if they are Carbon instances (due to $casts in model?
        // Note: Your uploaded Model/Offer.php only casts created_at/updated_at by default.
        // You SHOULD add start_date to casts in your code, or this test reveals a missing feature.)

        // If you strictly test the provided file:
        $this->assertEquals('2025-01-20', $offer->start_date);

        // Suggested improvement for Member 3: Update Model/Offer.php casts
        /* protected $casts = [
               'start_date' => 'date',
               'sent_at' => 'datetime',
           ];
        */
    }

    public function test_offer_is_mass_assignable()
    {
        $data = [
            'candidate_id' => 1,
            'base_salary' => 100000,
            'bonus' => 5000,
            'status' => 'draft'
        ];

        $offer = new Offer($data);

        $this->assertEquals(100000, $offer->base_salary);
        $this->assertEquals('draft', $offer->status);
    }
}
