import { useState, useEffect } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { ArrowLeft, Save } from 'lucide-react';
import { animalService, catalogService } from './services';
import type { AnimalFormData } from './types';
import type { Species, Breed } from '../../shared/types/common';
import LoadingSpinner from '../../shared/components/LoadingSpinner';

const emptyForm: AnimalFormData = {
  user_id: '', species_id: '', breed_id: '', name: '', gender: '',
  birth_date: '', approximate_age: '', color: '', size: '',
  reproductive_status: '', distinctive_features: '', notes: '',
};

export default function AnimalForm() {
  const { id } = useParams();
  const navigate = useNavigate();
  const isEdit = Boolean(id);

  const [form, setForm] = useState<AnimalFormData>(emptyForm);
  const [species, setSpecies] = useState<Species[]>([]);
  const [breeds, setBreeds] = useState<Breed[]>([]);
  const [loading, setLoading] = useState(false);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState('');

  useEffect(() => {
    catalogService.species().then(r => setSpecies(r.data));
    if (isEdit && id) {
      setLoading(true);
      animalService.get(Number(id)).then(r => {
        const a = r.data;
        setForm({
          user_id: a.user_id, species_id: a.species_id, breed_id: a.breed_id || '',
          name: a.name, gender: a.gender, birth_date: a.birth_date || '',
          approximate_age: a.approximate_age || '', color: a.color || '',
          size: a.size || '', reproductive_status: a.reproductive_status || '',
          distinctive_features: a.distinctive_features || '', notes: a.notes || '',
        });
        if (a.species_id) catalogService.breeds(a.species_id).then(r2 => setBreeds(r2.data));
      }).catch(() => setError('Error cargando datos del animal'))
        .finally(() => setLoading(false));
    }
  }, [id, isEdit]);

  useEffect(() => {
    if (form.species_id) {
      catalogService.breeds(Number(form.species_id)).then(r => setBreeds(r.data));
    } else { setBreeds([]); }
  }, [form.species_id]);

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement>) => {
    const { name, value } = e.target;
    setForm(f => ({ ...f, [name]: value }));
    if (name === 'species_id') setForm(f => ({ ...f, breed_id: '' }));
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError(''); setSaving(true);
    try {
      const payload = { ...form };
      if (!payload.breed_id) delete payload.breed_id;
      if (isEdit && id) {
        await animalService.update(Number(id), payload);
      } else {
        await animalService.create(payload);
      }
      navigate('/animals');
    } catch (err: any) {
      setError(err.response?.data?.message || 'Error al guardar');
    } finally { setSaving(false); }
  };

  if (loading) return <LoadingSpinner />;

  return (
    <div className="page-container" style={{ maxWidth: 720 }}>
      <div className="page-header">
        <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
          <button className="btn btn--ghost btn--icon" onClick={() => navigate('/animals')}><ArrowLeft size={20} /></button>
          <div className="page-header-left">
            <h1>{isEdit ? 'Editar Animal' : 'Registrar Nuevo Animal'}</h1>
            <p>{isEdit ? 'Modifica los datos del animal' : 'Completa los datos para registrar un nuevo animal'}</p>
          </div>
        </div>
      </div>

      <div className="card">
        <div className="card-body">
          {error && <div className="alert alert-error">{error}</div>}
          <form onSubmit={handleSubmit}>
            {!isEdit && (
              <div className="form-group">
                <label>ID del Propietario *</label>
                <input className="form-control" name="user_id" type="number" value={form.user_id} onChange={handleChange} required />
              </div>
            )}

            <div className="form-row">
              <div className="form-group">
                <label>Nombre *</label>
                <input className="form-control" name="name" value={form.name} onChange={handleChange} required maxLength={100} />
              </div>
              <div className="form-group">
                <label>Sexo *</label>
                <select className="form-control" name="gender" value={form.gender} onChange={handleChange} required>
                  <option value="">Seleccionar...</option>
                  <option value="M">Macho</option>
                  <option value="F">Hembra</option>
                  <option value="UNKNOWN">Desconocido</option>
                </select>
              </div>
            </div>

            <div className="form-row">
              <div className="form-group">
                <label>Especie *</label>
                <select className="form-control" name="species_id" value={form.species_id} onChange={handleChange} required>
                  <option value="">Seleccionar...</option>
                  {species.map(s => <option key={s.id} value={s.id}>{s.name}</option>)}
                </select>
              </div>
              <div className="form-group">
                <label>Raza</label>
                <select className="form-control" name="breed_id" value={form.breed_id || ''} onChange={handleChange}>
                  <option value="">Sin especificar</option>
                  {breeds.map(b => <option key={b.id} value={b.id}>{b.name}</option>)}
                </select>
              </div>
            </div>

            <div className="form-row">
              <div className="form-group">
                <label>Fecha de Nacimiento</label>
                <input className="form-control" type="date" name="birth_date" value={form.birth_date || ''} onChange={handleChange} />
              </div>
              <div className="form-group">
                <label>Edad Aproximada</label>
                <input className="form-control" name="approximate_age" value={form.approximate_age || ''} onChange={handleChange} placeholder="Ej: 2 años" />
              </div>
            </div>

            <div className="form-row">
              <div className="form-group">
                <label>Color</label>
                <input className="form-control" name="color" value={form.color || ''} onChange={handleChange} />
              </div>
              <div className="form-group">
                <label>Tamaño</label>
                <select className="form-control" name="size" value={form.size || ''} onChange={handleChange}>
                  <option value="">Seleccionar...</option>
                  <option value="SMALL">Pequeño</option>
                  <option value="MEDIUM">Mediano</option>
                  <option value="LARGE">Grande</option>
                  <option value="GIANT">Gigante</option>
                </select>
              </div>
            </div>

            <div className="form-group">
              <label>Estado Reproductivo</label>
              <select className="form-control" name="reproductive_status" value={form.reproductive_status || ''} onChange={handleChange}>
                <option value="">Seleccionar...</option>
                <option value="INTACT">Intacto</option>
                <option value="SPAYED">Esterilizada</option>
                <option value="NEUTERED">Castrado</option>
                <option value="UNKNOWN">Desconocido</option>
              </select>
            </div>

            <div className="form-group">
              <label>Características Distintivas</label>
              <textarea className="form-control" name="distinctive_features" rows={3} value={form.distinctive_features || ''} onChange={handleChange} placeholder="Marcas, cicatrices, etc." />
            </div>

            <div className="form-group">
              <label>Notas</label>
              <textarea className="form-control" name="notes" rows={3} value={form.notes || ''} onChange={handleChange} />
            </div>

            <div style={{ display: 'flex', gap: 12, justifyContent: 'flex-end', paddingTop: 16 }}>
              <button type="button" className="btn btn--secondary" onClick={() => navigate('/animals')}>Cancelar</button>
              <button type="submit" className="btn btn--primary" disabled={saving}>
                <Save size={18} /> {saving ? 'Guardando...' : isEdit ? 'Guardar Cambios' : 'Registrar Animal'}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  );
}
