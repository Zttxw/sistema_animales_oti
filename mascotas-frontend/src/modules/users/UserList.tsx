import { useState, useEffect, useCallback } from 'react';
import { Plus, Eye, Edit, UserX, ShieldCheck } from 'lucide-react';
import { userService } from './services';
import type { User, UserFilters } from './types';
import type { PaginatedResponse } from '../../shared/types/api';
import StatusBadge from '../../shared/components/StatusBadge';
import LoadingSpinner from '../../shared/components/LoadingSpinner';
import EmptyState from '../../shared/components/EmptyState';
import SearchInput from '../../shared/components/SearchInput';
import Modal from '../../shared/components/Modal';

const ROLES = [
  { value: 'ADMIN', label: 'Administrador' },
  { value: 'COORDINATOR', label: 'Coordinador' },
  { value: 'VETERINARIAN', label: 'Veterinario' },
  { value: 'INSPECTOR', label: 'Inspector' },
  { value: 'CITIZEN', label: 'Ciudadano' },
];

export default function UserList() {
  const [users, setUsers] = useState<User[]>([]);
  const [pagination, setPagination] = useState({ current_page: 1, last_page: 1, total: 0 });
  const [loading, setLoading] = useState(true);
  const [filters, setFilters] = useState<UserFilters>({ page: 1 });
  const [showForm, setShowForm] = useState(false);
  const [showDetail, setShowDetail] = useState<User | null>(null);
  const [saving, setSaving] = useState(false);
  const [message, setMessage] = useState({ text: '', type: '' });
  const [form, setForm] = useState({
    first_name: '', last_name: '', identity_document: '', email: '',
    password: '', phone: '', address: '', sector: '', role: 'CITIZEN',
  });

  const fetchUsers = useCallback(async () => {
    setLoading(true);
    try {
      const res = await userService.list(filters);
      const data = res.data as PaginatedResponse<User>;
      setUsers(data.data);
      setPagination({ current_page: data.current_page, last_page: data.last_page, total: data.total });
    } catch (err) { console.error(err); }
    finally { setLoading(false); }
  }, [filters]);

  useEffect(() => { fetchUsers(); }, [fetchUsers]);

  const handleSearch = useCallback((search: string) => {
    setFilters(f => ({ ...f, search, page: 1 }));
  }, []);

  const handleCreate = async (e: React.FormEvent) => {
    e.preventDefault();
    setSaving(true); setMessage({ text: '', type: '' });
    try {
      await userService.create(form);
      setMessage({ text: 'Usuario creado exitosamente', type: 'success' });
      setShowForm(false);
      setForm({ first_name: '', last_name: '', identity_document: '', email: '', password: '', phone: '', address: '', sector: '', role: 'CITIZEN' });
      fetchUsers();
    } catch (err: any) {
      setMessage({ text: err.response?.data?.message || 'Error al crear usuario', type: 'error' });
    } finally { setSaving(false); }
  };

  const handleStatusChange = async (userId: number, status: string) => {
    try {
      await userService.updateStatus(userId, status);
      fetchUsers();
    } catch (err) { console.error(err); }
  };

  const handleRoleChange = async (userId: number, role: string) => {
    try {
      await userService.updateRole(userId, role);
      fetchUsers();
    } catch (err) { console.error(err); }
  };

  return (
    <div className="page-container">
      <div className="page-header">
        <div className="page-header-left">
          <h1>Gestión de Usuarios</h1>
          <p>{pagination.total} usuarios en el sistema</p>
        </div>
        <button className="btn btn--primary" onClick={() => setShowForm(true)}>
          <Plus size={18} /> Nuevo Usuario
        </button>
      </div>

      {message.text && (
        <div className={`alert alert-${message.type}`}>{message.text}</div>
      )}

      <div className="toolbar">
        <div className="toolbar-left">
          <SearchInput placeholder="Buscar por nombre, DNI o email..." onSearch={handleSearch} />
          <select className="form-control" style={{ maxWidth: 160 }}
            value={filters.role || ''} onChange={e => setFilters(f => ({ ...f, role: e.target.value || undefined, page: 1 }))}>
            <option value="">Todos los roles</option>
            {ROLES.map(r => <option key={r.value} value={r.value}>{r.label}</option>)}
          </select>
          <select className="form-control" style={{ maxWidth: 150 }}
            value={filters.status || ''} onChange={e => setFilters(f => ({ ...f, status: e.target.value || undefined, page: 1 }))}>
            <option value="">Todos los estados</option>
            <option value="ACTIVE">Activo</option>
            <option value="SUSPENDED">Suspendido</option>
            <option value="INACTIVE">Inactivo</option>
          </select>
        </div>
      </div>

      {loading ? <LoadingSpinner /> : users.length === 0 ? <EmptyState message="No se encontraron usuarios." /> : (
        <div className="card">
          <div style={{ overflowX: 'auto' }}>
            <table className="data-table">
              <thead>
                <tr><th>Nombre</th><th>DNI</th><th>Email</th><th>Teléfono</th><th>Rol</th><th>Estado</th><th>Acciones</th></tr>
              </thead>
              <tbody>
                {users.map(u => (
                  <tr key={u.id}>
                    <td style={{ fontWeight: 600 }}>{u.first_name} {u.last_name}</td>
                    <td>{u.identity_document}</td>
                    <td>{u.email}</td>
                    <td>{u.phone || '—'}</td>
                    <td>
                      {u.roles && u.roles.length > 0
                        ? u.roles.map(r => <span key={r.id} className="user-role" style={{ marginRight: 4 }}>{r.name}</span>)
                        : <span className="user-role" style={{ background: '#64748b' }}>CITIZEN</span>}
                    </td>
                    <td><StatusBadge status={u.status} /></td>
                    <td>
                      <div className="table-actions">
                        <button className="btn btn--ghost btn--sm btn--icon" onClick={() => setShowDetail(u)} title="Ver detalle"><Eye size={16} /></button>
                        <button className="btn btn--ghost btn--sm btn--icon" title="Cambiar rol"
                          onClick={() => {
                            const newRole = prompt('Nuevo rol (ADMIN, COORDINATOR, VETERINARIAN, INSPECTOR, CITIZEN):', u.roles?.[0]?.name || 'CITIZEN');
                            if (newRole) handleRoleChange(u.id, newRole);
                          }}><ShieldCheck size={16} /></button>
                        {u.status === 'ACTIVE' ? (
                          <button className="btn btn--ghost btn--sm btn--icon" onClick={() => handleStatusChange(u.id, 'SUSPENDED')} title="Suspender"><UserX size={16} /></button>
                        ) : (
                          <button className="btn btn--ghost btn--sm btn--icon" onClick={() => handleStatusChange(u.id, 'ACTIVE')} title="Activar"><Edit size={16} /></button>
                        )}
                      </div>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
          {pagination.last_page > 1 && (
            <div className="pagination" style={{ padding: '16px 24px' }}>
              <span className="pagination-info">Página {pagination.current_page} de {pagination.last_page}</span>
              <div className="pagination-buttons">
                <button className="pagination-btn" disabled={pagination.current_page <= 1} onClick={() => setFilters(f => ({ ...f, page: (f.page || 1) - 1 }))}>Anterior</button>
                <button className="pagination-btn" disabled={pagination.current_page >= pagination.last_page} onClick={() => setFilters(f => ({ ...f, page: (f.page || 1) + 1 }))}>Siguiente</button>
              </div>
            </div>
          )}
        </div>
      )}

      {/* Create User Modal */}
      <Modal isOpen={showForm} onClose={() => setShowForm(false)} title="Registrar Nuevo Usuario" size="lg">
        <form onSubmit={handleCreate}>
          <div className="form-row">
            <div className="form-group"><label>Nombres *</label><input className="form-control" value={form.first_name} onChange={e => setForm(f => ({ ...f, first_name: e.target.value }))} required /></div>
            <div className="form-group"><label>Apellidos *</label><input className="form-control" value={form.last_name} onChange={e => setForm(f => ({ ...f, last_name: e.target.value }))} required /></div>
          </div>
          <div className="form-row">
            <div className="form-group"><label>DNI *</label><input className="form-control" value={form.identity_document} onChange={e => setForm(f => ({ ...f, identity_document: e.target.value }))} required /></div>
            <div className="form-group"><label>Email *</label><input type="email" className="form-control" value={form.email} onChange={e => setForm(f => ({ ...f, email: e.target.value }))} required /></div>
          </div>
          <div className="form-row">
            <div className="form-group"><label>Contraseña *</label><input type="password" className="form-control" value={form.password} onChange={e => setForm(f => ({ ...f, password: e.target.value }))} required minLength={8} /></div>
            <div className="form-group"><label>Teléfono</label><input className="form-control" value={form.phone} onChange={e => setForm(f => ({ ...f, phone: e.target.value }))} /></div>
          </div>
          <div className="form-row">
            <div className="form-group"><label>Dirección</label><input className="form-control" value={form.address} onChange={e => setForm(f => ({ ...f, address: e.target.value }))} /></div>
            <div className="form-group"><label>Sector</label><input className="form-control" value={form.sector} onChange={e => setForm(f => ({ ...f, sector: e.target.value }))} /></div>
          </div>
          <div className="form-group">
            <label>Rol *</label>
            <select className="form-control" value={form.role} onChange={e => setForm(f => ({ ...f, role: e.target.value }))}>
              {ROLES.map(r => <option key={r.value} value={r.value}>{r.label}</option>)}
            </select>
          </div>
          <div style={{ display: 'flex', gap: 12, justifyContent: 'flex-end', paddingTop: 8 }}>
            <button type="button" className="btn btn--secondary" onClick={() => setShowForm(false)}>Cancelar</button>
            <button type="submit" className="btn btn--primary" disabled={saving}>{saving ? 'Creando...' : 'Crear Usuario'}</button>
          </div>
        </form>
      </Modal>

      {/* User Detail Modal */}
      <Modal isOpen={!!showDetail} onClose={() => setShowDetail(null)} title="Detalle de Usuario" size="md">
        {showDetail && (
          <div className="detail-info-grid" style={{ gridTemplateColumns: 'repeat(2, 1fr)' }}>
            <div className="detail-info-item"><label>Nombre Completo</label><span>{showDetail.first_name} {showDetail.last_name}</span></div>
            <div className="detail-info-item"><label>DNI</label><span>{showDetail.identity_document}</span></div>
            <div className="detail-info-item"><label>Email</label><span>{showDetail.email}</span></div>
            <div className="detail-info-item"><label>Teléfono</label><span>{showDetail.phone || '—'}</span></div>
            <div className="detail-info-item"><label>Dirección</label><span>{showDetail.address || '—'}</span></div>
            <div className="detail-info-item"><label>Sector</label><span>{showDetail.sector || '—'}</span></div>
            <div className="detail-info-item"><label>Estado</label><StatusBadge status={showDetail.status} size="md" /></div>
            <div className="detail-info-item"><label>Registro</label><span>{showDetail.created_at ? new Date(showDetail.created_at).toLocaleDateString('es-PE') : '—'}</span></div>
          </div>
        )}
      </Modal>
    </div>
  );
}
