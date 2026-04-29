import { useState, useEffect } from 'react';
import { BarChart3, PieChart, TrendingUp, Calendar } from 'lucide-react';
import api from '../../api/axios';
import LoadingSpinner from '../../shared/components/LoadingSpinner';

interface SpeciesData { species: string; total: number }
interface MonthData { year: number; month: number; total: number }
interface CampaignData { id: number; title: string; scheduled_at: string; status: string; participants_count: number; attended_count: number }
interface StrayData { by_status: { status: string; total: number }[]; by_species: { species: string; total: number }[] }

const MONTHS = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];

export default function ReportsDashboard() {
  const [bySpecies, setBySpecies] = useState<SpeciesData[]>([]);
  const [perMonth, setPerMonth] = useState<MonthData[]>([]);
  const [campaigns, setCampaigns] = useState<CampaignData[]>([]);
  const [stray, setStray] = useState<StrayData | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    Promise.all([
      api.get('/reports/animals/by-species').then(r => setBySpecies(r.data)),
      api.get('/reports/animals/per-month').then(r => setPerMonth(r.data)),
      api.get('/reports/campaigns/participation').then(r => setCampaigns(r.data)),
      api.get('/reports/stray-animals/summary').then(r => setStray(r.data)),
    ]).catch(console.error).finally(() => setLoading(false));
  }, []);

  if (loading) return <LoadingSpinner message="Cargando reportes..." />;

  const maxSpecies = Math.max(...bySpecies.map(s => s.total), 1);
  const maxMonth = Math.max(...perMonth.map(m => m.total), 1);

  return (
    <div className="page-container">
      <div className="page-header"><div className="page-header-left"><h1>Reportes y Estadísticas</h1><p>Información consolidada del sistema</p></div></div>

      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(400px, 1fr))', gap: 20 }}>
        {/* By Species */}
        <div className="card">
          <div className="card-header"><h3><PieChart size={18} style={{ marginRight: 8, verticalAlign: 'middle' }} />Animales por Especie</h3></div>
          <div className="card-body">
            {bySpecies.length === 0 ? <p style={{ color: 'var(--text-muted)' }}>Sin datos</p> : bySpecies.map(s => (
              <div key={s.species} style={{ marginBottom: 12 }}>
                <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: '0.85rem', marginBottom: 4 }}>
                  <span style={{ fontWeight: 600 }}>{s.species}</span><span style={{ color: 'var(--text-muted)' }}>{s.total}</span>
                </div>
                <div style={{ height: 8, background: 'var(--border-light)', borderRadius: 4, overflow: 'hidden' }}>
                  <div style={{ height: '100%', width: `${(s.total / maxSpecies) * 100}%`, background: 'linear-gradient(90deg, var(--primary), var(--accent))', borderRadius: 4, transition: 'width 0.5s ease' }} />
                </div>
              </div>
            ))}
          </div>
        </div>

        {/* Per Month */}
        <div className="card">
          <div className="card-header"><h3><BarChart3 size={18} style={{ marginRight: 8, verticalAlign: 'middle' }} />Registros por Mes</h3></div>
          <div className="card-body">
            {perMonth.length === 0 ? <p style={{ color: 'var(--text-muted)' }}>Sin datos</p> : (
              <div style={{ display: 'flex', alignItems: 'flex-end', gap: 6, height: 160 }}>
                {perMonth.map(m => (
                  <div key={`${m.year}-${m.month}`} style={{ flex: 1, display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 4 }}>
                    <span style={{ fontSize: '0.7rem', fontWeight: 600, color: 'var(--text-primary)' }}>{m.total}</span>
                    <div style={{ width: '100%', height: `${(m.total / maxMonth) * 120}px`, background: 'linear-gradient(180deg, var(--primary), var(--accent))', borderRadius: '4px 4px 0 0', minHeight: 4, transition: 'height 0.5s ease' }} />
                    <span style={{ fontSize: '0.65rem', color: 'var(--text-muted)' }}>{MONTHS[m.month - 1]}</span>
                  </div>
                ))}
              </div>
            )}
          </div>
        </div>

        {/* Campaign Participation */}
        <div className="card">
          <div className="card-header"><h3><Calendar size={18} style={{ marginRight: 8, verticalAlign: 'middle' }} />Participación en Campañas</h3></div>
          <div className="card-body">
            {campaigns.length === 0 ? <p style={{ color: 'var(--text-muted)' }}>Sin datos</p> : (
              <div style={{ overflowX: 'auto' }}>
                <table className="data-table">
                  <thead><tr><th>Campaña</th><th>Inscritos</th><th>Asistieron</th><th>%</th></tr></thead>
                  <tbody>
                    {campaigns.slice(0, 10).map(c => (
                      <tr key={c.id}>
                        <td style={{ fontWeight: 500, maxWidth: 200, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>{c.title}</td>
                        <td>{c.participants_count}</td>
                        <td>{c.attended_count}</td>
                        <td><span style={{ fontWeight: 600, color: c.participants_count > 0 ? 'var(--success)' : 'var(--text-muted)' }}>{c.participants_count > 0 ? Math.round((c.attended_count / c.participants_count) * 100) : 0}%</span></td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            )}
          </div>
        </div>

        {/* Stray Animals */}
        <div className="card">
          <div className="card-header"><h3><TrendingUp size={18} style={{ marginRight: 8, verticalAlign: 'middle' }} />Animales Callejeros</h3></div>
          <div className="card-body">
            {!stray ? <p style={{ color: 'var(--text-muted)' }}>Sin datos</p> : (
              <>
                <h4 style={{ fontSize: '0.8rem', color: 'var(--text-muted)', textTransform: 'uppercase', marginBottom: 12 }}>Por Estado</h4>
                {stray.by_status.map(s => (
                  <div key={s.status} style={{ display: 'flex', justifyContent: 'space-between', padding: '8px 0', borderBottom: '1px solid var(--border-light)', fontSize: '0.9rem' }}>
                    <span>{s.status}</span><span style={{ fontWeight: 600 }}>{s.total}</span>
                  </div>
                ))}
                <h4 style={{ fontSize: '0.8rem', color: 'var(--text-muted)', textTransform: 'uppercase', marginBottom: 12, marginTop: 20 }}>Por Especie</h4>
                {stray.by_species.map(s => (
                  <div key={s.species} style={{ display: 'flex', justifyContent: 'space-between', padding: '8px 0', borderBottom: '1px solid var(--border-light)', fontSize: '0.9rem' }}>
                    <span>{s.species}</span><span style={{ fontWeight: 600 }}>{s.total}</span>
                  </div>
                ))}
              </>
            )}
          </div>
        </div>
      </div>
    </div>
  );
}
