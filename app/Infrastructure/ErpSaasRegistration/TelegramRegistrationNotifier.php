<?php

declare(strict_types=1);

namespace App\Infrastructure\ErpSaasRegistration;

use App\Domain\ErpSaasRegistration\RegisterTenantResult;
use App\Domain\ErpSaasRegistration\RegistrationNotifier;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class TelegramRegistrationNotifier implements RegistrationNotifier
{
    public function notifySuccess(RegisterTenantResult $result): void
    {
        $data = $result->data;
        $lines = [
            '🆕 ERP SaaS registration',
            'Company: '.($data['company_name'] ?? ''),
            'Contact: '.($data['contact_name'] ?? ''),
            'Email: '.($data['email'] ?? ''),
            'Phone: '.($data['phone'] ?? ''),
            'Modules: '.(
                is_array($data['modules'] ?? null)
                    ? implode(', ', $data['modules'])
                    : ''
            ),
            'Domain: '.($data['domain'] ?? ''),
            'URL: '.($data['tenant_url'] ?? ''),
            'Email sent: '.(($data['email_sent'] ?? false) ? 'yes' : 'no'),
            'Time: '.now()->toIso8601String(),
        ];
        $this->send(implode("\n", $lines));
    }

    public function notifyMailFailed(RegisterTenantResult $result): void
    {
        $data = $result->data;
        $this->send(implode("\n", [
            '⚠️ ERP SaaS welcome email FAILED',
            'Company: '.($data['company_name'] ?? ''),
            'Email: '.($data['email'] ?? ''),
            'URL: '.($data['tenant_url'] ?? ''),
            'Admin: reset password manually if needed.',
        ]));
    }

    private function send(string $text): void
    {
        $token = (string) config('services.telegram.bot_token');
        $chatId = (string) config('services.telegram.registration_chat_id');

        if ($token === '' || $chatId === '') {
            Log::info('Telegram registration notify skipped (not configured)', [
                'preview' => mb_substr($text, 0, 120),
            ]);

            return;
        }

        try {
            $response = Http::timeout(8)->asForm()->post(
                "https://api.telegram.org/bot{$token}/sendMessage",
                [
                    'chat_id' => $chatId,
                    'text' => $text,
                    'disable_web_page_preview' => true,
                ],
            );
            if (! $response->successful()) {
                Log::warning('Telegram registration notify failed', [
                    'status' => $response->status(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('Telegram registration notify exception', [
                'message' => $e->getMessage(),
            ]);
        }
    }
}
