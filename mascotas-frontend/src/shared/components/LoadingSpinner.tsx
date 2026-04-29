export default function LoadingSpinner({ message = 'Cargando...' }: { message?: string }) {
  return (
    <div className="loading-container">
      <div className="spinner" />
      <p className="loading-text">{message}</p>
    </div>
  );
}
