import { useState, useEffect, useCallback } from 'react';
import { Plus, HeartHandshake, Eye } from 'lucide-react';
import { adoptionService } from './services';
import type { Adoption, AdoptionFilters, AdoptionFormData } from './types';
import type { PaginatedResponse } from '../../shared/types/api';
import StatusBadge from '../../shared/components/StatusBadge';
import LoadingSpinner from '../../shared/components/LoadingSpinner';
import EmptyState from '../../shared/components/EmptyState';
import Modal from '../../shared/components/Modal';
import { useAuth } from '../../context/AuthContext';
import api from '../../api/axios';

export default function AdoptionList() {
  const { hasRole } = useAuth();
  const canManage = hasRole('ADMIN') || hasRole('COORDINATOR') || hasRole('VETERINARIAN');
  const [adoptions, setAdoptions] = useState<Adoption[]>([]);
  const [pagination, setPagination] = useState({ current_page: 1, last_page: 1, total: 0 });
  const [loading, setLoading] = useState(true);
  const [filters, setFilters] = useState<AdoptionFilters>({ page: 1 });
  const [showForm, setShowForm] = useState(false);
  const [showDetail, setShowDetail] = useState<Adoption | null>(null);
  const [saving, setSaving] = useState(false);
  const [species, setSpecies] = useState<{id: number; name: string}[]>([]);
  const [breeds, setBreeds] = useState<{id: number; name: string; species_id: number}[]>([]);
  const [form, setForm] = useState<AdoptionFormData>({ animal_id: '', reason: '', description: '', requirements: '', contact: '', photo_url: '' });

  const fetch = useCallback(async () => {
    setLoading(true);
    try {
      const res = await adoptionService.list(filters);
      const data = res.data as PaginatedResponse<Adoption>;
      setAdoptions(data.data);
      setPagination({ current_page: data.current_page, last_page: data.last_page, total: data.total });
    } catch (err) { console.error(err); }
    finally { setLoading(false); }
  }, [filters]);

  useEffect(() => { fetch(); }, [fetch]);

  useEffect(() => {
    api.get('/catalogs/species').then(r => setSpecies(r.data)).catch(console.error);
    api.get('/catalogs/breeds').then(r => setBreeds(r.data)).catch(console.error);
  }, []);

  const handleFileUpload = async (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (!file) return;
    const formData = new FormData();
    formData.append('file', file);
    try {
      const res = await api.post('/upload', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
      setForm(f => ({ ...f, photo_url: res.data.url }));
    } catch (err) {
      console.error('Error uploading file:', err);
      alert('Error al subir la imagen');
    }
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setSaving(true);
    try {
      await adoptionService.create(form);
      setShowForm(false);
      setForm({ animal_id: '', reason: '', description: '', requirements: '', contact: '', photo_url: '' });
      fetch();
    } catch (err) { console.error(err); }
    finally { setSaving(false); }
  };

  const handleStatusChange = async (id: number, status: string, extra?: Record<string, any>) => {
    await adoptionService.updateStatus(id, status, extra);
    fetch();
    if (showDetail?.id === id) {
      const res = await adoptionService.get(id);
      setShowDetail(res.data);
    }
  };

  const statusFlowButtons = (adoption: Adoption) => {
    const btns = [];
    if (adoption.status === 'AVAILABLE') btns.push(<button key="p" className="btn btn--accent btn--sm" onClick={() => handleStatusChange(adoption.id, 'IN_PROCESS')}>Iniciar Proceso</button>);
    if (adoption.status === 'IN_PROCESS') {
      btns.push(<button key="a" className="btn btn--primary btn--sm" onClick={() => {
        const adoptedBy = prompt('ID del adoptante:');
        if (adoptedBy) handleStatusChange(adoption.id, 'ADOPTED', { adopted_by: Number(adoptedBy), adopted_at: new Date().toISOString().split('T')[0] });
      }}>Completar Adopción</button>);
    }
    if (['AVAILABLE', 'IN_PROCESS'].includes(adoption.status)) btns.push(<button key="w" className="btn btn--danger btn--sm" onClick={() => handleStatusChange(adoption.id, 'WITHDRAWN')}>Retirar</button>);
    return btns;
  };

  return (
    <div className="page-container">
      <div className="page-header">
        <div className="page-header-left"><h1>Adopciones</h1><p>{pagination.total} registros de adopción</p></div>
        {canManage && <button className="btn btn--primary" onClick={() => setShowForm(true)}><Plus size={18} /> Publicar Adopción</button>}
      </div>

      <div className="toolbar">
        <div className="toolbar-left" style={{ display: 'flex', gap: '12px', flexWrap: 'wrap' }}>
          <select className="form-control" style={{ maxWidth: 180 }} value={filters.status || ''} onChange={e => setFilters({ ...filters, status: e.target.value || undefined, page: 1 })}>
            <option value="">Todos los estados</option>
            <option value="AVAILABLE">Disponible</option>
            <option value="IN_PROCESS">En Proceso</option>
            <option value="ADOPTED">Adoptado</option>
            <option value="WITHDRAWN">Retirado</option>
          </select>
          <select className="form-control" style={{ maxWidth: 180 }} value={filters.species_id || ''} onChange={e => setFilters({ ...filters, species_id: e.target.value || undefined, breed_id: undefined, page: 1 })}>
            <option value="">Todas las especies</option>
            {species.map(s => <option key={s.id} value={s.id}>{s.name}</option>)}
          </select>
          <select className="form-control" style={{ maxWidth: 180 }} value={filters.breed_id || ''} onChange={e => setFilters({ ...filters, breed_id: e.target.value || undefined, page: 1 })} disabled={!filters.species_id}>
            <option value="">Todas las razas</option>
            {breeds.filter(b => b.species_id === Number(filters.species_id)).map(b => <option key={b.id} value={b.id}>{b.name}</option>)}
          </select>
        </div>
      </div>

      {loading ? <LoadingSpinner /> : adoptions.length === 0 ? <EmptyState message="No hay adopciones registradas." /> : (
        <>
          <div className="animal-grid">
            {adoptions.map(a => (
              <div key={a.id} className="animal-card" onClick={() => adoptionService.get(a.id).then(r => setShowDetail(r.data))}>
                <div className="animal-card-img" style={{ height: 140, background: 'linear-gradient(135deg, rgba(245,158,11,0.12), rgba(236,72,153,0.08))' }}>
                  {a.animal?.photos?.find(p => p.is_cover) ? <img src={a.animal.photos.find(p => p.is_cover)!.url} alt="" /> : <HeartHandshake size={40} color="var(--warning)" />}
                </div>
                <div className="animal-card-body">
                  <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start' }}>
                    <h4>{a.animal?.name || `Animal #${a.animal_id}`}</h4>
                    <StatusBadge status={a.status} />
                  </div>
                  <div className="animal-card-meta">
                    {a.animal?.species && <span>{a.animal.species.name}</span>}
                    {a.animal?.breed && <><span>·</span><span>{a.animal.breed.name}</span></>}
                  </div>
                  {a.contact && <p style={{ fontSize: '0.8rem', color: 'var(--text-muted)', marginTop: 8 }}>Contacto: {a.contact}</p>}
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
      <Modal isOpen={!!showDetail} onClose={() => setShowDetail(null)} title={`Adopción — ${showDetail?.animal?.name || ''}`} size="lg">
        {showDetail && (
          <div>
            <div style={{ display: 'flex', gap: 8, marginBottom: 16 }}><StatusBadge status={showDetail.status} size="md" /></div>
            <div className="detail-info-grid" style={{ gridTemplateColumns: 'repeat(2,1fr)', marginBottom: 16 }}>
              <div className="detail-info-item"><label>Animal</label><span>{showDetail.animal?.name} ({showDetail.animal?.municipal_code})</span></div>
              <div className="detail-info-item"><label>Especie / Raza</label><span>{showDetail.animal?.species?.name} {showDetail.animal?.breed ? `/ ${showDetail.animal.breed.name}` : ''}</span></div>
              <div className="detail-info-item"><label>Contacto</label><span>{showDetail.contact || '—'}</span></div>
              <div className="detail-info-item"><label>Fecha Publicación</label><span>{new Date(showDetail.created_at).toLocaleDateString('es-PE')}</span></div>
              {showDetail.adopter && <div className="detail-info-item"><label>Adoptante</label><span>{showDetail.adopter.first_name} {showDetail.adopter.last_name}</span></div>}
              {showDetail.adopted_at && <div className="detail-info-item"><label>Fecha Adopción</label><span>{showDetail.adopted_at}</span></div>}
            </div>
            {showDetail.description && <div style={{ marginBottom: 12 }}><label style={{ display: 'block', fontSize: '0.75rem', color: 'var(--text-muted)', textTransform: 'uppercase', marginBottom: 4 }}>Descripción</label><p style={{ fontSize: '0.9rem' }}>{showDetail.description}</p></div>}
            {showDetail.reason && <div style={{ marginBottom: 12 }}><label style={{ display: 'block', fontSize: '0.75rem', color: 'var(--text-muted)', textTransform: 'uppercase', marginBottom: 4 }}>Razón</label><p style={{ fontSize: '0.9rem' }}>{showDetail.reason}</p></div>}
            {canManage && (
              <div style={{ display: 'flex', gap: 8, flexWrap: 'wrap', paddingTop: 12, borderTop: '1px solid var(--border-light)' }}>
                {statusFlowButtons(showDetail)}
              </div>
            )}
          </div>
        )}
      </Modal>

      {/* Create Modal */}
      <Modal isOpen={showForm} onClose={() => setShowForm(false)} title="Publicar Animal para Adopción" size="md">
        <form onSubmit={handleSubmit}>
          <div className="form-row">
            <div className="form-group"><label>ID del Animal *</label><input type="number" className="form-control" value={form.animal_id} onChange={e => setForm(f => ({ ...f, animal_id: e.target.value }))} required /></div>
            <div className="form-group">
              <label>Foto (Opcional)</label>
              <input type="file" className="form-control" accept="image/*" onChange={handleFileUpload} />
              {form.photo_url && <img src={form.photo_url} alt="Preview" style={{ marginTop: 8, height: 100, borderRadius: 4, objectFit: 'cover' }} />}
            </div>
          </div>
          <div className="form-group"><label>Razón</label><textarea className="form-control" rows={2} value={form.reason || ''} onChange={e => setForm(f => ({ ...f, reason: e.target.value }))} /></div>
          <div className="form-group"><label>Descripción</label><textarea className="form-control" rows={3} value={form.description || ''} onChange={e => setForm(f => ({ ...f, description: e.target.value }))} /></div>
          <div className="form-group"><label>Requisitos</label><textarea className="form-control" rows={2} value={form.requirements || ''} onChange={e => setForm(f => ({ ...f, requirements: e.target.value }))} /></div>
          <div className="form-group"><label>Contacto</label><input className="form-control" value={form.contact || ''} onChange={e => setForm(f => ({ ...f, contact: e.target.value }))} /></div>
          <div style={{ display: 'flex', gap: 12, justifyContent: 'flex-end', paddingTop: 8 }}>
            <button type="button" className="btn btn--secondary" onClick={() => setShowForm(false)}>Cancelar</button>
            <button type="submit" className="btn btn--primary" disabled={saving}>{saving ? 'Publicando...' : 'Publicar'}</button>
          </div>
        </form>
      </Modal>
    </div>
  );
}
