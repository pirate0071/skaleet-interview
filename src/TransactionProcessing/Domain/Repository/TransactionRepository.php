<?php

namespace Skaleet\Interview\TransactionProcessing\Domain\Repository;

use Skaleet\Interview\TransactionProcessing\Domain\Model\TransactionLog;

interface TransactionRepository
{

    public function add(TransactionLog $transaction): void;
}
