// src/contexts/AuthContext.tsx
import { createContext, useState, useContext, useEffect, type ReactNode } from 'react';
import api from '../services/api';

// 型定義
interface User {
  id: number;
  name: string;
  email: string;
  email_verified_at: string | null;
  created_at: string;
  updated_at: string;
}

interface AuthContextType {
  user: User | null;
  token: string | null;
  loading: boolean;
  isAuthenticated: boolean;
  login: (email: string, password: string) => Promise<LoginResult>;
  register: (
    name: string,
    email: string,
    password: string,
    passwordConfirmation: string
  ) => Promise<RegisterResult>;
  logout: () => Promise<void>;
}

interface LoginResult {
  success: boolean;
  message?: string;
}

interface RegisterResult {
  success: boolean;
  message?: string;
  errors?: Record<string, string[]>;
}

// Context作成
const AuthContext = createContext<AuthContextType | null>(null);

// Provider Props型定義
interface AuthProviderProps {
  children: ReactNode;
}

// Provider実装
export const AuthProvider = ({ children }: AuthProviderProps) => {
  const [user, setUser] = useState<User | null>(null);
  const [token, setToken] = useState<string | null>(localStorage.getItem('token'));
  const [loading, setLoading] = useState<boolean>(true);

  // トークンをaxiosのヘッダーに設定
  useEffect(() => {
    if (token) {
      api.defaults.headers.common['Authorization'] = `Bearer ${token}`;
      fetchUser();
    } else {
      delete api.defaults.headers.common['Authorization'];
      setLoading(false);
    }
  }, [token]);

  // ユーザー情報取得
  const fetchUser = async () => {
    try {
      const response = await api.get<User>('/me');
      setUser(response.data);
    } catch (error) {
      console.error('Failed to fetch user:', error);
      logout();
    } finally {
      setLoading(false);
    }
  };

  // ログイン
  const login = async (email: string, password: string): Promise<LoginResult> => {
    try {
      const response = await api.post<{ user: User; token: string }>('/login', {
        email,
        password,
      });

      const { token: newToken, user: userData } = response.data;

      setToken(newToken);
      localStorage.setItem('token', newToken);
      setUser(userData);

      return { success: true };
    } catch (error: any) {
      return {
        success: false,
        message: error.response?.data?.message || 'ログインに失敗しました',
      };
    }
  };

  // 登録
  const register = async (
    name: string,
    email: string,
    password: string,
    passwordConfirmation: string
  ): Promise<RegisterResult> => {
    try {
      const response = await api.post<{ user: User; token: string }>('/register', {
        name,
        email,
        password,
        password_confirmation: passwordConfirmation,
      });

      const { token: newToken, user: userData } = response.data;

      setToken(newToken);
      localStorage.setItem('token', newToken);
      setUser(userData);

      return { success: true };
    } catch (error: any) {
      return {
        success: false,
        errors: error.response?.data?.errors || {},
        message: error.response?.data?.message || '登録に失敗しました',
      };
    }
  };

  // ログアウト
  const logout = async (): Promise<void> => {
    try {
      if (token) {
        await api.post('/logout');
      }
    } catch (error) {
      console.error('Logout error:', error);
    } finally {
      setToken(null);
      setUser(null);
      localStorage.removeItem('token');
      delete api.defaults.headers.common['Authorization'];
    }
  };

  const value: AuthContextType = {
    user,
    token,
    loading,
    login,
    register,
    logout,
    isAuthenticated: !!user,
  };

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
};

// カスタムフック
export const useAuth = (): AuthContextType => {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error('useAuth must be used within AuthProvider');
  }
  return context;
};