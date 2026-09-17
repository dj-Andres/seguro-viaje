<?php

namespace App\Services;

use App\Models\Country;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class CountryService
{
    private const CACHE_KEY = 'countries.list';

    /**
     * Retrieve the list of normalized countries.
     * Strategy: cache -> external API -> local database snapshot.
     */
    public function list(): Collection
    {
        $cached = Cache::get(self::CACHE_KEY);

        if ($cached !== null) {
            return collect($cached);
        }

        $countries = $this->fetchFromApi();

        if ($countries->isEmpty()) {
            $countries = $this->fallbackFromDatabase();
        }

        $data = $countries->all();

        Cache::put(self::CACHE_KEY, $data, (int) config('restcountries.cache_ttl'));

        return collect($data);
    }

    /**
     * Find a normalized country by its ISO 3166-1 alpha-2 code.
     *
     * @return array<string, mixed>|null
     */
    public function findByCode(string $code): ?array
    {
        return $this->list()->firstWhere('code', strtoupper(trim($code)));
    }

    /**
     * Map a normalized country to one of the pricing regions.
     */
    public function pricingRegion(array $country): string
    {
        $region = $country['region'] ?? '';
        $subregion = $country['subregion'] ?? '';

        if ($subregion === 'South America') {
            return 'South America';
        }

        if ($region === 'Americas') {
            return 'North America';
        }

        return $region;
    }

    /**
     * Fetch all countries from the REST Countries v5 API (paginated).
     * Returns an empty collection on any failure so the caller can fall back.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function fetchFromApi(): Collection
    {
        $key = (string) config('restcountries.key');

        if ($key === '') {
            Log::warning('REST Countries API key is not configured.');

            return collect();
        }

        $baseUrl = (string) config('restcountries.base_url');
        $limit = (int) config('restcountries.limit', 100);
        $timeout = (int) config('restcountries.timeout', 5);
        $retryTimes = (int) config('restcountries.retry_times', 2);
        $retryDelay = (int) config('restcountries.retry_delay', 500);
        $maxPages = (int) config('restcountries.max_pages', 10);
        $fields = (string) config('restcountries.fields');

        $countries = collect();
        $offset = 0;

        for ($page = 0; $page < $maxPages; $page++) {
            try {
                $response = Http::acceptJson()
                    ->withToken($key)
                    ->timeout($timeout)
                    ->retry($retryTimes, $retryDelay)
                    ->get($baseUrl, [
                        'limit' => $limit,
                        'offset' => $offset,
                        'fields' => $fields,
                    ]);

                $payload = $response->json();

                if (! $response->successful() || isset($payload['errors'])) {
                    Log::warning('Countries API returned an unexpected response.', [
                        'status' => $response->status(),
                        'errors' => $payload['errors'] ?? null,
                    ]);

                    return collect();
                }

                $objects = $payload['data']['objects'] ?? null;
                $meta = $payload['data']['meta'] ?? null;

                if (! is_array($objects)) {
                    Log::warning('Countries API response has an unexpected structure.');

                    return collect();
                }

                $countries = $countries->concat($this->normalize($objects));

                if (! is_array($meta) || empty($meta['more'])) {
                    break;
                }

                $offset = (int) ($meta['offset'] ?? $offset) + (int) ($meta['count'] ?? $limit);
            } catch (Throwable $e) {
                Log::warning('Countries API request failed.', [
                    'error' => $e->getMessage(),
                ]);

                return collect();
            }
        }

        return $countries->sortBy('name')->values();
    }

    /**
     * Normalize v5 API objects into a uniform structure.
     *
     * @param  array<int, mixed>  $objects
     * @return Collection<int, array<string, mixed>>
     */
    private function normalize(array $objects): Collection
    {
        $countries = collect();

        foreach ($objects as $item) {
            $code = strtoupper((string) ($item['codes']['alpha_2'] ?? ''));
            $commonName = $item['names']['common'] ?? null;
            $spanishName = $item['names']['translations']['spa']['common'] ?? null;

            if ($code === '' || $commonName === null) {
                continue;
            }

            $countries->push([
                'name' => $spanishName ?? $commonName,
                'code' => $code,
                'region' => $item['region'] ?? null,
                'subregion' => $item['subregion'] ?? null,
                'flag' => $item['flag']['url_png'] ?? null,
            ]);
        }

        return $countries;
    }

    /**
     * Fallback when the external API is unavailable.
     *
     * @return Collection<int, array<string, mixed>>
     */
    private function fallbackFromDatabase(): Collection
    {
        return Country::query()
            ->orderBy('nombre')
            ->get()
            ->map(fn (Country $country) => [
                'name' => $country->nombre,
                'code' => strtoupper($country->codigo),
                'region' => $country->region,
                'subregion' => $country->subregion,
                'flag' => $country->bandera,
            ]);
    }
}
