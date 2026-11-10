<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GeminiService
{
    public function configured(): bool
    {
        return filled(config('services.gemini.key'));
    }

    public function systemPrompt(): string
    {
        return trim(file_get_contents(resource_path('prompts/tutor_system.txt')));
    }

    /**
     * @param  array<int, array{role: string, text: string}>  $turns  urutan user/model, terakhir harus user
     *
     * @throws RuntimeException pesan sudah aman ditampilkan ke pengguna
     */
    public function generate(array $turns): string
    {
        if (! $this->configured()) {
            throw new RuntimeException('GEMINI_API_KEY belum diisi.');
        }

        $model = config('services.gemini.model');
        $contents = array_map(fn ($t) => [
            'role' => $t['role'],
            'parts' => [['text' => $t['text']]],
        ], $turns);

        try {
            $response = Http::timeout((int) config('services.gemini.timeout'))
                ->withHeaders(['x-goog-api-key' => config('services.gemini.key')])
                ->acceptJson()
                ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent", [
                    'systemInstruction' => ['parts' => [['text' => $this->systemPrompt()]]],
                    'contents' => $contents,
                    'generationConfig' => [
                        'maxOutputTokens' => 1200,
                        'thinkingConfig' => ['thinkingLevel' => 'low'],
                    ],
                ]);
        } catch (ConnectionException) {
            throw new RuntimeException('Tidak bisa terhubung ke Gemini. Cek koneksi lalu coba lagi.');
        }

        if ($response->failed()) {
            $status = $response->status();
            throw new RuntimeException(match (true) {
                in_array($status, [400, 403], true) && str_contains(strtolower($response->body()), 'api key') => 'API key Gemini ditolak. Cek GEMINI_API_KEY di .env.',
                $status === 404 => "Model '{$model}' tidak ditemukan. Cek GEMINI_MODEL di .env.",
                $status === 429 => 'Kuota Gemini sedang habis atau terlalu cepat. Coba lagi sebentar.',
                $status >= 500 => 'Gemini sedang bermasalah. Coba lagi nanti.',
                default => 'Permintaan ke Gemini gagal (kode '.$status.').',
            });
        }

        $parts = $response->json('candidates.0.content.parts') ?? [];
        $text = trim(collect($parts)->where('thought', '!=', true)->pluck('text')->filter()->implode("\n"));

        if ($text === '') {
            throw new RuntimeException('Gemini tidak memberi jawaban (mungkin diblokir filter). Coba ulangi dengan kalimat lain.');
        }

        return $text;
    }
}
