<?php

declare(strict_types=1);

namespace Brainbits\FunctionalTestHelpers\HttpClientMock;

use Symfony\Component\HttpClient\Response\MockResponse;

final class SymfonyMockResponseFactory implements MockResponseFactory
{
    public function fromRequestBuilder(MockRequestBuilder $requestBuilder): MockResponse
    {
        $responseBuilder = $requestBuilder->getResponseBuilder();

        if (!$responseBuilder) {
            return new MockResponse();
        }

        $info = [];

        if ($responseBuilder->hasCode()) {
            $info['http_code'] = $responseBuilder->getCode();
        }

        if ($responseBuilder->hasHeaders()) {
            $info['response_headers'] = $responseBuilder->getHeaders();
        }

        $body = (string) $responseBuilder->getContent();

        return new MockResponse($body, $info);
    }
}
