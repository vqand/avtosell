import { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';

export default function Register() {
  const { register } = useAuth();
  const navigate = useNavigate();

  const [form, setForm] = useState({ name: '', email: '', phone: '', password: '' });
  const [errors, setErrors] = useState({});
  const [error, setError] = useState('');
  const [busy, setBusy] = useState(false);

  const set = (k) => (e) => setForm({ ...form, [k]: e.target.value });

  const submit = async (e) => {
    e.preventDefault();
    setError('');
    setErrors({});
    setBusy(true);
    try {
      await register({
        name: form.name.trim(),
        email: form.email.trim(),
        phone: form.phone.trim(),
        password: form.password,
      });
      navigate('/', { replace: true });
    } catch (err) {
      if (err.fields) setErrors(err.fields);
      else if (err.status === 409) setError('Эта почта уже зарегистрирована');
      else setError(err.message);
    } finally {
      setBusy(false);
    }
  };

  return (
    <div className="auth-wrap">
      <Link to="/" className="logo logo--xl">5VITO</Link>
      <form className="card stack" onSubmit={submit}>
        <h2>Регистрация</h2>
        <input className="input" placeholder="имя" value={form.name} onChange={set('name')} />
        {errors.name && <div className="error-text">{errors.name[0]}</div>}
        <input className="input" placeholder="почта" value={form.email} onChange={set('email')} />
        {errors.email && <div className="error-text">{errors.email[0]}</div>}
        <input className="input" placeholder="телефон" value={form.phone} onChange={set('phone')} />
        <input className="input" type="password" placeholder="пароль (мин. 6 символов)" value={form.password} onChange={set('password')} />
        {errors.password && <div className="error-text">{errors.password[0]}</div>}
        {error && <div className="error-text">{error}</div>}
        <button className="btn btn--block" disabled={busy}>{busy ? '…' : 'Создать аккаунт'}</button>
        <span className="muted">Уже есть аккаунт? <Link to="/login" className="link">Войти</Link></span>
      </form>
    </div>
  );
}
