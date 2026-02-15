'use client';

import { useEffect, useState } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import { api } from '../../lib/api';

export default function DashboardPage() {
  const router = useRouter();
  const [topics, setTopics] = useState([]);
  const [user, setUser] = useState(null);
  const [error, setError] = useState('');
  const [selectedTopicCode, setSelectedTopicCode] = useState('');
  const [searchKeyword, setSearchKeyword] = useState('');

  useEffect(() => {
    const raw = localStorage.getItem('user');
    if (raw) setUser(JSON.parse(raw));

    api('/topics')
      .then((d) => {
        const loadedTopics = d.topics || [];
        setTopics(loadedTopics);
        if (loadedTopics.length > 0) {
          setSelectedTopicCode(loadedTopics[0].code);
        }
      })
      .catch((e) => setError(e.message));
  }, []);

  const filteredTopics = topics.filter((topic) => {
    const keyword = searchKeyword.trim().toLowerCase();
    if (!keyword) return true;

    return [topic.name, topic.description, topic.code]
      .filter(Boolean)
      .some((value) => value.toLowerCase().includes(keyword));
  });

  const onSearch = (e) => {
    e.preventDefault();
    if (!selectedTopicCode) {
      setError('선택된 주제가 없습니다.');
      return;
    }
    setError('');
    router.push(`/topics/${selectedTopicCode}`);
  };

  return (
    <main className="container">
      <div className="card">
        <div className="row" style={{ justifyContent: 'space-between' }}>
          <h2>대시보드</h2>
          <span className="meta">{user ? `${user.display_name}님 환영합니다` : ''}</span>
        </div>
        <p>원하는 주제를 선택하고 검색하세요.</p>
        <form onSubmit={onSearch}>
          <label>주제 선택</label>
          <select
            value={selectedTopicCode}
            onChange={(e) => setSelectedTopicCode(e.target.value)}
            disabled={topics.length === 0}
          >
            {!topics.length && <option value="">주제가 없습니다</option>}
            {topics.map((topic) => (
              <option key={topic.id} value={topic.code}>{topic.name}</option>
            ))}
          </select>

          <label>주제 검색</label>
          <input
            placeholder="주제명/설명/코드로 검색"
            value={searchKeyword}
            onChange={(e) => setSearchKeyword(e.target.value)}
          />
          <button type="submit" disabled={!selectedTopicCode}>검색</button>
        </form>
      </div>

      {error && <p style={{ color: 'crimson' }}>{error}</p>}
      {filteredTopics.map((topic) => (
        <div className="card" key={topic.id}>
          <span className="tag">{topic.name}</span>
          <p>{topic.description}</p>
          <Link href={`/topics/${topic.code}`}>
            <button>게시판 열기</button>
          </Link>
        </div>
      ))}
      {!filteredTopics.length && !error && (
        <div className="card">
          <p>검색 결과가 없습니다.</p>
        </div>
      )}
    </main>
  );
}
