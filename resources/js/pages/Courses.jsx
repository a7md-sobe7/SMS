import React, { useState, useEffect, useCallback } from 'react';
import {
  BookOpen,
  Search,
  Plus,
  Users,
  GraduationCap,
  Clock,
  Edit2,
  Trash2,
  Eye,
  CheckCircle2,
  Layers,
  Sparkles,
  UserPlus,
} from 'lucide-react';
import api from '../services/api';
import { useAuth } from '../context/AuthContext.jsx';
import { useToast } from '../context/ToastContext.jsx';
import SlideOver from '../components/common/SlideOver.jsx';
import Badge from '../components/common/Badge.jsx';
import Modal from '../components/common/Modal.jsx';

export const Courses = () => {
  const { role, user } = useAuth();
  const toast = useToast();

  const [courses, setCourses] = useState([]);
  const [departments, setDepartments] = useState([]);
  const [instructors, setInstructors] = useState([]);
  const [allStudents, setAllStudents] = useState([]);
  const [isLoading, setIsLoading] = useState(true);

  // Filter State
  const [search, setSearch] = useState('');
  const [selectedDept, setSelectedDept] = useState('');
  const [selectedSemester, setSelectedSemester] = useState('');

  // Course Details Slide-Over
  const [detailCourseId, setDetailCourseId] = useState(null);
  const [courseDetails, setCourseDetails] = useState(null);
  const [courseRoster, setCourseRoster] = useState([]);
  const [isDetailLoading, setIsDetailLoading] = useState(false);
  const [enrollStudentId, setEnrollStudentId] = useState('');
  const [isEnrolling, setIsEnrolling] = useState(false);

  // Add / Edit Course Slide-Over
  const [isFormOpen, setIsFormOpen] = useState(false);
  const [formMode, setFormMode] = useState('create');
  const [formData, setFormData] = useState({
    course_code: '',
    course_name: '',
    department_id: '',
    instructor_id: '',
    credit_hours: 3,
    semester: 'Fall',
    academic_year: new Date().getFullYear(),
    capacity: 35,
    description: '',
  });
  const [isSaving, setIsSaving] = useState(false);

  // Delete Modal
  const [deleteTarget, setDeleteTarget] = useState(null);
  const [isDeleting, setIsDeleting] = useState(false);

  // Fetch Courses
  const fetchCourses = useCallback(async () => {
    setIsLoading(true);
    try {
      const params = new URLSearchParams();
      if (selectedDept) params.append('department_id', selectedDept);
      const res = await api.get(`/courses?${params.toString()}`);
      let list = res.data || [];
      if (search) {
        list = list.filter(
          (c) =>
            c.course_code.toLowerCase().includes(search.toLowerCase()) ||
            c.course_name.toLowerCase().includes(search.toLowerCase()) ||
            c.department_name?.toLowerCase().includes(search.toLowerCase())
        );
      }
      if (selectedSemester) {
        list = list.filter((c) => c.semester === selectedSemester);
      }
      setCourses(list);
    } catch (err) {
      toast.error('Failed to load courses catalog.');
    } finally {
      setIsLoading(false);
    }
  }, [search, selectedDept, selectedSemester, toast]);

  // Fetch auxiliary resources
  useEffect(() => {
    const fetchAuxData = async () => {
      try {
        const [deptRes, instRes, stdRes] = await Promise.all([
          api.get('/departments'),
          api.get('/instructors'),
          api.get('/students'),
        ]);
        setDepartments(deptRes.data || []);
        setInstructors(instRes.data || []);
        setAllStudents(stdRes.data || []);
      } catch (e) {
        console.error(e);
      }
    };
    fetchAuxData();
  }, []);

  useEffect(() => {
    fetchCourses();
  }, [fetchCourses]);

  // Open Course Detail in Slide-Over (with Roster)
  const handleOpenDetail = async (courseId) => {
    setDetailCourseId(courseId);
    setIsDetailLoading(true);
    try {
      const [cRes, rosterRes] = await Promise.all([
        api.get(`/courses/${courseId}`),
        api.get(`/enrollments?course_id=${courseId}`),
      ]);
      setCourseDetails(cRes.data);
      setCourseRoster(rosterRes.data?.roster || []);
    } catch (err) {
      toast.error('Could not load course details.');
      setDetailCourseId(null);
    } finally {
      setIsDetailLoading(false);
    }
  };

  // Enroll Student from Slide-Over
  const handleEnrollStudent = async () => {
    if (!enrollStudentId || !detailCourseId) {
      toast.warning('Please select a student to enroll.');
      return;
    }
    setIsEnrolling(true);
    try {
      await api.post('/enrollments', {
        student_id: enrollStudentId,
        course_id: detailCourseId,
      });
      toast.success('Student enrolled successfully!');
      setEnrollStudentId('');
      // Reload roster & course
      const rosterRes = await api.get(`/enrollments?course_id=${detailCourseId}`);
      setCourseRoster(rosterRes.data?.roster || []);
      fetchCourses();
    } catch (err) {
      toast.error(err.message || 'Enrollment failed.');
    } finally {
      setIsEnrolling(false);
    }
  };

  // Drop Student from Slide-Over
  const handleDropStudent = async (enrollmentId) => {
    try {
      await api.post(`/enrollments/${enrollmentId}/drop`);
      toast.success('Course registration dropped.');
      const rosterRes = await api.get(`/enrollments?course_id=${detailCourseId}`);
      setCourseRoster(rosterRes.data?.roster || []);
      fetchCourses();
    } catch (err) {
      toast.error(err.message || 'Failed to drop course.');
    }
  };

  // Open Create Slide-Over
  const handleOpenCreate = () => {
    setFormMode('create');
    setFormData({
      course_code: '',
      course_name: '',
      department_id: departments[0]?.id || '',
      instructor_id: instructors[0]?.id || '',
      credit_hours: 3,
      semester: 'Fall',
      academic_year: new Date().getFullYear(),
      capacity: 35,
      description: '',
    });
    setIsFormOpen(true);
  };

  // Open Edit Slide-Over
  const handleOpenEdit = (c) => {
    setFormMode('edit');
    setFormData({
      id: c.id,
      course_code: c.course_code || '',
      course_name: c.course_name || '',
      department_id: c.department_id || departments[0]?.id || '',
      instructor_id: c.instructor_id || '',
      credit_hours: c.credit_hours || 3,
      semester: c.semester || 'Fall',
      academic_year: c.academic_year || new Date().getFullYear(),
      capacity: c.capacity || 35,
      description: c.description || '',
    });
    setIsFormOpen(true);
  };

  // Save Course
  const handleSaveCourse = async (e) => {
    e?.preventDefault();
    setIsSaving(true);
    try {
      if (formMode === 'create') {
        await api.post('/courses', formData);
        toast.success('Course created successfully!');
      } else {
        await api.put(`/courses/${formData.id}`, formData);
        toast.success('Course updated successfully!');
      }
      setIsFormOpen(false);
      fetchCourses();
    } catch (err) {
      toast.error(err.message || 'Error saving course.');
    } finally {
      setIsSaving(false);
    }
  };

  // Delete Course
  const handleDeleteConfirm = async () => {
    if (!deleteTarget) return;
    setIsDeleting(true);
    try {
      await api.delete(`/courses/${deleteTarget.id}`);
      toast.success('Course offering deleted.');
      setDeleteTarget(null);
      fetchCourses();
    } catch (err) {
      toast.error(err.message || 'Failed to delete course.');
    } finally {
      setIsDeleting(false);
    }
  };

  const canManage = ['admin', 'registrar'].includes(role);

  return (
    <div className="space-y-6 animate-fade-in">
      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h2 className="text-2xl font-extrabold text-white tracking-tight flex items-center gap-2.5">
            <BookOpen className="w-6 h-6 text-indigo-400" />
            <span>Course Catalog & Curriculum</span>
          </h2>
          <p className="text-xs text-slate-400 mt-0.5">
            Browse course schedules, instructor assignments, and live seat capacities
          </p>
        </div>

        {canManage && (
          <button
            onClick={handleOpenCreate}
            className="px-4 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-bold text-xs shadow-lg shadow-indigo-600/30 flex items-center gap-2 transition-all shrink-0"
          >
            <Plus className="w-4 h-4" />
            <span>Add Course Offering</span>
          </button>
        )}
      </div>

      {/* Filter Row */}
      <div className="glass-panel p-4 rounded-2xl border border-slate-800/80 flex flex-wrap items-center justify-between gap-3">
        <div className="flex-1 min-w-[240px] relative">
          <Search className="w-4 h-4 text-slate-500 absolute left-3.5 top-1/2 -translate-y-1/2" />
          <input
            type="text"
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            placeholder="Search by code, title, or discipline..."
            className="w-full pl-10 pr-4 py-2 rounded-xl bg-slate-900 border border-slate-800 text-white text-xs placeholder-slate-500 focus:outline-none focus:border-indigo-500"
          />
        </div>

        <div className="flex flex-wrap items-center gap-2">
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

          <select
            value={selectedSemester}
            onChange={(e) => setSelectedSemester(e.target.value)}
            className="px-3 py-2 rounded-xl bg-slate-900 border border-slate-800 text-slate-300 text-xs focus:outline-none focus:border-indigo-500"
          >
            <option value="">All Semesters</option>
            <option value="Fall">Fall</option>
            <option value="Spring">Spring</option>
            <option value="Summer">Summer</option>
          </select>
        </div>
      </div>

      {/* Courses Cards Grid */}
      {isLoading ? (
        <div className="glass-panel p-12 text-center rounded-3xl border border-slate-800 text-slate-400 text-sm">
          Loading course catalog...
        </div>
      ) : courses.length === 0 ? (
        <div className="glass-panel p-12 text-center rounded-3xl border border-slate-800 text-slate-400">
          <BookOpen className="w-12 h-12 mx-auto text-slate-600 mb-3" />
          <p className="font-semibold text-white">No courses match your filter</p>
        </div>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
          {courses.map((course) => {
            const enrolled = Number(course.enrolled_count || 0);
            const capacity = Number(course.capacity || 30);
            const percentFilled = Math.min(100, Math.round((enrolled / capacity) * 100));

            return (
              <div
                key={course.id}
                onClick={() => handleOpenDetail(course.id)}
                className="glass-card-interactive p-6 rounded-3xl border border-slate-800/80 flex flex-col justify-between cursor-pointer group"
              >
                <div>
                  <div className="flex items-start justify-between gap-3">
                    <span className="px-2.5 py-1 rounded-xl bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 font-mono font-bold text-xs">
                      {course.course_code}
                    </span>
                    <span className="px-2 py-0.5 rounded-lg bg-slate-800 text-slate-300 text-[10px] font-semibold">
                      {course.credit_hours} Credits
                    </span>
                  </div>

                  <h3 className="text-base font-bold text-white mt-3 group-hover:text-indigo-300 transition-colors line-clamp-1">
                    {course.course_name}
                  </h3>

                  <p className="text-xs text-slate-400 mt-1 line-clamp-2">
                    {course.description || 'Comprehensive university curriculum offering with practical lab components.'}
                  </p>

                  <div className="mt-4 pt-3 border-t border-slate-800/80 space-y-2 text-xs">
                    <div className="flex items-center justify-between text-slate-400">
                      <span className="flex items-center gap-1.5">
                        <GraduationCap className="w-3.5 h-3.5 text-indigo-400" />
                        <span>Faculty:</span>
                      </span>
                      <span className="font-semibold text-slate-200 truncate max-w-[140px]">
                        {course.instructor_name || 'Unassigned'}
                      </span>
                    </div>

                    <div className="flex items-center justify-between text-slate-400">
                      <span className="flex items-center gap-1.5">
                        <Clock className="w-3.5 h-3.5 text-indigo-400" />
                        <span>Term:</span>
                      </span>
                      <span className="font-semibold text-slate-200">
                        {course.semester} {course.academic_year}
                      </span>
                    </div>
                  </div>

                  {/* Seat Capacity Progress Bar */}
                  <div className="mt-4">
                    <div className="flex items-center justify-between text-[11px] mb-1.5">
                      <span className="text-slate-400 font-medium">Seat Capacity</span>
                      <span className="font-bold text-white">
                        {enrolled} / {capacity} ({percentFilled}%)
                      </span>
                    </div>
                    <div className="w-full h-2 rounded-full bg-slate-800 overflow-hidden">
                      <div
                        className={`h-full rounded-full transition-all duration-500 ${
                          percentFilled >= 90
                            ? 'bg-rose-500'
                            : percentFilled >= 70
                            ? 'bg-amber-500'
                            : 'bg-indigo-500'
                        }`}
                        style={{ width: `${percentFilled}%` }}
                      />
                    </div>
                  </div>
                </div>

                <div
                  className="mt-5 pt-3 border-t border-slate-800/80 flex items-center justify-between"
                  onClick={(e) => e.stopPropagation()}
                >
                  <button
                    onClick={() => handleOpenDetail(course.id)}
                    className="text-xs font-semibold text-indigo-400 hover:text-indigo-300 flex items-center gap-1.5"
                  >
                    <span>View Roster Drawer</span>
                    <Eye className="w-3.5 h-3.5" />
                  </button>

                  {canManage && (
                    <div className="flex items-center gap-1">
                      <button
                        onClick={() => handleOpenEdit(course)}
                        className="p-1 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition-colors"
                      >
                        <Edit2 className="w-3.5 h-3.5" />
                      </button>
                      {role === 'admin' && (
                        <button
                          onClick={() => setDeleteTarget(course)}
                          className="p-1 rounded-lg text-rose-400 hover:text-rose-300 hover:bg-rose-500/10 transition-colors"
                        >
                          <Trash2 className="w-3.5 h-3.5" />
                        </button>
                      )}
                    </div>
                  )}
                </div>
              </div>
            );
          })}
        </div>
      )}

      {/* ========================================================================= */}
      {/* 1. SLIDE-OVER: COURSE DETAILS & ENROLLED ROSTER */}
      {/* ========================================================================= */}
      <SlideOver
        isOpen={Boolean(detailCourseId)}
        onClose={() => setDetailCourseId(null)}
        title={courseDetails?.course_name || 'Course Offering'}
        subtitle={
          courseDetails
            ? `Code: ${courseDetails.course_code} • Department: ${courseDetails.department_name}`
            : ''
        }
      >
        {isDetailLoading || !courseDetails ? (
          <div className="py-12 text-center text-slate-400 text-xs">
            Loading course details & enrollment roster...
          </div>
        ) : (
          <div className="space-y-6">
            {/* Quick Stats Banner */}
            <div className="grid grid-cols-3 gap-3">
              <div className="p-3.5 rounded-xl bg-slate-950/60 border border-slate-800 text-center">
                <p className="text-[10px] text-slate-400 font-semibold uppercase">Enrolled</p>
                <p className="text-xl font-extrabold text-white mt-1">
                  {courseRoster.length} / {courseDetails.capacity}
                </p>
              </div>
              <div className="p-3.5 rounded-xl bg-slate-950/60 border border-slate-800 text-center">
                <p className="text-[10px] text-slate-400 font-semibold uppercase">Credit Hours</p>
                <p className="text-xl font-extrabold text-white mt-1">
                  {courseDetails.credit_hours}
                </p>
              </div>
              <div className="p-3.5 rounded-xl bg-slate-950/60 border border-slate-800 text-center">
                <p className="text-[10px] text-slate-400 font-semibold uppercase">Instructor</p>
                <p className="text-xs font-bold text-white mt-2 truncate">
                  {courseDetails.instructor_name || 'Unassigned'}
                </p>
              </div>
            </div>

            {/* Quick Enroll Student Form (for Admin/Registrar) */}
            {canManage && (
              <div className="p-4 rounded-2xl bg-indigo-950/30 border border-indigo-500/20 space-y-3">
                <h4 className="text-xs font-bold uppercase tracking-wider text-indigo-300 flex items-center gap-1.5">
                  <UserPlus className="w-4 h-4" /> Enroll Student to Course
                </h4>
                <div className="flex gap-2">
                  <select
                    value={enrollStudentId}
                    onChange={(e) => setEnrollStudentId(e.target.value)}
                    className="flex-1 px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:outline-none focus:border-indigo-500"
                  >
                    <option value="">Select a student...</option>
                    {allStudents.map((s) => (
                      <option key={s.id} value={s.id}>
                        {s.last_name}, {s.first_name} ({s.student_code})
                      </option>
                    ))}
                  </select>
                  <button
                    onClick={handleEnrollStudent}
                    disabled={isEnrolling}
                    className="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold transition-colors disabled:opacity-50"
                  >
                    {isEnrolling ? 'Enrolling...' : 'Enroll'}
                  </button>
                </div>
              </div>
            )}

            {/* Enrolled Roster */}
            <div>
              <h4 className="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3 flex items-center gap-1.5">
                <Users className="w-4 h-4 text-indigo-400" /> Active Student Roster ({courseRoster.length})
              </h4>
              <div className="space-y-2">
                {courseRoster.length > 0 ? (
                  courseRoster.map((item) => (
                    <div
                      key={item.enrollment_id || item.id}
                      className="p-3.5 rounded-xl bg-slate-950/40 border border-slate-800/80 flex items-center justify-between"
                    >
                      <div>
                        <p className="font-bold text-xs text-white">
                          {item.first_name} {item.last_name}
                        </p>
                        <p className="text-[11px] text-slate-400">
                          {item.student_code} • {item.email}
                        </p>
                      </div>

                      <div className="flex items-center gap-3">
                        {item.letter_grade ? (
                          <Badge variant={`grade-${item.letter_grade}`}>{item.letter_grade}</Badge>
                        ) : (
                          <Badge variant={item.enrollment_status}>{item.enrollment_status}</Badge>
                        )}

                        {canManage && (
                          <button
                            onClick={() => handleDropStudent(item.enrollment_id || item.id)}
                            className="p-1 rounded text-rose-400 hover:text-rose-300 hover:bg-rose-500/10 text-xs font-medium"
                            title="Drop from course"
                          >
                            Drop
                          </button>
                        )}
                      </div>
                    </div>
                  ))
                ) : (
                  <p className="p-4 text-center text-xs text-slate-500 bg-slate-950/20 rounded-xl">
                    No students currently enrolled in this course offering.
                  </p>
                )}
              </div>
            </div>
          </div>
        )}
      </SlideOver>

      {/* ========================================================================= */}
      {/* 2. SLIDE-OVER: ADD / EDIT COURSE FORM */}
      {/* ========================================================================= */}
      <SlideOver
        isOpen={isFormOpen}
        onClose={() => setIsFormOpen(false)}
        title={formMode === 'create' ? 'Add Course Offering' : 'Edit Course Details'}
        subtitle={
          formMode === 'create'
            ? 'Define course catalog codes, credits, and seat limits.'
            : `Updating course ${formData.course_code}`
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
              onClick={handleSaveCourse}
              disabled={isSaving}
              className="px-5 py-2 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-bold text-xs shadow-lg shadow-indigo-600/30 transition-all disabled:opacity-50"
            >
              {isSaving ? 'Saving...' : formMode === 'create' ? 'Create Course' : 'Update Course'}
            </button>
          </div>
        }
      >
        <form onSubmit={handleSaveCourse} className="space-y-4">
          <div className="grid grid-cols-2 gap-3">
            <div>
              <label className="block text-[11px] font-bold uppercase tracking-wider text-slate-300 mb-1">
                Course Code *
              </label>
              <input
                type="text"
                value={formData.course_code}
                onChange={(e) => setFormData({ ...formData, course_code: e.target.value })}
                required
                placeholder="e.g. CS101"
                className="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono text-xs focus:outline-none focus:border-indigo-500"
              />
            </div>
            <div>
              <label className="block text-[11px] font-bold uppercase tracking-wider text-slate-300 mb-1">
                Credit Hours *
              </label>
              <input
                type="number"
                min="1"
                max="6"
                value={formData.credit_hours}
                onChange={(e) => setFormData({ ...formData, credit_hours: Number(e.target.value) })}
                required
                className="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:outline-none focus:border-indigo-500"
              />
            </div>
          </div>

          <div>
            <label className="block text-[11px] font-bold uppercase tracking-wider text-slate-300 mb-1">
              Course Title *
            </label>
            <input
              type="text"
              value={formData.course_name}
              onChange={(e) => setFormData({ ...formData, course_name: e.target.value })}
              required
              placeholder="e.g. Introduction to Computer Systems"
              className="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:outline-none focus:border-indigo-500"
            />
          </div>

          <div className="grid grid-cols-2 gap-3">
            <div>
              <label className="block text-[11px] font-bold uppercase tracking-wider text-slate-300 mb-1">
                Academic Department *
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
                Assigned Instructor
              </label>
              <select
                value={formData.instructor_id}
                onChange={(e) => setFormData({ ...formData, instructor_id: e.target.value })}
                className="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:outline-none focus:border-indigo-500"
              >
                <option value="">Unassigned</option>
                {instructors.map((inst) => (
                  <option key={inst.id} value={inst.id}>
                    {inst.first_name} {inst.last_name} ({inst.employee_code})
                  </option>
                ))}
              </select>
            </div>
          </div>

          <div className="grid grid-cols-3 gap-3">
            <div>
              <label className="block text-[11px] font-bold uppercase tracking-wider text-slate-300 mb-1">
                Semester *
              </label>
              <select
                value={formData.semester}
                onChange={(e) => setFormData({ ...formData, semester: e.target.value })}
                className="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:outline-none focus:border-indigo-500"
              >
                <option value="Fall">Fall</option>
                <option value="Spring">Spring</option>
                <option value="Summer">Summer</option>
              </select>
            </div>
            <div>
              <label className="block text-[11px] font-bold uppercase tracking-wider text-slate-300 mb-1">
                Academic Year *
              </label>
              <input
                type="number"
                value={formData.academic_year}
                onChange={(e) => setFormData({ ...formData, academic_year: Number(e.target.value) })}
                className="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:outline-none focus:border-indigo-500"
              />
            </div>
            <div>
              <label className="block text-[11px] font-bold uppercase tracking-wider text-slate-300 mb-1">
                Seat Capacity *
              </label>
              <input
                type="number"
                min="1"
                max="500"
                value={formData.capacity}
                onChange={(e) => setFormData({ ...formData, capacity: Number(e.target.value) })}
                className="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:outline-none focus:border-indigo-500"
              />
            </div>
          </div>

          <div>
            <label className="block text-[11px] font-bold uppercase tracking-wider text-slate-300 mb-1">
              Course Description
            </label>
            <textarea
              rows={3}
              value={formData.description}
              onChange={(e) => setFormData({ ...formData, description: e.target.value })}
              placeholder="Detailed syllabus outline..."
              className="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:outline-none focus:border-indigo-500"
            />
          </div>
        </form>
      </SlideOver>

      {/* Delete Modal */}
      <Modal
        isOpen={Boolean(deleteTarget)}
        onClose={() => setDeleteTarget(null)}
        onConfirm={handleDeleteConfirm}
        title="Delete Course Offering"
        message={`Are you sure you want to delete course ${deleteTarget?.course_code} - ${deleteTarget?.course_name}? This action cannot be undone.`}
        confirmText="Delete Course"
        isLoading={isDeleting}
      />
    </div>
  );
};

export default Courses;
