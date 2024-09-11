<?php

namespace Skaleet\Interview\TransactionProcessing\Domain\Validators;

use Skaleet\Interview\TransactionProcessing\Domain\Exception\NotSameCurrencyException;
use Skaleet\Interview\TransactionProcessing\Domain\Model\Amount;

class SameCurrencyValidator extends AbstractTransactionValidator
{
    public function __construct(public Amount $amount, public string $currency)
    {
    }

    protected function doValidation(): void
    {
        if ($this->amount->currency !== $this->currency) {
            throw new NotSameCurrencyException($this->amount->currency, $this->currency);
        }
    }
}
