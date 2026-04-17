<?php

namespace App\Support;

use InvalidArgumentException;

class OnboardingUrl
{
    public static function normalize(string $value): string
    {
        $url = trim($value);

        if ($url === '') {
            throw new InvalidArgumentException('La URL es obligatoria.');
        }

        if (! preg_match('/^[a-z][a-z0-9+\-.]*:\/\//i', $url)) {
            $url = 'https://'.$url;
        }

        $parts = parse_url($url);

        if (! is_array($parts) || empty($parts['scheme']) || empty($parts['host'])) {
            throw new InvalidArgumentException('La URL no es valida.');
        }

        $scheme = strtolower((string) $parts['scheme']);
        $host = strtolower((string) $parts['host']);

        if (! in_array($scheme, ['http', 'https'], true)) {
            throw new InvalidArgumentException('Solo se admiten URLs http y https.');
        }

        $normalized = $scheme.'://'.$host;

        if (isset($parts['port'])) {
            $normalized .= ':'.$parts['port'];
        }

        $path = $parts['path'] ?? '/';
        $normalized .= $path !== '' ? $path : '/';

        if (! empty($parts['query'])) {
            $normalized .= '?'.$parts['query'];
        }

        return $normalized;
    }

    public static function host(string $url): ?string
    {
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));

        return $host !== '' ? $host : null;
    }

    public static function isAllowed(string $url): bool
    {
        $parts = parse_url($url);

        if (! is_array($parts)) {
            return false;
        }

        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $host = strtolower((string) ($parts['host'] ?? ''));

        if (! in_array($scheme, ['http', 'https'], true) || $host === '') {
            return false;
        }

        if ($host === 'localhost' || str_ends_with($host, '.local') || str_ends_with($host, '.internal')) {
            return false;
        }

        if (filter_var($host, FILTER_VALIDATE_IP)) {
            $isPublicIp = filter_var(
                $host,
                FILTER_VALIDATE_IP,
                FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
            );

            return (bool) $isPublicIp;
        }

        return true;
    }

    public static function assertAllowed(string $url): void
    {
        if (! self::isAllowed($url)) {
            throw new InvalidArgumentException('La URL indicada no es valida para el configurador.');
        }
    }
}
