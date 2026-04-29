import { useState, useEffect } from 'react';
import { Bell, Check, CheckCheck, Trash2 } from 'lucide-react';
import { notificationService } from './services';
import type { Notification } from './services';
import LoadingSpinner from '../../shared/components/LoadingSpinner';
import EmptyState from '../../shared/components/EmptyState';

export default function NotificationList() {
  const [notifications, setNotifications] = useState<Notification[]>([]);
  const [loading, setLoading] = useState(true);

  const fetch = () => {
    setLoading(true);
    notificationService.list()
      .then(r => setNotifications(Array.isArray(r.data) ? r.data : (r.data as any).data || []))
      .catch(console.error)
      .finally(() => setLoading(false));
  };

  useEffect(() => { fetch(); }, []);

  const handleMarkRead = async (id: number) => { await notificationService.markAsRead(id); fetch(); };
  const handleMarkAll = async () => { await notificationService.markAllAsRead(); fetch(); };
  const handleDelete = async (id: number) => { await notificationService.delete(id); fetch(); };

  const unread = notifications.filter(n => !n.read_at).length;

  if (loading) return <LoadingSpinner />;

  return (
    <div className="page-container" style={{ maxWidth: 800 }}>
      <div className="page-header">
        <div className="page-header-left">
          <h1>Notificaciones</h1>
          <p>{unread > 0 ? `${unread} sin leer` : 'Todas leídas'}</p>
        </div>
        {unread > 0 && (
          <button className="btn btn--secondary" onClick={handleMarkAll}>
            <CheckCheck size={18} /> Marcar todas como leídas
          </button>
        )}
      </div>

      {notifications.length === 0 ? <EmptyState title="Sin notificaciones" message="No tienes notificaciones por el momento." icon={<Bell size={48} strokeWidth={1.5} />} /> : (
        <div style={{ display: 'flex', flexDirection: 'column', gap: 8 }}>
          {notifications.map(n => (
            <div key={n.id} className="card" style={{
              borderLeft: !n.read_at ? '3px solid var(--primary)' : '3px solid transparent',
              background: !n.read_at ? 'var(--surface)' : 'var(--surface-hover)',
              opacity: n.read_at ? 0.75 : 1,
            }}>
              <div style={{ padding: '16px 20px', display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', gap: 12 }}>
                <div style={{ flex: 1 }}>
                  <h4 style={{ fontSize: '0.95rem', fontWeight: n.read_at ? 500 : 600, marginBottom: 4 }}>{n.title}</h4>
                  <p style={{ fontSize: '0.85rem', color: 'var(--text-secondary)' }}>{n.message}</p>
                  <span style={{ fontSize: '0.75rem', color: 'var(--text-muted)', marginTop: 6, display: 'inline-block' }}>
                    {new Date(n.created_at).toLocaleString('es-PE')}
                  </span>
                </div>
                <div style={{ display: 'flex', gap: 4 }}>
                  {!n.read_at && (
                    <button className="btn btn--ghost btn--sm btn--icon" onClick={() => handleMarkRead(n.id)} title="Marcar como leída"><Check size={16} /></button>
                  )}
                  <button className="btn btn--ghost btn--sm btn--icon" onClick={() => handleDelete(n.id)} title="Eliminar"><Trash2 size={16} /></button>
                </div>
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}
