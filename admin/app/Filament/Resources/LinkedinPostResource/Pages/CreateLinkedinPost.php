<?php

namespace App\Filament\Resources\LinkedinPostResource\Pages;

use App\Filament\Resources\LinkedinPostResource;
use App\Jobs\PublishLinkedinPost;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CreateLinkedinPost extends CreateRecord
{
    protected static string $resource = LinkedinPostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('chat_gemini')
                ->label('Chat Gemini')
                ->icon('heroicon-o-sparkles')
                ->color('info')
                ->modalHeading('Assistente Gemini para post')
                ->modalDescription('Descreva o que deseja e o Gemini gera/ajusta o texto do post.')
                ->modalSubmitActionLabel('Gerar texto')
                ->form([
                    Forms\Components\Textarea::make('prompt')
                        ->label('Pergunta / instrução')
                        ->rows(6)
                        ->required()
                        ->helperText('Exemplo: "Crie um post curto sobre arquitetura cloud para CEOs, com CTA no final".'),
                    Forms\Components\Select::make('generation_length')
                        ->label('Tamanho do texto')
                        ->options([
                            'short' => 'Curto',
                            'medium' => 'Médio',
                            'long' => 'Longo',
                        ])
                        ->default('long')
                        ->native(false)
                        ->required(),
                    Forms\Components\Select::make('apply_mode')
                        ->label('Aplicar no campo de texto')
                        ->options([
                            'replace' => 'Substituir texto atual',
                            'append'  => 'Adicionar ao final do texto atual',
                        ])
                        ->default('append')
                        ->native(false)
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $apiKey = trim((string) config('services.gemini.api_key'));

                    if ($apiKey === '') {
                        Notification::make()
                            ->title('GEMINI_API_KEY não configurada')
                            ->body('Configure GEMINI_API_KEY (ou GEMINI_API_KEY_FILE) para usar o assistente.')
                            ->danger()
                            ->send();

                        return;
                    }

                    $state = is_array($this->data ?? null) ? $this->data : [];
                    $currentText = trim((string) ($state['text'] ?? ''));

                    try {
                        $generatedText = $this->generateWithGemini(
                            prompt: (string) $data['prompt'],
                            currentText: $currentText,
                            generationLength: (string) ($data['generation_length'] ?? 'long'),
                        );

                        if ($generatedText === '') {
                            Notification::make()
                                ->title('Gemini não retornou conteúdo')
                                ->warning()
                                ->send();

                            return;
                        }

                        $mode = (string) ($data['apply_mode'] ?? 'append');

                        $newText = $mode === 'replace'
                            ? $generatedText
                            : trim($currentText === '' ? $generatedText : $currentText."\n\n".$generatedText);

                        $this->data = array_merge($state, [
                            'text' => $newText,
                        ]);

                        $this->form->fill($this->data);

                        Notification::make()
                            ->title('Texto gerado com Gemini')
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        Log::error('Falha ao gerar texto com Gemini', [
                            'error' => $e->getMessage(),
                        ]);

                        Notification::make()
                            ->title('Erro ao consultar Gemini')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['status'] = ! empty($data['scheduled_at']) ? 'scheduled' : 'draft';

        return $data;
    }

    protected function afterCreate(): void
    {
        $record = $this->getRecord();

        if ($record->status === 'scheduled' && $record->scheduled_at !== null) {
            $delay = max(0, now()->diffInSeconds($record->scheduled_at, false));
            PublishLinkedinPost::dispatch($record->id)->delay($delay);
        }
    }

    private function generateWithGemini(string $prompt, string $currentText = '', string $generationLength = 'long'): string
    {
        $apiKey = trim((string) config('services.gemini.api_key'));
        $model = trim((string) config('services.gemini.model', 'gemini-1.5-flash'));

        $lengthInstruction = match ($generationLength) {
            'short' => 'Gere um post curto, direto, entre 400 e 700 caracteres.',
            'medium' => 'Gere um post de tamanho médio, entre 800 e 1400 caracteres.',
            default => 'Gere um post longo e desenvolvido, entre 1500 e 2300 caracteres.',
        };

        $systemInstruction = implode("\n", [
            'Você é um assistente especialista em copy para LinkedIn.',
            'Responda em pt-BR e entregue somente o texto final do post, sem markdown, sem cercas de código e sem explicações extras.',
            $lengthInstruction,
            'Estruture o texto com: gancho inicial forte, desenvolvimento consistente, conclusão prática e CTA final.',
            'Quando fizer sentido, inclua hashtags relevantes no final.',
            'Evite respostas curtas, incompletas, truncadas ou repetidas.',
        ]);

        $userPrompt = $systemInstruction."\n\n"
            .'Contexto do texto atual (pode estar vazio):' . "\n"
            .($currentText !== '' ? $currentText : '(vazio)') . "\n\n"
            .'Pedido do usuário:' . "\n"
            .$prompt;

        $response = Http::timeout(30)
            ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}", [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $userPrompt],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'maxOutputTokens' => 2048,
                ],
            ])
            ->throw();

        $generatedText = (string) data_get($response->json(), 'candidates.0.content.parts.0.text', '');

        return trim($generatedText);
    }
}
