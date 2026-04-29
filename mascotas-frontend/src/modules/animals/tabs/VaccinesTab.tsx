import { useState, useEffect } from 'react';
import { Plus, Trash2 } from 'lucide-react';
import { vaccinationService } from '../services';
import type { Vaccination } from '../types';
import LoadingSpinner from '../../../shared/components/LoadingSpinner';
import EmptyState from '../../../shared/components/EmptyState';
import Modal from '../../../shared/components/Modal';

export default function VaccinesTab({ animalId }: { animalId: number }) {
  const [vaccines, setVaccines] = useState<Vaccination[]>([]);
  const [loading, setLoading] = useState(true);
  const [showForm, setShowForm] = useState(false);
  const [saving, setSaving] = useState(false);
  const [form, setForm] = useState({ vaccine_name: '', applied_at: '', next_dose_at: '', notes: '' });

  const fetch = () => {
    setLoading(true);
    vaccinationService.list(animalId)
      .then(r => setVaccines(r.data))
      .catch(console.error)
      .finally(() => setLoading(false));
  };

  useEffect(() => { fetch(); }, [animalId]);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setSaving(true);
    try {
      await vaccinationService.create(animalId, form);
      setShowForm(false);
      setForm({ vaccine_name: '', applied_at: '', next_dose_at: '', notes: '' });
      fetch();
    } catch (err) { console.error(err); }
    finally { setSaving(false); }
  };

  const handleDelete = async (id: number) => {
    if (!confirm('¿Eliminar esta vacuna?')) return;
    await vaccinationService.delete(animalId, id);
    fetch();
  };

  if (loading) return <LoadingSpinner />;

  return (
    <>
      <div style={{ display: 'flex', justifyContent: 'flex-end', marginBottom: 16 }}>
        <button className="btn btn--primary btn--sm" onClick={() => setShowForm(true)}>
          <Plus size={16} /> Registrar Vacuna
        </button>
      </div>

      {vaccines.length === 0 ? <EmptyState message="No hay vacunas registradas." /> : (
        <div className="card">
          <div style={{ overflowX: 'auto' }}>
            <table className="data-table">
              <thead>
                <tr><th>Vacuna</th><th>Fecha Aplicación</th><th>Próxima Dosis</th><th>Registrado por</th><th>Notas</th><th></th></tr>
              </thead>
              <tbody>
                {vaccines.map(v => (
                  <tr key={v.id}>
                    <td style={{ fontWeight: 600 }}>{v.vaccine_name}</td>
                    <td>{new Date(v.applied_at).toLocaleDateString('es-PE')}</td>
                    <td>{v.next_dose_at ? new Date(v.next_dose_at).toLocaleDateString('es-PE') : '—'}</td>
                    <td>{v.registered_by ? `${v.registered_by.first_name} ${v.registered_by.last_name}` : '—'}</td>
                    <td style={{ maxWidth: 200, overflow: 'hidden', textOverflow: 'ellipsis' }}>{v.notes || '—'}</td>
                    <td>
                      <button className="btn btn--ghost btn--sm btn--icon" onClick={() => handleDelete(v.id)} title="Eliminar">
                        <Trash2 size={16} />
                      </button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      )}

      <Modal isOpen={showForm} onClose={() => setShowForm(false)} title="Registrar Vacuna" size="md">
        <form onSubmit={handleSubmit}>
          <div className="form-group">
            <label>Nombre de la Vacuna *</label>
            <input className="form-control" value={form.vaccine_name} onChange={e => setForm(f => ({ ...f, vaccine_name: e.target.value }))} required />
          </div>
          <div className="form-row">
            <div className="form-group">
              <label>Fecha de Aplicación *</label>
              <input className="form-control" type="date" value={form.applied_at} onChange={e => setForm(f => ({ ...f, applied_at: e.target.value }))} required />
            </div>
            <div className="form-group">
              <label>Próxima Dosis</label>
              <input className="form-control" type="date" value={form.next_dose_at} onChange={e => setForm(f => ({ ...f, next_dose_at: e.target.value }))} />
            </div>
          </div>
          <div className="form-group">
            <label>Notas</label>
            <textarea className="form-control" rows={3} value={form.notes} onChange={e => setForm(f => ({ ...f, notes: e.target.value }))} />
          </div>
          <div style={{ display: 'flex', gap: 12, justifyContent: 'flex-end', paddingTop: 8 }}>
            <button type="button" className="btn btn--secondary" onClick={() => setShowForm(false)}>Cancelar</button>
            <button type="submit" className="btn btn--primary" disabled={saving}>{saving ? 'Guardando...' : 'Guardar'}</button>
          </div>
        </form>
      </Modal>
    </>
  );
}
