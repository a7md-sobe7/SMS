import React, { useState, useEffect, useCallback } from 'react';
import {
  ClipboardList,
  Search,
  Edit3,
  Award,
  BookOpen,
  CheckCircle2,
  AlertCircle,
  Sparkles,
} from 'lucide-react';
import api from '../services/api';
import { useAuth } from '../context/AuthContext.jsx';
import { useToast } from '../context/ToastContext.jsx';
import SlideOver from '../components/common/SlideOver.jsx';
import Badge from '../components/common/Badge.jsx';

export const Grades = () => {
  const { role } = useAuth();
  const toast = useToast();

  const [courses, setCourses] = useState([]);
  const [selectedCourseId, setSelectedCourseId] = useState('');
  const [selectedCourse, setSelectedCourse] = useState(null);
  const [roster, setRoster] = useState([]);
  const [isLoading, setIsLoading] = useState(true);

  // Grade Entry Slide-Over
  const [isSlideOpen, setIsSlideOpen] = useState(false);
  const [editingStudent, setEditingStudent] = useState(null);
  const [gradeForm, setGradeForm] = useState({
    enrollment_id: '',
    assignment_grade: 0,
    midterm_grade: 0,
    final_grade: 0,
  });
  const [isSaving, setIsSaving] = useState(false);

  // Fetch Courses list
  useEffect(() => {
    const fetchCourses = async () => {
      try {
        const res = await api.get('/grades');
        const list = res.data?.courses || [];
        setCourses(list);
        if (list.length > 0) {
          setSelectedCourseId(list[0].id);
        }
      } catch (err) {
        toast.error('Failed to load courses for gradebook.');
      } finally {
        setIsLoading(false);
      }
    };
    fetchCourses();
  }, [toast]);

  // Fetch Roster with Grades when selected course changes
  const fetchRoster = useCallback(async () => {
    if (!selectedCourseId) return;
    setIsLoading(true);
    try {
      const res = await api.get(`/grades?course_id=${selectedCourseId}`);
      setSelectedCourse(res.data?.course);
      setRoster(res.data?.roster || []);
    } catch (err) {
      toast.error('Failed to load course grade roster.');
    } finally {
      setIsLoading(false);
    }
  }, [selectedCourseId, toast]);

  useEffect(() => {
    fetchRoster();
  }, [fetchRoster]);

  // Open Grade Slide-Over
  const handleOpenGradeDrawer = (student) => {
    setEditingStudent(student);
    setGradeForm({
      enrollment_id: student.enrollment_id || student.id,
      assignment_grade: Number(student.assignment_grade || 0),
      midterm_grade: Number(student.midterm_grade || 0),
      final_grade: Number(student.final_grade || 0),
    });
    setIsSlideOpen(true);
  };

  // Real-time calculation preview
  const previewTotal = (
    gradeForm.assignment_grade * 0.2 +
    gradeForm.midterm_grade * 0.3 +
    gradeForm.final_grade * 0.5
  ).toFixed(1);

  const getPreviewLetter = (total) => {
    const num = Number(total);
    if (num >= 90) return 'A';
    if (num >= 80) return 'B';
    if (num >= 70) return 'C';
    if (num >= 60) return 'D';
    return 'F';
  };

  const previewLetter = getPreviewLetter(previewTotal);

  // Save Grade
  const handleSaveGrade = async (e) => {
    e?.preventDefault();
    setIsSaving(true);
    try {
      await api.post('/grades', gradeForm);
      toast.success('Grade recorded and letter evaluation computed!');
      setIsSlideOpen(false);
      fetchRoster();
    } catch (err) {
      toast.error(err.message || 'Failed to record grade.');
    } finally {
      setIsSaving(false);
    }
  };

  const canEditGrades = ['admin', 'instructor'].includes(role);

  return (
    <div className="space-y-6 animate-fade-in">
      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h2 className="text-2xl font-extrabold text-white tracking-tight flex items-center gap-2.5">
            <ClipboardList className="w-6 h-6 text-indigo-400" />
            <span>Academic Gradebook & Evaluation</span>
          </h2>
          <p className="text-xs text-slate-400 mt-0.5">
            Weighted assessment scores (20% Assignments, 30% Midterm, 50% Final)
          </p>
        </div>

        {/* Course Selection Dropdown */}
        <div className="flex items-center gap-2">
          <label className="text-xs font-semibold text-slate-400">Select Course:</label>
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
        </div>
      </div>

      {/* Gradebook Table */}
      {isLoading ? (
        <div className="glass-panel p-12 text-center rounded-3xl border border-slate-800 text-slate-400 text-sm">
          Loading gradebook...
        </div>
      ) : roster.length === 0 ? (
        <div className="glass-panel p-12 text-center rounded-3xl border border-slate-800 text-slate-400">
          <ClipboardList className="w-12 h-12 mx-auto text-slate-600 mb-3" />
          <p className="font-semibold text-white">No enrolled students in this course</p>
        </div>
      ) : (
        <div className="glass-panel rounded-3xl border border-slate-800 overflow-hidden shadow-2xl">
          <div className="p-5 border-b border-slate-800/80 bg-slate-900/60 flex items-center justify-between">
            <div>
              <h3 className="font-bold text-white text-sm">
                {selectedCourse?.course_code} - {selectedCourse?.course_name}
              </h3>
              <p className="text-xs text-slate-400 mt-0.5">
                Instructor: {selectedCourse?.instructor_name || 'Unassigned'} • Enrolled: {roster.length} students
              </p>
            </div>
            <span className="px-3 py-1 rounded-full bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 text-xs font-semibold">
              Term 2026/2027
            </span>
          </div>

          <div className="overflow-x-auto">
            <table className="w-full text-left text-xs text-slate-300">
              <thead className="bg-slate-900/90 text-slate-400 uppercase tracking-wider font-semibold border-b border-slate-800">
                <tr>
                  <th className="px-6 py-4">Student</th>
                  <th className="px-6 py-4">Code</th>
                  <th className="px-6 py-4 text-center">Assignments (20%)</th>
                  <th className="px-6 py-4 text-center">Midterm (30%)</th>
                  <th className="px-6 py-4 text-center">Final (50%)</th>
                  <th className="px-6 py-4 text-center">Total (100%)</th>
                  <th className="px-6 py-4 text-center">Letter Grade</th>
                  {canEditGrades && <th className="px-6 py-4 text-right">Actions</th>}
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-800/60">
                {roster.map((row) => (
                  <tr
                    key={row.enrollment_id || row.id}
                    className="hover:bg-slate-800/40 transition-colors group cursor-pointer"
                    onClick={() => canEditGrades && handleOpenGradeDrawer(row)}
                  >
                    <td className="px-6 py-4">
                      <p className="font-bold text-white group-hover:text-indigo-300 transition-colors">
                        {row.first_name} {row.last_name}
                      </p>
                      <p className="text-[11px] text-slate-400">{row.email}</p>
                    </td>
                    <td className="px-6 py-4 font-mono font-bold text-indigo-400">
                      {row.student_code}
                    </td>
                    <td className="px-6 py-4 text-center font-mono">
                      {row.assignment_grade !== null ? `${row.assignment_grade}%` : '—'}
                    </td>
                    <td className="px-6 py-4 text-center font-mono">
                      {row.midterm_grade !== null ? `${row.midterm_grade}%` : '—'}
                    </td>
                    <td className="px-6 py-4 text-center font-mono">
                      {row.final_grade !== null ? `${row.final_grade}%` : '—'}
                    </td>
                    <td className="px-6 py-4 text-center font-bold text-white font-mono">
                      {row.total_grade !== null ? `${row.total_grade}%` : '—'}
                    </td>
                    <td className="px-6 py-4 text-center">
                      {row.letter_grade ? (
                        <Badge variant={`grade-${row.letter_grade}`}>{row.letter_grade}</Badge>
                      ) : (
                        <span className="text-slate-500 text-[11px]">Ungraded</span>
                      )}
                    </td>
                    {canEditGrades && (
                      <td className="px-6 py-4 text-right" onClick={(e) => e.stopPropagation()}>
                        <button
                          onClick={() => handleOpenGradeDrawer(row)}
                          className="px-3 py-1.5 rounded-xl bg-indigo-600/10 hover:bg-indigo-600/20 text-indigo-400 border border-indigo-500/30 font-semibold text-xs transition-colors inline-flex items-center gap-1.5"
                        >
                          <Edit3 className="w-3.5 h-3.5" />
                          <span>Input Scores</span>
                        </button>
                      </td>
                    )}
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      )}

      {/* ========================================================================= */}
      {/* CONTEXTUAL SLIDE-OVER: GRADE INPUT DRAWER */}
      {/* ========================================================================= */}
      <SlideOver
        isOpen={isSlideOpen}
        onClose={() => setIsSlideOpen(false)}
        title="Grade Score Input"
        subtitle={
          editingStudent
            ? `Student: ${editingStudent.first_name} ${editingStudent.last_name} (${editingStudent.student_code})`
            : ''
        }
        footer={
          <div className="flex items-center justify-end gap-3 w-full">
            <button
              type="button"
              onClick={() => setIsSlideOpen(false)}
              disabled={isSaving}
              className="px-4 py-2 rounded-xl text-xs font-semibold text-slate-400 hover:bg-slate-800 transition-colors"
            >
              Cancel
            </button>
            <button
              type="button"
              onClick={handleSaveGrade}
              disabled={isSaving}
              className="px-5 py-2 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-bold text-xs shadow-lg shadow-indigo-600/30 transition-all disabled:opacity-50"
            >
              {isSaving ? 'Saving...' : 'Record Grade'}
            </button>
          </div>
        }
      >
        <form onSubmit={handleSaveGrade} className="space-y-6">
          {/* Real-time Grade Computation Box */}
          <div className="p-5 rounded-2xl bg-gradient-to-br from-indigo-950/40 via-slate-950 to-purple-950/30 border border-indigo-500/30 text-center">
            <p className="text-xs text-indigo-300 font-semibold uppercase tracking-wider">
              Automated Weighted Evaluation
            </p>
            <div className="flex items-center justify-center gap-6 mt-3">
              <div>
                <p className="text-xs text-slate-400">Total Score</p>
                <p className="text-3xl font-black text-white">{previewTotal}%</p>
              </div>
              <div className="h-10 w-px bg-slate-800" />
              <div>
                <p className="text-xs text-slate-400">Letter Grade</p>
                <Badge variant={`grade-${previewLetter}`} size="lg">
                  Grade {previewLetter}
                </Badge>
              </div>
            </div>
          </div>

          <div className="space-y-4">
            <div>
              <div className="flex justify-between items-center mb-1">
                <label className="text-xs font-bold uppercase tracking-wider text-slate-300">
                  Assignment Score (20% weight)
                </label>
                <span className="text-xs font-mono font-bold text-indigo-400">
                  {gradeForm.assignment_grade} / 100
                </span>
              </div>
              <input
                type="number"
                min="0"
                max="100"
                step="0.5"
                value={gradeForm.assignment_grade}
                onChange={(e) =>
                  setGradeForm({ ...gradeForm, assignment_grade: Number(e.target.value) })
                }
                required
                className="w-full px-3 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono text-sm focus:outline-none focus:border-indigo-500"
              />
            </div>

            <div>
              <div className="flex justify-between items-center mb-1">
                <label className="text-xs font-bold uppercase tracking-wider text-slate-300">
                  Midterm Examination (30% weight)
                </label>
                <span className="text-xs font-mono font-bold text-indigo-400">
                  {gradeForm.midterm_grade} / 100
                </span>
              </div>
              <input
                type="number"
                min="0"
                max="100"
                step="0.5"
                value={gradeForm.midterm_grade}
                onChange={(e) =>
                  setGradeForm({ ...gradeForm, midterm_grade: Number(e.target.value) })
                }
                required
                className="w-full px-3 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono text-sm focus:outline-none focus:border-indigo-500"
              />
            </div>

            <div>
              <div className="flex justify-between items-center mb-1">
                <label className="text-xs font-bold uppercase tracking-wider text-slate-300">
                  Final Examination (50% weight)
                </label>
                <span className="text-xs font-mono font-bold text-indigo-400">
                  {gradeForm.final_grade} / 100
                </span>
              </div>
              <input
                type="number"
                min="0"
                max="100"
                step="0.5"
                value={gradeForm.final_grade}
                onChange={(e) =>
                  setGradeForm({ ...gradeForm, final_grade: Number(e.target.value) })
                }
                required
                className="w-full px-3 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono text-sm focus:outline-none focus:border-indigo-500"
              />
            </div>
          </div>
        </form>
      </SlideOver>
    </div>
  );
};

export default Grades;
