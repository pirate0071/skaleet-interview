<?php

namespace Skaleet\Interview\TransactionProcessing\Application\Command;

use Skaleet\Interview\TransactionProcessing\Domain\Model\Account;
use Skaleet\Interview\TransactionProcessing\Domain\Model\Amount;
use Skaleet\Interview\TransactionProcessing\Domain\Repository\AccountRegistry;
use Skaleet\Interview\TransactionProcessing\Domain\Repository\TransactionRepository;
use Skaleet\Interview\TransactionProcessing\Domain\Service\FeeCalculatorService;
use Skaleet\Interview\TransactionProcessing\Domain\Service\TransactionService;
use Skaleet\Interview\TransactionProcessing\Infrastructure\ExistingAccounts;
use Webmozart\Assert\Assert;

class PayByCardCommandHandler
{
    public function __construct(
            private TransactionRepository $transactionRepository,
            private AccountRegistry       $accountRegistry,
            private TransactionService    $transactionService,
            private FeeCalculatorService  $feeCalculatorService
    )
    {
    }

    public function handle(PayByCardCommand $command): void
    {
        $clientAccount = $this->accountRegistry->loadByNumber($command->clientAccountNumber);
        $merchantAccount = $this->accountRegistry->loadByNumber($command->merchantAccountNumber);
        $bankAccountNumber = constant(ExistingAccounts::class . '::' . "BANK_" . strtoupper($command->currency));
        $bankAccount = $this->accountRegistry->loadByNumber($bankAccountNumber);
        $transactionAmount = new Amount($command->amount, $command->currency);
        Assert::isInstanceOf($clientAccount, Account::class, 'Transaction failed: cannot retrieve client account');
        Assert::isInstanceOf($merchantAccount, Account::class, 'Transaction failed: cannot retrieve merchant account');
        Assert::isInstanceOf($bankAccount, Account::class, 'Transaction failed: cannot retrieve bank account');

        //Calculate fees
        $feesAmount = $this->feeCalculatorService->calculateFees($transactionAmount);

        //Validate Transaction
        $this->transactionService->validateTransaction($clientAccount, $merchantAccount, $bankAccount, $transactionAmount, $feesAmount);

        //Process Transaction
        $transactionLog = $this->transactionService->processTransaction($clientAccount, $merchantAccount, $bankAccount, $transactionAmount, $feesAmount);

        //Log Transaction
        $this->transactionRepository->add($transactionLog);
    }
}
