import React, { useState, useEffect } from 'react';
import {
  Sparkles,
  Shield,
  ArrowRight,
  Lock,
  User,
  Mail,
  Building2,
  GraduationCap,
  BookOpen,
  Eye,
  EyeOff,
  Check,
  AlertCircle,
  Briefcase,
  Layers,
  ArrowLeft
} from 'lucide-react';
import { useAuth } from '../context/AuthContext.jsx';
import { useToast } from '../context/ToastContext.jsx';
import api from '../services/api';

const ROLES = [
  {
    id: 'student',
    title: 'Student',
    desc: 'Access courses, enrollments, track grades & attendance',
    icon: GraduationCap,
    gradient: 'from-amber-500/20 to-orange-500/20',
    border: 'hover:border-amber-500/50',
    activeBorder: 'border-amber-500 bg-amber-500/10 text-amber-300',
    badge: 'bg-amber-500/20 text-amber-300 border-amber-500/30',
  },
  {
    id: 'instructor',
    title: 'Faculty / Instructor',
    desc: 'Manage course syllabi, assign grades & attendance',
    icon: BookOpen,
    gradient: 'from-emerald-500/20 to-teal-500/20',
    border: 'hover:border-emerald-500/50',
    activeBorder: 'border-emerald-500 bg-emerald-500/10 text-emerald-300',
    badge: 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30',
  },
  {
    id: 'registrar',
    title: 'Registrar Staff',
    desc: 'Oversee student admissions, courses & curriculum',
    icon: Briefcase,
    gradient: 'from-blue-500/20 to-cyan-500/20',
    border: 'hover:border-blue-500/50',
    activeBorder: 'border-blue-500 bg-blue-500/10 text-blue-300',
    badge: 'bg-blue-500/20 text-blue-300 border-blue-500/30',
  },
  {
    id: 'admin',
    title: 'Administrator',
    desc: 'Full system configuration, user & security control',
    icon: Shield,
    gradient: 'from-purple-500/20 to-indigo-500/20',
    border: 'hover:border-purple-500/50',
    activeBorder: 'border-purple-500 bg-purple-500/10 text-purple-300',
    badge: 'bg-purple-500/20 text-purple-300 border-purple-500/30',
  },
];

const FALLBACK_DEPARTMENTS = [
  { id: 1, code: 'CS', name: 'Computer Science & Software Engineering' },
  { id: 2, code: 'EE', name: 'Electrical and Electronics Engineering' },
  { id: 3, code: 'BA', name: 'Business Administration & Management' },
  { id: 4, code: 'MATH', name: 'Mathematics and Applied Statistics' },
];

