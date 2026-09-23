import React from 'react';
import { ShoppingBag, X, Trash2, Plus, Minus, ArrowRight, ShieldCheck } from 'lucide-react';

export default function CartDrawer({
  isCartOpen,
  setIsCartOpen,
  cart = [],
  removeFromCart,
  updateQuantity,
  subtotal = 0,
  gstAmount = 0,
  gstCalcType = 'inclusive',
  shippingCharge = 0,
  grandTotal = 0,
  freeDeliveryMin = 999,
  primaryColor = '#10b981',
  onCheckout,
}) {
  if (!isCartOpen) return null;

  const freeShippingLeft = Math.max(0, freeDeliveryMin - subtotal);
  const freeShippingProgress = Math.min(100, (subtotal / freeDeliveryMin) * 100);

  return (
    <div style={{ position: 'fixed', inset: 0, zIndex: 9000 }}>
      {/* Backdrop */}
      <div
        onClick={() => setIsCartOpen(false)}
        style={{
          position: 'absolute',
          inset: 0,
          background: 'rgba(15, 23, 42, 0.6)',
          backdropFilter: 'blur(4px)',
          transition: 'opacity 0.3s',
        }}
      />

      {/* Drawer */}
      <div
        style={{
          position: 'absolute',
          top: 0,
          right: 0,
          bottom: 0,
          width: '100%',
          maxWidth: '440px',
          background: '#ffffff',
          color: '#0f172a',
          boxShadow: '-10px 0 30px rgba(0,0,0,0.15)',
          display: 'flex',
          flexDirection: 'column',
          zIndex: 9001,
          animation: 'slideInRight 0.3s cubic-bezier(0.16, 1, 0.3, 1)',
        }}
      >
        {/* Header */}
        <div
          style={{
            padding: '1.25rem 1.5rem',
            borderBottom: '1px solid #f1f5f9',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'space-between',
          }}
        >
          <div style={{ display: 'flex', alignItems: 'center', gap: '0.6rem' }}>
            <div
              style={{
                width: '32px',
                height: '32px',
                borderRadius: '8px',
                background: `${primaryColor}15`,
                color: primaryColor,
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
              }}
            >
              <ShoppingBag size={18} />
            </div>
            <h2 style={{ fontSize: '1.1rem', fontWeight: 800, color: '#0f172a', margin: 0 }}>
              Shopping Cart ({cart.reduce((s, i) => s + i.quantity, 0)})
            </h2>
          </div>
          <button
            onClick={() => setIsCartOpen(false)}
            style={{
              width: '36px',
              height: '36px',
              borderRadius: '10px',
              border: 'none',
              background: '#f8fafc',
              cursor: 'pointer',
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center',
              color: '#64748b',
            }}
          >
            <X size={18} />
          </button>
        </div>

        {/* Free Shipping Meter */}
        <div style={{ padding: '0.75rem 1.5rem', background: '#f8fafc', borderBottom: '1px solid #e2e8f0' }}>
          {freeShippingLeft > 0 ? (
            <p style={{ margin: '0 0 0.4rem', fontSize: '0.78rem', color: '#475569', fontWeight: 600 }}>
              Add <b style={{ color: primaryColor }}>₹{freeShippingLeft.toLocaleString('en-IN')}</b> more to unlock <b style={{ color: '#16a34a' }}>FREE Delivery</b>!
            </p>
          ) : (
            <p style={{ margin: '0 0 0.4rem', fontSize: '0.78rem', color: '#16a34a', fontWeight: 700 }}>
              🎉 You unlocked FREE Delivery!
            </p>
          )}
          <div style={{ width: '100%', height: '6px', background: '#e2e8f0', borderRadius: '99px', overflow: 'hidden' }}>
            <div
              style={{
                width: `${freeShippingProgress}%`,
                height: '100%',
                background: primaryColor,
                borderRadius: '99px',
                transition: 'width 0.3s ease',
              }}
            />
          </div>
        </div>

        {/* Cart Items */}
        <div style={{ flex: 1, overflowY: 'auto', padding: '1rem 1.5rem' }}>
          {cart.length === 0 ? (
            <div
              style={{
                height: '100%',
                display: 'flex',
                flexDirection: 'column',
                alignItems: 'center',
                justifyContent: 'center',
                textAlign: 'center',
                color: '#94a3b8',
                gap: '1rem',
              }}
            >
              <div
                style={{
                  width: '64px',
                  height: '64px',
                  borderRadius: '20px',
                  background: '#f1f5f9',
                  display: 'flex',
                  alignItems: 'center',
                  justifyContent: 'center',
                }}
              >
                <ShoppingBag size={28} />
              </div>
              <p style={{ margin: 0, fontWeight: 700, fontSize: '1rem', color: '#475569' }}>
                Your cart is empty
              </p>
              <p style={{ margin: 0, fontSize: '0.85rem' }}>Add items from the store to see them here.</p>
              <button
                onClick={() => setIsCartOpen(false)}
                style={{
                  marginTop: '0.5rem',
                  padding: '0.6rem 1.4rem',
                  borderRadius: '12px',
                  border: 'none',
                  background: primaryColor,
                  color: '#fff',
                  fontWeight: 700,
                  fontSize: '0.85rem',
                  cursor: 'pointer',
                }}
              >
                Start Shopping
              </button>
            </div>
          ) : (
            <div style={{ display: 'flex', flexDirection: 'column', gap: '1rem' }}>
              {cart.map((item) => (
                <div
                  key={item.id}
                  style={{
                    display: 'flex',
                    gap: '1rem',
                    padding: '0.875rem',
                    background: '#f8fafc',
                    borderRadius: '16px',
                    border: '1px solid #f1f5f9',
                  }}
                >
                  <img
                    src={item.image}
                    alt={item.product_name}
                    style={{
                      width: '72px',
                      height: '72px',
                      borderRadius: '12px',
                      objectFit: 'cover',
                      background: '#fff',
                    }}
                    onError={(e) => {
                      e.target.src = 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=200';
                    }}
                  />
                  <div style={{ flex: 1, minWidth: 0 }}>
                    <h4
                      style={{
                        margin: '0 0 0.35rem',
                        fontSize: '0.88rem',
                        fontWeight: 700,
                        color: '#0f172a',
                        lineHeight: 1.3,
                        whiteSpace: 'nowrap',
                        overflow: 'hidden',
                        textOverflow: 'ellipsis',
                      }}
                    >
                      {item.product_name}
                    </h4>
                    <div style={{ display: 'flex', alignItems: 'center', gap: '0.4rem', marginBottom: '0.5rem' }}>
                      <span style={{ fontSize: '0.85rem', fontWeight: 800, color: primaryColor }}>
                        ₹{item.selling_price?.toLocaleString('en-IN')}
                      </span>
                      {Number(item.tax) > 0 && (
                        <span style={{ fontSize: '0.68rem', fontWeight: 700, color: '#64748b', background: '#f1f5f9', padding: '0.1rem 0.35rem', borderRadius: '4px' }}>
                          {gstCalcType === 'inclusive' ? `Incl. ${item.tax}% GST` : `+${item.tax}% GST`}
                        </span>
                      )}
                    </div>

                    <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
                      {/* Qty controller */}
                      <div
                        style={{
                          display: 'inline-flex',
                          alignItems: 'center',
                          gap: '0.35rem',
                          background: '#ffffff',
                          border: '1.5px solid #cbd5e1',
                          borderRadius: '10px',
                          padding: '0.2rem 0.4rem',
                        }}
                      >
                        <button
                          onClick={() => updateQuantity(item.id, item.quantity - 1)}
                          style={{
                            width: '24px',
                            height: '24px',
                            border: 'none',
                            background: '#f1f5f9',
                            borderRadius: '6px',
                            cursor: 'pointer',
                            display: 'flex',
                            alignItems: 'center',
                            justifyContent: 'center',
                            color: '#1e293b',
                            transition: 'all 0.15s ease',
                          }}
                          aria-label="Decrease quantity"
                        >
                          <Minus size={12} strokeWidth={2.5} />
                        </button>
                        <span
                          style={{
                            fontSize: '0.9rem',
                            fontWeight: 800,
                            minWidth: '24px',
                            textAlign: 'center',
                            color: '#0f172a',
                            display: 'inline-block',
                            lineHeight: '1.2',
                            userSelect: 'none',
                          }}
                        >
                          {item.quantity}
                        </span>
                        <button
                          onClick={() => updateQuantity(item.id, item.quantity + 1)}
                          disabled={item.quantity >= (item.stock || 999)}
                          style={{
                            width: '24px',
                            height: '24px',
                            border: 'none',
                            background: '#f1f5f9',
                            borderRadius: '6px',
                            cursor: item.quantity >= (item.stock || 999) ? 'not-allowed' : 'pointer',
                            display: 'flex',
                            alignItems: 'center',
                            justifyContent: 'center',
                            color: '#1e293b',
                            opacity: item.quantity >= (item.stock || 999) ? 0.4 : 1,
                            transition: 'all 0.15s ease',
                          }}
                          aria-label="Increase quantity"
                        >
                          <Plus size={12} strokeWidth={2.5} />
                        </button>
                      </div>

                      <button
                        onClick={() => removeFromCart(item.id)}
                        style={{
                          border: 'none',
                          background: 'none',
                          color: '#ef4444',
                          cursor: 'pointer',
                          padding: '0.3rem',
                          display: 'flex',
                          alignItems: 'center',
                        }}
                        title="Remove item"
                      >
                        <Trash2 size={15} />
                      </button>
                    </div>
                  </div>
                </div>
              ))}
            </div>
          )}
        </div>

        {/* Footer / Checkout */}
        {cart.length > 0 && (
          <div
            style={{
              padding: '1.25rem 1.5rem',
              borderTop: '1px solid #f1f5f9',
              background: '#fff',
              display: 'flex',
              flexDirection: 'column',
              gap: '0.75rem',
            }}
          >
            <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: '0.85rem', color: '#64748b' }}>
              <span>Subtotal</span>
              <span style={{ fontWeight: 700, color: '#0f172a' }}>₹{subtotal?.toLocaleString('en-IN')}</span>
            </div>
            <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: '0.85rem', color: '#64748b' }}>
              <span>{gstCalcType === 'inclusive' ? 'GST (Included)' : 'GST'}</span>
              <span style={{ fontWeight: 700, color: '#0f172a' }}>
                {gstAmount > 0
                  ? `₹${Number(gstAmount).toFixed(2)}`
                  : '₹0'}
              </span>
            </div>
            <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: '0.85rem', color: '#64748b' }}>
              <span>Delivery</span>
              <span style={{ fontWeight: 700, color: shippingCharge === 0 ? '#16a34a' : '#0f172a' }}>
                {shippingCharge === 0 ? 'FREE' : `₹${shippingCharge}`}
              </span>
            </div>
            <div
              style={{
                display: 'flex',
                justifyContent: 'space-between',
                fontSize: '1.1rem',
                fontWeight: 900,
                color: '#0f172a',
                paddingTop: '0.5rem',
                borderTop: '1px dashed #e2e8f0',
              }}
            >
              <span>Total</span>
              <span style={{ color: primaryColor }}>₹{grandTotal?.toLocaleString('en-IN')}</span>
            </div>

            <button
              onClick={onCheckout}
              style={{
                marginTop: '0.5rem',
                width: '100%',
                padding: '0.95rem',
                borderRadius: '14px',
                border: 'none',
                background: primaryColor,
                color: '#fff',
                fontWeight: 800,
                fontSize: '0.95rem',
                cursor: 'pointer',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                gap: '0.6rem',
                boxShadow: `0 8px 20px ${primaryColor}40`,
                transition: 'transform 0.15s ease',
              }}
              onMouseDown={(e) => (e.currentTarget.style.transform = 'scale(0.98)')}
              onMouseUp={(e) => (e.currentTarget.style.transform = 'scale(1)')}
            >
              <span>Proceed to Checkout</span>
              <ArrowRight size={18} />
            </button>

            <div
              style={{
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                gap: '0.4rem',
                fontSize: '0.72rem',
                color: '#94a3b8',
                fontWeight: 600,
              }}
            >
              <ShieldCheck size={14} color="#16a34a" />
              <span>Safe & Secure ERP Checkout</span>
            </div>
          </div>
        )}
      </div>

      <style>{`
        @keyframes slideInRight {
          from { transform: translateX(100%); }
          to { transform: translateX(0); }
        }
      `}</style>
    </div>
  );
}
