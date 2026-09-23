import React, { useState } from 'react';
import { X, Search, Package, CheckCircle2, Clock, AlertCircle, Truck } from 'lucide-react';
import { erpApi } from '../../services/erpApi';

export default function OrderTracker({ isOpen, onClose, primaryColor = '#10b981' }) {
  const [orderQuery, setOrderQuery] = useState('');
  const [loading, setLoading] = useState(false);
  const [orderData, setOrderData] = useState(null);
  const [errorMsg, setErrorMsg] = useState('');

  if (!isOpen) return null;

  const handleTrack = async (e) => {
    e.preventDefault();
    if (!orderQuery.trim()) return;

    setLoading(true);
    setErrorMsg('');
    setOrderData(null);

    try {
      const res = await erpApi.trackOrder(orderQuery.trim());
      if (res && (res.order || res.status || res.order_status || res.order_number || res.id || res.invoice_no)) {
        setOrderData(res.order || res);
      } else {
        setErrorMsg(res?.message || 'No order found with this Order ID or Mobile number.');
      }
    } catch (err) {
      setErrorMsg('Failed to fetch order status. Please try again.');
    } finally {
      setLoading(false);
    }
  };

  const steps = [
    { key: 'PENDING', label: 'Order Received' },
    { key: 'PROCESSING', label: 'Processing in Warehouse' },
    { key: 'SHIPPED', label: 'Shipped & Out for Delivery' },
    { key: 'DELIVERED', label: 'Delivered' },
  ];

  const currentStatus = String(orderData?.status || 'PENDING').toUpperCase();
  const getStepIndex = (status) => {
    switch (status) {
      case 'PENDING':
        return 0;
      case 'PROCESSING':
      case 'CONFIRMED':
        return 1;
      case 'SHIPPED':
      case 'DISPATCHED':
        return 2;
      case 'DELIVERED':
        return 3;
      default:
        return 0;
    }
  };
  const activeStepIdx = getStepIndex(currentStatus);

  return (
    <div style={{ position: 'fixed', inset: 0, zIndex: 9500, display: 'flex', alignItems: 'center', justifyContent: 'center', padding: '1rem' }}>
      {/* Backdrop */}
      <div
        onClick={onClose}
        style={{
          position: 'absolute',
          inset: 0,
          background: 'rgba(15, 23, 42, 0.65)',
          backdropFilter: 'blur(6px)',
        }}
      />

      {/* Modal Card */}
      <div
        style={{
          position: 'relative',
          width: '100%',
          maxWidth: '520px',
          maxHeight: '90vh',
          overflowY: 'auto',
          background: '#ffffff',
          borderRadius: '24px',
          boxShadow: '0 25px 60px rgba(0,0,0,0.25)',
          padding: '2rem',
          zIndex: 9501,
          fontFamily: 'system-ui, sans-serif',
          boxSizing: 'border-box',
        }}
      >
        <button
          onClick={onClose}
          style={{
            position: 'absolute',
            top: '1.25rem',
            right: '1.25rem',
            width: '36px',
            height: '36px',
            borderRadius: '10px',
            border: 'none',
            background: '#f1f5f9',
            cursor: 'pointer',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            color: '#64748b',
          }}
        >
          <X size={18} />
        </button>

        <div style={{ textAlign: 'center', marginBottom: '1.5rem' }}>
          <div
            style={{
              width: '52px',
              height: '52px',
              borderRadius: '16px',
              background: `${primaryColor}15`,
              color: primaryColor,
              margin: '0 auto 0.75rem',
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center',
            }}
          >
            <Truck size={26} />
          </div>
          <h2 style={{ fontSize: '1.35rem', fontWeight: 800, color: '#0f172a', margin: '0 0 0.25rem' }}>
            Live Order Tracking
          </h2>
          <p style={{ fontSize: '0.85rem', color: '#64748b', margin: 0 }}>
            Enter your Order ID or registered Mobile Number to track status
          </p>
        </div>

        <form onSubmit={handleTrack} style={{ display: 'flex', gap: '0.5rem', marginBottom: '1.5rem' }}>
          <input
            type="text"
            required
            value={orderQuery}
            onChange={(e) => setOrderQuery(e.target.value)}
            placeholder="e.g. ORD-1001 or 9876543210"
            style={{
              flex: 1,
              height: '46px',
              padding: '0 1rem',
              borderRadius: '12px',
              border: '1.5px solid #e2e8f0',
              fontSize: '0.9rem',
              outline: 'none',
              background: '#f8fafc',
            }}
          />
          <button
            type="submit"
            disabled={loading}
            style={{
              padding: '0 1.4rem',
              borderRadius: '12px',
              border: 'none',
              background: primaryColor,
              color: '#fff',
              fontWeight: 700,
              fontSize: '0.9rem',
              cursor: loading ? 'not-allowed' : 'pointer',
              display: 'flex',
              alignItems: 'center',
              gap: '0.4rem',
            }}
          >
            <Search size={16} />
            <span>{loading ? 'Searching…' : 'Track'}</span>
          </button>
        </form>

        {errorMsg && (
          <div
            style={{
              padding: '0.75rem 1rem',
              borderRadius: '12px',
              background: '#fef2f2',
              border: '1px solid #fecaca',
              color: '#b91c1c',
              fontSize: '0.82rem',
              display: 'flex',
              alignItems: 'center',
              gap: '0.5rem',
              marginBottom: '1rem',
              fontWeight: 600,
            }}
          >
            <AlertCircle size={16} />
            <span>{errorMsg}</span>
          </div>
        )}

        {orderData && (
          <div style={{ background: '#f8fafc', borderRadius: '18px', padding: '1.25rem', border: '1px solid #e2e8f0' }}>
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: '1rem', paddingBottom: '0.75rem', borderBottom: '1px solid #e2e8f0' }}>
              <div>
                <span style={{ fontSize: '0.72rem', fontWeight: 700, color: '#64748b', textTransform: 'uppercase' }}>
                  Order Number
                </span>
                <div style={{ fontSize: '1.05rem', fontWeight: 800, color: '#0f172a' }}>
                  {orderData.order_number || orderData.id || orderQuery}
                </div>
              </div>
              <span
                style={{
                  padding: '0.35rem 0.75rem',
                  borderRadius: '99px',
                  fontSize: '0.75rem',
                  fontWeight: 800,
                  background: `${primaryColor}20`,
                  color: primaryColor,
                  textTransform: 'uppercase',
                }}
              >
                {currentStatus}
              </span>
            </div>

            {/* Timeline */}
            <div style={{ display: 'flex', flexDirection: 'column', gap: '1rem', margin: '1.25rem 0' }}>
              {steps.map((s, idx) => {
                const isPassed = idx <= activeStepIdx;
                const isCurrent = idx === activeStepIdx;
                return (
                  <div key={s.key} style={{ display: 'flex', alignItems: 'center', gap: '0.75rem' }}>
                    <div
                      style={{
                        width: '28px',
                        height: '28px',
                        borderRadius: '50%',
                        background: isPassed ? primaryColor : '#e2e8f0',
                        color: '#fff',
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: 'center',
                        fontSize: '0.75rem',
                        fontWeight: 800,
                        boxShadow: isCurrent ? `0 0 0 4px ${primaryColor}30` : 'none',
                      }}
                    >
                      {isPassed ? <CheckCircle2 size={16} /> : idx + 1}
                    </div>
                    <div>
                      <div style={{ fontSize: '0.85rem', fontWeight: isCurrent ? 800 : 600, color: isPassed ? '#0f172a' : '#94a3b8' }}>
                        {s.label}
                      </div>
                    </div>
                  </div>
                );
              })}
            </div>

            {orderData.grand_total && (
              <div style={{ display: 'flex', justifyContent: 'space-between', paddingTop: '0.75rem', borderTop: '1px solid #e2e8f0', fontSize: '0.85rem' }}>
                <span style={{ color: '#64748b' }}>Total Amount:</span>
                <span style={{ fontWeight: 800, color: '#0f172a' }}>₹{Number(orderData.grand_total).toLocaleString('en-IN')}</span>
              </div>
            )}
          </div>
        )}
      </div>
    </div>
  );
}
