import { useEffect, useState } from 'react';
import { useParams, useNavigate, Link } from 'react-router-dom';
import { api } from '../api/client';
import { useAuth } from '../context/AuthContext';
import { formatPrice, formatNumber, FUEL, TRANSMISSION, DRIVE } from '../lib/format';

export default function CarDetail() {
  const { id } = useParams();
  const navigate = useNavigate();
  const { isAuthenticated } = useAuth();

  const [car, setCar] = useState(null);
  const [fav, setFav] = useState(false);
  const [error, setError] = useState('');

  useEffect(() => {
    api
      .get(`/api/cars/${id}`)
      .then(({ car }) => { setCar(car); setFav(!!car.is_favorite); })
      .catch((e) => setError(e.status === 404 ? 'Объявление не найдено' : e.message));
  }, [id]);

  const toggleFav = async () => {
    if (!isAuthenticated) return navigate('/login');
    try {
      if (fav) { await api.del(`/api/favorites/${id}`); setFav(false); }
      else { await api.post(`/api/favorites/${id}`); setFav(true); }
    } catch (e) { void e; }
  };

  const report = async () => {
    const reason = window.prompt('Причина жалобы:');
    if (!reason) return;
    try {
      await api.post(`/api/cars/${id}/report`, { reason });
      alert('Жалоба отправлена. Спасибо!');
    } catch (e) { alert(e.message); }
  };

  if (error) return <div className="page"><h1>{error}</h1></div>;
  if (!car) return <div className="page muted">Загрузка…</div>;

  const primaryImage = car.images?.[0]?.url || '/placeholder-car.svg';

  const specs = [
    ['Год выпуска', car.year],
    ['Пробег', `${formatNumber(car.mileage)} км`],
    ['Владельцев по ПТС', car.owners_count],
    ['Объём двигателя', car.engine_volume ? `${car.engine_volume} л` : null],
    ['Мощность', car.power_hp ? `${car.power_hp} л.с.` : null],
    ['Тип двигателя', FUEL[car.fuel_type]],
    ['Коробка передач', TRANSMISSION[car.transmission]],
    ['Привод', DRIVE[car.drive_type]],
    ['Тип кузова', car.body_type],
    ['Цвет', car.color],
    ['VIN или номер кузова', car.vin],
  ].filter(([, v]) => v !== null && v !== undefined && v !== '');

  return (
    <div className="page">
      <div className="detail-head">
        <h1>{car.title}</h1>
        <div className="detail-price">{formatPrice(car.price)}</div>
      </div>

      <div className="detail-actions">
        <button onClick={toggleFav}>{fav ? '♥' : '♡'} {fav ? 'В избранном' : 'Добавить в избранное'}</button>
        <button onClick={report}>⚠ Пожаловаться на объявление</button>
      </div>

      <div className="detail-grid">
        <div>
          <img className="detail-img" src={primaryImage} alt={car.title}
               onError={(e) => { e.currentTarget.src = '/placeholder-car.svg'; }} />
          {car.images?.length > 1 && (
            <div className="row" style={{ marginTop: 10 }}>
              {car.images.map((img) => (
                <img key={img.id} src={img.url} alt="" width="90" height="64"
                     style={{ objectFit: 'cover', borderRadius: 4 }} />
              ))}
            </div>
          )}
          {car.description && <p style={{ whiteSpace: 'pre-line', marginTop: 16 }}>{car.description}</p>}
        </div>

        <div>
          <ul className="spec-list">
            {specs.map(([k, v]) => <li key={k}><b>{k}:</b> {v}</li>)}
          </ul>

          <div className="contact-row">
            {car.seller_phone && (
              <a className="btn btn--ghost" href={`tel:${car.seller_phone}`}>
                Позвонить<br />{car.seller_phone}
              </a>
            )}
            <Link className="btn" to={`/cars/${car.id}/buy`}>Заявка на покупку</Link>
          </div>
          <p className="muted" style={{ marginTop: 18 }}>
            Продавец: {car.seller_name}<br />
            Дата публикации: {new Date(car.created_at).toLocaleDateString('ru-RU')}
          </p>
        </div>
      </div>
    </div>
  );
}
