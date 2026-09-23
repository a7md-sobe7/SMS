import React, { useState, useEffect } from 'react';
import { GraduationCap, Plus, Mail, Phone, BookOpen, Eye } from 'lucide-react';
import api from '../services/api';
import { useAuth } from '../context/AuthContext.jsx';
import { useToast } from '../context/ToastContext.jsx';
import SlideOver from '../components/common/SlideOver.jsx';

export const Instructors = () => {
  const { role } = useAuth();
  const toast = useToast();

  const [instructors, setInstructors] = useState([]);
  const [departments, setDepartments] = useState([]);
  const [isLoading, setIsLoading] = useState(true);

  // Instructor Detail Slide-Over
  const [detailId, setDetailId] = useState(null);
  const [detailData, setDetailData] = useState(null);

  // Create Slide-Over
  const [isFormOpen, setIsFormOpen] = useState(false);
  const [formData, setFormData] = useState({
    employee_code: '',
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    department_id: '',
  });
  const [isSaving, setIsSaving] = useState(false);

  const fetchInstructors = async () => {
    setIsLoading(true);
    try {
      const [instRes, deptRes] = await Promise.all([
        api.get('/instructors'),
        api.get('/departments'),
      ]);
      setInstructors(instRes.data || []);
      setDepartments(deptRes.data || []);
    } catch {
      toast.error('Failed to load faculty directory.');
    } finally {
      setIsLoading(false);
    }
  };

  useEffect(() => {
    fetchInstructors();
  }, []);

  const handleOpenDetail = async (id) => {
    setDetailId(id);
    try {
      const res = await api.get(`/instructors/${id}`);
      setDetailData(res.data);
    } catch {
      toast.error('Failed to load instructor profile.');
      setDetailId(null);
    }
  };

  const handleOpenCreate = () => {
    setFormData({
      employee_code: `FAC${Math.floor(100 + Math.random() * 900)}`,
      first_name: '',
      last_name: '',
      email: '',
      phone: '',
      department_id: departments[0]?.id || '',
    });
    setIsFormOpen(true);
  };

  const handleSave = async (e) => {
    e?.preventDefault();
    setIsSaving(true);
    try {
      await api.post('/instructors', formData);
      toast.success('Instructor registered successfully!');
      setIsFormOpen(false);
      fetchInstructors();
    } catch (err) {
      toast.error(err.message || 'Failed to register instructor.');
    } finally {
      setIsSaving(false);
    }
  };

  const canManage = role === 'admin';

  return (
    <div className="space-y-6 animate-fade-in">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h2 className="text-2xl font-extrabold text-white tracking-tight flex items-center gap-2.5">
            <GraduationCap className="w-6 h-6 text-indigo-400" />
            <span>Faculty & Instructors</span>
          </h2>
          <p className="text-xs text-slate-400 mt-0.5">
            Academic faculty directory, department affiliations, and course assignments
          </p>
        </div>

        {canManage && (
          <button
            onClick={handleOpenCreate}
            className="px-4 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-bold text-xs shadow-lg shadow-indigo-600/30 flex items-center gap-2 transition-all shrink-0"
          >
            <Plus className="w-4 h-4" />
            <span>Register Faculty</span>
          </button>
        )}
      </div>

      {isLoading ? (
        <div className="glass-panel p-12 text-center rounded-3xl border border-slate-800 text-slate-400 text-sm">
          Loading faculty...
        </div>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
          {instructors.map((inst) => (
            <div
              key={inst.id}
              onClick={() => handleOpenDetail(inst.id)}
              className="glass-card-interactive p-6 rounded-3xl border border-slate-800/80 flex flex-col justify-between cursor-pointer group"
            >
              <div>
                <div className="flex items-center gap-3.5">
                  <div className="w-12 h-12 rounded-2xl bg-gradient-to-tr from-indigo-600/30 to-purple-600/30 border border-indigo-500/40 flex items-center justify-center font-bold text-indigo-300 text-sm">
                    {inst.first_name?.[0]}
                    {inst.last_name?.[0]}
                  </div>
                  <div>
                    <h3 className="font-bold text-white group-hover:text-indigo-300 transition-colors">
                      {inst.first_name} {inst.last_name}
                    </h3>
                    <p className="font-mono text-xs font-bold text-indigo-400">
                      {inst.employee_code}
                    </p>
                  </div>
                </div>

                <div className="mt-4 pt-3 border-t border-slate-800/80 space-y-2 text-xs text-slate-300">
                  <div className="flex items-center gap-2 text-slate-400">
                    <Mail className="w-3.5 h-3.5 text-indigo-400 shrink-0" />
                    <span className="truncate">{inst.email}</span>
                  </div>
                  <div className="flex items-center gap-2 text-slate-400">
                    <GraduationCap className="w-3.5 h-3.5 text-indigo-400 shrink-0" />
                    <span>{inst.department_name || 'Academic Faculty'}</span>
                  </div>
                </div>
              </div>

              <div className="mt-5 pt-3 border-t border-slate-800/80 flex items-center justify-between text-xs">
                <span className="text-indigo-400 font-semibold flex items-center gap-1">
                  <span>View Details</span>
                  <Eye className="w-3.5 h-3.5" />
                </span>
              </div>
            </div>
          ))}
        </div>
      )}

      {/* Slide-Over: Instructor Details */}
      <SlideOver
        isOpen={Boolean(detailId)}
        onClose={() => setDetailId(null)}
        title={detailData ? `${detailData.first_name} ${detailData.last_name}` : 'Faculty Profile'}
        subtitle={detailData ? `Code: ${detailData.employee_code} • Department: ${detailData.department_name}` : ''}
      >
        {detailData && (
          <div className="space-y-6">
            <div className="p-4 rounded-2xl bg-slate-950/60 border border-slate-800 space-y-2 text-xs">
              <div className="flex justify-between py-1 border-b border-slate-800/60">
                <span className="text-slate-400">Email</span>
                <span className="font-semibold text-white">{detailData.email}</span>
              </div>
              <div className="flex justify-between py-1 border-b border-slate-800/60">
                <span className="text-slate-400">Phone</span>
                <span className="font-semibold text-white">{detailData.phone || '—'}</span>
              </div>
            </div>

            <div>
              <h4 className="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3 flex items-center gap-1.5">
                <BookOpen className="w-4 h-4 text-indigo-400" /> Assigned Courses (
                {detailData.courses?.length || 0})
              </h4>
              <div className="space-y-2">
                {detailData.courses && detailData.courses.length > 0 ? (
                  detailData.courses.map((c) => (
                    <div
                      key={c.id}
                      className="p-3.5 rounded-xl bg-slate-950/40 border border-slate-800 flex items-center justify-between"
                    >
                      <div>
                        <span className="font-mono text-xs font-bold text-indigo-400">
                          {c.course_code}
                        </span>
                        <p className="font-semibold text-white text-xs">{c.course_name}</p>
                      </div>
                      <span className="text-xs text-slate-400 font-medium">
                        {c.enrolled_count || 0} enrolled
                      </span>
                    </div>
                  ))
                ) : (
                  <p className="p-4 text-center text-xs text-slate-500 bg-slate-950/20 rounded-xl">
                    No active course teaching assignments.
                  </p>
                )}
              </div>
            </div>
          </div>
        )}
      </SlideOver>

      {/* Slide-Over: Create Instructor */}
      <SlideOver
        isOpen={isFormOpen}
        onClose={() => setIsFormOpen(false)}
        title="Register Faculty Member"
        subtitle="Add instructor profile and department affiliation."
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
              onClick={handleSave}
              disabled={isSaving}
              className="px-5 py-2 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-bold text-xs shadow-lg shadow-indigo-600/30 transition-all disabled:opacity-50"
            >
              {isSaving ? 'Registering...' : 'Register Faculty'}
            </button>
          </div>
        }
      >
        <form onSubmit={handleSave} className="space-y-4">
          <div className="grid grid-cols-2 gap-3">
            <div>
              <label className="block text-[11px] font-bold uppercase tracking-wider text-slate-300 mb-1">
                Employee Code *
              </label>
              <input
                type="text"
                value={formData.employee_code}
                onChange={(e) => setFormData({ ...formData, employee_code: e.target.value })}
                required
                className="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono text-xs focus:outline-none focus:border-indigo-500"
              />
            </div>
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
                Email *
              </label>
              <input
                type="email"
                value={formData.email}
                onChange={(e) => setFormData({ ...formData, email: e.target.value })}
                required
                placeholder="faculty@sms.edu"
                className="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:outline-none focus:border-indigo-500"
              />
            </div>
            <div>
              <label className="block text-[11px] font-bold uppercase tracking-wider text-slate-300 mb-1">
                Phone
              </label>
              <input
                type="tel"
                value={formData.phone}
                onChange={(e) => setFormData({ ...formData, phone: e.target.value })}
                placeholder="+1 555-0100"
                className="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:outline-none focus:border-indigo-500"
              />
            </div>
          </div>
        </form>
      </SlideOver>
    </div>
  );
};

export default Instructors;
