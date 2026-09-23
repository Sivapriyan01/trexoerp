import React, { useState } from 'react';
import { X, CheckCircle, ShieldCheck, Truck, ArrowRight, Smartphone, AlertCircle } from 'lucide-react';
import { erpApi } from '../../services/erpApi';

export default function CheckoutModal({
  isOpen,
  onClose,
  cart = [],
  subtotal = 0,
  gstAmount = 0,
  gstCalcType,
  shippingCharge = 0,
  grandTotal = 0,
  settings = {},
  primaryColor = '#10b981',
  onOrderPlaced,
}) {
  const [formData, setFormData] = useState({
    name: '',
    phone: '',
    email: '',
    address: '',
    city: '',
    pincode: '',
    paymentMethod: 'COD', // COD | UPI
  });

  const [step, setStep] = useState('FORM'); // 'FORM' | 'OTP' | 'SUCCESS'
  const [otpValue, setOtpValue] = useState('');
  const [serverOtp, setServerOtp] = useState('');
  const [orderResult, setOrderResult] = useState(null);
  const [loading, setLoading] = useState(false);
  const [errorMsg, setErrorMsg] = useState('');
  const [finalAmount, setFinalAmount] = useState(0);
  const [resendTimer, setResendTimer] = useState(30);

  React.useEffect(() => {
    let interval = null;
    if (step === 'OTP' && resendTimer > 0) {
      interval = setInterval(() => {
        setResendTimer((prev) => (prev > 0 ? prev - 1 : 0));
      }, 1000);
    }
    return () => {
      if (interval) clearInterval(interval);
    };
  }, [step, resendTimer]);

  React.useEffect(() => {
    if (!isOpen) return;
    window.onMsg91Verified = (data) => {
      console.log('MSG91 Widget Verified via callback:', data);
      setStep('ADDRESS');
    };
    return () => {
      window.onMsg91Verified = null;
    };
  }, [isOpen]);

  if (!isOpen) return null;

  const effectiveGstCalcType = gstCalcType || settings?.gst_calc_type || 'inclusive';
  const requiresOtp = !(
    settings?.otp_required === false ||
    settings?.otp_required === '0' ||
    settings?.otp_required === 0 ||
    settings?.otp_required === 'false'
  );

  const handleInputChange = (e) => {
    const { name, value } = e.target;
    setFormData((prev) => ({ ...prev, [name]: value }));
  };

  const handleSubmitContact = async (e) => {
    e.preventDefault();
    setErrorMsg('');

    let phone = formData.phone.replace(/\D/g, '');
    if (phone.length === 11 && phone.startsWith('0')) phone = phone.substring(1);
    if (phone.length < 10) {
      setErrorMsg('Please enter a valid 10-digit mobile number.');
      return;
    }

    const fullPhone = phone.length === 10 ? '91' + phone : phone;

    if (requiresOtp) {
      setLoading(true);
      setErrorMsg('');

      // 1. Dispatch real OTP via MSG91 client SDK
      const sendOtpFn = window.sendOtp || window.sendOTP;
      if (typeof sendOtpFn === 'function') {
        try {
          sendOtpFn(
            fullPhone,
            (data) => console.log('MSG91 Widget OTP dispatched:', data),
            (error) => console.warn('MSG91 Widget sendOtp error:', error)
          );
        } catch (e) {
          console.warn('sendOtp call error:', e);
        }
      }

      // 2. Also dispatch backend ERP OTP
      try {
        await erpApi.sendOtp(formData.phone);
      } catch (err) {
        console.warn('Backend sendOtp error:', err);
      } finally {
        setLoading(false);
        setResendTimer(30);
        setStep('OTP');
      }
    } else {
      setStep('ADDRESS');
    }
  };

  const handleResendOtp = async () => {
    if (resendTimer > 0 || loading) return;
    setErrorMsg('');
    setLoading(true);
    try {
      const res = await erpApi.sendOtp(formData.phone);
      if (res && res.success) {
        setErrorMsg('New OTP sent to your phone.');
        setResendTimer(30);
      } else {
        setErrorMsg(res?.message || 'Failed to resend OTP.');
      }
    } catch (e) {
      setErrorMsg('Error resending OTP.');
    } finally {
      setLoading(false);
    }
  };

  const handleVerifyOtp = async (e) => {
    e.preventDefault();
    setErrorMsg('');
    if (!otpValue || otpValue.length < 4) {
      setErrorMsg('Please enter the 4-digit verification code.');
      return;
    }

    setLoading(true);

    const onOtpSuccess = (cust) => {
      if (cust) {
        setFormData((prev) => ({
          ...prev,
          name: cust.name || prev.name,
          email: cust.email || prev.email,
          address: cust.address || prev.address,
        }));
      }
      setStep('ADDRESS');
      setLoading(false);
    };

    // 1. Verify with MSG91 widget first if available
    const verifyOtpFn = window.verifyOtp || window.verifyOTP;
    if (typeof verifyOtpFn === 'function') {
      try {
        verifyOtpFn(
          otpValue,
          async (data) => {
            console.log('MSG91 Widget OTP verified successfully:', data);
            try {
              const res = await erpApi.verifyOtp(formData.phone, otpValue);
              onOtpSuccess(res?.customer);
            } catch {
              onOtpSuccess(null);
            }
          },
          async (error) => {
            console.warn('MSG91 verifyOtp failed, trying ERP verification:', error);
            try {
              const res = await erpApi.verifyOtp(formData.phone, otpValue);
              if (res && res.success) {
                onOtpSuccess(res.customer);
              } else {
                setErrorMsg(error?.message || res?.message || 'Invalid or expired OTP. Please check your phone.');
                setLoading(false);
              }
            } catch (err) {
              setErrorMsg('Invalid or expired OTP.');
              setLoading(false);
            }
          }
        );
        return;
      } catch (e) {
        console.warn('verifyOtp error:', e);
      }
    }

    // 2. Verify with ERP backend
    try {
      const res = await erpApi.verifyOtp(formData.phone, otpValue);
      if (res && res.success) {
        onOtpSuccess(res.customer);
      } else {
        setErrorMsg(res?.message || 'Invalid or expired OTP.');
        setLoading(false);
      }
    } catch (err) {
      setErrorMsg('Error verifying OTP.');
      setLoading(false);
    }
  };

  const handleOpenMsg91Widget = () => {
    const widgetId = settings?.widget_id || '36697164476b323432353839';
    const tokenAuth = settings?.token_auth || '572040TDOpHdLN6aab6e64P1';
    let phone = formData.phone.replace(/\D/g, '');
    if (phone.length === 11 && phone.startsWith('0')) phone = phone.substring(1);
    const fullPhone = phone.length === 10 ? '91' + phone : phone;

    if (typeof window.initSendOTP === 'function') {
      try {
        window.initSendOTP({
          widgetId,
          tokenAuth,
          identifier: fullPhone,
          exposeMethods: false,
          success: (data) => {
            console.log('MSG91 Widget Verified:', data);
            setStep('ADDRESS');
          },
          failure: (error) => {
            console.warn('MSG91 Widget Failure:', error);
            setErrorMsg('MSG91 popup closed or not completed.');
          },
        });
      } catch (e) {
        console.warn('initSendOTP invocation error:', e);
        setErrorMsg('Please enter the OTP received on your phone.');
      }
    } else {
      setErrorMsg('MSG91 Widget is not ready. Please enter the OTP received on your phone.');
    }
  };

  const handleAddressSubmit = async (e) => {
    e.preventDefault();
    setErrorMsg('');
    if (!formData.name || !formData.name.trim()) {
      setErrorMsg('Please enter your full name for delivery.');
      return;
    }
    if (!formData.address || !formData.address.trim()) {
      setErrorMsg('Please enter your delivery address.');
      return;
    }
    await finalizeOrder();
  };

  const finalizeOrder = async () => {
    setLoading(true);
    try {
      const payload = {
        customer: {
          name: formData.name,
          phone: formData.phone,
          email: formData.email,
          shipping_address: `${formData.address}, ${formData.city} - ${formData.pincode}`,
          billing_address: `${formData.address}, ${formData.city} - ${formData.pincode}`,
        },
        customer_name: formData.name,
        customer_phone: formData.phone,
        customer_email: formData.email,
        shipping_address: `${formData.address}, ${formData.city} - ${formData.pincode}`,
        payment_method: formData.paymentMethod,
        items: (cart && cart.length > 0 ? cart : []).map((i) => ({
          product_id: i.id || null,
          product_name: i.product_name || i.name || 'Item',
          quantity: Number(i.quantity) || 1,
          price: Number(i.selling_price || i.price || 0),
          total: (Number(i.selling_price || i.price || 0)) * (Number(i.quantity) || 1),
        })),
        subtotal: Number(subtotal) || 0,
        tax: Number(gstAmount) || 0,
        tax_amount: Number(gstAmount) || 0,
        shipping_charge: Number(shippingCharge) || 0,
        shipping_amount: Number(shippingCharge) || 0,
        grand_total: Number(grandTotal) || 0,
      };

      const finalAmountVal = Number(grandTotal) || 0;
      setFinalAmount(finalAmountVal);

      const placeFn = erpApi.placeOrder ? erpApi.placeOrder.bind(erpApi) : erpApi.createOrder.bind(erpApi);
      const result = await placeFn(payload);
      if (result && (result.success || result.order_id || result.id || result.order)) {
        setOrderResult(result);
        setStep('SUCCESS');
        if (onOrderPlaced) onOrderPlaced(result);
      } else {
        setErrorMsg(result?.message || 'Failed to record order. Please try again.');
      }
    } catch (err) {
      console.error('Order placement exception:', err);
      setErrorMsg(err?.message ? `Error saving order: ${err.message}` : 'Error saving order into ERP.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div style={{ position: 'fixed', inset: 0, zIndex: 9500, display: 'flex', alignItems: 'center', justifyContent: 'center', padding: '1rem' }}>
      {/* Backdrop */}
      <div
        onClick={step === 'SUCCESS' ? onClose : undefined}
        style={{
          position: 'absolute',
          inset: 0,
          background: 'rgba(15, 23, 42, 0.65)',
          backdropFilter: 'blur(6px)',
        }}
      />

      {/* Modal Content */}
      <div
        style={{
          position: 'relative',
          width: '100%',
          maxWidth: '560px',
          maxHeight: '90vh',
          overflowY: 'auto',
          background: '#ffffff',
          color: '#0f172a',
          borderRadius: '24px',
          boxShadow: '0 25px 60px rgba(0,0,0,0.25)',
          padding: '2rem',
          zIndex: 9501,
          fontFamily: 'system-ui, sans-serif',
          boxSizing: 'border-box',
        }}
      >
        {/* Close button */}
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

        {/* Step Indicator Header (Hide on SUCCESS) */}
        {step !== 'SUCCESS' && (
          <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', gap: '0.4rem', marginBottom: '1.25rem' }}>
            <div style={{ display: 'flex', alignItems: 'center', gap: '0.35rem' }}>
              <span style={{
                width: '24px', height: '24px', borderRadius: '50%',
                background: step === 'FORM' ? primaryColor : '#16a34a',
                color: '#fff', fontSize: '0.75rem', fontWeight: 800,
                display: 'flex', alignItems: 'center', justifyContent: 'center'
              }}>
                {step === 'FORM' ? '1' : '✓'}
              </span>
              <span style={{ fontSize: '0.78rem', fontWeight: step === 'FORM' ? 800 : 600, color: step === 'FORM' ? '#0f172a' : '#64748b' }}>
                Phone
              </span>
            </div>

            {requiresOtp && (
              <>
                <div style={{ width: '24px', height: '2px', background: step !== 'FORM' ? '#16a34a' : '#e2e8f0' }} />

                <div style={{ display: 'flex', alignItems: 'center', gap: '0.35rem' }}>
                  <span style={{
                    width: '24px', height: '24px', borderRadius: '50%',
                    background: step === 'OTP' ? primaryColor : (step === 'ADDRESS' ? '#16a34a' : '#f1f5f9'),
                    color: step === 'FORM' ? '#94a3b8' : '#fff',
                    fontSize: '0.75rem', fontWeight: 800,
                    display: 'flex', alignItems: 'center', justifyContent: 'center',
                    border: step === 'FORM' ? '1px solid #cbd5e1' : 'none',
                  }}>
                    {step === 'ADDRESS' ? '✓' : '2'}
                  </span>
                  <span style={{ fontSize: '0.78rem', fontWeight: step === 'OTP' ? 800 : 600, color: step === 'OTP' ? '#0f172a' : '#64748b' }}>
                    OTP
                  </span>
                </div>
              </>
            )}

            <div style={{ width: '24px', height: '2px', background: step === 'ADDRESS' ? primaryColor : '#e2e8f0' }} />

            <div style={{ display: 'flex', alignItems: 'center', gap: '0.35rem' }}>
              <span style={{
                width: '24px', height: '24px', borderRadius: '50%',
                background: step === 'ADDRESS' ? primaryColor : '#f1f5f9',
                color: step === 'ADDRESS' ? '#fff' : '#94a3b8',
                fontSize: '0.75rem', fontWeight: 800,
                display: 'flex', alignItems: 'center', justifyContent: 'center',
                border: step !== 'ADDRESS' ? '1px solid #cbd5e1' : 'none',
              }}>
                {requiresOtp ? '3' : '2'}
              </span>
              <span style={{ fontSize: '0.78rem', fontWeight: step === 'ADDRESS' ? 800 : 600, color: step === 'ADDRESS' ? '#0f172a' : '#64748b' }}>
                Address & Pay
              </span>
            </div>
          </div>
        )}

        {/* STEP 1: ONLY PHONE NUMBER */}
        {step === 'FORM' && (
          <form onSubmit={handleSubmitContact} style={{ display: 'flex', flexDirection: 'column', gap: '1.25rem' }}>
            <div>
              <span style={{ fontSize: '0.75rem', fontWeight: 800, textTransform: 'uppercase', color: primaryColor, letterSpacing: '0.05em' }}>
                Step 1 of {requiresOtp ? '3' : '2'}
              </span>
              <h2 style={{ fontSize: '1.35rem', fontWeight: 800, color: '#0f172a', margin: '0.25rem 0 0' }}>
                Enter Mobile Number
              </h2>
              <p style={{ fontSize: '0.82rem', color: '#64748b', margin: '0.2rem 0 0' }}>
                {requiresOtp 
                  ? "We'll send an OTP to verify your mobile number before entering your delivery address."
                  : "Enter your mobile number to proceed to delivery details."}
              </p>
            </div>

            <div>
              <label style={{ display: 'block', fontSize: '0.8rem', fontWeight: 700, color: '#334155', marginBottom: '0.4rem' }}>
                Mobile Number *
              </label>
              <div style={{ position: 'relative', display: 'flex', alignItems: 'center' }}>
                <span style={{
                  position: 'absolute', left: '0.85rem', fontSize: '0.95rem', fontWeight: 800, color: '#475569'
                }}>
                  +91
                </span>
                <input
                  required
                  autoFocus
                  type="tel"
                  maxLength={10}
                  name="phone"
                  value={formData.phone}
                  onChange={(e) => {
                    const val = e.target.value.replace(/\D/g, '');
                    setFormData((prev) => ({ ...prev, phone: val }));
                  }}
                  placeholder="10-digit mobile number"
                  style={{ ...inputStyle, paddingLeft: '3.1rem', fontSize: '1.05rem', height: '46px', fontWeight: 600, letterSpacing: '0.05em' }}
                />
              </div>
            </div>

            {/* Quick Summary Card */}
            <div style={{ padding: '0.875rem 1rem', background: '#f8fafc', borderRadius: '14px', display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
              <div>
                <div style={{ fontSize: '0.75rem', color: '#64748b', fontWeight: 600 }}>Total Order Amount</div>
                <div style={{ fontSize: '1.15rem', fontWeight: 800, color: primaryColor }}>
                  ₹{Number(grandTotal).toLocaleString('en-IN')}
                </div>
              </div>
              <div style={{ textAlign: 'right', fontSize: '0.78rem', color: '#64748b' }}>
                <span style={{ fontWeight: 700, color: '#0f172a' }}>{cart.reduce((s, i) => s + i.quantity, 0)} items</span> in cart
              </div>
            </div>

            <button
              type="submit"
              disabled={loading}
              style={{
                marginTop: '0.25rem',
                width: '100%',
                padding: '0.95rem',
                borderRadius: '14px',
                border: 'none',
                background: primaryColor,
                color: '#fff',
                fontWeight: 800,
                fontSize: '0.95rem',
                cursor: loading ? 'not-allowed' : 'pointer',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                gap: '0.5rem',
              }}
            >
              {loading ? (
                'Sending OTP…'
              ) : requiresOtp ? (
                <>
                  <span>Send OTP</span>
                  <ArrowRight size={18} />
                </>
              ) : (
                <>
                  <span>Continue to Delivery Address</span>
                  <ArrowRight size={18} />
                </>
              )}
            </button>
          </form>
        )}

        {/* STEP 2: OTP VERIFICATION */}
        {step === 'OTP' && (
          <form onSubmit={handleVerifyOtp} style={{ display: 'flex', flexDirection: 'column', gap: '1.25rem', textAlign: 'center' }}>
            <div style={{ width: '56px', height: '56px', borderRadius: '18px', background: `${primaryColor}15`, color: primaryColor, margin: '0 auto', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
              <Smartphone size={28} />
            </div>
            <div>
              <span style={{ fontSize: '0.75rem', fontWeight: 800, textTransform: 'uppercase', color: primaryColor, letterSpacing: '0.05em' }}>
                Step 2 of 3
              </span>
              <h2 style={{ fontSize: '1.3rem', fontWeight: 800, color: '#0f172a', margin: '0.25rem 0 0.4rem' }}>
                Verify Mobile Number
              </h2>
              <p style={{ fontSize: '0.85rem', color: '#64748b', margin: 0 }}>
                Please enter the OTP sent to <b>+91 {formData.phone}</b>
              </p>
              <div style={{ marginTop: '0.5rem' }}>
                {resendTimer > 0 ? (
                  <span style={{ fontSize: '0.8rem', color: '#64748b', fontWeight: 600 }}>
                    Didn't receive code? Resend in <span style={{ color: primaryColor, fontWeight: 800 }}>{resendTimer}s</span>
                  </span>
                ) : (
                  <button
                    type="button"
                    onClick={handleResendOtp}
                    disabled={loading}
                    style={{
                      background: 'none',
                      border: 'none',
                      color: primaryColor,
                      fontSize: '0.8rem',
                      fontWeight: 700,
                      cursor: loading ? 'not-allowed' : 'pointer',
                      textDecoration: 'underline',
                    }}
                  >
                    Didn't receive code? Resend OTP
                  </button>
                )}
              </div>
            </div>

            <div>
              <input
                required
                type="text"
                maxLength={6}
                value={otpValue}
                onChange={(e) => setOtpValue(e.target.value.replace(/\D/g, ''))}
                placeholder="••••"
                autoFocus
                style={{
                  ...inputStyle,
                  width: '180px',
                  margin: '0 auto',
                  textAlign: 'center',
                  fontSize: '1.5rem',
                  letterSpacing: '0.4rem',
                  fontWeight: 800,
                }}
              />
            </div>

            <button
              type="submit"
              disabled={loading}
              style={{
                width: '100%',
                padding: '0.95rem',
                borderRadius: '14px',
                border: 'none',
                background: primaryColor,
                color: '#fff',
                fontWeight: 800,
                fontSize: '0.95rem',
                cursor: loading ? 'not-allowed' : 'pointer',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                gap: '0.5rem',
              }}
            >
              {loading ? (
                'Verifying…'
              ) : (
                <>
                  <span>Confirm OTP & Enter Address</span>
                  <ArrowRight size={18} />
                </>
              )}
            </button>

            <button
              type="button"
              onClick={() => setStep('FORM')}
              style={{ background: 'none', border: 'none', color: '#64748b', fontSize: '0.82rem', fontWeight: 600, cursor: 'pointer' }}
            >
              ← Change mobile number
            </button>
          </form>
        )}

        {/* STEP 3: DELIVERY ADDRESS & PAYMENT */}
        {step === 'ADDRESS' && (
          <form onSubmit={handleAddressSubmit} style={{ display: 'flex', flexDirection: 'column', gap: '1rem' }}>
            <div>
              <span style={{ fontSize: '0.75rem', fontWeight: 800, textTransform: 'uppercase', color: primaryColor, letterSpacing: '0.05em' }}>
                Step {requiresOtp ? '3 of 3' : '2 of 2'}
              </span>
              <h2 style={{ fontSize: '1.35rem', fontWeight: 800, color: '#0f172a', margin: '0.25rem 0 0' }}>
                Delivery Address & Payment
              </h2>
            </div>

            {/* Verified contact banner */}
            <div style={{
              padding: '0.65rem 0.85rem',
              borderRadius: '12px',
              background: '#f0fdf4',
              border: '1px solid #bbf7d0',
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'space-between',
              fontSize: '0.8rem',
              color: '#166534',
              fontWeight: 600
            }}>
              <div style={{ display: 'flex', alignItems: 'center', gap: '0.4rem' }}>
                <CheckCircle size={15} color="#16a34a" />
                <span>{requiresOtp ? 'Mobile Verified: ' : 'Contact Phone: '}<b>+91 {formData.phone}</b></span>
              </div>
              <button
                type="button"
                onClick={() => setStep('FORM')}
                style={{
                  background: 'none',
                  border: 'none',
                  color: '#15803d',
                  fontSize: '0.75rem',
                  fontWeight: 700,
                  cursor: 'pointer',
                  textDecoration: 'underline'
                }}
              >
                Change
              </button>
            </div>

            {/* Customer Name & Email */}
            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '0.75rem' }}>
              <div>
                <label style={{ display: 'block', fontSize: '0.78rem', fontWeight: 700, color: '#334155', marginBottom: '0.35rem' }}>
                  Full Name *
                </label>
                <input
                  required
                  type="text"
                  name="name"
                  value={formData.name}
                  onChange={handleInputChange}
                  placeholder="e.g. Rahul Sharma"
                  style={inputStyle}
                />
              </div>
              <div>
                <label style={{ display: 'block', fontSize: '0.78rem', fontWeight: 700, color: '#334155', marginBottom: '0.35rem' }}>
                  Email Address (Optional)
                </label>
                <input
                  type="email"
                  name="email"
                  value={formData.email}
                  onChange={handleInputChange}
                  placeholder="For tracking updates"
                  style={inputStyle}
                />
              </div>
            </div>

            <div>
              <label style={{ display: 'block', fontSize: '0.78rem', fontWeight: 700, color: '#334155', marginBottom: '0.35rem' }}>
                Delivery Address *
              </label>
              <textarea
                required
                rows={2}
                name="address"
                value={formData.address}
                onChange={handleInputChange}
                placeholder="Flat / House No., Street, Building, Landmark"
                style={{ ...inputStyle, height: 'auto', padding: '0.6rem 0.75rem' }}
              />
            </div>

            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '0.75rem' }}>
              <div>
                <label style={{ display: 'block', fontSize: '0.78rem', fontWeight: 700, color: '#334155', marginBottom: '0.35rem' }}>
                  City / Town
                </label>
                <input
                  type="text"
                  name="city"
                  value={formData.city}
                  onChange={handleInputChange}
                  placeholder="e.g. Coimbatore"
                  style={inputStyle}
                />
              </div>
              <div>
                <label style={{ display: 'block', fontSize: '0.78rem', fontWeight: 700, color: '#334155', marginBottom: '0.35rem' }}>
                  PIN Code
                </label>
                <input
                  type="text"
                  name="pincode"
                  value={formData.pincode}
                  onChange={handleInputChange}
                  placeholder="e.g. 641001"
                  style={inputStyle}
                />
              </div>
            </div>

            {/* Payment Method */}
            <div>
              <label style={{ display: 'block', fontSize: '0.78rem', fontWeight: 700, color: '#334155', marginBottom: '0.4rem' }}>
                Payment Method
              </label>
              <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '0.75rem' }}>
                <label
                  style={{
                    display: 'flex',
                    alignItems: 'center',
                    gap: '0.6rem',
                    padding: '0.75rem',
                    borderRadius: '12px',
                    border: `1.5px solid ${formData.paymentMethod === 'COD' ? primaryColor : '#e2e8f0'}`,
                    background: formData.paymentMethod === 'COD' ? `${primaryColor}08` : '#fff',
                    cursor: 'pointer',
                  }}
                >
                  <input
                    type="radio"
                    name="paymentMethod"
                    value="COD"
                    checked={formData.paymentMethod === 'COD'}
                    onChange={handleInputChange}
                  />
                  <span style={{ fontSize: '0.82rem', fontWeight: 700, color: '#0f172a' }}>Cash on Delivery</span>
                </label>
                <label
                  style={{
                    display: 'flex',
                    alignItems: 'center',
                    gap: '0.6rem',
                    padding: '0.75rem',
                    borderRadius: '12px',
                    border: `1.5px solid ${formData.paymentMethod === 'UPI' ? primaryColor : '#e2e8f0'}`,
                    background: formData.paymentMethod === 'UPI' ? `${primaryColor}08` : '#fff',
                    cursor: 'pointer',
                  }}
                >
                  <input
                    type="radio"
                    name="paymentMethod"
                    value="UPI"
                    checked={formData.paymentMethod === 'UPI'}
                    onChange={handleInputChange}
                  />
                  <span style={{ fontSize: '0.82rem', fontWeight: 700, color: '#0f172a' }}>UPI / Online</span>
                </label>
              </div>
            </div>

            {/* Price Summary */}
            <div style={{ padding: '0.875rem 1rem', background: '#f8fafc', borderRadius: '14px', marginTop: '0.25rem', display: 'flex', flexDirection: 'column', gap: '0.35rem' }}>
              <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: '0.82rem', color: '#64748b' }}>
                <span>Total Items</span>
                <span style={{ fontWeight: 600, color: '#0f172a' }}>{cart.reduce((s, i) => s + i.quantity, 0)} items</span>
              </div>
              <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: '0.82rem', color: '#64748b' }}>
                <span>Subtotal</span>
                <span style={{ fontWeight: 600, color: '#0f172a' }}>₹{subtotal?.toLocaleString('en-IN')}</span>
              </div>
              <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: '0.82rem', color: '#64748b' }}>
                <span>{effectiveGstCalcType === 'inclusive' ? 'GST (Included)' : 'GST'}</span>
                <span style={{ fontWeight: 600, color: '#0f172a' }}>
                  {gstAmount > 0 ? `₹${Number(gstAmount).toFixed(2)}` : '₹0'}
                </span>
              </div>
              <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: '0.82rem', color: '#64748b' }}>
                <span>Delivery</span>
                <span style={{ fontWeight: 600, color: shippingCharge === 0 ? '#16a34a' : '#0f172a' }}>
                  {shippingCharge === 0 ? 'FREE' : `₹${shippingCharge}`}
                </span>
              </div>
              <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: '1rem', fontWeight: 800, color: '#0f172a', paddingTop: '0.4rem', borderTop: '1px dashed #e2e8f0', marginTop: '0.2rem' }}>
                <span>Order Total</span>
                <span style={{ color: primaryColor }}>₹{grandTotal?.toLocaleString('en-IN')}</span>
              </div>
            </div>

            <button
              type="submit"
              disabled={loading}
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
                cursor: loading ? 'not-allowed' : 'pointer',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                gap: '0.5rem',
              }}
            >
              {loading ? (
                'Placing Order…'
              ) : (
                <>
                  <span>Place Order (₹{grandTotal?.toLocaleString('en-IN')})</span>
                  <CheckCircle size={18} />
                </>
              )}
            </button>

            <button
              type="button"
              onClick={() => setStep(requiresOtp ? 'OTP' : 'FORM')}
              style={{ background: 'none', border: 'none', color: '#64748b', fontSize: '0.82rem', fontWeight: 600, cursor: 'pointer', textAlign: 'center' }}
            >
              ← Back
            </button>
          </form>
        )}

        {/* STEP 3: SUCCESS */}
        {step === 'SUCCESS' && (
          <div style={{ textAlign: 'center', padding: '1rem 0' }}>
            <div style={{ width: '64px', height: '64px', borderRadius: '22px', background: '#dcfce7', color: '#16a34a', margin: '0 auto 1.25rem', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
              <CheckCircle size={36} />
            </div>
            <h2 style={{ fontSize: '1.4rem', fontWeight: 900, color: '#0f172a', margin: '0 0 0.4rem' }}>
              Order Placed Successfully!
            </h2>
            <p style={{ fontSize: '0.88rem', color: '#64748b', maxWidth: '380px', margin: '0 auto 1.5rem', lineHeight: 1.5 }}>
              Thank you for ordering. Your order has been placed in TrexoERP and warehouse fulfillment is now underway.
            </p>

            <div style={{ background: '#f8fafc', borderRadius: '16px', padding: '1rem', marginBottom: '1.5rem', textAlign: 'left' }}>
              <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: '0.5rem', fontSize: '0.85rem' }}>
                <span style={{ color: '#64748b' }}>Order Number:</span>
                <span style={{ fontWeight: 800, color: '#0f172a' }}>{orderResult?.order_number || orderResult?.order_id || 'CONFIRMED'}</span>
              </div>
              <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: '0.5rem', fontSize: '0.85rem' }}>
                <span style={{ color: '#64748b' }}>Amount:</span>
                <span style={{ fontWeight: 800, color: primaryColor }}>
                  ₹{Number(orderResult?.order?.grand_total ?? orderResult?.grand_total ?? finalAmount ?? grandTotal ?? 0).toLocaleString('en-IN')}
                </span>
              </div>
              <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: '0.85rem' }}>
                <span style={{ color: '#64748b' }}>Customer:</span>
                <span style={{ fontWeight: 700, color: '#0f172a' }}>{formData.name} ({formData.phone})</span>
              </div>
            </div>

            <button
              onClick={onClose}
              style={{
                width: '100%',
                padding: '0.95rem',
                borderRadius: '14px',
                border: 'none',
                background: primaryColor,
                color: '#fff',
                fontWeight: 800,
                fontSize: '0.95rem',
                cursor: 'pointer',
              }}
            >
              Continue Shopping
            </button>
          </div>
        )}
      </div>
    </div>
  );
}

const inputStyle = {
  width: '100%',
  height: '42px',
  padding: '0 0.85rem',
  borderRadius: '12px',
  border: '1.5px solid #e2e8f0',
  fontSize: '0.85rem',
  outline: 'none',
  boxSizing: 'border-box',
  background: '#f8fafc',
  fontFamily: 'inherit',
};
