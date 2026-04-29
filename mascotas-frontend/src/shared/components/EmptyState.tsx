import { Inbox } from 'lucide-react';

interface EmptyStateProps {
  title?: string;
  message?: string;
  icon?: React.ReactNode;
}

export default function EmptyState({
  title = 'Sin resultados',
  message = 'No se encontraron registros.',
  icon,
}: EmptyStateProps) {
  return (
    <div className="empty-state">
      {icon || <Inbox size={48} strokeWidth={1.5} />}
      <h3>{title}</h3>
      <p>{message}</p>
    </div>
  );
}
