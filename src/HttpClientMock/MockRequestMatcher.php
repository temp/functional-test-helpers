<?php

declare(strict_types=1);

namespace Brainbits\FunctionalTestHelpers\HttpClientMock;

final class MockRequestMatcher
{
    private Compare $compare;

    public function __construct()
    {
        $this->compare = new Compare();
    }

    public function __invoke(MockRequestBuilder $expectation, MockRequestBuilder $realRequest): MockRequestMatch
    {
        if ($expectation->isEmpty()) {
            return MockRequestMatch::empty();
        }

        if ($expectation->getMethod() !== null && $expectation->getMethod() !== $realRequest->getMethod()) {
            return MockRequestMatch::mismatchingMethod($expectation->getMethod(), $realRequest->getMethod());
        }

        if ($expectation->getUri() !== null && $expectation->getUri() !== $realRequest->getUri()) {
            return MockRequestMatch::mismatchingUri($expectation->getUri(), $realRequest->getUri());
        }

        // phpcs:disable Generic.Files.LineLength.TooLong
        if ($expectation->hasQueryParams() && !($this->compare)($expectation->getQueryParams(), $realRequest->getQueryParams())) {
            return MockRequestMatch::mismatchingQueryParams($expectation->getQueryParams(), $realRequest->getQueryParams());
        }

        // phpcs:enable Generic.Files.LineLength.TooLong

        if ($expectation->isJson()) {
            if (!$this->isJsonContentMatching($expectation, $realRequest)) {
                return MockRequestMatch::mismatchingJsonContent(
                    $expectation->getContent(),
                    $realRequest->getContent()
                );
            }
        } elseif ($expectation->hasRequestParams()) {
            if (!$this->isRequestParamsMatching($expectation, $realRequest)) {
                return MockRequestMatch::mismatchingRequestParameterContent(
                    $expectation->getContent(),
                    $realRequest->getContent()
                );
            }
        } elseif ($expectation->hasContent()) {
            if (!$this->isPlainContentMatching($expectation, $realRequest)) {
                return MockRequestMatch::mismatchingContent(
                    $expectation->getContent(),
                    $realRequest->getContent()
                );
            }
        }

        if ($expectation->getMultiparts() !== null) {
            if (!($this->compare)($expectation->getMultiparts(), $realRequest->getMultiparts())) {
                return MockRequestMatch::mismatchingMultiparts(
                    $expectation->getMultiparts(),
                    $realRequest->getMultiparts()
                );
            }
        }

        $match = MockRequestMatch::create();

        if ($expectation->getMethod() !== null && $expectation->getMethod() === $realRequest->getMethod()) {
            $match->matchesMethod($expectation->getMethod());
        }

        if ($expectation->getUri() !== null && $expectation->getUri() === $realRequest->getUri()) {
            $match->matchesUri($expectation->getUri());
        }

        if (
            $expectation->hasQueryParams()
            && ($this->compare)($expectation->getQueryParams(), $realRequest->getQueryParams())
        ) {
            $match->matchesQueryParams($expectation->getQueryParams());
        }

        if (
            $this->isPlainContentMatching($expectation, $realRequest)
            || $this->isJsonContentMatching($expectation, $realRequest)
            || $this->isRequestParamsMatching($expectation, $realRequest)
        ) {
            $match->matchesContent($expectation->getContent());
        }

        if (
            $expectation->getMultiparts() !== null
            && ($this->compare)($expectation->getMultiparts(), $realRequest->getMultiparts())
        ) {
            $match->matchesMultiparts($expectation->getMultiparts());
        }

        return $match;
    }

    private function isPlainContentMatching(MockRequestBuilder $expectation, MockRequestBuilder $realRequest): bool
    {
        if (!$expectation->hasContent() || !$realRequest->hasContent()) {
            return false;
        }

        return $expectation->getContent() === $realRequest->getContent();
    }

    private function isJsonContentMatching(MockRequestBuilder $expectation, MockRequestBuilder $realRequest): bool
    {
        if (!$expectation->hasContent() || !$expectation->isJson()) {
            return false;
        }

        if (!$realRequest->hasContent() || !$realRequest->isJson()) {
            return false;
        }

        return ($this->compare)($expectation->getJson(), $realRequest->getJson());
    }

    private function isRequestParamsMatching(MockRequestBuilder $expectation, MockRequestBuilder $realRequest): bool
    {
        if (!$expectation->hasContent() || !$expectation->hasRequestParams()) {
            return false;
        }

        if (!$realRequest->hasContent() || !$realRequest->hasRequestParams()) {
            return false;
        }

        return ($this->compare)($expectation->getRequestParams(), $realRequest->getRequestParams());
    }
}
