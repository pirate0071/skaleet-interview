<?php

namespace Skaleet\Interview\TransactionProcessing\Domain\Service;

use Skaleet\Interview\TransactionProcessing\Domain\Model\Amount;
use Skaleet\Interview\TransactionProcessing\Domain\Repository\FeeConfigRepository;

class FeeCalculatorService
{

    public function __construct()
    {
    }

    public function calculateFees(Amount $amount): Amount
    {
        $fee = min(
                $amount->value * FeeConfigRepository::TRANSACTION_FEE_PERCENTAGE,
                FeeConfigRepository::TRANSACTION_FEE_MIN
        );
        return new Amount($fee, $amount->currency);
    }

}
