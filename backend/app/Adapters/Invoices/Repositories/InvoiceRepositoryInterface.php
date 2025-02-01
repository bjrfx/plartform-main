<?php

namespace App\Adapters\Invoices\Repositories;

interface InvoiceRepositoryInterface
{
    public function setAccountNumber(string $accountNumber): self;
    public function getInvoice(): ?string;
}
