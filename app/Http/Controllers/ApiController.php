<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Sekolah;

class ApiController extends Controller
{
    private function getApiKey()
    {
        $sekolah = Sekolah::first();
        return $sekolah ? $sekolah->api_co_id_key : '';
    }

    private function getBaseUrl()
    {
        return 'https://use.api.co.id';
    }

    public function searchSekolah(Request $request)
    {
        $keyword = $request->query('name', '');
        if (strlen($keyword) < 3) {
            return response()->json(['data' => []]);
        }

        try {
            $response = Http::withHeaders([
                'x-api-co-id' => $this->getApiKey()
            ])->timeout(5)->get($this->getBaseUrl() . '/regional/indonesia/schools', [
                'name' => $keyword
            ]);

            if ($response->successful()) {
                // api.co.id returns standard paginated/array data
                return response()->json($response->json());
            }
        } catch (\Exception $e) {
            // Log error silently
        }

        return response()->json(['data' => []]);
    }

    public function getProvinces()
    {
        try {
            $response = Http::withHeaders([
                'x-api-co-id' => $this->getApiKey()
            ])->timeout(5)->get($this->getBaseUrl() . '/regional/indonesia/provinces');

            if ($response->successful()) {
                return response()->json($response->json());
            }
        } catch (\Exception $e) {}

        return response()->json([]);
    }

    public function getRegencies($provinceCode)
    {
        try {
            // Coba dengan path param atau query param
            $response = Http::withHeaders([
                'x-api-co-id' => $this->getApiKey()
            ])->timeout(5)->get($this->getBaseUrl() . '/regional/indonesia/regencies', [
                'province_code' => $provinceCode
            ]);

            // Fallback ke path jika 404
            if ($response->status() == 404) {
                $response = Http::withHeaders([
                    'x-api-co-id' => $this->getApiKey()
                ])->timeout(5)->get($this->getBaseUrl() . '/regional/indonesia/regencies/' . $provinceCode);
            }

            if ($response->successful()) {
                return response()->json($response->json());
            }
        } catch (\Exception $e) {}

        return response()->json([]);
    }

    public function getDistricts($regencyCode)
    {
        try {
            $response = Http::withHeaders([
                'x-api-co-id' => $this->getApiKey()
            ])->timeout(5)->get($this->getBaseUrl() . '/regional/indonesia/districts', [
                'regency_code' => $regencyCode
            ]);

            if ($response->status() == 404) {
                $response = Http::withHeaders([
                    'x-api-co-id' => $this->getApiKey()
                ])->timeout(5)->get($this->getBaseUrl() . '/regional/indonesia/districts/' . $regencyCode);
            }

            if ($response->successful()) {
                return response()->json($response->json());
            }
        } catch (\Exception $e) {}

        return response()->json([]);
    }

    public function getVillages($districtCode)
    {
        try {
            $response = Http::withHeaders([
                'x-api-co-id' => $this->getApiKey()
            ])->timeout(5)->get($this->getBaseUrl() . '/regional/indonesia/villages', [
                'district_code' => $districtCode
            ]);

            if ($response->status() == 404) {
                $response = Http::withHeaders([
                    'x-api-co-id' => $this->getApiKey()
                ])->timeout(5)->get($this->getBaseUrl() . '/regional/indonesia/villages/' . $districtCode);
            }

            if ($response->successful()) {
                return response()->json($response->json());
            }
        } catch (\Exception $e) {}

        return response()->json([]);
    }

    public function getHolidays(Request $request)
    {
        $year = $request->query('year', date('Y'));
        $month = $request->query('month');
        
        $params = ['year' => $year];
        if ($month) {
            $params['month'] = $month;
        }

        try {
            $response = Http::withHeaders([
                'x-api-co-id' => $this->getApiKey()
            ])->timeout(5)->get($this->getBaseUrl() . '/indonesian-holidays', $params);

            if ($response->successful()) {
                return response()->json($response->json());
            }
        } catch (\Exception $e) {}

        return response()->json([]);
    }
}
