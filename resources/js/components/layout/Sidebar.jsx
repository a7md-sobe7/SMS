import React from 'react';
import {
  LayoutDashboard,
  Users,
  GraduationCap,
  BookOpen,
  Building2,
  UserCheck,
  ClipboardList,
  CalendarCheck,
  ChevronLeft,
  ChevronRight,
  LogOut,
  Sparkles,
} from 'lucide-react';
import { useAuth } from '../../context/AuthContext';

export const Sidebar = ({ isCollapsed, onToggleCollapse, activePage, onNavigate }) => {
  const { user, role, logout } = useAuth();

  const navigationItems = [
    {
      id: 'dashboard',
      label: 'Dashboard',
      icon: LayoutDashboard,
      roles: ['admin', 'registrar', 'instructor', 'student'],
      description: 'System overview & metrics',
    },
    {
      id: 'students',
      label: 'Students',
      icon: Users,
      roles: ['admin', 'registrar', 'instructor'],
      description: 'Directory & academic profiles',
    },
    {
      id: 'courses',
      label: 'Courses',
      icon: BookOpen,
      roles: ['admin', 'registrar', 'instructor', 'student'],
      description: 'Course offerings & capacity',
    },
    {
      id: 'instructors',
      label: 'Faculty & Staff',
      icon: GraduationCap,
      roles: ['admin', 'registrar', 'instructor'],
      description: 'Instructor assignments',
    },
    {
      id: 'departments',
      label: 'Departments',
      icon: Building2,
      roles: ['admin', 'registrar', 'instructor', 'student'],
      description: 'Academic divisions',
    },
    {
      id: 'enrollments',
      label: 'Enrollments',
      icon: UserCheck,
      roles: ['admin', 'registrar', 'student'],
      description: 'Course registrations',
    },
    {
      id: 'grades',
      label: 'Gradebook',
      icon: ClipboardList,
      roles: ['admin', 'instructor', 'student'],
      description: 'Scores, GPA & letters',
    },
    {
      id: 'attendance',
      label: 'Attendance',
      icon: CalendarCheck,
      roles: ['admin', 'instructor', 'student'],
      description: 'Daily roll-call records',
    },
  ];

  const visibleItems = navigationItems.filter(
    (item) => !item.roles || item.roles.includes(role || 'student')
  );

  const getRoleBadge = (r) => {
    switch (r) {
      case 'admin':
        return { label: 'Administrator', bg: 'bg-purple-500/10 text-purple-400 border-purple-500/20' };
      case 'registrar':
        return { label: 'Registrar', bg: 'bg-blue-500/10 text-blue-400 border-blue-500/20' };
      case 'instructor':
        return { label: 'Faculty', bg: 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' };
      case 'student':
        return { label: 'Student', bg: 'bg-amber-500/10 text-amber-400 border-amber-500/20' };
      default:
        return { label: 'Guest', bg: 'bg-slate-500/10 text-slate-400 border-slate-500/20' };
    }
  };

  const badge = getRoleBadge(role);

  return (
    <aside
      className={`fixed top-0 left-0 bottom-0 z-40 flex flex-col justify-between transition-all duration-300 ease-in-out border-r border-slate-800/80 bg-slate-950/85 backdrop-blur-2xl ${
        isCollapsed ? 'w-20' : 'w-64'
      }`}
    >
      {/* Brand / Logo Header */}
      <div>
        <div className="h-18 flex items-center justify-between px-4 border-b border-slate-800/80">
          <div
            onClick={() => onNavigate('dashboard')}
            className="flex items-center gap-3 cursor-pointer overflow-hidden select-none"
          >
            <div className="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-600 via-indigo-500 to-purple-500 flex items-center justify-center text-white font-black shadow-lg shadow-indigo-500/25 shrink-0">
              <Sparkles className="w-5 h-5 animate-pulse" />
            </div>
            {!isCollapsed && (
              <div className="flex flex-col">
                <span className="font-extrabold text-base tracking-tight bg-gradient-to-r from-white via-slate-100 to-slate-400 bg-clip-text text-transparent">
                  SMS Portal
                </span>
                <span className="text-[10px] text-indigo-400 uppercase tracking-widest font-semibold">
                  Enterprise v2.0
                </span>
              </div>
            )}
          </div>

          {/* Collapse Toggle Button */}
          <button
            onClick={onToggleCollapse}
            className="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800/60 transition-colors focus:outline-none"
            title={isCollapsed ? 'Expand sidebar' : 'Collapse sidebar'}
          >
            {isCollapsed ? <ChevronRight className="w-4 h-4" /> : <ChevronLeft className="w-4 h-4" />}
          </button>
        </div>

        {/* User Role Card (Expanded only) */}
        {!isCollapsed && (
          <div className="p-4 mx-3 mt-3 rounded-xl bg-slate-900/60 border border-slate-800/60 backdrop-blur-sm">
            <div className="flex items-center gap-3">
              <div className="w-9 h-9 rounded-lg bg-slate-800 border border-slate-700 flex items-center justify-center font-bold text-sm text-indigo-300 shrink-0 uppercase">
                {user?.username?.substring(0, 2) || 'US'}
              </div>
              <div className="overflow-hidden">
                <p className="text-xs font-semibold text-white truncate">
                  {user?.first_name ? `${user.first_name} ${user.last_name}` : user?.username || 'User'}
                </p>
                <span className={`inline-block text-[10px] font-medium px-2 py-0.5 rounded-full border mt-0.5 ${badge.bg}`}>
                  {badge.label}
                </span>
              </div>
            </div>
          </div>
        )}

        {/* Navigation Items */}
        <nav className="p-3 space-y-1.5 mt-2">
          {visibleItems.map((item) => {
            const Icon = item.icon;
            const isActive = activePage === item.id;

            return (
              <div key={item.id} className="relative group">
                <button
                  onClick={() => onNavigate(item.id)}
                  className={`w-full flex items-center gap-3 px-3.5 py-3 rounded-xl font-medium text-sm transition-all duration-200 ${
                    isActive
                      ? 'bg-gradient-to-r from-indigo-600/90 to-purple-600/90 text-white shadow-lg shadow-indigo-500/20 font-semibold'
                      : 'text-slate-400 hover:text-white hover:bg-slate-900/80'
                  }`}
                >
                  <Icon className={`w-5 h-5 shrink-0 transition-transform group-hover:scale-110 ${isActive ? 'text-white' : 'text-slate-400 group-hover:text-indigo-400'}`} />
                  {!isCollapsed && (
                    <span className="truncate">{item.label}</span>
                  )}
                </button>

                {/* Floating Tooltip in Collapsed Mode */}
                {isCollapsed && (
                  <div className="absolute left-full top-1/2 -translate-y-1/2 ml-3 px-3 py-1.5 bg-slate-900 border border-slate-700 text-white text-xs rounded-lg shadow-xl pointer-events-none opacity-0 group-hover:opacity-100 transition-opacity z-50 whitespace-nowrap">
                    <p className="font-semibold">{item.label}</p>
                    <p className="text-[10px] text-slate-400">{item.description}</p>
                  </div>
                )}
              </div>
            );
          })}
        </nav>
      </div>

      {/* Footer / Logout */}
      <div className="p-3 border-t border-slate-800/80">
        <button
          onClick={logout}
          className="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm text-rose-400 hover:text-rose-300 hover:bg-rose-500/10 transition-colors group"
          title="Sign Out"
        >
          <LogOut className="w-5 h-5 shrink-0 transition-transform group-hover:-translate-x-0.5" />
          {!isCollapsed && <span>Sign Out</span>}
        </button>
      </div>
    </aside>
  );
};

export default Sidebar;
