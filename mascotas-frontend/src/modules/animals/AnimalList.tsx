import { useState, useEffect, useCallback } from 'react';
import { useNavigate } from 'react-router-dom';
import { Plus, Eye, Grid3X3, List } from 'lucide-react';
import { animalService, catalogService } from './services';
import type { Animal, AnimalFilters } from './types';
import type { PaginatedResponse } from '../../shared/types/api';
import type { Species } from '../../shared/types/common';
import StatusBadge from '../../shared/components/StatusBadge';
import LoadingSpinner from '../../shared/components/LoadingSpinner';
import EmptyState from '../../shared/components/EmptyState';
import SearchInput from '../../shared/components/SearchInput';

export default function AnimalList() {
  const navigate = useNavigate();
  const [animals, setAnimals] = useState<Animal[]>([]);
  const [pagination, setPagination] = useState({ current_page: 1, last_page: 1, total: 0 });
  const [loading, setLoading] = useState(true);
  const [species, setSpecies] = useState<Species[]>([]);
  const [filters, setFilters] = useState<AnimalFilters>({ page: 1 });
  const [viewMode, setViewMode] = useState<'grid' | 'table'>('grid');

  const fetchAnimals = useCallback(async () => {
    setLoading(true);
    try {
      const res = await animalService.list(filters);
      const data = res.data as PaginatedResponse<Animal>;
      setAnimals(data.data);
      setPagination({ current_page: data.current_page, last_page: data.last_page, total: data.total });
    } catch (err) { console.error(err); }
    finally { setLoading(false); }
  }, [filters]);

  useEffect(() => { fetchAnimals(); }, [fetchAnimals]);
  useEffect(() => { catalogService.species().then(r => setSpecies(r.data)); }, []);

  const handleSearch = useCallback((search: string) => {
    setFilters(f => ({ ...f, search, page: 1 }));
  }, []);

  const genderLabel: Record<string, string> = { M: 'Macho', F: 'Hembra', UNKNOWN: 'Desc.' };
  const sizeLabel: Record<string, string> = { SMALL: 'Pequeño', MEDIUM: 'Mediano', LARGE: 'Grande', GIANT: 'Gigante' };

  return (
    <div className="page-container">
      <div className="page-header">
        <div className="page-header-left">
          <h1>Animales de Compañía</h1>
          <p>{pagination.total} registros en el sistema</p>
        </div>
        <button className="btn btn--primary" onClick={() => navigate('/animals/new')}>
          <Plus size={18} /> Registrar Animal
        </button>
      </div>

      <div className="toolbar">
        <div className="toolbar-left">
          <SearchInput placeholder="Buscar por nombre o código..." onSearch={handleSearch} />
          <select className="form-control" style={{ maxWidth: 180 }}
            value={filters.species_id || ''} onChange={e => setFilters(f => ({ ...f, species_id: e.target.value || undefined, page: 1 }))}>
            <option value="">Todas las especies</option>
            {species.map(s => <option key={s.id} value={s.id}>{s.name}</option>)}
          </select>
          <select className="form-control" style={{ maxWidth: 160 }}
            value={filters.status || ''} onChange={e => setFilters(f => ({ ...f, status: e.target.value as any || '', page: 1 }))}>
            <option value="">Todos los estados</option>
            <option value="ACTIVE">Activo</option>
            <option value="LOST">Perdido</option>
            <option value="FOR_ADOPTION">En Adopción</option>
            <option value="DECEASED">Fallecido</option>
          </select>
        </div>
        <div className="toolbar-right">
          <button className={`btn btn--icon btn--${viewMode === 'grid' ? 'primary' : 'ghost'}`} onClick={() => setViewMode('grid')} title="Vista tarjetas"><Grid3X3 size={18} /></button>
          <button className={`btn btn--icon btn--${viewMode === 'table' ? 'primary' : 'ghost'}`} onClick={() => setViewMode('table')} title="Vista tabla"><List size={18} /></button>
        </div>
      </div>

      {loading ? <LoadingSpinner /> : animals.length === 0 ? <EmptyState message="No se encontraron animales con los filtros aplicados." /> : (
        <>
          {viewMode === 'grid' ? (
            <div className="animal-grid">
              {animals.map(animal => (
                <div key={animal.id} className="animal-card" onClick={() => navigate(`/animals/${animal.id}`)}>
                  <div className="animal-card-img">
                    {animal.cover_photo ? <img src={animal.cover_photo.url} alt={animal.name} /> : <span style={{ fontSize: '3rem' }}>🐾</span>}
                  </div>
                  <div className="animal-card-body">
                    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start' }}>
                      <div>
                        <h4>{animal.name}</h4>
                        <span className="animal-code">{animal.municipal_code}</span>
                      </div>
                      <StatusBadge status={animal.status} />
                    </div>
                    <div className="animal-card-meta">
                      <span>{animal.species?.name}</span>
                      {animal.breed && <><span>·</span><span>{animal.breed.name}</span></>}
                      <span>·</span>
                      <span>{genderLabel[animal.gender] || animal.gender}</span>
                      {animal.size && <><span>·</span><span>{sizeLabel[animal.size]}</span></>}
                    </div>
                  </div>
                </div>
              ))}
            </div>
          ) : (
            <div className="card">
              <div style={{ overflowX: 'auto' }}>
                <table className="data-table">
                  <thead>
                    <tr>
                      <th>Código</th><th>Nombre</th><th>Especie</th><th>Raza</th><th>Sexo</th><th>Estado</th><th>Acciones</th>
                    </tr>
                  </thead>
                  <tbody>
                    {animals.map(a => (
                      <tr key={a.id}>
                        <td><span className="animal-code">{a.municipal_code}</span></td>
                        <td style={{ fontWeight: 600 }}>{a.name}</td>
                        <td>{a.species?.name}</td>
                        <td>{a.breed?.name || '—'}</td>
                        <td>{genderLabel[a.gender]}</td>
                        <td><StatusBadge status={a.status} /></td>
                        <td>
                          <button className="btn btn--ghost btn--sm" onClick={() => navigate(`/animals/${a.id}`)}>
                            <Eye size={16} /> Ver
                          </button>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </div>
          )}

          {pagination.last_page > 1 && (
            <div className="pagination">
              <span className="pagination-info">Página {pagination.current_page} de {pagination.last_page} ({pagination.total} registros)</span>
              <div className="pagination-buttons">
                <button className="pagination-btn" disabled={pagination.current_page <= 1}
                  onClick={() => setFilters(f => ({ ...f, page: (f.page || 1) - 1 }))}>Anterior</button>
                <button className="pagination-btn" disabled={pagination.current_page >= pagination.last_page}
                  onClick={() => setFilters(f => ({ ...f, page: (f.page || 1) + 1 }))}>Siguiente</button>
              </div>
            </div>
          )}
        </>
      )}
    </div>
  );
}
