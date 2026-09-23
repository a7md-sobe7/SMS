import React from 'react';

export const StatsCard = ({ title, value, icon: Icon, change, trend = 'up', color = 'indigo' }) => {
  const colorStyles = {
    indigo: {
      border: 'border-indigo-500/20 hover:border-indigo-500/40',
      iconBg: 'bg-indigo-500/10 text-indigo-400',
      glow: 'group-hover:shadow-indigo-500/10',
    },
    purple: {
      border: 'border-purple-500/20 hover:border-purple-500/40',
      iconBg: 'bg-purple-500/10 text-purple-400',
      glow: 'group-hover:shadow-purple-500/10',
    },
    emerald: {
      border: 'border-emerald-500/20 hover:border-emerald-500/40',
      iconBg: 'bg-emerald-500/10 text-emerald-400',
      glow: 'group-hover:shadow-emerald-500/10',
    },
    amber: {
      border: 'border-amber-500/20 hover:border-amber-500/40',
      iconBg: 'bg-amber-500/10 text-amber-400',
      glow: 'group-hover:shadow-amber-500/10',
    },
    rose: {
      border: 'border-rose-500/20 hover:border-rose-500/40',
      iconBg: 'bg-rose-500/10 text-rose-400',
      glow: 'group-hover:shadow-rose-500/10',
    },
  }[color] || {
    border: 'border-slate-800 hover:border-slate-700',
    iconBg: 'bg-slate-800 text-slate-300',
    glow: '',
  };

  return (
    <div
      className={`glass-panel p-5 rounded-2xl border transition-all duration-300 group shadow-xl hover:-translate-y-1 ${colorStyles.border} ${colorStyles.glow}`}
    >
      <div className="flex items-center justify-between">
        <div>
          <p className="text-xs font-semibold uppercase tracking-wider text-slate-400">
            {title}
          </p>
          <h3 className="text-2xl sm:text-3xl font-extrabold text-white mt-1 tracking-tight">
            {value}
          </h3>
          {change && (
            <div className="flex items-center gap-1.5 mt-2">
              <span
                className={`text-xs font-semibold ${
                  trend === 'up' ? 'text-emerald-400' : 'text-rose-400'
                }`}
              >
                {trend === 'up' ? '↑' : '↓'} {change}
              </span>
              <span className="text-[11px] text-slate-500 font-medium">vs last month</span>
            </div>
          )}
        </div>
        {Icon && (
          <div className={`p-3.5 rounded-2xl ${colorStyles.iconBg} transition-transform group-hover:scale-110 shadow-inner`}>
            <Icon className="w-6 h-6" />
          </div>
        )}
      </div>
    </div>
  );
};

export default StatsCard;
