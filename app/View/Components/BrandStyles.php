<?php

namespace App\View\Components;

use App\Models\Landlord\Studio;
use Illuminate\View\Component;
use Illuminate\View\View;

class BrandStyles extends Component
{
    public array $brand;

    public function __construct()
    {
        $settings = Studio::current()?->settings;

        // DVN's own palette — used on marketing pages, the Super Admin
        // panel, and as a safety net if a tenant has no settings yet.
        $this->brand = [
            'color_primary'    => $settings->color_primary    ?? '#E50914',
            'color_secondary'  => $settings->color_secondary  ?? '#1F2833',
            'color_accent'     => $settings->color_accent      ?? '#C9A84C',
            'color_text_dark'  => $settings->color_text_dark   ?? '#0B0C10',
            'color_text_light' => $settings->color_text_light  ?? '#F8F9FA',
            'color_background' => $settings->color_background  ?? '#FFFFFF',
            'font_heading'     => $settings->font_heading       ?? 'Plus Jakarta Sans',
            'font_body'        => $settings->font_body          ?? 'Inter',
        ];
    }

    public function render(): View
    {
        return view('components.brand-styles');
    }
}