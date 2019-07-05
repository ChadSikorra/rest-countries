<?php

namespace App\Tests\php\Controller;

use App\Controller\ApiController;
use App\Exception\ApiException;
use App\Service\CountryApi;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ApiControllerTest extends TestCase
{
    /**
     * @var ApiController
     */
    private $controller;

    /**
     * @var MockObject
     */
    private $apiMock;

    /**
     * @var Request
     */
    protected $request;

    public function setUp()
    {
        $this->request = new Request();
        $this->apiMock = $this->createMock(CountryApi::class);
        $this->controller = new ApiController($this->apiMock);
    }

    public function testApiCountry()
    {
        $this->apiMock->expects($this->once())->method('search')->with('foo', 'all')->willReturn(['foo']);

        $this->assertEquals(new JsonResponse(['foo']), $this->controller->apiAction($this->request, 'foo'));
    }

    public function testApiSendsErrorResponseOnEmptySearch()
    {
        $this->assertEquals(new JsonResponse(['error' => 'You must supply a search term for the countries.'], 400), $this->controller->apiAction($this->request, ''));
    }

    public function testApiSetsResponseOnApiException()
    {
        $this->apiMock->method('search')->willThrowException(new ApiException('foo', 400));

        $this->assertEquals(new JsonResponse(['error' => 'foo'], 400), $this->controller->apiAction($this->request,'bar'));
    }

    public function testApiSetsErrorResponseOnNonApiException()
    {
        $this->apiMock->method('search')->willThrowException(new \ErrorException('should not be seen'));

        $this->assertEquals(new JsonResponse(['error' => 'An unexpected error occurred. Please try again later.'], 500), $this->controller->apiAction($this->request,'bar'));
    }

    public function testApiSendsTheFilterThrough()
    {
        $this->request->query->add(['filter' => 'code']);

        $this->apiMock->expects($this->once())->method('search')->with('usa', 'code');
        $this->controller->apiAction($this->request, 'usa');
    }
}
