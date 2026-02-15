# 확장형 커뮤니티 시스템 (Linux + Oracle + Next.js + PHP)

로그인 후 대시보드에서 주제를 선택하고, 주제별 게시판에서 글/첨부파일(최대 3개)을 작성할 수 있는 기본 시스템입니다.

## 기술 스택
- Frontend: Next.js 14 (App Router)
- Backend: PHP 8.3 (REST API)
- Database: Oracle DB (Oracle Free)
- Infra: Docker Compose

## 핵심 요구사항 반영
- 로그인 후 기본 대시보드 표시
- 주제 선택 시 해당 주제 게시판 표시
- 글쓰기 + 첨부파일 3개 제한
- 로그인 사용자의 댓글/좋아요 기능
- 첫 기본 주제: `AI 실무활용` (`code: ai-practical`)
- 확장성 고려: `topics` 기반 멀티 보드 구조

## 빠른 실행
```bash
cp backend/.env.example backend/.env
docker compose up -d
```

- Frontend: http://localhost:3000
- Backend API: http://localhost:8080/api
- Oracle: localhost:1521 (service: FREEPDB1)

## API 요약
- `POST /api/auth/login`
- `GET /api/auth/me`
- `GET /api/topics`
- `GET /api/topics/{code}/posts`
- `POST /api/topics/{code}/posts` (multipart/form-data)
- `POST /api/posts/{postId}/comments`
- `POST /api/posts/{postId}/likes`
- `DELETE /api/posts/{postId}/likes`

## 초기 계정
- email: `admin@example.com`
- password: `password123`

## 확장성/성능 설계 포인트
- 주제 테이블 분리 + 코드 기반 라우팅으로 무한 주제 확장
- 게시글/첨부 인덱스 적용 (`idx_posts_topic_created`, `idx_attachments_post_id`)
- API 무상태(JWT)로 수평 확장 용이
- 파일 업로드 경로 분리, DB에는 메타데이터만 저장
- CORS/API 분리 구조로 프론트/백 독립 확장 가능

## 스키마 파일
- 기본 실행 스키마: `infra/oracle/schema.sql`
- 지식공유사이트 확장 설계 스키마(권한/태그/북마크/이력/모더레이션 포함): `infra/oracle/knowledge_sharing_schema.sql`
