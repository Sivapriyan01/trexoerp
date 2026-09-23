<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class WebsiteSettingsController extends Controller
{
    /**
     * Display the website settings form
     */
    public function index()
    {
        $settings = [
            'msg91_widget_id'          => Setting::get('msg91_widget_id', config('services.msg91.template_id', env('MSG91_TEMPLATE_ID', '36697164476b323432353839'))),
            'msg91_token_auth'          => Setting::get('msg91_token_auth', config('services.msg91.auth_key', env('MSG91_AUTH_KEY', '572040TDOpHdLN6aab6e64P1'))),
            'msg91_auth_key'            => Setting::get('msg91_auth_key', config('services.msg91.auth_key', env('MSG91_AUTH_KEY', ''))),
            'msg91_template_id'         => Setting::get('msg91_template_id', config('services.msg91.template_id', env('MSG91_TEMPLATE_ID', ''))),
            'website_otp_required'      => Setting::get('website_otp_required', '1'),
            'website_store_name'        => Setting::get('website_store_name', 'Square Store'),
            'website_support_phone'     => Setting::get('website_support_phone', '6383272563'),
            'website_support_email'     => Setting::get('website_support_email', 'support@squarestore.in'),
            'website_free_delivery_min' => Setting::get('website_free_delivery_min', '999'),
            'website_shipping_fee'      => Setting::get('website_shipping_fee', '99'),
            'website_discount_percent'  => Setting::get('website_discount_percent', '0'),
            'website_announcement'      => Setting::get('website_announcement', '🎉 FREE Delivery on orders above ₹999! Verified Authentic Stock.'),
            'website_template'          => Setting::get('website_template', 'supermarket'),
            'website_primary_color'     => Setting::get('website_primary_color', '#10b981'),
            'website_accent_color'      => Setting::get('website_accent_color', '#059669'),
            'website_font_family'       => Setting::get('website_font_family', 'Inter'),
            'website_hero_title'        => Setting::get('website_hero_title', 'Seamless Shopping. Instant ERP Fulfillment.'),
            'website_hero_subtitle'     => Setting::get('website_hero_subtitle', 'Browse authentic products with live warehouse stock sync, instant GST-compliant invoicing, and automated courier dispatch.'),
            'website_hero_badge'        => Setting::get('website_hero_badge', 'Official ERP Connected Store'),
            'website_business_type'     => Setting::get('website_business_type', 'Clothing Store'),
            'website_positioning'       => Setting::get('website_positioning', 'sustainable fashion'),
            'website_palette_name'      => Setting::get('website_palette_name', 'Sage'),
            'website_layout_style'      => Setting::get('website_layout_style', 'split'),
            'website_border_radius'     => Setting::get('website_border_radius', '16px'),
            'website_header_style'      => Setting::get('website_header_style', 'regular'),
            'website_shadow_style'      => Setting::get('website_shadow_style', 'small'),
        ];

        return view('tenant.website_settings.index', compact('settings'));
    }

    /**
     * Update website settings
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'msg91_widget_id'          => 'nullable|string|max:100',
            'msg91_token_auth'          => 'nullable|string|max:100',
            'msg91_auth_key'            => 'nullable|string|max:100',
            'msg91_template_id'         => 'nullable|string|max:100',
            'website_otp_required'      => 'nullable|in:0,1',
            'website_store_name'        => 'nullable|string|max:150',
            'website_support_phone'     => 'nullable|string|max:20',
            'website_support_email'     => 'nullable|email|max:150',
            'website_free_delivery_min' => 'nullable|numeric|min:0',
            'website_shipping_fee'      => 'nullable|numeric|min:0',
            'website_discount_percent'  => 'nullable|numeric|min:0|max:100',
            'website_announcement'      => 'nullable|string|max:255',
            'website_template'          => 'nullable|string|max:50',
            'website_primary_color'     => 'nullable|string|max:20',
            'website_accent_color'      => 'nullable|string|max:20',
            'website_font_family'       => 'nullable|string|max:50',
            'website_hero_title'        => 'nullable|string|max:255',
            'website_hero_subtitle'     => 'nullable|string|max:500',
            'website_hero_badge'        => 'nullable|string|max:100',
            'website_card_style'        => 'nullable|string|max:50',
            'website_business_type'     => 'nullable|string|max:100',
            'website_positioning'       => 'nullable|string|max:100',
            'website_palette_name'      => 'nullable|string|max:50',
            'website_layout_style'      => 'nullable|string|max:50',
            'website_border_radius'     => 'nullable|string|max:20',
            'website_header_style'      => 'nullable|string|max:50',
            'website_shadow_style'      => 'nullable|string|max:50',
        ]);

        foreach ($validated as $key => $value) {
            Setting::set($key, (string) ($value ?? ''), 'website');
        }

        // Checkbox handling for boolean flags (1 if checked, 0 if unchecked)
        $isOtpRequired = $request->input('website_otp_required') === '1' || $request->input('website_otp_required') === 1;
        Setting::set('website_otp_required', $isOtpRequired ? '1' : '0', 'website');

        return redirect()->route('tenant.website-settings.index')
            ->with('success', 'Website & MSG91 OTP settings updated successfully!');
    }
}