export const SignUp = ({ onSwitchToLogin }) => {
  const { register } = useAuth();
  const toast = useToast();

  const [role, setRole] = useState('student');
  const [firstName, setFirstName] = useState('');
  const [lastName, setLastName] = useState('');
  const [username, setUsername] = useState('');
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [passwordConfirmation, setPasswordConfirmation] = useState('');
  const [departmentId, setDepartmentId] = useState('1');
  const [academicLevel, setAcademicLevel] = useState('freshman');
  const [showPassword, setShowPassword] = useState(false);
  const [isLoading, setIsLoading] = useState(false);
  const [errors, setErrors] = useState({});

  const [departments, setDepartments] = useState(FALLBACK_DEPARTMENTS);

  useEffect(() => {
    const fetchDepartments = async () => {
      try {
        const res = await api.get('/departments/public');
        if (res.data && Array.isArray(res.data) && res.data.length > 0) {
          setDepartments(res.data);
          setDepartmentId(String(res.data[0].id));
        }
      } catch (err) {
        // Use fallback silently
        console.warn('Could not load public departments, using fallback list.');
      }
    };
    fetchDepartments();
  }, []);

  // Password strength calculation
  const getPasswordStrength = (pass) => {
    if (!pass) return { score: 0, label: 'Empty', color: 'bg-slate-700' };
    let score = 0;
    if (pass.length >= 6) score++;
    if (pass.length >= 10) score++;
    if (/[A-Z]/.test(pass)) score++;
    if (/[0-9]/.test(pass)) score++;
    if (/[^A-Za-z0-9]/.test(pass)) score++;

    if (score <= 1) return { score: 1, label: 'Weak', color: 'bg-rose-500' };
    if (score <= 3) return { score: 2, label: 'Fair', color: 'bg-amber-500' };
    if (score <= 4) return { score: 3, label: 'Good', color: 'bg-indigo-500' };
    return { score: 4, label: 'Strong', color: 'bg-emerald-500' };
  };

  const passwordStrength = getPasswordStrength(password);
  const passwordsMatch = password && passwordConfirmation && password === passwordConfirmation;

  const handleSubmit = async (e) => {
    e.preventDefault();
    setErrors({});

    const newErrors = {};
    if (!firstName.trim()) newErrors.first_name = 'First name is required';
    if (!username.trim()) newErrors.username = 'Username is required';
    if (!email.trim()) newErrors.email = 'Email address is required';
    if (!password) newErrors.password = 'Password is required';
    else if (password.length < 6) newErrors.password = 'Password must be at least 6 characters';
    if (password !== passwordConfirmation) newErrors.password_confirmation = 'Passwords do not match';

    if (Object.keys(newErrors).length > 0) {
      setErrors(newErrors);
      toast.warning('Please review and resolve the errors in the form.');
      return;
    }

    setIsLoading(true);
    const payload = {
      role,
      first_name: firstName.trim(),
      last_name: lastName.trim(),
      username: username.trim(),
      email: email.trim().toLowerCase(),
      password,
      password_confirmation: passwordConfirmation,
      department_id: departmentId ? parseInt(departmentId, 10) : undefined,
      academic_level: role === 'student' ? academicLevel : undefined,
    };

    const res = await register(payload);
    setIsLoading(false);

    if (res.success) {
      toast.success(`Account created! Welcome aboard, ${res.user.username}!`);
    } else {
      toast.error(res.message || 'Registration failed. Please try again.');
      if (res.message && res.message.toLowerCase().includes('already been taken')) {
        if (res.message.toLowerCase().includes('username')) {
          setErrors((prev) => ({ ...prev, username: 'Username already in use' }));
        }
        if (res.message.toLowerCase().includes('email')) {
          setErrors((prev) => ({ ...prev, email: 'Email address already in use' }));
        }
      }
    }
  };

  return (
    <div className="min-h-screen bg-slate-950 text-white flex flex-col justify-center items-center p-4 sm:p-6 relative overflow-hidden">
      {/* Background ambient glowing spheres */}
      <div className="absolute top-1/6 left-1/5 w-96 h-96 bg-indigo-600/20 rounded-full blur-3xl pointer-events-none -translate-x-1/2 -translate-y-1/2" />
      <div className="absolute bottom-1/6 right-1/5 w-96 h-96 bg-purple-600/20 rounded-full blur-3xl pointer-events-none translate-x-1/2 translate-y-1/2" />

      <div className="w-full max-w-5xl rounded-3xl border border-slate-800/80 bg-slate-900/80 backdrop-blur-2xl shadow-2xl overflow-hidden z-10 my-8">
        <div className="grid lg:grid-cols-12">
          {/* Left column: Branding & Role Selection Guide */}
          <div className="lg:col-span-5 p-8 sm:p-10 flex flex-col justify-between bg-gradient-to-br from-indigo-950/70 via-slate-900/70 to-purple-950/50 border-b lg:border-b-0 lg:border-r border-slate-800">
            <div>
              <div className="flex items-center justify-between">
                <div className="flex items-center gap-3">
                  <div className="w-12 h-12 rounded-2xl bg-gradient-to-tr from-indigo-600 to-purple-500 flex items-center justify-center text-white shadow-lg shadow-indigo-500/30">
                    <Sparkles className="w-6 h-6" />
                  </div>
                  <div>
                    <h1 className="text-xl font-black tracking-tight">SMS Portal</h1>
                    <p className="text-xs text-indigo-400 font-semibold uppercase tracking-wider">Join Academic Workspace</p>
                  </div>
                </div>

                <button
                  type="button"
                  onClick={onSwitchToLogin}
                  className="text-xs font-semibold text-slate-400 hover:text-white flex items-center gap-1.5 transition-colors lg:hidden"
                >
                  <ArrowLeft className="w-3.5 h-3.5" /> Sign In
                </button>
              </div>

              <div className="mt-8">
                <h2 className="text-2xl font-black text-white tracking-tight">Create Account</h2>
                <p className="text-xs text-slate-400 mt-1.5 leading-relaxed">
                  Register your account to access course schedules, grades, enrollments, and academic workflows.
                </p>
              </div>

              {/* Role Selection Picker */}
              <div className="mt-6 space-y-2.5">
                <p className="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Select Your Account Type</p>
                <div className="grid grid-cols-1 gap-2">
                  {ROLES.map((r) => {
                    const Icon = r.icon;
                    const isSelected = role === r.id;
                    return (
                      <button
                        key={r.id}
                        type="button"
                        onClick={() => setRole(r.id)}
                        className={`w-full text-left p-3 rounded-2xl border transition-all flex items-start gap-3.5 ${
                          isSelected
                            ? `${r.activeBorder} shadow-lg shadow-indigo-900/20`
                            : `bg-slate-950/40 border-slate-800/80 text-slate-400 ${r.border} hover:text-slate-200`
                        }`}
                      >
                        <div
                          className={`w-9 h-9 rounded-xl flex items-center justify-center shrink-0 ${
                            isSelected ? 'bg-indigo-600 text-white shadow-md' : 'bg-slate-800/80 text-slate-400'
                          }`}
                        >
                          <Icon className="w-4 h-4" />
                        </div>
                        <div className="flex-1 min-w-0">
                          <div className="flex items-center justify-between">
                            <span className={`text-xs font-bold ${isSelected ? 'text-white' : 'text-slate-300'}`}>
                              {r.title}
                            </span>
                            {isSelected && (
                              <span className="w-2 h-2 rounded-full bg-indigo-400 animate-ping" />
                            )}
                          </div>
                          <p className="text-[11px] text-slate-400 mt-0.5 leading-snug">{r.desc}</p>
                        </div>
                      </button>
                    );
                  })}
                </div>
              </div>
            </div>

            <div className="mt-8 pt-6 border-t border-slate-800/80 text-xs text-slate-400 flex items-center justify-between">
              <span>Already registered?</span>
              <button
                type="button"
                onClick={onSwitchToLogin}
                className="text-xs font-bold text-indigo-400 hover:text-indigo-300 flex items-center gap-1.5 transition-colors group"
              >
                <span>Sign in here</span>
                <ArrowRight className="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform" />
              </button>
            </div>
          </div>

          {/* Right column: Interactive Registration Form */}
          <div className="lg:col-span-7 p-8 sm:p-10 flex flex-col justify-center">
            <div className="flex items-center justify-between pb-4 mb-4 border-b border-slate-800/80">
              <div>
                <h3 className="text-lg font-bold text-white flex items-center gap-2">
                  <span>Sign Up Information</span>
                  <span className="text-[11px] font-semibold px-2.5 py-0.5 rounded-full uppercase tracking-wider bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                    {role}
                  </span>
                </h3>
                <p className="text-xs text-slate-400 mt-0.5">Please provide your details to initiate your workspace.</p>
              </div>
            </div>

            <form onSubmit={handleSubmit} className="space-y-4">
              {/* First & Last Name */}
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                  <label className="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                    First Name <span className="text-rose-400">*</span>
                  </label>
                  <div className="relative">
                    <User className="w-4 h-4 text-slate-500 absolute left-3.5 top-1/2 -translate-y-1/2" />
                    <input
                      type="text"
                      value={firstName}
                      onChange={(e) => {
                        setFirstName(e.target.value);
                        if (errors.first_name) setErrors((prev) => ({ ...prev, first_name: null }));
                      }}
                      placeholder="e.g. John"
                      required
                      className={`w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-950/60 border text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 transition-all ${
                        errors.first_name
                          ? 'border-rose-500/60 focus:border-rose-500 focus:ring-rose-500/20'
                          : 'border-slate-800 focus:border-indigo-500 focus:ring-indigo-500/20'
                      }`}
                    />
                  </div>
                  {errors.first_name && (
                    <p className="text-[11px] text-rose-400 mt-1 flex items-center gap-1">
                      <AlertCircle className="w-3 h-3" /> {errors.first_name}
                    </p>
                  )}
                </div>

                <div>
                  <label className="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                    Last Name
                  </label>
                  <div className="relative">
                    <User className="w-4 h-4 text-slate-500 absolute left-3.5 top-1/2 -translate-y-1/2" />
                    <input
                      type="text"
                      value={lastName}
                      onChange={(e) => setLastName(e.target.value)}
                      placeholder="e.g. Doe"
                      className="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-950/60 border border-slate-800 text-white placeholder-slate-500 text-sm focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all"
                    />
                  </div>
                </div>
              </div>

              {/* Username & Email */}
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                  <label className="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                    Username <span className="text-rose-400">*</span>
                  </label>
                  <div className="relative">
                    <User className="w-4 h-4 text-slate-500 absolute left-3.5 top-1/2 -translate-y-1/2" />
                    <input
                      type="text"
                      value={username}
                      onChange={(e) => {
                        setUsername(e.target.value);
                        if (errors.username) setErrors((prev) => ({ ...prev, username: null }));
                      }}
                      placeholder="e.g. jdoe24"
                      required
                      className={`w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-950/60 border text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 transition-all ${
                        errors.username
                          ? 'border-rose-500/60 focus:border-rose-500 focus:ring-rose-500/20'
                          : 'border-slate-800 focus:border-indigo-500 focus:ring-indigo-500/20'
                      }`}
                    />
                  </div>
                  {errors.username && (
                    <p className="text-[11px] text-rose-400 mt-1 flex items-center gap-1">
                      <AlertCircle className="w-3 h-3" /> {errors.username}
                    </p>
                  )}
                </div>

                <div>
                  <label className="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                    Email Address <span className="text-rose-400">*</span>
                  </label>
                  <div className="relative">
                    <Mail className="w-4 h-4 text-slate-500 absolute left-3.5 top-1/2 -translate-y-1/2" />
                    <input
                      type="email"
                      value={email}
                      onChange={(e) => {
                        setEmail(e.target.value);
                        if (errors.email) setErrors((prev) => ({ ...prev, email: null }));
                      }}
                      placeholder="name@example.com"
                      required
                      className={`w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-950/60 border text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 transition-all ${
                        errors.email
                          ? 'border-rose-500/60 focus:border-rose-500 focus:ring-rose-500/20'
                          : 'border-slate-800 focus:border-indigo-500 focus:ring-indigo-500/20'
                      }`}
                    />
                  </div>
                  {errors.email && (
                    <p className="text-[11px] text-rose-400 mt-1 flex items-center gap-1">
                      <AlertCircle className="w-3 h-3" /> {errors.email}
                    </p>
                  )}
                </div>
              </div>

              {/* Department & Academic Level (conditional for student/instructor) */}
              {(role === 'student' || role === 'instructor') && (
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                  <div>
                    <label className="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                      Academic Department
                    </label>
                    <div className="relative">
                      <Building2 className="w-4 h-4 text-slate-500 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" />
                      <select
                        value={departmentId}
                        onChange={(e) => setDepartmentId(e.target.value)}
                        className="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-950/60 border border-slate-800 text-white text-sm focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all appearance-none cursor-pointer"
                      >
                        {departments.map((dept) => (
                          <option key={dept.id} value={dept.id} className="bg-slate-900 text-white">
                            {dept.code} - {dept.name}
                          </option>
                        ))}
                      </select>
                    </div>
                  </div>

                  {role === 'student' && (
                    <div>
                      <label className="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                        Academic Level
                      </label>
                      <div className="relative">
                        <Layers className="w-4 h-4 text-slate-500 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" />
                        <select
                          value={academicLevel}
                          onChange={(e) => setAcademicLevel(e.target.value)}
                          className="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-950/60 border border-slate-800 text-white text-sm focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all appearance-none cursor-pointer"
                        >
                          <option value="freshman" className="bg-slate-900 text-white">Freshman (Year 1)</option>
                          <option value="sophomore" className="bg-slate-900 text-white">Sophomore (Year 2)</option>
                          <option value="junior" className="bg-slate-900 text-white">Junior (Year 3)</option>
                          <option value="senior" className="bg-slate-900 text-white">Senior (Year 4)</option>
                          <option value="graduate" className="bg-slate-900 text-white">Graduate Studies</option>
                        </select>
                      </div>
                    </div>
                  )}
                </div>
              )}

              {/* Password & Confirmation */}
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                  <label className="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                    Password <span className="text-rose-400">*</span>
                  </label>
                  <div className="relative">
                    <Lock className="w-4 h-4 text-slate-500 absolute left-3.5 top-1/2 -translate-y-1/2" />
                    <input
                      type={showPassword ? 'text' : 'password'}
                      value={password}
                      onChange={(e) => {
                        setPassword(e.target.value);
                        if (errors.password) setErrors((prev) => ({ ...prev, password: null }));
                      }}
                      placeholder="Min 6 characters"
                      required
                      className={`w-full pl-10 pr-10 py-2.5 rounded-xl bg-slate-950/60 border text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 transition-all ${
                        errors.password
                          ? 'border-rose-500/60 focus:border-rose-500 focus:ring-rose-500/20'
                          : 'border-slate-800 focus:border-indigo-500 focus:ring-indigo-500/20'
                      }`}
                    />
                    <button
                      type="button"
                      onClick={() => setShowPassword(!showPassword)}
                      className="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-300 transition-colors"
                    >
                      {showPassword ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
                    </button>
                  </div>
                  {/* Strength Bar */}
                  {password && (
                    <div className="mt-1.5 flex items-center gap-1.5">
                      <div className="flex-1 h-1 bg-slate-800 rounded-full overflow-hidden flex gap-0.5">
                        <div className={`h-full ${passwordStrength.color} transition-all duration-300`} style={{ width: `${(passwordStrength.score / 4) * 100}%` }} />
                      </div>
                      <span className="text-[10px] font-semibold text-slate-400">{passwordStrength.label}</span>
                    </div>
                  )}
                  {errors.password && (
                    <p className="text-[11px] text-rose-400 mt-1 flex items-center gap-1">
                      <AlertCircle className="w-3 h-3" /> {errors.password}
                    </p>
                  )}
                </div>

                <div>
                  <label className="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                    Confirm Password <span className="text-rose-400">*</span>
                  </label>
                  <div className="relative">
                    <Lock className="w-4 h-4 text-slate-500 absolute left-3.5 top-1/2 -translate-y-1/2" />
                    <input
                      type={showPassword ? 'text' : 'password'}
                      value={passwordConfirmation}
                      onChange={(e) => {
                        setPasswordConfirmation(e.target.value);
                        if (errors.password_confirmation) setErrors((prev) => ({ ...prev, password_confirmation: null }));
                      }}
                      placeholder="Repeat password"
                      required
                      className={`w-full pl-10 pr-10 py-2.5 rounded-xl bg-slate-950/60 border text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 transition-all ${
                        errors.password_confirmation
                          ? 'border-rose-500/60 focus:border-rose-500 focus:ring-rose-500/20'
                          : passwordsMatch
                          ? 'border-emerald-500/50 focus:border-emerald-500 focus:ring-emerald-500/20'
                          : 'border-slate-800 focus:border-indigo-500 focus:ring-indigo-500/20'
                      }`}
                    />
                    {passwordsMatch && (
                      <div className="absolute right-3.5 top-1/2 -translate-y-1/2 text-emerald-400">
                        <Check className="w-4 h-4" />
                      </div>
                    )}
                  </div>
                  {errors.password_confirmation && (
                    <p className="text-[11px] text-rose-400 mt-1 flex items-center gap-1">
                      <AlertCircle className="w-3 h-3" /> {errors.password_confirmation}
                    </p>
                  )}
                </div>
              </div>

              {/* Submit Button */}
              <div className="pt-2">
                <button
                  type="submit"
                  disabled={isLoading}
                  className="w-full py-3 px-4 rounded-xl bg-gradient-to-r from-indigo-600 via-purple-600 to-indigo-600 hover:from-indigo-500 hover:via-purple-500 hover:to-indigo-500 text-white font-bold text-sm shadow-xl shadow-indigo-600/30 flex items-center justify-center gap-2 transition-all group disabled:opacity-50 cursor-pointer"
                >
                  <span>{isLoading ? 'Creating Your Account...' : 'Complete Registration & Sign In'}</span>
                  <ArrowRight className="w-4 h-4 group-hover:translate-x-1 transition-transform" />
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  );
};

export default SignUp;
