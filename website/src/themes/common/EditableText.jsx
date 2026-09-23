import React, { useRef, useState, useEffect } from 'react';

/**
 * EditableText — Inline click-to-edit text block for visual website customization.
 * When inside ERP configurator iframe or ?edit=1, clicking on text allows live in-place typing.
 * Changes sync instantly to parent window via postMessage.
 */
export function EditableText({
  fieldKey,
  value = '',
  onChange,
  as: Component = 'span',
  style = {},
  className = '',
  placeholder = 'Click to edit…',
  multiline = false,
  children,
}) {
  const isEditor =
    typeof window !== 'undefined' &&
    (window.self !== window.top ||
      new URLSearchParams(window.location.search).get('edit') === '1');

  const ref = useRef(null);
  const [isFocused, setIsFocused] = useState(false);
  const [isHovered, setIsHovered] = useState(false);

  const textContent = value !== undefined && value !== null ? String(value) : '';

  // Synchronize internal text with incoming prop when not currently focused
  useEffect(() => {
    if (ref.current && !isFocused && ref.current.innerText !== textContent) {
      ref.current.innerText = textContent;
    }
  }, [textContent, isFocused]);

  if (!isEditor) {
    return (
      <Component style={style} className={className}>
        {children || textContent}
      </Component>
    );
  }

  const handleBlur = () => {
    setIsFocused(false);
    if (!ref.current) return;
    const newText = ref.current.innerText.trim();
    if (onChange && newText !== textContent) {
      onChange(fieldKey, newText);
    }
    // Post back to parent ERP configurator
    try {
      if (window.parent && window.parent !== window) {
        window.parent.postMessage(
          {
            type: 'TREXO_INLINE_TEXT_UPDATE',
            key: fieldKey,
            value: newText,
          },
          '*'
        );
      }
    } catch (e) {}
  };

  const handleKeyDown = (e) => {
    if (!multiline && e.key === 'Enter') {
      e.preventDefault();
      ref.current?.blur();
    }
  };

  return (
    <Component
      style={{
        ...style,
        position: 'relative',
        cursor: 'text',
        outline: isFocused
          ? '2px solid #6366f1'
          : isHovered
          ? '2px dashed #818cf8'
          : 'none',
        outlineOffset: '4px',
        borderRadius: '6px',
        transition: 'outline 0.15s ease, background 0.15s ease',
        background: isFocused ? 'rgba(99, 102, 241, 0.05)' : style.background || 'transparent',
      }}
      className={className}
      onMouseEnter={() => setIsHovered(true)}
      onMouseLeave={() => setIsHovered(false)}
      onClick={(e) => {
        e.stopPropagation();
        if (ref.current) {
          ref.current.focus();
        }
      }}
    >
      <span
        ref={ref}
        contentEditable={true}
        suppressContentEditableWarning={true}
        onFocus={() => setIsFocused(true)}
        onBlur={handleBlur}
        onKeyDown={handleKeyDown}
        data-placeholder={placeholder}
        style={{
          display: 'inline-block',
          width: '100%',
          minWidth: '20px',
          outline: 'none',
          cursor: 'text',
          userSelect: 'text',
        }}
      >
        {textContent || children}
      </span>

      {/* Floating hover badge */}
      {isHovered && !isFocused && (
        <span
          contentEditable={false}
          style={{
            position: 'absolute',
            bottom: 'calc(100% + 4px)',
            left: '0',
            background: '#4f46e5',
            color: '#ffffff',
            fontSize: '10px',
            fontWeight: 700,
            padding: '2px 6px',
            borderRadius: '4px',
            pointerEvents: 'none',
            whiteSpace: 'nowrap',
            zIndex: 9999,
            boxShadow: '0 2px 6px rgba(0,0,0,0.15)',
            letterSpacing: '0.02em',
          }}
        >
          ✏️ Click to edit
        </span>
      )}
    </Component>
  );
}
