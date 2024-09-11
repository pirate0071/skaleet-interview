<?php

namespace Skaleet\Interview\TransactionProcessing\Domain\Model;

class Account
{
    public function __construct(
            public string $number,
            public Amount $balance,
    )
    {
    }

    public function debit(Amount $amount): Amount
    {
        $this->balance = new Amount($this->balance->value - $amount->value, $amount->currency);
        return $this->balance;
    }

    public function credit(Amount $amount): Amount
    {
        $this->balance = new Amount($this->balance->value + $amount->value, $amount->currency);
        return $this->balance;
    }

    public function getBalance(): Amount
    {
        return $this->balance;
    }
}
