import { useState } from 'react';
import { Link, useNavigate, useLocation } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';

export default function Login() {
  const { login } = useAuth();
  const navigate = useNavigate();
  const location = useLocation();
  const from = location.state?.from?.pathname || '/';

  const [form, setForm] = useState({ login: '', password: '' });
  const [error, setError] = useState('');
  const [busy, setBusy] = useState(false);

  const submit = async (e) => {
    e.preventDefault();
    setError('');
    setBusy(true);
    try {
      await login(form.login.trim(), form.password);
      navigate(from, { replace: true });
    } catch (err) {
      setError(err.status === 401 ? 'Неверный логин или пароль' : err.message);
    } finally {
      setBusy(false);
    }
  };

  return (
    <div className="auth-wrap">
      <Link to="/" className="logo logo--xl">5VITO</Link>
      <form className="card stack" onSubmit={submit}>
        <h2>Войти</h2>
        <input
          className="input"
          placeholder="телефон или почта"
          value={form.login}
          onChange={(e) => setForm({ ...form, login: e.target.value })}
          autoFocus
        />
        <input
          className="input"
          type="password"
          placeholder="пароль"
          value={form.password}
          onChange={(e) => setForm({ ...form, password: e.target.value })}
        />
        {error && <div className="error-text">{error}</div>}
        <button className="btn btn--block" disabled={busy}>
          {busy ? '…' : 'Sign In'}
        </button>
        <div className="spread">
          <span className="muted">Нет аккаунта? <Link to="/register" className="link">Регистрация</Link></span>
        </div>
      </form>
    </div>
  );
}
