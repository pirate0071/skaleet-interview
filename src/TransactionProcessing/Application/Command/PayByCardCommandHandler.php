<?php

namespace Skaleet\Interview\TransactionProcessing\Application\Command;

use Cassandra\Date;
use Skaleet\Interview\TransactionProcessing\Application\UseCase\ProcessTransactionUseCase;
use Skaleet\Interview\TransactionProcessing\Domain\AccountRegistry;
use Skaleet\Interview\TransactionProcessing\Domain\Model\Account;
use Skaleet\Interview\TransactionProcessing\Domain\Model\AccountingEntry;
use Skaleet\Interview\TransactionProcessing\Domain\Model\Amount;
use Skaleet\Interview\TransactionProcessing\Domain\Model\TransactionLog;
use Skaleet\Interview\TransactionProcessing\Domain\TransactionRepository;
use Skaleet\Interview\TransactionProcessing\Domain\Validators\TransactionValidatorFactory;
use Webmozart\Assert\Assert;

class PayByCardCommandHandler
{
    public function __construct(
            private TransactionRepository $transactionRepository,
            private AccountRegistry       $accountRegistry
    )
    {
    }


    public function handle(PayByCardCommand $command): void
    {
        $clientAccount = $this->accountRegistry->loadByNumber($command->clientAccountNumber);
        $merchantAccount = $this->accountRegistry->loadByNumber($command->merchantAccountNumber);

        Assert::isInstanceOf($clientAccount, Account::class);
        Assert::isInstanceOf($merchantAccount, Account::class);

        $amount = new Amount($command->amount, $command->currency);

        $transactionValidators = TransactionValidatorFactory::createTransactionValidator($clientAccount, $merchantAccount, $amount);

        $transactionValidators->validate();

        $transactionLog = new TransactionLog(
                uniqid(),
                new \DateTimeImmutable(),
                [
                        new AccountingEntry($clientAccount->number, new Amount(-$amount->value, $amount->currency), $clientAccount->debit($amount)),
                        new AccountingEntry($merchantAccount->number, $amount, $merchantAccount->credit($amount))
                ]
        );

        $this->transactionRepository->add($transactionLog);
    }
}
