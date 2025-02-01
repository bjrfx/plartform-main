<?php

namespace App\Adapters\Billings\Repositories\Tyler\SoapTemplates;

use Illuminate\Support\Arr;
use InvalidArgumentException;
use XMLWriter;

class GetRequestTemplate
{
    public static function generate(
        string $username,
        string $password,
        string $SOAPAction,
        array $soapBodyData,
    ): string {
        if (empty($username) || empty($password) || empty($SOAPAction) || empty($soapBodyData)) {
            throw new InvalidArgumentException("All parameters must be provided and non-empty.");
        }

        $writer = new XMLWriter();
        $writer->openMemory(); // Write to memory instead of a file
        $writer->startDocument('1.0', 'UTF-8');

        // Root Envelope
        $writer->startElementNs(
            'env',
            'Envelope',
            'http://www.w3.org/2003/05/soap-envelope'
        );

        // Header
        self::generateHeader(
            writer: $writer,
            username: $username,
            password: $password
        );
        // Body
        self::generateBody(
            writer: $writer,
            SOAPAction: $SOAPAction,
            soapBodyData:$soapBodyData
        );

        // Close Envelope
        $writer->endElement(); // env:Envelope

        $writer->endDocument();

        return $writer->outputMemory(); // Return the generated XML as a string
    }

    private static function generateHeader(XMLWriter $writer, string $username, string $password): void
    {
        $writer->startElement('env:Header');
        /** @noinspection HttpUrlsUsage */
        $writer->startElementNs(
            'wsse',
            'Security',
            'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd'
        );
        $writer->startElement('wsse:UsernameToken');
        $writer->writeElement('wsse:Username', $username);
        $writer->writeElement('wsse:Password', $password);
        $writer->endElement(); // wsse:UsernameToken
        $writer->endElement(); // wsse:Security
        $writer->endElement(); // env:Header
    }

    /** @noinspection SpellCheckingInspection */
    private static function generateBody(XMLWriter $writer, string $SOAPAction, array $soapBodyData): void
    {
        $writer->startElement('env:Body');
        /** @noinspection HttpUrlsUsage */
        $writer->startElementNs(
            'ns1',
            $SOAPAction,
            'http://tempuri.org/'
        );

        //Tyler fail if 'strJur', 'nTaxyr' are not at a defined order
        $soapBodyData = self::addBodyElement(writer: $writer,
            soapBodyData: $soapBodyData,
            key: 'strJur'
        );
        $soapBodyData = self::addBodyElement(writer: $writer,
            soapBodyData: $soapBodyData,
            key: 'nTaxyr'
        );

        // Add soapBodyData elements dynamically
        $dynamicSoapBodyData = Arr::except($soapBodyData, ['nMaxRec']);
        foreach ($dynamicSoapBodyData as $key => $val) {
            self::addBodyElement(writer: $writer,
                soapBodyData: $dynamicSoapBodyData,
                key: $key
            );
        }

        self::addBodyElement(writer: $writer,
            soapBodyData: $soapBodyData,
            key: 'nMaxRec'
        );

        $writer->endElement(); // ns1:$SOAPAction
        $writer->endElement(); // env:Body
    }

    private static function addBodyElement(XMLWriter $writer, array $soapBodyData, string $key): array
    {
        $value = Arr::pull($soapBodyData, $key);
        if (!is_null($value)) {
            $writer->startElement("ns1:$key");
            $writer->writeRaw("<![CDATA[$value]]>");
            $writer->endElement();
        }

        return $soapBodyData;
    }
}
