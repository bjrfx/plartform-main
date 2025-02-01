<?php

namespace App\Adapters\Billings\Repositories\Tyler;

use App\Adapters\Billings\Repositories\Tyler\SoapTemplates\GetRequestTemplate;
use App\Adapters\Billings\Services\AddressParserService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use XMLReader;
use SimpleXMLElement;

class TylerXmlApiRepository
{
    protected string $apiUrl;
    protected string $username;
    protected string $password;

    protected const int LIMIT = 100;
    protected const int PER_PAGE = 10;
    protected const string SOAP_ACTION_SEARCH = 'SearchParcel';

    /** @noinspection SpellCheckingInspection */
    protected const string SOAP_ACTION_ITEM = 'GetPymtsPostingRecordsAsXml';


    public function __construct(object $gateway)
    {
        $this->apiUrl = $gateway->custom_url;//$gateway->getAttribute('custom_url');
        $this->username = $gateway->username;//$gateway->getAttribute('username');
        $this->password = $gateway->password;//$gateway->getAttribute('password');
    }

    /**
     * @throws ValidationException | ConnectionException
     */
    public function searchParcel(array $params, int $page = 1, array $cycleDates = [], bool $fullInfo = true): array
    {

        $data = $this->getSearchParcelPayload($params);

        $soapEnvelope = GetRequestTemplate::generate(
            username: $this->username,
            password: $this->password,
            SOAPAction: self::SOAP_ACTION_SEARCH,
            soapBodyData: $data
        );

        $responseBody = $this->sendPostRequest(
            SOAPAction: self::SOAP_ACTION_SEARCH,
            soapEnvelope: $soapEnvelope
        );

        return $this->buildSearchParcelResponse(
            responseBody: $responseBody,
            page: $page,
            cycleDates: $cycleDates,
            fullInfo: $fullInfo
        );
    }

    /**
     * @throws ValidationException
     * @throws ConnectionException
     */
    public function getParcelItemInfo(string $jur, string $parcelId, array $cycleDates): array
    {
        $soapEnvelope = $this->getParcelItemSoapEnvelope(
            jur: $jur,
            parcelId: $parcelId
        );

        $responseBody = $this->sendPostRequest(
            SOAPAction: self::SOAP_ACTION_ITEM,
            soapEnvelope: $soapEnvelope
        );

        $responseBody = current($responseBody);

        $currentCycleIndex = $this->getCurrentCycleIndexFromCycleDates(cycleDates: $cycleDates);

        $dues = $this->calculateBillDues(
            responseBody: $responseBody,
            currentCycleIndex: $currentCycleIndex
        );

        $responseBody['current_due'] = $dues['current_due'];
        $responseBody['due'] = $dues['due'];
        $responseBody['delinquent_due'] = $dues['delinquent_due'];
        $responseBody['total_due'] = 0;
        $responseBody['total_due'] += $responseBody['current_due'];
        $responseBody['total_due'] += $responseBody['due'];
        $responseBody['total_due'] += $responseBody['delinquent_due'];

        return $responseBody;
    }

    private function getParcelItemSoapEnvelope(string $jur, string $parcelId): string
    {
        /** @noinspection SpellCheckingInspection */
        return GetRequestTemplate::generate(
            username: $this->username,
            password: $this->password,
            SOAPAction: self::SOAP_ACTION_ITEM,
            soapBodyData: [
                'strJur' => $jur,
                'strParid' => $parcelId,
            ]
        );
    }

    /**
     * @throws ValidationException
     * @noinspection SpellCheckingInspection
     */
    private function getSearchParcelPayload(array $params): array
    {
        $okay = false;
        $data = [
            'nMaxRec' => self::LIMIT,
        ];
        $jur = Arr::get($params, 'strJur');
        if (!is_null($jur)) {
            $data['strJur'] = $jur;
        }

        $taxYear = Arr::get($params, 'nTaxyr');
        if (!is_null($jur)) {
            $data['nTaxyr'] = $taxYear;
        }
        $name = Arr::get($params, 'strOwner');
        if (!is_null($name)) {
            $okay = true;
            $data['strOwner'] = $name;
        }
        $parcelId = Arr::get($params, 'strParid');
        if (!$okay && !is_null($parcelId)) {
            $okay = true;
            $data['strParid'] = $parcelId;
        }
        $address = Arr::get($params, 'address');
        if (!$okay && !is_null($address)) {
            $okay = true;
            $data = $this->getAddressParts(data: $data, address: $address);
        }

        if ($okay) {
            return $data;
        }

        throw ValidationException::withMessages([
            'tyler_soap_error' => "Search Parcel invalid payload",
        ]);
    }

