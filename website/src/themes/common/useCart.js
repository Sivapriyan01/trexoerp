import { useState, useEffect, useMemo } from 'react';

const CART_STORAGE_KEY = 'trexo_website_cart';

export function useCart(settings = {}) {
  const [cart, setCart] = useState(() => {
    try {
      const saved = localStorage.getItem(CART_STORAGE_KEY);
      return saved ? JSON.parse(saved) : [];
    } catch {
      return [];
    }
  });

  const [isCartOpen, setIsCartOpen] = useState(false);

  // Sync to local storage
  useEffect(() => {
    try {
      localStorage.setItem(CART_STORAGE_KEY, JSON.stringify(cart));
    } catch (e) {
      console.warn('Failed to save cart to localStorage:', e);
    }
  }, [cart]);

  const addToCart = (product, qty = 1) => {
    if (!product || product.stock <= 0) return;

    setCart((prev) => {
      const existing = prev.find((item) => item.id === product.id);
      if (existing) {
        const newQty = Math.min(existing.quantity + qty, product.stock);
        return prev.map((item) =>
          item.id === product.id ? { ...item, quantity: newQty } : item
        );
      }
      return [
        ...prev,
        {
          id: product.id,
          product_name: product.product_name,
          selling_price: Number(product.selling_price || 0),
          mrp: Number(product.mrp || product.selling_price || 0),
          discount: product.discount || 0,
          image: product.image,
          stock: product.stock,
          tax: Number(product.tax || 0),
          quantity: Math.min(qty, product.stock),
        },
      ];
    });
    setIsCartOpen(true);
  };

  const removeFromCart = (productId) => {
    setCart((prev) => prev.filter((item) => item.id !== productId));
  };

  const updateQuantity = (productId, newQty) => {
    if (newQty <= 0) {
      removeFromCart(productId);
      return;
    }
    setCart((prev) =>
      prev.map((item) => {
        if (item.id === productId) {
          const clampedQty = Math.min(newQty, item.stock || 999);
          return { ...item, quantity: clampedQty };
        }
        return item;
      })
    );
  };

  const clearCart = () => {
    setCart([]);
    try {
      localStorage.removeItem(CART_STORAGE_KEY);
    } catch {}
  };

  // Calculations
  const cartQty = useMemo(
    () => cart.reduce((sum, item) => sum + (item.quantity || 0), 0),
    [cart]
  );
  const totalItems = cartQty;

  const subtotal = useMemo(
    () => cart.reduce((sum, item) => sum + (item.selling_price || 0) * (item.quantity || 0), 0),
    [cart]
  );

  const gstCalcType = settings?.gst_calc_type || 'inclusive';

  const gstAmount = useMemo(
    () =>
      cart.reduce((sum, item) => {
        const taxRate = Number(item.tax || 0);
        if (taxRate <= 0) return sum;
        const itemTotal = (item.selling_price || 0) * (item.quantity || 0);
        if (gstCalcType === 'inclusive') {
          // Inclusive GST: selling_price already includes GST (same formula as ERP: total * rate / (100 + rate))
          return sum + (itemTotal * taxRate) / (100 + taxRate);
        } else {
          // Exclusive GST: tax is added on top
          return sum + (itemTotal * taxRate) / 100;
        }
      }, 0),
    [cart, gstCalcType]
  );

  const freeDeliveryMin = Number(settings?.free_delivery_min ?? 999);
  const standardShipping = Number(settings?.shipping_fee ?? 99);

  const shippingCharge = useMemo(() => {
    if (subtotal === 0) return 0;
    return subtotal >= freeDeliveryMin ? 0 : standardShipping;
  }, [subtotal, freeDeliveryMin, standardShipping]);

  const grandTotal = useMemo(() => {
    if (gstCalcType === 'inclusive') {
      return Math.round((subtotal + shippingCharge) * 100) / 100;
    } else {
      return Math.round((subtotal + gstAmount + shippingCharge) * 100) / 100;
    }
  }, [subtotal, gstAmount, shippingCharge, gstCalcType]);

  return {
    cart,
    isCartOpen,
    setIsCartOpen,
    addToCart,
    removeFromCart,
    updateQuantity,
    clearCart,
    cartQty,
    totalItems,
    subtotal,
    gstAmount,
    gstCalcType,
    shippingCharge,
    grandTotal,
    freeDeliveryMin,
  };
}
