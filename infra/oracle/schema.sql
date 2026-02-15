-- Oracle Community Schema
CREATE TABLE users (
  id NUMBER PRIMARY KEY,
  email VARCHAR2(255) NOT NULL UNIQUE,
  password_hash VARCHAR2(255) NOT NULL,
  display_name VARCHAR2(100) NOT NULL,
  created_at TIMESTAMP DEFAULT SYSTIMESTAMP NOT NULL
);

CREATE TABLE topics (
  id NUMBER PRIMARY KEY,
  code VARCHAR2(50) NOT NULL UNIQUE,
  name VARCHAR2(100) NOT NULL,
  description VARCHAR2(500),
  sort_order NUMBER DEFAULT 100 NOT NULL,
  is_active NUMBER(1) DEFAULT 1 NOT NULL,
  created_at TIMESTAMP DEFAULT SYSTIMESTAMP NOT NULL
);

CREATE TABLE posts (
  id NUMBER PRIMARY KEY,
  topic_id NUMBER NOT NULL REFERENCES topics(id),
  user_id NUMBER NOT NULL REFERENCES users(id),
  title VARCHAR2(200) NOT NULL,
  content CLOB NOT NULL,
  created_at TIMESTAMP DEFAULT SYSTIMESTAMP NOT NULL
);

CREATE TABLE attachments (
  id NUMBER PRIMARY KEY,
  post_id NUMBER NOT NULL REFERENCES posts(id) ON DELETE CASCADE,
  file_name VARCHAR2(255) NOT NULL,
  stored_name VARCHAR2(255) NOT NULL,
  file_size NUMBER NOT NULL,
  mime_type VARCHAR2(100),
  created_at TIMESTAMP DEFAULT SYSTIMESTAMP NOT NULL
);

CREATE TABLE comments (
  id NUMBER PRIMARY KEY,
  post_id NUMBER NOT NULL REFERENCES posts(id) ON DELETE CASCADE,
  user_id NUMBER NOT NULL REFERENCES users(id),
  content VARCHAR2(2000) NOT NULL,
  created_at TIMESTAMP DEFAULT SYSTIMESTAMP NOT NULL
);

CREATE TABLE post_likes (
  id NUMBER PRIMARY KEY,
  post_id NUMBER NOT NULL REFERENCES posts(id) ON DELETE CASCADE,
  user_id NUMBER NOT NULL REFERENCES users(id),
  created_at TIMESTAMP DEFAULT SYSTIMESTAMP NOT NULL,
  CONSTRAINT uq_post_likes_post_user UNIQUE (post_id, user_id)
);

CREATE SEQUENCE users_seq START WITH 1 INCREMENT BY 1;
CREATE SEQUENCE topics_seq START WITH 1 INCREMENT BY 1;
CREATE SEQUENCE posts_seq START WITH 1 INCREMENT BY 1;
CREATE SEQUENCE attachments_seq START WITH 1 INCREMENT BY 1;
CREATE SEQUENCE comments_seq START WITH 1 INCREMENT BY 1;
CREATE SEQUENCE post_likes_seq START WITH 1 INCREMENT BY 1;

CREATE INDEX idx_posts_topic_created ON posts(topic_id, created_at DESC);
CREATE INDEX idx_attachments_post_id ON attachments(post_id);
CREATE INDEX idx_comments_post_created ON comments(post_id, created_at DESC);
CREATE INDEX idx_post_likes_post_id ON post_likes(post_id);

INSERT INTO users (id, email, password_hash, display_name)
VALUES (users_seq.NEXTVAL, 'admin@example.com', '$2y$10$qf9qAKr84rKQ7i3F20z6MutKD4x5x0Fgx4nR2DP6PAHyBs64K6l1m', '관리자');
-- plain password: password123

INSERT INTO topics (id, code, name, description, sort_order, is_active)
VALUES (topics_seq.NEXTVAL, 'ai-practical', 'AI 실무활용', 'AI를 실무에 적용하는 노하우를 공유하는 게시판', 1, 1);

COMMIT;
