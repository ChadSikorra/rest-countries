<?php

namespace App\Tests\php\Service;

use App\Exception\ApiException;
use App\Service\CountryApi;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Cache\CacheItemInterface;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use Symfony\Component\Cache\Adapter\AdapterInterface;

class CountryApiTest extends TestCase
{
    protected const API_DATA = __DIR__. '/../../resources/rest-countries-data.txt';

    protected const CACHED_DATA = __DIR__.'/../../resources/cached-data.txt';

    /**
     * @var CountryApi
     */
    protected $api;

    /**
     * @var MockObject
     */
    protected $cacheMock;

    /**
     * @var MockObject
     */
    protected $cacheItemMock;

    /**
     * @var HandlerStack
     */
    protected $handlerStack;

    public function setUp()
    {
        $mockHandler = new MockHandler([
            new Response(200, [], file_get_contents(self::API_DATA))
        ]);
        $this->handlerStack = HandlerStack::create($mockHandler);

        $this->cacheItemMock = $this->createMock(CacheItemInterface::class);
        $this->cacheMock = $this->createMock(AdapterInterface::class);
        $this->cacheMock->method('getItem')->with('countries')->willReturn($this->cacheItemMock);

        $this->api = new CountryApi($this->cacheMock, new Client(['handler' => $this->handlerStack]),'https://restcountries.eu/rest/v2/all');
    }

    public function testSearchWithoutCache()
    {
        $this->cacheItemMock->method('isHit')->willReturn(false);

        $this->cacheMock->expects($this->once())->method('save');
        $result = $this->api->search('America');

        $this->assertCount(2, $result);
        $this->assertEquals("American Samoa", $result[0]['name']);
        $this->assertEquals("United States of America", $result[1]['name']);
    }

    public function testSearchWithCache()
    {
        $this->cacheItemMock->method('isHit')->willReturn(true);
        $this->cacheItemMock->method('get')->willReturn(unserialize(file_get_contents(self::CACHED_DATA)));

        $this->cacheMock->expects($this->never())->method('save');
        $result = $this->api->search('America');

        $this->assertCount(2, $result);
    }

    public function testSearchResultContainsNeededKeys()
    {
        $result = $this->api->search('America');

        $this->assertEquals([
            "languages",
            "flag",
            "name",
            "alpha2Code",
            "alpha3Code",
            "subregion",
            "population",
            "nativeName",
        ], array_keys($result[0]));
    }

    public function testSearchLimitsResultsTo50()
    {
        $result = $this->api->search('a');

        $this->assertCount(50, $result);
    }

    public function testSearchSortsProperly()
    {
        $result = $this->api->search('ru');

        $this->assertCount(10, $result);
        $this->assertEquals([
            'Aruba',
            'Belarus',
            'Bhutan',
            'Brunei Darussalam',
            'Burundi',
            'Cyprus',
            'Nauru',
            'Peru',
            'Russian Federation',
            'Uruguay',
        ], array_column($result,'name'));
    }

    public function testSearchIsCaseInsensitive()
    {
        $result = $this->api->search('UNITED states OF america');

        $this->assertCount(1, $result);
    }

    public function testItReturnsAnEmptyArrayWhenTheSearchContainsNoResults()
    {
        $result = $this->api->search('ThisWillNeverWork');

        $this->assertEquals([], $result);
    }

    public function testItThrowsAnApiExceptionOnExternalEndpointErrors()
    {
        $this->handlerStack->setHandler(new MockHandler([
            new Response(500, [], '')
        ]));

        $this->expectExceptionObject(new ApiException('Error retrieving data from endpoint.'));
        $this->api->search('am');
    }

    public function testItThrowsAnApiExceptionOnEmptyExternalEndpointData()
    {
        $this->handlerStack->setHandler(new MockHandler([
            new Response(200, [], '')
        ]));

        $this->expectExceptionObject(new ApiException('Error retrieving data from endpoint.'));
        $this->api->search('am');
    }

    public function testItFiltersByCodeOnly()
    {
        $results = $this->api->search('us', CountryApi::FILTER_CODE);

        $this->assertEquals([
            'Australia',
            'Mauritius',
            'Russian Federation',
            'United States of America'
        ], array_column($results, 'name'));
    }

    public function testItFiltersByNameOnly()
    {
        $results = $this->api->search('us', CountryApi::FILTER_NAME);

        $this->assertEquals([
            'Australia',
            'Austria',
            'Belarus',
            'Bonaire, Sint Eustatius and Saba',
            'Brunei Darussalam',
            'Cyprus',
            'French Southern Territories',
            'Mauritius',
            'Russian Federation'
        ], array_column($results, 'name'));
    }

    public function testItThrowsAnApiExceptionOnAnInvalidFilter()
    {
        $this->expectExceptionObject(new ApiException('The filter is invalid.', 400));
        $this->api->search('foo', 'bar');
    }
}
