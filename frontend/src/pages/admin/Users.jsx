import { useEffect, useState } from 'react';
import { api } from '../../api/client';

export default function AdminUsers() {
  const [users, setUsers] = useState([]);

  const load = () => api.get('/api/admin/users').then(({ data }) => setUsers(data)).catch(() => {});
  useEffect(() => { load(); }, []);

  const toggleBlock = async (u) => {
    await api.patch(`/api/admin/users/${u.id}/block`, { blocked: !u.is_blocked });
    load();
  };

  const remove = async (u) => {
    if (!window.confirm(`Удалить пользователя «${u.name}»?`)) return;
    await api.del(`/api/admin/users/${u.id}`);
    load();
  };

  return (
    <div>
      <h1>Пользователи</h1>
      <table className="table">
        <thead>
          <tr><th>Имя</th><th>Почта</th><th>Телефон</th><th>Роль</th><th>Объявлений</th><th>Статус</th><th></th></tr>
        </thead>
        <tbody>
          {users.map((u) => (
            <tr key={u.id}>
              <td>{u.name}</td>
              <td>{u.email}</td>
              <td>{u.phone || '—'}</td>
              <td>{u.role}</td>
              <td>{u.cars_count}</td>
              <td>{u.is_blocked ? <span className="badge badge--rejected">Заблокирован</span> : <span className="badge badge--approved">Активен</span>}</td>
              <td>
                <div className="row">
                  <button className="btn btn--ghost btn--sm" onClick={() => toggleBlock(u)}>
                    {u.is_blocked ? 'Разблокировать' : 'Заблокировать'}
                  </button>
                  <button className="btn btn--danger btn--sm" onClick={() => remove(u)}>Удалить</button>
                </div>
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}
