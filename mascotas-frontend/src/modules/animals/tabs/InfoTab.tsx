import type { Animal } from '../types';

const sizeLabel: Record<string, string> = { SMALL: 'Pequeño', MEDIUM: 'Mediano', LARGE: 'Grande', GIANT: 'Gigante' };
const reproLabel: Record<string, string> = { INTACT: 'Intacto', SPAYED: 'Esterilizada', NEUTERED: 'Castrado', UNKNOWN: 'Desconocido' };

export default function InfoTab({ animal }: { animal: Animal }) {
  return (
    <div className="card">
      <div className="card-body">
        <div className="detail-info-grid" style={{ gridTemplateColumns: 'repeat(auto-fill, minmax(200px, 1fr))' }}>
          <div className="detail-info-item">
            <label>Código Municipal</label>
            <span>{animal.municipal_code}</span>
          </div>
          <div className="detail-info-item">
            <label>Fecha de Nacimiento</label>
            <span>{animal.birth_date || 'No registrada'}</span>
          </div>
          <div className="detail-info-item">
            <label>Edad Aproximada</label>
            <span>{animal.approximate_age || 'No especificada'}</span>
          </div>
          <div className="detail-info-item">
            <label>Color</label>
            <span>{animal.color || 'No especificado'}</span>
          </div>
          <div className="detail-info-item">
            <label>Tamaño</label>
            <span>{animal.size ? sizeLabel[animal.size] : 'No especificado'}</span>
          </div>
          <div className="detail-info-item">
            <label>Estado Reproductivo</label>
            <span>{animal.reproductive_status ? reproLabel[animal.reproductive_status] : 'No especificado'}</span>
          </div>
          <div className="detail-info-item">
            <label>Registrado</label>
            <span>{new Date(animal.created_at).toLocaleDateString('es-PE')}</span>
          </div>
          {animal.death_date && (
            <div className="detail-info-item">
              <label>Fecha de Fallecimiento</label>
              <span>{animal.death_date}</span>
            </div>
          )}
        </div>
        {animal.distinctive_features && (
          <div style={{ marginTop: 24 }}>
            <label style={{ display: 'block', fontSize: '0.75rem', color: 'var(--text-muted)', textTransform: 'uppercase', letterSpacing: '0.04em', marginBottom: 6 }}>Características Distintivas</label>
            <p style={{ fontSize: '0.9rem' }}>{animal.distinctive_features}</p>
          </div>
        )}
        {animal.notes && (
          <div style={{ marginTop: 16 }}>
            <label style={{ display: 'block', fontSize: '0.75rem', color: 'var(--text-muted)', textTransform: 'uppercase', letterSpacing: '0.04em', marginBottom: 6 }}>Notas</label>
            <p style={{ fontSize: '0.9rem' }}>{animal.notes}</p>
          </div>
        )}
        {animal.death_reason && (
          <div style={{ marginTop: 16 }}>
            <label style={{ display: 'block', fontSize: '0.75rem', color: 'var(--text-muted)', textTransform: 'uppercase', letterSpacing: '0.04em', marginBottom: 6 }}>Razón de Fallecimiento</label>
            <p style={{ fontSize: '0.9rem' }}>{animal.death_reason}</p>
          </div>
        )}
      </div>
    </div>
  );
}
