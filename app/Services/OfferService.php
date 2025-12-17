<?php

namespace App\Services;

use App\Models\Offer;
use Carbon\Carbon;
use Carbon\Traits\ToStringFormat;
use Http as FacadesHttp;

class OfferService{
    public static function createOffer($offer , $recruiter_id){
        $newOffer = self::saveOffer($offer , $recruiter_id);
        return $newOffer;// n8n takes it from here
    }

   private static function saveOffer($offer , $recruiter_id){
        $newOffer = new Offer;
        $newOffer->candidate_id = $offer["candidate_id"];
        $newOffer->created_by = $recruiter_id;
        $newOffer->role_id = $offer["role_id"];
        $newOffer->base_salary = $offer["form"]["base_salary"];
        $newOffer->equity = $offer["form"]["equity"];
        $newOffer->bonus = $offer["form"]["bonus"];
        $newOffer->benifits = $offer["form"]["benefits"];
        $newOffer->start_date = $offer["form"]["start_date"];
        $newOffer->status = "sent";
        $newOffer->expiry_date = $offer["form"]["expiry_date"];
        $newOffer->sent_at = Carbon::now();
        $newOffer->responded_at = null;
        $newOffer->contract_type = $offer["form"]["contract_type"];

        $newOffer->save();

        return $newOffer;
   }



}
