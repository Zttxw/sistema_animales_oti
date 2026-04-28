import { useState, useEffect } from 'react';
import { useAuth } from '../../context/AuthContext';
import api from '../../api/axios';

interface DashboardStats {
  animals: { total: number };
  campaigns: { total: number; upcoming: number };
  adoptions: { completed: number };
  stray_animals: { total: number };
}

export default function Dashboard() {
  const { user, hasRole } = useAuth();
  const [stats, setStats] = useState<DashboardStats | null>(null);
  const [loading, setLoading] = useState(true);

  // Check if user has permission to see full reports
  const canSeeStats = hasRole('ADMIN') || hasRole('COORDINATOR') || hasRole('VETERINARIAN') || hasRole('INSPECTOR');

  useEffect(() => {
    if (canSeeStats) {
      api.get('/reports/dashboard')
        .then(response => {
          setStats(response.data);
        })
        .catch(err => {
          console.error("Error fetching dashboard stats:", err);
        })
        .finally(() => {
          setLoading(false);
        });
    } else {
      setLoading(false);
    }
  }, [canSeeStats]);

  return (
    <div className="page-container">
      <div className="page-header">
        <h1>Dashboard General</h1>
        <p>Bienvenido al Sistema, {user?.name}</p>
      </div>

      {canSeeStats ? (
        <div className="stats-grid">
          <div className="stat-card">
            <h3>Mascotas Registradas</h3>
            <p className="stat-number">{loading ? '...' : (stats?.animals?.total || 0)}</p>
          </div>
          <div className="stat-card">
            <h3>Campañas Próximas/Activas</h3>
            <p className="stat-number">{loading ? '...' : (stats?.campaigns?.upcoming || 0)}</p>
          </div>
          <div className="stat-card">
            <h3>Adopciones Exitosas</h3>
            <p className="stat-number">{loading ? '...' : (stats?.adoptions?.completed || 0)}</p>
          </div>
          <div className="stat-card">
            <h3>Animales Callejeros</h3>
            <p className="stat-number">{loading ? '...' : (stats?.stray_animals?.total || 0)}</p>
          </div>
        </div>
      ) : (
        <div className="stat-card" style={{ maxWidth: '400px' }}>
          <h3>Portal Ciudadano</h3>
          <p>Desde aquí podrás gestionar a tus mascotas y solicitudes de adopción usando el menú lateral.</p>
        </div>
      )}
    </div>
  );
}
