<?php

namespace App\Service;

use App\Exception\ApiException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\Cache\Adapter\AdapterInterface;

class CountryApi
{
    public const FILTER_ALL = 'all';

    public const FILTER_CODE = 'code';

    public const FILTER_NAME = 'name';

    protected const RESULT_LIMIT = 50;

    protected const RETRIEVABLE_FIELDS = [
        'name',
        'nativeName',
        'alpha2Code',
        'alpha3Code',
        'flag',
        'region',
        'subregion',
        'population',
        'languages',
    ];

    protected const SEARCHABLE_FIELDS = [
        self::FILTER_ALL => [
            'name',
            'nativeName',
            'alpha2Code',
            'alpha3Code',
        ],
        self::FILTER_CODE => [
            'alpha2Code',
            'alpha3Code',
        ],
        self::FILTER_NAME => [
            'name',
            'nativeName',
        ]
    ];

    protected $http;

    protected $cache;

    protected $endpoint;

    protected $data;

    public function __construct(AdapterInterface $cache, Client $http, string $endpoint)
    {
        $this->http = $http;
        $this->cache = $cache;
        $this->endpoint = $endpoint;
    }

    /**
     * @throws ApiException
     */
    public function search(string $search, string $filter = self::FILTER_ALL) : array
    {
        return $this->filter($this->data(), $search, $filter);
    }

    protected function filter(array $results, string $text, string $filter) : array
    {
        if (!isset(self::SEARCHABLE_FIELDS[$filter])) {
            throw new ApiException('The filter is invalid.', 400);
        }

        $results = array_filter($results, function ($country) use ($text, $filter) {
            foreach (self::SEARCHABLE_FIELDS[$filter] as $field) {
                if (isset($country[$field]) && stripos($country[$field], $text) !== false) {
                    return true;
                }
            }

            return false;
        });

        return array_slice(
            array_values($results),
            0,
            self::RESULT_LIMIT
        );
    }

    protected function data() : array
    {
        $cacheItem = $this->cache->getItem('countries');
        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $data = $this->getApiData();
        $cacheItem->set($data);
        $cacheItem->expiresAt(new \DateTime('3 hours'));
        $this->cache->save($cacheItem);

        return $data;
    }

    protected function getApiData() : array
    {
        try {
            $response = $this->http->get(
                sprintf('%s?fields=%s', $this->endpoint, implode(';', self::RETRIEVABLE_FIELDS)),
                [
                    'connect_timeout' => 5,
                    'headers' => [
                        'Accept' => 'application/json',
                        'Content-type' => 'application/json'
                    ],
                ]
            );
        } catch (ConnectException|RequestException $e) {
            throw new ApiException('Error retrieving data from endpoint.');
        }

        $data = $response->getBody()->getContents();
        if ($data === '') {
            throw new ApiException('Error retrieving data from endpoint.');
        }
        $data = json_decode($data, true);
        if ($data === null) {
            throw new ApiException('Error decoding response from endpoint.');
        }

        # Pre-sort and / normalize the language data so it only has to be done once before caching
        usort($data, function($a, $b) {
            $aName = $a['name'] ?? '';
            $bName = $b['name'] ?? '';
            $aPop = $a['population'] ?? 0;
            $bPop = $b['population'] ?? 0;

            return $aName <=> $bName ?: $aPop <=> $bPop;
        });
        foreach ($data as $i => $country) {
            $data[$i]['languages'] = $this->getLanguages($country);
        }

        return $data;
    }

    /**
     * Only displaying a list of language names, so use this to filter it to be only what we need.
     */
    protected function getLanguages(array $country) : array
    {
        $languages = [];
        if (!(isset($country['languages']) && is_array($country['languages']))) {
            return [];
        }

        foreach ($country['languages'] as $language) {
            if (!isset($language['name'])) {
                continue;
            }
            $languages[] = $language['name'];
        }

        return $languages;
    }
}
