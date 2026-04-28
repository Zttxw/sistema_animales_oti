import { useState } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import { useAuth } from '../../context/AuthContext';
import api from '../../api/axios';

export default function Register() {
  const [formData, setFormData] = useState({
    first_name: '',
    last_name: '',
    identity_document: '',
    email: '',
    password: '',
    password_confirmation: ''
  });
  const [error, setError] = useState('');
  const [isLoading, setIsLoading] = useState(false);
  const { login } = useAuth();
  const navigate = useNavigate();

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError('');
    setIsLoading(true);

    try {
      const response = await api.post('/auth/register', formData);
      const { token, user } = response.data;
      login(token, user);
      navigate('/');
    } catch (err: any) {
      setError(err.response?.data?.message || 'Error al registrar. Verifica los datos.');
    } finally {
      setIsLoading(false);
    }
  };

  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    setFormData({ ...formData, [e.target.name]: e.target.value });
  };

  return (
    <div className="login-container">
      <div className="login-card">
        <h2>Registro Ciudadano</h2>
        <p>Crea tu cuenta para registrar a tus mascotas</p>
        
        {error && <div className="alert-error">{error}</div>}
        
        <form onSubmit={handleSubmit}>
          <div className="form-group">
            <label>Nombres</label>
            <input type="text" name="first_name" required className="form-control" onChange={handleChange} />
          </div>
          <div className="form-group">
            <label>Apellidos</label>
            <input type="text" name="last_name" required className="form-control" onChange={handleChange} />
          </div>
          <div className="form-group">
            <label>DNI</label>
            <input type="text" name="identity_document" required className="form-control" onChange={handleChange} />
          </div>
          <div className="form-group">
            <label>Correo Electrónico</label>
            <input type="email" name="email" required className="form-control" onChange={handleChange} />
          </div>
          <div className="form-group">
            <label>Contraseña</label>
            <input type="password" name="password" required className="form-control" minLength={8} onChange={handleChange} />
          </div>
          <div className="form-group">
            <label>Confirmar Contraseña</label>
            <input type="password" name="password_confirmation" required className="form-control" minLength={8} onChange={handleChange} />
          </div>
          <button type="submit" disabled={isLoading} className="btn-primary full-width">
            {isLoading ? 'Registrando...' : 'Registrarme'}
          </button>
        </form>
        <div style={{ marginTop: '15px', textAlign: 'center' }}>
          <Link to="/login" style={{ color: 'var(--primary)', textDecoration: 'none', fontWeight: 500 }}>
            ¿Ya tienes una cuenta? Inicia Sesión
          </Link>
        </div>
      </div>
    </div>
  );
}
