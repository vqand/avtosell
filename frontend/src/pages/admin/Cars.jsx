import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { api } from '../../api/client';
import { formatPrice, STATUS } from '../../lib/format';

export default function AdminCars() {
  const [cars, setCars] = useState([]);
  const [filter, setFilter] = useState('');

  const load = () => {
    const q = filter ? `?status=${filter}` : '';
    api.get(`/api/admin/cars${q}`).then(({ data }) => setCars(data)).catch(() => {});
  };
  useEffect(load, [filter]);

  const setStatus = async (id, status) => {
    await api.patch(`/api/admin/cars/${id}/status`, { status });
    load();
  };

  const remove = async (id) => {
    if (!window.confirm('Удалить объявление?')) return;
    await api.del(`/api/admin/cars/${id}`);
    load();
  };

  return (
    <div>
      <div className="spread">
        <h1>Объявления</h1>
        <select className="select" style={{ width: 200 }} value={filter} onChange={(e) => setFilter(e.target.value)}>
          <option value="">Все</option>
          <option value="pending">На модерации</option>
          <option value="approved">Одобренные</option>
          <option value="rejected">Отклонённые</option>
        </select>
      </div>

      <table className="table">
        <thead><tr><th>Название</th><th>Цена</th><th>Статус</th><th>Действия</th></tr></thead>
        <tbody>
          {cars.map((c) => (
            <tr key={c.id}>
              <td><Link className="link" to={`/cars/${c.id}`}>{c.title}</Link></td>
              <td>{formatPrice(c.price)}</td>
              <td><span className={`badge badge--${c.status}`}>{STATUS[c.status]}</span></td>
              <td>
                <div className="row">
                  {c.status !== 'approved' && <button className="btn btn--ghost btn--sm" onClick={() => setStatus(c.id, 'approved')}>Одобрить</button>}
                  {c.status !== 'rejected' && <button className="btn btn--ghost btn--sm" onClick={() => setStatus(c.id, 'rejected')}>Отклонить</button>}
                  <button className="btn btn--danger btn--sm" onClick={() => remove(c.id)}>Удалить</button>
                </div>
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}
