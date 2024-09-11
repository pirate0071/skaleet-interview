<?php

namespace Skaleet\Interview\TransactionProcessing\Domain\Exception;


class NegativeAmountException extends \Exception
{
    public function __construct()
    {
        parent::__construct("Transaction amount must be positive.");
    }

}
