<?php

namespace App\Adapters\Billings\Repositories\Tyler;

use App\Adapters\Billings\Services\AddressParserService;
use Carbon\Carbon;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Cache;

class TylerJsonApiRepository
{
    protected string $apiUrl;
    protected string $clientId;
    protected string $clientSecret;

    protected string $apiPath = '/api/public/v1/payments/';

    protected const int LIMIT = 100;
    protected const int PER_PAGE = 10;

    public function __construct(object $gateway)
    {
        $this->apiUrl = $gateway->custom_url;//$gateway->getAttribute('custom_url');
        $this->clientId = $gateway->username;//$gateway->getAttribute('username');
        $this->clientSecret = $gateway->password;//$gateway->getAttribute('password');
    }

    /**
     * @throws ValidationException
     * @throws ConnectionException
     */
    public function getFlagLabels(): array
    {
        return $this->SendGetRequest(rest: 'flag-labels');
    }

    /**
     * @throws ConnectionException
     * @throws ValidationException
     */
    public function getCycleLabels(): array
    {
        return $this->SendGetRequest(rest: 'labels');
    }


    /**
     * @throws ValidationException
     * @throws ConnectionException
     */
    public function searchParcel(array $criteria): array
    {
        $requestBody = $this->getSearchParcelPayload($criteria);

        return $this->SendPostRequest(
            rest: 'search',
            requestData: $requestBody
        );
    }

    /**
     * @throws ValidationException
     * @throws ConnectionException
     */
    public function GetPaymentInfo(string $parcelId)
    {
        return $this->SendGetRequest(rest: "payment-info/$parcelId");
    }


    /**
     * @throws ValidationException
     * @throws ConnectionException
     */
    public function postPayments(array $payments): array
    {
        if (!Arr::exists($payments, 0)) {
            $payments = [$payments];
        }
        // Transform payments using Arr::map
        $formattedPayments = Arr::map($payments, function ($payment) {
            return [
                'parid' => Arr::get($payment, 'parid'),
                'auditLine' => Arr::get($payment, 'auditLine'),
                'batch' => Arr::get($payment, 'batch'),
                'transno' => Arr::get($payment, 'transno'),
                'transTot' => Arr::get($payment, 'transTot'),
                'batchSeq' => Arr::get($payment, 'batchSeq'),
                'businessDate' => Arr::get($payment, 'businessDate'),
                'paymentMethod1' => Arr::get($payment, 'paymentMethod1'),
                'paymentMethodReference1' => Arr::get($payment, 'paymentMethodReference1'),
                'paymentMethodAmount1' => Arr::get($payment, 'paymentMethodAmount1'),
                'paymentMethod2' => Arr::get($payment, 'paymentMethod2'),
                'paymentMethodReference2' => Arr::get($payment, 'paymentMethodReference2'),
                'paymentMethodAmount2' => Arr::get($payment, 'paymentMethodAmount2'),
                'paymentMethod3' => Arr::get($payment, 'paymentMethod3'),
                'paymentMethodReference3' => Arr::get($payment, 'paymentMethodReference3'),
                'paymentMethodAmount3' => Arr::get($payment, 'paymentMethodAmount3'),
                'note2' => Arr::get($payment, 'note2'),
                'payerName' => Arr::get($payment, 'payerName'),
                'payerAddress1' => Arr::get($payment, 'payerAddress1'),
                'payerAddress2' => Arr::get($payment, 'payerAddress2'),
                'payerAddress3' => Arr::get($payment, 'payerAddress3'),
                'ownerSeq' => Arr::get($payment, 'ownerSeq'),
                'paymentSource' => Arr::get($payment, 'paymentSource'),
                'paymentStatus' => Arr::get($payment, 'paymentStatus'),
                'postingMethod' => Arr::get($payment, 'postingMethod'),
                'paymentAmountDetails' => Arr::get($payment, 'paymentAmountDetails'),
                'jur' => Arr::get($payment, 'jur'),
                'rollType' => Arr::get($payment, 'rollType'),
                'stubNumber' => Arr::get($payment, 'stubNumber'),
                'effectiveDate' => Arr::get($payment, 'effectiveDate'),
                'paymentAmount' => Arr::get($payment, 'paymentAmount'),
                'taxYear' => Arr::get($payment, 'taxYear'),
                'paymentType' => Arr::get($payment, 'paymentType'),
                'overPaymentAmount' => Arr::get($payment, 'overPaymentAmount'),
                'overPaymentType' => Arr::get($payment, 'overPaymentType'),
                'reference' => Arr::get($payment, 'reference'),
                'note1' => Arr::get($payment, 'note1'),
                'userId' => Arr::get($payment, 'userId'),
            ];
        });

        return $this->SendPostRequest(
            rest: 'labels',
            requestData: $formattedPayments
        );
    }


