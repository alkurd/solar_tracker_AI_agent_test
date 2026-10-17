<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class TrackerPlcClient
{
    private const ALLOWED_COMMANDS = [
        'oost',
        'west',
        'poot_omhoog',
        'poot_omlaag',
        'toggle_modus',
        'noodstop',
        'noodstop_reset',
        'set_mode',
    ];

    public function send(string $command, array $parameters = []): Response
    {
        if (! in_array($command, self::ALLOWED_COMMANDS, true)) {
            throw new RuntimeException('The requested PLC command is not allowed.');
        }

        $baseUrl = config('services.tracker.plc_url');

        if (
            ! is_string($baseUrl)
            || filter_var($baseUrl, FILTER_VALIDATE_URL) === false
            || ! in_array(parse_url($baseUrl, PHP_URL_SCHEME), ['http', 'https'], true)
        ) {
            throw new RuntimeException('The PLC API URL must be a valid HTTP(S) URL.');
        }

        $url = rtrim($baseUrl, '/').'/'.ltrim((string) config('services.tracker.plc_command_path'), '/');
        $request = Http::acceptJson()
            ->asJson()
            ->timeout(max(1, (int) config('services.tracker.plc_timeout', 5)));

        $token = config('services.tracker.plc_token');
        if (is_string($token) && $token !== '') {
            $request = $request->withToken($token);
        }

        try {
            return $request->post($url, array_merge($parameters, ['command' => $command]));
        } catch (ConnectionException $exception) {
            throw new RuntimeException('The PLC API could not be reached.', previous: $exception);
        }
    }
}