import { useState } from 'react';
import { Link } from 'react-router-dom';
import { api } from '../api/client';
import { useAuth } from '../context/AuthContext';

export default function Profile() {
  const { user, setUser } = useAuth();
  const [form, setForm] = useState({ name: user?.name || '', phone: user?.phone || '' });
  const [saved, setSaved] = useState(false);
  const [busy, setBusy] = useState(false);

  const submit = async (e) => {
    e.preventDefault();
    setBusy(true);
    setSaved(false);
    try {
      const { user: updated } = await api.put('/api/auth/profile', {
        name: form.name.trim(),
        phone: form.phone.trim(),
      });
      setUser(updated);
      setSaved(true);
    } finally {
      setBusy(false);
    }
  };

  return (
    <div className="page">
      <div className="spread">
        <h1>Профиль</h1>
        <Link to="/my-listings" className="btn btn--ghost btn--sm">Мои объявления</Link>
      </div>

      <form className="card stack" style={{ maxWidth: 460 }} onSubmit={submit}>
        <label className="field">
          <span>Имя</span>
          <input className="input" value={form.name} onChange={(e) => setForm({ ...form, name: e.target.value })} />
        </label>
        <label className="field">
          <span>Телефон</span>
          <input className="input" value={form.phone} onChange={(e) => setForm({ ...form, phone: e.target.value })} />
        </label>
        <label className="field">
          <span>Почта</span>
          <input className="input" value={user?.email || ''} disabled />
        </label>
        {saved && <div className="muted">Сохранено ✓</div>}
        <button className="btn" disabled={busy}>{busy ? '…' : 'Сохранить'}</button>
      </form>
    </div>
  );
}
