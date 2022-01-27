<?php

declare(strict_types=1);

namespace Brainbits\FunctionalTestHelpers\HttpClientMock;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;

final class CallbackHandler extends AbstractProcessingHandler
{
    /** @var callable */
    private $fn;

    public function __construct(callable $fn, int $level = Logger::DEBUG, bool $bubble = true)
    {
        parent::__construct($level, $bubble);

        $this->fn = $fn;
    }

    public function clear(): void
    {
    }

    public function reset(): void
    {
    }

    /**
     * {@inheritdoc}
     */
    protected function write(array $record): void
    {
        ($this->fn)($record);
    }
}
