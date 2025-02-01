<?php
/** @noinspection SpellCheckingInspection */

namespace App\Adapters\Transactions\Repositories\CardConnect;

use App\Adapters\Transactions\Dtos\ResponseDto;
use App\Adapters\Transactions\Repositories\TransactionRepositoryAbstract;
use App\Enums\Billings\PaymentMethodTypesEnums;
use App\Models\Gateway\DepartmentGateway;
use BadMethodCallException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

/**
 * @api https://developer.fiserv.com/product/CardPointe/api/?type=post&path=/api/v3/authCard&branch=main&version=1.0.0
 */
class CardConnectApiTerminalRepository extends TransactionRepositoryAbstract
{
    protected ?string $merchid = null;

    public function getType(string $token): null
    {
        return null;
    }

    /**
     * @throws ValidationException
     */
    public function __construct(DepartmentGateway $departmentGateway)
    {
        parent::__construct($departmentGateway);

        $additionalData = $departmentGateway->getAttribute('additional_data');
        $this->merchid = Arr::get($additionalData, 'merchid');

        $this->validatePropertiesSet();
    }

    /**
     * @throws ValidationException | ConnectionException
     * @note As part of the UI selection to use terminal a payment method type (credit/debit) need to be selected
     *      this selection will allow to calculate fee as they are not the same
     *      and to limit the terminal transaction to a payment method
     */
    public function executeTransaction(array $transactionDetails, string $paymentTransactionId): ResponseDto
    {
        $amount = Arr::get($transactionDetails, 'amount', 0);
        if (is_numeric($amount) && (float)$amount === 0.0) {
            throw ValidationException::withMessages([
                'card_connect_terminal_payment' => 'Invalid transaction amount',
            ]);
        }
        $hsn = Arr::get($transactionDetails, 'hsn', '');
        if (strlen($hsn) === 0) {
            throw ValidationException::withMessages([
                'card_connect_terminal_payment' => 'Invalid transaction HSN',
            ]);
        }
        $cardType = Arr::get($transactionDetails, 'card_type', '');
        if (strlen($cardType) === 0 || is_null(PaymentMethodTypesEnums::tryFrom($cardType))) {
            throw ValidationException::withMessages([
                'card_connect_terminal_payment' => 'Invalid transaction card type',
            ]);
        }

        $transactionDetails = [
            'hsn' => $hsn,
            'aid' => $cardType,
            'amount' => round($amount, 2) * 100,
            'includeSignature' => false,
            'includeAmountDisplay' => false,
            'beep' => true,
            'capture' => true,
            'includeAVS' => false,
            'includeCVV' => false,
            'gzipSignature' => false,
            'orderId' => '',
            'clearDisplayDelay' => 500,
        ];

        $responseBody = $this->sendPostRequest(
            action: 'v3/authCard',
            paymentDetails: $transactionDetails
        );

        $respstat = Arr::get($responseBody, 'respstat', '');

        $extraData = [];
        // Validate the success conditions
        $success = false;
        if (Arr::get($responseBody, 'respcode') == '000' && strcasecmp($respstat, 'A') === 0) {
            $success = true;
            $extraData = [
                'token' => Arr::get($responseBody, 'token'),
                'expiry' => Arr::get($responseBody, 'expiry'),
                'full_name' => Arr::get($responseBody, 'receiptData.nameOnCard') ?? Arr::get($responseBody, 'name'),
                'address1' => Arr::get($responseBody, 'receiptData.address1'),
                'address2' => Arr::get($responseBody, 'receiptData.address2'),
                'phone' => Arr::get($responseBody, 'receiptData.phone'),
            ];
        }

        return new ResponseDto(
            success: $success,
            reference_number: Arr::get($responseBody, 'retref'),
            status_code: Arr::get($responseBody, 'respcode'),
            status_message: Arr::get($responseBody, 'resptext'),
            batch_id: Arr::get($responseBody, 'batchid'),
            extra_data: $extraData
        );
    }

    public function executeVoidTransaction(array $transactionDetails, string $paymentTransactionId): ResponseDto
    {
        throw new BadMethodCallException("Void is not supported for Terminal.");
    }

    public function executeRefundTransaction(array $transactionDetails, string $paymentTransactionId): ResponseDto
    {
        throw new BadMethodCallException("Refund is not supported for Terminal.");
    }

