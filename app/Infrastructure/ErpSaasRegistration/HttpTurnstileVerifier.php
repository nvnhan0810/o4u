<?php

declare(strict_types=1);

namespace App\Infrastructure\ErpSaasRegistration;

use App\Domain\ErpSaasRegistration\TurnstileVerifier;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class HttpTurnstileVerifier implements TurnstileVerifier
{
    public function verify(string $token, ?string $remoteIp): bool
    {
        $secret = (string) config('services.turnstile.secret_key');
        $bypass = (bool) config('services.turnstile.bypass');

        if ($bypass) {
            return true;
        }

        if ($secret === '') {
            Log::error('Turnstile secret key is not configured');

            return false;
        }

        if ($token === '') {
            return false;
        }

        $response = Http::asForm()
            ->timeout(10)
            ->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', array_filter([
                'secret' => $secret,
                'response' => $token,
                'remoteip' => $remoteIp,
            ]));

        if (! $response->successful()) {
            Log::warning('Turnstile siteverify HTTP failure', [
                'status' => $response->status(),
            ]);

            return false;
        }

        return (bool) $response->json('success');
    }
}
