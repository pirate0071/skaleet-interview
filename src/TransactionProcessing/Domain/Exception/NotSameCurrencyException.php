<?php

namespace Skaleet\Interview\TransactionProcessing\Domain\Exception;


class NotSameCurrencyException extends \Exception
{
    public function __construct(string $expected, string $value)
    {
        parent::__construct("Transaction must have the same currency expected: $expected, got : $value");
    }

}
