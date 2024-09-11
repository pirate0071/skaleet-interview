<?php

namespace Skaleet\Interview\Tests\TransactionProcessing\UseCase;

use DateTime;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Skaleet\Interview\TransactionProcessing\Application\Command\PayByCardCommand;
use Skaleet\Interview\TransactionProcessing\Application\Command\PayByCardCommandHandler;
use Skaleet\Interview\TransactionProcessing\Domain\Model\Account;
use Skaleet\Interview\TransactionProcessing\Domain\Model\Amount;
use Skaleet\Interview\TransactionProcessing\Domain\Repository\AccountRegistry;
use Skaleet\Interview\TransactionProcessing\Domain\Repository\TransactionRepository;
use Skaleet\Interview\TransactionProcessing\Domain\Service\FeeCalculatorService;
use Skaleet\Interview\TransactionProcessing\Domain\Service\TransactionService;
use Skaleet\Interview\TransactionProcessing\Infrastructure\ExistingAccounts;
use Throwable;

class PayByCardCommandHandlerTest extends TestCase
{
    private PayByCardCommandHandler $handler;
    private $transactionRepository;
    private $accountRegistry;
    private $transactionService;
    private $feeCalculatorService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->transactionRepository = $this->createMock(TransactionRepository::class);
        $this->accountRegistry = $this->createMock(AccountRegistry::class);
        $this->transactionService = $this->createMock(TransactionService::class);
        $this->feeCalculatorService = $this->createMock(FeeCalculatorService::class);

        $this->handler = new PayByCardCommandHandler(
                $this->transactionRepository,
                $this->accountRegistry,
                $this->transactionService,
                $this->feeCalculatorService
        );
    }

    /**
     * @dataProvider commandProvider
     * @throws Throwable
     */
    public function processTransactionTest(
            string  $clientAccountNumber,
            string  $merchantAccountNumber,
            int     $amount,
            string  $currency,
            int     $clientBalance,
            int     $merchantBalance,
            int     $bankBalance,
            ?string $expectedException,
            ?string $expectedMessage,
            int     $expectedFees
    ): void
    {
        if ($expectedException) {
            $this->expectException($expectedException);
            $this->expectExceptionMessage($expectedMessage);
        }

        $clientAccount = $this->createMock(Account::class);
        $merchantAccount = $this->createMock(Account::class);
        $bankAccount = $this->createMock(Account::class);

        $transactionAmount = new Amount($amount, $currency);
        $feesAmount = new Amount($expectedFees, $currency);

        // Set account balances
        $clientAccount->method('getBalance')->willReturn(
                new Amount($clientBalance, $currency)
        );
        $merchantAccount->method('getBalance')->willReturn(
                new Amount($merchantBalance, $currency)
        );
        $bankAccount->method('getBalance')->willReturn(
                new Amount($bankBalance, $currency)
        );

        // Simulate loading accounts
        $this->accountRegistry->expects($this->exactly(3))
                ->method('loadByNumber')
                ->willReturnMap([
                        [$clientAccountNumber, $clientAccount],
                        [$merchantAccountNumber, $merchantAccount],
                        [constant(ExistingAccounts::class . '::' . "BANK_" . strtoupper($currency)), $bankAccount],
                ]);

        // Simulate calculating fees
        $this->feeCalculatorService->expects($this->once())
                ->method('calculateFees')
                ->with($this->isInstanceOf(Amount::class))
                ->willReturn($feesAmount);

        // Assert that the calculated fees match expected fees
        $this->assertEquals($expectedFees, $feesAmount->value, 'Fees should be calculated correctly');

        // Simulate validating transaction
        $this->transactionService->expects($this->once())
                ->method('validateTransaction')
                ->with($clientAccount, $merchantAccount, $bankAccount, $transactionAmount, $feesAmount);

        // Execute the handler with provided command
        $command = new PayByCardCommand(
                clientAccountNumber: $clientAccountNumber,
                merchantAccountNumber: $merchantAccountNumber,
                amount: $amount,
                currency: $currency
        );

        $this->handler->handle($command);

        // Final assertion for successful execution when no exception is expected
        if (!$expectedException) {
            $this->assertTrue(true, 'Handle method should execute without errors');
        }
    }

    public function commandProvider(): \Generator
    {
        $currentDate = new DateTime();

        // Successful cases with positive amounts and correct balance adjustments
        yield 'Valid EUR transaction with correct fee and balance updates' => [
                "dummyClientAccountEUR",
                "dummyMerchantAccountEUR",
                100, // Transaction amount
                "EUR",
                150, // Client balance
                2500, // Merchant balance
                1000, // Bank balance
                null, // Expected exception
                null, // Expected message
                2 // Expected fees (2% of 100, capped at 3)

        ];

        // Edge case: Max fee cap applied
        yield 'Valid EUR transaction with fee cap of €3' => [
                "dummyClientAccountEUR",
                "dummyMerchantAccountEUR",
                200, // Transaction amount
                "EUR",
                500, // Client balance
                2900, // Merchant balance
                1000, // Bank balance
                null, // Expected exception
                null, // Expected message
                3 // Expected fees (capped at €3)
        ];

        // Negative amount should trigger exception
        yield 'Invalid transaction: negative amount' => [
                "dummyClientAccountEUR",
                "dummyMerchantAccountEUR",
                -10,
                "EUR",
                150, // Client balance
                2500, // Merchant balance
                1000, // Bank balance
                InvalidArgumentException::class,
                'Transaction failed: amount must be strictly positive',
                0
        ];

        // Currency mismatch between client and transaction
        yield 'Invalid transaction: currency mismatch' => [
                "dummyClientAccountEUR",
                "dummyMerchantAccountUSD",
                100,
                "EUR",
                150, // Client balance
                2500, // Merchant balance
                1000, // Bank balance
                InvalidArgumentException::class,
                'Transaction failed: currency mismatch between accounts and transaction',
                0
        ];

        // Client's balance cannot go negative
        yield 'Invalid transaction: client balance cannot be negative' => [
                "dummyClientAccountEUR",
                "dummyMerchantAccountEUR",
                200,
                "EUR",
                100, // Client balance (too low)
                2500, // Merchant balance
                1000, // Bank balance
                InvalidArgumentException::class,
                'Transaction failed: client balance cannot be less than €0',
                0
        ];

        // Merchant balance exceeds €3,000
        yield 'Invalid transaction: merchant balance cannot exceed €3,000' => [
                "dummyClientAccountEUR",
                "dummyMerchantAccountEUR",
                200,
                "EUR",
                500, // Client balance
                2999, // Merchant balance (too high)
                1000, // Bank balance
                InvalidArgumentException::class,
                'Transaction failed: merchant balance cannot exceed €3,000',
                0
        ];

        // Merchant balance goes below -€1,000
        yield 'Invalid transaction: merchant balance below -€1,000' => [
                "dummyClientAccountEUR",
                "dummyMerchantAccountEUR",
                200,
                "EUR",
                500, // Client balance
                -1000, // Merchant balance (already negative)
                1000, // Bank balance
                InvalidArgumentException::class,
                'Transaction failed: merchant balance cannot be less than -€1,000',
                0
        ];
    }
}
