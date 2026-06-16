import { createContext, useContext, useEffect, useState, useCallback } from 'react';
import { api, tokenStore } from '../api/client';

const AuthContext = createContext(null);

export function AuthProvider({ children }) {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    if (!tokenStore.get()) {
      setLoading(false);
      return;
    }
    api
      .get('/api/auth/me')
      .then(({ user }) => setUser(user))
      .catch(() => tokenStore.clear())
      .finally(() => setLoading(false));
  }, []);

  const finishAuth = useCallback(({ token, user }) => {
    tokenStore.set(token);
    setUser(user);
  }, []);

  const login = useCallback(
    async (login, password) => {
      const data = await api.post('/api/auth/login', { login, password });
      finishAuth(data);
      return data.user;
    },
    [finishAuth]
  );

  const register = useCallback(
    async (payload) => {
      const data = await api.post('/api/auth/register', payload);
      finishAuth(data);
      return data.user;
    },
    [finishAuth]
  );

  const logout = useCallback(() => {
    tokenStore.clear();
    setUser(null);
  }, []);

  const value = {
    user,
    loading,
    isAuthenticated: !!user,
    isAdmin: user?.role === 'admin',
    login,
    register,
    logout,
    setUser,
  };

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export const useAuth = () => useContext(AuthContext);
