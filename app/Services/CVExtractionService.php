<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Smalot\PdfParser\Parser as PdfParser;
use PhpOffice\PhpWord\IOFactory;
use Storage;

class CVExtractionService
{
    /**
     * Download file (PDF/DOCX/TXT) from URL into temporary storage.
     */
    private function downloadTempFile($url){
        $url = $this->convertGoogleDriveUrl($url);
        $response = Http::get($url);

        if ($response->failed()) {
            throw new Exception("Response failed");
        }

        $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);
        $extension = strtolower($extension ?: 'pdf'); 

        $filename = uniqid('cv_', true) . '.' . $extension;
        $tempPath = storage_path("app/temp_cv/" . $filename);

        file_put_contents($tempPath, $response->body());

        return $tempPath;
    }

    /**
     * Main function: download → extract → delete
     */
    public function extract($url)
    {
        $filePath = $this->downloadTempFile($url);

        if (!$filePath) {
            return null;
        }

        $ext = pathinfo($filePath, PATHINFO_EXTENSION);

        $text = null;

        if ($ext === "pdf") {
            $text = $this->extractPdf($filePath);
        }

        if (in_array($ext, ["docx", "doc"])) {
            $text = $this->extractDocx($filePath);
        }

        if ($ext === "txt") {
            $text = file_get_contents($filePath);
        }

        // Remove the temp file
        @unlink($filePath);

        return $text;
    }

    private function extractPdf($filePath)
    {
        $parser = new PdfParser();
        $pdf = $parser->parseFile($filePath);

        return $pdf->getText();
    }

    private function extractDocx($filePath)
    {
        $phpWord = IOFactory::load($filePath);
        $text = "";

        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                if (method_exists($element, "getText")) {
                    $text .= $element->getText() . "\n";
                }
            }
        }

        return $text;
    }

    private function convertGoogleDriveUrl($url){
        // Extract the file ID
        preg_match('/\/d\/(.*?)\//', $url, $matches);
        if (!isset($matches[1])) {
            return $url; // Not a Google Drive link
        }

        $fileId = $matches[1];

        // Return direct download URL
        return "https://drive.google.com/uc?export=download&id={$fileId}";
    }


}
