import { useState, useEffect } from 'react';
import { animalService } from '../services';
import type { AnimalHistory } from '../types';
import LoadingSpinner from '../../../shared/components/LoadingSpinner';
import EmptyState from '../../../shared/components/EmptyState';

const typeLabels: Record<string, string> = {
  DATA: 'Datos', STATUS: 'Estado', VACCINE: 'Vacuna', PROCEDURE: 'Procedimiento', ADOPTION: 'Adopción',
};
const typeColors: Record<string, string> = {
  DATA: '#6366f1', STATUS: '#f59e0b', VACCINE: '#10b981', PROCEDURE: '#3b82f6', ADOPTION: '#ec4899',
};

export default function HistoryTab({ animalId }: { animalId: number }) {
  const [history, setHistory] = useState<AnimalHistory[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    animalService.history(animalId)
      .then(r => setHistory(r.data))
      .catch(console.error)
      .finally(() => setLoading(false));
  }, [animalId]);

  if (loading) return <LoadingSpinner />;
  if (!history.length) return <EmptyState message="No hay registros en el historial." />;

  return (
    <div className="card">
      <div className="card-body">
        <div className="timeline">
          {history.map(h => (
            <div key={h.id} className="timeline-item">
              <div style={{ display: 'flex', gap: 8, alignItems: 'center', marginBottom: 4 }}>
                <span className="status-badge" style={{ backgroundColor: typeColors[h.change_type] + '20', color: typeColors[h.change_type] }}>
                  {typeLabels[h.change_type] || h.change_type}
                </span>
                <span className="timeline-date">{new Date(h.created_at).toLocaleString('es-PE')}</span>
              </div>
              <p>{h.description}</p>
              {h.registered_by && (
                <span style={{ fontSize: '0.8rem', color: 'var(--text-muted)' }}>
                  Por: {h.registered_by.first_name} {h.registered_by.last_name}
                </span>
              )}
            </div>
          ))}
        </div>
      </div>
    </div>
  );
}
