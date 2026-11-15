<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/** Menjaga JSON soal tetap utuh saat diedit manual. */
class ContentDataTest extends TestCase
{
    public function test_every_question_is_well_formed(): void
    {
        $dir = dirname(__DIR__, 2).'/database/seeders/data/questions';
        $files = glob("$dir/*.json");
        $this->assertNotEmpty($files);

        $codes = [];
        foreach ($files as $file) {
            $set = json_decode(file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
            foreach ($set['questions'] as $q) {
                $this->assertNotContains($q['id'], $codes, 'kode ganda: '.$q['id']);
                $codes[] = $q['id'];
                $this->assertCount(3, $q['hints'], $q['id']);
                $this->assertIsBool($q['verified']);
                $this->assertNotEmpty($q['explanation']);

                if ($q['type'] === 'completion') {
                    $ids = array_column($q['options'], 'id');
                    $this->assertContains($q['answer']['option'], $ids, $q['id']);
                    if (empty($q['meta']['noBlank'])) {
                        $this->assertStringContainsString('_____', $q['text'], $q['id']);
                    }
                    if (isset($q['meta']['script'])) {
                        $this->assertNotEmpty($q['meta']['script'], $q['id'].' butuh script');
                        $this->assertContains($q['meta']['scriptSource'], ['book', 'reconstructed'], $q['id']);
                        if ($q['meta']['scriptSource'] === 'reconstructed') {
                            $this->assertFalse($q['verified'], $q['id'].' rekonstruksi tidak boleh verified');
                        }
                    }
                    if (isset($q['meta']['passage'])) {
                        $this->assertNotEmpty($q['meta']['passage']['lines'], $q['id'].' passage kosong');
                    }
                } else {
                    $this->assertContains($q['answer']['kind'], ['segment', 'missing', 'correct'], $q['id']);
                    if ($q['answer']['kind'] === 'segment') {
                        $this->assertArrayHasKey($q['answer']['index'], $q['segments'], $q['id']);
                        $this->assertNotContains($q['answer']['index'], $q['meta']['plain'] ?? [], $q['id'].' kunci di segmen non-tap');
                    }
                }
            }
        }
    }
}
