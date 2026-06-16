import { useNavigate } from 'react-router-dom';
import { formatPrice } from '../lib/format';

export default function CarCard({ car }) {
  const navigate = useNavigate();
  return (
    <div className="car-card" onClick={() => navigate(`/cars/${car.id}`)}>
      <img
        className="car-card__img"
        src={car.image || '/placeholder-car.svg'}
        alt={car.title}
        onError={(e) => { e.currentTarget.src = '/placeholder-car.svg'; }}
      />
      <div className="car-card__title">{car.title}</div>
      <div className="car-card__price">{formatPrice(car.price)}</div>
    </div>
  );
}
