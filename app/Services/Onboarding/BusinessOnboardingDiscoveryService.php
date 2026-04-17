<?php

namespace App\Services\Onboarding;

use App\Models\BusinessOnboardingSession;
use App\Support\OnboardingUrl;
use DOMDocument;
use DOMXPath;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class BusinessOnboardingDiscoveryService
{
    private const MAX_PAGES = 6;

    private const MAX_BODY_BYTES = 750000;

    /**
     * @var array<int, string>
     */
    private const EXPERIENCE_KEYWORDS = [
        'experiencia',
        'experiencias',
        'visita',
        'visitas',
        'cata',
        'catas',
        'degustacion',
        'wine',
        'tasting',
        'tour',
        'tours',
        'enoturismo',
        'servicio',
        'servicios',
    ];

    /**
     * @var array<int, string>
     */
    private const CONTACT_KEYWORDS = [
        'contacto',
        'contact',
        'reserva',
        'reservas',
        'booking',
        'book',
    ];

    /**
     * @var array<int, string>
     */
    private const HOURS_KEYWORDS = [
        'horario',
        'horarios',
        'hora',
        'hours',
        'opening',
    ];

    public function run(BusinessOnboardingSession $session): BusinessOnboardingSession
    {
        OnboardingUrl::assertAllowed($session->source_url);

        $session->update([
            'status' => BusinessOnboardingSession::STATUS_DISCOVERING,
            'last_error' => null,
            'discovery_started_at' => now(),
            'discovery_finished_at' => null,
        ]);

        $session->sources()->delete();

        $queue = [[
            'url' => $session->source_url,
            'priority' => 100,
            'role' => 'home',
        ]];

        $visited = [];
        $pages = [];
        $emails = [];
        $phones = [];
        $addresses = [];
        $openingHours = [];
        $experienceCandidates = [];
        $notes = [];

        while ($queue !== [] && count($visited) < self::MAX_PAGES) {
            usort($queue, static fn (array $left, array $right) => ($right['priority'] ?? 0) <=> ($left['priority'] ?? 0));
            $current = array_shift($queue);

            if (! is_array($current) || ! isset($current['url'])) {
                continue;
            }

            $url = (string) $current['url'];

            if (isset($visited[$url])) {
                continue;
            }

            $visited[$url] = true;
            $page = $this->fetchPage($url);
            $pages[] = $page;

            $session->sources()->create([
                'url' => $url,
                'page_role' => $page['role'],
                'title' => $page['title'],
                'http_status' => $page['http_status'],
                'content_type' => $page['content_type'],
                'extracted_payload' => [
                    'description' => $page['description'],
                    'h1' => $page['h1'],
                    'emails' => $page['emails'],
                    'phones' => $page['phones'],
                    'opening_hours' => $page['opening_hours'],
                ],
                'discovered_at' => now(),
            ]);

            foreach ($page['emails'] as $email) {
                $emails[$email] = true;
            }

            foreach ($page['phones'] as $phone) {
                $phones[$phone] = true;
            }

            if ($page['address'] !== null) {
                $addresses[$page['address']] = true;
            }

            foreach ($page['opening_hours'] as $row) {
                $key = is_array($row) ? md5(json_encode($row)) : md5((string) $row);
                $openingHours[$key] = $row;
            }

            $candidate = $this->buildExperienceCandidate($page);

            if ($candidate !== null) {
                $experienceCandidates[$candidate['source_url']] = $candidate;
            }

            foreach ($this->prioritizeLinks($page['links'], (string) $session->source_host) as $link) {
                if (! isset($visited[$link['url']]) && ! $this->queueContains($queue, $link['url'])) {
                    $queue[] = $link;
                }
            }
        }

        if ($pages === []) {
            $notes[] = 'No se ha podido obtener ninguna pagina valida de la web indicada.';
        }

        if ($openingHours === []) {
            $notes[] = 'No se han detectado horarios estructurados; conviene revisarlos a mano.';
        }

        if ($experienceCandidates === []) {
            $notes[] = 'No se han encontrado experiencias claras; habra que completarlas en el back o con la siguiente iteracion del agente.';
        }

        $draft = $this->buildDraft(
            $session,
            $pages,
            array_keys($emails),
            array_keys($phones),
            array_keys($addresses),
            array_values($openingHours),
            array_values($experienceCandidates),
            $notes,
        );

        $status = $this->resolveStatus($pages, $draft['missing_required_fields']);

        $session->update([
            'status' => $status,
            'draft_payload' => $draft,
            'missing_required_fields' => $draft['missing_required_fields'],
            'discovery_finished_at' => now(),
            'last_error' => null,
        ]);

        return $session->fresh(['requestedTipoNegocio', 'sources']);
    }

    /**
     * @return array{
     *     url:string,
     *     http_status:int|null,
     *     content_type:string|null,
     *     title:?string,
     *     description:?string,
     *     h1:?string,
     *     site_name:?string,
     *     role:string,
     *     links:array<int, array{url:string, label:string, priority:int, role:string}>,
     *     emails:array<int, string>,
     *     phones:array<int, string>,
     *     address:?string,
     *     opening_hours:array<int, array<string, mixed>|string>
     * }
     */
    private function fetchPage(string $url): array
    {
        $response = Http::timeout(20)
            ->withUserAgent('ClockiaOnboardingDiscovery/1.0 (+https://clockia.net)')
            ->withOptions([
                'allow_redirects' => ['max' => 5],
            ])
            ->get($url);

        $contentType = strtolower(trim((string) explode(';', (string) $response->header('Content-Type'))[0]));
        $page = [
            'url' => $url,
            'http_status' => $response->status(),
            'content_type' => $contentType !== '' ? $contentType : null,
            'title' => null,
            'description' => null,
            'h1' => null,
            'site_name' => null,
            'role' => 'general',
            'links' => [],
            'emails' => [],
            'phones' => [],
            'address' => null,
            'opening_hours' => [],
        ];

        if (! $response->successful() || ! str_contains($contentType, 'html')) {
            return $page;
        }

        $html = substr((string) $response->body(), 0, self::MAX_BODY_BYTES);
        $parsed = $this->parseHtml($url, $html);

        return array_replace($page, $parsed);
    }

    /**
     * @return array<string, mixed>
     */
    private function parseHtml(string $url, string $html): array
    {
        libxml_use_internal_errors(true);

        $dom = new DOMDocument;
        $dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));

        libxml_clear_errors();

        $xpath = new DOMXPath($dom);
        $title = $this->firstText($xpath, '//title');
        $h1 = $this->firstText($xpath, '//h1');
        $siteName = $this->firstMetaContent($xpath, ['og:site_name']);
        $description = $this->firstMetaContent($xpath, ['description', 'og:description', 'twitter:description']);
        $links = $this->extractLinks($xpath, $url);
        $bodyText = $this->normalizeWhitespace((string) optional($xpath->query('//body')->item(0))->textContent);
        $jsonLdObjects = $this->extractJsonLdObjects($xpath);

        $emails = array_values(array_unique(array_merge(
            $this->extractEmails($bodyText),
            $this->extractEmailsFromJsonLd($jsonLdObjects),
        )));

        $phones = array_values(array_unique(array_merge(
            $this->extractPhones($bodyText),
            $this->extractPhonesFromJsonLd($jsonLdObjects),
        )));

        $address = $this->extractAddressFromJsonLd($jsonLdObjects);
        $openingHours = $this->extractOpeningHoursFromJsonLd($jsonLdObjects);
        $role = $this->detectPageRole($url, $title, $h1);

        return [
            'title' => $title,
            'description' => $description,
            'h1' => $h1,
            'site_name' => $siteName,
            'role' => $role,
            'links' => $links,
            'emails' => $emails,
            'phones' => $phones,
            'address' => $address,
            'opening_hours' => $openingHours,
        ];
    }

    /**
     * @param  array<int, array{url:string, label:string, priority:int, role:string}>  $links
     * @return array<int, array{url:string, priority:int, role:string}>
     */
    private function prioritizeLinks(array $links, string $host): array
    {
        $filtered = [];

        foreach ($links as $link) {
            $url = $link['url'];
            $linkHost = OnboardingUrl::host($url);

            if ($linkHost === null || ! $this->sameHost($host, $linkHost) || ! OnboardingUrl::isAllowed($url)) {
                continue;
            }

            if ($this->isSkippableLink($url)) {
                continue;
            }

            $filtered[] = [
                'url' => $url,
                'priority' => $link['priority'],
                'role' => $link['role'],
            ];
        }

        usort($filtered, static fn (array $left, array $right) => $right['priority'] <=> $left['priority']);

        return array_slice($filtered, 0, 12);
    }

    /**
     * @param  array<int, array{url:string, priority:int, role:string}>  $queue
     */
    private function queueContains(array $queue, string $url): bool
    {
        foreach ($queue as $item) {
            if (($item['url'] ?? null) === $url) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<int, array<string, mixed>>  $pages
     * @param  array<int, string>  $emails
     * @param  array<int, string>  $phones
     * @param  array<int, string>  $addresses
     * @param  array<int, array<string, mixed>|string>  $openingHours
     * @param  array<int, array<string, mixed>>  $experienceCandidates
     * @param  array<int, string>  $notes
     * @return array<string, mixed>
     */
    private function buildDraft(
        BusinessOnboardingSession $session,
        array $pages,
        array $emails,
        array $phones,
        array $addresses,
        array $openingHours,
        array $experienceCandidates,
        array $notes
    ): array {
        $home = $pages[0] ?? [];
        $businessName = $session->requested_business_name
            ?: $this->firstFilled([
                Arr::get($home, 'site_name'),
                $this->nameFromTitle((string) Arr::get($home, 'title')),
                Arr::get($home, 'h1'),
            ]);

        $description = $this->firstFilled(array_map(
            static fn (array $page) => $page['description'] ?? null,
            $pages
        ));

        $business = [
            'nombre' => $businessName,
            'tipo_negocio_id' => $session->requested_tipo_negocio_id,
            'email' => $emails[0] ?? null,
            'telefono' => $phones[0] ?? null,
            'zona_horaria' => 'Europe/Madrid',
            'activo' => true,
            'dias_apertura' => $this->extractOpenDays($openingHours),
            'descripcion_publica' => $description,
            'direccion' => $addresses[0] ?? null,
            'url_publica' => $session->source_url,
            'permite_modificacion' => true,
        ];

        $admin = [
            'name' => $session->requested_admin_name ?: ($businessName ? 'Admin '.$businessName : null),
            'email' => $session->requested_admin_email,
            'password_ready' => $session->requested_admin_password_hash !== null,
        ];

        $draft = [
            'business' => $business,
            'admin' => $admin,
            'experience_candidates' => $experienceCandidates,
            'opening_hours' => $openingHours,
            'notes' => array_values(array_unique(array_filter($notes))),
            'pages' => array_values(array_map(static fn (array $page) => [
                'url' => $page['url'] ?? null,
                'title' => $page['title'] ?? null,
                'role' => $page['role'] ?? null,
            ], $pages)),
        ];

        $draft['missing_required_fields'] = $this->computeMissingRequiredFields($business, $admin);

        if ($business['zona_horaria'] === 'Europe/Madrid') {
            $draft['notes'][] = 'Se propone Europe/Madrid como zona horaria inicial.';
        }

        return $draft;
    }

    /**
     * @param  array<string, mixed>  $business
     * @param  array<string, mixed>  $admin
     * @return array<int, string>
     */
    private function computeMissingRequiredFields(array $business, array $admin): array
    {
        $missing = [];

        if (! filled($business['nombre'] ?? null)) {
            $missing[] = 'business.nombre';
        }

        if (! filled($business['tipo_negocio_id'] ?? null)) {
            $missing[] = 'business.tipo_negocio_id';
        }

        if (! filled($admin['email'] ?? null)) {
            $missing[] = 'admin.email';
        }

        if (($admin['password_ready'] ?? false) !== true) {
            $missing[] = 'admin.password';
        }

        return $missing;
    }

    /**
     * @param  array<int, array<string, mixed>>  $pages
     * @param  array<int, string>  $missingRequiredFields
     */
    private function resolveStatus(array $pages, array $missingRequiredFields): string
    {
        $hasSuccessfulPage = collect($pages)->contains(
            static fn (array $page) => (($page['http_status'] ?? 0) >= 200) && (($page['http_status'] ?? 0) < 400)
        );

        if (! $hasSuccessfulPage) {
            return BusinessOnboardingSession::STATUS_FAILED;
        }

        return $missingRequiredFields === []
            ? BusinessOnboardingSession::STATUS_READY_FOR_REVIEW
            : BusinessOnboardingSession::STATUS_NEEDS_INPUT;
    }

    /**
     * @return array<int, array{url:string, label:string, priority:int, role:string}>
     */
    private function extractLinks(DOMXPath $xpath, string $baseUrl): array
    {
        $links = [];

        foreach ($xpath->query('//a[@href]') as $anchor) {
            $href = trim((string) $anchor->attributes?->getNamedItem('href')?->nodeValue);
            $label = $this->normalizeWhitespace((string) $anchor->textContent);
            $absoluteUrl = $this->resolveUrl($baseUrl, $href);

            if ($absoluteUrl === null) {
                continue;
            }

            $role = $this->detectPageRole($absoluteUrl, $label, $label);
            $links[] = [
                'url' => $absoluteUrl,
                'label' => $label,
                'priority' => $this->linkPriority($absoluteUrl, $label),
                'role' => $role,
            ];
        }

        return $links;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function extractJsonLdObjects(DOMXPath $xpath): array
    {
        $objects = [];

        foreach ($xpath->query('//script[@type="application/ld+json"]') as $script) {
            $decoded = json_decode((string) $script->textContent, true);

            if ($decoded === null) {
                continue;
            }

            foreach ($this->flattenJsonLd($decoded) as $object) {
                if (is_array($object)) {
                    $objects[] = $object;
                }
            }
        }

        return $objects;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function flattenJsonLd(mixed $payload): array
    {
        if (! is_array($payload)) {
            return [];
        }

        $objects = [];

        if (! array_is_list($payload)) {
            $objects[] = $payload;

            if (isset($payload['@graph']) && is_array($payload['@graph'])) {
                foreach ($this->flattenJsonLd($payload['@graph']) as $graphNode) {
                    $objects[] = $graphNode;
                }
            }

            return $objects;
        }

        foreach ($payload as $item) {
            foreach ($this->flattenJsonLd($item) as $node) {
                $objects[] = $node;
            }
        }

        return $objects;
    }

    /**
     * @param  array<int, array<string, mixed>>  $objects
     * @return array<int, string>
     */
    private function extractEmailsFromJsonLd(array $objects): array
    {
        $emails = [];

        foreach ($objects as $object) {
            $email = $object['email'] ?? null;

            if (is_string($email)) {
                $emails = array_merge($emails, $this->extractEmails($email));
            }
        }

        return array_values(array_unique($emails));
    }

    /**
     * @param  array<int, array<string, mixed>>  $objects
     * @return array<int, string>
     */
    private function extractPhonesFromJsonLd(array $objects): array
    {
        $phones = [];

        foreach ($objects as $object) {
            $phone = $object['telephone'] ?? null;

            if (is_string($phone)) {
                $phones = array_merge($phones, $this->extractPhones($phone));
            }
        }

        return array_values(array_unique($phones));
    }

    /**
     * @param  array<int, array<string, mixed>>  $objects
     */
    private function extractAddressFromJsonLd(array $objects): ?string
    {
        foreach ($objects as $object) {
            $address = $object['address'] ?? null;

            if (is_string($address) && trim($address) !== '') {
                return $this->normalizeWhitespace($address);
            }

            if (is_array($address)) {
                $parts = array_filter([
                    $address['streetAddress'] ?? null,
                    $address['postalCode'] ?? null,
                    $address['addressLocality'] ?? null,
                    $address['addressRegion'] ?? null,
                    $address['addressCountry'] ?? null,
                ]);

                if ($parts !== []) {
                    return $this->normalizeWhitespace(implode(', ', $parts));
                }
            }
        }

        return null;
    }

    /**
     * @param  array<int, array<string, mixed>>  $objects
     * @return array<int, array<string, mixed>|string>
     */
    private function extractOpeningHoursFromJsonLd(array $objects): array
    {
        $rows = [];

        foreach ($objects as $object) {
            $specifications = $object['openingHoursSpecification'] ?? null;
            $flatSpecs = is_array($specifications) && array_is_list($specifications)
                ? $specifications
                : (is_array($specifications) ? [$specifications] : []);

            foreach ($flatSpecs as $specification) {
                if (! is_array($specification)) {
                    continue;
                }

                $days = Arr::wrap($specification['dayOfWeek'] ?? []);
                $mappedDays = array_values(array_filter(array_map(
                    fn ($day) => $this->mapSchemaDay($day),
                    $days
                ), static fn ($day) => $day !== null));

                $rows[] = array_filter([
                    'days' => $mappedDays,
                    'opens' => $specification['opens'] ?? null,
                    'closes' => $specification['closes'] ?? null,
                ], static fn ($value) => $value !== null && $value !== []);
            }

            $rawHours = $object['openingHours'] ?? null;

            foreach (Arr::wrap($rawHours) as $rawHour) {
                if (is_string($rawHour) && trim($rawHour) !== '') {
                    $rows[] = $this->normalizeWhitespace($rawHour);
                }
            }
        }

        return array_values(array_filter($rows));
    }

    private function firstText(DOMXPath $xpath, string $expression): ?string
    {
        $value = $xpath->evaluate("string({$expression}[1])");
        $value = is_string($value) ? $this->normalizeWhitespace($value) : '';

        return $value !== '' ? $value : null;
    }

    /**
     * @param  array<int, string>  $properties
     */
    private function firstMetaContent(DOMXPath $xpath, array $properties): ?string
    {
        foreach ($properties as $property) {
            $escaped = str_replace("'", "\\'", $property);
            $value = $xpath->evaluate("string((//meta[@name='{$escaped}' or @property='{$escaped}']/@content)[1])");
            $value = is_string($value) ? $this->normalizeWhitespace($value) : '';

            if ($value !== '') {
                return $value;
            }
        }

        return null;
    }

    /**
     * @return array<int, string>
     */
    private function extractEmails(string $text): array
    {
        preg_match_all('/[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}/iu', $text, $matches);

        return array_values(array_unique(array_map(
            static fn ($email) => strtolower((string) $email),
            $matches[0] ?? []
        )));
    }

    /**
     * @return array<int, string>
     */
    private function extractPhones(string $text): array
    {
        preg_match_all('/(?:(?:\+|00)\d{1,3}[\s\-\.]*)?(?:\(?\d{2,4}\)?[\s\-\.]*){2,5}\d{2,4}/u', $text, $matches);

        $phones = [];

        foreach ($matches[0] ?? [] as $match) {
            $normalized = preg_replace('/\s+/u', ' ', trim((string) $match));
            $digitsOnly = preg_replace('/\D+/u', '', (string) $normalized);

            if ($normalized !== '' && strlen((string) $digitsOnly) >= 9) {
                $phones[] = $normalized;
            }
        }

        return array_values(array_unique($phones));
    }

    private function detectPageRole(string $url, ?string $title, ?string $h1): string
    {
        $haystack = mb_strtolower($url.' '.($title ?? '').' '.($h1 ?? ''));

        if ($this->containsAny($haystack, self::CONTACT_KEYWORDS)) {
            return 'contact';
        }

        if ($this->containsAny($haystack, self::HOURS_KEYWORDS)) {
            return 'hours';
        }

        if ($this->containsAny($haystack, self::EXPERIENCE_KEYWORDS)) {
            return 'experience';
        }

        if (str_contains($haystack, 'blog') || str_contains($haystack, 'noticia')) {
            return 'blog';
        }

        return 'general';
    }

    private function buildExperienceCandidate(array $page): ?array
    {
        if (($page['role'] ?? 'general') !== 'experience') {
            return null;
        }

        $name = $this->firstFilled([
            $page['h1'] ?? null,
            $this->nameFromTitle((string) ($page['title'] ?? '')),
        ]);

        if (! filled($name)) {
            return null;
        }

        return [
            'nombre' => $name,
            'descripcion' => $page['description'] ?? null,
            'source_url' => $page['url'] ?? null,
        ];
    }

    private function linkPriority(string $url, string $label): int
    {
        $haystack = mb_strtolower($url.' '.$label);

        if ($this->containsAny($haystack, self::EXPERIENCE_KEYWORDS)) {
            return 90;
        }

        if ($this->containsAny($haystack, self::CONTACT_KEYWORDS)) {
            return 80;
        }

        if ($this->containsAny($haystack, self::HOURS_KEYWORDS)) {
            return 70;
        }

        if (str_contains($haystack, 'about') || str_contains($haystack, 'quienes-somos') || str_contains($haystack, 'nosotros')) {
            return 40;
        }

        return 10;
    }

    private function resolveUrl(string $baseUrl, string $href): ?string
    {
        if ($href === '' || str_starts_with($href, '#') || str_starts_with($href, 'javascript:')) {
            return null;
        }

        if (str_starts_with($href, 'mailto:') || str_starts_with($href, 'tel:')) {
            return null;
        }

        if (preg_match('/^[a-z][a-z0-9+\-.]*:\/\//i', $href)) {
            try {
                return OnboardingUrl::normalize($href);
            } catch (\Throwable) {
                return null;
            }
        }

        $base = parse_url($baseUrl);

        if (! is_array($base) || empty($base['scheme']) || empty($base['host'])) {
            return null;
        }

        $scheme = strtolower((string) $base['scheme']);
        $host = strtolower((string) $base['host']);
        $port = isset($base['port']) ? ':'.$base['port'] : '';

        if (str_starts_with($href, '//')) {
            try {
                return OnboardingUrl::normalize($scheme.':'.$href);
            } catch (\Throwable) {
                return null;
            }
        }

        if (str_starts_with($href, '/')) {
            try {
                return OnboardingUrl::normalize($scheme.'://'.$host.$port.$href);
            } catch (\Throwable) {
                return null;
            }
        }

        $basePath = (string) ($base['path'] ?? '/');
        $directory = rtrim(str_replace('\\', '/', dirname($basePath)), '/');
        $directory = $directory === '.' ? '' : $directory;

        try {
            return OnboardingUrl::normalize($scheme.'://'.$host.$port.$directory.'/'.$href);
        } catch (\Throwable) {
            return null;
        }
    }

    private function isSkippableLink(string $url): bool
    {
        $lower = mb_strtolower($url);

        if (str_contains($lower, '/wp-admin') || str_contains($lower, '/login') || str_contains($lower, '/cart')) {
            return true;
        }

        foreach (['.jpg', '.jpeg', '.png', '.gif', '.svg', '.webp', '.pdf', '.zip'] as $extension) {
            if (str_contains($lower, $extension)) {
                return true;
            }
        }

        return false;
    }

    private function sameHost(string $left, string $right): bool
    {
        $normalize = static fn (string $host) => preg_replace('/^www\./i', '', strtolower($host));

        return $normalize($left) === $normalize($right);
    }

    private function containsAny(string $haystack, array $keywords): bool
    {
        foreach ($keywords as $keyword) {
            if (str_contains($haystack, $keyword)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<int, array<string, mixed>|string>  $openingHours
     * @return array<int, int>|null
     */
    private function extractOpenDays(array $openingHours): ?array
    {
        $days = [];

        foreach ($openingHours as $row) {
            if (is_array($row) && isset($row['days']) && is_array($row['days'])) {
                foreach ($row['days'] as $day) {
                    if (is_int($day) && $day >= 0 && $day <= 6) {
                        $days[] = $day;
                    }
                }
            }
        }

        if ($days === []) {
            return null;
        }

        sort($days);

        return array_values(array_unique($days));
    }

    private function mapSchemaDay(mixed $day): ?int
    {
        $value = mb_strtolower((string) $day);
        $value = preg_replace('#^.*/#', '', $value);

        return match ($value) {
            'sunday' => 0,
            'monday' => 1,
            'tuesday' => 2,
            'wednesday' => 3,
            'thursday' => 4,
            'friday' => 5,
            'saturday' => 6,
            default => null,
        };
    }

    /**
     * @param  array<int, string|null>  $values
     */
    private function firstFilled(array $values): ?string
    {
        foreach ($values as $value) {
            if (filled($value)) {
                return trim((string) $value);
            }
        }

        return null;
    }

    private function nameFromTitle(string $title): ?string
    {
        $clean = trim($title);

        if ($clean === '') {
            return null;
        }

        $parts = preg_split('/\s*[|\-–—·]\s*/u', $clean) ?: [];

        if ($parts === []) {
            return $clean;
        }

        $last = trim((string) end($parts));

        return $last !== '' ? $last : $clean;
    }

    private function normalizeWhitespace(string $value): string
    {
        return preg_replace('/\s+/u', ' ', trim($value)) ?? trim($value);
    }
}
