<?php

namespace Skaleet\Interview\TransactionProcessing\Domain\Validators;

use Skaleet\Interview\TransactionProcessing\Domain\Exception\NegativeAmountException;
use Skaleet\Interview\TransactionProcessing\Domain\Model\Amount;

class PositiveAmountValidator extends AbstractTransactionValidator
{

    public function __construct(public Amount $amount)
    {
    }

    protected function doValidation(): void
    {
        if ($this->amount->value <= 0) {
            throw new NegativeAmountException();
        }
    }
}
