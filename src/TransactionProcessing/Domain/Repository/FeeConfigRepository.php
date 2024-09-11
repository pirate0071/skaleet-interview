<?php

namespace Skaleet\Interview\TransactionProcessing\Domain\Repository;

class FeeConfigRepository
{
    const TRANSACTION_FEE_PERCENTAGE = 200;
    const TRANSACTION_FEE_MIN = 300;
    const TRANSACTION_FEE_MIN_MERCHANT_BALANCE_QUOTA = -1000_00;
    const TRANSACTION_FEE_MAX_MERCHANT_BALANCE_QUOTA = 3000_00;
}
