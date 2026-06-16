import { useEffect, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { api } from '../api/client';
import { formatPrice, STATUS } from '../lib/format';

export default function MyListings() {
  const navigate = useNavigate();
  const [cars, setCars] = useState([]);
  const [loading, setLoading] = useState(true);

  const load = () => {
    setLoading(true);
    api.get('/api/my/cars')
      .then(({ data }) => setCars(data))
      .catch(() => setCars([]))
      .finally(() => setLoading(false));
  };

  useEffect(load, []);

  const remove = async (id) => {
    if (!window.confirm('Удалить объявление?')) return;
    await api.del(`/api/cars/${id}`);
    load();
  };

  return (
    <div className="page">
      <div className="spread">
        <h1>Мои объявления</h1>
        <Link to="/listings/new" className="btn btn--sm">+ Разместить авто</Link>
      </div>

      {loading ? (
        <p className="muted">Загрузка…</p>
      ) : cars.length === 0 ? (
        <p className="muted">У вас пока нет объявлений.</p>
      ) : (
        <table className="table">
          <thead>
            <tr><th>Название</th><th>Цена</th><th>Статус</th><th></th></tr>
          </thead>
          <tbody>
            {cars.map((c) => (
              <tr key={c.id}>
                <td><Link className="link" to={`/cars/${c.id}`}>{c.title}</Link></td>
                <td>{formatPrice(c.price)}</td>
                <td><span className={`badge badge--${c.status}`}>{STATUS[c.status]}</span></td>
                <td>
                  <div className="row">
                    <button className="btn btn--ghost btn--sm" onClick={() => navigate(`/listings/${c.id}/edit`)}>Изменить</button>
                    <button className="btn btn--danger btn--sm" onClick={() => remove(c.id)}>Удалить</button>
                  </div>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      )}
    </div>
  );
}
