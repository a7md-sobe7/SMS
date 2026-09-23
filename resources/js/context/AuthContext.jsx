import React, { createContext, useContext, useState, useEffect, useCallback } from 'react';
import api from '../services/api';

const AuthContext = createContext(null);

export const DEMO_ACCOUNTS = {
  admin: { identifier: 'admin', password: 'Admin@123456', label: 'Administrator', badgeColor: 'from-purple-500 to-indigo-600' },
  registrar: { identifier: 'registrar', password: 'Admin@123456', label: 'Registrar', badgeColor: 'from-blue-500 to-cyan-600' },
  instructor: { identifier: 'dr.alan', password: 'Admin@123456', label: 'Instructor (Dr. Alan)', badgeColor: 'from-emerald-500 to-teal-600' },
  student: { identifier: 'john.doe', password: 'Admin@123456', label: 'Student (John Doe)', badgeColor: 'from-amber-500 to-orange-600' },
};

export const AuthProvider = ({ children }) => {
  const [user, setUser] = useState(null);
  const [isLoading, setIsLoading] = useState(true);

  const checkAuth = useCallback(async () => {
    try {
      const res = await api.get('/auth/me', { timeout: 3000 });
      if (res.data?.user) {
        setUser(res.data.user);
      } else {
        setUser(null);
      }
    } catch {
      setUser(null);
    } finally {
      setIsLoading(false);
    }
  }, []);

  useEffect(() => {
    // Safety timer to prevent stuck loading state
    const timer = setTimeout(() => {
      setIsLoading(false);
    }, 1500);

    checkAuth().finally(() => clearTimeout(timer));

    return () => clearTimeout(timer);
  }, [checkAuth]);

  const login = async (identifier, password) => {
    setIsLoading(true);
    try {
      const res = await api.post('/auth/login', { identifier, password });
      if (res.data?.user) {
        setUser(res.data.user);
        return { success: true, user: res.data.user };
      }
      return { success: false, message: 'Authentication payload missing user.' };
    } catch (err) {
      return { success: false, message: err.message };
    } finally {
      setIsLoading(false);
    }
  };

  const register = async (formData) => {
    setIsLoading(true);
    try {
      const res = await api.post('/auth/register', formData);
      if (res.data?.user) {
        setUser(res.data.user);
        return { success: true, user: res.data.user };
      }
      return { success: false, message: 'Registration payload missing user.' };
    } catch (err) {
      return { success: false, message: err.message };
    } finally {
      setIsLoading(false);
    }
  };

  const logout = async () => {
    try {
      await api.post('/auth/logout');
    } catch (e) {
      console.error('Logout error:', e);
    } finally {
      setUser(null);
    }
  };

  const switchDemoRole = async (roleKey) => {
    const creds = DEMO_ACCOUNTS[roleKey];
    if (creds) {
      return await login(creds.identifier, creds.password);
    }
  };

  const value = {
    user,
    role: user?.role || null,
    isAuthenticated: Boolean(user),
    isLoading,
    login,
    register,
    logout,
    switchDemoRole,
    checkAuth,
  };

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
};

export const useAuth = () => {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
};
