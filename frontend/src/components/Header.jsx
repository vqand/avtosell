import { useState } from 'react';
import { Link, useNavigate, useSearchParams } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';

export default function Header() {
  const { isAuthenticated, isAdmin, logout } = useAuth();
  const navigate = useNavigate();
  const [params] = useSearchParams();
  const [q, setQ] = useState(params.get('q') || '');

  const submit = (e) => {
    e.preventDefault();
    navigate(`/?q=${encodeURIComponent(q.trim())}`);
  };

  return (
    <header className="header">
      <div className="container header__row">
        <Link to="/" className="logo">5VITO</Link>

        <form className="header__search" onSubmit={submit}>
          <input
            className="input search-input"
            placeholder="Поиск авто"
            value={q}
            onChange={(e) => setQ(e.target.value)}
          />
        </form>

        {isAdmin && (
          <Link to="/admin" className="link" title="Админ-панель">⚙</Link>
        )}

        {isAuthenticated ? (
          <>
            <Link to="/favorites" className="icon-btn" title="Избранное">♡</Link>
            <Link to="/profile" className="icon-btn" title="Профиль">⚇</Link>
            <button
              className="icon-btn"
              title="Выйти"
              onClick={() => { logout(); navigate('/login'); }}
            >
              ⎋
            </button>
          </>
        ) : (
          <Link to="/login" className="icon-btn" title="Войти">⚇</Link>
        )}
      </div>
    </header>
  );
}
