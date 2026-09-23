import React, { useState, useEffect, useCallback } from 'react';
import {
  Users,
  Search,
  Plus,
  Filter,
  Grid,
  List,
  GraduationCap,
  Mail,
  Phone,
  Calendar,
  Edit2,
  Trash2,
  Eye,
  Award,
  BookOpen,
  CheckCircle2,
} from 'lucide-react';
import api from '../services/api';
import { useAuth } from '../context/AuthContext.jsx';
import { useToast } from '../context/ToastContext.jsx';
import SlideOver from '../components/common/SlideOver.jsx';
import Badge from '../components/common/Badge.jsx';
import Modal from '../components/common/Modal.jsx';

export const Students = () => {
  const { role } = useAuth();
  const toast = useToast();

  const [students, setStudents] = useState([]);
  const [departments, setDepartments] = useState([]);
  const [isLoading, setIsLoading] = useState(true);
  const [viewMode, setViewMode] = useState('table'); // 'table' | 'grid'

  // Search & Filter State
  const [search, setSearch] = useState('');
  const [selectedDept, setSelectedDept] = useState('');
  const [selectedLevel, setSelectedLevel] = useState('');
  const [selectedStatus, setSelectedStatus] = useState('');

  // Slide-Over Drawers State
  const [detailStudentId, setDetailStudentId] = useState(null);
  const [studentDetails, setStudentDetails] = useState(null);
  const [isDetailLoading, setIsDetailLoading] = useState(false);

  const [isFormOpen, setIsFormOpen] = useState(false);
  const [formMode, setFormMode] = useState('create'); // 'create' | 'edit'
  const [formData, setFormData] = useState({
    student_code: '',
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    department_id: '',
    date_of_birth: '2004-01-01',
    gender: 'male',
    academic_level: 'freshman',
    enrollment_year: new Date().getFullYear(),
    status: 'active',
    address: '',
  });
  const [formErrors, setFormErrors] = useState({});
  const [isSaving, setIsSaving] = useState(false);

  // Delete Modal
  const [deleteTarget, setDeleteTarget] = useState(null);
  const [isDeleting, setIsDeleting] = useState(false);

  // Fetch Students list
  const fetchStudents = useCallback(async () => {
    setIsLoading(true);
    try {
      const params = new URLSearchParams();
      if (search) params.append('search', search);
      if (selectedDept) params.append('department_id', selectedDept);
      if (selectedLevel) params.append('level', selectedLevel);
      if (selectedStatus) params.append('status', selectedStatus);

      const res = await api.get(`/students?${params.toString()}`);
      setStudents(res.data || []);
    } catch (err) {
      toast.error('Failed to load students directory.');
    } finally {
      setIsLoading(false);
    }
  }, [search, selectedDept, selectedLevel, selectedStatus, toast]);

  // Fetch Departments for dropdown
  useEffect(() => {
    const fetchDepts = async () => {
      try {
        const res = await api.get('/departments');
        setDepartments(res.data || []);
      } catch (e) {
        console.error(e);
      }
    };
    fetchDepts();
  }, []);

  useEffect(() => {
    const delayDebounce = setTimeout(() => {
      fetchStudents();
    }, 250);
    return () => clearTimeout(delayDebounce);
  }, [fetchStudents]);

  // Open Student Details in Slide-Over
  const handleOpenDetail = async (id) => {
    setDetailStudentId(id);
    setIsDetailLoading(true);
    try {
      const res = await api.get(`/students/${id}`);
      setStudentDetails(res.data);
    } catch (err) {
      toast.error('Could not load student profile.');
      setDetailStudentId(null);
    } finally {
      setIsDetailLoading(false);
    }
  };

  // Open Add Student Slide-Over
  const handleOpenCreate = () => {
    setFormMode('create');
    setFormData({
      student_code: `STD${Math.floor(1000 + Math.random() * 9000)}`,
      first_name: '',
      last_name: '',
      email: '',
      phone: '',
      department_id: departments[0]?.id || '',
      date_of_birth: '2004-01-01',
      gender: 'male',
      academic_level: 'freshman',
      enrollment_year: new Date().getFullYear(),
      status: 'active',
      address: '',
    });
    setFormErrors({});
    setIsFormOpen(true);
  };

  // Open Edit Student Slide-Over
  const handleOpenEdit = (student) => {
    setFormMode('edit');
    setFormData({
      id: student.id,
      student_code: student.student_code || '',
      first_name: student.first_name || '',
      last_name: student.last_name || '',
      email: student.email || '',
      phone: student.phone || '',
      department_id: student.department_id || departments[0]?.id || '',
      date_of_birth: student.date_of_birth || '2004-01-01',
      gender: student.gender || 'male',
      academic_level: student.academic_level || 'freshman',
      enrollment_year: student.enrollment_year || new Date().getFullYear(),
      status: student.status || 'active',
      address: student.address || '',
    });
    setFormErrors({});
    setIsFormOpen(true);
  };

  // Save Student (Create or Update)
  const handleSaveStudent = async (e) => {
    e?.preventDefault();
    setIsSaving(true);
    setFormErrors({});

    try {
      if (formMode === 'create') {
        await api.post('/students', formData);
        toast.success('Student registered successfully!');
      } else {
        await api.put(`/students/${formData.id}`, formData);
        toast.success('Student profile updated successfully!');
      }
      setIsFormOpen(false);
      fetchStudents();
    } catch (err) {
      toast.error(err.message || 'Error saving student record.');
    } finally {
      setIsSaving(false);
    }
  };

  // Delete Student
  const handleDeleteConfirm = async () => {
    if (!deleteTarget) return;
    setIsDeleting(true);
    try {
      await api.delete(`/students/${deleteTarget.id}`);
      toast.success('Student record archived.');
      setDeleteTarget(null);
      fetchStudents();
    } catch (err) {
      toast.error(err.message || 'Failed to delete student.');
    } finally {
      setIsDeleting(false);
    }
  };

  const canManage = ['admin', 'registrar'].includes(role);

  return (
    <div className="space-y-6">
      {/* Top Header & Action Row */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h2 className="text-2xl font-extrabold text-white tracking-tight flex items-center gap-2.5">
            <Users className="w-6 h-6 text-indigo-400" />
            <span>Students Directory</span>
          </h2>
          <p className="text-xs text-slate-400 mt-0.5">
            Manage student registrations, academic levels, and transcripts
          </p>
        </div>

        {canManage && (
          <button
            onClick={handleOpenCreate}
            className="px-4 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-bold text-xs shadow-lg shadow-indigo-600/30 flex items-center gap-2 transition-all shrink-0"
          >
            <Plus className="w-4 h-4" />
            <span>Register New Student</span>
          </button>
        )}
      </div>

      {/* Filter & Search Bar */}
      <div className="glass-panel p-4 rounded-2xl border border-slate-800/80 flex flex-wrap items-center justify-between gap-3">
        <div className="flex-1 min-w-[240px] relative">
          <Search className="w-4 h-4 text-slate-500 absolute left-3.5 top-1/2 -translate-y-1/2" />
          <input
            type="text"
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            placeholder="Search by student code, name, or email..."
            className="w-full pl-10 pr-4 py-2 rounded-xl bg-slate-900 border border-slate-800 text-white text-xs placeholder-slate-500 focus:outline-none focus:border-indigo-500 transition-all"
          />
        </div>

        <div className="flex flex-wrap items-center gap-2">
          {/* Department Filter */}
          <select
            value={selectedDept}
            onChange={(e) => setSelectedDept(e.target.value)}
            className="px-3 py-2 rounded-xl bg-slate-900 border border-slate-800 text-slate-300 text-xs focus:outline-none focus:border-indigo-500"
          >
            <option value="">All Departments</option>
            {departments.map((d) => (
              <option key={d.id} value={d.id}>
                {d.name} ({d.code})
              </option>
            ))}
          </select>

          {/* Level Filter */}
          <select
            value={selectedLevel}
            onChange={(e) => setSelectedLevel(e.target.value)}
            className="px-3 py-2 rounded-xl bg-slate-900 border border-slate-800 text-slate-300 text-xs focus:outline-none focus:border-indigo-500"
          >
            <option value="">All Levels</option>
            <option value="freshman">Freshman</option>
            <option value="sophomore">Sophomore</option>
            <option value="junior">Junior</option>
            <option value="senior">Senior</option>
            <option value="graduate">Graduate</option>
          </select>

          {/* Status Filter */}
          <select
            value={selectedStatus}
            onChange={(e) => setSelectedStatus(e.target.value)}
            className="px-3 py-2 rounded-xl bg-slate-900 border border-slate-800 text-slate-300 text-xs focus:outline-none focus:border-indigo-500"
          >
            <option value="">All Statuses</option>
            <option value="active">Active</option>
            <option value="suspended">Suspended</option>
            <option value="graduated">Graduated</option>
            <option value="withdrawn">Withdrawn</option>
          </select>

          {/* View Toggle (Grid vs Table) */}
          <div className="flex items-center rounded-xl bg-slate-900 border border-slate-800 p-0.5">
            <button
              onClick={() => setViewMode('table')}
              className={`p-1.5 rounded-lg transition-colors ${
                viewMode === 'table' ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:text-white'
              }`}
              title="Table View"
            >
              <List className="w-4 h-4" />
            </button>
            <button
              onClick={() => setViewMode('grid')}
              className={`p-1.5 rounded-lg transition-colors ${
                viewMode === 'grid' ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:text-white'
              }`}
              title="Card Grid View"
            >
              <Grid className="w-4 h-4" />
            </button>
          </div>
        </div>
      </div>

      {/* Main Student Directory Content */}
      {isLoading ? (
        <div className="glass-panel p-12 text-center rounded-3xl border border-slate-800 text-slate-400 text-sm">
          Loading students...
        </div>
      ) : students.length === 0 ? (
        <div className="glass-panel p-12 text-center rounded-3xl border border-slate-800 text-slate-400">
          <Users className="w-12 h-12 mx-auto text-slate-600 mb-3" />
          <p className="font-semibold text-white">No students found</p>
          <p className="text-xs text-slate-500 mt-1">Try adjusting your filters or register a new student.</p>
        </div>
      ) : viewMode === 'table' ? (
        /* TABLE VIEW */
        <div className="glass-panel rounded-3xl border border-slate-800 overflow-hidden shadow-2xl">
          <div className="overflow-x-auto">
            <table className="w-full text-left text-xs text-slate-300">
              <thead className="bg-slate-900/90 text-slate-400 uppercase tracking-wider font-semibold border-b border-slate-800">
                <tr>
                  <th className="px-6 py-4">Student</th>
                  <th className="px-6 py-4">Code</th>
                  <th className="px-6 py-4">Department</th>
                  <th className="px-6 py-4">Level</th>
                  <th className="px-6 py-4">Status</th>
                  <th className="px-6 py-4 text-right">Actions</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-800/60">
                {students.map((student) => (
                  <tr
                    key={student.id}
                    className="hover:bg-slate-800/40 transition-colors group cursor-pointer"
                    onClick={() => handleOpenDetail(student.id)}
                  >
                    <td className="px-6 py-4">
                      <div className="flex items-center gap-3">
                        <div className="w-8 h-8 rounded-xl bg-gradient-to-tr from-indigo-600/30 to-purple-600/30 border border-indigo-500/30 flex items-center justify-center font-bold text-indigo-300 text-xs">
                          {student.first_name?.[0]}
                          {student.last_name?.[0]}
                        </div>
                        <div>
                          <p className="font-bold text-white group-hover:text-indigo-300 transition-colors">
                            {student.first_name} {student.last_name}
                          </p>
                          <p className="text-[11px] text-slate-400">{student.email}</p>
                        </div>
                      </div>
                    </td>
                    <td className="px-6 py-4 font-mono font-bold text-indigo-400">
                      {student.student_code}
                    </td>
                    <td className="px-6 py-4">
                      <span className="text-slate-200 font-medium">
                        {student.department_name || '—'}
                      </span>
                    </td>
                    <td className="px-6 py-4">
                      <span className="capitalize text-slate-300 font-medium">
                        {student.academic_level}
                      </span>
                    </td>
                    <td className="px-6 py-4">
                      <Badge variant={student.status}>{student.status}</Badge>
                    </td>
                    <td className="px-6 py-4 text-right" onClick={(e) => e.stopPropagation()}>
                      <div className="flex items-center justify-end gap-1.5">
                        <button
                          onClick={() => handleOpenDetail(student.id)}
                          className="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition-colors"
                          title="View Profile Drawer"
                        >
                          <Eye className="w-4 h-4" />
                        </button>
                        {canManage && (
                          <>
                            <button
                              onClick={() => handleOpenEdit(student)}
                              className="p-1.5 rounded-lg text-indigo-400 hover:text-indigo-300 hover:bg-indigo-500/10 transition-colors"
                              title="Edit in Drawer"
                            >
                              <Edit2 className="w-4 h-4" />
                            </button>
                            {role === 'admin' && (
                              <button
                                onClick={() => setDeleteTarget(student)}
                                className="p-1.5 rounded-lg text-rose-400 hover:text-rose-300 hover:bg-rose-500/10 transition-colors"
                                title="Delete"
                              >
                                <Trash2 className="w-4 h-4" />
                              </button>
                            )}
                          </>
                        )}
                      </div>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      ) : (
        /* GRID CARD VIEW */
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
          {students.map((student) => (
            <div
              key={student.id}
              onClick={() => handleOpenDetail(student.id)}
              className="glass-card-interactive p-5 rounded-3xl border border-slate-800/80 flex flex-col justify-between cursor-pointer group"
            >
              <div>
                <div className="flex items-start justify-between gap-3">
                  <div className="flex items-center gap-3">
                    <div className="w-11 h-11 rounded-2xl bg-gradient-to-tr from-indigo-600/30 to-purple-600/30 border border-indigo-500/40 flex items-center justify-center font-bold text-indigo-300 text-sm">
                      {student.first_name?.[0]}
                      {student.last_name?.[0]}
                    </div>
                    <div>
                      <h4 className="font-bold text-white group-hover:text-indigo-300 transition-colors">
                        {student.first_name} {student.last_name}
                      </h4>
                      <p className="font-mono text-[11px] text-indigo-400 font-bold">
                        {student.student_code}
                      </p>
                    </div>
                  </div>
                  <Badge variant={student.status}>{student.status}</Badge>
                </div>

                <div className="mt-4 space-y-2 text-xs text-slate-300">
                  <div className="flex items-center gap-2 text-slate-400">
                    <Mail className="w-3.5 h-3.5 shrink-0" />
                    <span className="truncate">{student.email}</span>
                  </div>
                  <div className="flex items-center gap-2 text-slate-400">
                    <GraduationCap className="w-3.5 h-3.5 shrink-0" />
                    <span>
                      {student.department_name} •{' '}
                      <span className="capitalize">{student.academic_level}</span>
                    </span>
                  </div>
                </div>
              </div>

              <div
                className="mt-5 pt-3 border-t border-slate-800/70 flex items-center justify-between"
                onClick={(e) => e.stopPropagation()}
              >
                <button
                  onClick={() => handleOpenDetail(student.id)}
                  className="text-xs font-semibold text-indigo-400 hover:text-indigo-300 flex items-center gap-1"
                >
                  <span>View Drawer</span>
                  <Eye className="w-3 h-3" />
                </button>

                {canManage && (
                  <div className="flex items-center gap-1">
                    <button
                      onClick={() => handleOpenEdit(student)}
                      className="p-1 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition-colors"
                    >
                      <Edit2 className="w-3.5 h-3.5" />
                    </button>
                    {role === 'admin' && (
                      <button
                        onClick={() => setDeleteTarget(student)}
                        className="p-1 rounded-lg text-rose-400 hover:text-rose-300 hover:bg-rose-500/10 transition-colors"
                      >
                        <Trash2 className="w-3.5 h-3.5" />
                      </button>
                    )}
                  </div>
                )}
              </div>
            </div>
          ))}
        </div>
      )}

      {/* ========================================================================= */}
      {/* 1. CONTEXTUAL SLIDE-OVER DRAWER: STUDENT DETAIL & TRANSCRIPT */}
      {/* ========================================================================= */}
      <SlideOver
        isOpen={Boolean(detailStudentId)}
        onClose={() => setDetailStudentId(null)}
        title={
          studentDetails
            ? `${studentDetails.first_name} ${studentDetails.last_name}`
            : 'Student Profile'
        }
        subtitle={
          studentDetails
            ? `ID: ${studentDetails.student_code} • Department: ${studentDetails.department_name}`
            : ''
        }
        footer={
          canManage && studentDetails ? (
            <div className="flex items-center gap-2">
              <button
                onClick={() => {
                  const s = studentDetails;
                  setDetailStudentId(null);
                  handleOpenEdit(s);
                }}
                className="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-md transition-colors flex items-center gap-2"
              >
                <Edit2 className="w-3.5 h-3.5" />
                <span>Edit Profile</span>
              </button>
            </div>
          ) : null
        }
      >
        {isDetailLoading || !studentDetails ? (
          <div className="py-12 text-center text-slate-400 text-xs">
            Loading student academic profile...
          </div>
        ) : (
          <div className="space-y-6 animate-fade-in">
            {/* Student Header Summary Card */}
            <div className="p-5 rounded-2xl bg-slate-950/60 border border-slate-800 flex items-center justify-between">
              <div className="flex items-center gap-3.5">
                <div className="w-14 h-14 rounded-2xl bg-gradient-to-tr from-indigo-600 to-purple-600 flex items-center justify-center text-white font-extrabold text-lg shadow-lg shadow-indigo-600/20">
                  {studentDetails.first_name?.[0]}
                  {studentDetails.last_name?.[0]}
                </div>
                <div>
                  <h3 className="font-extrabold text-lg text-white">
                    {studentDetails.first_name} {studentDetails.last_name}
                  </h3>
                  <p className="text-xs text-slate-400">{studentDetails.email}</p>
                </div>
              </div>
              <Badge variant={studentDetails.status}>{studentDetails.status}</Badge>
            </div>

            {/* GPA & Performance Metrics */}
            <div>
              <h4 className="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3 flex items-center gap-1.5">
                <Award className="w-4 h-4 text-indigo-400" /> Academic GPA Summary
              </h4>
              <div className="grid grid-cols-3 gap-3">
                <div className="p-3.5 rounded-xl bg-slate-950/40 border border-slate-800 text-center">
                  <p className="text-[10px] text-slate-400 font-semibold uppercase">Average Score</p>
                  <p className="text-xl font-extrabold text-white mt-1">
                    {Number(studentDetails.gpa_summary?.average_numerical_grade || 0).toFixed(1)}%
                  </p>
                </div>
                <div className="p-3.5 rounded-xl bg-slate-950/40 border border-slate-800 text-center">
                  <p className="text-[10px] text-slate-400 font-semibold uppercase">Graded Classes</p>
                  <p className="text-xl font-extrabold text-white mt-1">
                    {studentDetails.gpa_summary?.total_graded_courses || 0}
                  </p>
                </div>
                <div className="p-3.5 rounded-xl bg-slate-950/40 border border-slate-800 text-center">
                  <p className="text-[10px] text-slate-400 font-semibold uppercase">Credits</p>
                  <p className="text-xl font-extrabold text-white mt-1">
                    {studentDetails.gpa_summary?.total_credits_earned || 0} hrs
                  </p>
                </div>
              </div>
            </div>

            {/* General Profile Details */}
            <div>
              <h4 className="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3 flex items-center gap-1.5">
                <Users className="w-4 h-4 text-indigo-400" /> Personal & Enrollment Data
              </h4>
              <div className="p-4 rounded-2xl bg-slate-950/40 border border-slate-800/80 space-y-2.5 text-xs">
                <div className="flex justify-between py-1 border-b border-slate-800/60">
                  <span className="text-slate-400">Academic Level</span>
                  <span className="capitalize font-semibold text-white">{studentDetails.academic_level}</span>
                </div>
                <div className="flex justify-between py-1 border-b border-slate-800/60">
                  <span className="text-slate-400">Enrollment Year</span>
                  <span className="font-semibold text-white">{studentDetails.enrollment_year}</span>
                </div>
                <div className="flex justify-between py-1 border-b border-slate-800/60">
                  <span className="text-slate-400">Phone</span>
                  <span className="font-semibold text-white">{studentDetails.phone || '—'}</span>
                </div>
                <div className="flex justify-between py-1 border-b border-slate-800/60">
                  <span className="text-slate-400">Gender</span>
                  <span className="capitalize font-semibold text-white">{studentDetails.gender || '—'}</span>
                </div>
                <div className="flex justify-between py-1">
                  <span className="text-slate-400">Date of Birth</span>
                  <span className="font-semibold text-white">{studentDetails.date_of_birth || '—'}</span>
                </div>
              </div>
            </div>

            {/* Enrolled Courses & Grades */}
            <div>
              <h4 className="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3 flex items-center gap-1.5">
                <BookOpen className="w-4 h-4 text-indigo-400" /> Course Transcripts & Scores
              </h4>
              <div className="space-y-2">
                {studentDetails.enrollments && studentDetails.enrollments.length > 0 ? (
                  studentDetails.enrollments.map((enr) => (
                    <div
                      key={enr.id}
                      className="p-3.5 rounded-xl bg-slate-950/40 border border-slate-800 flex items-center justify-between"
                    >
                      <div>
                        <div className="flex items-center gap-2">
                          <span className="font-mono text-xs font-bold text-indigo-400">
                            {enr.course_code}
                          </span>
                          <span className="font-semibold text-white text-xs">{enr.course_name}</span>
                        </div>
                        <p className="text-[11px] text-slate-400 mt-0.5">
                          {enr.semester} {enr.academic_year} • {enr.credit_hours} Credits
                        </p>
                      </div>

                      <div className="text-right">
                        {enr.letter_grade ? (
                          <Badge variant={`grade-${enr.letter_grade}`}>
                            {enr.letter_grade} ({enr.total_grade}%)
                          </Badge>
                        ) : (
                          <Badge variant={enr.status}>{enr.status}</Badge>
                        )}
                      </div>
                    </div>
                  ))
                ) : (
                  <p className="p-4 text-center text-xs text-slate-500 bg-slate-950/30 rounded-xl">
                    No course registrations recorded for this student.
                  </p>
                )}
              </div>
            </div>
          </div>
        )}
      </SlideOver>

      {/* ========================================================================= */}
      {/* 2. CONTEXTUAL SLIDE-OVER DRAWER: ADD / EDIT STUDENT FORM */}
      {/* ========================================================================= */}
      <SlideOver
        isOpen={isFormOpen}
        onClose={() => setIsFormOpen(false)}
        title={formMode === 'create' ? 'Register New Student' : 'Edit Student Record'}
        subtitle={
          formMode === 'create'
            ? 'Complete student academic registration without reloading the page.'
            : `Updating student profile ID: ${formData.student_code}`
        }
        footer={
          <div className="flex items-center justify-end gap-3 w-full">
            <button
              type="button"
              onClick={() => setIsFormOpen(false)}
              disabled={isSaving}
              className="px-4 py-2 rounded-xl text-xs font-semibold text-slate-400 hover:bg-slate-800 transition-colors"
            >
              Cancel
            </button>
            <button
              type="button"
              onClick={handleSaveStudent}
              disabled={isSaving}
              className="px-5 py-2 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-bold text-xs shadow-lg shadow-indigo-600/30 transition-all disabled:opacity-50"
            >
              {isSaving ? 'Saving...' : formMode === 'create' ? 'Register Student' : 'Update Student'}
            </button>
          </div>
        }
      >
        <form onSubmit={handleSaveStudent} className="space-y-4">
          <div className="grid grid-cols-2 gap-3">
            <div>
              <label className="block text-[11px] font-bold uppercase tracking-wider text-slate-300 mb-1">
                Student Code *
              </label>
              <input
                type="text"
                value={formData.student_code}
                onChange={(e) => setFormData({ ...formData, student_code: e.target.value })}
                required
                className="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono text-xs focus:outline-none focus:border-indigo-500"
              />
            </div>
            <div>
              <label className="block text-[11px] font-bold uppercase tracking-wider text-slate-300 mb-1">
                Academic Level *
              </label>
              <select
                value={formData.academic_level}
                onChange={(e) => setFormData({ ...formData, academic_level: e.target.value })}
                className="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:outline-none focus:border-indigo-500"
              >
                <option value="freshman">Freshman</option>
                <option value="sophomore">Sophomore</option>
                <option value="junior">Junior</option>
                <option value="senior">Senior</option>
                <option value="graduate">Graduate</option>
              </select>
            </div>
          </div>

          <div className="grid grid-cols-2 gap-3">
            <div>
              <label className="block text-[11px] font-bold uppercase tracking-wider text-slate-300 mb-1">
                First Name *
              </label>
              <input
                type="text"
                value={formData.first_name}
                onChange={(e) => setFormData({ ...formData, first_name: e.target.value })}
                required
                placeholder="e.g. Alan"
                className="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:outline-none focus:border-indigo-500"
              />
            </div>
            <div>
              <label className="block text-[11px] font-bold uppercase tracking-wider text-slate-300 mb-1">
                Last Name *
              </label>
              <input
                type="text"
                value={formData.last_name}
                onChange={(e) => setFormData({ ...formData, last_name: e.target.value })}
                required
                placeholder="e.g. Turing"
                className="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:outline-none focus:border-indigo-500"
              />
            </div>
          </div>

          <div className="grid grid-cols-2 gap-3">
            <div>
              <label className="block text-[11px] font-bold uppercase tracking-wider text-slate-300 mb-1">
                Email Address *
              </label>
              <input
                type="email"
                value={formData.email}
                onChange={(e) => setFormData({ ...formData, email: e.target.value })}
                required
                placeholder="student@sms.edu"
                className="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:outline-none focus:border-indigo-500"
              />
            </div>
            <div>
              <label className="block text-[11px] font-bold uppercase tracking-wider text-slate-300 mb-1">
                Phone Number
              </label>
              <input
                type="tel"
                value={formData.phone}
                onChange={(e) => setFormData({ ...formData, phone: e.target.value })}
                placeholder="+1 (555) 000-0000"
                className="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:outline-none focus:border-indigo-500"
              />
            </div>
          </div>

          <div className="grid grid-cols-2 gap-3">
            <div>
              <label className="block text-[11px] font-bold uppercase tracking-wider text-slate-300 mb-1">
                Department *
              </label>
              <select
                value={formData.department_id}
                onChange={(e) => setFormData({ ...formData, department_id: e.target.value })}
                required
                className="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:outline-none focus:border-indigo-500"
              >
                {departments.map((d) => (
                  <option key={d.id} value={d.id}>
                    {d.name} ({d.code})
                  </option>
                ))}
              </select>
            </div>
            <div>
              <label className="block text-[11px] font-bold uppercase tracking-wider text-slate-300 mb-1">
                Status *
              </label>
              <select
                value={formData.status}
                onChange={(e) => setFormData({ ...formData, status: e.target.value })}
                className="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:outline-none focus:border-indigo-500"
              >
                <option value="active">Active</option>
                <option value="suspended">Suspended</option>
                <option value="graduated">Graduated</option>
                <option value="withdrawn">Withdrawn</option>
              </select>
            </div>
          </div>

          <div className="grid grid-cols-2 gap-3">
            <div>
              <label className="block text-[11px] font-bold uppercase tracking-wider text-slate-300 mb-1">
                Date of Birth *
              </label>
              <input
                type="date"
                value={formData.date_of_birth}
                onChange={(e) => setFormData({ ...formData, date_of_birth: e.target.value })}
                required
                className="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:outline-none focus:border-indigo-500"
              />
            </div>
            <div>
              <label className="block text-[11px] font-bold uppercase tracking-wider text-slate-300 mb-1">
                Gender *
              </label>
              <select
                value={formData.gender}
                onChange={(e) => setFormData({ ...formData, gender: e.target.value })}
                className="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:outline-none focus:border-indigo-500"
              >
                <option value="male">Male</option>
                <option value="female">Female</option>
                <option value="other">Other</option>
              </select>
            </div>
          </div>
        </form>
      </SlideOver>

      {/* Delete Confirmation Modal */}
      <Modal
        isOpen={Boolean(deleteTarget)}
        onClose={() => setDeleteTarget(null)}
        onConfirm={handleDeleteConfirm}
        title="Archive Student Record"
        message={`Are you sure you want to archive student ${deleteTarget?.first_name} ${deleteTarget?.last_name} (${deleteTarget?.student_code})? This will preserve historic transcript records.`}
        confirmText="Archive Student"
        isLoading={isDeleting}
      />
    </div>
  );
};

export default Students;
