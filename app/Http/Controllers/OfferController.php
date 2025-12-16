<?php

namespace App\Http\Controllers;

use App\Http\Requests\OfferForm;
use App\Services\OfferService;
use Auth;
use Illuminate\Http\Request;

class OfferController extends Controller{

    public function createOffer(OfferForm $offer){
        try {
            OfferService::createOffer($offer->validated() , Auth::id());
            return $this->successResponse("Offer created");
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create offer', 500, ['error' => $e->getMessage()]);
        }
    }
}
