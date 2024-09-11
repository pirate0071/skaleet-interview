<?php

namespace Skaleet\Interview\TransactionProcessing\Domain\Validators;

use Skaleet\Interview\TransactionProcessing\Domain\Exception\InsufisantFundsException;
use Skaleet\Interview\TransactionProcessing\Domain\Model\Account;
use Skaleet\Interview\TransactionProcessing\Domain\Model\Amount;

class ClientDebitedValidator extends AbstractTransactionValidator
{
    public function __construct(public Account $clientAccount, public Amount $amount)
    {
    }

    protected function doValidation(): void
    {
        if (($this->clientAccount->balance->value - $this->amount->value) <= 0) {
            throw new InsufisantFundsException($this->clientAccount->number);
        }
    }
}
