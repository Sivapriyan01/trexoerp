import React, { useState } from 'react';
import { GripVertical, ArrowUp, ArrowDown, Eye, EyeOff } from 'lucide-react';

/**
 * SectionWrapper — Wraps storefront sections with visual drag-and-drop handles
 * and move up/down controls when running inside the ERP Configurator.
 */
export function SectionWrapper({
  sectionId,
  title,
  index,
  totalSections,
  visible = true,
  onMoveUp,
  onMoveDown,
  onDragStart,
  onDragOver,
  onDrop,
  onToggleVisibility,
  children,
}) {
  const isEditor =
    typeof window !== 'undefined' &&
    (window.self !== window.top ||
      new URLSearchParams(window.location.search).get('edit') === '1');

  const [isHovered, setIsHovered] = useState(false);
  const [isDragOver, setIsDragOver] = useState(false);

  if (!visible && !isEditor) {
    return null;
  }

  if (!isEditor) {
    return <>{children}</>;
  }

  return (
    <div
      data-section-id={sectionId}
      onMouseEnter={() => setIsHovered(true)}
      onMouseLeave={() => setIsHovered(false)}
      onDragOver={(e) => {
        e.preventDefault();
        setIsDragOver(true);
        if (onDragOver) onDragOver(e, sectionId);
      }}
      onDragLeave={() => setIsDragOver(false)}
      onDrop={(e) => {
        setIsDragOver(false);
        if (onDrop) onDrop(e, sectionId);
      }}
      style={{
        position: 'relative',
        outline: isDragOver
          ? '3px solid #6366f1'
          : isHovered
          ? '1.5px dashed rgba(99, 102, 241, 0.45)'
          : 'none',
        outlineOffset: '-1.5px',
        transition: 'outline 0.15s ease',
        opacity: visible ? 1 : 0.4,
      }}
    >
      {/* Visual builder toolbar on hover */}
      {isHovered && (
        <div
          contentEditable={false}
          style={{
            position: 'absolute',
            top: '8px',
            right: '16px',
            zIndex: 900,
            display: 'flex',
            alignItems: 'center',
            gap: '4px',
            background: '#1e1b4b',
            color: '#ffffff',
            padding: '4px 8px',
            borderRadius: '8px',
            boxShadow: '0 4px 14px rgba(0,0,0,0.25)',
            fontSize: '11px',
            fontWeight: 700,
            userSelect: 'none',
          }}
        >
          {/* Drag handle */}
          <div
            draggable={true}
            onDragStart={(e) => {
              e.dataTransfer.setData('text/plain', sectionId);
              if (onDragStart) onDragStart(e, sectionId);
            }}
            title="Drag to reorder section"
            style={{
              cursor: 'grab',
              display: 'flex',
              alignItems: 'center',
              padding: '2px',
              color: '#a5b4fc',
            }}
          >
            <GripVertical size={14} />
          </div>

          <span style={{ padding: '0 4px', color: '#e0e7ff', borderRight: '1px solid rgba(255,255,255,0.15)' }}>
            {title}
          </span>

          {/* Move Up */}
          <button
            type="button"
            disabled={index <= 0}
            onClick={(e) => {
              e.stopPropagation();
              if (onMoveUp) onMoveUp(sectionId);
            }}
            title="Move section up"
            style={{
              background: 'none',
              border: 'none',
              color: index <= 0 ? 'rgba(255,255,255,0.25)' : '#ffffff',
              cursor: index <= 0 ? 'default' : 'pointer',
              padding: '2px',
              display: 'flex',
              alignItems: 'center',
            }}
          >
            <ArrowUp size={13} />
          </button>

          {/* Move Down */}
          <button
            type="button"
            disabled={index >= totalSections - 1}
            onClick={(e) => {
              e.stopPropagation();
              if (onMoveDown) onMoveDown(sectionId);
            }}
            title="Move section down"
            style={{
              background: 'none',
              border: 'none',
              color: index >= totalSections - 1 ? 'rgba(255,255,255,0.25)' : '#ffffff',
              cursor: index >= totalSections - 1 ? 'default' : 'pointer',
              padding: '2px',
              display: 'flex',
              alignItems: 'center',
            }}
          >
            <ArrowDown size={13} />
          </button>

          {/* Toggle visibility */}
          <button
            type="button"
            onClick={(e) => {
              e.stopPropagation();
              if (onToggleVisibility) onToggleVisibility(sectionId);
            }}
            title={visible ? 'Hide section' : 'Show section'}
            style={{
              background: 'none',
              border: 'none',
              color: visible ? '#86efac' : '#f87171',
              cursor: 'pointer',
              padding: '2px',
              display: 'flex',
              alignItems: 'center',
              marginLeft: '2px',
            }}
          >
            {visible ? <Eye size={13} /> : <EyeOff size={13} />}
          </button>
        </div>
      )}

      {children}
    </div>
  );
}
