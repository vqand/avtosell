import { useEffect, useMemo, useState } from 'react';
import { useSearchParams } from 'react-router-dom';
import { api, qs } from '../api/client';
import CarCard from '../components/CarCard';
import { DRIVE } from '../lib/format';

const DRIVE_KEYS = ['rear', 'front', 'full'];

export default function Catalog() {
  const [searchParams] = useSearchParams();
  const q = searchParams.get('q') || '';

  const [brands, setBrands] = useState([]);
  const [cars, setCars] = useState([]);
  const [loading, setLoading] = useState(true);

  const [brandIds, setBrandIds] = useState([]);
  const [origin, setOrigin] = useState([]);
  const [drives, setDrives] = useState([]);
  const [priceMax, setPriceMax] = useState(10000000);
  const [sort, setSort] = useState('new');

  useEffect(() => {
    api.get('/api/brands').then(({ data }) => setBrands(data)).catch(() => {});
  }, []);

  useEffect(() => {
    setLoading(true);
    const params = {
      q,
      brand_ids: brandIds,
      drive_type: drives.length === 1 ? drives[0] : '',
      origin: origin.length === 1 ? origin[0] : '',
      price_max: priceMax < 10000000 ? priceMax : '',
      sort,
      per_page: 24,
    };
    api
      .get(`/api/cars${qs(params)}`)
      .then(({ data }) => setCars(data))
      .catch(() => setCars([]))
      .finally(() => setLoading(false));
  }, [q, brandIds, drives, origin, priceMax, sort]);

  const toggle = (list, setList, value) =>
    setList(list.includes(value) ? list.filter((v) => v !== value) : [...list, value]);

  const activeChips = useMemo(
    () => brands.filter((b) => brandIds.includes(b.id)),
    [brands, brandIds]
  );

  return (
    <div className="catalog">
      <aside className="filters">
        <h4>Фильтры</h4>
        <div className="chips">
          {activeChips.map((b) => (
            <span className="chip" key={b.id}>
              {b.name}
              <button onClick={() => toggle(brandIds, setBrandIds, b.id)}>×</button>
            </span>
          ))}
          {!activeChips.length && <span className="muted">не выбраны</span>}
        </div>

        <h4>Бренд</h4>
        {brands.map((b) => (
          <label className="checkrow" key={b.id}>
            <input
              type="checkbox"
              checked={brandIds.includes(b.id)}
              onChange={() => toggle(brandIds, setBrandIds, b.id)}
            />
            {b.name}
          </label>
        ))}

        <h4>Цена</h4>
        <div className="muted">до {Number(priceMax).toLocaleString('ru-RU')} ₽</div>
        <input
          type="range"
          min="100000"
          max="10000000"
          step="100000"
          value={priceMax}
          onChange={(e) => setPriceMax(Number(e.target.value))}
          style={{ width: '100%' }}
        />

        <h4>Страна бренда</h4>
        {[['domestic', 'Отечественные'], ['foreign', 'Иномарки']].map(([val, lbl]) => (
          <label className="checkrow" key={val}>
            <input
              type="checkbox"
              checked={origin.includes(val)}
              onChange={() => toggle(origin, setOrigin, val)}
            />
            {lbl}
          </label>
        ))}

        <h4>Привод</h4>
        {DRIVE_KEYS.map((d) => (
          <label className="checkrow" key={d}>
            <input
              type="checkbox"
              checked={drives.includes(d)}
              onChange={() => toggle(drives, setDrives, d)}
            />
            {DRIVE[d]}
          </label>
        ))}
      </aside>

      <section>
        <div className="spread" style={{ marginBottom: 16 }}>
          <span className="muted">{loading ? 'Загрузка…' : `Найдено: ${cars.length}`}</span>
          <select className="select" style={{ width: 200 }} value={sort} onChange={(e) => setSort(e.target.value)}>
            <option value="new">Сначала новые</option>
            <option value="price_asc">Дешевле</option>
            <option value="price_desc">Дороже</option>
            <option value="year_desc">Год выпуска</option>
          </select>
        </div>

        {!loading && cars.length === 0 && <p className="muted">Ничего не найдено.</p>}

        <div className="grid">
          {cars.map((car) => <CarCard key={car.id} car={car} />)}
        </div>
      </section>
    </div>
  );
}
