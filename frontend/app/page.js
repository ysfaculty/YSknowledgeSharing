'use client';

import { useState } from 'react';
import { useRouter } from 'next/navigation';

const API_URL = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8080/api';

export default function LoginPage() {
  const router = useRouter();
  const [email, setEmail] = useState('admin@example.com');
  const [password, setPassword] = useState('password123');
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);

  const onSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setError('');
    try {
      const res = await fetch(`${API_URL}/auth/login`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email, password }),
      });
      const data = await res.json();
      if (!res.ok) throw new Error(data.message || '로그인 실패');
      localStorage.setItem('token', data.token);
      localStorage.setItem('user', JSON.stringify(data.user));
      router.push('/dashboard');
    } catch (err) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  };

  return (
    <main className="container">
      <div className="card" style={{ maxWidth: 480, margin: '80px auto' }}>
        <h2>커뮤니티 로그인</h2>
        <p className="meta">Linux + Oracle + Next.js + PHP</p>
        <form onSubmit={onSubmit}>
          <label>이메일</label>
          <input value={email} onChange={(e) => setEmail(e.target.value)} />
          <label>비밀번호</label>
          <input type="password" value={password} onChange={(e) => setPassword(e.target.value)} />
          {error && <p style={{ color: 'crimson' }}>{error}</p>}
          <button disabled={loading}>{loading ? '로그인 중...' : '로그인'}</button>
        </form>
      </div>
    </main>
  );
}
