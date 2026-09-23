import { lazy } from 'react';

// Lazy-loaded theme components for code splitting & fast initial load
const Theme01 = lazy(() => import('./Theme01_ModernMinimal/index.jsx'));
const Theme02 = lazy(() => import('./Theme02_PremiumDark/index.jsx'));
const Theme03 = lazy(() => import('./Theme03_BoldCommerce/index.jsx'));
const ThemePlaceholder = lazy(() => import('./ThemePlaceholder/index.jsx'));

export const THEMES_REGISTRY = [
  {
    id: 'modern_minimal',
    name: 'Modern Minimal',
    description: 'Clean white editorial design with large typography and minimal decoration.',
    status: 'active',
    category: 'Minimalist',
    component: Theme01,
  },
  {
    id: 'premium_dark',
    name: 'Premium Dark',
    description: 'Luxury dark theme with gold accents and glassmorphism cards.',
    status: 'active',
    category: 'Luxury',
    component: Theme02,
  },
  {
    id: 'bold_commerce',
    name: 'Bold Commerce',
    description: 'High-energy colorful storefront with dense product grid.',
    status: 'active',
    category: 'Retail & Electronics',
    component: Theme03,
  },
  {
    id: 'clean_business',
    name: 'Clean Business',
    description: 'Professional corporate design with structured layout.',
    status: 'active',
    category: 'Corporate',
    component: ThemePlaceholder,
  },
  {
    id: 'creative_commerce',
    name: 'Creative Commerce',
    description: 'Artistic asymmetric layout for creative brands.',
    status: 'active',
    category: 'Creative & Art',
    component: ThemePlaceholder,
  },
  {
    id: 'luxury_store',
    name: 'Luxury Store',
    description: 'Ultra-premium full-screen design for jewellery and luxury goods.',
    status: 'active',
    category: 'Luxury',
    component: ThemePlaceholder,
  },
  {
    id: 'electronics_store',
    name: 'Electronics Store',
    description: 'Spec-driven layout with comparison tables.',
    status: 'active',
    category: 'Electronics',
    component: ThemePlaceholder,
  },
  {
    id: 'fashion_store',
    name: 'Fashion Store',
    description: 'Editorial fashion catalog with lookbook-style hero.',
    status: 'active',
    category: 'Fashion',
    component: ThemePlaceholder,
  },
  {
    id: 'manufacturing',
    name: 'Manufacturing Products',
    description: 'Industrial product catalog for B2B buyers.',
    status: 'active',
    category: 'B2B & Industrial',
    component: ThemePlaceholder,
  },
  {
    id: 'industrial',
    name: 'Industrial Business',
    description: 'Heavy-duty design for machinery and tools.',
    status: 'active',
    category: 'Industrial',
    component: ThemePlaceholder,
  },
  {
    id: 'corporate',
    name: 'Corporate Website',
    description: 'Multi-section corporate landing page.',
    status: 'active',
    category: 'Corporate',
    component: ThemePlaceholder,
  },
  {
    id: 'bold_colorful',
    name: 'Bold Colorful',
    description: 'Maximum color energy with gradient backgrounds.',
    status: 'active',
    category: 'Creative',
    component: ThemePlaceholder,
  },
  {
    id: 'glassmorphism',
    name: 'Glassmorphism',
    description: 'Frosted glass cards and translucent UI elements.',
    status: 'active',
    category: 'Modern Tech',
    component: ThemePlaceholder,
  },
  {
    id: 'classic_commerce',
    name: 'Classic Commerce',
    description: 'Traditional e-commerce layout familiar to shoppers.',
    status: 'active',
    category: 'Classic',
    component: ThemePlaceholder,
  },
  {
    id: 'modern_grid',
    name: 'Modern Grid',
    description: 'Masonry grid layout with dynamic product sizing.',
    status: 'active',
    category: 'Editorial',
    component: ThemePlaceholder,
  },
  {
    id: 'product_focused',
    name: 'Product-Focused',
    description: 'Single-product hero with detailed feature sections.',
    status: 'active',
    category: 'Single Product',
    component: ThemePlaceholder,
  },
  {
    id: 'pro_catalog',
    name: 'Professional Catalog',
    description: 'Dense catalog optimized for large inventories.',
    status: 'active',
    category: 'Catalog',
    component: ThemePlaceholder,
  },
  {
    id: 'elegant_white',
    name: 'Elegant White',
    description: 'Minimalist all-white design with serif typography.',
    status: 'active',
    category: 'Minimalist',
    component: ThemePlaceholder,
  },
  {
    id: 'dark_industrial',
    name: 'Dark Industrial',
    description: 'Raw dark aesthetic with heavy typography.',
    status: 'active',
    category: 'Dark',
    component: ThemePlaceholder,
  },
  {
    id: 'modern_landing',
    name: 'Modern Landing Page',
    description: 'Conversion-optimized landing with animated hero.',
    status: 'active',
    category: 'Landing Page',
    component: ThemePlaceholder,
  },
];

/**
 * Get theme entry by ID. Falls back to Modern Minimal if not found.
 */
export function getTheme(themeId) {
  const match = THEMES_REGISTRY.find((t) => t.id === themeId);
  return match || THEMES_REGISTRY[0];
}
