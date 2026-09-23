<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class WebsiteTemplatesController extends Controller
{
    /**
     * Template gallery — list all 20 templates with active status.
     */
    public function index()
    {
        $activeTemplate = Setting::get('website_template', 'modern_minimal');
        $draftTemplate  = Setting::get('website_template_draft', $activeTemplate);

        $templates = $this->templateGallery();

        // Current customization settings
        $settings = $this->currentSettings();

        return view('tenant.website_settings.templates', compact(
            'templates', 'activeTemplate', 'draftTemplate', 'settings'
        ));
    }

    /**
     * Set the active template (publish immediately).
     */
    public function setActive(Request $request)
    {
        $request->validate([
            'template' => 'required|string|max:60',
        ]);

        $templateId = $request->template;

        Setting::set('website_template', $templateId, 'website');
        Setting::set('website_template_draft', $templateId, 'website');
        Setting::set('website_published_at', now()->toIso8601String(), 'website');

        // Apply template default design settings
        $defaults = $this->templateDefaults($templateId);
        foreach ($defaults as $key => $value) {
            Setting::set($key, $value, 'website');
        }

        if ($request->wantsJson() || $request->ajax() || $request->header('Accept') === 'application/json') {
            return response()->json([
                'success'  => true,
                'message'  => 'Template "' . $templateId . '" is now live on your storefront!',
                'template' => $templateId,
            ]);
        }

        return redirect('/website-templates')
            ->with('success', 'Template "' . $templateId . '" is now live on your storefront!');
    }

    /**
     * Open configurator for a specific template.
     */
    public function configurator(Request $request)
    {
        $activeTemplate = Setting::get('website_template', 'modern_minimal');
        $templateId     = $request->query('template', $activeTemplate);
        $settings       = $this->currentSettings();
        $templates      = $this->templateGallery();
        $currentTemplate = collect($templates)->firstWhere('id', $templateId) ?? $templates[0];

        $allTemplateDefaults = [];
        foreach ($templates as $t) {
            $allTemplateDefaults[$t['id']] = $this->templateDefaults($t['id']);
        }

        // If configuring a template other than active, apply its signature template defaults
        if ($templateId !== $activeTemplate && isset($allTemplateDefaults[$templateId])) {
            foreach ($allTemplateDefaults[$templateId] as $k => $v) {
                $cleanKey = str_replace('website_', '', $k);
                $settings[$cleanKey] = $v;
            }
        }
        $settings['template'] = $templateId;

        return view('tenant.website_settings.configurator', compact(
            'templateId', 'activeTemplate', 'settings', 'currentTemplate', 'templates', 'allTemplateDefaults'
        ));
    }

    /**
     * Save draft settings (does NOT publish live).
     */
    public function saveDraft(Request $request)
    {
        $allowed = [
            'website_template_draft', 'website_logo', 'website_hero_image',
            'website_primary_color', 'website_accent_color',
            'website_text_color',
            'website_font_family', 'website_hero_title', 'website_hero_subtitle',
            'website_hero_badge', 'website_hero_cta_text', 'website_store_name',
            'website_products_title', 'website_products_subtitle',
            'website_promo_badge', 'website_promo_title', 'website_promo_desc', 'website_promo_code',
            'website_announcement', 'website_support_phone', 'website_support_email',
            'website_free_delivery_min', 'website_shipping_fee', 'website_card_style',
            'website_layout_style', 'website_border_radius', 'website_header_style',
            'website_shadow_style', 'website_secondary_color', 'website_bg_color',
            'website_footer_text', 'website_social_instagram', 'website_social_facebook',
            'website_social_whatsapp', 'website_seo_title', 'website_seo_description',
            'website_otp_required', 'website_business_type', 'website_positioning',
            'website_sections_order', 'website_sections_visibility',
        ];

        foreach ($request->all() as $key => $value) {
            if (str_starts_with($key, 'website_') && !is_array($value)) {
                Setting::set($key, (string) ($value ?? ''), 'website');
            }
        }

        return response()->json(['success' => true, 'message' => 'Draft saved successfully.']);
    }

    /**
     * Publish draft settings — promote draft → live.
     */
    public function publish(Request $request)
    {
        $request->validate(['template' => 'nullable|string|max:60']);

        // If a draft template was set, make it live
        $draftTemplate = $request->template
            ?? Setting::get('website_template_draft', Setting::get('website_template', 'modern_minimal'));

        Setting::set('website_template', $draftTemplate, 'website');
        Setting::set('website_template_draft', $draftTemplate, 'website');
        Setting::set('website_published_at', now()->toIso8601String(), 'website');

        // Copy draft config to live
        $draftJson = Setting::get('website_config_json_draft', '');
        if ($draftJson) {
            Setting::set('website_config_json', $draftJson, 'website');
        }

        return response()->json([
            'success'  => true,
            'message'  => 'Template "' . $draftTemplate . '" has been published and set live!',
            'template' => $draftTemplate,
        ]);
    }

    /**
     * Reset settings for a template to its defaults.
     */
    public function reset(Request $request)
    {
        $templateId = $request->input('template', 'modern_minimal');
        $defaults   = $this->templateDefaults($templateId);

        foreach ($defaults as $key => $value) {
            Setting::set($key, $value, 'website');
        }

        return response()->json([
            'success'  => true,
            'message'  => 'Template reset to default settings.',
            'defaults' => $defaults,
        ]);
    }

    /**
     * Upload an image (store logo, hero banner, or section photo).
     */
    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:5120',
            'type'  => 'nullable|string|in:logo,hero_image,section_image',
        ]);

        $path = $request->file('image')->store('website', 'public');
        $url  = tenant_asset($path);

        $type = $request->input('type', 'general');
        if ($type === 'logo') {
            Setting::set('website_logo', $url, 'website');
        } elseif ($type === 'hero_image') {
            Setting::set('website_hero_image', $url, 'website');
        }

        return response()->json([
            'success' => true,
            'url'     => $url,
            'path'    => $path,
            'message' => 'Image uploaded successfully!',
        ]);
    }

    // ── Private Helpers ────────────────────────────────────────────

    private function currentSettings(): array
    {
        $defaultLogo = '';
        try {
            $bLogo = Setting::get('business_logo');
            if ($bLogo) {
                $defaultLogo = str_starts_with($bLogo, 'http') ? $bLogo : tenant_asset($bLogo);
            }
        } catch (\Throwable $e) {}

        return [
            'store_name'        => Setting::get('website_store_name', 'Square Store'),
            'logo'              => Setting::get('website_logo', $defaultLogo),
            'hero_image'        => Setting::get('website_hero_image', ''),
            'template'          => Setting::get('website_template', 'modern_minimal'),
            'template_draft'    => Setting::get('website_template_draft', 'modern_minimal'),
            'primary_color'     => Setting::get('website_primary_color', '#10b981'),
            'accent_color'      => Setting::get('website_accent_color', '#059669'),
            'text_color'        => Setting::get('website_text_color', '#0f172a'),
            'secondary_color'   => Setting::get('website_secondary_color', '#f1f5f9'),
            'bg_color'          => Setting::get('website_bg_color', '#ffffff'),
            'font_family'       => Setting::get('website_font_family', 'Inter'),
            'border_radius'     => Setting::get('website_border_radius', '16px'),
            'hero_title'        => Setting::get('website_hero_title', 'Seamless Shopping. Instant ERP Fulfillment.'),
            'hero_subtitle'     => Setting::get('website_hero_subtitle', 'Browse authentic products with live warehouse stock sync.'),
            'hero_badge'        => Setting::get('website_hero_badge', 'Official ERP Connected Store'),
            'hero_cta_text'     => Setting::get('website_hero_cta_text', 'Explore Catalog'),
            'products_title'    => Setting::get('website_products_title', 'All Products'),
            'products_subtitle' => Setting::get('website_products_subtitle', ''),
            'promo_badge'       => Setting::get('website_promo_badge', 'Limited Time Offer'),
            'promo_title'       => Setting::get('website_promo_title', 'Mega Festive Sale — Extra 20% Off Storewide!'),
            'promo_desc'        => Setting::get('website_promo_desc', 'Stock fulfilled live from certified warehouse with automated GST billing and priority dispatch.'),
            'promo_code'        => Setting::get('website_promo_code', 'CODE: FESTIVE20'),
            'announcement'      => Setting::get('website_announcement', '🎉 FREE Delivery on orders above ₹999!'),
            'support_phone'     => Setting::get('website_support_phone', ''),
            'support_email'     => Setting::get('website_support_email', ''),
            'free_delivery_min' => Setting::get('website_free_delivery_min', '999'),
            'shipping_fee'      => Setting::get('website_shipping_fee', '99'),
            'header_style'      => Setting::get('website_header_style', 'regular'),
            'layout_style'      => Setting::get('website_layout_style', 'split'),
            'card_style'        => Setting::get('website_card_style', 'modern'),
            'shadow_style'      => Setting::get('website_shadow_style', 'small'),
            'footer_text'       => Setting::get('website_footer_text', ''),
            'social_instagram'  => Setting::get('website_social_instagram', ''),
            'social_facebook'   => Setting::get('website_social_facebook', ''),
            'social_whatsapp'   => Setting::get('website_social_whatsapp', ''),
            'seo_title'         => Setting::get('website_seo_title', ''),
            'seo_description'   => Setting::get('website_seo_description', ''),
            'business_type'     => Setting::get('website_business_type', 'General Store'),
            'published_at'      => Setting::get('website_published_at', null),
            'otp_required'      => Setting::get('website_otp_required', '1') === '1',
            'sections_order'    => Setting::get('website_sections_order', json_encode(['announcement', 'header', 'hero', 'categories', 'products', 'features', 'footer'])),
            'sections_visibility' => Setting::get('website_sections_visibility', json_encode(['announcement' => true, 'header' => true, 'hero' => true, 'categories' => true, 'products' => true, 'features' => true, 'footer' => true])),
        ];

        try {
            $allWebsite = Setting::where('key', 'like', 'website_%')->get();
            foreach ($allWebsite as $row) {
                $cleanKey = substr($row->key, 8);
                if (!isset($settings[$cleanKey])) {
                    $settings[$cleanKey] = $row->value;
                }
            }
        } catch (\Throwable $e) {}

        return $settings;
    }

    private function templateDefaults(string $templateId): array
    {
        $map = [
            'modern_minimal'     => ['website_primary_color' => '#10b981', 'website_accent_color' => '#059669', 'website_text_color' => '#0f172a', 'website_font_family' => 'Inter', 'website_border_radius' => '16px'],
            'premium_dark'       => ['website_primary_color' => '#f59e0b', 'website_accent_color' => '#d97706', 'website_text_color' => '#f8fafc', 'website_font_family' => 'Plus Jakarta Sans', 'website_border_radius' => '20px'],
            'bold_commerce'      => ['website_primary_color' => '#6366f1', 'website_accent_color' => '#4f46e5', 'website_text_color' => '#0f172a', 'website_font_family' => 'Outfit', 'website_border_radius' => '12px'],
            'clean_business'     => ['website_primary_color' => '#0ea5e9', 'website_accent_color' => '#0284c7', 'website_text_color' => '#0f172a', 'website_font_family' => 'Inter', 'website_border_radius' => '10px'],
            'creative_commerce'  => ['website_primary_color' => '#ec4899', 'website_accent_color' => '#db2777', 'website_text_color' => '#1e1b4b', 'website_font_family' => 'Outfit', 'website_border_radius' => '24px'],
            'luxury_store'       => ['website_primary_color' => '#d97706', 'website_accent_color' => '#b45309', 'website_text_color' => '#fdfbf7', 'website_font_family' => 'Playfair Display', 'website_border_radius' => '12px'],
            'electronics_store'  => ['website_primary_color' => '#1e40af', 'website_accent_color' => '#2563eb', 'website_text_color' => '#0b1329', 'website_font_family' => 'Inter', 'website_border_radius' => '12px'],
            'fashion_store'      => ['website_primary_color' => '#be185d', 'website_accent_color' => '#9d174d', 'website_text_color' => '#27272a', 'website_font_family' => 'Playfair Display', 'website_border_radius' => '16px'],
            'manufacturing'      => ['website_primary_color' => '#475569', 'website_accent_color' => '#334155', 'website_text_color' => '#1e293b', 'website_font_family' => 'Inter', 'website_border_radius' => '8px'],
            'industrial'         => ['website_primary_color' => '#ea580c', 'website_accent_color' => '#c2410c', 'website_text_color' => '#f9fafb', 'website_font_family' => 'Inter', 'website_border_radius' => '6px'],
            'corporate'          => ['website_primary_color' => '#1d4ed8', 'website_accent_color' => '#1e40af', 'website_text_color' => '#0f172a', 'website_font_family' => 'Inter', 'website_border_radius' => '12px'],
            'bold_colorful'      => ['website_primary_color' => '#7c3aed', 'website_accent_color' => '#6d28d9', 'website_text_color' => '#1e1b4b', 'website_font_family' => 'Outfit', 'website_border_radius' => '20px'],
            'glassmorphism'      => ['website_primary_color' => '#38bdf8', 'website_accent_color' => '#0284c7', 'website_text_color' => '#f0f6fc', 'website_font_family' => 'Plus Jakarta Sans', 'website_border_radius' => '24px'],
            'classic_commerce'   => ['website_primary_color' => '#16a34a', 'website_accent_color' => '#15803d', 'website_text_color' => '#0f172a', 'website_font_family' => 'Inter', 'website_border_radius' => '10px'],
            'modern_grid'        => ['website_primary_color' => '#d97706', 'website_accent_color' => '#b45309', 'website_text_color' => '#1c1917', 'website_font_family' => 'Outfit', 'website_border_radius' => '16px'],
            'product_focused'    => ['website_primary_color' => '#0891b2', 'website_accent_color' => '#0e7490', 'website_text_color' => '#083344', 'website_font_family' => 'Plus Jakarta Sans', 'website_border_radius' => '18px'],
            'pro_catalog'        => ['website_primary_color' => '#4f46e5', 'website_accent_color' => '#4338ca', 'website_text_color' => '#0f172a', 'website_font_family' => 'Inter', 'website_border_radius' => '8px'],
            'elegant_white'      => ['website_primary_color' => '#a16207', 'website_accent_color' => '#854d0e', 'website_text_color' => '#171717', 'website_font_family' => 'Playfair Display', 'website_border_radius' => '8px'],
            'dark_industrial'    => ['website_primary_color' => '#dc2626', 'website_accent_color' => '#b91c1c', 'website_text_color' => '#fafafa', 'website_font_family' => 'Inter', 'website_border_radius' => '6px'],
            'modern_landing'     => ['website_primary_color' => '#059669', 'website_accent_color' => '#047857', 'website_text_color' => '#064e3b', 'website_font_family' => 'Plus Jakarta Sans', 'website_border_radius' => '18px'],
        ];
        return $map[$templateId] ?? $map['modern_minimal'];
    }

    private function templateGallery(): array
    {
        return [
            ['id' => 'modern_minimal',    'name' => 'Modern Minimal',        'description' => 'Clean white editorial design with large typography and minimal decoration.', 'status' => 'active', 'preview_color' => '#10b981', 'preview_bg' => '#f8fafc', 'tags' => ['minimal','editorial']],
            ['id' => 'premium_dark',      'name' => 'Premium Dark',           'description' => 'Luxury dark theme with gold accents and glassmorphism cards.', 'status' => 'active', 'preview_color' => '#f59e0b', 'preview_bg' => '#0f172a', 'tags' => ['dark','luxury','gold']],
            ['id' => 'bold_commerce',     'name' => 'Bold Commerce',          'description' => 'High-energy colorful storefront with dense product grid.', 'status' => 'active', 'preview_color' => '#6366f1', 'preview_bg' => '#f4f6fc', 'tags' => ['bold','colorful']],
            ['id' => 'clean_business',    'name' => 'Clean Business',         'description' => 'Professional corporate design with structured layout.', 'status' => 'active', 'preview_color' => '#0ea5e9', 'preview_bg' => '#f0f9ff', 'tags' => ['corporate']],
            ['id' => 'creative_commerce', 'name' => 'Creative Commerce',      'description' => 'Artistic asymmetric layout for creative brands.', 'status' => 'active', 'preview_color' => '#ec4899', 'preview_bg' => '#fdf2f8', 'tags' => ['creative','art']],
            ['id' => 'luxury_store',      'name' => 'Luxury Store',           'description' => 'Ultra-premium full-screen design for jewellery and luxury goods.', 'status' => 'active', 'preview_color' => '#d97706', 'preview_bg' => '#1c1410', 'tags' => ['luxury']],
            ['id' => 'electronics_store', 'name' => 'Electronics Store',      'description' => 'Spec-driven layout with comparison tables.', 'status' => 'active', 'preview_color' => '#1e40af', 'preview_bg' => '#eff6ff', 'tags' => ['electronics']],
            ['id' => 'fashion_store',     'name' => 'Fashion Store',          'description' => 'Editorial fashion catalog with lookbook-style hero.', 'status' => 'active', 'preview_color' => '#be185d', 'preview_bg' => '#fdf2f8', 'tags' => ['fashion']],
            ['id' => 'manufacturing',     'name' => 'Manufacturing Products',  'description' => 'Industrial product catalog for B2B buyers.', 'status' => 'active', 'preview_color' => '#475569', 'preview_bg' => '#fafaf9', 'tags' => ['manufacturing','b2b']],
            ['id' => 'industrial',        'name' => 'Industrial Business',    'description' => 'Heavy-duty design for machinery and tools.', 'status' => 'active', 'preview_color' => '#ea580c', 'preview_bg' => '#111827', 'tags' => ['industrial']],
            ['id' => 'corporate',         'name' => 'Corporate Website',      'description' => 'Multi-section corporate landing page.', 'status' => 'active', 'preview_color' => '#1d4ed8', 'preview_bg' => '#f8fafc', 'tags' => ['corporate']],
            ['id' => 'bold_colorful',     'name' => 'Bold Colorful',          'description' => 'Maximum color energy with gradient backgrounds.', 'status' => 'active', 'preview_color' => '#7c3aed', 'preview_bg' => '#faf5ff', 'tags' => ['colorful']],
            ['id' => 'glassmorphism',     'name' => 'Glassmorphism',          'description' => 'Frosted glass cards and translucent UI elements.', 'status' => 'active', 'preview_color' => '#38bdf8', 'preview_bg' => '#1e3a5f', 'tags' => ['glass','modern']],
            ['id' => 'classic_commerce',  'name' => 'Classic Commerce',       'description' => 'Traditional e-commerce layout familiar to shoppers.', 'status' => 'active', 'preview_color' => '#16a34a', 'preview_bg' => '#f0fdf4', 'tags' => ['classic']],
            ['id' => 'modern_grid',       'name' => 'Modern Grid',            'description' => 'Masonry grid layout with dynamic product sizing.', 'status' => 'active', 'preview_color' => '#d97706', 'preview_bg' => '#fffbeb', 'tags' => ['grid','masonry']],
            ['id' => 'product_focused',   'name' => 'Product-Focused',        'description' => 'Single-product hero with detailed feature sections.', 'status' => 'active', 'preview_color' => '#0891b2', 'preview_bg' => '#ecfeff', 'tags' => ['product']],
            ['id' => 'pro_catalog',       'name' => 'Professional Catalog',   'description' => 'Dense catalog optimized for large inventories.', 'status' => 'active', 'preview_color' => '#4f46e5', 'preview_bg' => '#eef2ff', 'tags' => ['catalog']],
            ['id' => 'elegant_white',     'name' => 'Elegant White',          'description' => 'Minimalist all-white design with serif typography.', 'status' => 'active', 'preview_color' => '#a16207', 'preview_bg' => '#fafafa', 'tags' => ['elegant','serif']],
            ['id' => 'dark_industrial',   'name' => 'Dark Industrial',        'description' => 'Raw dark aesthetic with heavy typography.', 'status' => 'active', 'preview_color' => '#dc2626', 'preview_bg' => '#1c1917', 'tags' => ['dark','industrial']],
            ['id' => 'modern_landing',    'name' => 'Modern Landing Page',    'description' => 'Conversion-optimized landing with animated hero.', 'status' => 'active', 'preview_color' => '#059669', 'preview_bg' => '#f0fdf4', 'tags' => ['landing']],
        ];
    }
}
