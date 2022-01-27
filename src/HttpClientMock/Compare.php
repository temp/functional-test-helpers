<?php

declare(strict_types=1);

namespace Brainbits\FunctionalTestHelpers\HttpClientMock;

use function array_key_exists;
use function is_array;
use function is_numeric;

final class Compare
{
    public function __invoke(mixed $actual, mixed $expected): bool
    {
        if (!is_array($expected) || !is_array($actual)) {
            return $actual === $expected;
        }

        foreach ($expected as $key => $value) {
            if (is_numeric($key)) {
                $match = false;
                foreach ($actual as $otherKey => $otherValue) {
                    if (is_numeric($otherKey) && $this($value, $otherValue)) {
                        $match = true;
                        break;
                    }
                }

                if (!$match) {
                    return false;
                }
            } elseif (!array_key_exists($key, $actual) || !$this($actual[$key], $expected[$key])) {
                return false;
            }
        }

        foreach ($actual as $key => $value) {
            if (is_numeric($key)) {
                $match = false;
                foreach ($expected as $otherKey => $otherValue) {
                    if (is_numeric($otherKey) && $this($value, $otherValue)) {
                        $match = true;
                        break;
                    }
                }

                if (!$match) {
                    return false;
                }
            } elseif (!array_key_exists($key, $expected) || !$this($actual[$key], $expected[$key])) {
                return false;
            }
        }

        return true;
    }
}
