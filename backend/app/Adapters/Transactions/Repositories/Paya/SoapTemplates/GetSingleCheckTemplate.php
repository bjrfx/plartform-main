<?php

namespace App\Adapters\Transactions\Repositories\Paya\SoapTemplates;

class GetSingleCheckTemplate
{
    public static function generate(
        string      $token,
        string      $identifier,
        float       $amount,
        string      $terminalId,
        string      $transactionId,
        string      $firstName,
        string|null $middleName,
        string      $lastName,
        string      $address1,
        ?string     $address2,
        string      $city,
        string      $state,
        string      $zip,
        string      $phoneNumber,
        string      $email,
        ?string     $paymentReference = null,
        ?string     $departmentName = null,
    ): string
    {
        $xml = new \XMLWriter();
        $xml->openMemory();
        $xml->setIndent(true); // Enables indentation for readability
        //$xml->startDocument('1.0', 'UTF-8');

        // AUTH_GATEWAY Root Element
        $xml->startElement('AUTH_GATEWAY');
        $xml->writeAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $xml->writeAttribute('xmlns:xsd', 'http://www.w3.org/2001/XMLSchema');

        // TRANSACTION
        $xml->startElement('TRANSACTION');
        $xml->writeElement('TRANSACTION_ID', $transactionId);

        // MERCHANT
        $xml->startElement('MERCHANT');
        $xml->writeElement('TERMINAL_ID', $terminalId);
        $xml->endElement(); // MERCHANT

        // PACKET
        $xml->startElement('PACKET');

        // IDENTIFIER
        $xml->writeElement('IDENTIFIER', $identifier);

        // ACCOUNT
        $xml->startElement('ACCOUNT');
        $xml->writeElement('TOKEN', $token);
        $xml->endElement(); // ACCOUNT

        // CONSUMER
        $xml->startElement('CONSUMER');
        $xml->writeElement('FIRST_NAME', $firstName);
        $xml->writeElement('LAST_NAME', $lastName);
        $xml->writeElement('ADDRESS1', $address1);
        $xml->writeElement('ADDRESS2', $address2 ?? '');
        $xml->writeElement('CITY', $city);
        $xml->writeElement('STATE', $state);
        $xml->writeElement('ZIP', $zip);
        $xml->writeElement('PHONE_NUMBER', $phoneNumber);
        $xml->writeElement('DL_STATE', '');
        $xml->writeElement('DL_NUMBER', '');
        $xml->writeElement('COURTESY_CARD_ID', '');
        $xml->endElement(); // CONSUMER

        // CHECK
        $xml->startElement('CHECK');
        $xml->writeElement('CHECK_AMOUNT', $amount); // Ensures proper decimal format
        $xml->endElement(); // CHECK

        // CUSTOM
        $xml->startElement('CUSTOM');
        $xml->writeElement('CUSTOM1', $transactionId);
        $xml->endElement(); // CUSTOM

        $xml->endElement(); // PACKET
        $xml->endElement(); // TRANSACTION
        $xml->endElement(); // AUTH_GATEWAY

        return $xml->outputMemory();
    }
}
