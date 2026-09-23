import React, { useState } from 'react';
import { useAuth } from './context/AuthContext.jsx';
import AppLayout from './components/layout/AppLayout.jsx';
import Login from './pages/Login.jsx';
import SignUp from './pages/SignUp.jsx';
import Dashboard from './pages/Dashboard.jsx';
import Students from './pages/Students.jsx';
import Courses from './pages/Courses.jsx';
import Grades from './pages/Grades.jsx';
import Attendance from './pages/Attendance.jsx';
import Departments from './pages/Departments.jsx';
import Instructors from './pages/Instructors.jsx';
import { Sparkles } from 'lucide-react';

export const App = () => {
  const { isAuthenticated, isLoading } = useAuth();
  const [activePage, setActivePage] = useState('dashboard');
  const [authView, setAuthView] = useState('login');

  if (isLoading) {
    return (
      <div className="min-h-screen bg-slate-950 flex flex-col items-center justify-center text-white">
        <div className="w-12 h-12 rounded-2xl bg-gradient-to-tr from-indigo-600 to-purple-600 flex items-center justify-center text-white shadow-xl shadow-indigo-600/30 animate-pulse">
          <Sparkles className="w-6 h-6" />
        </div>
        <p className="mt-4 text-xs font-semibold text-slate-400 tracking-wider uppercase">
          Initializing SMS Portal...
        </p>
      </div>
    );
  }

  if (!isAuthenticated) {
    if (authView === 'signup') {
      return <SignUp onSwitchToLogin={() => setAuthView('login')} />;
    }
    return <Login onSwitchToSignUp={() => setAuthView('signup')} />;
  }

  const renderPage = () => {
    switch (activePage) {
      case 'dashboard':
        return <Dashboard onNavigate={setActivePage} />;
      case 'students':
        return <Students />;
      case 'courses':
        return <Courses />;
      case 'enrollments':
        return <Courses />;
      case 'grades':
        return <Grades />;
      case 'attendance':
        return <Attendance />;
      case 'departments':
        return <Departments />;
      case 'instructors':
        return <Instructors />;
      default:
        return <Dashboard onNavigate={setActivePage} />;
    }
  };

  const pageTitles = {
    dashboard: 'Academic Dashboard',
    students: 'Students Directory',
    courses: 'Course Catalog & Curriculum',
    enrollments: 'Course Registrations',
    grades: 'Academic Gradebook',
    attendance: 'Daily Attendance Roll-Call',
    departments: 'Academic Departments',
    instructors: 'Faculty & Instructors',
  };

  return (
    <AppLayout
      activePage={activePage}
      onNavigate={setActivePage}
      pageTitle={pageTitles[activePage] || activePage}
    >
      {renderPage()}
    </AppLayout>
  );
};

export default App;
