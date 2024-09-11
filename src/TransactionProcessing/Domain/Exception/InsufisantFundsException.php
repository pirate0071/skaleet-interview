<?php

namespace Skaleet\Interview\TransactionProcessing\Domain\Exception;


class InsufisantFundsException extends \Exception
{
    public function __construct(string $accountNumber)
    {
        parent::__construct("Insufisant fund on account #$accountNumber");
    }

}
