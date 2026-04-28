import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import { AuthProvider } from './context/AuthContext';
import { ProtectedRoute } from './auth/ProtectedRoute';
import AdminLayout from './layouts/AdminLayout';
import PublicLayout from './layouts/PublicLayout';

// Pages
import Login from './pages/auth/Login';
import Register from './pages/auth/Register';
import Dashboard from './pages/dashboard/Dashboard';
import Users from './pages/users/Users';
import Unauthorized from './pages/errors/Unauthorized';

function App() {
  return (
    <AuthProvider>
      <Router>
        <Routes>
          {/* Public Routes */}
          <Route element={<PublicLayout />}>
            <Route path="/login" element={<Login />} />
            <Route path="/register" element={<Register />} />
          </Route>

          {/* Protected Routes */}
          <Route element={<ProtectedRoute />}>
            <Route element={<AdminLayout />}>
              <Route path="/" element={<Dashboard />} />
              
              {/* Placeholders for other routes */}
              <Route path="/users" element={<Users />} />
              <Route path="/pets" element={<div className="page-container"><h2>Módulo de Animales de Compañía en desarrollo...</h2></div>} />
              <Route path="/health" element={<div className="page-container"><h2>Módulo de Salud Animal en desarrollo...</h2></div>} />
              <Route path="/adoptions" element={<div className="page-container"><h2>Módulo de Adopciones en desarrollo...</h2></div>} />
              <Route path="/campaigns" element={<div className="page-container"><h2>Módulo de Campañas en desarrollo...</h2></div>} />
              <Route path="/stray" element={<div className="page-container"><h2>Módulo de Animales Callejeros en desarrollo...</h2></div>} />
              <Route path="/posts" element={<div className="page-container"><h2>Módulo de Publicaciones en desarrollo...</h2></div>} />
              <Route path="/notifications" element={<div className="page-container"><h2>Módulo de Notificaciones en desarrollo...</h2></div>} />
              <Route path="/reports" element={<div className="page-container"><h2>Módulo de Reportes en desarrollo...</h2></div>} />
              
              <Route path="/unauthorized" element={<Unauthorized />} />
            </Route>
          </Route>

          {/* Fallback */}
          <Route path="*" element={<Navigate to="/" replace />} />
        </Routes>
      </Router>
    </AuthProvider>
  );
}

export default App;