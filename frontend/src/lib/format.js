export const formatPrice = (n) =>
  new Intl.NumberFormat('ru-RU').format(Math.round(Number(n) || 0)) + ' ₽';

export const formatNumber = (n) => new Intl.NumberFormat('ru-RU').format(Number(n) || 0);

export const FUEL = {
  petrol: 'Бензин',
  diesel: 'Дизель',
  hybrid: 'Гибрид',
  electric: 'Электро',
  gas: 'Газ',
};

export const TRANSMISSION = {
  manual: 'Механика',
  automatic: 'Автомат',
  robot: 'Робот',
  variator: 'Вариатор',
};

export const DRIVE = {
  rear: 'Задний',
  front: 'Передний',
  full: 'Полный',
};

export const STATUS = {
  pending: 'На модерации',
  approved: 'Одобрено',
  rejected: 'Отклонено',
};

export const label = (map, key) => map[key] || key;
