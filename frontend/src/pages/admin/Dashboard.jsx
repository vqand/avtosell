import { useEffect, useState } from 'react';
import { api } from '../../api/client';
import { formatPrice, STATUS } from '../../lib/format';

const STAT_LABELS = {
  total_users: 'Пользователей',
  total_cars: 'Всего авто',
  active_cars: 'Активных объявлений',
  pending_cars: 'На модерации',
  open_reports: 'Открытых жалоб',
  blocked_users: 'Заблокировано',
};

export default function Dashboard() {
  const [data, setData] = useState(null);

  useEffect(() => {
    api.get('/api/admin/dashboard').then(setData).catch(() => {});
  }, []);

  if (!data) return <p className="muted">Загрузка…</p>;

  return (
    <div>
      <h1>Статистика</h1>
      <div className="stat-grid">
        {Object.entries(STAT_LABELS).map(([key, label]) => (
          <div className="stat" key={key}>
            <div className="stat__num">{data.stats[key]}</div>
            <div className="stat__label">{label}</div>
          </div>
        ))}
      </div>

      <h1>Недавние объявления</h1>
      <table className="table">
        <thead><tr><th>Название</th><th>Цена</th><th>Продавец</th><th>Статус</th><th>Дата</th></tr></thead>
        <tbody>
          {data.recent_cars.map((c) => (
            <tr key={c.id}>
              <td>{c.title}</td>
              <td>{formatPrice(c.price)}</td>
              <td>{c.seller}</td>
              <td><span className={`badge badge--${c.status}`}>{STATUS[c.status]}</span></td>
              <td>{new Date(c.created_at).toLocaleDateString('ru-RU')}</td>
            </tr>
          ))}
        </tbody>
      </table>

      {data.recent_logs?.length > 0 && (
        <>
          <h1>Журнал действий</h1>
          <table className="table">
            <thead><tr><th>Администратор</th><th>Действие</th><th>Объект</th><th>Дата</th></tr></thead>
            <tbody>
              {data.recent_logs.map((l) => (
                <tr key={l.id}>
                  <td>{l.admin_name}</td>
                  <td>{l.action}</td>
                  <td>{l.entity_type} #{l.entity_id}</td>
                  <td>{new Date(l.created_at).toLocaleString('ru-RU')}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </>
      )}
    </div>
  );
}
