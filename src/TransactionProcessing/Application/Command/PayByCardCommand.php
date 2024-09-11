<?php

namespace Skaleet\Interview\TransactionProcessing\Application\Command;


class PayByCardCommand
{
    public function __construct(
        public string $clientAccountNumber,
        public string $merchantAccountNumber,
        public int    $amount,
        public string $currency,
    )
    {
    }

}
