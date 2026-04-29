import { STATUS_COLORS, STATUS_LABELS } from '../types/common';

interface StatusBadgeProps {
  status: string;
  size?: 'sm' | 'md';
}

export default function StatusBadge({ status, size = 'sm' }: StatusBadgeProps) {
  const colors = STATUS_COLORS[status] || { bg: '#f3f4f6', color: '#6b7280' };
  const label = STATUS_LABELS[status] || status;

  return (
    <span
      className={`status-badge status-badge--${size}`}
      style={{ backgroundColor: colors.bg, color: colors.color }}
    >
      {label}
    </span>
  );
}
