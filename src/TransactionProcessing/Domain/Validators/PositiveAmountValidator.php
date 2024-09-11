<?php

namespace Skaleet\Interview\TransactionProcessing\Domain\Validators;

use Skaleet\Interview\TransactionProcessing\Domain\Exception\NegativeAmountException;
use Skaleet\Interview\TransactionProcessing\Domain\Model\Amount;
use Webmozart\Assert\Assert;

class PositiveAmountValidator extends AbstractTransactionValidator
{

    public function __construct(public Amount $amount)
    {
    }

    protected function doValidation(): void
    {
        Assert::positiveInteger($this->amount->value, "Transaction Failed: transaction amount should be positive.");
    }
}
