import { useState, useEffect } from 'react';
import { Plus, Trash2 } from 'lucide-react';
import { procedureService, catalogService } from '../services';
import type { HealthProcedure } from '../types';
import type { ProcedureType } from '../../../shared/types/common';
import LoadingSpinner from '../../../shared/components/LoadingSpinner';
import EmptyState from '../../../shared/components/EmptyState';
import Modal from '../../../shared/components/Modal';

export default function ProceduresTab({ animalId }: { animalId: number }) {
  const [procedures, setProcedures] = useState<HealthProcedure[]>([]);
  const [procedureTypes, setProcedureTypes] = useState<ProcedureType[]>([]);
  const [loading, setLoading] = useState(true);
  const [showForm, setShowForm] = useState(false);
  const [saving, setSaving] = useState(false);
  const [form, setForm] = useState({ procedure_type_id: '', performed_at: '', description: '', notes: '' });

  const fetch = () => {
    setLoading(true);
    procedureService.list(animalId)
      .then(r => setProcedures(r.data))
      .catch(console.error)
      .finally(() => setLoading(false));
  };

  useEffect(() => {
    fetch();
    catalogService.procedureTypes().then(r => setProcedureTypes(r.data));
  }, [animalId]);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setSaving(true);
    try {
      await procedureService.create(animalId, form);
      setShowForm(false);
      setForm({ procedure_type_id: '', performed_at: '', description: '', notes: '' });
      fetch();
    } catch (err) { console.error(err); }
    finally { setSaving(false); }
  };

  const handleDelete = async (id: number) => {
    if (!confirm('¿Eliminar este procedimiento?')) return;
    await procedureService.delete(animalId, id);
    fetch();
  };

  if (loading) return <LoadingSpinner />;

  return (
    <>
      <div style={{ display: 'flex', justifyContent: 'flex-end', marginBottom: 16 }}>
        <button className="btn btn--primary btn--sm" onClick={() => setShowForm(true)}>
          <Plus size={16} /> Registrar Procedimiento
        </button>
      </div>

      {procedures.length === 0 ? <EmptyState message="No hay procedimientos registrados." /> : (
        <div className="card">
          <div style={{ overflowX: 'auto' }}>
            <table className="data-table">
              <thead>
                <tr><th>Tipo</th><th>Fecha</th><th>Descripción</th><th>Registrado por</th><th></th></tr>
              </thead>
              <tbody>
                {procedures.map(p => (
                  <tr key={p.id}>
                    <td style={{ fontWeight: 600 }}>{p.procedure_type?.name || '—'}</td>
                    <td>{new Date(p.performed_at).toLocaleDateString('es-PE')}</td>
                    <td style={{ maxWidth: 250, overflow: 'hidden', textOverflow: 'ellipsis' }}>{p.description || '—'}</td>
                    <td>{p.registered_by ? `${p.registered_by.first_name} ${p.registered_by.last_name}` : '—'}</td>
                    <td>
                      <button className="btn btn--ghost btn--sm btn--icon" onClick={() => handleDelete(p.id)} title="Eliminar">
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

      <Modal isOpen={showForm} onClose={() => setShowForm(false)} title="Registrar Procedimiento" size="md">
        <form onSubmit={handleSubmit}>
          <div className="form-group">
            <label>Tipo de Procedimiento *</label>
            <select className="form-control" value={form.procedure_type_id} onChange={e => setForm(f => ({ ...f, procedure_type_id: e.target.value }))} required>
              <option value="">Seleccionar...</option>
              {procedureTypes.map(pt => <option key={pt.id} value={pt.id}>{pt.name}</option>)}
            </select>
          </div>
          <div className="form-group">
            <label>Fecha *</label>
            <input className="form-control" type="date" value={form.performed_at} onChange={e => setForm(f => ({ ...f, performed_at: e.target.value }))} required />
          </div>
          <div className="form-group">
            <label>Descripción</label>
            <textarea className="form-control" rows={3} value={form.description} onChange={e => setForm(f => ({ ...f, description: e.target.value }))} />
          </div>
          <div className="form-group">
            <label>Notas</label>
            <textarea className="form-control" rows={2} value={form.notes} onChange={e => setForm(f => ({ ...f, notes: e.target.value }))} />
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
