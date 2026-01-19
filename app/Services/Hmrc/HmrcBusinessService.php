<?php

namespace App\Services\Hmrc;

use App\Models\HmrcBusiness;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

use function Pest\Laravel\call;

class HmrcBusinessService
{
    public function __construct(private HmrcApiClient $apiClient) {}

    public function syncAllBusinesses(int $userId, string $nino, ?string $testScenario = null): Collection
    {
        $response = $this->apiClient->get("/individuals/business/details/{$nino}/list", [], $testScenario);

        $businesses = collect((array) ($response['listOfBusinesses'] ?? []));

        $businessesDbData = collect();
        foreach ($businesses as $businessData) {
            $link = $businessData['links'][0];


            $data  = [
                'user_id' => $userId,
                'business_id' => (string) $businessData['businessId'],
                'nino' => $nino,
                'type_of_business' => (string) ($businessData['typeOfBusiness'] ?? ''),
                'trading_name' => $businessData['tradingName'] ?? null,
                'accounting_type' => $businessData['accountingType'] ?? null,
                'commencement_date' => $businessData['commencementDate'] ?? null,
                'cessation_date' => $businessData['cessationDate'] ?? null,
                'business_address_json' => $businessData['businessAddress'] ?? null,
                'accounting_periods_json' => $businessData['accountingPeriods'] ?? null,
                'metadata_json' => $businessData,
                'last_synced_at' => now(),
            ];

            // if ($link['rel'] === 'self') {
            $businessDetail  = null;

            try {
                $businessDetail = $this->apiClient->get($link['href'], [], $testScenario);
            } catch (\Exception $e) {
                Log::error('Failed to fetch business details', [
                    'business_id' => $businessData['businessId'],
                    'error' => $e->getMessage()
                ]);

                // JSON encode array fields before insertion
                $data['accounting_periods_json'] = json_encode($data['accounting_periods_json']);
                $data['business_address_json'] = json_encode($data['business_address_json']);
                $data['metadata_json'] = json_encode($data['metadata_json']);

                $businessesDbData->push($data);

                continue;
            }
            $data = array_merge($data, [
                'trading_name' => $businessDetail['tradingName'] ?? null,
                'accounting_type' => $businessDetail['accountingType'] ?? null,
                'commencement_date' => $businessDetail['commencementDate'] ?? null,
                'cessation_date' => $businessDetail['cessationDate'] ?? null,
                'quarterly_period_type' => $businessDetail['quarterlyTypeChoice']['quarterlyPeriodType'] ?? null,
                'tax_year_of_choice' => $businessDetail['quarterlyTypeChoice']['taxYearOfChoice'] ?? null,
                'accounting_periods_json' => $businessDetail['accountingPeriods'] ?? null,
                'business_address_json' =>  [
                    'addressLine1' => $businessDetail['businessAddressLineOne'] ?? null,
                    'addressLine2' => $businessDetail['businessAddressLineTwo'] ?? null,
                    'addressLine3' => $businessDetail['businessAddressLineThree'] ?? null,
                    'addressLine4' => $businessDetail['businessAddressLineFour'] ?? null,
                    'postCode' => $businessDetail['businessAddressPostCode'] ?? null,
                    'countryCode' => $businessDetail['businessAddressCountryCode'] ?? null,
                ]
            ]);

            // JSON encode array fields before insertion
            $data['accounting_periods_json'] = json_encode($data['accounting_periods_json']);
            $data['business_address_json'] = json_encode($data['business_address_json']);
            $data['metadata_json'] = json_encode($data['metadata_json']);

            $businessesDbData->push($data);
        }

        // Upsert: insert or update based on user_id and business_id in a single query
        HmrcBusiness::upsert(
            $businessesDbData->toArray(),
            ['user_id', 'business_id'], // Unique keys
            [
                'nino',
                'type_of_business',
                'trading_name',
                'accounting_type',
                'commencement_date',
                'cessation_date',
                'quarterly_period_type',
                'tax_year_of_choice',
                'business_address_json',
                'accounting_periods_json',
                'metadata_json',
                'last_synced_at'
            ] // Columns to update if record exists
        );

        return HmrcBusiness::where('user_id', $userId)->get();
    }

    public function getBusinessDetails(int $userId, string $businessId): array
    {
        $biz = HmrcBusiness::where('user_id', $userId)
            ->where('business_id', $businessId)
            ->firstOrFail();

        $response = $this->apiClient->get("/individuals/business/details/{$biz->nino}/{$businessId}");
        $details = (array) $response;

        $biz->update([
            'metadata_json' => $details,
            'last_synced_at' => now(),
        ]);

        return $details;
    }

    public function updateQuarterlyPeriodType(string $businessId, string $taxYear, string $periodType): bool
    {
        // Placeholder: depends on specific HMRC endpoint for updates.
        Log::info('Updating quarterly period type', [
            'business_id' => $businessId,
            'tax_year' => $taxYear,
            'period_type' => $periodType,
        ]);

        return true;
    }
}
