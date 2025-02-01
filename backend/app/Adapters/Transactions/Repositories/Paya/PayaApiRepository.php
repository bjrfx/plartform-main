<?php
/** @noinspection SpellCheckingInspection Needed for "Paya" */

namespace App\Adapters\Transactions\Repositories\Paya;

use App\Adapters\Transactions\Dtos\ResponseDto;
use App\Adapters\Transactions\Repositories\TransactionRepositoryAbstract;
use App\Adapters\Transactions\Repositories\Paya\SoapTemplates\GetTokenTemplate;
use App\Adapters\Transactions\Repositories\Paya\SoapTemplates\GetRequestTemplate;
use App\Adapters\Transactions\Repositories\Paya\SoapTemplates\GetSingleCheckTemplate;
use App\Models\Gateway\DepartmentGateway;
use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use SimpleXMLElement;

class PayaApiRepository extends TransactionRepositoryAbstract
{
    protected ?string $webTerminalId = null;
    protected ?string $largeTransactionTerminalId = null;
    protected ?string $ccdVoidTerminalId = null;

    //protected ?string $VoidTerminalIdBig = null;

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
        $this->webTerminalId = Arr::get($additionalData, 'web_terminal_id');
        $this->largeTransactionTerminalId = Arr::get($additionalData, 'large_transaction_terminal_id');
        $this->ccdVoidTerminalId = Arr::get($additionalData, 'ccd_void_terminal_id');
        //$this->VoidTerminalIdBig = Arr::get($additionalData, 'void_terminal_id_big');

