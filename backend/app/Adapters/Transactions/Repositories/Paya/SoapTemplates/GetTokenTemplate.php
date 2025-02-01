<?php

namespace App\Adapters\Transactions\Repositories\Paya\SoapTemplates;

class GetTokenTemplate
{
    public static function generate(
        string $transactionId,
        string $terminalId,
        string $routingNumber,
        string $accountNumber,
        string $accountType,
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
        // ACCOUNT
        $xml->startElement('ACCOUNT');
        $xml->writeElement('ROUTING_NUMBER', $routingNumber);
        $xml->writeElement('ACCOUNT_NUMBER', $accountNumber);
        $xml->writeElement('ACCOUNT_TYPE', $accountType);
        $xml->endElement(); // ACCOUNT

        $xml->endElement(); // PACKET
        $xml->endElement(); // TRANSACTION
        $xml->endElement(); // AUTH_GATEWAY

        return $xml->outputMemory();

        return "<TRANSACTION>
            <TRANSACTION_ID>$transactionId</TRANSACTION_ID>
            <MERCHANT>
              <TERMINAL_ID>$terminalId</TERMINAL_ID>
            </MERCHANT>
            <PACKET>
              <ACCOUNT>
                <ROUTING_NUMBER>$routingNumber</ROUTING_NUMBER>
                <ACCOUNT_NUMBER>$accountNumber</ACCOUNT_NUMBER>
                <ACCOUNT_TYPE>$accountType</ACCOUNT_TYPE>
              </ACCOUNT>
            </PACKET>
          </TRANSACTION>";
        /*        return <<<XML
    <DataPacket><![CDATA[
        <AUTH_GATEWAY xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                      xmlns:xsd="http://www.w3.org/2001/XMLSchema">
          <TRANSACTION>
            <TRANSACTION_ID>$transactionId</TRANSACTION_ID>
            <MERCHANT>
              <TERMINAL_ID>$terminalId</TERMINAL_ID>
            </MERCHANT>
            <PACKET>
              <ACCOUNT>
                <ROUTING_NUMBER>$routingNumber</ROUTING_NUMBER>
                <ACCOUNT_NUMBER>$accountNumber</ACCOUNT_NUMBER>
                <ACCOUNT_TYPE>$accountType</ACCOUNT_TYPE>
              </ACCOUNT>
            </PACKET>
          </TRANSACTION>
        </AUTH_GATEWAY>
      ]]></DataPacket>
  XML;/*
        return trim(<<<XML
<?xml version="1.0" encoding="utf-8"?>
    <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                   xmlns:xsd="http://www.w3.org/2001/XMLSchema"
                   xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
      <soap:Header>
        <AuthGatewayHeader xmlns="http://tempuri.org/GETI.eMagnus.WebServices/AuthGateway">
          <UserName>RecoAnywhereGateway</UserName>
          <Password>JokX2qj5#JGDSQuv</Password>
          <TerminalID>1356706</TerminalID>
        </AuthGatewayHeader>
      </soap:Header>
      <soap:Body>
        <GetToken xmlns="http://tempuri.org/GETI.eMagnus.WebServices/AuthGateway">
          <DataPacket><![CDATA[
            <AUTH_GATEWAY xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                          xmlns:xsd="http://www.w3.org/2001/XMLSchema">
              <TRANSACTION>
                <TRANSACTION_ID>1729251995</TRANSACTION_ID>
                <MERCHANT>
                  <TERMINAL_ID>1356706</TERMINAL_ID>
                </MERCHANT>
                <PACKET>
                  <ACCOUNT>
                    <ROUTING_NUMBER>490000018</ROUTING_NUMBER>
                    <ACCOUNT_NUMBER>5007090255</ACCOUNT_NUMBER>
                    <ACCOUNT_TYPE>Checking</ACCOUNT_TYPE>
                  </ACCOUNT>
                </PACKET>
              </TRANSACTION>
            </AUTH_GATEWAY>
          ]]></DataPacket>
        </GetToken>
      </soap:Body>
    </soap:Envelope>
XML
        );*/
    }
}
