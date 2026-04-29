import { useState, useEffect, useCallback } from 'react';
import { Plus, Eye, MapPin, Calendar, Users } from 'lucide-react';
import { campaignService } from './services';
import type { Campaign, CampaignFilters, CampaignFormData } from './types';
import type { PaginatedResponse } from '../../shared/types/api';
import type { CampaignType } from '../../shared/types/common';
import StatusBadge from '../../shared/components/StatusBadge';
import LoadingSpinner from '../../shared/components/LoadingSpinner';
import EmptyState from '../../shared/components/EmptyState';
import Modal from '../../shared/components/Modal';
import { useAuth } from '../../context/AuthContext';

export default function CampaignList() {
  const { hasRole } = useAuth();
  const canManage = hasRole('ADMIN') || hasRole('COORDINATOR') || hasRole('VETERINARIAN');
  const [campaigns, setCampaigns] = useState<Campaign[]>([]);
  const [pagination, setPagination] = useState({ current_page: 1, last_page: 1, total: 0 });
  const [loading, setLoading] = useState(true);
  const [filters, setFilters] = useState<CampaignFilters>({ page: 1 });
  const [types, setTypes] = useState<CampaignType[]>([]);
  const [showForm, setShowForm] = useState(false);
  const [showDetail, setShowDetail] = useState<Campaign | null>(null);
  const [saving, setSaving] = useState(false);
  const [form, setForm] = useState<CampaignFormData>({ title: '', campaign_type_id: '', scheduled_at: '', location: '', capacity: '', description: '', target_audience: '', requirements: '' });

  const fetch = useCallback(async () => {
    setLoading(true);
    try {
      const res = await campaignService.list(filters);
      const data = res.data as PaginatedResponse<Campaign>;
      setCampaigns(data.data);
      setPagination({ current_page: data.current_page, last_page: data.last_page, total: data.total });
    } catch (err) { console.error(err); }
    finally { setLoading(false); }
  }, [filters]);

  useEffect(() => { fetch(); }, [fetch]);
  useEffect(() => { campaignService.campaignTypes().then(r => setTypes(r.data)); }, []);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setSaving(true);
    try {
      await campaignService.create(form);
      setShowForm(false);
      setForm({ title: '', campaign_type_id: '', scheduled_at: '', location: '', capacity: '', description: '', target_audience: '', requirements: '' });
      fetch();
    } catch (err) { console.error(err); }
    finally { setSaving(false); }
  };

  const handleStatusChange = async (id: number, status: string) => {
    await campaignService.updateStatus(id, status);
    fetch();
    if (showDetail?.id === id) {
      const res = await campaignService.get(id);
      setShowDetail(res.data);
    }
  };

  const formatDate = (d: string) => new Date(d).toLocaleDateString('es-PE', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });

  return (
    <div className="page-container">
      <div className="page-header">
        <div className="page-header-left"><h1>Campañas de Zoonosis</h1><p>{pagination.total} campañas registradas</p></div>
        {canManage && <button className="btn btn--primary" onClick={() => setShowForm(true)}><Plus size={18} /> Nueva Campaña</button>}
      </div>

      <div className="toolbar">
        <select className="form-control" style={{ maxWidth: 180 }} value={filters.status || ''} onChange={e => setFilters({ status: e.target.value || undefined, page: 1 })}>
          <option value="">Todos los estados</option>
          <option value="DRAFT">Borrador</option>
          <option value="PUBLISHED">Publicada</option>
          <option value="IN_PROGRESS">En Progreso</option>
          <option value="FINISHED">Finalizada</option>
          <option value="CANCELLED">Cancelada</option>
        </select>
      </div>

      {loading ? <LoadingSpinner /> : campaigns.length === 0 ? <EmptyState message="No hay campañas registradas." /> : (
        <>
          <div className="animal-grid">
            {campaigns.map(c => (
              <div key={c.id} className="animal-card" onClick={() => campaignService.get(c.id).then(r => setShowDetail(r.data))}>
                <div className="animal-card-img" style={{ height: 100, background: 'linear-gradient(135deg, rgba(59,130,246,0.15), rgba(20,184,166,0.1))' }}>
                  <Calendar size={36} color="var(--info)" />
                </div>
                <div className="animal-card-body">
                  <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start' }}>
                    <h4>{c.title}</h4>
                    <StatusBadge status={c.status} />
                  </div>
                  <div className="animal-card-meta" style={{ marginTop: 8 }}>
                    {c.campaign_type && <span>{c.campaign_type.name}</span>}
                    <span>·</span>
                    <span><Calendar size={12} /> {formatDate(c.scheduled_at)}</span>
                  </div>
                  {c.location && <div className="animal-card-meta"><MapPin size={12} /><span>{c.location}</span></div>}
                  {c.capacity && <div className="animal-card-meta"><Users size={12} /><span>Capacidad: {c.capacity}</span></div>}
                </div>
              </div>
            ))}
          </div>
          {pagination.last_page > 1 && (
            <div className="pagination">
              <span className="pagination-info">Página {pagination.current_page} de {pagination.last_page}</span>
              <div className="pagination-buttons">
                <button className="pagination-btn" disabled={pagination.current_page <= 1} onClick={() => setFilters(f => ({ ...f, page: (f.page || 1) - 1 }))}>Anterior</button>
                <button className="pagination-btn" disabled={pagination.current_page >= pagination.last_page} onClick={() => setFilters(f => ({ ...f, page: (f.page || 1) + 1 }))}>Siguiente</button>
              </div>
            </div>
          )}
        </>
      )}

      {/* Detail Modal */}
      <Modal isOpen={!!showDetail} onClose={() => setShowDetail(null)} title={showDetail?.title || ''} size="lg">
        {showDetail && (
          <div>
            <div style={{ display: 'flex', gap: 8, marginBottom: 16 }}>
              <StatusBadge status={showDetail.status} size="md" />
              {showDetail.campaign_type && <span className="status-badge" style={{ background: 'var(--info-bg)', color: 'var(--info)' }}>{showDetail.campaign_type.name}</span>}
            </div>
            <div className="detail-info-grid" style={{ gridTemplateColumns: 'repeat(2,1fr)', marginBottom: 16 }}>
              <div className="detail-info-item"><label>Fecha Programada</label><span>{formatDate(showDetail.scheduled_at)}</span></div>
              <div className="detail-info-item"><label>Ubicación</label><span>{showDetail.location || '—'}</span></div>
              <div className="detail-info-item"><label>Capacidad</label><span>{showDetail.capacity || 'Sin límite'}</span></div>
              <div className="detail-info-item"><label>Público Objetivo</label><span>{showDetail.target_audience || '—'}</span></div>
            </div>
            {showDetail.description && <div style={{ marginBottom: 16 }}><label style={{ display: 'block', fontSize: '0.75rem', color: 'var(--text-muted)', textTransform: 'uppercase', marginBottom: 4 }}>Descripción</label><p style={{ fontSize: '0.9rem' }}>{showDetail.description}</p></div>}
            {showDetail.requirements && <div style={{ marginBottom: 16 }}><label style={{ display: 'block', fontSize: '0.75rem', color: 'var(--text-muted)', textTransform: 'uppercase', marginBottom: 4 }}>Requisitos</label><p style={{ fontSize: '0.9rem' }}>{showDetail.requirements}</p></div>}
            <div style={{ display: 'flex', gap: 8, flexWrap: 'wrap', paddingTop: 8, borderTop: '1px solid var(--border-light)' }}>
              {canManage && showDetail.status === 'DRAFT' && <button className="btn btn--primary btn--sm" onClick={() => handleStatusChange(showDetail.id, 'PUBLISHED')}>Publicar</button>}
              {canManage && showDetail.status === 'PUBLISHED' && <button className="btn btn--accent btn--sm" onClick={() => handleStatusChange(showDetail.id, 'IN_PROGRESS')}>Iniciar</button>}
              {canManage && showDetail.status === 'IN_PROGRESS' && <button className="btn btn--primary btn--sm" onClick={() => handleStatusChange(showDetail.id, 'FINISHED')}>Finalizar</button>}
              {canManage && !['FINISHED', 'CANCELLED'].includes(showDetail.status) && <button className="btn btn--danger btn--sm" onClick={() => handleStatusChange(showDetail.id, 'CANCELLED')}>Cancelar</button>}
            </div>
          </div>
        )}
      </Modal>

      {/* Create Modal */}
      <Modal isOpen={showForm} onClose={() => setShowForm(false)} title="Nueva Campaña" size="lg">
        <form onSubmit={handleSubmit}>
          <div className="form-group"><label>Título *</label><input className="form-control" value={form.title} onChange={e => setForm(f => ({ ...f, title: e.target.value }))} required /></div>
          <div className="form-row">
            <div className="form-group"><label>Tipo *</label><select className="form-control" value={form.campaign_type_id} onChange={e => setForm(f => ({ ...f, campaign_type_id: e.target.value }))} required><option value="">Seleccionar...</option>{types.map(t => <option key={t.id} value={t.id}>{t.name}</option>)}</select></div>
            <div className="form-group"><label>Fecha Programada *</label><input type="datetime-local" className="form-control" value={form.scheduled_at} onChange={e => setForm(f => ({ ...f, scheduled_at: e.target.value }))} required /></div>
          </div>
          <div className="form-row">
            <div className="form-group"><label>Ubicación</label><input className="form-control" value={form.location || ''} onChange={e => setForm(f => ({ ...f, location: e.target.value }))} /></div>
            <div className="form-group"><label>Capacidad</label><input type="number" className="form-control" value={form.capacity || ''} onChange={e => setForm(f => ({ ...f, capacity: e.target.value }))} min={1} /></div>
          </div>
          <div className="form-group"><label>Descripción</label><textarea className="form-control" rows={3} value={form.description || ''} onChange={e => setForm(f => ({ ...f, description: e.target.value }))} /></div>
          <div className="form-group"><label>Público Objetivo</label><input className="form-control" value={form.target_audience || ''} onChange={e => setForm(f => ({ ...f, target_audience: e.target.value }))} /></div>
          <div className="form-group"><label>Requisitos</label><textarea className="form-control" rows={2} value={form.requirements || ''} onChange={e => setForm(f => ({ ...f, requirements: e.target.value }))} /></div>
          <div style={{ display: 'flex', gap: 12, justifyContent: 'flex-end', paddingTop: 8 }}>
            <button type="button" className="btn btn--secondary" onClick={() => setShowForm(false)}>Cancelar</button>
            <button type="submit" className="btn btn--primary" disabled={saving}>{saving ? 'Creando...' : 'Crear Campaña'}</button>
          </div>
        </form>
      </Modal>
    </div>
  );
}
