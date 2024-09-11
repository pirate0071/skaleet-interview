<?php

namespace Skaleet\Interview\TransactionProcessing\Domain\Validators;

use Skaleet\Interview\TransactionProcessing\Domain\Model\Account;
use Skaleet\Interview\TransactionProcessing\Domain\Model\Amount;

class TransactionValidatorFactory
{

    public static function createTransactionValidator(
            Account $clientAccount,
            Account $merchantAccount,
            Amount  $amount
    ): TransactionValidatorInterface
    {
        $validator = new PositiveAmountValidator($amount);
        $validator->setNext(new SameCurrencyValidator($amount, $clientAccount->balance->currency))
                ->setNext(new SameCurrencyValidator($amount, $merchantAccount->balance->currency))
                ->setNext(new ClientDebitedValidator($clientAccount, $amount));

        return $validator;
    }
}
