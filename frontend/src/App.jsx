import { Routes, Route, useLocation } from 'react-router-dom';
import Header from './components/Header';
import ProtectedRoute from './components/ProtectedRoute';

import Login from './pages/Login';
import Register from './pages/Register';
import Catalog from './pages/Catalog';
import CarDetail from './pages/CarDetail';
import PurchaseRequest from './pages/PurchaseRequest';
import Success from './pages/Success';
import Favorites from './pages/Favorites';
import Profile from './pages/Profile';
import MyListings from './pages/MyListings';
import CarForm from './pages/CarForm';

import AdminLayout from './pages/admin/AdminLayout';
import Dashboard from './pages/admin/Dashboard';
import AdminUsers from './pages/admin/Users';
import AdminCars from './pages/admin/Cars';
import AdminReports from './pages/admin/Reports';
import AdminBrands from './pages/admin/Brands';

export default function App() {
  const { pathname } = useLocation();
  const bareRoutes = ['/login', '/register'];
  const showHeader = !bareRoutes.includes(pathname);

  return (
    <>
      {showHeader && <Header />}
      <main className="container">
        <Routes>
          <Route path="/login" element={<Login />} />
          <Route path="/register" element={<Register />} />

          <Route path="/" element={<Catalog />} />
          <Route path="/cars/:id" element={<CarDetail />} />
          <Route path="/cars/:id/buy" element={<PurchaseRequest />} />
          <Route path="/success" element={<Success />} />

          <Route path="/favorites" element={<ProtectedRoute><Favorites /></ProtectedRoute>} />
          <Route path="/profile" element={<ProtectedRoute><Profile /></ProtectedRoute>} />
          <Route path="/my-listings" element={<ProtectedRoute><MyListings /></ProtectedRoute>} />
          <Route path="/listings/new" element={<ProtectedRoute><CarForm /></ProtectedRoute>} />
          <Route path="/listings/:id/edit" element={<ProtectedRoute><CarForm /></ProtectedRoute>} />

          <Route path="/admin" element={<ProtectedRoute adminOnly><AdminLayout /></ProtectedRoute>}>
            <Route index element={<Dashboard />} />
            <Route path="users" element={<AdminUsers />} />
            <Route path="cars" element={<AdminCars />} />
            <Route path="reports" element={<AdminReports />} />
            <Route path="brands" element={<AdminBrands />} />
          </Route>

          <Route path="*" element={<div className="page"><h1>404 — страница не найдена</h1></div>} />
        </Routes>
      </main>
    </>
  );
}
