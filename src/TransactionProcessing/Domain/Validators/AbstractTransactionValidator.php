<?php

namespace Skaleet\Interview\TransactionProcessing\Domain\Validators;

abstract class AbstractTransactionValidator implements TransactionValidatorInterface
{
    private ?TransactionValidatorInterface $nextValidator = null;

    public function setNext(TransactionValidatorInterface $nextValidator): TransactionValidatorInterface
    {
        $this->nextValidator = $nextValidator;
        return $this->nextValidator;
    }

    public function validate(): void
    {
        $this->doValidation();

        $this->nextValidator?->validate();
    }

    abstract protected function doValidation(): void;
}
