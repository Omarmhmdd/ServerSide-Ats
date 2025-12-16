<?php

namespace App\Services;

use App\Models\Offer;
use Date;
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
        $newOffer->base_salary = $offer["base_salary"];
        $newOffer->equity = $offer["equity"];
        $newOffer->bonus = $offer["bonus"];
        $newOffer->benifits = $offer["benifits"];
        $newOffer->start_date = $offer["start_date"];
        $newOffer->status = "";
        $newOffer->expiry_date = $offer["expiry_date"];
        $newOffer->sent_at = new Date.now();
        $newOffer->responded_at = null;
        $newOffer->contract_type = $offer["contract_type"];

        $newOffer->save();

        return $newOffer;
   }



}
