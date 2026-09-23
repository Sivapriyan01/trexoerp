/**
 * Theme 02 — Premium Dark
 * Dark slate background, gold/amber accents, glassmorphism product cards.
 * Luxury feel for high-end fashion, jewellery, or premium brands.
 * Supports: Drag-and-drop section reordering & Direct inline text editing!
 */
import React, { useState, useEffect } from 'react';
import {
  ShoppingBag, Search, Star, ArrowRight, X, Truck,
  ShieldCheck, Phone, Mail, ChevronRight, Package, Zap, Crown
} from 'lucide-react';
import { erpApi } from '../../services/erpApi';
import { useCart } from '../common/useCart';
import CartDrawer from '../common/CartDrawer';
import CheckoutModal from '../common/CheckoutModal';
import OrderTracker from '../common/OrderTracker';
import { EditableText } from '../common/EditableText';
import { SectionWrapper } from '../common/SectionWrapper';

export default function Theme02_PremiumDark({
  settings,
  updateField,
  reorderSections,
  toggleSectionVisibility,
  isEditor = false,
}) {
  const primary = settings?.primary_color || '#f59e0b'; // Gold default for dark theme
  const accent  = settings?.accent_color  || '#d97706';
  const font    = settings?.font_family   || 'Plus Jakarta Sans';

  const [products, setProducts]               = useState([]);
  const [categories, setCategories]           = useState(['All']);
  const [selectedCategory, setSelectedCategory] = useState('All');
  const [searchQuery, setSearchQuery]         = useState('');
  const [selectedProduct, setSelectedProduct] = useState(null);
  const [isCheckoutOpen, setIsCheckoutOpen]   = useState(false);
  const [isTrackerOpen, setIsTrackerOpen]     = useState(false);
  const [loading, setLoading]                 = useState(true);

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

  const storeName = settings?.store_name || 'Square Store';
  const heroTitle = settings?.hero_title || 'Luxury. Redefined.';
  const heroSub   = settings?.hero_subtitle || 'Exclusive products, curated for discerning tastes.';
  const heroBadge = settings?.hero_badge || 'Premium Collection';
  const heroCta   = settings?.hero_cta_text || 'Explore Collection';
  const announcement = settings?.announcement || '';

  // Dark palette
  const bg     = '#0a0f1e';
  const surface= '#111827';
  const card   = '#1a2235';
  const border = '#1e293b';
  const textPrimary = settings?.text_color || '#f1f5f9';
  const textSec = '#64748b';

  // Sections management
  const defaultOrder = ['announcement', 'header', 'hero', 'categories', 'products', 'features', 'footer'];
  const sectionsOrder = Array.isArray(settings?.sections_order)
    ? settings.sections_order
    : defaultOrder;
  const sectionsVis = settings?.sections_visibility || {};

  const sectionTitles = {
    announcement: '📢 Announcement Bar',
    header: '🧭 Navigation Header',
    hero: '🦸 Hero Showcase',
    categories: '🏷️ Category Pills',
    products: '🛍️ Products Grid',
    features: '🛡️ Trust & Features',
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
            background: `linear-gradient(90deg, ${primary}, ${accent})`,
            color: '#000', textAlign: 'center', padding: '0.55rem 1rem',
            fontSize: '0.78rem', fontWeight: 700, letterSpacing: '0.05em',
          }}>
            ✦ <EditableText fieldKey="announcement" value={announcement} onChange={updateField} placeholder="Enter announcement…" /> ✦
          </div>
        );

      case 'header':
        return (
          <header style={{
            position: 'sticky', top: 0, zIndex: 100,
            background: `${surface}ee`, backdropFilter: 'blur(20px)',
            borderBottom: `1px solid ${border}`,
          }}>
            <div style={{ maxWidth: '1280px', margin: '0 auto', padding: '0 2rem', display: 'flex', alignItems: 'center', height: '72px', gap: '2rem' }}>
              {/* Logo */}
              <div style={{ display: 'flex', alignItems: 'center', gap: '0.75rem', flex: '0 0 auto' }}>
                <div style={{
                  width: '38px', height: '38px', borderRadius: '10px',
                  background: `linear-gradient(135deg, ${primary}, ${accent})`,
                  display: 'flex', alignItems: 'center', justifyContent: 'center',
                  boxShadow: `0 4px 16px ${primary}40`,
                }}>
                  <Crown size={18} color="#000" />
                </div>
                <EditableText
                  fieldKey="store_name"
                  value={storeName}
                  onChange={updateField}
                  as="span"
                  style={{ fontWeight: 800, fontSize: '1.15rem', color: textPrimary, letterSpacing: '-0.02em' }}
                />
              </div>

              {/* Search */}
              <div style={{ flex: 1, position: 'relative', maxWidth: '480px' }}>
                <Search size={15} style={{ position: 'absolute', left: '1rem', top: '50%', transform: 'translateY(-50%)', color: textSec }} />
                <input
                  type="text"
                  placeholder="Search luxury catalog…"
                  value={searchQuery}
                  onChange={(e) => setSearchQuery(e.target.value)}
                  style={{
                    width: '100%', paddingLeft: '2.5rem', paddingRight: '1rem', height: '42px',
                    border: `1px solid ${border}`, borderRadius: '12px', outline: 'none',
                    fontSize: '0.85rem', background: '#0a0f1e', color: textPrimary,
                    fontFamily: 'inherit', boxSizing: 'border-box',
                  }}
                  onFocus={(e) => (e.target.style.borderColor = primary)}
                  onBlur={(e) => (e.target.style.borderColor = border)}
                />
              </div>

              <div style={{ display: 'flex', gap: '0.5rem', alignItems: 'center', marginLeft: 'auto' }}>
                <button
                  onClick={() => setIsTrackerOpen(true)}
                  style={{ background: 'none', border: 'none', color: textSec, cursor: 'pointer', fontWeight: 600, fontSize: '0.85rem', padding: '0.5rem 0.75rem' }}
                >
                  Track Order
                </button>
                <button
                  onClick={() => cart.setIsCartOpen(true)}
                  style={{
                    position: 'relative', border: 'none', borderRadius: '12px',
                    background: `linear-gradient(135deg, ${primary}, ${accent})`,
                    color: '#000', padding: '0.55rem 1.25rem',
                    fontWeight: 700, fontSize: '0.85rem', cursor: 'pointer',
                    display: 'flex', alignItems: 'center', gap: '0.4rem',
                    fontFamily: 'inherit', boxShadow: `0 4px 16px ${primary}40`,
                  }}
                >
                  <ShoppingBag size={16} /> Cart
                  {cart.cartQty > 0 && (
                    <span style={{ position: 'absolute', top: '-6px', right: '-6px', background: '#ef4444', color: '#fff', borderRadius: '99px', width: '18px', height: '18px', fontSize: '0.62rem', fontWeight: 800, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
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
            position: 'relative', overflow: 'hidden',
            padding: '5.5rem 2rem',
            background: `radial-gradient(ellipse at 70% 50%, ${primary}15 0%, transparent 60%), ${bg}`,
          }}>
            <div style={{ maxWidth: '1280px', margin: '0 auto', position: 'relative', display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '5rem', alignItems: 'center' }}>
              <div>
                <div style={{
                  display: 'inline-flex', alignItems: 'center', gap: '0.5rem',
                  background: `${primary}20`, border: `1px solid ${primary}40`,
                  borderRadius: '99px', padding: '0.35rem 1rem',
                  fontSize: '0.72rem', fontWeight: 700, color: primary,
                  letterSpacing: '0.1em', textTransform: 'uppercase', marginBottom: '1.5rem',
                }}>
                  <Zap size={10} fill={primary} />
                  <EditableText fieldKey="hero_badge" value={heroBadge} onChange={updateField} />
                </div>

                <EditableText
                  fieldKey="hero_title"
                  value={heroTitle}
                  onChange={updateField}
                  as="h1"
                  style={{
                    fontSize: 'clamp(2.5rem, 5vw, 4rem)', fontWeight: 900,
                    lineHeight: 1.1, letterSpacing: '-0.04em', marginBottom: '1.25rem',
                    background: `linear-gradient(135deg, ${textPrimary} 0%, ${primary} 60%)`,
                    WebkitBackgroundClip: 'text', WebkitTextFillColor: 'transparent',
                    backgroundClip: 'text',
                  }}
                />

                <EditableText
                  fieldKey="hero_subtitle"
                  value={heroSub}
                  onChange={updateField}
                  as="p"
                  multiline={true}
                  style={{ fontSize: '1rem', color: textSec, lineHeight: 1.8, marginBottom: '2.5rem', maxWidth: '420px' }}
                />

                <div style={{ display: 'flex', gap: '1rem' }}>
                  <button
                    onClick={() => document.getElementById('pd2-products')?.scrollIntoView({ behavior: 'smooth' })}
                    style={{
                      background: `linear-gradient(135deg, ${primary}, ${accent})`,
                      color: '#000', border: 'none', padding: '1rem 2rem',
                      borderRadius: '14px', fontWeight: 800, fontSize: '0.95rem',
                      cursor: 'pointer', display: 'flex', alignItems: 'center', gap: '0.5rem',
                      fontFamily: 'inherit', boxShadow: `0 8px 32px ${primary}40`,
                      transition: 'transform 0.2s',
                    }}
                  >
                    <EditableText fieldKey="hero_cta_text" value={heroCta} onChange={updateField} />
                    <ArrowRight size={16} />
                  </button>
                  <button
                    onClick={() => setIsTrackerOpen(true)}
                    style={{
                      background: 'transparent', color: textSec, border: `1px solid ${border}`,
                      padding: '1rem 1.5rem', borderRadius: '14px', fontWeight: 600,
                      fontSize: '0.95rem', cursor: 'pointer', fontFamily: 'inherit',
                      display: 'flex', alignItems: 'center', gap: '0.5rem',
                    }}
                  >
                    <Package size={16} /> Track Order
                  </button>
                </div>
              </div>

              {/* Hero product showcase */}
              <div style={{ position: 'relative' }}>
                <div style={{
                  background: `linear-gradient(135deg, ${card} 0%, #0f172a 100%)`,
                  borderRadius: '32px', padding: '1.5rem',
                  border: `1px solid ${border}`,
                  boxShadow: `0 40px 80px rgba(0,0,0,0.4), 0 0 0 1px ${primary}20`,
                }}>
                  <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '0.75rem' }}>
                    {products.slice(0, 4).map((p, idx) => (
                      <div key={p.id} style={{
                        background: '#0a0f1e', borderRadius: '16px', overflow: 'hidden',
                        border: `1px solid ${border}`, cursor: 'pointer',
                        transform: idx % 2 === 1 ? 'translateY(12px)' : 'none',
                        transition: 'transform 0.2s',
                      }}>
                        <img src={p.image} alt={p.product_name}
                          style={{ width: '100%', height: '100px', objectFit: 'cover' }}
                          onError={(e) => { e.target.src = 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=200'; }} />
                        <div style={{ padding: '0.6rem 0.75rem' }}>
                          <div style={{ fontSize: '0.65rem', fontWeight: 700, color: '#e2e8f0', lineHeight: 1.3 }}>{p.product_name?.slice(0, 18)}…</div>
                          <div style={{ fontSize: '0.7rem', fontWeight: 800, color: primary, marginTop: '0.2rem' }}>₹{p.selling_price?.toLocaleString('en-IN')}</div>
                        </div>
                      </div>
                    ))}
                  </div>
                </div>
              </div>
            </div>
          </section>
        );

      case 'categories':
        return (
          <section style={{ padding: '2.5rem 2rem 0', maxWidth: '1280px', margin: '0 auto' }}>
            <div style={{ display: 'flex', gap: '0.5rem', flexWrap: 'wrap' }}>
              {categories.map((cat) => (
                <button
                  key={cat}
                  onClick={() => setSelectedCategory(cat)}
                  style={{
                    padding: '0.5rem 1.25rem', borderRadius: '99px',
                    border: `1px solid ${selectedCategory === cat ? primary : border}`,
                    background: selectedCategory === cat ? `linear-gradient(135deg, ${primary}, ${accent})` : 'transparent',
                    color: selectedCategory === cat ? '#000' : textSec,
                    fontWeight: 700, fontSize: '0.82rem', cursor: 'pointer',
                    transition: 'all 0.2s', fontFamily: 'inherit',
                  }}
                >
                  {cat}
                </button>
              ))}
            </div>
          </section>
        );

      case 'products':
        return (
          <section id="pd2-products" style={{ maxWidth: '1280px', margin: '0 auto', padding: '2rem 2rem 5rem' }}>
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'baseline', marginBottom: '1.5rem' }}>
              <div>
                {selectedCategory === 'All' ? (
                  <EditableText
                    fieldKey="products_title"
                    value={settings?.products_title || 'Catalog Collection'}
                    onChange={updateField}
                    as="h2"
                    style={{ fontSize: '1.6rem', fontWeight: 900, color: textPrimary, letterSpacing: '-0.02em' }}
                  />
                ) : (
                  <h2 style={{ fontSize: '1.6rem', fontWeight: 900, color: textPrimary, letterSpacing: '-0.02em' }}>
                    {selectedCategory}
                  </h2>
                )}
                <EditableText
                  fieldKey="products_subtitle"
                  value={
                    settings?.products_subtitle !== undefined && settings?.products_subtitle !== ''
                      ? settings.products_subtitle
                      : `Exclusive products (${filtered.length})`
                  }
                  onChange={updateField}
                  as="p"
                  style={{ fontSize: '0.82rem', color: textSec }}
                />
              </div>
            </div>

            {loading ? (
              <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(260px, 1fr))', gap: '1.5rem' }}>
                {[1, 2, 3, 4].map((i) => (
                  <div key={i} style={{ height: '360px', borderRadius: '20px', background: card, animation: 'pulse 1.5s infinite' }} />
                ))}
              </div>
            ) : (
              <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(260px, 1fr))', gap: '1.5rem' }}>
                {filtered.map((p) => (
                  <DarkProductCard
                    key={p.id} product={p} primary={primary} accent={accent}
                    card={card} border={border} textPrimary={textPrimary} textSec={textSec}
                    onView={() => setSelectedProduct(p)}
                    onAdd={() => cart.addToCart(p)}
                  />
                ))}
              </div>
            )}
          </section>
        );

      case 'features':
        return (
          <section style={{ background: surface, borderTop: `1px solid ${border}`, borderBottom: `1px solid ${border}`, padding: '3rem 2rem' }}>
            <div style={{ maxWidth: '1280px', margin: '0 auto', display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(240px, 1fr))', gap: '2rem' }}>
              <div style={{ display: 'flex', gap: '1rem', alignItems: 'center' }}>
                <div style={{ width: '48px', height: '48px', borderRadius: '14px', background: `${primary}20`, color: primary, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                  <Truck size={24} />
                </div>
                <div>
                  <h4 style={{ margin: '0 0 0.25rem', fontSize: '0.95rem', fontWeight: 800, color: textPrimary }}>White Glove Dispatch</h4>
                  <p style={{ margin: 0, fontSize: '0.8rem', color: textSec }}>Insured delivery across India</p>
                </div>
              </div>
              <div style={{ display: 'flex', gap: '1rem', alignItems: 'center' }}>
                <div style={{ width: '48px', height: '48px', borderRadius: '14px', background: `${primary}20`, color: primary, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                  <ShieldCheck size={24} />
                </div>
                <div>
                  <h4 style={{ margin: '0 0 0.25rem', fontSize: '0.95rem', fontWeight: 800, color: textPrimary }}>Guaranteed Authenticity</h4>
                  <p style={{ margin: 0, fontSize: '0.8rem', color: textSec }}>Direct ERP verified stock</p>
                </div>
              </div>
              <div style={{ display: 'flex', gap: '1rem', alignItems: 'center' }}>
                <div style={{ width: '48px', height: '48px', borderRadius: '14px', background: `${primary}20`, color: primary, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                  <Crown size={24} />
                </div>
                <div>
                  <h4 style={{ margin: '0 0 0.25rem', fontSize: '0.95rem', fontWeight: 800, color: textPrimary }}>VIP Client Support</h4>
                  <p style={{ margin: 0, fontSize: '0.8rem', color: textSec }}>Dedicated helpline & fast resolutions</p>
                </div>
              </div>
            </div>
          </section>
        );

      case 'footer':
        return (
          <footer style={{ background: '#050a14', borderTop: `1px solid ${border}`, padding: '3rem 2rem 2rem' }}>
            <div style={{ maxWidth: '1280px', margin: '0 auto' }}>
              <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(180px, 1fr))', gap: '2rem', marginBottom: '2rem' }}>
                <div>
                  <div style={{ display: 'flex', alignItems: 'center', gap: '0.6rem', marginBottom: '0.75rem' }}>
                    <div style={{ width: '32px', height: '32px', borderRadius: '8px', background: `linear-gradient(135deg, ${primary}, ${accent})`, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                      <Crown size={16} color="#000" />
                    </div>
                    <span style={{ fontWeight: 800, color: textPrimary }}>{storeName}</span>
                  </div>
                  <p style={{ fontSize: '0.8rem', color: textSec, lineHeight: 1.7 }}>
                    Premium products backed by TrexoERP real-time stock management.
                  </p>
                </div>
                <div>
                  <div style={{ fontWeight: 700, color: textPrimary, marginBottom: '0.75rem', fontSize: '0.85rem' }}>Support</div>
                  <div style={{ display: 'flex', alignItems: 'center', gap: '0.5rem', fontSize: '0.8rem', color: textSec, marginBottom: '0.4rem' }}>
                    <Phone size={12} />
                    <EditableText fieldKey="support_phone" value={settings?.support_phone || '+91 98765 43210'} onChange={updateField} />
                  </div>
                  <div style={{ display: 'flex', alignItems: 'center', gap: '0.5rem', fontSize: '0.8rem', color: textSec, marginBottom: '0.4rem' }}>
                    <Mail size={12} />
                    <EditableText fieldKey="support_email" value={settings?.support_email || 'support@luxury.com'} onChange={updateField} />
                  </div>
                </div>
                <div>
                  <div style={{ fontWeight: 700, color: textPrimary, marginBottom: '0.75rem', fontSize: '0.85rem' }}>Navigation</div>
                  {['Track Order', 'Return Policy', 'Privacy Policy', 'Contact'].map((l) => (
                    <div key={l} style={{ fontSize: '0.8rem', color: textSec, marginBottom: '0.35rem', cursor: 'pointer', display: 'flex', alignItems: 'center', gap: '0.3rem' }}>
                      <ChevronRight size={11} style={{ color: primary }} /> {l}
                    </div>
                  ))}
                </div>
                <div>
                  <div style={{ fontWeight: 700, color: textPrimary, marginBottom: '0.75rem', fontSize: '0.85rem' }}>Secure Payments</div>
                  {['UPI / GPay', 'Credit / Debit Cards', 'Net Banking', 'Cash on Delivery'].map((m) => (
                    <div key={m} style={{ fontSize: '0.8rem', color: textSec, marginBottom: '0.3rem' }}>✓ {m}</div>
                  ))}
                </div>
              </div>
              <div style={{ borderTop: `1px solid ${border}`, paddingTop: '1.25rem', display: 'flex', justifyContent: 'space-between', flexWrap: 'wrap', gap: '0.5rem', fontSize: '0.75rem', color: textSec }}>
                <span>© {new Date().getFullYear()} {storeName}</span>
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
    <div style={{ fontFamily: `'${font}', system-ui, sans-serif`, background: bg, minHeight: '100vh', color: textPrimary }}>
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

      {/* Product Modal */}
      {selectedProduct && (
        <DarkProductModal
          product={selectedProduct} primary={primary} accent={accent}
          card={card} border={border} textPrimary={textPrimary} textSec={textSec}
          onClose={() => setSelectedProduct(null)}
          onAdd={() => { cart.addToCart(selectedProduct); setSelectedProduct(null); }}
        />
      )}

      {/* Shared components */}
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
        @keyframes pulse { 0%,100%{opacity:1}50%{opacity:.4} }
        * { box-sizing: border-box; }
      `}</style>
    </div>
  );
}

// ── Dark Product Card ─────────────────────────────────────────────
function DarkProductCard({ product, primary, accent, card, border, textPrimary, textSec, onView, onAdd }) {
  const [hovered, setHovered] = useState(false);
  const isOOS = product.stock <= 0;

  return (
    <div
      onMouseEnter={() => setHovered(true)}
      onMouseLeave={() => setHovered(false)}
      style={{
        background: card,
        borderRadius: '24px',
        border: `1px solid ${hovered ? primary + '60' : border}`,
        overflow: 'hidden',
        boxShadow: hovered ? `0 20px 40px rgba(0,0,0,0.5), 0 0 20px ${primary}20` : '0 4px 16px rgba(0,0,0,0.2)',
        transform: hovered ? 'translateY(-4px)' : 'none',
        transition: 'all 0.25s ease',
        cursor: 'pointer',
      }}
    >
      <div style={{ position: 'relative', height: '220px', background: '#0a0f1e', overflow: 'hidden' }}>
        <img
          src={product.image} alt={product.product_name} onClick={onView}
          style={{ width: '100%', height: '100%', objectFit: 'cover', transition: 'transform 0.4s', transform: hovered ? 'scale(1.06)' : 'scale(1)' }}
          onError={(e) => { e.target.src = 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=400'; }}
        />
        {product.discount > 0 && (
          <div style={{ position: 'absolute', top: '12px', left: '12px', background: `linear-gradient(135deg, ${primary}, ${accent})`, color: '#000', borderRadius: '8px', padding: '0.2rem 0.6rem', fontSize: '0.72rem', fontWeight: 900 }}>
            -{product.discount}%
          </div>
        )}
      </div>

      <div style={{ padding: '1.25rem' }}>
        <div style={{ fontSize: '0.7rem', color: textSec, fontWeight: 700, textTransform: 'uppercase', letterSpacing: '0.08em', marginBottom: '0.35rem' }}>
          {product.brand} · {product.category}
        </div>
        <div style={{ fontWeight: 700, color: textPrimary, fontSize: '0.95rem', lineHeight: 1.4, marginBottom: '0.75rem', display: '-webkit-box', WebkitLineClamp: 2, WebkitBoxOrient: 'vertical', overflow: 'hidden' }}>
          {product.product_name}
        </div>
        <div style={{ display: 'flex', alignItems: 'baseline', gap: '0.6rem', marginBottom: '1rem' }}>
          <span style={{ fontWeight: 900, fontSize: '1.2rem', color: primary }}>₹{product.selling_price?.toLocaleString('en-IN')}</span>
          {product.mrp > product.selling_price && (
            <span style={{ fontSize: '0.8rem', color: textSec, textDecoration: 'line-through' }}>₹{product.mrp?.toLocaleString('en-IN')}</span>
          )}
        </div>
        <button
          onClick={isOOS ? undefined : onAdd}
          disabled={isOOS}
          style={{
            width: '100%', padding: '0.7rem', border: 'none', borderRadius: '12px',
            background: isOOS ? border : `linear-gradient(135deg, ${primary}, ${accent})`,
            color: isOOS ? textSec : '#000',
            fontWeight: 800, fontSize: '0.85rem', cursor: isOOS ? 'not-allowed' : 'pointer',
            display: 'flex', alignItems: 'center', justifyContent: 'center', gap: '0.4rem',
            fontFamily: 'inherit',
          }}
        >
          <ShoppingBag size={14} />
          {isOOS ? 'Out of Stock' : 'Add to Cart'}
        </button>
      </div>
    </div>
  );
}

function DarkProductModal({ product, primary, accent, card, border, textPrimary, textSec, onClose, onAdd }) {
  return (
    <>
      <div onClick={onClose} style={{ position: 'fixed', inset: 0, background: 'rgba(0,0,0,0.7)', zIndex: 8000, backdropFilter: 'blur(8px)' }} />
      <div style={{
        position: 'fixed', top: '50%', left: '50%', transform: 'translate(-50%, -50%)',
        width: '90%', maxWidth: '680px', maxHeight: '90vh', overflowY: 'auto',
        background: card, borderRadius: '28px', border: `1px solid ${border}`,
        zIndex: 8001, display: 'grid', gridTemplateColumns: '1fr 1fr',
        boxShadow: `0 40px 100px rgba(0,0,0,0.8), 0 0 40px ${primary}20`,
      }}>
        <img
          src={product.image} alt={product.product_name}
          style={{ width: '100%', height: '100%', objectFit: 'cover', borderRadius: '28px 0 0 28px' }}
          onError={(e) => { e.target.src = 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=400'; }}
        />
        <div style={{ padding: '2rem', display: 'flex', flexDirection: 'column', gap: '0.75rem' }}>
          <button onClick={onClose} style={{ alignSelf: 'flex-end', width: '32px', height: '32px', border: 'none', background: border, borderRadius: '8px', cursor: 'pointer', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
            <X size={16} color={textSec} />
          </button>
          <div style={{ fontSize: '0.72rem', color: textSec, fontWeight: 700, textTransform: 'uppercase' }}>{product.brand} · {product.category}</div>
          <h2 style={{ fontWeight: 800, fontSize: '1.2rem', color: textPrimary, lineHeight: 1.3 }}>{product.product_name}</h2>
          <p style={{ fontSize: '0.85rem', color: textSec, lineHeight: 1.6 }}>{product.description}</p>
          <div style={{ display: 'flex', alignItems: 'baseline', gap: '0.75rem' }}>
            <span style={{ fontWeight: 900, fontSize: '1.6rem', color: primary }}>₹{product.selling_price?.toLocaleString('en-IN')}</span>
            {product.mrp > product.selling_price && <span style={{ color: textSec, textDecoration: 'line-through' }}>₹{product.mrp?.toLocaleString('en-IN')}</span>}
          </div>
          <button
            onClick={onAdd}
            disabled={product.stock <= 0}
            style={{
              padding: '0.875rem', border: 'none', borderRadius: '14px', marginTop: 'auto',
              background: product.stock <= 0 ? border : `linear-gradient(135deg, ${primary}, ${accent})`,
              color: product.stock <= 0 ? textSec : '#000',
              fontWeight: 800, fontSize: '0.95rem', cursor: product.stock <= 0 ? 'not-allowed' : 'pointer',
              display: 'flex', alignItems: 'center', justifyContent: 'center', gap: '0.5rem',
            }}
          >
            <ShoppingBag size={16} /> {product.stock > 0 ? 'Add to Cart' : 'Out of Stock'}
          </button>
        </div>
      </div>
    </>
  );
}
