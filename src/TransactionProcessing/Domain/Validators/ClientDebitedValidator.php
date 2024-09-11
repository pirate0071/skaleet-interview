<?php

namespace Skaleet\Interview\TransactionProcessing\Domain\Validators;

use Skaleet\Interview\TransactionProcessing\Domain\Exception\InsufisantFundsException;
use Skaleet\Interview\TransactionProcessing\Domain\Model\Account;
use Skaleet\Interview\TransactionProcessing\Domain\Model\Amount;
use Webmozart\Assert\Assert;

class ClientDebitedValidator extends AbstractTransactionValidator
{
    public function __construct(public Account $clientAccount, public Amount $amount)
    {
    }

    protected function doValidation(): void
    {
        Assert::greaterThan($this->clientAccount->balance->value, $this->amount->value, "Transaction failed: insufficient fund on account #" . $this->clientAccount->number);
    }
}
