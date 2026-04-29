import { useState, useEffect, useCallback } from 'react';
import { Plus, MessageSquare, Eye, Trash2, Send } from 'lucide-react';
import { postService } from './services';
import type { Post, Comment } from './services';
import type { PaginatedResponse } from '../../shared/types/api';
import StatusBadge from '../../shared/components/StatusBadge';
import LoadingSpinner from '../../shared/components/LoadingSpinner';
import EmptyState from '../../shared/components/EmptyState';
import Modal from '../../shared/components/Modal';
import { useAuth } from '../../context/AuthContext';
import api from '../../api/axios';

export default function PostList() {
  const { hasRole } = useAuth();
  const isAdmin = hasRole('ADMIN') || hasRole('COORDINATOR');
  const [posts, setPosts] = useState<Post[]>([]);
  const [pagination, setPagination] = useState({ current_page: 1, last_page: 1, total: 0 });
  const [loading, setLoading] = useState(true);
  const [page, setPage] = useState(1);
  const [postTypes, setPostTypes] = useState<{ id: number; name: string }[]>([]);
  const [showForm, setShowForm] = useState(false);
  const [showDetail, setShowDetail] = useState<Post | null>(null);
  const [comments, setComments] = useState<Comment[]>([]);
  const [newComment, setNewComment] = useState('');
  const [commentError, setCommentError] = useState('');
  const [saving, setSaving] = useState(false);
  const [form, setForm] = useState({ title: '', content: '', post_type_id: '', photo_url: '' });

  const fetch = useCallback(async () => {
    setLoading(true);
    try {
      const res = await postService.list({ page });
      const data = res.data as PaginatedResponse<Post>;
      setPosts(data.data);
      setPagination({ current_page: data.current_page, last_page: data.last_page, total: data.total });
    } catch (err) { console.error(err); }
    finally { setLoading(false); }
  }, [page]);

  useEffect(() => { fetch(); }, [fetch]);
  useEffect(() => { postService.postTypes().then(r => setPostTypes(r.data)); }, []);

  const openDetail = async (post: Post) => {
    setShowDetail(post);
    try {
      const r = await postService.comments(post.id);
      setComments(Array.isArray(r.data) ? r.data : (r.data as any).data || []);
    } catch { setComments([]); }
  };

  const handleFileUpload = async (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (!file) return;
    const formData = new FormData();
    formData.append('file', file);
    try {
      const res = await api.post('/upload', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
      setForm(f => ({ ...f, photo_url: res.data.url }));
    } catch (err) {
      console.error('Error uploading file:', err);
      alert('Error al subir la imagen');
    }
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault(); setSaving(true);
    try {
      const payload: any = { ...form };
      if (form.photo_url) payload.photos = [form.photo_url];
      
      await postService.create(payload);
      setShowForm(false);
      setForm({ title: '', content: '', post_type_id: '', photo_url: '' });
      fetch();
    } catch (err) { console.error(err); }
    finally { setSaving(false); }
  };

  const handleAddComment = async () => {
    if (!newComment.trim() || !showDetail) return;
    setCommentError('');
    try {
      await postService.addComment(showDetail.id, newComment);
      setNewComment('');
      const r = await postService.comments(showDetail.id);
      setComments(Array.isArray(r.data) ? r.data : (r.data as any).data || []);
    } catch (err: any) {
      setCommentError(err.response?.data?.message || 'Error al enviar comentario');
    }
  };

  const handleStatusChange = async (id: number, status: string) => {
    await postService.updateStatus(id, status);
    fetch();
    if (showDetail?.id === id) {
      const res = await postService.get(id);
      setShowDetail(res.data);
    }
  };

  const handleDelete = async (id: number) => {
    if (!confirm('¿Eliminar esta publicación?')) return;
    await postService.delete(id);
    fetch();
  };

  return (
    <div className="page-container">
      <div className="page-header">
        <div className="page-header-left"><h1>Publicaciones</h1><p>{pagination.total} publicaciones</p></div>
        <button className="btn btn--primary" onClick={() => setShowForm(true)}><Plus size={18} /> Nueva Publicación</button>
      </div>

      {loading ? <LoadingSpinner /> : posts.length === 0 ? <EmptyState message="No hay publicaciones." /> : (
        <>
          <div style={{ display: 'flex', flexDirection: 'column', gap: 12 }}>
            {posts.map(p => (
              <div key={p.id} className="card" style={{ cursor: 'pointer' }} onClick={() => openDetail(p)}>
                <div style={{ padding: '20px 24px', display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start' }}>
                  <div style={{ flex: 1 }}>
                    <div style={{ display: 'flex', gap: 8, alignItems: 'center', marginBottom: 6 }}>
                      {p.post_type && <span className="status-badge" style={{ background: 'var(--info-bg)', color: 'var(--info)' }}>{p.post_type.name}</span>}
                      <StatusBadge status={p.status} />
                    </div>
                    <h3 style={{ fontSize: '1.05rem', fontWeight: 600, marginBottom: 4 }}>{p.title}</h3>
                    <p style={{ fontSize: '0.85rem', color: 'var(--text-secondary)', overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap', maxWidth: 600 }}>{p.content}</p>
                    <div style={{ display: 'flex', gap: 16, marginTop: 8, fontSize: '0.8rem', color: 'var(--text-muted)' }}>
                      <span>{p.author ? `${p.author.first_name} ${p.author.last_name}` : '—'}</span>
                      <span>{new Date(p.created_at).toLocaleDateString('es-PE')}</span>
                      <span><MessageSquare size={12} style={{ verticalAlign: 'middle' }} /> {p.comments_count || 0} comentarios</span>
                    </div>
                  </div>
                  <button className="btn btn--ghost btn--sm btn--icon" onClick={(e) => { e.stopPropagation(); handleDelete(p.id); }} title="Eliminar"><Trash2 size={16} /></button>
                </div>
              </div>
            ))}
          </div>
          {pagination.last_page > 1 && (
            <div className="pagination">
              <span className="pagination-info">Página {pagination.current_page} de {pagination.last_page}</span>
              <div className="pagination-buttons">
                <button className="pagination-btn" disabled={page <= 1} onClick={() => setPage(p => p - 1)}>Anterior</button>
                <button className="pagination-btn" disabled={page >= pagination.last_page} onClick={() => setPage(p => p + 1)}>Siguiente</button>
              </div>
            </div>
          )}
        </>
      )}

      {/* Detail + Comments */}
      <Modal isOpen={!!showDetail} onClose={() => { setShowDetail(null); setComments([]); setCommentError(''); }} title={showDetail?.title || ''} size="lg">
        {showDetail && (<div>
          <div style={{ display: 'flex', gap: 8, marginBottom: 12 }}>
            {showDetail.post_type && <span className="status-badge" style={{ background: 'var(--info-bg)', color: 'var(--info)' }}>{showDetail.post_type.name}</span>}
            <StatusBadge status={showDetail.status} size="md" />
          </div>
          
          <p style={{ fontSize: '0.9rem', lineHeight: 1.6, marginBottom: 20, whiteSpace: 'pre-wrap' }}>{showDetail.content}</p>
          
          {/* Photos */}
          {(showDetail as any).photos?.length > 0 && (
            <div className="photo-grid" style={{ marginBottom: 20 }}>
              {(showDetail as any).photos.map((p: any) => (
                <div key={p.id} className="photo-item"><img src={p.url} alt="" /></div>
              ))}
            </div>
          )}

          <div style={{ fontSize: '0.8rem', color: 'var(--text-muted)', marginBottom: 20 }}>
            Por {showDetail.author?.first_name} {showDetail.author?.last_name} · {new Date(showDetail.created_at).toLocaleString('es-PE')}
          </div>
          
          <div style={{ display: 'flex', gap: 8, flexWrap: 'wrap', marginBottom: 16 }}>
            {isAdmin && showDetail.status === 'DRAFT' && <button className="btn btn--primary btn--sm" onClick={() => handleStatusChange(showDetail.id, 'PUBLISHED')}>Aprobar y Publicar</button>}
            {isAdmin && showDetail.status === 'PUBLISHED' && <button className="btn btn--accent btn--sm" onClick={() => handleStatusChange(showDetail.id, 'FEATURED')}>Destacar</button>}
          </div>

          <div style={{ borderTop: '1px solid var(--border-light)', paddingTop: 16 }}>
            <h4 style={{ fontSize: '0.9rem', marginBottom: 12 }}>Comentarios ({comments.length})</h4>
            {comments.map(c => (
              <div key={c.id} style={{ padding: '10px 0', borderBottom: '1px solid var(--border-light)' }}>
                <div style={{ display: 'flex', justifyContent: 'space-between' }}>
                  <span style={{ fontWeight: 600, fontSize: '0.85rem' }}>{c.user?.first_name} {c.user?.last_name}</span>
                  <span style={{ fontSize: '0.75rem', color: 'var(--text-muted)' }}>{new Date(c.created_at).toLocaleString('es-PE')}</span>
                </div>
                <p style={{ fontSize: '0.85rem', marginTop: 4 }}>{c.content}</p>
              </div>
            ))}
            
            {showDetail.status === 'PUBLISHED' || showDetail.status === 'FEATURED' ? (
              <div style={{ marginTop: 16 }}>
                {commentError && <div className="alert alert-error" style={{ padding: '8px', marginBottom: 8, fontSize: '0.8rem' }}>{commentError}</div>}
                <div style={{ display: 'flex', gap: 8 }}>
                  <input className="form-control" placeholder="Escribe un comentario..." value={newComment} onChange={e => setNewComment(e.target.value)}
                    onKeyDown={e => { if (e.key === 'Enter') handleAddComment(); }} />
                  <button className="btn btn--primary btn--icon" onClick={handleAddComment} disabled={!newComment.trim()}><Send size={18} /></button>
                </div>
              </div>
            ) : (
              <div style={{ marginTop: 16, padding: 12, background: 'var(--surface-hover)', borderRadius: 6, fontSize: '0.85rem', color: 'var(--text-muted)', textAlign: 'center' }}>
                Los comentarios están desactivados para esta publicación.
              </div>
            )}
          </div>
        </div>)}
      </Modal>

      {/* Create */}
      <Modal isOpen={showForm} onClose={() => setShowForm(false)} title="Nueva Publicación" size="md">
        <form onSubmit={handleSubmit}>
          <div className="form-group"><label>Tipo *</label><select className="form-control" value={form.post_type_id} onChange={e => setForm(f => ({ ...f, post_type_id: e.target.value }))} required><option value="">Seleccionar...</option>{postTypes.map(t => <option key={t.id} value={t.id}>{t.name}</option>)}</select></div>
          <div className="form-group"><label>Título *</label><input className="form-control" value={form.title} onChange={e => setForm(f => ({ ...f, title: e.target.value }))} required /></div>
          <div className="form-group">
            <label>Foto (Opcional)</label>
            <input type="file" className="form-control" accept="image/*" onChange={handleFileUpload} />
            {form.photo_url && <img src={form.photo_url} alt="Preview" style={{ marginTop: 8, height: 100, borderRadius: 4, objectFit: 'cover' }} />}
          </div>
          <div className="form-group"><label>Contenido *</label><textarea className="form-control" rows={5} value={form.content} onChange={e => setForm(f => ({ ...f, content: e.target.value }))} required /></div>
          <div style={{ display: 'flex', gap: 12, justifyContent: 'flex-end', paddingTop: 8 }}>
            <button type="button" className="btn btn--secondary" onClick={() => setShowForm(false)}>Cancelar</button>
            <button type="submit" className="btn btn--primary" disabled={saving}>{saving ? 'Publicando...' : 'Publicar'}</button>
          </div>
        </form>
      </Modal>
    </div>
  );
}
