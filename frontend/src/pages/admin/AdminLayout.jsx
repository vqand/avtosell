import { NavLink, Outlet } from 'react-router-dom';

const links = [
  ['/admin', 'Дашборд', true],
  ['/admin/users', 'Пользователи'],
  ['/admin/cars', 'Объявления'],
  ['/admin/reports', 'Жалобы'],
  ['/admin/brands', 'Бренды'],
];

export default function AdminLayout() {
  return (
    <div className="admin">
      <nav className="admin-nav stack">
        <b>Админ-панель</b>
        {links.map(([to, label, end]) => (
          <NavLink key={to} to={to} end={end}
                   className={({ isActive }) => (isActive ? 'active' : '')}>
            {label}
          </NavLink>
        ))}
      </nav>
      <section><Outlet /></section>
    </div>
  );
}
