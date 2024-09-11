<?php

namespace Skaleet\Interview\TransactionProcessing\Domain\Validators;

interface TransactionValidatorInterface
{

    public function setNext(TransactionValidatorInterface $nextValidator): TransactionValidatorInterface;
    public function validate();
}
