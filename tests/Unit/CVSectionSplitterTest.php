<?php

namespace Tests\Unit;

use App\Services\CV\CVSectionSplitter;
use PHPUnit\Framework\TestCase;

class CVSectionSplitterTest extends TestCase
{
    public function test_it_splits_cv_text_into_named_sections()
    {
        $rawText = "
            John Doe
            Software Engineer

            Summary
            Experienced Laravel developer with 5 years of experience.

            Technical Skills
            PHP, Laravel, React, Docker, AWS.

            Experience
            Senior Developer at Tech Corp (2020-Present)
            - Built things.

            Education
            BSc Computer Science - University of X
        ";

        $splitter = new CVSectionSplitter();

        $sections = $splitter->split($rawText);

        $this->assertArrayHasKey('summary', $sections);
        $this->assertArrayHasKey('skills', $sections);
        $this->assertArrayHasKey('experience', $sections);
        $this->assertArrayHasKey('education', $sections);

        $this->assertStringContainsString('Experienced Laravel developer', $sections['summary']);
        $this->assertStringContainsString('PHP, Laravel', $sections['skills']);
    }

    public function test_it_handles_unknown_sections_as_general()
    {
        $rawText = "
            Some random header
            Some random text
        ";

        $splitter = new CVSectionSplitter();
        $sections = $splitter->split($rawText);

        $this->assertArrayHasKey('general', $sections);
        $this->assertStringContainsString('Some random text', $sections['general']);
    }
}
