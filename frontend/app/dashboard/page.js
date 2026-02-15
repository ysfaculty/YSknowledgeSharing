'use client';

import { useEffect, useState } from 'react';
import Link from 'next/link';
import { api } from '../../lib/api';

export default function DashboardPage() {
  const [topics, setTopics] = useState([]);
  const [user, setUser] = useState(null);
  const [error, setError] = useState('');

  useEffect(() => {
    const raw = localStorage.getItem('user');
    if (raw) setUser(JSON.parse(raw));

    api('/topics')
      .then((d) => setTopics(d.topics || []))
      .catch((e) => setError(e.message));
  }, []);

  return (
    <main className="container">
      <div className="card">
        <div className="row" style={{ justifyContent: 'space-between' }}>
          <h2>대시보드</h2>
          <span className="meta">{user ? `${user.display_name}님 환영합니다` : ''}</span>
        </div>
        <p>원하는 주제를 선택하세요.</p>
      </div>

      {error && <p style={{ color: 'crimson' }}>{error}</p>}
      {topics.map((topic) => (
        <div className="card" key={topic.id}>
          <span className="tag">{topic.name}</span>
          <p>{topic.description}</p>
          <Link href={`/topics/${topic.code}`}>
            <button>게시판 열기</button>
          </Link>
        </div>
      ))}
    </main>
  );
}
