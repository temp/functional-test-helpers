<?php

declare(strict_types=1);

namespace Brainbits\FunctionalTestHelpers\HttpClientMock;

use Brainbits\FunctionalTestHelpers\HttpClientMock\Exception\NoMatchingMockRequest;

final class MockRequestResolver
{
    private MockRequestMatcher $match;

    public function __construct()
    {
        $this->match = new MockRequestMatcher();
    }

    /**
     * @param mixed[] $options
     */
    public function __invoke(
        MockRequestBuilderCollection $requestBuilders,
        MockRequestBuilder $realRequest
    ): MockRequestBuilder {
        $bestMatchingRequestBuilder = null;

        $matches = [];
        $bestMatch = null;
        foreach ($requestBuilders as $requestBuilder) {
            $matches[] = $match = ($this->match)($requestBuilder, $realRequest);

            if ($match->isMismatch() || ($bestMatch && $match->getScore() <= $bestMatch->getScore())) {
                continue;
            }

            $bestMatchingRequestBuilder = $requestBuilder;
            $bestMatch = $match;
        }

        if (!$bestMatchingRequestBuilder) {
            throw NoMatchingMockRequest::fromMockRequest($realRequest, $matches);
        }

        return $bestMatchingRequestBuilder;
    }
}
