<?php

namespace App\Services;

use App\Models\ScoreCard;
use Error;

class ScorecardServices
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    static function getScorecards($id){
        if(!$id){
            $scorecards = ScoreCard::all();
            return $scorecards;
        }

        $scorecard = Scorecard::findOrFail($id);
        return $scorecard;
    }

    static function addOrUpdateScorecard($request,$id = 0){
        if($id ==  0){
            $scorecard = new Scorecard();
        }  else {
            $scorecard = Scorecard::find($id);
            if(!$scorecard){
            throw new Error("No Job Scorecard Found.");
            }
        }

            $scorecard->fill($request->all());


            if($scorecard->save()){
            return $scorecard;
            }

        throw new Error("Error Saving Scorecard.");
        }

        static function deleteScoreCard($id){
        $role = ScoreCard::findOrFail($id);
        $role->delete();
    }
}