    /**
     * @throws ValidationException | ConnectionException
     */
    public function sendToTerminalForConfirmation(string $hsn, float $amountDue, float $fee): bool
    {
        $amountDue = round($amountDue, 2);
        $fee = round($fee, 2);
        $total = $amountDue + $fee;

        $amountDue = $this->padAmount($amountDue, $total);
        $fee = $this->padAmount($fee, $total);
        $total = $this->padAmount($total, $total);

        $message = "YOUR AMOUNT BREAKDOWN:\n" .
            "  PAYMENT DUE: $amountDue\n" .
            "  SERVICE FEE: $fee\n" .
            "  TOTAL:       $total\n\n" .
            "PLEASE CONFIRM";

        $paymentDetails = [
            'hsn' => $hsn,
            'beep' => true,
            'message' => $message
            //'message' => "Your amount breakdown: \n $$total - Payment Due \n $$amountDue - Service Fee \n $$fee - Total \n Please Confirm:"
        ];

        $responseBody = $this->sendPostRequest(
            action: 'v3/readConfirmation',
            paymentDetails: $paymentDetails
        );

        $respstat = Arr::get($responseBody, 'respstat', '');
        if (strcasecmp($respstat, 'A') === 0) {
            return true;
        }

        throw ValidationException::withMessages([
            'CardPointeTerminalApi' => __("Fail User Confirmation-:ERROR",
                [
                    'ERROR' => Arr::get($responseBody, 'resptext')
                ])
        ]);
    }

    /**
     * @throws ValidationException | ConnectionException
     */
    public function sendToTerminalPrintReceipt(string $hsn, string $orderId): ResponseDto
    {
        if (strlen($hsn) === 0) {
            throw ValidationException::withMessages([
                'card_connect_terminal_payment' => 'Invalid transaction HSN',
            ]);
        }
        if (strlen($orderId) === 0) {
            throw ValidationException::withMessages([
                'card_connect_terminal_payment' => 'Invalid transaction orderId',
            ]);
        }

        $transactionDetails = [
            'merchantId' => $this->merchid,
            'hsn' => $hsn,
            'orderId' => $orderId,
            'printExtraReceipt' => true,
            'printDelay' => 2000,
        ];

        $responseBody = $this->sendPostRequest(
            action: 'v3/printReceipt',
            paymentDetails: $transactionDetails
        );

        $respstat = Arr::get($responseBody, 'respstat', '');

        // Validate the success conditions
        $success = false;
        if (Arr::get($responseBody, 'respcode') == '000' && strcasecmp($respstat, 'A') === 0) {
            $success = true;
        }

        return new ResponseDto(
            success: $success,
            reference_number: Arr::get($responseBody, 'retref'),
            status_code: Arr::get($responseBody, 'respcode'),
            status_message: Arr::get($responseBody, 'resptext'),
        );
    }

    /**
     * @throws ValidationException|ConnectionException
     */
    private function getSessionKey(array $headers, string $hsn): string
    {
        $paymentDetails = [
            'merchid' => $this->merchid,
            'hsn' => $hsn,
            'force' => true
        ];

        $response = Http::withHeaders(
            $headers
        )->post(
            url: $this->apiUrl . '/v2/connect',
            data: $paymentDetails
        );

        $responseBody = $response->json();

        $respstat = Arr::get($responseBody, 'respstat', '');
        $sessionkey = Arr::get($responseBody, 'sessionkey', '');
        if (strcasecmp($respstat, 'A') === 0 && strlen($sessionkey) > 0) {
            return $sessionkey;
        }

        throw ValidationException::withMessages([
            'CardPointeTerminalApi' => __("Fail retrieve session key-:ERROR",
                [
                    'ERROR' => Arr::get($responseBody, 'resptext')
                ])
        ]);
    }

    /**
     * @throws ValidationException | ConnectionException
     */
    private function sendPostRequest(string $action, array $paymentDetails): array
    {
        $paymentDetails['merchid'] = $this->merchid;

        $headers = [
            'Authorization' => 'Basic ' . $this->assignAuthorization(),
            'Content-Type' => 'application/json',
        ];

        $sessionKey = $this->getSessionKey(
            headers: $headers,
            hsn: Arr::get($paymentDetails, 'hsn')
        );

        $headers['X-CardConnect-SessionKey'] = $sessionKey;

        $response = Http::withHeaders(
            $headers
        )->post(
            url: $this->apiUrl . '/' . ltrim($action, '/'),
            data: $paymentDetails
        );

        return $response->json();
    }

    /**
     * @throws ValidationException
     */
    private function validatePropertiesSet(): void
    {
        $vars = get_class_vars(__CLASS__);
        foreach ($vars as $key => $value) {
            if (is_null($value)) {
                throw ValidationException::withMessages(['CardPointeTerminalApi' => __("$key is not set.")]);
            }
        }
    }

    /**
     * @note Function to pad amounts based on total length
     *      This way they all be aligned to the right
     */
    private function padAmount(float $amount, float $total): string
    {
        if ($amount !== $total) {
            $padding = strlen("$total") - strlen("$amount");
            $amount = str_repeat(" ", $padding) . $amount;
        }
        return "$" . number_format($amount, 2);
    }
}
