import { useEffect, useState } from 'react';
import { api } from '../api/client';
import CarCard from '../components/CarCard';

export default function Favorites() {
  const [cars, setCars] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    api.get('/api/favorites')
      .then(({ data }) => setCars(data))
      .catch(() => setCars([]))
      .finally(() => setLoading(false));
  }, []);

  return (
    <div className="page">
      <h1>Избранное</h1>
      {loading ? (
        <p className="muted">Загрузка…</p>
      ) : cars.length === 0 ? (
        <p className="muted">Вы пока ничего не добавили в избранное.</p>
      ) : (
        <div className="grid">
          {cars.map((c) => <CarCard key={c.id} car={c} />)}
        </div>
      )}
    </div>
  );
}
