import { useEffect, useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { api } from '../api/client';
import { FUEL, TRANSMISSION, DRIVE } from '../lib/format';

const EMPTY = {
  title: '', brand_id: '', model_id: '', price: '', year: new Date().getFullYear(),
  mileage: 0, fuel_type: 'petrol', transmission: 'automatic', drive_type: 'front',
  body_type: '', engine_volume: '', power_hp: '', color: '', owners_count: '',
  vin: '', description: '',
};

export default function CarForm() {
  const { id } = useParams();
  const editing = !!id;
  const navigate = useNavigate();

  const [form, setForm] = useState(EMPTY);
  const [brands, setBrands] = useState([]);
  const [models, setModels] = useState([]);
  const [errors, setErrors] = useState({});
  const [error, setError] = useState('');
  const [busy, setBusy] = useState(false);
  const [file, setFile] = useState(null);

  const set = (k) => (e) => setForm({ ...form, [k]: e.target.value });

  const onBrandChange = (e) =>
    setForm({ ...form, brand_id: e.target.value, model_id: '' });

  useEffect(() => {
    api.get('/api/brands').then(({ data }) => setBrands(data)).catch(() => {});
  }, []);

  useEffect(() => {
    if (editing) {
      api.get(`/api/cars/${id}`).then(({ car }) => {
        setForm({ ...EMPTY, ...car });
      }).catch(() => setError('Не удалось загрузить объявление'));
    }
  }, [id, editing]);

  useEffect(() => {
    if (!form.brand_id) { setModels([]); return; }
    api.get(`/api/brands/${form.brand_id}/models`).then(({ data }) => setModels(data)).catch(() => {});
  }, [form.brand_id]);

  const submit = async (e) => {
    e.preventDefault();
    setError(''); setErrors({});
    if (!form.brand_id) { setErrors({ brand_id: ['Выберите бренд'] }); return; }
    setBusy(true);
    const payload = {
      ...form,
      brand_id: Number(form.brand_id),
      model_id: form.model_id ? Number(form.model_id) : null,
      price: Number(form.price),
      year: Number(form.year),
      mileage: Number(form.mileage) || 0,
      engine_volume: form.engine_volume || null,
      power_hp: form.power_hp || null,
      owners_count: form.owners_count || null,
    };
    try {
      const { car } = editing
        ? await api.put(`/api/cars/${id}`, payload)
        : await api.post('/api/cars', payload);

      if (file) {
        const fd = new FormData();
        fd.append('image', file);
        await api.upload(`/api/cars/${car.id}/images`, fd).catch(() => {});
      }
      navigate('/my-listings');
    } catch (err) {
      if (err.fields) setErrors(err.fields);
      else setError(err.message);
    } finally {
      setBusy(false);
    }
  };

  const enumField = (key, map) => (
    <label className="field">
      <span>{ {fuel_type:'Тип двигателя', transmission:'Коробка передач', drive_type:'Привод'}[key] }</span>
      <select className="select" value={form[key]} onChange={set(key)}>
        {Object.entries(map).map(([k, v]) => <option key={k} value={k}>{v}</option>)}
      </select>
    </label>
  );

  return (
    <div className="page">
      <h1>{editing ? 'Редактировать объявление' : 'Новое объявление'}</h1>
      {error && <div className="error-text">{error}</div>}

      <form className="card" style={{ maxWidth: 720 }} onSubmit={submit}>
        <label className="field">
          <span>Название*</span>
          <input className="input" value={form.title} onChange={set('title')}
                 placeholder="BMW M5 4.4 AT, 2026" />
          {errors.title && <div className="error-text">{errors.title[0]}</div>}
        </label>

        <div className="row">
          <label className="field" style={{ flex: 1 }}>
            <span>Бренд*</span>
            <select className="select" value={form.brand_id} onChange={onBrandChange}>
              <option value="">— выбрать —</option>
              {brands.map((b) => <option key={b.id} value={b.id}>{b.name}</option>)}
            </select>
            {errors.brand_id && <div className="error-text">{errors.brand_id[0]}</div>}
          </label>
          <label className="field" style={{ flex: 1 }}>
            <span>Модель</span>
            <select className="select" value={form.model_id || ''} onChange={set('model_id')} disabled={!models.length}>
              <option value="">— выбрать —</option>
              {models.map((m) => <option key={m.id} value={m.id}>{m.name}</option>)}
            </select>
          </label>
        </div>

        <div className="row">
          <label className="field" style={{ flex: 1 }}>
            <span>Цена, ₽*</span>
            <input className="input" type="number" value={form.price} onChange={set('price')} />
            {errors.price && <div className="error-text">{errors.price[0]}</div>}
          </label>
          <label className="field" style={{ flex: 1 }}>
            <span>Год выпуска*</span>
            <input className="input" type="number" value={form.year} onChange={set('year')} />
            {errors.year && <div className="error-text">{errors.year[0]}</div>}
          </label>
          <label className="field" style={{ flex: 1 }}>
            <span>Пробег, км</span>
            <input className="input" type="number" value={form.mileage} onChange={set('mileage')} />
          </label>
        </div>

        <div className="row">
          {enumField('fuel_type', FUEL)}
          {enumField('transmission', TRANSMISSION)}
          {enumField('drive_type', DRIVE)}
        </div>

        <div className="row">
          <label className="field" style={{ flex: 1 }}>
            <span>Тип кузова</span>
            <input className="input" value={form.body_type || ''} onChange={set('body_type')} />
          </label>
          <label className="field" style={{ flex: 1 }}>
            <span>Объём двигателя, л</span>
            <input className="input" type="number" step="0.1" value={form.engine_volume || ''} onChange={set('engine_volume')} />
          </label>
          <label className="field" style={{ flex: 1 }}>
            <span>Мощность, л.с.</span>
            <input className="input" type="number" value={form.power_hp || ''} onChange={set('power_hp')} />
          </label>
        </div>

        <div className="row">
          <label className="field" style={{ flex: 1 }}>
            <span>Цвет</span>
            <input className="input" value={form.color || ''} onChange={set('color')} />
          </label>
          <label className="field" style={{ flex: 1 }}>
            <span>Владельцев по ПТС</span>
            <input className="input" type="number" value={form.owners_count || ''} onChange={set('owners_count')} />
          </label>
          <label className="field" style={{ flex: 1 }}>
            <span>VIN</span>
            <input className="input" value={form.vin || ''} onChange={set('vin')} />
          </label>
        </div>

        <label className="field">
          <span>Описание</span>
          <textarea className="input" rows="4" value={form.description || ''} onChange={set('description')} />
        </label>

        <label className="field">
          <span>Фотография</span>
          <input type="file" accept="image/*" onChange={(e) => setFile(e.target.files?.[0] || null)} />
        </label>

        <p className="muted">После сохранения объявление сразу публикуется в каталоге.</p>
        <button className="btn" disabled={busy}>{busy ? '…' : 'Сохранить'}</button>
      </form>
    </div>
  );
}
