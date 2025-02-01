<?php

namespace App\Adapters\Invoices\Repositories\DirectStatement;

use App\Adapters\Invoices\Repositories\InvoiceRepositoryInterface;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;


class DirectStatementInvoiceApiRepository implements InvoiceRepositoryInterface
{
    protected ?string $apiKey = null;
    protected ?string $apiUrl = null;
    protected ?string $accountNumber = null;

    public function __construct(object $gateway)
    {
        $this->apiKey = $gateway->getAttribute('token');
        $this->apiUrl = $gateway->getAttribute('custom_url');
    }


    public function setAccountNumber(string $accountNumber): self
    {
        $this->accountNumber = $accountNumber;
        return $this;
    }

    /**
     * @throws ValidationException | ConnectionException
     */
    public function getInvoice(): ?string
    {
        $this->validatePropertiesSet();
        return $this->getLatestInvoice();
    }

    /**
     * Get the latest direct statement URL for a given account number.
     * @throws ValidationException | ConnectionException
     */
    private function getLatestInvoice(): ?string
    {

        $responseData = $this->sendRequest();

        $StatementUrlLatest = Arr::get($responseData, 'StatementUrlLatest');
        if(is_null($StatementUrlLatest) || strlen($StatementUrlLatest) === 0) {
            return null;
        }

        return $StatementUrlLatest;
    }

    /**
     * @throws ValidationException|ConnectionException
     */
    private function sendRequest(): array
    {
        $response = Http::withHeaders([
            'x-api-key' => $this->apiKey,
        ])->withUrlParameters([
            'ACCOUNT_NUMBER' => $this->accountNumber,
        ])->get($this->apiUrl);

        if ($response->successful()) {
            return $response->json();
        }
        $this->throwError($response->body());
    }


    /**
     * @throws ValidationException
     */
    private function validatePropertiesSet(): void
    {
        if (is_null($this->accountNumber)) {
            $this->throwError('account number is not set.');
        }
        if (is_null($this->apiKey)) {
            $this->throwError('API Key is not set.');
        }
        if (is_null($this->apiUrl)) {
            $this->throwError('API URL is not set.');
        }
    }

    /**
     * @throws ValidationException
     */
    private function throwError(string $message): void
    {
        throw ValidationException::withMessages(['DirectStatementApi' => __($message)]);
    }
}
