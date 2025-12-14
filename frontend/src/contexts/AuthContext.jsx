import React, { createContext, useContext, useState, useEffect } from 'react';
import { authService } from '../services/api';
import api from '../services/api';

const AuthContext = createContext();

export const useAuth = () => {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
};

export const AuthProvider = ({ children }) => {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);
  const [initialized, setInitialized] = useState(false);

  useEffect(() => {
    const savedUser = localStorage.getItem('user');
    const token = localStorage.getItem('auth_token');
    
    if (savedUser && token) {
      try {
        const parsedUser = JSON.parse(savedUser);
        setUser(parsedUser);
      } catch (error) {
        console.error('Error parsing saved user:', error);
        localStorage.removeItem('user');
        localStorage.removeItem('auth_token');
      }
    }
    setInitialized(true);
    setLoading(false);
  }, []);

  useEffect(() => {
    if (!user) return;

    const handleFocus = () => {
      refreshUserPoints();
    };

    window.addEventListener('focus', handleFocus);
    
    return () => {
      window.removeEventListener('focus', handleFocus);
    };
  }, [user]);

  const login = async (credentials) => {
    try {
      const response = await authService.login(credentials);
      if (response.success) {
        const userData = response.data.user;
        setUser(userData);

const isBarbero = userData.is_barbero === true || 
                         (typeof userData.email === 'string' && userData.email.toLowerCase().endsWith('@barbero.com'));

return { 
          success: true, 
          isBarbero: isBarbero
        };
      }
      return { success: false, message: response.message };
    } catch (error) {
      return { success: false, message: error.response?.data?.message || 'Error al iniciar sesión' };
    }
  };

  const register = async (userData) => {
    try {
      const response = await authService.register(userData);
      if (response.success) {
        const userData = response.data.user;
        setUser(userData);

return { 
          success: true, 
          isBarbero: userData.is_barbero || false 
        };
      }
      return { success: false, message: response.message };
    } catch (error) {
      if (error.response?.data?.errors) {
        const errors = error.response.data.errors;
        
        if (errors.email) {
          if (errors.email.includes('already been taken')) {
            return { success: false, message: 'El email ya está en uso' };
          }
          if (errors.email.includes('required')) {
            return { success: false, message: 'El email es obligatorio' };
          }
          if (errors.email.includes('email')) {
            return { success: false, message: 'El formato del email no es válido' };
          }
        }
        
        if (errors.name) {
          if (errors.name.includes('required')) {
            return { success: false, message: 'El nombre es obligatorio' };
          }
          if (errors.name.includes('max')) {
            return { success: false, message: 'El nombre es demasiado largo' };
          }
        }
        
        if (errors.password) {
          if (errors.password.includes('required')) {
            return { success: false, message: 'La contraseña es obligatoria' };
          }
          if (errors.password.includes('min')) {
            return { success: false, message: 'La contraseña debe tener al menos 8 caracteres' };
          }
        }
        
        if (errors.password_confirmation) {
          if (errors.password_confirmation.includes('confirmed')) {
            return { success: false, message: 'Las contraseñas no coinciden' };
          }
        }
        
        const firstError = Object.values(errors)[0];
        return { success: false, message: Array.isArray(firstError) ? firstError[0] : firstError };
      }
      return { success: false, message: error.response?.data?.message || 'Error al registrarse' };
    }
  };

  const logout = async () => {
    try {
      await authService.logout();
    } catch (error) {
      console.error('Error al cerrar sesión:', error);
    } finally {
      setUser(null);
      localStorage.removeItem('user');
      localStorage.removeItem('auth_token');
    }
  };

  const updateUserPoints = (newPoints) => {
    if (user) {
      const updatedUser = { ...user, points: newPoints };
      setUser(updatedUser);
      localStorage.setItem('user', JSON.stringify(updatedUser));
    }
  };

  const refreshUserData = async () => {
    try {
      const response = await authService.getProfile();
      if (response.success) {
        const updatedUser = response.data.user;
        setUser(updatedUser);
        localStorage.setItem('user', JSON.stringify(updatedUser));
        
        window.dispatchEvent(new CustomEvent('userDataUpdated', { detail: updatedUser }));
      }
    } catch (error) {
      console.error('Error refreshing user data:', error);
    }
  };

  const refreshUserPoints = async () => {
    try {
      const response = await api.get('/points');
      if (response.data.success && user) {
        const updatedUser = { ...user, points: response.data.data.points };
        setUser(updatedUser);
        localStorage.setItem('user', JSON.stringify(updatedUser));
      }
    } catch (error) {
      console.error('Error refreshing user points:', error);
    }
  };

  const value = {
    user,
    loading,
    initialized,
    login,
    register,
    logout,
    updateUserPoints,
    refreshUserData,
    refreshUserPoints,
    isAuthenticated: !!user
  };

  return (
    <AuthContext.Provider value={value}>
      {children}
    </AuthContext.Provider>
  );
}; 