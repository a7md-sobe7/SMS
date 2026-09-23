import React, { useState } from 'react';
import {
  Search,
  Bell,
  User,
  Shield,
  Check,
  ChevronDown,
  Sparkles,
} from 'lucide-react';
import { useAuth, DEMO_ACCOUNTS } from '../../context/AuthContext';
import { useToast } from '../../context/ToastContext';

export const Navbar = ({ onOpenSearch, activePageTitle }) => {
  const { user, role, switchDemoRole } = useAuth();
  const toast = useToast();
  const [showRoleDropdown, setShowRoleDropdown] = useState(false);

  const handleRoleSwitch = async (roleKey) => {
    setShowRoleDropdown(false);
    const res = await switchDemoRole(roleKey);
    if (res?.success) {
      toast.success(`Switched role to ${DEMO_ACCOUNTS[roleKey].label}`);
    } else {
      toast.error('Failed to switch role.');
    }
  };

  return (
    <header className="h-18 sticky top-0 z-30 flex items-center justify-between px-6 border-b border-slate-800/80 bg-slate-950/70 backdrop-blur-xl">
      {/* Current Page Title & Breadcrumb */}
      <div className="flex items-center gap-3">
        <h1 className="text-lg font-bold text-white tracking-tight capitalize">
          {activePageTitle}
        </h1>
        <span className="hidden md:inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
          <span className="w-1.5 h-1.5 rounded-full bg-indigo-400 animate-ping" />
          Live API
        </span>
      </div>

      {/* Center / Right controls */}
      <div className="flex items-center gap-3">
        {/* Quick Search Bar trigger */}
        <button
          onClick={onOpenSearch}
          className="hidden sm:flex items-center gap-2.5 px-3.5 py-1.5 rounded-xl bg-slate-900 border border-slate-800 text-slate-400 hover:border-slate-700 hover:text-slate-200 transition-colors text-xs font-medium shadow-inner"
        >
          <Search className="w-3.5 h-3.5 text-slate-500" />
          <span>Quick search or command...</span>
          <kbd className="px-1.5 py-0.5 rounded bg-slate-800 text-[10px] text-slate-400 font-mono border border-slate-700 ml-2">
            Ctrl K
          </kbd>
        </button>

        {/* Demo Role Switcher Dropdown */}
        <div className="relative">
          <button
            onClick={() => setShowRoleDropdown(!showRoleDropdown)}
            className="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-gradient-to-r from-slate-900 to-indigo-950/60 border border-indigo-500/30 text-indigo-200 hover:border-indigo-500/60 transition-all text-xs font-semibold shadow-md"
          >
            <Shield className="w-3.5 h-3.5 text-indigo-400" />
            <span className="hidden md:inline">Role:</span>
            <span className="capitalize text-white font-bold">{role || 'User'}</span>
            <ChevronDown className="w-3.5 h-3.5 text-slate-400" />
          </button>

          {showRoleDropdown && (
            <div className="absolute right-0 mt-2 w-56 rounded-2xl bg-slate-900 border border-slate-800 shadow-2xl p-2 z-50 animate-slide-in-right">
              <div className="px-3 py-2 border-b border-slate-800">
                <p className="text-[10px] font-bold uppercase tracking-wider text-indigo-400 flex items-center gap-1.5">
                  <Sparkles className="w-3 h-3" /> Quick Switch Role
                </p>
              </div>
              <div className="py-1 space-y-1">
                {Object.entries(DEMO_ACCOUNTS).map(([key, item]) => {
                  const isCurrent = role === key;
                  return (
                    <button
                      key={key}
                      onClick={() => handleRoleSwitch(key)}
                      className={`w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-medium transition-colors ${
                        isCurrent
                          ? 'bg-indigo-600/20 text-indigo-300 font-semibold border border-indigo-500/30'
                          : 'text-slate-300 hover:bg-slate-800/80 hover:text-white'
                      }`}
                    >
                      <span className="flex items-center gap-2">
                        <span className={`w-2 h-2 rounded-full bg-gradient-to-r ${item.badgeColor}`} />
                        {item.label}
                      </span>
                      {isCurrent && <Check className="w-3.5 h-3.5 text-indigo-400" />}
                    </button>
                  );
                })}
              </div>
            </div>
          )}
        </div>

        {/* Notifications Icon */}
        <button
          onClick={() => toast.info('You have 0 unread alerts.', 2000)}
          className="p-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-900 border border-transparent hover:border-slate-800 transition-colors relative"
          aria-label="Notifications"
        >
          <Bell className="w-4 h-4" />
          <span className="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-indigo-500 ring-2 ring-slate-950" />
        </button>

        {/* User Pill */}
        <div className="flex items-center gap-2.5 pl-2 border-l border-slate-800">
          <div className="w-8 h-8 rounded-xl bg-gradient-to-tr from-indigo-600 to-purple-600 flex items-center justify-center text-white text-xs font-bold shadow-md">
            {user?.username?.[0]?.toUpperCase() || <User className="w-4 h-4" />}
          </div>
        </div>
      </div>
    </header>
  );
};

export default Navbar;