    /**
     * @noinspection SpellCheckingInspection
     *
     * @throws ValidationException
     */
    private function buildSearchParcelResponse(array $responseBody, int $page = 1, array $cycleDates = [], bool $fullInfo = true): array
    {
        $total = count($responseBody);
        $offset = ($page - 1) * self::PER_PAGE;
        $paginatedData = array_slice($responseBody, $offset, self::PER_PAGE);
        $responseData = [
            'total' => $total,
            'per_page' => self::PER_PAGE,
            'current_page' => $page,
            'last_page' => ceil($total / static::PER_PAGE),
            'data' => [],
        ];

        foreach ($paginatedData as $row) {
            $responseData['data'][] = [
                'owner' => Arr::get($row, 'OWNER', ''),
                'Parcel_id' => Arr::get($row, 'PARID', ''),
                'stub' => Arr::get($row, 'STUB', ''),
                'jur' => Arr::get($row, 'JUR', ''),
                'tax_year' => Arr::get($row, 'TAXYR', ''),
                'address' => Arr::get($row, 'FULLADD', ''),
                'current_due' => 0,
                'due' => 0,
                'delinquent_due' => 0,
                'total_due' => 0,
                'actions' => '',
            ];
        }

        if ($fullInfo) {
            $responseData['data'] = $this->assignSearchParcelItemsInfo(paginatedData: $responseData['data'], cycleDates: $cycleDates);
        }

        return $responseData;
    }

    /**
     * @throws ValidationException
     */
    private function assignSearchParcelItemsInfo(array $paginatedData, array $cycleDates = []): array
    {
        $currentCycleIndex = $this->getCurrentCycleIndexFromCycleDates(cycleDates: $cycleDates);
        $responses = $this->getMultipleParcelItemsInfo(paginatedData: $paginatedData);

        foreach ($paginatedData as $key => $data) {
            $ParcelId = Arr::get($data, 'Parcel_id');
            $response = Arr::get($responses, $ParcelId);
            if ($response instanceof Response && $response->successful()) {
                $responseBody = $this->assignResponseBodyToArray(response: $response, SOAPAction: self::SOAP_ACTION_ITEM);

                $dues = $this->calculateBillDues(
                    responseBody: $responseBody,
                    currentCycleIndex: $currentCycleIndex
                );

                $paginatedData[$key]['current_due'] = $dues['current_due']; //the amount currently owed
                $paginatedData[$key]['due'] = $dues['due']; //the amount previously owed
                $paginatedData[$key]['delinquent_due'] = $dues['delinquent_due']; //the overdue
                $paginatedData[$key]['total_due'] += $dues['current_due'];
                $paginatedData[$key]['total_due'] += $dues['due'];
                $paginatedData[$key]['total_due'] += $dues['delinquent_due'];
            }
        }

        return $paginatedData;
    }

    private function getMultipleParcelItemsInfo(array $paginatedData): array
    {
        //Generate the XML once with placeholder
        $placeholderJur = '{{JUR}}';
        $placeholderParcelId = '{{PARCEL_ID}}';
        $soapEnvelope = $this->getParcelItemSoapEnvelope(
            jur: $placeholderJur,
            parcelId: $placeholderParcelId,
        );

        $soapEnvelopes = [];
        foreach ($paginatedData as $row) {
            $ParcelId = Arr::get($row, 'Parcel_id');
            $jur = Arr::get($row, 'jur');
            $soapEnvelopes[$ParcelId] = Str::of($soapEnvelope)
                ->replace($placeholderJur, $jur)
                ->replace($placeholderParcelId, $ParcelId);
        }
        //retry(3, 100) retries each request up to 3 times, waiting 100 milliseconds between attempts
        return Http::retry(3, 100)
            ->timeout(5)
            ->pool(function (Pool $pool) use ($soapEnvelopes) {
                $SOAPAction = self::SOAP_ACTION_ITEM;
                $requests = [];
                foreach ($soapEnvelopes as $ParcelId => $body) {
                    /** @noinspection HttpUrlsUsage */
                    $requests[] = $pool->as($ParcelId)->withHeaders([
                        'Accept' => 'text/xml',
                        'Cache-Control' => 'no-cache',
                        'Pragma' => 'no-cache',
                    ])
                        ->withOptions(['version' => '1.1']) // Force HTTP/1.1
                        ->withBody(
                            $body,
                            "application/soap+xml;charset=utf-8;action=\"http://tempuri.org/IPymt_2016_09/$SOAPAction\""
                        )
                        ->post($this->apiUrl);
                }

                return $requests;
            });
    }

    /**
     * @throws ValidationException
     * @throws ConnectionException
     */
    private function sendPostRequest(string $SOAPAction, string $soapEnvelope): array
    {
        $soapEnvelope = trim($soapEnvelope);
        $soapEnvelope = preg_replace('/\r\n|\r|\n/', '', $soapEnvelope);

        /** @noinspection HttpUrlsUsage */
        $response = Http::withHeaders([
            'Accept' => 'text/xml',
            'Cache-Control' => 'no-cache',
            'Pragma' => 'no-cache',
        ])
            ->withOptions(['version' => '1.1']) // Force HTTP/1.1
            ->withBody($soapEnvelope, "application/soap+xml;charset=utf-8;action=\"http://tempuri.org/IPymt_2016_09/$SOAPAction\"")
            ->post($this->apiUrl);

        if ($response->failed()) {
            // Throw a validation exception with the extracted message
            throw ValidationException::withMessages([
                'tyler_soap_error' => "HTTP request failed with status: " . $response->status(),
            ]);
        }

        return $this->assignResponseBodyToArray(response: $response, SOAPAction: $SOAPAction);
    }


