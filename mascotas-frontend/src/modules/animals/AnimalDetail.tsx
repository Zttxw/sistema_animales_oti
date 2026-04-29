import { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { ArrowLeft, Edit, Dog, Clock, Syringe, Stethoscope, Camera } from 'lucide-react';
import { animalService } from './services';
import type { Animal } from './types';
import StatusBadge from '../../shared/components/StatusBadge';
import LoadingSpinner from '../../shared/components/LoadingSpinner';
import InfoTab from './tabs/InfoTab';
import HistoryTab from './tabs/HistoryTab';
import VaccinesTab from './tabs/VaccinesTab';
import ProceduresTab from './tabs/ProceduresTab';
import PhotosTab from './tabs/PhotosTab';

type TabKey = 'info' | 'history' | 'vaccines' | 'procedures' | 'photos';

const tabs: { key: TabKey; label: string; icon: React.ElementType }[] = [
  { key: 'info', label: 'Información', icon: Dog },
  { key: 'history', label: 'Historial', icon: Clock },
  { key: 'vaccines', label: 'Vacunas', icon: Syringe },
  { key: 'procedures', label: 'Procedimientos', icon: Stethoscope },
  { key: 'photos', label: 'Fotos', icon: Camera },
];

export default function AnimalDetail() {
  const { id } = useParams();
  const navigate = useNavigate();
  const [animal, setAnimal] = useState<Animal | null>(null);
  const [loading, setLoading] = useState(true);
  const [activeTab, setActiveTab] = useState<TabKey>('info');

  const fetchAnimal = () => {
    if (!id) return;
    setLoading(true);
    animalService.get(Number(id))
      .then(r => setAnimal(r.data))
      .catch(() => navigate('/animals'))
      .finally(() => setLoading(false));
  };

  useEffect(() => { fetchAnimal(); }, [id]);

  if (loading || !animal) return <LoadingSpinner />;

  const genderLabel: Record<string, string> = { M: 'Macho', F: 'Hembra', UNKNOWN: 'Desconocido' };

  return (
    <div className="page-container">
      <div style={{ marginBottom: 20 }}>
        <button className="btn btn--ghost" onClick={() => navigate('/animals')}>
          <ArrowLeft size={18} /> Volver al listado
        </button>
      </div>

      <div className="detail-header">
        <div className="detail-cover">
          {animal.cover_photo || (animal.photos && animal.photos.length > 0)
            ? <img src={animal.cover_photo?.url || animal.photos![0].url} alt={animal.name} />
            : <span style={{ fontSize: '4rem' }}>🐾</span>
          }
        </div>
        <div className="detail-info">
          <div style={{ display: 'flex', alignItems: 'center', gap: 12, flexWrap: 'wrap' }}>
            <h1>{animal.name}</h1>
            <StatusBadge status={animal.status} size="md" />
            <span className="animal-code">{animal.municipal_code}</span>
          </div>
          <div className="detail-info-grid">
            <div className="detail-info-item">
              <label>Especie</label>
              <span>{animal.species?.name || '—'}</span>
            </div>
            <div className="detail-info-item">
              <label>Raza</label>
              <span>{animal.breed?.name || 'Sin especificar'}</span>
            </div>
            <div className="detail-info-item">
              <label>Sexo</label>
              <span>{genderLabel[animal.gender]}</span>
            </div>
            <div className="detail-info-item">
              <label>Propietario</label>
              <span>{animal.owner ? `${animal.owner.first_name} ${animal.owner.last_name}` : '—'}</span>
            </div>
          </div>
          <div style={{ marginTop: 16 }}>
            <button className="btn btn--primary btn--sm" onClick={() => navigate(`/animals/${id}/edit`)}>
              <Edit size={16} /> Editar
            </button>
          </div>
        </div>
      </div>

      <div className="tabs">
        {tabs.map(tab => (
          <button key={tab.key}
            className={`tab-btn ${activeTab === tab.key ? 'active' : ''}`}
            onClick={() => setActiveTab(tab.key)}>
            <tab.icon size={16} /> {tab.label}
          </button>
        ))}
      </div>

      {activeTab === 'info' && <InfoTab animal={animal} />}
      {activeTab === 'history' && <HistoryTab animalId={animal.id} />}
      {activeTab === 'vaccines' && <VaccinesTab animalId={animal.id} />}
      {activeTab === 'procedures' && <ProceduresTab animalId={animal.id} />}
      {activeTab === 'photos' && <PhotosTab animalId={animal.id} photos={animal.photos || []} onUpdate={fetchAnimal} />}
    </div>
  );
}
