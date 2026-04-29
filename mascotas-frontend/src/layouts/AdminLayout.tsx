import { Outlet, Link, useNavigate, useLocation } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import {
  LayoutDashboard, Users, Dog, Syringe, HeartHandshake,
  Megaphone, AlertTriangle, MessageSquare, Bell, FileText,
  LogOut, Menu, PawPrint
} from 'lucide-react';
import { useState } from 'react';

const menuItems = [
  { name: 'Dashboard', path: '/', icon: LayoutDashboard, roles: ['ADMIN','COORDINATOR','VETERINARIAN','INSPECTOR','CITIZEN'] },
  { name: 'Usuarios', path: '/users', icon: Users, roles: ['ADMIN'] },
  { name: 'Animales', path: '/animals', icon: Dog, roles: ['ADMIN','VETERINARIAN','CITIZEN'] },
  { name: 'Salud Animal', path: '/health', icon: Syringe, roles: ['ADMIN','VETERINARIAN'] },
  { name: 'Adopciones', path: '/adoptions', icon: HeartHandshake, roles: ['ADMIN','COORDINATOR','CITIZEN'] },
  { name: 'Campañas', path: '/campaigns', icon: Megaphone, roles: ['ADMIN','COORDINATOR','VETERINARIAN','CITIZEN'] },
  { name: 'Animales Callejeros', path: '/stray', icon: AlertTriangle, roles: ['ADMIN','INSPECTOR','COORDINATOR','VETERINARIAN'] },
  { name: 'Publicaciones', path: '/posts', icon: MessageSquare, roles: ['ADMIN','COORDINATOR','CITIZEN'] },
  { name: 'Notificaciones', path: '/notifications', icon: Bell, roles: ['ADMIN','COORDINATOR','VETERINARIAN','INSPECTOR','CITIZEN'] },
  { name: 'Reportes', path: '/reports', icon: FileText, roles: ['ADMIN','COORDINATOR','VETERINARIAN','INSPECTOR'] },
];

export default function AdminLayout() {
  const { user, logout, hasRole } = useAuth();
  const navigate = useNavigate();
  const location = useLocation();
  const [sidebarOpen, setSidebarOpen] = useState(true);

  const handleLogout = () => { logout(); navigate('/login'); };

  const filteredMenu = menuItems.filter(item =>
    item.roles.some(role => hasRole(role)) ||
    (user?.roles && user.roles.length === 0 && item.roles.includes('CITIZEN'))
  );

  const isActive = (path: string) => {
    if (path === '/') return location.pathname === '/';
    return location.pathname.startsWith(path);
  };

  return (
    <div className="admin-layout">
      <aside className={`sidebar ${sidebarOpen ? 'open' : 'closed'}`}>
        <div className="sidebar-header">
          <div className="logo-icon"><PawPrint size={20} color="#fff" /></div>
          {sidebarOpen && <h2>SIRACOM</h2>}
        </div>
        <nav className="sidebar-nav">
          <ul>
            {filteredMenu.map(item => (
              <li key={item.path} className={isActive(item.path) ? 'active' : ''}>
                <Link to={item.path}>
                  <item.icon className="icon" size={20} />
                  {sidebarOpen && <span>{item.name}</span>}
                </Link>
              </li>
            ))}
          </ul>
        </nav>
      </aside>

      <div className="main-wrapper">
        <header className="topbar">
          <button className="menu-toggle" onClick={() => setSidebarOpen(!sidebarOpen)}>
            <Menu size={22} />
          </button>
          <div className="user-profile">
            <span className="user-name">{user?.first_name || user?.name}</span>
            <span className="user-role">{user?.roles?.[0]?.name || 'CIUDADANO'}</span>
            <button onClick={handleLogout} className="logout-btn" title="Cerrar Sesión">
              <LogOut size={20} />
            </button>
          </div>
        </header>
        <main className="main-content">
          <Outlet />
        </main>
      </div>
    </div>
  );
}