    /**
     * @throws ValidationException
     * @throws ConnectionException
     */
    private function SendGetRequest(string $rest)
    {
        $url = trim($this->apiUrl, '/');
        $url .= '/';
        $url .= trim($this->apiPath, '/');
        $url .= '/';
        $url .= trim($rest, '/');

        $response = Http::withToken($this->getAccessToken())
            ->acceptJson()
            ->get($url);

        if ($response->successful()) {
            return $response->json();
        }

        throw ValidationException::withMessages([
            'tyler_atENT_error' => "Failed to fetch $rest: " . $response->body(),
        ]);
    }


    /**
     * @throws ValidationException
     * @throws ConnectionException
     */
    private function SendPostRequest(string $rest, ?array $requestData = []): array
    {
        $url = trim($this->apiUrl, '/');
        $url .= '/';
        $url .= trim($this->apiPath, '/');
        $url .= '/';
        $url .= trim($rest, '/');

        $response = Http::withToken($this->getAccessToken())
            ->acceptJson()
            ->post($url, $requestData);

        if ($response->successful()) {
            return $response->json();
        }

        throw ValidationException::withMessages([
            'tyler_atENT_error' => "Failed to fetch $rest: " . $response->body(),
        ]);
    }


    /**
     * @throws ValidationException|ConnectionException
     */
    protected function getAccessToken(): string
    {
        $cacheKey = hash('sha256', "tyler_oauth_token_" . $this->clientId . $this->clientSecret);

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $response = Http::asForm()->post('https://tyler-cloudintegration.okta.com/oauth2/aus12ent05vxOQUye358/v1/token', [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'grant_type' => 'client_credentials',
            'scope' => 'ias-api',
        ]);

        if ($response->successful()) {
            // Return the access token from the response
            $data = $response->json();

            // Get the token and expiration time
            $accessToken = Arr::get($data, 'access_token', '');
            $expiresIn = Arr::get($data, 'expires_in', 600);

            $expiresIn = min($expiresIn, 3600);//Max the value to an hour
            $expiresIn = $expiresIn - 60; //remove 1 minute to be safe
            // Cache the token with the expiration time
            Cache::put($cacheKey, $accessToken, $expiresIn);

            return $accessToken;
        }

        //To be safe remove the cached
        Cache::forget($cacheKey);

        throw ValidationException::withMessages([
            'tyler_atENT_error' => 'Failed to obtain access token: ' . $response->body(),
        ]);
    }


    /**
     * @throws ValidationException
     */
    private function getSearchParcelPayload(array $params): array
    {
        $okay = false;
        $data = [
            'maxRec' => self::LIMIT,
        ];
        $jur = Arr::get($params, 'jur');
        if (!is_null($jur)) {
            $data['jur'] = $jur;
        }

        $taxYear = Arr::get($params, 'taxYr');
        if (!is_null($taxYear)) {
            $data['taxYr'] = $taxYear;
        }
        $name = Arr::get($params, 'owner');
        if (!is_null($name)) {
            $okay = true;
            $data['owner'] = $name;
        }
        $parcelId = Arr::get($params, 'parId');
        if (!$okay && !is_null($parcelId)) {
            $okay = true;
            $data['parId'] = $parcelId;
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
            'tyler_atENT_error' => "Search Parcel invalid payload",
        ]);
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

    private function getAddressParts(array $data, string $address): array
    {
        $service = new AddressParserService();
        $addressParts = $service->parse(address: $address);

        $parsedAddressToTyler = [
            'street_number' => 'adrNo',
            'directional' => 'adrDir',
            'street_suffix' => 'adrSuf',
            'street_name' => 'adrAdd',
            'unit_number' => 'unitNo',
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