        $this->validatePropertiesSet();
    }

    /**
     * @throws ValidationException
     */
    public function executeTransaction(array $transactionDetails, string $paymentTransactionId): ResponseDto
    {
        // Since Paya not return referance key ot will pass our own as part of the request
        //$transactionDetails['transactionId'] = ReferenceKeyHelper::generate();
        return $this->processSingleCheck(
            identifier: "A",
            transactionDetails: $transactionDetails
        );
    }

    /**
     * @throws ValidationException
     */
    public function executeVoidTransaction(array $transactionDetails, string $paymentTransactionId): ResponseDto
    {
        return $this->processSingleCheck(
            identifier: "V",
            transactionDetails: $transactionDetails
        );
    }

    /**
     * @throws ValidationException
     */
    public function executeRefundTransaction(array $transactionDetails, string $paymentTransactionId): ResponseDto
    {
        return $this->processSingleCheck(
            identifier: "F", //"F" for reversal
            transactionDetails: $transactionDetails
        );
    }

    /**
     * @throws ValidationException
     */
    private function processSingleCheck(
        string $identifier,
        array  $transactionDetails
    ): ResponseDto
    {
        $details = $this->validateTransactionDetails($transactionDetails);

        $details['custom1'] = Arr::get($details, 'custom.transactionId');

        $amount = (float)$details['amount'];

        $accountType = Arr::get($details, 'accountType');
        $accountType = Str::title($accountType);
        $token = $this->getToken(
            identifier: $identifier,
            amount: $amount,
            transactionId: Arr::get($details, 'custom.transactionId'),
            routingNumber: Arr::get($details, 'routingNumber'),
            accountNumber: Arr::get($details, 'accountNumber'),
            accountType: $accountType,
        );

        $terminalId = $this->getTerminalId(identifier: $identifier, amount: $amount);

        $soapBody = GetSingleCheckTemplate::generate(
            token: $token,
            identifier: $identifier,
            amount: $amount,
            terminalId: $this->webTerminalId,
            transactionId: Arr::get($details, 'custom.transactionId'),
            firstName: Arr::get($details, 'consumer.firstName'),
            middleName: Arr::get($details, 'consumer.middleName', ''),
            lastName: Arr::get($details, 'consumer.lastName'),
            address1: Arr::get($details, 'consumer.address1'),
            address2: Arr::get($details, 'consumer.address2'),
            city: Arr::get($details, 'consumer.city'),
            state: Arr::get($details, 'consumer.state'),
            zip: Arr::get($details, 'consumer.zip'),
            phoneNumber: Arr::get($details, 'consumer.phoneNumber'),
            email: Arr::get($details, 'consumer.email'),
            paymentReference: Arr::get($details, 'custom.payment_reference'),
            departmentName: Arr::get($details, 'custom.department_name'),
        );

        // Load the XML response
        $xml = $this->makePostRequest(
            SOAPAction: 'ProcessSingleCheck',
            terminalId: $terminalId,
            soapBody: $soapBody
        );

        $validationResult = Str::between($xml, "<VALIDATION_MESSAGE>", "</VALIDATION_MESSAGE>");
        if (!is_null($validationResult)) {
            $validationResult = Str::between($xml, "<RESULT>", "</RESULT>");
        }

        $authorizationMessage = Str::between($xml, "<AUTHORIZATION_MESSAGE>", "</AUTHORIZATION_MESSAGE>");
        $transactionId = null;
        $referenceNumber = null;
        $typeCode = null;
        if (!is_null($authorizationMessage)) {
            $transactionId = Str::between($xml, "<TRANSACTION_ID>", "</TRANSACTION_ID>");
            $referenceNumber = Str::between($xml, "<CODE>", "</CODE>");
            $typeCode = Str::between($xml, "<TYPE_CODE>", "</TYPE_CODE>");
            $authorizationMessage = Str::between($xml, "<MESSAGE>", "</MESSAGE>");
        }

        // Check if both conditions for success are met (case-insensitive)
        if (strcasecmp($validationResult, 'Passed') === 0 && strcasecmp($authorizationMessage, 'APPROVAL') === 0) {
            return new ResponseDto(
                success: true,
                reference_number: $referenceNumber,
                status_code: $typeCode,
                status_message: $authorizationMessage
            );
        }

        // Return failure if either condition is not met
        return new ResponseDto(
            success: false,
            reference_number: $referenceNumber,
            status_code: $typeCode,
            status_message: $authorizationMessage
        );
    }

    /**
     * @throws ValidationException
     */
    private function makePostRequest(string $SOAPAction, string $terminalId, string $soapBody): string
    {
        try {
            $soapEnvelope = GetRequestTemplate::generate(
                soapAuthGateway: Arr::get($this->config, 'paya.soap_auth_gateway'),
                username: $this->username,
                password: $this->password,
                terminalId: $terminalId,
                SOAPAction: $SOAPAction,
                soapBody: $soapBody
            );

            // Make the POST request
            $xml = $this->sendPostRequest(
                SOAPAction: $SOAPAction,
                soapEnvelope: $soapEnvelope
            );

            return $xml;
        } catch (Exception $e) {
            // Handle errors if the XML is malformed or TOKEN is missing
            /** @noinspection SpellCheckingInspection Needed for "paya" */
            throw ValidationException::withMessages([
                'paya_soap_fail' => $e->getMessage(),
            ]);
        }
    }

    private function getTerminalId(string $identifier, float $amount): string
    {
        if ($identifier === "A") {
            return $this->webTerminalId;
        }
        return $this->ccdVoidTerminalId;
    }

    /**
     * @throws ValidationException
     */
    private function getToken(string $identifier, float $amount, string $transactionId, string $routingNumber, string $accountNumber, string $accountType): ?string
    {
        $terminalId = $this->getTerminalId($identifier, $amount);
        // Define the SOAP request payload
        $soapBody = GetTokenTemplate::generate(
            transactionId: $transactionId,
            terminalId: $terminalId,
            routingNumber: $routingNumber,
            accountNumber: $accountNumber,
            accountType: $accountType
        );

        // Load the XML response
        $xml = $this->makePostRequest(
            SOAPAction: 'GetToken',
            terminalId: $terminalId,
            soapBody: $soapBody
        );

        // Access the TOKEN
        return Str::between($xml, "<TOKEN>", "</TOKEN>");
    }

    /**
     * @throws ValidationException
     */
    private function validatePropertiesSet(): void
    {
        $vars = [
            'webTerminalId',
            'largeTransactionTerminalId',
            'ccdVoidTerminalId',
        ];
        foreach ($vars as $key) {
            $val = $this->{$key};
            if (is_null($val) || strlen($val) === 0) {
                throw ValidationException::withMessages(['PayaApi' => __("$key is not set.")]);
            }
        }
    }

    /**
     * @throws ValidationException
     */
    protected function validateTransactionDetails(array $transactionDetails): array
    {
        $details = [
            'custom.transactionId' => null,
            'routingNumber' => null,
            'accountNumber' => null,
            'accountType' => null,
            'consumer.firstName' => null,
            'consumer.lastName' => null,
        ];
        foreach ($details as $key => $value) {
            $item = Arr::get($transactionDetails, $key, '');
            $item = trim((string)$item);
            if (strlen($item) === 0) {
                throw ValidationException::withMessages([
                    'paya_soap_payload' => "Invalid transaction $key",
                ]);
            }
        }
        // Validation logic
        $amount = Arr::get($transactionDetails, 'amount', 0);
        if (is_numeric($amount) && !(float)$amount > 0) {
            throw ValidationException::withMessages([
                'paya_soap_payload' => 'Invalid transaction amount',
            ]);
        }

        return $transactionDetails;
    }

    /**
     * @throws ValidationException
     * @throws ConnectionException
     */
    private function sendPostRequest(string $SOAPAction, string $soapEnvelope): string
    {
        $SoapGateway = Arr::get($this->config, 'paya.soap_auth_gateway');
        $SoapGateway = trim($SoapGateway, '/');
        $SOAPAction = trim($SOAPAction, '/');

        $soapEnvelope = preg_replace('/>\s+</', '><', $soapEnvelope); // Remove spaces between elements
        $soapEnvelope = trim($soapEnvelope);

        $response = Http::withHeaders([
            'Content-Type' => 'text/xml; charset=utf-8',
            'SOAPAction' => "\"$SoapGateway/$SOAPAction\"",
            'Accept-Encoding' => 'identity',
        ])
            ->withOptions(['version' => '1.1'])
            ->send('POST', $this->apiUrl, ['body' => $soapEnvelope]);

        $responseXml = $response->body(); // Get the raw XML response

        $decodedXml = htmlspecialchars_decode($responseXml);

        return Str::between($decodedXml, "<{$SOAPAction}Result>", "</{$SOAPAction}Result>");
    }
}
