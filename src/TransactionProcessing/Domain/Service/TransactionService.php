<?php

namespace Skaleet\Interview\TransactionProcessing\Domain\Service;

use Skaleet\Interview\TransactionProcessing\Domain\Model\Account;
use Skaleet\Interview\TransactionProcessing\Domain\Model\AccountingEntry;
use Skaleet\Interview\TransactionProcessing\Domain\Model\Amount;
use Skaleet\Interview\TransactionProcessing\Domain\Model\TransactionLog;
use Skaleet\Interview\TransactionProcessing\Domain\Validators\TransactionValidatorFactory;
use Webmozart\Assert\Assert;

class TransactionService
{
    public function validateTransaction(Account $clientAccount, Account $merchantAccount, Account $bankAccount, Amount $transactionAmount, Amount $feesAmount): void
    {
        $transactionValidators = TransactionValidatorFactory::createTransactionValidator($clientAccount, $merchantAccount, $transactionAmount, $feesAmount);

        $transactionValidators->validate();
    }

    public function processTransaction(Account $clientAccount, Account $merchantAccount, Account $bankAccount, Amount $transactionAmount, Amount $feesAmount): TransactionLog
    {
        return new TransactionLog(
                uniqid(),
                new \DateTimeImmutable(),
                [
                        new AccountingEntry($clientAccount->number, new Amount(-$transactionAmount->value, $transactionAmount->currency), $clientAccount->debit($transactionAmount)),
                        new AccountingEntry($merchantAccount->number, $transactionAmount, $merchantAccount->credit($transactionAmount)),
                        new AccountingEntry($merchantAccount->number, new Amount(-$feesAmount->value, $feesAmount->currency), $merchantAccount->debit($feesAmount)),
                        new AccountingEntry($bankAccount->number, $feesAmount, $bankAccount->credit($feesAmount)),
                ]
        );
    }
}
