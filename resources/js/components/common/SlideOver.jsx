import React, { useEffect } from 'react';
import { X } from 'lucide-react';

export const SlideOver = ({
  isOpen,
  onClose,
  title,
  subtitle,
  children,
  footer,
  width = 'max-w-2xl',
}) => {
  // Handle escape key listener
  useEffect(() => {
    const handleKeyDown = (e) => {
      if (e.key === 'Escape' && isOpen) {
        onClose();
      }
    };
    if (isOpen) {
      document.addEventListener('keydown', handleKeyDown);
      document.body.style.overflow = 'hidden';
    }
    return () => {
      document.removeEventListener('keydown', handleKeyDown);
      document.body.style.overflow = 'unset';
    };
  }, [isOpen, onClose]);

  if (!isOpen) return null;

  return (
    <div className="fixed inset-0 z-50 overflow-hidden">
      {/* Backdrop overlay with blur */}
      <div
        className="fixed inset-0 bg-slate-950/70 backdrop-blur-sm transition-opacity duration-300 animate-fade-in"
        onClick={onClose}
      />

      <div className="fixed inset-y-0 right-0 max-w-full flex pl-10">
        <div
          className={`w-screen ${width} bg-slate-900 border-l border-slate-800 shadow-2xl flex flex-col justify-between animate-slide-in-right relative`}
        >
          {/* Header */}
          <div className="p-6 border-b border-slate-800/80 bg-slate-900/90 backdrop-blur-md flex items-center justify-between sticky top-0 z-10">
            <div>
              <h2 className="text-xl font-bold text-white tracking-tight flex items-center gap-2">
                {title}
              </h2>
              {subtitle && (
                <p className="text-xs text-slate-400 mt-1 font-medium">{subtitle}</p>
              )}
            </div>
            <button
              onClick={onClose}
              className="p-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500"
              aria-label="Close drawer"
            >
              <X className="w-5 h-5" />
            </button>
          </div>

          {/* Body with custom scrollbar */}
          <div className="flex-1 overflow-y-auto p-6 space-y-6 text-slate-200">
            {children}
          </div>

          {/* Optional Footer */}
          {footer && (
            <div className="p-5 border-t border-slate-800 bg-slate-950/80 backdrop-blur-md flex items-center justify-end gap-3 sticky bottom-0 z-10">
              {footer}
            </div>
          )}
        </div>
      </div>
    </div>
  );
};

export default SlideOver;
