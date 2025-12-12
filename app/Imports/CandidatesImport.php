<?php

namespace App\Imports;

use App\Models\Candidate;
use App\Models\MetaData;
use App\Models\Pipeline;
use Http;
use Log;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Number;
use PhpOffice\PhpSpreadsheet\Writer\Ods\Meta;
use Throwable;

class CandidatesImport implements ToModel , WithHeadingRow , WithChunkReading
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */

    use SkipsErrors;// error collection instead of distrubpting code flow

    protected int $recruiterId;
    protected $jobRoleId;

    
    public function __construct($recruiterId, $jobRoleId){
        $this->recruiterId = $recruiterId;
        $this->jobRoleId = $jobRoleId;
    }


    public function chunkSize(): int{
        return 100; // import 100 rows at a time avoid memory issues for large files
    }
    public function model(array $row){
        try{
            $new_candidate =  new Candidate([
                'first_name'    => $row['first_name'] ?? null,
                'last_name'     => $row['last_name'] ?? null,
                'email'         => $row['email'] ?? null,
                'portfolio'     => $row['portfolio'] ?? null,
                'linkedin_url'  => $row['linkedin_url'] ?? null,
                'github_username'    => $row['github_username'] ?? null,
                'source'        => $row['source'] ?? null,
                'location'      => $row['location'] ?? null,
                'notes'         => $row['notes'] ?? null,
                'phone'         => $row['phone'] ?? null,
                'attachments' => $row["cv"] ?? null,
                'recruiter_id'  => $this->recruiterId,
                'job_role_id'   => (int)$this->jobRoleId,
            ]);   

            $new_candidate->save();


           // create new pipeline here

            return $new_candidate;
        }catch(Throwable $e){
            $this->onError($e);
            return null; // skip this row and keep going
        }
    }
}
