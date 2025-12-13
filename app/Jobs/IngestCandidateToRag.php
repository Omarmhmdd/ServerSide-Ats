<?php

use App\Candidate\Services\CandidateIngestionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;


class IngestCandidateToRag implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;// protect against openAI timeouts

    public int $candidateId;

    public function __construct(int $candidateId)
    {
        $this->candidateId = $candidateId;
    }

    public function handle(CandidateIngestionService $service): void
    {
        $service->ingest($this->candidateId);
    }
}