import { useState, useEffect, createContext, useContext } from 'react';
import { User } from '../types/inventory';
import { AuthApi } from '../lib/api';

interface AuthContextType {
  user: User | null;
  login: (email: string, password: string) => Promise<boolean>;
  register: (email: string, password: string, name: string) => Promise<boolean>;
  logout: () => void;
  updateProfile: (updates: Partial<User>) => void;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export const useAuth = () => {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
};

export const useAuthProvider = () => {
  const [user, setUser] = useState<User | null>(null);

  useEffect(() => {
    const savedToken = localStorage.getItem('authToken');
    const savedUser = localStorage.getItem('currentUser');
    if (savedUser) {
      setUser(JSON.parse(savedUser));
    }
    if (savedToken && !savedUser) {
      AuthApi.me().then((me) => {
        const normalized: User = {
          id: String(me.id),
          email: me.email,
          name: me.name,
          role: (me.role || 'admin') as 'admin' | 'user',
          createdAt: me.created_at,
        };
        setUser(normalized);
        localStorage.setItem('currentUser', JSON.stringify(normalized));
      }).catch(() => {
        localStorage.removeItem('authToken');
        localStorage.removeItem('currentUser');
      });
    }
  }, []);

  const login = async (email: string, password: string): Promise<boolean> => {
    try {
      const { token, user: me } = await AuthApi.login(email, password);
      localStorage.setItem('authToken', token);
      const normalized: User = {
        id: String(me.id),
        email: me.email,
        name: me.name,
        role: (me.role || 'admin') as 'admin' | 'user',
        createdAt: me.created_at,
      };
      setUser(normalized);
      localStorage.setItem('currentUser', JSON.stringify(normalized));
      return true;
    } catch (e) {
      return false;
    }
  };

  const register = async (email: string, password: string, name: string): Promise<boolean> => {
    try {
      const { token, user: me } = await AuthApi.register(name, email, password);
      localStorage.setItem('authToken', token);
      const normalized: User = {
        id: String(me.id),
        email: me.email,
        name: me.name,
        role: (me.role || 'admin') as 'admin' | 'user',
        createdAt: me.created_at,
      };
      setUser(normalized);
      localStorage.setItem('currentUser', JSON.stringify(normalized));
      return true;
    } catch (e) {
      return false;
    }
  };

  const logout = async () => {
    try { await AuthApi.logout(); } catch {}
    setUser(null);
    localStorage.removeItem('currentUser');
    localStorage.removeItem('authToken');
  };

  const updateProfile = (updates: Partial<User>) => {
    if (user) {
      const updatedUser = { ...user, ...updates };
      setUser(updatedUser);
      localStorage.setItem('currentUser', JSON.stringify(updatedUser));
    }
  };

  return {
    user,
    login,
    register,
    logout,
    updateProfile,
  };
};

export { AuthContext };