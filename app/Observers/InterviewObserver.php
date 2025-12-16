<?php

namespace App\Observers;

use App\Models\Interview;
use App\Jobs\IngestInterviewNotesToRag;

class InterviewObserver{
    /**
     * Handle the Interview "created" event.
     */
    public function created(): void{
        //
    }

    public function saved(Interview $interview) : void{
     $this->callNoteIngestionDispatcher($interview);
    }

    /**
     * Handle the Interview "updated" event.
     */
    public function updated(Interview $interview): void
    {
        $this->callNoteIngestionDispatcher($interview);
    }

    /**
     * Handle the Interview "deleted" event.
     */
    public function deleted(Interview $interview): void
    {
        //
    }

    /**
     * Handle the Interview "restored" event.
     */
    public function restored(Interview $interview): void
    {
        //
    }

    /**
     * Handle the Interview "force deleted" event.
     */
    public function forceDeleted(Interview $interview): void
    {
        //
    }

    private function callNoteIngestionDispatcher($interview){
     //   if(!empty($interview->notes)){
      //      IngestInterviewNotesToRag::dispatch($interview->id);
      //  }
    }
}
