<?php
/** @noinspection SpellCheckingInspection */

namespace App\Adapters\Transactions\Repositories\CardConnect;

use App\Adapters\Transactions\Dtos\ResponseDto;
use App\Adapters\Transactions\Repositories\TransactionRepositoryAbstract;
use App\Enums\Billings\PaymentMethodTypesEnums;
use App\Models\Gateway\DepartmentGateway;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * api https://developer.fiserv.com/product/CardPointe/api/?type=post&path=/cardconnect/rest/auth&branch=main&version=1.0.0
 */
class CardConnectApiRepository extends TransactionRepositoryAbstract
{
    protected ?string $merchid = null;

    /**
     * @throws ValidationException
     */
    public function __construct(DepartmentGateway $departmentGateway)
    {
        parent::__construct($departmentGateway);

        $this->merchid = $departmentGateway->getAttribute('external_identifier');

        $this->validatePropertiesSet();
    }

    /**
     * @throws ValidationException|ConnectionException
     */
    public function executeTransaction(array $transactionDetails, string $paymentTransactionId): ResponseDto
    {
        $paymentDetails = [
            'name' => null, // Full Card Owner name
            'orderid' => null, // The payment order id from the DB
            'account' => null, // The iFrame/Terminal Token
            'amount' => null, // The paid Amount
        ];

        foreach ($paymentDetails as $key => $value) {
            $value = Arr::get($transactionDetails, $key, '');
            $paymentDetails[$key] = trim((string)$value);
            if (strlen($paymentDetails[$key]) === 0) {
                throw ValidationException::withMessages([
                    'card_connect_payment' => "Invalid transaction $key",
                ]);
            }
        }

        $amount = Arr::get($paymentDetails, 'amount', 0);
        if (!is_numeric($amount) || !(float)$amount > 0) {
            throw ValidationException::withMessages([
                'card_connect_payment' => 'Invalid transaction amount',
            ]);
        }

        $paymentDetails['userfields'] = Arr::get($transactionDetails, 'userfields', []);
        $paymentDetails['items'] = Arr::get($transactionDetails, 'items', []);

        $paymentDetails['amount'] = round($amount, 2);
        $paymentDetails['currency'] = 'USD';
        $paymentDetails['capture'] = 'Y';
        $paymentDetails['ecomind'] = 'E';

        $expiry = Arr::get($transactionDetails, 'expiry');
        if (!is_null($expiry)) {
            $paymentDetails['expiry'] = $expiry;
        }

        return $this->sendTransaction(
            action: 'auth',
            paymentDetails: $paymentDetails,
            paymentTransactionId: $paymentTransactionId
        );
    }


    /**
     * @throws ValidationException
     * @throws ConnectionException
     */
    public function executeVoidTransaction(array $transactionDetails, string $paymentTransactionId): ResponseDto
    {
        $retref = Arr::get($transactionDetails, 'reference_number', '');
        if (strlen($retref) === 0) {
            throw ValidationException::withMessages([
                'card_connect_payment' => 'Invalid void reference_number',
            ]);
        }

        return $this->sendTransaction(
            action: 'void',
            paymentDetails: [
                'retref' => $retref,
            ],
            paymentTransactionId: $paymentTransactionId
        );
    }

    /**
     * @throws ValidationException|ConnectionException
     */
    public function executeRefundTransaction(array $transactionDetails, string $paymentTransactionId): ResponseDto
    {
        $retref = Arr::get($transactionDetails, 'reference_number', '');
        if (strlen($retref) === 0) {
            throw ValidationException::withMessages([
                'card_connect_payment' => 'Invalid void reference_number',
            ]);
        }

        return $this->sendTransaction(
            action: 'refund',
            paymentDetails: [
                'retref' => $retref,
            ],
            paymentTransactionId: $paymentTransactionId
        );
    }

    /**
     * @note Needed to calculate fees for clients that have credit & debit
     * @throws ValidationException
     * @throws ConnectionException
     * @throws RequestException
     */
    public function getType(string $token): PaymentMethodTypesEnums
    {
        $action = 'bin/' . $this->merchid . '/' . $token;

        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . $this->assignAuthorization(),
            'Content-Type' => 'application/json',
        ])->get(
            url: $this->apiUrl . '/' . $action
        )->throw();

        $responseBody = $response->json();

        if ($response->failed()) {
            throw ValidationException::withMessages(['CardPointeApi' => __('Invalid Card Type')]);
        }

        $cardusestring = Arr::get($responseBody, 'cardusestring');

        if (Str::of($cardusestring)->lower()->contains('debit')) {
            return PaymentMethodTypesEnums::DEBIT;
        }

        return PaymentMethodTypesEnums::CREDIT;
    }

    /**
     * @throws ValidationException
     */
    private function validatePropertiesSet(): void
    {
        $vars = [
            'merchid',
            'apiUrl',
            'username',
            'password',
        ];
        foreach ($vars as $key) {
            if (is_null($this->{$key})) {
                throw ValidationException::withMessages(['CardPointeApi' => __("$key is not set.")]);
            }
        }
    }

    /**
     * @throws ConnectionException
     */
    private function sendTransaction(string $action, array $paymentDetails, string $paymentTransactionId): ResponseDto
    {
        $expiry = Arr::get($paymentDetails, 'expiry');

        $paymentDetails['merchid'] = $this->merchid;

        $response = $this->sendPostRequest(action: $action, paymentDetails: $paymentDetails);

        $responseBody = $response->json();

        $respstat = Arr::get($responseBody, 'respstat', '');
        // Validate the success conditions
        $success = false;
        if (Arr::get($responseBody, 'respcode') == '000' && strcasecmp($respstat, 'A') === 0) {
            $success = true;
        }

        $this->saveApiLog(paymentTransactionId: $paymentTransactionId, action: $action, success: $success, requestData: $paymentDetails, responseData: $responseBody);

        return new ResponseDto(
            success: $success,
            reference_number: Arr::get($responseBody, 'retref'),
            status_code: Arr::get($responseBody, 'respcode'),
            status_message: Arr::get($responseBody, 'resptext'),
            batch_id: Arr::get($responseBody, 'batchid'),
            expiry: Arr::get($responseBody, 'expiry', $expiry),
        );
    }

    /**
     * @throws ConnectionException
     */
    private function sendPostRequest(string $action, array $paymentDetails): PromiseInterface|Response
    {
        return Http::withHeaders([
            'Authorization' => 'Basic ' . $this->assignAuthorization(),
            'Content-Type' => 'application/json',
        ])->post(
            url: $this->apiUrl . '/' . ltrim($action, '/'),
            data: $paymentDetails
        );
    }
}
