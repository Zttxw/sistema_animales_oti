import { useState } from 'react';
import { Plus, Trash2, Star } from 'lucide-react';
import { photoService } from '../services';
import type { AnimalPhoto } from '../types';
import Modal from '../../../shared/components/Modal';
import EmptyState from '../../../shared/components/EmptyState';

interface PhotosTabProps {
  animalId: number;
  photos: AnimalPhoto[];
  onUpdate: () => void;
}

export default function PhotosTab({ animalId, photos, onUpdate }: PhotosTabProps) {
  const [showForm, setShowForm] = useState(false);
  const [urlInput, setUrlInput] = useState('');
  const [saving, setSaving] = useState(false);

  const handleUpload = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!urlInput.trim()) return;
    setSaving(true);
    try {
      await photoService.upload(animalId, [urlInput.trim()], photos.length === 0 ? 0 : undefined);
      setShowForm(false);
      setUrlInput('');
      onUpdate();
    } catch (err) { console.error(err); }
    finally { setSaving(false); }
  };

  const handleSetCover = async (photoId: number) => {
    await photoService.setCover(animalId, photoId);
    onUpdate();
  };

  const handleDelete = async (photoId: number) => {
    if (!confirm('¿Eliminar esta foto?')) return;
    await photoService.delete(animalId, photoId);
    onUpdate();
  };

  return (
    <>
      <div style={{ display: 'flex', justifyContent: 'flex-end', marginBottom: 16 }}>
        <button className="btn btn--primary btn--sm" onClick={() => setShowForm(true)}>
          <Plus size={16} /> Agregar Foto
        </button>
      </div>

      {photos.length === 0 ? <EmptyState message="No hay fotos del animal." /> : (
        <div className="photo-grid">
          {photos.map(photo => (
            <div key={photo.id} className="photo-item">
              <img src={photo.url} alt="Foto animal" />
              {photo.is_cover && <span className="cover-badge">Portada</span>}
              <div style={{
                position: 'absolute', bottom: 0, left: 0, right: 0,
                background: 'linear-gradient(transparent, rgba(0,0,0,0.7))',
                padding: '8px', display: 'flex', gap: 4, justifyContent: 'flex-end',
                opacity: 0, transition: 'opacity 0.2s',
              }}
              className="photo-actions"
              onMouseEnter={e => (e.currentTarget.style.opacity = '1')}
              onMouseLeave={e => (e.currentTarget.style.opacity = '0')}
              >
                {!photo.is_cover && (
                  <button className="btn btn--sm" style={{ background: 'rgba(255,255,255,0.2)', color: '#fff', border: 'none', padding: '4px 8px' }}
                    onClick={(e) => { e.stopPropagation(); handleSetCover(photo.id); }} title="Establecer como portada">
                    <Star size={14} />
                  </button>
                )}
                <button className="btn btn--sm" style={{ background: 'rgba(239,68,68,0.8)', color: '#fff', border: 'none', padding: '4px 8px' }}
                  onClick={(e) => { e.stopPropagation(); handleDelete(photo.id); }} title="Eliminar">
                  <Trash2 size={14} />
                </button>
              </div>
            </div>
          ))}
        </div>
      )}

      <Modal isOpen={showForm} onClose={() => setShowForm(false)} title="Agregar Foto" size="sm">
        <form onSubmit={handleUpload}>
          <div className="form-group">
            <label>URL de la imagen *</label>
            <input className="form-control" type="url" placeholder="https://..." value={urlInput} onChange={e => setUrlInput(e.target.value)} required />
          </div>
          <div style={{ display: 'flex', gap: 12, justifyContent: 'flex-end', paddingTop: 8 }}>
            <button type="button" className="btn btn--secondary" onClick={() => setShowForm(false)}>Cancelar</button>
            <button type="submit" className="btn btn--primary" disabled={saving}>{saving ? 'Subiendo...' : 'Agregar'}</button>
          </div>
        </form>
      </Modal>
    </>
  );
}
