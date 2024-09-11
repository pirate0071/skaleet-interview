<?php

namespace Skaleet\Interview\TransactionProcessing\Domain\Validators;

use Skaleet\Interview\TransactionProcessing\Domain\Exception\NotSameCurrencyException;
use Skaleet\Interview\TransactionProcessing\Domain\Model\Amount;
use Webmozart\Assert\Assert;

class SameCurrencyValidator extends AbstractTransactionValidator
{
    public function __construct(public Amount $amount, public string $currency)
    {
    }

    protected function doValidation(): void
    {
        Assert::same($this->amount->currency, $this->currency, 'Transaction failed: transaction currency mismatch.');
    }
}
