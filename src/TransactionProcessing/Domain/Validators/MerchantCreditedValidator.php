<?php

namespace Skaleet\Interview\TransactionProcessing\Domain\Validators;

use Skaleet\Interview\TransactionProcessing\Domain\Model\Account;
use Skaleet\Interview\TransactionProcessing\Domain\Model\Amount;
use Skaleet\Interview\TransactionProcessing\Domain\Repository\FeeConfigRepository;
use Webmozart\Assert\Assert;

class MerchantCreditedValidator extends AbstractTransactionValidator
{
    public function __construct(private Account $merchantAccount, private Amount $feesAmount)
    {
    }

    protected function doValidation(): void
    {
        Assert::greaterThan(
                $this->merchantAccount->balance->value - $this->feesAmount->value,
                FeeConfigRepository::TRANSACTION_FEE_MIN_MERCHANT_BALANCE_QUOTA,
                'Transaction Failed: Merchant balance should be greater than the minimum allowed balance.'
        );

        Assert::greaterThan(
                FeeConfigRepository::TRANSACTION_FEE_MAX_MERCHANT_BALANCE_QUOTA,
                $this->merchantAccount->balance->value + $this->feesAmount->value,
                'Transaction Failed: Merchant balance should not exceed max allowed balance.'
        );
    }
}
