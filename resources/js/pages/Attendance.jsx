import React, { useState, useEffect, useCallback } from 'react';
import {
  CalendarCheck,
  Calendar,
  CheckCircle2,
  Clock,
  XCircle,
  ShieldAlert,
  Save,
  Users,
} from 'lucide-react';
import api from '../services/api';
import { useAuth } from '../context/AuthContext.jsx';
import { useToast } from '../context/ToastContext.jsx';
import Badge from '../components/common/Badge.jsx';

export const Attendance = () => {
  const { role } = useAuth();
  const toast = useToast();

  const [courses, setCourses] = useState([]);
  const [selectedCourseId, setSelectedCourseId] = useState('');
  const [selectedDate, setSelectedDate] = useState(new Date().toISOString().split('T')[0]);
  const [selectedCourse, setSelectedCourse] = useState(null);
  const [roster, setRoster] = useState([]);
  const [attendanceState, setAttendanceState] = useState({});
  const [isLoading, setIsLoading] = useState(true);
  const [isSaving, setIsSaving] = useState(false);

  // Fetch Courses
  useEffect(() => {
    const fetchCourses = async () => {
      try {
        const res = await api.get('/attendance');
        const list = res.data?.courses || [];
        setCourses(list);
        if (list.length > 0) {
          setSelectedCourseId(list[0].id);
        }
      } catch (err) {
        toast.error('Failed to load courses.');
      } finally {
        setIsLoading(false);
      }
    };
    fetchCourses();
  }, [toast]);

  // Fetch Roster & existing attendance for selected Course & Date
  const fetchAttendance = useCallback(async () => {
    if (!selectedCourseId) return;
    setIsLoading(true);
    try {
      const res = await api.get(`/attendance?course_id=${selectedCourseId}&date=${selectedDate}`);
      setSelectedCourse(res.data?.course);
      const list = res.data?.roster || [];
      setRoster(list);

      // Initialize status map
      const stateMap = {};
      list.forEach((item) => {
        stateMap[item.student_id] = item.attendance_status || 'present';
      });
      setAttendanceState(stateMap);
    } catch (err) {
      toast.error('Failed to load attendance sheet.');
    } finally {
      setIsLoading(false);
    }
  }, [selectedCourseId, selectedDate, toast]);

  useEffect(() => {
    fetchAttendance();
  }, [fetchAttendance]);

  const handleStatusChange = (studentId, status) => {
    setAttendanceState((prev) => ({
      ...prev,
      [studentId]: status,
    }));
  };

  const handleMarkAll = (status) => {
    const nextState = {};
    roster.forEach((item) => {
      nextState[item.student_id] = status;
    });
    setAttendanceState(nextState);
  };

  const handleSaveAttendance = async () => {
    setIsSaving(true);
    try {
      await api.post('/attendance/record', {
        course_id: selectedCourseId,
        date: selectedDate,
        attendance: attendanceState,
      });
      toast.success('Attendance roll-call saved successfully!');
      fetchAttendance();
    } catch (err) {
      toast.error(err.message || 'Failed to save attendance.');
    } finally {
      setIsSaving(false);
    }
  };

  const canRecord = ['admin', 'instructor'].includes(role);

  // Summary counts
  const counts = Object.values(attendanceState).reduce(
    (acc, val) => {
      acc[val] = (acc[val] || 0) + 1;
      return acc;
    },
    { present: 0, late: 0, absent: 0, excused: 0 }
  );

  return (
    <div className="space-y-6 animate-fade-in">
      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h2 className="text-2xl font-extrabold text-white tracking-tight flex items-center gap-2.5">
            <CalendarCheck className="w-6 h-6 text-indigo-400" />
            <span>Daily Attendance Tracking</span>
          </h2>
          <p className="text-xs text-slate-400 mt-0.5">
            Track student attendance roll-calls, tardiness, and excused absences
          </p>
        </div>

        {/* Course & Date Picker Controls */}
        <div className="flex flex-wrap items-center gap-3">
          <select
            value={selectedCourseId}
            onChange={(e) => setSelectedCourseId(e.target.value)}
            className="px-3.5 py-2 rounded-xl bg-slate-900 border border-slate-800 text-white font-semibold text-xs focus:outline-none focus:border-indigo-500 shadow-md"
          >
            {courses.map((c) => (
              <option key={c.id} value={c.id}>
                {c.course_code} — {c.course_name}
              </option>
            ))}
          </select>

          <input
            type="date"
            value={selectedDate}
            onChange={(e) => setSelectedDate(e.target.value)}
            className="px-3.5 py-2 rounded-xl bg-slate-900 border border-slate-800 text-white font-semibold text-xs focus:outline-none focus:border-indigo-500 shadow-md"
          />
        </div>
      </div>

      {/* Summary KPI Pills */}
      <div className="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div className="glass-panel p-4 rounded-2xl border border-emerald-500/20 flex items-center justify-between">
          <div>
            <p className="text-[10px] uppercase font-bold text-slate-400">Present</p>
            <p className="text-2xl font-black text-emerald-400 mt-0.5">{counts.present}</p>
          </div>
          <CheckCircle2 className="w-6 h-6 text-emerald-400" />
        </div>

        <div className="glass-panel p-4 rounded-2xl border border-amber-500/20 flex items-center justify-between">
          <div>
            <p className="text-[10px] uppercase font-bold text-slate-400">Late</p>
            <p className="text-2xl font-black text-amber-400 mt-0.5">{counts.late}</p>
          </div>
          <Clock className="w-6 h-6 text-amber-400" />
        </div>

        <div className="glass-panel p-4 rounded-2xl border border-rose-500/20 flex items-center justify-between">
          <div>
            <p className="text-[10px] uppercase font-bold text-slate-400">Absent</p>
            <p className="text-2xl font-black text-rose-400 mt-0.5">{counts.absent}</p>
          </div>
          <XCircle className="w-6 h-6 text-rose-400" />
        </div>

        <div className="glass-panel p-4 rounded-2xl border border-purple-500/20 flex items-center justify-between">
          <div>
            <p className="text-[10px] uppercase font-bold text-slate-400">Excused</p>
            <p className="text-2xl font-black text-purple-400 mt-0.5">{counts.excused}</p>
          </div>
          <ShieldAlert className="w-6 h-6 text-purple-400" />
        </div>
      </div>

      {/* Roll-Call Sheet */}
      {isLoading ? (
        <div className="glass-panel p-12 text-center rounded-3xl border border-slate-800 text-slate-400 text-sm">
          Loading attendance roll-call...
        </div>
      ) : roster.length === 0 ? (
        <div className="glass-panel p-12 text-center rounded-3xl border border-slate-800 text-slate-400">
          <Users className="w-12 h-12 mx-auto text-slate-600 mb-3" />
          <p className="font-semibold text-white">No enrolled students in this course</p>
        </div>
      ) : (
        <div className="glass-panel rounded-3xl border border-slate-800 overflow-hidden shadow-2xl">
          {/* Quick Mark Batch Bar */}
          <div className="p-4 border-b border-slate-800/80 bg-slate-900/60 flex flex-wrap items-center justify-between gap-3">
            <div className="flex items-center gap-2">
              <span className="text-xs font-bold text-slate-400">Quick Mark All:</span>
              <button
                onClick={() => handleMarkAll('present')}
                className="px-2.5 py-1 rounded-lg bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 text-xs font-semibold hover:bg-emerald-500/20 transition-colors"
              >
                All Present
              </button>
              <button
                onClick={() => handleMarkAll('absent')}
                className="px-2.5 py-1 rounded-lg bg-rose-500/10 text-rose-400 border border-rose-500/20 text-xs font-semibold hover:bg-rose-500/20 transition-colors"
              >
                All Absent
              </button>
            </div>

            {canRecord && (
              <button
                onClick={handleSaveAttendance}
                disabled={isSaving}
                className="px-4 py-2 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-bold text-xs shadow-lg shadow-indigo-600/30 flex items-center gap-2 transition-all disabled:opacity-50"
              >
                <Save className="w-4 h-4" />
                <span>{isSaving ? 'Saving...' : 'Save Roll-Call'}</span>
              </button>
            )}
          </div>

          {/* Roster Table */}
          <div className="overflow-x-auto">
            <table className="w-full text-left text-xs text-slate-300">
              <thead className="bg-slate-900/90 text-slate-400 uppercase tracking-wider font-semibold border-b border-slate-800">
                <tr>
                  <th className="px-6 py-4">Student</th>
                  <th className="px-6 py-4">Student Code</th>
                  <th className="px-6 py-4 text-center">Status Selection</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-800/60">
                {roster.map((student) => {
                  const currentStatus = attendanceState[student.student_id] || 'present';

                  return (
                    <tr key={student.student_id} className="hover:bg-slate-800/30 transition-colors">
                      <td className="px-6 py-4">
                        <p className="font-bold text-white">
                          {student.first_name} {student.last_name}
                        </p>
                        <p className="text-[11px] text-slate-400">{student.email}</p>
                      </td>
                      <td className="px-6 py-4 font-mono font-bold text-indigo-400">
                        {student.student_code}
                      </td>
                      <td className="px-6 py-4">
                        <div className="flex items-center justify-center gap-2">
                          {[
                            { key: 'present', label: 'Present', color: 'emerald' },
                            { key: 'late', label: 'Late', color: 'amber' },
                            { key: 'absent', label: 'Absent', color: 'rose' },
                            { key: 'excused', label: 'Excused', color: 'purple' },
                          ].map((s) => {
                            const isSelected = currentStatus === s.key;
                            return (
                              <button
                                key={s.key}
                                onClick={() => handleStatusChange(student.student_id, s.key)}
                                className={`px-3 py-1.5 rounded-xl font-bold text-xs transition-all ${
                                  isSelected
                                    ? s.key === 'present'
                                      ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/30'
                                      : s.key === 'late'
                                      ? 'bg-amber-600 text-white shadow-lg shadow-amber-600/30'
                                      : s.key === 'absent'
                                      ? 'bg-rose-600 text-white shadow-lg shadow-rose-600/30'
                                      : 'bg-purple-600 text-white shadow-lg shadow-purple-600/30'
                                    : 'bg-slate-900 border border-slate-800 text-slate-400 hover:text-white hover:border-slate-700'
                                }`}
                              >
                                {s.label}
                              </button>
                            );
                          })}
                        </div>
                      </td>
                    </tr>
                  );
                })}
              </tbody>
            </table>
          </div>
        </div>
      )}
    </div>
  );
};

export default Attendance;
