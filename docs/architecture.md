# 아키텍처 개요

## 구성
- `frontend`: Next.js 사용자 UI (로그인, 대시보드, 주제 게시판)
- `backend`: PHP API (인증, 주제, 게시글, 첨부 업로드)
- `infra/oracle`: Oracle 스키마 및 시드 데이터

## 도메인 모델
- users
- topics
- posts
- attachments
- comments
- post_likes

`topics`를 통해 게시판 주제를 동적으로 확장하며, 첫 시드로 `AI 실무활용`을 제공합니다.

## 인증
- 로그인 성공 시 JWT 발급
- `Authorization: Bearer <token>` 기반 인증

## 업로드 정책
- 첨부파일 최대 3개
- 파일 용량 제한 (`MAX_UPLOAD_SIZE_MB`)
- 서버 로컬 스토리지 저장 + DB 메타데이터 보관
