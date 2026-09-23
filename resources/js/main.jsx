import React from 'react';
import ReactDOM from 'react-dom/client';
import App from './App.jsx';
import { AuthProvider } from './context/AuthContext.jsx';
import { ToastProvider } from './context/ToastContext.jsx';
import '../css/app.css';

console.log('🚀 SMS React App starting...');

const rootElement = document.getElementById('root');
if (rootElement) {
  try {
    const root = ReactDOM.createRoot(rootElement);
    root.render(
      <ToastProvider>
        <AuthProvider>
          <App />
        </AuthProvider>
      </ToastProvider>
    );
    console.log('✅ React Root rendered successfully.');
  } catch (err) {
    console.error('Fatal mount error:', err);
    rootElement.innerHTML = `
      <div style="padding: 40px; color: white; background: #090d16; min-height: 100vh; font-family: sans-serif;">
        <div style="max-width: 600px; margin: 0 auto; background: #1e293b; padding: 24px; border-radius: 16px; border: 1px solid #ef4444;">
          <h2 style="color: #f87171; margin: 0;">Initialization Error</h2>
          <p style="color: #cbd5e1; margin-top: 10px;">${err.message || err}</p>
        </div>
      </div>
    `;
  }
} else {
  console.error('Root element #root not found in document.');
}