    private function getCurrentCycleIndexFromCycleDates(array $cycleDates): int
    {
        $today = Carbon::today();

        $sortedDates = collect($cycleDates)
            ->map(fn($date) => Carbon::parse($date)) // Parse dates into Carbon instances
            ->sort() // Order dates in ASC order
            ->values(); // Reset the keys after sorting

        $ascendingIndex = $sortedDates->search(fn($date) => $date->greaterThanOrEqualTo($today)); // Find the first date >= today

        if ($ascendingIndex !== false) {
            return $sortedDates->count() - 1 - $ascendingIndex; // Convert to DESC index
        }
        return -1;
    }

    /**
     * @throws ValidationException | Exception
     */
    private function assignResponseBodyToArray(Response $response, string $SOAPAction): array
    {
        $localName = match ($SOAPAction) {
            self::SOAP_ACTION_ITEM => 'PRC',
            self::SOAP_ACTION_SEARCH => 'ROW',
            default => throw ValidationException::withMessages([
                'tyler_soap_error' => "Invalid local name"
            ]),
        };
        $responseXml = $response->body(); // Get the raw XML response

        $decodedXml = html_entity_decode($responseXml);
        //XML Reader do not load the entire XML into memory
        //XML Reader process the XML node by node, so memory is cleared as it move through the document
        $reader = new XMLReader();
        $reader->xml($decodedXml);

        $rows = []; // Initialize an array to store all rows
        while ($reader->read()) {
            if ($reader->nodeType === XMLReader::ELEMENT && $reader->localName === $localName) {
                // Convert the current <$localName> node into a SimpleXMLElement and then to an array
                $node = new SimpleXMLElement($reader->readOuterXML());
                $rows[] = json_decode(json_encode($node), true);
            }
        }

        $reader->close();

        return $this->transformEmptyArraysToNull($rows);
    }

    private function transformEmptyArraysToNull(array $array = [])
    {
        return array_map(function ($item) {
            if (is_array($item)) {
                $item = $this->transformEmptyArraysToNull($item);
                return empty($item) ? null : $item;
            }
            return $item;
        }, $array);
    }

    /** @noinspection SpellCheckingInspection */
    private function calculateBillDues(array $responseBody, int $currentCycleIndex): array
    {
        $dues = [
            'current_due' => 0, //the amount currently owed
            'due' => 0, //the amount previously owed
            'delinquent_due' => 0, //the overdue
        ];

        $tiCycles = Arr::get($responseBody, 'PROPERTY.TI_CYCLES.TI_CYCLE', []);

        foreach ($tiCycles as $tiCycle) {
            $payyr = (int)Arr::get($tiCycle, 'PAYYR');
            $taxyr = (int)Arr::get($tiCycle, 'TAXYR');
            $totamt = (float)Arr::get($tiCycle, 'TOTAMT');
            $cycle = (int)Arr::get($tiCycle, 'CYCLE');
            if ($payyr === $taxyr) {
                //Current tax year
                if ($cycle === $currentCycleIndex) {
                    //The “current due” amount is the value of property “TOTAMT” when the attribute “CYCLE” value is equal to “current cycle index”.
                    //Note: only if “current cycle index” exists.
                    $dues['current_due'] = $totamt;
                }
                if ($cycle < $currentCycleIndex) {
                    //The “due” amount is the value of property “TOTAMT” when the attribute “CYCLE” value is smaller than “current cycle index”.
                    $dues['due'] += $totamt;
                }
            }

            if ($payyr < $taxyr) {
                //Previous tax years are if: “PAYYR” is smaller than “TAXYR” (PAYYR< TAXYR)
                //The “delinquent due” is an accumulated amount from each cycle property “TOTAMT” value.
                $dues['delinquent_due'] += $totamt;
            }
        }

        return $dues;
    }

    private function getAddressParts(array $data, string $address): array
    {
        $service = new AddressParserService();
        $addressParts = $service->parse(address: $address);

        $parsedAddressToTyler = [
            'street_number' => 'nAdrno',
            'directional' => 'strAdrdir',
            'street_name' => 'strAdrstr',
            'unit_number' => 'strUnitno',
        ];

        foreach ($parsedAddressToTyler as $key => $index) {
            $addressValue = Arr::get($addressParts, $key);
            if (!is_null($addressValue)) {
                $data[$index] = $addressValue;
            }
        }

        return $data;
    }
}
