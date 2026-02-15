import './globals.css';

export const metadata = {
  title: '커뮤니티 시스템',
  description: '확장형 주제 게시판',
};

export default function RootLayout({ children }) {
  return (
    <html lang="ko">
      <body>{children}</body>
    </html>
  );
}
