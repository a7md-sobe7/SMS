import React, { useState } from 'react';
import { Sparkles, Shield, ArrowRight, Lock, User, CheckCircle2 } from 'lucide-react';
import { useAuth, DEMO_ACCOUNTS } from '../context/AuthContext.jsx';
import { useToast } from '../context/ToastContext.jsx';

export const Login = ({ onSwitchToSignUp }) => {
  const { login } = useAuth();
  const toast = useToast();
  const [identifier, setIdentifier] = useState('admin');
  const [password, setPassword] = useState('Admin@123456');
  const [isLoading, setIsLoading] = useState(false);

  const handleSubmit = async (e) => {
    e?.preventDefault();
    if (!identifier || !password) {
      toast.warning('Please enter both username and password.');
      return;
    }

    setIsLoading(true);
    const res = await login(identifier, password);
    setIsLoading(false);

    if (res.success) {
      toast.success(`Welcome back, ${res.user.username}!`);
    } else {
      toast.error(res.message || 'Invalid login credentials.');
    }
  };

  const handleQuickDemoFill = (acc) => {
    setIdentifier(acc.identifier);
    setPassword(acc.password);
  };

  return (
    <div className="min-h-screen bg-slate-950 text-white flex flex-col justify-center items-center p-4 sm:p-6 relative overflow-hidden">
      {/* Background ambient glowing spheres */}
      <div className="absolute top-1/4 left-1/4 w-96 h-96 bg-indigo-600/20 rounded-full blur-3xl pointer-events-none -translate-x-1/2 -translate-y-1/2" />
      <div className="absolute bottom-1/4 right-1/4 w-96 h-96 bg-purple-600/20 rounded-full blur-3xl pointer-events-none translate-x-1/2 translate-y-1/2" />

      <div className="w-full max-w-4xl grid md:grid-cols-2 rounded-3xl border border-slate-800/80 bg-slate-900/70 backdrop-blur-2xl shadow-2xl overflow-hidden z-10">
        {/* Left column: Brand & Features Overview */}
        <div className="p-8 sm:p-10 flex flex-col justify-between bg-gradient-to-br from-indigo-950/60 via-slate-900/60 to-purple-950/40 border-b md:border-b-0 md:border-r border-slate-800">
          <div>
            <div className="flex items-center gap-3">
              <div className="w-12 h-12 rounded-2xl bg-gradient-to-tr from-indigo-600 to-purple-500 flex items-center justify-center text-white shadow-lg shadow-indigo-500/30">
                <Sparkles className="w-6 h-6" />
              </div>
              <div>
                <h1 className="text-xl font-black tracking-tight">SMS Portal</h1>
                <p className="text-xs text-indigo-400 font-semibold uppercase tracking-wider">Enterprise React SPA</p>
              </div>
            </div>

            <div className="mt-8 space-y-4 text-sm text-slate-300">
              <p className="font-semibold text-white text-base">
                Welcome to the Unified Academic Command System.
              </p>
              <div className="space-y-3 pt-2">
                {[
                  'Multi-Role Support (Student, Faculty, Registrar, Admin)',
                  'Interactive Course Catalog & Student Gradebook',
                  'Attendance Tracker with Dynamic Roll-Call',
                  'Real-time Audit Trail and Security Monitoring',
                ].map((feature, i) => (
                  <div key={i} className="flex items-center gap-2.5 text-xs text-slate-300">
                    <CheckCircle2 className="w-4 h-4 text-indigo-400 shrink-0" />
                    <span>{feature}</span>
                  </div>
                ))}
              </div>
            </div>
          </div>

          <div className="mt-8 pt-6 border-t border-slate-800/80 text-xs text-slate-400 flex items-center justify-between">
            <span>New student or faculty?</span>
            <button
              type="button"
              onClick={onSwitchToSignUp}
              className="text-xs font-bold text-indigo-400 hover:text-indigo-300 flex items-center gap-1.5 transition-colors group cursor-pointer"
            >
              <span>Create an account</span>
              <ArrowRight className="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform" />
            </button>
          </div>
        </div>

        {/* Right column: Login form & Demo Role Fast Buttons */}
        <div className="p-8 sm:p-10 flex flex-col justify-center">
          <div className="flex items-center justify-between">
            <div>
              <h2 className="text-2xl font-bold text-white tracking-tight">Account Login</h2>
              <p className="text-xs text-slate-400 mt-1">Sign in with your credentials or click a demo account below.</p>
            </div>
            {onSwitchToSignUp && (
              <button
                type="button"
                onClick={onSwitchToSignUp}
                className="hidden sm:inline-flex text-xs font-semibold px-3 py-1.5 rounded-lg bg-indigo-500/10 text-indigo-300 border border-indigo-500/20 hover:bg-indigo-500/20 transition-colors"
              >
                Sign Up
              </button>
            )}
          </div>

          <form onSubmit={handleSubmit} className="mt-6 space-y-4">
            <div>
              <label className="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                Username or Email
              </label>
              <div className="relative">
                <User className="w-4 h-4 text-slate-500 absolute left-3.5 top-1/2 -translate-y-1/2" />
                <input
                  type="text"
                  value={identifier}
                  onChange={(e) => setIdentifier(e.target.value)}
                  placeholder="e.g. admin or john.doe"
                  required
                  className="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-950/60 border border-slate-800 text-white placeholder-slate-500 text-sm focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all"
                />
              </div>
            </div>

            <div>
              <label className="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                Password
              </label>
              <div className="relative">
                <Lock className="w-4 h-4 text-slate-500 absolute left-3.5 top-1/2 -translate-y-1/2" />
                <input
                  type="password"
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  placeholder="••••••••"
                  required
                  className="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-950/60 border border-slate-800 text-white placeholder-slate-500 text-sm focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all"
                />
              </div>
            </div>

            <button
              type="submit"
              disabled={isLoading}
              className="w-full py-3 px-4 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-bold text-sm shadow-lg shadow-indigo-600/30 flex items-center justify-center gap-2 transition-all group disabled:opacity-50"
            >
              <span>{isLoading ? 'Authenticating...' : 'Sign In'}</span>
              <ArrowRight className="w-4 h-4 group-hover:translate-x-1 transition-transform" />
            </button>
          </form>

          {/* Quick Demo Accounts Selector */}
          <div className="mt-8 pt-6 border-t border-slate-800/80">
            <p className="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2.5 flex items-center gap-1.5">
              <Shield className="w-3.5 h-3.5 text-indigo-400" /> Fast 1-Click Demo Accounts
            </p>
            <div className="grid grid-cols-2 gap-2">
              {Object.entries(DEMO_ACCOUNTS).map(([key, acc]) => (
                <button
                  key={key}
                  type="button"
                  onClick={() => handleQuickDemoFill(acc)}
                  className={`text-left p-2.5 rounded-xl border text-xs font-medium transition-all ${
                    identifier === acc.identifier
                      ? 'bg-indigo-600/20 border-indigo-500/50 text-indigo-200'
                      : 'bg-slate-950/40 border-slate-800 text-slate-400 hover:border-slate-700 hover:text-white'
                  }`}
                >
                  <p className="font-semibold text-white capitalize">{key}</p>
                  <p className="text-[10px] text-slate-400 truncate">{acc.identifier}</p>
                </button>
              ))}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default Login;
