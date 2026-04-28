import { Link } from 'react-router-dom';
import { AlertTriangle } from 'lucide-react';

export default function Unauthorized() {
  return (
    <div className="page-container" style={{ textAlign: 'center', marginTop: '100px' }}>
      <AlertTriangle size={64} color="var(--error)" style={{ margin: '0 auto 20px' }} />
      <h1>403 - Acceso Denegado</h1>
      <p style={{ marginBottom: '30px' }}>No tienes los permisos necesarios para acceder a este módulo.</p>
      <Link to="/" className="btn-primary" style={{ textDecoration: 'none' }}>
        Volver al Dashboard
      </Link>
    </div>
  );
}
