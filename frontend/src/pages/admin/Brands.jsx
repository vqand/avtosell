import { useEffect, useState } from 'react';
import { api } from '../../api/client';

export default function AdminBrands() {
  const [brands, setBrands] = useState([]);
  const [name, setName] = useState('');
  const [origin, setOrigin] = useState('foreign');

  const load = () => api.get('/api/brands').then(({ data }) => setBrands(data)).catch(() => {});
  useEffect(() => { load(); }, []);

  const create = async (e) => {
    e.preventDefault();
    if (!name.trim()) return;
    await api.post('/api/admin/brands', { name: name.trim(), origin });
    setName('');
    load();
  };

  const remove = async (b) => {
    if (!window.confirm(`Удалить бренд «${b.name}»? Это возможно только если нет связанных авто.`)) return;
    try {
      await api.del(`/api/admin/brands/${b.id}`);
      load();
    } catch (err) {
      alert(err.message || 'Не удалось удалить бренд (есть связанные объявления).');
    }
  };

  return (
    <div>
      <h1>Бренды</h1>

      <form className="card row" style={{ alignItems: 'flex-end', maxWidth: 560 }} onSubmit={create}>
        <label className="field" style={{ flex: 1, marginBottom: 0 }}>
          <span>Название</span>
          <input className="input" value={name} onChange={(e) => setName(e.target.value)} />
        </label>
        <label className="field" style={{ marginBottom: 0 }}>
          <span>Происхождение</span>
          <select className="select" value={origin} onChange={(e) => setOrigin(e.target.value)}>
            <option value="foreign">Иномарка</option>
            <option value="domestic">Отечественный</option>
          </select>
        </label>
        <button className="btn">Добавить</button>
      </form>

      <table className="table" style={{ marginTop: 20 }}>
        <thead><tr><th>Название</th><th>Происхождение</th><th>Авто</th><th></th></tr></thead>
        <tbody>
          {brands.map((b) => (
            <tr key={b.id}>
              <td>{b.name}</td>
              <td>{b.origin === 'domestic' ? 'Отечественный' : 'Иномарка'}</td>
              <td>{b.cars_count}</td>
              <td><button className="btn btn--danger btn--sm" onClick={() => remove(b)}>Удалить</button></td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}
