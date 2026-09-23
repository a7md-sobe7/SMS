import React, { useState, useEffect } from 'react';
import {
  Users,
  BookOpen,
  Building2,
  GraduationCap,
  Plus,
  ArrowUpRight,
  Clock,
  Sparkles,
  CalendarCheck,
  ClipboardList,
} from 'lucide-react';
import { useAuth } from '../context/AuthContext.jsx';
import api from '../services/api';
import StatsCard from '../components/common/StatsCard.jsx';
import Badge from '../components/common/Badge.jsx';

export const Dashboard = ({ onNavigate, onOpenQuickAction }) => {
  const { user, role } = useAuth();
  const [data, setData] = useState(null);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    const fetchStats = async () => {
      try {
        const res = await api.get('/dashboard/stats');
        setData(res.data);
      } catch (err) {
        console.error('Error loading dashboard stats:', err);
      } finally {
        setIsLoading(false);
      }
    };
    fetchStats();
  }, []);

  const stats = data?.stats || {};

  return (
    <div className="space-y-8 animate-fade-in">
      {/* Welcome Hero Banner */}
      <div className="relative p-6 sm:p-8 rounded-3xl overflow-hidden glass-panel border border-indigo-500/20 bg-gradient-to-r from-indigo-950/60 via-slate-900/80 to-purple-950/40 shadow-2xl">
        <div className="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
          <div className="space-y-2">
            <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 text-xs font-semibold">
              <Sparkles className="w-3.5 h-3.5" />
              <span>Academic Session 2026 / 2027</span>
            </div>
            <h2 className="text-2xl sm:text-3xl font-black text-white tracking-tight">
              Welcome back, {user?.first_name || user?.username || 'Scholar'}!
            </h2>
            <p className="text-sm text-slate-300 max-w-xl">
              {role === 'student'
                ? 'Check your academic progress, enrolled courses, and latest transcript grades below.'
                : role === 'instructor'
                ? 'Manage your assigned classes, input student midterm/final grades, and record roll-calls.'
                : 'Monitor student admissions, course capacities, faculty schedules, and security audits.'}
            </p>
          </div>

          {/* Quick Action Buttons */}
          <div className="flex flex-wrap gap-2.5 shrink-0">
            {role !== 'student' && (
              <button
                onClick={() => onNavigate('students')}
                className="px-4 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white text-xs font-bold shadow-lg shadow-indigo-500/20 flex items-center gap-2 transition-all"
              >
                <Users className="w-4 h-4" />
                <span>Manage Students</span>
              </button>
            )}
            <button
              onClick={() => onNavigate('courses')}
              className="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white border border-slate-700 text-xs font-bold transition-all flex items-center gap-2"
            >
              <BookOpen className="w-4 h-4" />
              <span>Browse Catalog</span>
            </button>
          </div>
        </div>
      </div>

      {/* KPI Cards Grid */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        {role === 'student' ? (
          <>
            <StatsCard
              title="Graded Courses"
              value={stats.my_gpa?.total_graded_courses || '0'}
              icon={BookOpen}
              color="indigo"
              change="Term 1"
            />
            <StatsCard
              title="Average Score"
              value={`${Number(stats.my_gpa?.average_numerical_grade || 0).toFixed(1)}%`}
              icon={Sparkles}
              color="purple"
              change="+2.4%"
            />
            <StatsCard
              title="Credits Earned"
              value={`${stats.my_gpa?.total_credits_earned || '0'} hrs`}
              icon={GraduationCap}
              color="emerald"
            />
            <StatsCard
              title="Enrolled Classes"
              value={stats.my_enrollments?.length || '0'}
              icon={Users}
              color="amber"
            />
          </>
        ) : (
          <>
            <StatsCard
              title="Total Students"
              value={stats.total_students ?? '...'}
              icon={Users}
              color="indigo"
              change="+14.2%"
            />
            <StatsCard
              title="Active Courses"
              value={stats.total_courses ?? '...'}
              icon={BookOpen}
              color="purple"
              change="+3 new"
            />
            <StatsCard
              title="Departments"
              value={stats.total_departments ?? '...'}
              icon={Building2}
              color="emerald"
            />
            <StatsCard
              title="Faculty & Staff"
              value={stats.total_instructors ?? '...'}
              icon={GraduationCap}
              color="amber"
              change="Full Staff"
            />
          </>
        )}
      </div>

      {/* Main Content Split (Departments & Audit Logs / My Courses) */}
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {/* Left 2 Cols: Departments / Enrolled courses */}
        <div className="lg:col-span-2 glass-panel p-6 rounded-3xl border border-slate-800 space-y-6">
          <div className="flex items-center justify-between">
            <div>
              <h3 className="text-base font-bold text-white tracking-tight">
                {role === 'student' ? 'My Current Course Enrolments' : 'Academic Departments & Capacities'}
              </h3>
              <p className="text-xs text-slate-400 mt-0.5">
                {role === 'student' ? 'Active semester registrations' : 'Student distribution across disciplines'}
              </p>
            </div>
            <button
              onClick={() => onNavigate(role === 'student' ? 'courses' : 'departments')}
              className="text-xs font-semibold text-indigo-400 hover:text-indigo-300 flex items-center gap-1"
            >
              <span>View All</span>
              <ArrowUpRight className="w-3.5 h-3.5" />
            </button>
          </div>

          {role === 'student' ? (
            <div className="space-y-3">
              {stats.my_enrollments && stats.my_enrollments.length > 0 ? (
                stats.my_enrollments.map((enr) => (
                  <div
                    key={enr.id}
                    className="p-4 rounded-2xl bg-slate-900/60 border border-slate-800/80 flex items-center justify-between hover:border-slate-700 transition-colors"
                  >
                    <div>
                      <div className="flex items-center gap-2">
                        <span className="font-mono text-xs font-bold text-indigo-400">
                          {enr.course_code}
                        </span>
                        <h4 className="text-sm font-semibold text-white">{enr.course_name}</h4>
                      </div>
                      <p className="text-xs text-slate-400 mt-1">
                        Instructor: {enr.instructor_name || 'TBA'} • {enr.credit_hours} Credits
                      </p>
                    </div>
                    <div>
                      {enr.letter_grade ? (
                        <Badge variant={`grade-${enr.letter_grade}`}>Grade: {enr.letter_grade}</Badge>
                      ) : (
                        <Badge variant={enr.status}>{enr.status}</Badge>
                      )}
                    </div>
                  </div>
                ))
              ) : (
                <div className="p-8 text-center text-xs text-slate-500">
                  No courses registered yet. Click "Browse Catalog" to enroll.
                </div>
              )}
            </div>
          ) : (
            <div className="grid sm:grid-cols-2 gap-4">
              {stats.departments && stats.departments.length > 0 ? (
                stats.departments.map((dept) => (
                  <div
                    key={dept.id}
                    className="p-4 rounded-2xl bg-slate-900/50 border border-slate-800/70 hover:border-indigo-500/30 transition-all group"
                  >
                    <div className="flex items-center justify-between">
                      <span className="px-2 py-0.5 rounded-lg bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 font-mono text-[10px] font-bold">
                        {dept.code}
                      </span>
                      <span className="text-xs font-semibold text-slate-400">
                        {dept.student_count || 0} Students
                      </span>
                    </div>
                    <h4 className="font-bold text-sm text-white mt-2 group-hover:text-indigo-300 transition-colors truncate">
                      {dept.name}
                    </h4>
                    <div className="mt-3 flex items-center justify-between text-[11px] text-slate-400 pt-2 border-t border-slate-800/60">
                      <span>{dept.course_count || 0} Courses</span>
                      <span>{dept.instructor_count || 0} Faculty</span>
                    </div>
                  </div>
                ))
              ) : (
                <div className="p-6 text-center text-xs text-slate-500 col-span-2">
                  Loading departments...
                </div>
              )}
            </div>
          )}
        </div>

        {/* Right Col: Recent Activity Timeline */}
        <div className="glass-panel p-6 rounded-3xl border border-slate-800 space-y-6">
          <div className="flex items-center justify-between">
            <div>
              <h3 className="text-base font-bold text-white tracking-tight">Recent Activity</h3>
              <p className="text-xs text-slate-400 mt-0.5">Live audit timeline</p>
            </div>
            <Clock className="w-4 h-4 text-slate-500" />
          </div>

          <div className="space-y-4">
            {stats.recent_logs && stats.recent_logs.length > 0 ? (
              stats.recent_logs.slice(0, 6).map((log) => (
                <div key={log.id} className="flex items-start gap-3 text-xs">
                  <div className="w-2 h-2 rounded-full bg-indigo-400 mt-1.5 shrink-0" />
                  <div className="flex-1 overflow-hidden">
                    <p className="font-semibold text-slate-200 truncate">
                      {log.action?.replace(/_/g, ' ')}
                    </p>
                    <p className="text-[11px] text-slate-400 truncate">
                      {log.entity_type} #{log.entity_id} by {log.username || 'System'}
                    </p>
                  </div>
                  <span className="text-[10px] text-slate-500 whitespace-nowrap">
                    {log.created_at ? new Date(log.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : ''}
                  </span>
                </div>
              ))
            ) : (
              <div className="p-6 text-center text-xs text-slate-500">
                No recent security audit logs recorded.
              </div>
            )}
          </div>
        </div>
      </div>
    </div>
  );
};

export default Dashboard;
