<?php

namespace App\Candidate\Services;

use App\CV\Services\CVExtractionService;
use App\CV\Services\CVSectionSplitter;
use App\Models\Candidate;
use App\Models\Interview;
use GuzzleHttp\Client;
use OpenAI;
use Str;

class CandidateIngestionService{

    protected Client $qdrant;

    public function __construct(){
        $this->qdrant = new Client([
            'base_url' => env('QDRANT_ENDPOINT_URL'),
            'headers' => [
                'api-key' => env('QDRANT_API_KEY'),
                'Content-type' => 'application/json'
            ]
            ]);
    }

    public static function chunkText(string $text, int $size = 500){
        $chunks = [];
        $length = strlen($text);
        for ($i = 0; $i < $length; $i += $size) {
            $chunks[] = substr($text, $i, $size);
        }
        return $chunks;
    }   


    public function ingest(int $candidateId){
        $candidate = Candidate::findOrFail($candidateId);

        $sources = $this->buildCVSource($candidate);

        foreach ($sources as $source) {
            $this->ingestSource(
                candidateId: $candidate->id,
                text: $source['text'],
                sourceType: $source['type'],
                sourceSection : $source['section'] ?? 'General',
                sourceLabel: $source['label'],
            );
        }
    }

    protected function buildCVSource(Candidate $candidate){
        $sources = [];
        if(!$candidate->attachments){
            return;
        }
    
        $cv_extractor = new CVExtractionService();
        $cvText = CandidateService::cleanUtf8($cv_extractor->extract($candidate->attachments));

        $splitter = new CVSectionSplitter();
        $sections = $splitter->split($cvText);
        foreach($sections as $section){
            $sources[] = [
                'type' => 'cv_pdf',
                'label' => 'CV',
                'section' => ucfirst($section),
                'text' => $cvText,
            ];
        }
        return $sources;
    }

    public function ingestInterviewNotes(int $interviewId): void{
        $interview = Interview::findOrFail($interviewId);

        if (! $interview->notes) {
            return;
        }

        if ($this->interviewAlreadyIngested($interviewId)) {
            return;
        }

        $this->ingestSource(
            candidateId: $interview->candidate_id,
            text: $interview->notes,
            sourceType: 'interview_notes',
            sourceLabel: 'Interview : ' . $interview->name,
            sourceSection : 'Interview notes',
            extraPayload: [
                'interview_id' => $interviewId
            ]
        );
    }


    protected function ingestSource(int $candidateId,string $text,string $sourceType,string $sourceLabel , string $sourceSection = "General" , array $extraPayload = []){
        $chunks = $this->chunkText($text);

        $points = [];

        foreach ($chunks as $i => $chunk) {
            $embedding = OpenAI::embeddings()->create([
                'model' => 'text-embedding-3-large',
                'input' => $chunk,
            ]);

            $points[] = [
                'id' => (string) Str::uuid(),
                'vector' => $embedding->data[0]->embedding,
                'payload' => array_merge([
                    'candidate_id' => $candidateId,
                    'source_type' => $sourceType,
                    'source_label' => $sourceLabel,
                    'source_section' => $sourceSection,
                    'chunk_id' => $i,
                    'text' => $chunk,
                ], $extraPayload)
            ];
        }

        $this->qdrant->put('/collections/candidates/points', [
            'json' => ['points' => $points]
        ]);
    }

    protected function interviewAlreadyIngested(int $interviewId): bool{
        $response = $this->qdrant->post(
            '/collections/candidates/points/scroll',
            [
                'json' => [
                    'limit' => 1,
                    'filter' => [
                        'must' => [
                            [
                                'key' => 'interview_id',
                                'match' => ['value' => $interviewId]
                            ]
                        ]
                    ]
                ]
            ]
        );

        $data = json_decode($response->getBody(), true);

        return ! empty($data['result']['points']);
    }









}
