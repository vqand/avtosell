import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { api } from '../../api/client';

const RSTATUS = { open: 'Открыта', reviewed: 'Рассмотрена', dismissed: 'Отклонена' };

export default function AdminReports() {
  const [reports, setReports] = useState([]);

  const load = () => api.get('/api/admin/reports').then(({ data }) => setReports(data)).catch(() => {});
  useEffect(() => { load(); }, []);

  const setStatus = async (id, status) => {
    await api.patch(`/api/admin/reports/${id}`, { status });
    load();
  };

  return (
    <div>
      <h1>Жалобы на объявления</h1>
      {reports.length === 0 && <p className="muted">Жалоб нет.</p>}
      <table className="table">
        <thead><tr><th>Объявление</th><th>Причина</th><th>Кто</th><th>Статус</th><th></th></tr></thead>
        <tbody>
          {reports.map((r) => (
            <tr key={r.id}>
              <td><Link className="link" to={`/cars/${r.car_id}`}>{r.car_title}</Link></td>
              <td>{r.reason}</td>
              <td>{r.reporter_name || '—'}</td>
              <td>{RSTATUS[r.status]}</td>
              <td>
                <div className="row">
                  <button className="btn btn--ghost btn--sm" onClick={() => setStatus(r.id, 'reviewed')}>Рассмотрена</button>
                  <button className="btn btn--ghost btn--sm" onClick={() => setStatus(r.id, 'dismissed')}>Отклонить</button>
                </div>
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}
