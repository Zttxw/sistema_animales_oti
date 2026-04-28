import { useState, useEffect } from 'react';
import api from '../../api/axios';

export default function Users() {
  const [users, setUsers] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  
  // For the creation form
  const [formData, setFormData] = useState({
    first_name: '',
    last_name: '',
    identity_document: '',
    email: '',
    password: '',
    role: 'CITIZEN'
  });
  const [message, setMessage] = useState('');

  const fetchUsers = () => {
    setLoading(true);
    api.get('/users')
      .then(res => {
        // Laravel paginate response has data inside .data
        setUsers(res.data.data || res.data);
      })
      .catch(err => console.error("Error fetching users", err))
      .finally(() => setLoading(false));
  };

  useEffect(() => {
    fetchUsers();
  }, []);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setMessage('');
    try {
      await api.post('/users', formData);
      setMessage('Usuario creado exitosamente');
      setFormData({
        first_name: '', last_name: '', identity_document: '', email: '', password: '', role: 'CITIZEN'
      });
      fetchUsers(); // Refresh list
    } catch (err: any) {
      setMessage(err.response?.data?.message || 'Error al crear usuario');
    }
  };

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement>) => {
    setFormData({ ...formData, [e.target.name]: e.target.value });
  };

  return (
    <div className="page-container">
      <div className="page-header">
        <h1>Gestión de Usuarios</h1>
      </div>

      <div style={{ display: 'flex', gap: '24px', flexWrap: 'wrap' }}>
        {/* Formulario de Alta Administrativa */}
        <div className="stat-card" style={{ flex: '1', minWidth: '300px' }}>
          <h3>Registrar Personal / Ciudadano</h3>
          {message && <div className="alert-error" style={{backgroundColor: '#e0f2fe', color: '#0369a1', borderColor: '#bae6fd'}}>{message}</div>}
          <form onSubmit={handleSubmit}>
             <div className="form-group">
                <label>Nombres</label>
                <input className="form-control" name="first_name" value={formData.first_name} onChange={handleChange} required />
             </div>
             <div className="form-group">
                <label>Apellidos</label>
                <input className="form-control" name="last_name" value={formData.last_name} onChange={handleChange} required />
             </div>
             <div className="form-group">
                <label>DNI</label>
                <input className="form-control" name="identity_document" value={formData.identity_document} onChange={handleChange} required />
             </div>
             <div className="form-group">
                <label>Email</label>
                <input type="email" className="form-control" name="email" value={formData.email} onChange={handleChange} required />
             </div>
             <div className="form-group">
                <label>Contraseña Temporal</label>
                <input type="password" className="form-control" name="password" value={formData.password} onChange={handleChange} required minLength={8} />
             </div>
             <div className="form-group">
                <label>Asignar Rol</label>
                <select className="form-control" name="role" value={formData.role} onChange={handleChange}>
                  <option value="ADMIN">Administrador</option>
                  <option value="COORDINATOR">Coordinador</option>
                  <option value="VETERINARIAN">Veterinario</option>
                  <option value="INSPECTOR">Inspector</option>
                  <option value="CITIZEN">Ciudadano (Normal)</option>
                </select>
             </div>
             <button type="submit" className="btn-primary full-width">Crear Usuario</button>
          </form>
        </div>

        {/* Lista de Usuarios */}
        <div className="stat-card" style={{ flex: '2', minWidth: '400px' }}>
          <h3>Lista de Usuarios</h3>
          {loading ? <p>Cargando lista...</p> : (
            <div style={{ overflowX: 'auto' }}>
              <table style={{ width: '100%', borderCollapse: 'collapse', marginTop: '10px', fontSize: '0.9rem' }}>
                <thead>
                  <tr style={{ borderBottom: '2px solid var(--border)', textAlign: 'left' }}>
                    <th style={{ padding: '12px 8px' }}>Nombre</th>
                    <th style={{ padding: '12px 8px' }}>DNI</th>
                    <th style={{ padding: '12px 8px' }}>Email</th>
                    <th style={{ padding: '12px 8px' }}>Rol(es)</th>
                    <th style={{ padding: '12px 8px' }}>Estado</th>
                  </tr>
                </thead>
                <tbody>
                  {users.map(u => (
                    <tr key={u.id} style={{ borderBottom: '1px solid var(--border)' }}>
                      <td style={{ padding: '12px 8px' }}>{u.first_name} {u.last_name}</td>
                      <td style={{ padding: '12px 8px' }}>{u.identity_document}</td>
                      <td style={{ padding: '12px 8px' }}>{u.email}</td>
                      <td style={{ padding: '12px 8px' }}>
                        {u.roles && u.roles.length > 0 
                          ? u.roles.map((r: any) => <span key={r.id} className="user-role" style={{marginRight:'4px'}}>{r.name}</span>)
                          : <span className="user-role" style={{background: '#64748b'}}>CITIZEN</span>}
                      </td>
                      <td style={{ padding: '12px 8px' }}>
                        <span style={{ 
                          padding: '4px 8px', borderRadius: '4px', fontSize: '0.8rem',
                          background: u.status === 'ACTIVE' ? '#dcfce7' : '#fee2e2',
                          color: u.status === 'ACTIVE' ? '#166534' : '#991b1b'
                        }}>{u.status}</span>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
