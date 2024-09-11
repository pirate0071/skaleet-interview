<?php

namespace Skaleet\Interview\TransactionProcessing\Domain\Repository;

use Skaleet\Interview\TransactionProcessing\Domain\Model\Account;

interface AccountRegistry
{
    public function loadByNumber($accountNumber): ?Account;
}
