/**
 * Theme 03 — Bold Commerce
 * High-energy, vibrant storefront with dense product grid and strong CTAs.
 * Best for electronics, general retail, supermarkets.
 * Supports: Drag-and-drop section reordering & Direct inline text editing!
 */
import React, { useState, useEffect } from 'react';
import {
  ShoppingBag, Search, Tag, ArrowRight, X, Zap, Flame,
  ShieldCheck, Truck, Package, Phone, Mail, ChevronRight, Grid, List
} from 'lucide-react';
import { erpApi } from '../../services/erpApi';
import { useCart } from '../common/useCart';
import CartDrawer from '../common/CartDrawer';
import CheckoutModal from '../common/CheckoutModal';
import OrderTracker from '../common/OrderTracker';
import { EditableText } from '../common/EditableText';
import { SectionWrapper } from '../common/SectionWrapper';

export default function Theme03_BoldCommerce({
  settings,
  updateField,
  reorderSections,
  toggleSectionVisibility,
  isEditor = false,
}) {
  const primary = settings?.primary_color || '#6366f1';
  const accent  = settings?.accent_color  || '#4f46e5';
  const textColor = settings?.text_color || '#0f172a';
  const font    = settings?.font_family   || 'Outfit';

  const [products, setProducts]               = useState([]);
  const [categories, setCategories]           = useState(['All']);
  const [selectedCategory, setSelectedCategory] = useState('All');
  const [searchQuery, setSearchQuery]         = useState('');
  const [selectedProduct, setSelectedProduct] = useState(null);
  const [isCheckoutOpen, setIsCheckoutOpen]   = useState(false);
  const [isTrackerOpen, setIsTrackerOpen]     = useState(false);
  const [loading, setLoading]                 = useState(true);
  const [viewMode, setViewMode]               = useState('grid'); // 'grid' | 'list'

  const cart = useCart(settings);

  useEffect(() => {
    (async () => {
      const list = await erpApi.getProducts();
      setProducts(list);
      setCategories(['All', ...new Set(list.map((p) => p.category).filter(Boolean))]);
      setLoading(false);
    })();
  }, []);

  const filtered = products.filter((p) => {
    const mc = selectedCategory === 'All' || p.category === selectedCategory;
    const q  = searchQuery.toLowerCase();
    const mq = !q || p.product_name?.toLowerCase().includes(q) || p.brand?.toLowerCase().includes(q);
    return mc && mq;
  });

  const storeName    = settings?.store_name || 'Square Store';
  const heroTitle    = settings?.hero_title || 'Best Deals. Every Day.';
  const heroSub      = settings?.hero_subtitle || 'Thousands of products at unbeatable prices.';
  const heroBadge    = settings?.hero_badge || 'Hot Deals Daily';
  const heroCta      = settings?.hero_cta_text || 'Shop Mega Deals';
  const announcement = settings?.announcement || '';

  const dealProducts = [...products].sort((a, b) => (b.discount || 0) - (a.discount || 0)).slice(0, 4);

  // Sections management
  const defaultOrder = ['announcement', 'header', 'hero', 'categories', 'features', 'products', 'footer'];
  const sectionsOrder = Array.isArray(settings?.sections_order)
    ? settings.sections_order
    : defaultOrder;
  const sectionsVis = settings?.sections_visibility || {};

  const sectionTitles = {
    announcement: '📢 Mega Promo Bar',
    header: '🧭 Navigation Header',
    hero: '🦸 High-Energy Hero',
    categories: '🏷️ Category Pills',
    features: '🛡️ Trust Strip',
    products: '🛍️ Products Grid',
    footer: '🦶 Store Footer',
  };

  const moveSection = (secId, direction) => {
    const idx = sectionsOrder.indexOf(secId);
    if (idx < 0) return;
    const targetIdx = direction === 'up' ? idx - 1 : idx + 1;
    if (targetIdx < 0 || targetIdx >= sectionsOrder.length) return;
    const next = [...sectionsOrder];
    const temp = next[idx];
    next[idx] = next[targetIdx];
    next[targetIdx] = temp;
    if (reorderSections) reorderSections(next);
  };

  const handleDrop = (e, targetSecId) => {
    e.preventDefault();
    const sourceSecId = e.dataTransfer.getData('text/plain');
    if (!sourceSecId || sourceSecId === targetSecId) return;
    const sourceIdx = sectionsOrder.indexOf(sourceSecId);
    const targetIdx = sectionsOrder.indexOf(targetSecId);
    if (sourceIdx < 0 || targetIdx < 0) return;
    const next = [...sectionsOrder];
    const [moved] = next.splice(sourceIdx, 1);
    next.splice(targetIdx, 0, moved);
    if (reorderSections) reorderSections(next);
  };

  const renderSectionContent = (secId) => {
    switch (secId) {
      case 'announcement':
        return (
          <div style={{
            background: `linear-gradient(90deg, ${accent}, ${primary}, ${accent})`,
            backgroundSize: '200% 100%',
            color: '#fff', textAlign: 'center', padding: '0.55rem 1rem',
            fontSize: '0.82rem', fontWeight: 700,
          }}>
            🔥 <EditableText fieldKey="announcement" value={announcement} onChange={updateField} placeholder="Enter promotional announcement…" />
          </div>
        );

      case 'header':
        return (
          <header style={{ background: '#fff', borderBottom: '2px solid #e2e8f0', position: 'sticky', top: 0, zIndex: 100 }}>
            <div style={{ maxWidth: '1400px', margin: '0 auto', padding: '0 1.5rem', display: 'flex', alignItems: 'center', height: '70px', gap: '1.5rem' }}>
              {/* Brand */}
              <div style={{ display: 'flex', alignItems: 'center', gap: '0.5rem', flex: '0 0 auto' }}>
                <div style={{ width: '38px', height: '38px', borderRadius: '10px', background: primary, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                  <Flame size={20} color="#fff" />
                </div>
                <EditableText
                  fieldKey="store_name"
                  value={storeName}
                  onChange={updateField}
                  as="span"
                  style={{ fontWeight: 900, fontSize: '1.25rem', color: '#0f172a', letterSpacing: '-0.03em' }}
                />
              </div>

              {/* Search */}
              <div style={{ flex: 1, maxWidth: '560px', position: 'relative' }}>
                <Search size={16} style={{ position: 'absolute', left: '1rem', top: '50%', transform: 'translateY(-50%)', color: '#94a3b8' }} />
                <input
                  type="text"
                  placeholder="Search products, brands, categories…"
                  value={searchQuery}
                  onChange={(e) => setSearchQuery(e.target.value)}
                  style={{
                    width: '100%', paddingLeft: '2.6rem', paddingRight: '1rem', height: '44px',
                    border: '2px solid #e2e8f0', borderRadius: '12px', outline: 'none',
                    fontSize: '0.9rem', background: '#f8fafc', fontFamily: 'inherit', boxSizing: 'border-box',
                  }}
                  onFocus={(e) => (e.target.style.borderColor = primary)}
                  onBlur={(e) => (e.target.style.borderColor = '#e2e8f0')}
                />
              </div>

              <div style={{ display: 'flex', gap: '0.75rem', alignItems: 'center', marginLeft: 'auto' }}>
                <button
                  onClick={() => setIsTrackerOpen(true)}
                  style={{ background: '#f1f5f9', border: 'none', borderRadius: '10px', padding: '0.55rem 1rem', fontWeight: 700, fontSize: '0.82rem', color: '#475569', cursor: 'pointer' }}
                >
                  Track Order
                </button>
                <button
                  onClick={() => cart.setIsCartOpen(true)}
                  style={{
                    position: 'relative', background: primary, color: '#fff',
                    border: 'none', borderRadius: '12px', padding: '0.6rem 1.25rem',
                    fontWeight: 800, fontSize: '0.88rem', cursor: 'pointer',
                    display: 'flex', alignItems: 'center', gap: '0.5rem',
                  }}
                >
                  <ShoppingBag size={17} /> Cart
                  {cart.cartQty > 0 && (
                    <span style={{ position: 'absolute', top: '-6px', right: '-6px', background: '#ef4444', color: '#fff', borderRadius: '99px', width: '20px', height: '20px', fontSize: '0.7rem', fontWeight: 900, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                      {cart.cartQty}
                    </span>
                  )}
                </button>
              </div>
            </div>
          </header>
        );

      case 'hero':
        return (
          <section style={{
            background: `linear-gradient(135deg, ${primary}12 0%, ${accent}20 100%)`,
            borderBottom: '2px solid #e2e8f0',
            padding: '4.5rem 1.5rem 3.5rem',
          }}>
            <div style={{ maxWidth: '1400px', margin: '0 auto', display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '3.5rem', alignItems: 'center' }}>
              <div>
                <div style={{
                  display: 'inline-flex', alignItems: 'center', gap: '0.4rem',
                  background: '#ef4444', color: '#fff',
                  borderRadius: '99px', padding: '0.35rem 0.9rem',
                  fontSize: '0.75rem', fontWeight: 900, textTransform: 'uppercase',
                  marginBottom: '1.25rem',
                }}>
                  <Flame size={12} fill="#fff" />
                  <EditableText fieldKey="hero_badge" value={heroBadge} onChange={updateField} />
                </div>

                <EditableText
                  fieldKey="hero_title"
                  value={heroTitle}
                  onChange={updateField}
                  as="h1"
                  style={{
                    fontSize: 'clamp(2.5rem, 5vw, 3.8rem)', fontWeight: 900,
                    lineHeight: 1.08, letterSpacing: '-0.03em', color: textColor,
                    marginBottom: '1rem',
                  }}
                />

                <EditableText
                  fieldKey="hero_subtitle"
                  value={heroSub}
                  onChange={updateField}
                  as="p"
                  multiline={true}
                  style={{ fontSize: '1.1rem', color: '#475569', lineHeight: 1.6, marginBottom: '2rem', maxWidth: '440px' }}
                />

                <div style={{ display: 'flex', gap: '1rem', flexWrap: 'wrap' }}>
                  <button
                    onClick={() => document.getElementById('bc3-grid')?.scrollIntoView({ behavior: 'smooth' })}
                    style={{
                      background: primary, color: '#fff', border: 'none',
                      padding: '1rem 2.25rem', borderRadius: '14px',
                      fontWeight: 900, fontSize: '1rem', cursor: 'pointer',
                      display: 'flex', alignItems: 'center', gap: '0.5rem',
                      boxShadow: `0 8px 25px ${primary}50`,
                    }}
                  >
                    <EditableText fieldKey="hero_cta_text" value={heroCta} onChange={updateField} />
                    <ArrowRight size={18} />
                  </button>
                  <button
                    onClick={() => setIsTrackerOpen(true)}
                    style={{
                      background: '#fff', color: '#0f172a', border: '2px solid #e2e8f0',
                      padding: '1rem 1.5rem', borderRadius: '14px', fontWeight: 800,
                      fontSize: '0.95rem', cursor: 'pointer',
                    }}
                  >
                    Track My Order
                  </button>
                </div>
              </div>

              {/* Deals Showcase strip */}
              <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '1rem' }}>
                {dealProducts.map((p) => (
                  <div
                    key={p.id}
                    onClick={() => setSelectedProduct(p)}
                    style={{
                      background: '#fff', borderRadius: '18px', padding: '1rem',
                      border: '2px solid #e2e8f0', cursor: 'pointer',
                      boxShadow: '0 6px 20px rgba(0,0,0,0.06)',
                    }}
                  >
                    <div style={{ position: 'relative', height: '110px', borderRadius: '12px', overflow: 'hidden', marginBottom: '0.6rem' }}>
                      <img src={p.image} alt={p.product_name} style={{ width: '100%', height: '100%', objectFit: 'cover' }}
                        onError={(e) => { e.target.src = 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=200'; }} />
                      {p.discount > 0 && (
                        <div style={{ position: 'absolute', top: '6px', left: '6px', background: '#ef4444', color: '#fff', borderRadius: '6px', padding: '0.15rem 0.45rem', fontSize: '0.7rem', fontWeight: 900 }}>
                          -{p.discount}% OFF
                        </div>
                      )}
                    </div>
                    <div style={{ fontSize: '0.78rem', fontWeight: 800, color: '#0f172a', lineHeight: 1.3, whiteSpace: 'nowrap', overflow: 'hidden', textOverflow: 'ellipsis' }}>
                      {p.product_name}
                    </div>
                    <div style={{ display: 'flex', alignItems: 'baseline', gap: '0.4rem', marginTop: '0.3rem' }}>
                      <span style={{ fontWeight: 900, fontSize: '0.95rem', color: primary }}>₹{p.selling_price?.toLocaleString('en-IN')}</span>
                      {p.mrp > p.selling_price && <span style={{ fontSize: '0.72rem', color: '#94a3b8', textDecoration: 'line-through' }}>₹{p.mrp}</span>}
                    </div>
                  </div>
                ))}
              </div>
            </div>
          </section>
        );

      case 'categories':
        return (
          <section style={{ padding: '1.5rem', maxWidth: '1400px', margin: '0 auto' }}>
            <div style={{ display: 'flex', gap: '0.5rem', flexWrap: 'wrap' }}>
              {categories.map((cat) => (
                <button
                  key={cat}
                  onClick={() => setSelectedCategory(cat)}
                  style={{
                    padding: '0.5rem 1.25rem', borderRadius: '10px',
                    border: `2px solid ${selectedCategory === cat ? primary : '#e2e8f0'}`,
                    background: selectedCategory === cat ? primary : '#fff',
                    color: selectedCategory === cat ? '#fff' : '#475569',
                    fontWeight: 700, fontSize: '0.85rem', cursor: 'pointer',
                  }}
                >
                  {cat}
                </button>
              ))}
            </div>
          </section>
        );

      case 'features':
        return (
          <div style={{ background: '#fff', borderBottom: '1px solid #e2e8f0' }}>
            <div style={{ maxWidth: '1400px', margin: '0 auto', padding: '0.875rem 1.5rem', display: 'flex', gap: '2rem', justifyContent: 'center', flexWrap: 'wrap' }}>
              {[
                { icon: <Truck size={15} />, text: `Free Delivery ₹${settings?.free_delivery_min || 999}+` },
                { icon: <ShieldCheck size={15} />, text: '100% Authentic Products' },
                { icon: <Tag size={15} />, text: 'Lowest Price Guaranteed' },
                { icon: <Package size={15} />, text: 'Easy Returns' },
              ].map(({ icon, text }) => (
                <div key={text} style={{ display: 'flex', alignItems: 'center', gap: '0.4rem', fontSize: '0.82rem', fontWeight: 700, color: '#475569' }}>
                  <span style={{ color: primary }}>{icon}</span> {text}
                </div>
              ))}
            </div>
          </div>
        );

      case 'products':
        return (
          <section id="bc3-grid" style={{ maxWidth: '1400px', margin: '0 auto', padding: '1.5rem' }}>
            <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: '1.25rem', flexWrap: 'wrap', gap: '0.75rem' }}>
              <div>
                {selectedCategory === 'All' ? (
                  <EditableText
                    fieldKey="products_title"
                    value={settings?.products_title || 'All Products'}
                    onChange={updateField}
                    as="h2"
                    style={{ fontWeight: 800, fontSize: '1.3rem', color: textColor }}
                  />
                ) : (
                  <h2 style={{ fontWeight: 800, fontSize: '1.3rem', color: textColor }}>
                    {selectedCategory}
                  </h2>
                )}
                <EditableText
                  fieldKey="products_subtitle"
                  value={
                    settings?.products_subtitle !== undefined && settings?.products_subtitle !== ''
                      ? settings.products_subtitle
                      : `${filtered.length} products found`
                  }
                  onChange={updateField}
                  as="div"
                  style={{ fontSize: '0.8rem', color: '#64748b' }}
                />
              </div>
              <div style={{ display: 'flex', gap: '0.5rem' }}>
                {['grid', 'list'].map((mode) => (
                  <button
                    key={mode}
                    onClick={() => setViewMode(mode)}
                    style={{
                      width: '36px', height: '36px', border: `1px solid ${viewMode === mode ? primary : '#e2e8f0'}`,
                      borderRadius: '8px', background: viewMode === mode ? primary : '#fff',
                      color: viewMode === mode ? '#fff' : '#64748b',
                      cursor: 'pointer', display: 'flex', alignItems: 'center', justifyContent: 'center',
                    }}
                  >
                    {mode === 'grid' ? <Grid size={15} /> : <List size={15} />}
                  </button>
                ))}
              </div>
            </div>

            {loading ? (
              <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(200px, 1fr))', gap: '1rem' }}>
                {[...Array(8)].map((_, i) => (
                  <div key={i} style={{ height: '280px', borderRadius: '16px', background: '#e2e8f0', animation: 'pulse 1.5s infinite' }} />
                ))}
              </div>
            ) : filtered.length === 0 ? (
              <div style={{ textAlign: 'center', padding: '5rem 0', color: '#94a3b8' }}>
                <Search size={48} style={{ margin: '0 auto 1rem', opacity: 0.4 }} />
                <p style={{ fontWeight: 600 }}>No products found</p>
              </div>
            ) : viewMode === 'grid' ? (
              <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(200px, 1fr))', gap: '1rem' }}>
                {filtered.map((p) => (
                  <BoldProductCard
                    key={p.id} product={p} primary={primary} accent={accent}
                    onView={() => setSelectedProduct(p)} onAdd={() => cart.addToCart(p)}
                  />
                ))}
              </div>
            ) : (
              <div style={{ display: 'flex', flexDirection: 'column', gap: '0.75rem' }}>
                {filtered.map((p) => (
                  <BoldListCard
                    key={p.id} product={p} primary={primary} accent={accent}
                    onView={() => setSelectedProduct(p)} onAdd={() => cart.addToCart(p)}
                  />
                ))}
              </div>
            )}
          </section>
        );

      case 'footer':
        return (
          <footer style={{ background: '#1e1e2e', color: '#94a3b8', padding: '3rem 1.5rem 2rem', marginTop: '3rem' }}>
            <div style={{ maxWidth: '1400px', margin: '0 auto' }}>
              <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(180px, 1fr))', gap: '2rem', marginBottom: '2rem' }}>
                <div>
                  <div style={{ display: 'flex', alignItems: 'center', gap: '0.5rem', marginBottom: '0.75rem' }}>
                    <div style={{ width: '30px', height: '30px', borderRadius: '8px', background: primary, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                      <Flame size={16} color="#fff" />
                    </div>
                    <span style={{ fontWeight: 800, color: '#f1f5f9', fontSize: '1rem' }}>{storeName}</span>
                  </div>
                  <p style={{ fontSize: '0.8rem', lineHeight: 1.6 }}>Your one-stop shop for the best deals, powered by TrexoERP.</p>
                </div>
                <div>
                  <div style={{ fontWeight: 700, color: '#f1f5f9', marginBottom: '0.75rem', fontSize: '0.85rem' }}>Help & Support</div>
                  <div style={{ display: 'flex', alignItems: 'center', gap: '0.5rem', fontSize: '0.8rem', marginBottom: '0.4rem' }}>
                    <Phone size={12} />
                    <EditableText fieldKey="support_phone" value={settings?.support_phone || '+91 98765 43210'} onChange={updateField} />
                  </div>
                  <div style={{ display: 'flex', alignItems: 'center', gap: '0.5rem', fontSize: '0.8rem', marginBottom: '0.4rem' }}>
                    <Mail size={12} />
                    <EditableText fieldKey="support_email" value={settings?.support_email || 'support@boldstore.com'} onChange={updateField} />
                  </div>
                </div>
                <div>
                  <div style={{ fontWeight: 700, color: '#f1f5f9', marginBottom: '0.75rem', fontSize: '0.85rem' }}>Information</div>
                  {['Track Order', 'Return Policy', 'Shipping Info', 'Contact Us'].map((l) => (
                    <div key={l} style={{ fontSize: '0.8rem', marginBottom: '0.35rem', cursor: 'pointer', display: 'flex', alignItems: 'center', gap: '0.3rem' }}>
                      <ChevronRight size={11} color={primary} /> {l}
                    </div>
                  ))}
                </div>
                <div>
                  <div style={{ fontWeight: 700, color: '#f1f5f9', marginBottom: '0.75rem', fontSize: '0.85rem' }}>Payment</div>
                  {['UPI / GPay', 'Cards', 'Net Banking', 'Cash on Delivery'].map((m) => (
                    <div key={m} style={{ fontSize: '0.8rem', marginBottom: '0.3rem' }}>✓ {m}</div>
                  ))}
                </div>
              </div>
              <div style={{ borderTop: '1px solid #2d2d3e', paddingTop: '1.25rem', display: 'flex', justifyContent: 'space-between', flexWrap: 'wrap', gap: '0.5rem', fontSize: '0.75rem' }}>
                <span>© {new Date().getFullYear()} {storeName}. All rights reserved.</span>
                <span style={{ color: primary }}>Powered by TrexoERP</span>
              </div>
            </div>
          </footer>
        );

      default:
        return null;
    }
  };

  return (
    <div style={{ fontFamily: `'${font}', system-ui, sans-serif`, background: '#f4f6fc', minHeight: '100vh', color: textColor }}>
      {/* Sections reorderable */}
      {sectionsOrder.map((secId, idx) => (
        <SectionWrapper
          key={secId}
          sectionId={secId}
          title={sectionTitles[secId] || secId}
          index={idx}
          totalSections={sectionsOrder.length}
          visible={sectionsVis[secId] !== false}
          onMoveUp={() => moveSection(secId, 'up')}
          onMoveDown={() => moveSection(secId, 'down')}
          onDrop={handleDrop}
          onToggleVisibility={() => toggleSectionVisibility && toggleSectionVisibility(secId)}
        >
          {renderSectionContent(secId)}
        </SectionWrapper>
      ))}

      {/* Modals */}
      {selectedProduct && (
        <BoldProductModal
          product={selectedProduct} primary={primary} accent={accent}
          onClose={() => setSelectedProduct(null)}
          onAdd={() => { cart.addToCart(selectedProduct); setSelectedProduct(null); }}
        />
      )}
      <CartDrawer {...cart} primaryColor={primary} onCheckout={() => { cart.setIsCartOpen(false); setIsCheckoutOpen(true); }} />
      <CheckoutModal
        isOpen={isCheckoutOpen} onClose={() => setIsCheckoutOpen(false)}
        cart={cart.cart} subtotal={cart.subtotal} gstAmount={cart.gstAmount}
        shippingCharge={cart.shippingCharge} grandTotal={cart.grandTotal}
        settings={settings} primaryColor={primary}
        onOrderPlaced={() => cart.clearCart()}
      />
      <OrderTracker isOpen={isTrackerOpen} onClose={() => setIsTrackerOpen(false)} primaryColor={primary} />

      <style>{`
        @keyframes pulse { 0%,100%{opacity:1}50%{opacity:.5} }
        * { box-sizing: border-box; }
      `}</style>
    </div>
  );
}

function BoldProductCard({ product, primary, accent, onView, onAdd }) {
  const [hov, setHov] = useState(false);
  const isOOS = product.stock <= 0;

  return (
    <div
      onMouseEnter={() => setHov(true)}
      onMouseLeave={() => setHov(false)}
      style={{
        background: '#fff', borderRadius: '16px', overflow: 'hidden',
        border: `2px solid ${hov ? primary : '#e2e8f0'}`,
        transition: 'all 0.2s', transform: hov ? 'translateY(-3px)' : 'none',
        boxShadow: hov ? `0 12px 30px ${primary}25` : '0 2px 8px rgba(0,0,0,0.04)',
        cursor: 'pointer',
      }}
    >
      <div style={{ position: 'relative', height: '170px', background: '#f8fafc', overflow: 'hidden' }}>
        <img
          src={product.image} alt={product.product_name} onClick={onView}
          style={{ width: '100%', height: '100%', objectFit: 'cover', transition: 'transform 0.3s', transform: hov ? 'scale(1.05)' : 'scale(1)' }}
          onError={(e) => { e.target.src = 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=200'; }}
        />
        {product.discount > 0 && (
          <div style={{ position: 'absolute', top: '8px', left: '8px', background: '#ef4444', color: '#fff', borderRadius: '6px', padding: '0.2rem 0.5rem', fontSize: '0.72rem', fontWeight: 900 }}>
            -{product.discount}%
          </div>
        )}
      </div>

      <div style={{ padding: '0.875rem' }}>
        <div style={{ fontSize: '0.7rem', fontWeight: 700, color: '#94a3b8', textTransform: 'uppercase', marginBottom: '0.25rem' }}>
          {product.brand}
        </div>
        <div style={{ fontWeight: 800, color: '#0f172a', fontSize: '0.88rem', lineHeight: 1.3, marginBottom: '0.5rem', display: '-webkit-box', WebkitLineClamp: 2, WebkitBoxOrient: 'vertical', overflow: 'hidden' }}>
          {product.product_name}
        </div>
        <div style={{ display: 'flex', alignItems: 'baseline', gap: '0.4rem', marginBottom: '0.75rem' }}>
          <span style={{ fontWeight: 900, fontSize: '1.1rem', color: primary }}>₹{product.selling_price?.toLocaleString('en-IN')}</span>
          {product.mrp > product.selling_price && <span style={{ fontSize: '0.78rem', color: '#94a3b8', textDecoration: 'line-through' }}>₹{product.mrp}</span>}
        </div>
        <button
          onClick={isOOS ? undefined : onAdd}
          disabled={isOOS}
          style={{
            width: '100%', padding: '0.6rem', border: 'none', borderRadius: '10px',
            background: isOOS ? '#f1f5f9' : primary, color: isOOS ? '#94a3b8' : '#fff',
            fontWeight: 800, fontSize: '0.82rem', cursor: isOOS ? 'not-allowed' : 'pointer',
            display: 'flex', alignItems: 'center', justifyContent: 'center', gap: '0.4rem',
          }}
        >
          <ShoppingBag size={14} /> {isOOS ? 'Sold Out' : 'Add to Cart'}
        </button>
      </div>
    </div>
  );
}

function BoldListCard({ product, primary, accent, onView, onAdd }) {
  const isOOS = product.stock <= 0;
  return (
    <div style={{ background: '#fff', borderRadius: '14px', padding: '0.875rem', border: '1.5px solid #e2e8f0', display: 'flex', gap: '1rem', alignItems: 'center' }}>
      <img src={product.image} alt={product.product_name} onClick={onView}
        style={{ width: '80px', height: '80px', borderRadius: '10px', objectFit: 'cover', flexShrink: 0, cursor: 'pointer' }}
        onError={(e) => { e.target.src = 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=200'; }} />
      <div style={{ flex: 1, minWidth: 0 }}>
        <div style={{ fontSize: '0.7rem', color: '#94a3b8', fontWeight: 700, textTransform: 'uppercase' }}>{product.brand} · {product.category}</div>
        <div style={{ fontWeight: 800, color: '#0f172a', fontSize: '0.9rem', whiteSpace: 'nowrap', overflow: 'hidden', textOverflow: 'ellipsis' }}>{product.product_name}</div>
        <div style={{ display: 'flex', alignItems: 'baseline', gap: '0.5rem', marginTop: '0.25rem' }}>
          <span style={{ fontWeight: 900, color: primary, fontSize: '1rem' }}>₹{product.selling_price?.toLocaleString('en-IN')}</span>
          {product.mrp > product.selling_price && <span style={{ fontSize: '0.75rem', color: '#94a3b8', textDecoration: 'line-through' }}>₹{product.mrp}</span>}
        </div>
      </div>
      <button
        onClick={isOOS ? undefined : onAdd}
        disabled={isOOS}
        style={{
          padding: '0.6rem 1.25rem', borderRadius: '10px', border: 'none',
          background: isOOS ? '#f1f5f9' : primary, color: isOOS ? '#94a3b8' : '#fff',
          fontWeight: 800, fontSize: '0.85rem', cursor: isOOS ? 'not-allowed' : 'pointer', flexShrink: 0,
        }}
      >
        {isOOS ? 'Sold Out' : 'Add to Cart'}
      </button>
    </div>
  );
}

function BoldProductModal({ product, primary, accent, onClose, onAdd }) {
  return (
    <>
      <div onClick={onClose} style={{ position: 'fixed', inset: 0, background: 'rgba(0,0,0,0.6)', zIndex: 8000 }} />
      <div style={{
        position: 'fixed', top: '50%', left: '50%', transform: 'translate(-50%, -50%)',
        width: '90%', maxWidth: '640px', maxHeight: '90vh', overflowY: 'auto',
        background: '#fff', borderRadius: '24px', zIndex: 8001,
        display: 'grid', gridTemplateColumns: '1fr 1fr', boxShadow: '0 30px 80px rgba(0,0,0,0.25)',
      }}>
        <img src={product.image} alt={product.product_name} style={{ width: '100%', height: '100%', objectFit: 'cover', borderRadius: '24px 0 0 24px' }}
          onError={(e) => { e.target.src = 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=400'; }} />
        <div style={{ padding: '2rem', display: 'flex', flexDirection: 'column', gap: '0.75rem' }}>
          <button onClick={onClose} style={{ alignSelf: 'flex-end', width: '32px', height: '32px', border: 'none', background: '#f8fafc', borderRadius: '8px', cursor: 'pointer', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
            <X size={16} />
          </button>
          <div style={{ fontSize: '0.72rem', color: '#94a3b8', fontWeight: 700, textTransform: 'uppercase' }}>{product.brand} · {product.category}</div>
          <h2 style={{ fontWeight: 900, fontSize: '1.25rem', color: '#0f172a', lineHeight: 1.25 }}>{product.product_name}</h2>
          <p style={{ fontSize: '0.85rem', color: '#64748b', lineHeight: 1.6 }}>{product.description}</p>
          <div style={{ display: 'flex', alignItems: 'baseline', gap: '0.5rem' }}>
            <span style={{ fontWeight: 900, fontSize: '1.6rem', color: primary }}>₹{product.selling_price?.toLocaleString('en-IN')}</span>
            {product.mrp > product.selling_price && <span style={{ color: '#94a3b8', textDecoration: 'line-through' }}>₹{product.mrp}</span>}
          </div>
          <button
            onClick={onAdd}
            disabled={product.stock <= 0}
            style={{
              padding: '0.875rem', border: 'none', borderRadius: '12px', marginTop: 'auto',
              background: product.stock <= 0 ? '#f1f5f9' : primary, color: product.stock <= 0 ? '#94a3b8' : '#fff',
              fontWeight: 800, fontSize: '0.95rem', cursor: product.stock <= 0 ? 'not-allowed' : 'pointer',
            }}
          >
            {product.stock > 0 ? 'Add to Cart' : 'Sold Out'}
          </button>
        </div>
      </div>
    </>
  );
}
