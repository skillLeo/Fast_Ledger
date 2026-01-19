<?php

namespace App\Http\Controllers\CompanyModule\Traits;

trait CompanyDataTrait
{
    /**
     * Get countries list
     */
    protected function getCountries(): array
    {
        return [
            'GB' => 'United Kingdom',
            'ES' => 'Spain',
            'FR' => 'France',
            'DE' => 'Germany',
            'IT' => 'Italy',
            // Add more as needed
        ];
    }

    /**
     * Get Spain company types
     */
    protected function getCompanyTypesES(): array
    {
        return [
            'autonomo' => 'Autónomo',
            'sociedad_limitada' => 'Sociedad Limitada (SL)',
            'sociedad_anonima' => 'Sociedad Anónima (SA)',
            'cooperativa' => 'Cooperativa',
            'sociedad_civil' => 'Sociedad Civil',
            'comunidades_bienes' => 'Comunidades de Bienes',
            'fundacion_asociacion' => 'Fundación / Asociación',
            'otra' => 'Otra',
        ];
    }

    /**
     * Get UK company types
     */
    protected function getCompanyTypesUK(): array
    {
        return [
            'sole_trader' => 'Sole Trader',
            'private_limited_company' => 'Private Limited Company (Ltd)',
            'public_limited_company' => 'Public Limited Company (PLC)',
            'limited_liability_partnership' => 'Limited Liability Partnership (LLP)',
            'partnership' => 'Partnership',
            'community_interest_company' => 'Community Interest Company (CIC)',
            'charity' => 'Charity',
            'overseas_company' => 'Overseas Company',
            'other' => 'Other',
        ];
    }

    /**
     * Get tax regimes (Spain only)
     */
    protected function getTaxRegimes(): array
    {
        return [
            'regimen_general' => 'Régimen General',
            'regimen_simplificado' => 'Régimen Simplificado',
            'recargo_equivalencia' => 'Recargo de Equivalencia',
            'agricultura_ganaderia_pesca' => 'Agricultura / Ganadería / Pesca',
            'grupo_iva' => 'Grupo de IVA',
            'oss_ioss' => 'OSS / IOSS',
            'estimacion_directa_objetiva' => 'Estimación Directa / Objetiva',
            'bienes_usados_arte_antiguos' => 'Bienes Usados / Arte / Objetos Antiguos',
            'otra' => 'Otra',
        ];
    }
}