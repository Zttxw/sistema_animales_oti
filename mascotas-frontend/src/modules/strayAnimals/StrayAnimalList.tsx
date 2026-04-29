import { useState, useEffect, useCallback } from 'react';
import { Plus, Eye, MapPin, AlertTriangle } from 'lucide-react';
import { strayAnimalService } from './services';
import type { StrayAnimal, StrayAnimalFormData } from './services';
import type { PaginatedResponse } from '../../shared/types/api';
import type { Species } from '../../shared/types/common';
import StatusBadge from '../../shared/components/StatusBadge';
import LoadingSpinner from '../../shared/components/LoadingSpinner';
import EmptyState from '../../shared/components/EmptyState';
import Modal from '../../shared/components/Modal';
import SearchInput from '../../shared/components/SearchInput';
import api from '../../api/axios';

const STATUS_LABELS: Record<string, string> = {
  OBSERVED: 'Observado', RESCUED: 'Rescatado', IN_TREATMENT: 'En Tratamiento',
  RELEASED: 'Liberado', ADOPTED: 'Adoptado', DECEASED: 'Fallecido',
};
const STATUS_COLORS: Record<string, { bg: string; color: string }> = {
  OBSERVED: { bg: '#fef3c7', color: '#92400e' }, RESCUED: { bg: '#dbeafe', color: '#1e40af' },
  IN_TREATMENT: { bg: '#fce7f3', color: '#9d174d' }, RELEASED: { bg: '#dcfce7', color: '#166534' },
  ADOPTED: { bg: '#e0e7ff', color: '#3730a3' }, DECEASED: { bg: '#f3f4f6', color: '#6b7280' },
};

