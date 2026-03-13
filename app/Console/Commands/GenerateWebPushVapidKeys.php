<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Minishlink\WebPush\VAPID;

class GenerateWebPushVapidKeys extends Command
{
    protected $signature = 'webpush:vapid
        {--write : Write generated values into the local .env file}
        {--force : Overwrite existing WEBPUSH_VAPID_* values when writing}';

    protected $description = 'Generate VAPID keys for web push notifications';

    public function handle(): int
    {
        $keys = VAPID::createVapidKeys();
        $subject = (string) config('webpush.vapid.subject', 'mailto:support@example.com');

        $lines = [
            'WEBPUSH_ENABLED=true',
            'WEBPUSH_VAPID_SUBJECT=' . $subject,
            'WEBPUSH_VAPID_PUBLIC_KEY=' . $keys['publicKey'],
            'WEBPUSH_VAPID_PRIVATE_KEY=' . $keys['privateKey'],
        ];

        $this->info('Generated VAPID keys:');
        foreach ($lines as $line) {
            $this->line($line);
        }

        if ($this->option('write')) {
            $this->writeToEnvironment($lines, (bool) $this->option('force'));
            $this->info('.env updated.');
        }

        return self::SUCCESS;
    }

    private function writeToEnvironment(array $lines, bool $force): void
    {
        $envPath = base_path('.env');
        $contents = is_file($envPath) ? (string) file_get_contents($envPath) : '';
        $contents = rtrim($contents);

        foreach ($lines as $line) {
            [$key, $value] = explode('=', $line, 2);
            $pattern = '/^' . preg_quote($key, '/') . '=.*$/m';

            if (preg_match($pattern, $contents)) {
                if (! $force && filled(env($key))) {
                    continue;
                }

                $contents = preg_replace($pattern, $key . '=' . $value, $contents) ?? $contents;
                continue;
            }

            $contents .= PHP_EOL . $key . '=' . $value;
        }

        file_put_contents($envPath, $contents . PHP_EOL);
    }
}
