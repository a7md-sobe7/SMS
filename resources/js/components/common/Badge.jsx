import React from 'react';

export const Badge = ({ children, variant = 'slate', size = 'md' }) => {
  const sizeClasses = {
    sm: 'px-2 py-0.5 text-[10px]',
    md: 'px-2.5 py-1 text-xs',
    lg: 'px-3 py-1.5 text-sm',
  }[size] || 'px-2.5 py-1 text-xs';

  const variantClasses = {
    // Statuses
    active: 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
    enrolled: 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
    present: 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
    completed: 'bg-blue-500/10 text-blue-400 border-blue-500/20',
    late: 'bg-amber-500/10 text-amber-400 border-amber-500/20',
    suspended: 'bg-rose-500/10 text-rose-400 border-rose-500/20',
    dropped: 'bg-rose-500/10 text-rose-400 border-rose-500/20',
    absent: 'bg-rose-500/10 text-rose-400 border-rose-500/20',
    excused: 'bg-purple-500/10 text-purple-400 border-purple-500/20',
    graduated: 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20',
    withdrawn: 'bg-slate-500/10 text-slate-400 border-slate-500/20',

    // Grades
    'grade-A': 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30 font-bold',
    'grade-B': 'bg-blue-500/20 text-blue-300 border-blue-500/30 font-bold',
    'grade-C': 'bg-yellow-500/20 text-yellow-300 border-yellow-500/30 font-bold',
    'grade-D': 'bg-amber-500/20 text-amber-300 border-amber-500/30 font-bold',
    'grade-F': 'bg-rose-500/20 text-rose-300 border-rose-500/30 font-bold',

    // Generic
    indigo: 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20',
    purple: 'bg-purple-500/10 text-purple-400 border-purple-500/20',
    slate: 'bg-slate-800/60 text-slate-300 border-slate-700/60',
  }[variant] || 'bg-slate-800/60 text-slate-300 border-slate-700/60';

  return (
    <span
      className={`inline-flex items-center gap-1.5 font-semibold rounded-full border tracking-wide uppercase ${sizeClasses} ${variantClasses}`}
    >
      {children}
    </span>
  );
};

export default Badge;