export default function StrayAnimalList() {
  const [animals, setAnimals] = useState<StrayAnimal[]>([]);
  const [pagination, setPagination] = useState({ current_page: 1, last_page: 1, total: 0 });
  const [loading, setLoading] = useState(true);
  const [filters, setFilters] = useState<Record<string, any>>({ page: 1 });
  const [species, setSpecies] = useState<Species[]>([]);
  const [showForm, setShowForm] = useState(false);
  const [showDetail, setShowDetail] = useState<StrayAnimal | null>(null);
  const [saving, setSaving] = useState(false);
  const [form, setForm] = useState<StrayAnimalFormData>({
    species_id: '', location: '', description: '', risk_level: '', estimated_age: '', color: '', size: '', health_condition: '', notes: '',
  });

  const fetch = useCallback(async () => {
    setLoading(true);
    try {
      const res = await strayAnimalService.list(filters);
      const data = res.data as PaginatedResponse<StrayAnimal>;
      setAnimals(data.data || (res.data as any));
      if (data.current_page) setPagination({ current_page: data.current_page, last_page: data.last_page, total: data.total });
    } catch (err) { console.error(err); }
    finally { setLoading(false); }
  }, [filters]);

  useEffect(() => { fetch(); }, [fetch]);
  useEffect(() => { api.get('/catalogs/species').then(r => setSpecies(r.data)); }, []);

  const handleSearch = useCallback((search: string) => { setFilters(f => ({ ...f, search, page: 1 })); }, []);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault(); setSaving(true);
    try {
      await strayAnimalService.create(form);
      setShowForm(false);
      setForm({ species_id: '', location: '', description: '', risk_level: '', estimated_age: '', color: '', size: '', health_condition: '', notes: '' });
      fetch();
    } catch (err) { console.error(err); }
    finally { setSaving(false); }
  };

  const handleStatusChange = async (id: number, status: string) => {
    await strayAnimalService.updateStatus(id, status); fetch();
    if (showDetail?.id === id) { const r = await strayAnimalService.get(id); setShowDetail(r.data); }
  };

  return (
    <div className="page-container">
      <div className="page-header">
        <div className="page-header-left"><h1>Animales Callejeros</h1><p>{pagination.total} reportes registrados</p></div>
        <button className="btn btn--primary" onClick={() => setShowForm(true)}><Plus size={18} /> Nuevo Reporte</button>
      </div>

      <div className="toolbar">
        <div className="toolbar-left">
          <SearchInput placeholder="Buscar por ubicación..." onSearch={handleSearch} />
          <select className="form-control" style={{ maxWidth: 180 }} value={filters.status || ''} onChange={e => setFilters(f => ({ ...f, status: e.target.value || undefined, page: 1 }))}>
            <option value="">Todos los estados</option>
            {Object.entries(STATUS_LABELS).map(([k, v]) => <option key={k} value={k}>{v}</option>)}
          </select>
        </div>
      </div>

      {loading ? <LoadingSpinner /> : animals.length === 0 ? <EmptyState message="No hay reportes de animales callejeros." /> : (
        <>
          <div className="card"><div style={{ overflowX: 'auto' }}>
            <table className="data-table">
              <thead><tr><th>ID</th><th>Especie</th><th>Ubicación</th><th>Estado</th><th>Riesgo</th><th>Fecha</th><th>Acciones</th></tr></thead>
              <tbody>
                {animals.map(a => (
                  <tr key={a.id}>
                    <td>#{a.id}</td>
                    <td>{a.species?.name || '—'}</td>
                    <td><MapPin size={14} style={{ verticalAlign: 'middle', marginRight: 4 }} />{a.location}</td>
                    <td><span className="status-badge" style={{ backgroundColor: STATUS_COLORS[a.status]?.bg, color: STATUS_COLORS[a.status]?.color }}>{STATUS_LABELS[a.status] || a.status}</span></td>
                    <td>{a.risk_level || '—'}</td>
                    <td>{new Date(a.created_at).toLocaleDateString('es-PE')}</td>
                    <td><button className="btn btn--ghost btn--sm" onClick={() => strayAnimalService.get(a.id).then(r => setShowDetail(r.data))}><Eye size={16} /> Ver</button></td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div></div>
          {pagination.last_page > 1 && (
            <div className="pagination">
              <span className="pagination-info">Página {pagination.current_page} de {pagination.last_page}</span>
              <div className="pagination-buttons">
                <button className="pagination-btn" disabled={pagination.current_page <= 1} onClick={() => setFilters(f => ({ ...f, page: f.page - 1 }))}>Anterior</button>
                <button className="pagination-btn" disabled={pagination.current_page >= pagination.last_page} onClick={() => setFilters(f => ({ ...f, page: f.page + 1 }))}>Siguiente</button>
              </div>
            </div>
          )}
        </>
      )}

      {/* Detail */}
      <Modal isOpen={!!showDetail} onClose={() => setShowDetail(null)} title={`Reporte #${showDetail?.id}`} size="lg">
        {showDetail && (<div>
          <div style={{ display: 'flex', gap: 8, marginBottom: 16 }}>
            <span className="status-badge status-badge--md" style={{ backgroundColor: STATUS_COLORS[showDetail.status]?.bg, color: STATUS_COLORS[showDetail.status]?.color }}>{STATUS_LABELS[showDetail.status]}</span>
          </div>
          <div className="detail-info-grid" style={{ gridTemplateColumns: 'repeat(2,1fr)', marginBottom: 16 }}>
            <div className="detail-info-item"><label>Especie</label><span>{showDetail.species?.name || '—'}</span></div>
            <div className="detail-info-item"><label>Ubicación</label><span>{showDetail.location}</span></div>
            <div className="detail-info-item"><label>Color</label><span>{showDetail.color || '—'}</span></div>
            <div className="detail-info-item"><label>Tamaño</label><span>{showDetail.size || '—'}</span></div>
            <div className="detail-info-item"><label>Edad Est.</label><span>{showDetail.estimated_age || '—'}</span></div>
            <div className="detail-info-item"><label>Condición Salud</label><span>{showDetail.health_condition || '—'}</span></div>
            <div className="detail-info-item"><label>Nivel de Riesgo</label><span>{showDetail.risk_level || '—'}</span></div>
            <div className="detail-info-item"><label>Reportado</label><span>{new Date(showDetail.created_at).toLocaleString('es-PE')}</span></div>
          </div>
          {showDetail.description && <div style={{ marginBottom: 12 }}><label style={{ display: 'block', fontSize: '0.75rem', color: 'var(--text-muted)', textTransform: 'uppercase', marginBottom: 4 }}>Descripción</label><p style={{ fontSize: '0.9rem' }}>{showDetail.description}</p></div>}
          {showDetail.photos && showDetail.photos.length > 0 && (
            <div className="photo-grid" style={{ marginBottom: 16 }}>{showDetail.photos.map(p => <div key={p.id} className="photo-item"><img src={p.url} alt="" /></div>)}</div>
          )}
          <div style={{ display: 'flex', gap: 8, flexWrap: 'wrap', paddingTop: 12, borderTop: '1px solid var(--border-light)' }}>
            {showDetail.status === 'OBSERVED' && <button className="btn btn--primary btn--sm" onClick={() => handleStatusChange(showDetail.id, 'RESCUED')}>Rescatar</button>}
            {showDetail.status === 'RESCUED' && <button className="btn btn--accent btn--sm" onClick={() => handleStatusChange(showDetail.id, 'IN_TREATMENT')}>Iniciar Tratamiento</button>}
            {showDetail.status === 'IN_TREATMENT' && <>
              <button className="btn btn--primary btn--sm" onClick={() => handleStatusChange(showDetail.id, 'RELEASED')}>Liberar</button>
              <button className="btn btn--accent btn--sm" onClick={() => handleStatusChange(showDetail.id, 'ADOPTED')}>Adoptar</button>
            </>}
          </div>
        </div>)}
      </Modal>

      {/* Create */}
      <Modal isOpen={showForm} onClose={() => setShowForm(false)} title="Reportar Animal Callejero" size="lg">
        <form onSubmit={handleSubmit}>
          <div className="form-row">
            <div className="form-group"><label>Especie *</label><select className="form-control" value={form.species_id} onChange={e => setForm(f => ({ ...f, species_id: e.target.value }))} required><option value="">Seleccionar...</option>{species.map(s => <option key={s.id} value={s.id}>{s.name}</option>)}</select></div>
            <div className="form-group"><label>Ubicación *</label><input className="form-control" value={form.location} onChange={e => setForm(f => ({ ...f, location: e.target.value }))} required /></div>
          </div>
          <div className="form-row">
            <div className="form-group"><label>Color</label><input className="form-control" value={form.color || ''} onChange={e => setForm(f => ({ ...f, color: e.target.value }))} /></div>
            <div className="form-group"><label>Tamaño</label><select className="form-control" value={form.size || ''} onChange={e => setForm(f => ({ ...f, size: e.target.value }))}><option value="">Seleccionar...</option><option value="SMALL">Pequeño</option><option value="MEDIUM">Mediano</option><option value="LARGE">Grande</option></select></div>
          </div>
          <div className="form-row">
            <div className="form-group"><label>Edad Estimada</label><input className="form-control" value={form.estimated_age || ''} onChange={e => setForm(f => ({ ...f, estimated_age: e.target.value }))} /></div>
            <div className="form-group"><label>Nivel de Riesgo</label><input className="form-control" value={form.risk_level || ''} onChange={e => setForm(f => ({ ...f, risk_level: e.target.value }))} placeholder="Bajo, Medio, Alto" /></div>
          </div>
          <div className="form-group"><label>Condición de Salud</label><input className="form-control" value={form.health_condition || ''} onChange={e => setForm(f => ({ ...f, health_condition: e.target.value }))} /></div>
          <div className="form-group"><label>Descripción</label><textarea className="form-control" rows={3} value={form.description || ''} onChange={e => setForm(f => ({ ...f, description: e.target.value }))} /></div>
          <div style={{ display: 'flex', gap: 12, justifyContent: 'flex-end', paddingTop: 8 }}>
            <button type="button" className="btn btn--secondary" onClick={() => setShowForm(false)}>Cancelar</button>
            <button type="submit" className="btn btn--primary" disabled={saving}>{saving ? 'Guardando...' : 'Reportar'}</button>
          </div>
        </form>
      </Modal>
    </div>
  );
}
