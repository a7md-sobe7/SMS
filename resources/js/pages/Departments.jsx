import React, { useState, useEffect } from 'react';
import { Building2, Plus, Edit2, Users, BookOpen, GraduationCap } from 'lucide-react';
import api from '../services/api';
import { useAuth } from '../context/AuthContext.jsx';
import { useToast } from '../context/ToastContext.jsx';
import SlideOver from '../components/common/SlideOver.jsx';

export const Departments = () => {
  const { role } = useAuth();
  const toast = useToast();

  const [departments, setDepartments] = useState([]);
  const [isLoading, setIsLoading] = useState(true);

  // Slide-Over
  const [isFormOpen, setIsFormOpen] = useState(false);
  const [formMode, setFormMode] = useState('create');
  const [formData, setFormData] = useState({ code: '', name: '', description: '' });
  const [isSaving, setIsSaving] = useState(false);

  const fetchDepts = async () => {
    setIsLoading(true);
    try {
      const res = await api.get('/departments');
      setDepartments(res.data || []);
    } catch {
      toast.error('Failed to load departments.');
    } finally {
      setIsLoading(false);
    }
  };

  useEffect(() => {
    fetchDepts();
  }, []);

  const handleOpenCreate = () => {
    setFormMode('create');
    setFormData({ code: '', name: '', description: '' });
    setIsFormOpen(true);
  };

  const handleOpenEdit = (dept) => {
    setFormMode('edit');
    setFormData({
      id: dept.id,
      code: dept.code || '',
      name: dept.name || '',
      description: dept.description || '',
    });
    setIsFormOpen(true);
  };

  const handleSave = async (e) => {
    e?.preventDefault();
    setIsSaving(true);
    try {
      if (formMode === 'create') {
        await api.post('/departments', formData);
        toast.success('Department created successfully!');
      } else {
        await api.put(`/departments/${formData.id}`, formData);
        toast.success('Department updated successfully!');
      }
      setIsFormOpen(false);
      fetchDepts();
    } catch (err) {
      toast.error(err.message || 'Failed to save department.');
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
            <Building2 className="w-6 h-6 text-indigo-400" />
            <span>Academic Departments</span>
          </h2>
          <p className="text-xs text-slate-400 mt-0.5">
            Manage academic divisions, course quotas, and faculty assignments
          </p>
        </div>

        {canManage && (
          <button
            onClick={handleOpenCreate}
            className="px-4 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-bold text-xs shadow-lg shadow-indigo-600/30 flex items-center gap-2 transition-all shrink-0"
          >
            <Plus className="w-4 h-4" />
            <span>New Department</span>
          </button>
        )}
      </div>

      {isLoading ? (
        <div className="glass-panel p-12 text-center rounded-3xl border border-slate-800 text-slate-400 text-sm">
          Loading departments...
        </div>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
          {departments.map((dept) => (
            <div
              key={dept.id}
              className="glass-card-interactive p-6 rounded-3xl border border-slate-800/80 flex flex-col justify-between group"
            >
              <div>
                <div className="flex items-center justify-between">
                  <span className="px-2.5 py-1 rounded-xl bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 font-mono font-bold text-xs">
                    {dept.code}
                  </span>
                  {canManage && (
                    <button
                      onClick={() => handleOpenEdit(dept)}
                      className="p-1 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition-colors"
                      title="Edit in Drawer"
                    >
                      <Edit2 className="w-3.5 h-3.5" />
                    </button>
                  )}
                </div>

                <h3 className="text-base font-bold text-white mt-3 group-hover:text-indigo-300 transition-colors">
                  {dept.name}
                </h3>
                <p className="text-xs text-slate-400 mt-1 line-clamp-2">
                  {dept.description || 'Academic department and faculty division.'}
                </p>

                <div className="mt-5 pt-4 border-t border-slate-800/80 grid grid-cols-3 gap-2 text-center text-xs">
                  <div className="p-2 rounded-xl bg-slate-900/60 border border-slate-800/60">
                    <p className="text-[10px] text-slate-400 font-semibold uppercase">Students</p>
                    <p className="font-extrabold text-white mt-0.5">{dept.student_count || 0}</p>
                  </div>
                  <div className="p-2 rounded-xl bg-slate-900/60 border border-slate-800/60">
                    <p className="text-[10px] text-slate-400 font-semibold uppercase">Courses</p>
                    <p className="font-extrabold text-white mt-0.5">{dept.course_count || 0}</p>
                  </div>
                  <div className="p-2 rounded-xl bg-slate-900/60 border border-slate-800/60">
                    <p className="text-[10px] text-slate-400 font-semibold uppercase">Faculty</p>
                    <p className="font-extrabold text-white mt-0.5">{dept.instructor_count || 0}</p>
                  </div>
                </div>
              </div>
            </div>
          ))}
        </div>
      )}

      {/* Slide-Over: Create / Edit Department */}
      <SlideOver
        isOpen={isFormOpen}
        onClose={() => setIsFormOpen(false)}
        title={formMode === 'create' ? 'Create Academic Department' : 'Edit Department'}
        subtitle="Configure department code, name, and curriculum details."
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
              {isSaving ? 'Saving...' : formMode === 'create' ? 'Create' : 'Save Changes'}
            </button>
          </div>
        }
      >
        <form onSubmit={handleSave} className="space-y-4">
          <div>
            <label className="block text-[11px] font-bold uppercase tracking-wider text-slate-300 mb-1">
              Department Code *
            </label>
            <input
              type="text"
              value={formData.code}
              onChange={(e) => setFormData({ ...formData, code: e.target.value })}
              required
              placeholder="e.g. CS or MATH"
              className="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono text-xs focus:outline-none focus:border-indigo-500"
            />
          </div>
          <div>
            <label className="block text-[11px] font-bold uppercase tracking-wider text-slate-300 mb-1">
              Department Name *
            </label>
            <input
              type="text"
              value={formData.name}
              onChange={(e) => setFormData({ ...formData, name: e.target.value })}
              required
              placeholder="e.g. Department of Computer Science"
              className="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:outline-none focus:border-indigo-500"
            />
          </div>
          <div>
            <label className="block text-[11px] font-bold uppercase tracking-wider text-slate-300 mb-1">
              Description
            </label>
            <textarea
              rows={3}
              value={formData.description}
              onChange={(e) => setFormData({ ...formData, description: e.target.value })}
              placeholder="Division mission statement..."
              className="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:outline-none focus:border-indigo-500"
            />
          </div>
        </form>
      </SlideOver>
    </div>
  );
};

export default Departments;
