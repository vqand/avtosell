import { useNavigate } from 'react-router-dom';

export default function Success() {
  const navigate = useNavigate();
  return (
    <div className="center-screen stack" style={{ textAlign: 'center' }}>
      <div className="big-msg">Успешно!</div>
      <button className="btn btn--ghost" onClick={() => navigate('/')}>Вернуться в каталог</button>
    </div>
  );
}
