<?php

namespace App\Adapters\Transactions\Repositories\Paya\SoapTemplates;

class GetRequestTemplate
{
    /** @noinspection XmlUnusedNamespaceDeclaration */
    public static function generate(
        string $soapAuthGateway,
        string $username,
        string $password,
        string $terminalId,
        string $SOAPAction,
        string $soapBody
    ): string
    {
        $soapBody = trim($soapBody);
        $xml = new \XMLWriter();
        $xml->openMemory();
        $xml->setIndent(true); // Enables indentation for readability
        $xml->startDocument('1.0', 'UTF-8');

// Start SOAP Envelope
        $xml->startElementNS('soap12', 'Envelope', 'http://www.w3.org/2003/05/soap-envelope');

// Header
        $xml->startElement('soap12:Header');
        $xml->startElement('AuthGatewayHeader');
        $xml->writeAttribute('xmlns', $soapAuthGateway);
        $xml->writeElement('UserName', $username);
        $xml->writeElement('Password', $password);
        $xml->writeElement('TerminalID', $terminalId);
        $xml->endElement(); // AuthGatewayHeader
        $xml->endElement(); // soap12:Header

// Body
        $xml->startElement('soap12:Body');
        $xml->startElement($SOAPAction);
        $xml->writeAttribute('xmlns', $soapAuthGateway);

// DataPacket (with CDATA)
        $xml->startElement('DataPacket');
        $xml->writeCData($soapBody);
        $xml->endElement(); // DataPacket

        $xml->endElement(); // ProcessSingleCheck
        $xml->endElement(); // soap12:Body
        $xml->endElement(); // soap12:Envelope

        return $xml->outputMemory();
    }
}
