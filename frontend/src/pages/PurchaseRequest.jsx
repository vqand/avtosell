import { useEffect, useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { api } from '../api/client';

export default function PurchaseRequest() {
  const { id } = useParams();
  const navigate = useNavigate();
  const [car, setCar] = useState(null);
  const [phone, setPhone] = useState('+7');
  const [date, setDate] = useState('');

  useEffect(() => {
    api.get(`/api/cars/${id}`).then(({ car }) => setCar(car)).catch(() => {});
  }, [id]);

  const submit = (e) => {
    e.preventDefault();
    if (!phone.trim() || !date) return;
    navigate('/success');
  };

  return (
    <div className="page">
      <h1>Заявка на осмотр (покупку) данного автомобиля</h1>
      <div className="detail-grid">
        <img
          className="detail-img"
          src={car?.images?.[0]?.url || '/placeholder-car.svg'}
          alt={car?.title || ''}
          onError={(e) => { e.currentTarget.src = '/placeholder-car.svg'; }}
        />
        <form className="stack" onSubmit={submit}>
          <p className="muted">{car?.title}</p>
          <label className="field">
            <span>Оставьте ваш контактный номер телефона</span>
            <input className="input" value={phone} onChange={(e) => setPhone(e.target.value)} placeholder="+7..." />
          </label>
          <label className="field">
            <span>Дата осмотра</span>
            <input className="input" type="date" value={date} onChange={(e) => setDate(e.target.value)} />
          </label>
          <button className="btn btn--block">ОТПРАВИТЬ</button>
        </form>
      </div>
    </div>
  );
}
