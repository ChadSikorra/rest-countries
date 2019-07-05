<?php

namespace App\Controller;

use App\Exception\ApiException;
use App\Service\CountryApi;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ApiController
{
    protected $api;

    public function __construct(CountryApi $api)
    {
        $this->api = $api;
    }

    /**
     * @Route("/api/country/{search}")
     */
    public function apiAction(Request $request, string $search = '')
    {
        if ($search === '') {
            return new JsonResponse([
                'error' => 'You must supply a search term for the countries.'
            ], 400);
        }
        $filter = $request->query->get('filter') ?? CountryApi::FILTER_ALL;

        try {
            return new JsonResponse($this->api->search($search, $filter));
        } catch (ApiException $e) {
            return new JsonResponse([
                'error' => $e->getMessage()
            ], $e->getCode());
        } catch (\Throwable $e) {
            return new JsonResponse([
                'error' => 'An unexpected error occurred. Please try again later.'
            ], 500);
        }
    }
}
