import { useState, useEffect } from 'react';
import { useAuth } from '../../context/AuthContext';
import { useNavigate } from 'react-router-dom';
import api from '../../api/axios';
import { Dog, Users, HeartHandshake, Megaphone, AlertTriangle, Syringe, TrendingUp, Activity } from 'lucide-react';
import LoadingSpinner from '../../shared/components/LoadingSpinner';

interface DashboardStats {
  animals: { total: number; active: number; lost: number; for_adoption: number; deceased: number };
  stray_animals: { total: number; observed: number; rescued: number; in_treatment: number };
  adoptions: { available: number; in_process: number; completed: number };
  campaigns: { total: number; upcoming: number; finished: number };
  vaccinations_pending: number;
  users_total: number;
}

export default function Dashboard() {
  const { user, hasRole } = useAuth();
  const navigate = useNavigate();
  const [stats, setStats] = useState<DashboardStats | null>(null);
  const [loading, setLoading] = useState(true);

  const canSeeStats = hasRole('ADMIN') || hasRole('COORDINATOR') || hasRole('VETERINARIAN') || hasRole('INSPECTOR');

  useEffect(() => {
    if (canSeeStats) {
      api.get('/reports/dashboard')
        .then(res => setStats(res.data))
        .catch(err => console.error('Dashboard stats error:', err))
        .finally(() => setLoading(false));
    } else {
      setLoading(false);
    }
  }, [canSeeStats]);

  if (loading) return <LoadingSpinner message="Cargando dashboard..." />;

  const statCards = stats ? [
    { label: 'Animales Registrados', value: stats.animals.total, sub: `${stats.animals.active} activos`, icon: Dog, color: '#6366f1', bg: 'rgba(99,102,241,0.1)', path: '/animals' },
    { label: 'Usuarios Activos', value: stats.users_total, sub: 'en el sistema', icon: Users, color: '#14b8a6', bg: 'rgba(20,184,166,0.1)', path: '/users' },
    { label: 'Adopciones', value: stats.adoptions.completed, sub: `${stats.adoptions.available} disponibles`, icon: HeartHandshake, color: '#f59e0b', bg: 'rgba(245,158,11,0.1)', path: '/adoptions' },
    { label: 'Campañas Activas', value: stats.campaigns.upcoming, sub: `${stats.campaigns.total} total`, icon: Megaphone, color: '#3b82f6', bg: 'rgba(59,130,246,0.1)', path: '/campaigns' },
    { label: 'Animales Callejeros', value: stats.stray_animals.total, sub: `${stats.stray_animals.rescued} rescatados`, icon: AlertTriangle, color: '#ef4444', bg: 'rgba(239,68,68,0.1)', path: '/stray' },
    { label: 'Vacunas Pendientes', value: stats.vaccinations_pending, sub: 'próximos 30 días', icon: Syringe, color: '#8b5cf6', bg: 'rgba(139,92,246,0.1)', path: '/health' },
    { label: 'Animales Perdidos', value: stats.animals.lost, sub: 'reportados', icon: Activity, color: '#ec4899', bg: 'rgba(236,72,153,0.1)', path: '/animals' },
    { label: 'En Adopción', value: stats.animals.for_adoption, sub: 'buscando hogar', icon: TrendingUp, color: '#10b981', bg: 'rgba(16,185,129,0.1)', path: '/adoptions' },
  ] : [];

  return (
    <div className="page-container">
      <div className="page-header">
        <div className="page-header-left">
          <h1>Dashboard General</h1>
          <p>Bienvenido al Sistema, {user?.first_name || user?.name} 👋</p>
        </div>
      </div>

      {canSeeStats && stats ? (
        <div className="stats-grid">
          {statCards.map((card) => (
            <div key={card.label} className="stat-card" onClick={() => navigate(card.path)} style={{ cursor: 'pointer' }}>
              <div className="stat-icon" style={{ background: card.bg, color: card.color }}>
                <card.icon size={22} />
              </div>
              <h3>{card.label}</h3>
              <p className="stat-number">{card.value}</p>
              <p className="stat-subtitle">{card.sub}</p>
            </div>
          ))}
        </div>
      ) : (
        <div className="stats-grid">
          <div className="stat-card" onClick={() => navigate('/animals')} style={{ cursor: 'pointer' }}>
            <div className="stat-icon" style={{ background: 'rgba(99,102,241,0.1)', color: '#6366f1' }}>
              <Dog size={22} />
            </div>
            <h3>Mis Mascotas</h3>
            <p className="stat-subtitle">Gestiona el registro de tus animales de compañía</p>
          </div>
          <div className="stat-card" onClick={() => navigate('/adoptions')} style={{ cursor: 'pointer' }}>
            <div className="stat-icon" style={{ background: 'rgba(245,158,11,0.1)', color: '#f59e0b' }}>
              <HeartHandshake size={22} />
            </div>
            <h3>Adopciones</h3>
            <p className="stat-subtitle">Encuentra tu compañero ideal</p>
          </div>
          <div className="stat-card" onClick={() => navigate('/campaigns')} style={{ cursor: 'pointer' }}>
            <div className="stat-icon" style={{ background: 'rgba(59,130,246,0.1)', color: '#3b82f6' }}>
              <Megaphone size={22} />
            </div>
            <h3>Campañas</h3>
            <p className="stat-subtitle">Participa en campañas de zoonosis</p>
          </div>
        </div>
      )}
    </div>
  );
}
