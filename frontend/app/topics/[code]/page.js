'use client';

import { useEffect, useState } from 'react';
import { api } from '../../../lib/api';

export default function TopicPage({ params }) {
  const code = params.code;
  const [topic, setTopic] = useState(null);
  const [posts, setPosts] = useState([]);
  const [title, setTitle] = useState('');
  const [content, setContent] = useState('');
  const [files, setFiles] = useState([]);
  const [commentInputs, setCommentInputs] = useState({});
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);

  const load = () => api(`/topics/${code}/posts`).then((d) => {
    setTopic(d.topic);
    setPosts(d.posts || []);
  }).catch((e) => setError(e.message));

  useEffect(() => {
    load();
  }, [code]);

  const onWrite = async (e) => {
    e.preventDefault();
    if (files.length > 3) {
      setError('첨부파일은 최대 3개까지 가능합니다.');
      return;
    }

    setLoading(true);
    setError('');

    const form = new FormData();
    form.append('title', title);
    form.append('content', content);
    [...files].forEach((f) => form.append('attachments[]', f));

    try {
      await api(`/topics/${code}/posts`, { method: 'POST', body: form });
      setTitle('');
      setContent('');
      setFiles([]);
      await load();
    } catch (err) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  };

  const onToggleLike = async (post) => {
    try {
      if (post.liked_by_me) {
        await api(`/posts/${post.id}/likes`, { method: 'DELETE' });
      } else {
        await api(`/posts/${post.id}/likes`, { method: 'POST' });
      }
      await load();
    } catch (err) {
      setError(err.message);
    }
  };

  const onCreateComment = async (postId) => {
    const text = (commentInputs[postId] || '').trim();
    if (!text) return;

    try {
      await api(`/posts/${postId}/comments`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ content: text }),
      });
      setCommentInputs((prev) => ({ ...prev, [postId]: '' }));
      await load();
    } catch (err) {
      setError(err.message);
    }
  };

  return (
    <main className="container">
      <div className="card">
        <h2>{topic?.name || '게시판'}</h2>
        <p className="meta">{topic?.description}</p>
      </div>

      <div className="card">
        <h3>글쓰기</h3>
        <form onSubmit={onWrite}>
          <label>제목</label>
          <input value={title} onChange={(e) => setTitle(e.target.value)} required />
          <label>내용</label>
          <textarea rows={6} value={content} onChange={(e) => setContent(e.target.value)} required />
          <label>첨부파일 (최대 3개)</label>
          <input type="file" multiple onChange={(e) => setFiles(e.target.files)} />
          {error && <p style={{ color: 'crimson' }}>{error}</p>}
          <button disabled={loading}>{loading ? '등록 중...' : '글쓰기'}</button>
        </form>
      </div>

      <div className="card">
        <h3>게시글</h3>
        {!posts.length && <p>아직 게시글이 없습니다.</p>}
        {posts.map((p) => (
          <article key={p.id} className="post">
            <h4>{p.title}</h4>
            <p className="meta">작성자: {p.author_name} · {new Date(p.created_at).toLocaleString()}</p>
            <p style={{ whiteSpace: 'pre-wrap' }}>{p.content}</p>
            <div className="row">
              <button type="button" onClick={() => onToggleLike(p)}>
                {p.liked_by_me ? '좋아요 취소' : '좋아요'} ({p.like_count})
              </button>
            </div>
            {(p.attachments || []).length > 0 && (
              <ul>
                {p.attachments.map((a) => (
                  <li key={a.id}>{a.file_name} ({Math.round(a.file_size / 1024)}KB)</li>
                ))}
              </ul>
            )}

            <div style={{ marginTop: 12 }}>
              <h5>댓글</h5>
              <div className="row">
                <input
                  placeholder="댓글을 입력하세요"
                  value={commentInputs[p.id] || ''}
                  onChange={(e) => setCommentInputs((prev) => ({ ...prev, [p.id]: e.target.value }))}
                />
                <button type="button" onClick={() => onCreateComment(p.id)}>등록</button>
              </div>
              {(p.comments || []).map((c) => (
                <div key={c.id} style={{ borderTop: '1px solid #eee', paddingTop: 8, marginTop: 8 }}>
                  <p className="meta">{c.author_name} · {new Date(c.created_at).toLocaleString()}</p>
                  <p>{c.content}</p>
                </div>
              ))}
            </div>
          </article>
        ))}
      </div>
    </main>
  );
}
