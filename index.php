<!DOCTYPE html>
<html lang="vi" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, viewport-fit=cover">
    <title>Khám Phá Nhân Vật - Character Explorer</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --bg-primary: #0a0a0a;
            --bg-secondary: #141414;
            --bg-card: #1a1a1a;
            --bg-hover: #252525;
            --text-primary: #ffffff;
            --text-secondary: #9ca3af;
            --text-tertiary: #6b7280;
            --accent: #8b5cf6;
            --accent-hover: #7c3aed;
            --accent-light: rgba(139, 92, 246, 0.1);
            --accent-pink: #ec4899;
            --accent-rose: #f43f5e;
            --border: rgba(255, 255, 255, 0.08);
            --border-hover: rgba(139, 92, 246, 0.4);
            --shadow: rgba(0, 0, 0, 0.5);
            --shadow-accent: rgba(139, 92, 246, 0.2);
            --gradient-primary: linear-gradient(135deg, #8b5cf6 0%, #ec4899 100%);
            --gradient-hover: linear-gradient(135deg, #7c3aed 0%, #db2777 100%);
            --gradient-card: linear-gradient(135deg, rgba(139, 92, 246, 0.15) 0%, rgba(236, 72, 153, 0.15) 100%);
            --glow-purple: rgba(139, 92, 246, 0.4);
            --glow-pink: rgba(236, 72, 153, 0.4);
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', 'Cantarell', sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            line-height: 1.6;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* Header */
        .header {
            position: sticky;
            top: 0;
            background: rgba(10, 10, 10, 0.98);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border);
            padding: 1rem 2rem;
            z-index: 100;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 2rem;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.3), 0 0 40px rgba(139, 92, 246, 0.05);
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 800;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: -0.02em;
            animation: gradientShift 8s ease infinite;
            background-size: 200% 200%;
        }

        @keyframes gradientShift {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        .search-container {
            flex: 1;
            max-width: 600px;
            position: relative;
        }

        .search-input {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 2.5rem;
            background: var(--bg-secondary);
            border: 1px solid var(--border);
            border-radius: 14px;
            color: var(--text-primary);
            font-size: 0.95rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .search-input:hover {
            border-color: rgba(139, 92, 246, 0.3);
        }

        .search-input:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.15), 0 0 20px rgba(139, 92, 246, 0.1);
            background: rgba(20, 20, 20, 0.8);
        }

        .search-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
        }

        /* Filter Bar */
        .filter-bar {
            padding: 1.25rem 2rem;
            background: var(--bg-secondary);
            border-bottom: 1px solid var(--border);
            display: flex;
            gap: 1.5rem;
            flex-wrap: wrap;
            align-items: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
        }

        .filter-group {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        .filter-label {
            color: var(--text-secondary);
            font-size: 0.9rem;
            font-weight: 600;
            letter-spacing: 0.02em;
        }

        .filter-btn {
            padding: 0.5rem 1rem;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 10px;
            color: var(--text-primary);
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-size: 0.875rem;
            font-weight: 500;
            position: relative;
            overflow: hidden;
        }

        .filter-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: var(--gradient-card);
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: -1;
        }

        .filter-btn:hover::before {
            opacity: 1;
        }

        .filter-btn:hover {
            background: var(--bg-hover);
            border-color: var(--border-hover);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }

        .filter-btn.active {
            background: var(--gradient-primary);
            border-color: transparent;
            color: white;
            box-shadow: 0 4px 16px var(--glow-purple), 0 0 30px rgba(139, 92, 246, 0.2);
            font-weight: 600;
        }

        .filter-btn.active::before {
            opacity: 0;
        }

        /* Tag Suggestions */
        .tag-suggestions {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-top: none;
            border-radius: 0 0 8px 8px;
            max-height: 300px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
            box-shadow: 0 4px 12px var(--shadow);
        }

        .tag-suggestions.show {
            display: block;
        }

        .tag-suggestion-item {
            padding: 0.75rem 1rem;
            cursor: pointer;
            transition: background 0.2s ease;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .tag-suggestion-item:hover {
            background: var(--bg-hover);
        }

        .tag-suggestion-name {
            color: var(--text-primary);
            font-size: 0.9rem;
        }

        .tag-suggestion-count {
            color: var(--text-secondary);
            font-size: 0.8rem;
        }

        .tag-input-container {
            position: relative;
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        /* Main Content */
        .container {
            max-width: 1600px;
            margin: 0 auto;
            padding: 2.5rem 2rem;
        }

        @media (min-width: 1920px) {
            .container {
                max-width: 1800px;
            }
        }

        .section-title {
            font-size: 1.75rem;
            font-weight: 800;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: -0.02em;
        }

        .section-subtitle {
            color: var(--text-secondary);
            font-size: 0.95rem;
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        /* Grid Layout */
        .character-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 1.25rem;
            margin-bottom: 3rem;
        }

        @media (min-width: 1400px) {
            .character-grid {
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            }
        }

        /* Character Card */
        .character-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 18px;
            overflow: hidden;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            height: 100%;
            display: flex;
            flex-direction: column;
            animation: fadeInUp 0.4s ease-out forwards;
            opacity: 0;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .character-card::before {
            content: '';
            position: absolute;
            inset: -2px;
            border-radius: 18px;
            padding: 2px;
            background: var(--gradient-primary);
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            opacity: 0;
            transition: opacity 0.3s ease;
            pointer-events: none;
            z-index: -1;
        }

        .character-card:hover::before {
            opacity: 0.6;
        }

        .character-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 
                0 20px 40px rgba(0, 0, 0, 0.4),
                0 0 60px var(--glow-purple),
                0 0 30px var(--glow-pink);
            border-color: rgba(139, 92, 246, 0.3);
        }

        .character-image {
            width: 100%;
            aspect-ratio: 3/4;
            object-fit: cover;
            background: linear-gradient(135deg, #1a1a1a, #252525);
            transition: opacity 0.3s ease, transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .character-card:hover .character-image {
            transform: scale(1.05);
        }

        .character-media-container {
            position: relative;
            width: 100%;
            aspect-ratio: 3/4;
            overflow: hidden;
            background: linear-gradient(135deg, #1a1a1a, #252525);
            border-radius: 18px 18px 0 0;
        }

        .character-media-container::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, transparent 0%, rgba(0,0,0,0.3) 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
            pointer-events: none;
        }

        .character-card:hover .character-media-container::after {
            opacity: 1;
        }

        .character-media-container .character-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }

        .media-counter {
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            background: rgba(0, 0, 0, 0.75);
            backdrop-filter: blur(10px);
            padding: 0.3rem 0.6rem;
            border-radius: 6px;
            font-size: 0.7rem;
            color: white;
            font-weight: 600;
            z-index: 2;
            display: flex;
            gap: 0.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
        }

        .media-counter-item {
            display: flex;
            align-items: center;
            gap: 0.35rem;
        }

        .media-counter-item svg {
            display: block;
            flex-shrink: 0;
        }

        .media-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            opacity: 0;
            transition: all 0.3s ease;
            z-index: 3;
        }

        .character-card:hover .media-nav {
            opacity: 1;
        }

        .media-nav:hover {
            background: rgba(139, 92, 246, 0.8);
            border-color: var(--accent);
            transform: translateY(-50%) scale(1.1);
        }

        .media-nav:active {
            transform: translateY(-50%) scale(0.95);
        }

        .media-nav.prev {
            left: 0.5rem;
        }

        .media-nav.next {
            right: 0.5rem;
        }

        .media-nav:disabled {
            opacity: 0.3;
            cursor: not-allowed;
        }

        .character-card:hover .media-nav:disabled {
            opacity: 0.3;
        }

        .character-info {
            padding: 1rem;
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .character-name {
            font-size: 1.05rem;
            font-weight: 700;
            line-height: 1.3;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            color: var(--text-primary);
        }

        .character-desc {
            font-size: 0.8rem;
            color: var(--text-secondary);
            line-height: 1.5;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            flex: 1;
        }

        .character-tags {
            display: flex;
            flex-wrap: nowrap;
            gap: 0.35rem;
            overflow: hidden;
            white-space: nowrap;
            position: relative;
            transition: all 0.3s ease;
        }

        .character-tags:hover {
            overflow: visible;
            flex-wrap: wrap;
            z-index: 10;
            background: var(--bg-card);
            padding: 0.5rem;
            margin: 0.25rem -0.5rem;
            border-radius: 8px;
            box-shadow: 0 4px 12px var(--shadow);
            border: 1px solid var(--border);
        }

        .tag {
            padding: 0.25rem 0.7rem;
            background: linear-gradient(135deg, rgba(139, 92, 246, 0.2), rgba(236, 72, 153, 0.15));
            border: 1px solid rgba(139, 92, 246, 0.4);
            border-radius: 6px;
            font-size: 0.7rem;
            color: var(--accent);
            flex-shrink: 0;
            white-space: nowrap;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-weight: 500;
        }

        .character-tags:not(:hover) .tag {
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 120px;
        }

        .tag:hover {
            background: var(--gradient-primary);
            border-color: var(--accent-pink);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px var(--glow-purple);
        }

        .view-media-btn {
            padding: 0.4rem;
            background: var(--accent-light);
            border: 1px solid rgba(139, 92, 246, 0.3);
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s ease;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--accent);
        }

        .view-media-btn:hover {
            background: var(--accent);
            border-color: var(--accent);
            color: white;
            transform: scale(1.1);
        }

        .view-media-btn svg {
            display: block;
        }

        .character-stats {
            display: flex;
            gap: 0.75rem;
            font-size: 0.8rem;
            color: var(--text-secondary);
            padding-top: 0.5rem;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            margin-top: auto;
        }

        .stat {
            display: flex;
            align-items: center;
            gap: 0.3rem;
            font-weight: 500;
        }

        /* Loading & Empty States */
        .loading {
            text-align: center;
            padding: 4rem;
            color: var(--text-secondary);
        }

        .spinner {
            width: 48px;
            height: 48px;
            border: 4px solid var(--border);
            border-top-color: var(--accent);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 1rem;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .empty-state {
            text-align: center;
            padding: 4rem;
            color: var(--text-secondary);
        }

        .empty-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.5rem;
            margin-top: 3rem;
            padding: 1.5rem;
            background: var(--bg-secondary);
            border-radius: 16px;
            border: 1px solid var(--border);
        }

        .page-btn {
            padding: 0.6rem 1rem;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 10px;
            color: var(--text-primary);
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-weight: 500;
            min-width: 40px;
        }

        .page-btn:hover:not(:disabled) {
            background: var(--bg-hover);
            border-color: var(--accent);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }

        .page-btn:disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }

        .page-btn.active {
            background: var(--gradient-primary);
            border-color: transparent;
            box-shadow: 0 4px 16px var(--glow-purple), 0 0 30px rgba(139, 92, 246, 0.15);
            font-weight: 700;
            color: white;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .container {
                max-width: 100%;
                padding: 2rem 1.5rem;
            }

            .character-grid {
                grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
                gap: 1rem;
            }
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                padding: 1rem;
                gap: 1rem;
            }

            .search-container {
                max-width: 100%;
            }

            .filter-bar {
                padding: 1rem;
                gap: 0.75rem;
            }

            .filter-group {
                width: 100%;
            }

            .container {
                padding: 1.5rem 1rem;
            }

            .character-grid {
                grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
                gap: 0.875rem;
            }

            .character-info {
                padding: 0.75rem;
            }

            .character-name {
                font-size: 0.95rem;
            }

            .character-desc {
                font-size: 0.8rem;
            }

            .section-title {
                font-size: 1.5rem;
            }

            .pagination {
                flex-wrap: wrap;
                gap: 0.5rem;
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="logo">Character Explorer</div>
        <div class="search-container">
            <svg class="search-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"></circle>
                <path d="m21 21-4.35-4.35"></path>
            </svg>
            <input type="text" class="search-input" id="searchInput" placeholder="Tìm kiếm nhân vật...">
        </div>
    </header>

    <!-- Filter Bar -->
    <div class="filter-bar">
        <div class="filter-group">
            <span class="filter-label">Sắp xếp:</span>
            <button class="filter-btn" data-sort="created_at">🕒 Ngày tạo</button>
            <button class="filter-btn" data-sort="name">📝 Tên</button>
            <button class="filter-btn" data-sort="like_count">❤️ Yêu thích</button>
            <button class="filter-btn active" data-sort="message_count">💬 Tin nhắn</button>
            <button class="filter-btn" data-sort="age">👤 Tuổi</button>
        </div>
        <div class="filter-group">
            <span class="filter-label">Thứ tự:</span>
            <button class="filter-btn active" data-order="desc" id="orderDesc">⬇ Giảm dần</button>
            <button class="filter-btn" data-order="asc" id="orderAsc">⬆ Tăng dần</button>
        </div>
        <div class="filter-group">
            <span class="filter-label">Giới tính:</span>
            <button class="filter-btn" data-gender="">Tất cả</button>
            <button class="filter-btn" data-gender="Male">Nam</button>
            <button class="filter-btn active" data-gender="Female">Nữ</button>
            <button class="filter-btn" data-gender="Non-binary">Khác</button>
        </div>
        <div class="filter-group">
            <span class="filter-label">Phong cách:</span>
            <button class="filter-btn" data-style="">Tất cả</button>
            <button class="filter-btn active" data-style="Realistic">Realistic</button>
            <button class="filter-btn" data-style="Anime">Anime</button>
        </div>
        <div class="filter-group">
            <span class="filter-label">Tag:</span>
            <div class="tag-input-container">
                <div style="position: relative; min-width: 200px;">
                    <input type="text" id="tagInput" placeholder="Nhập tag..." style="padding: 0.5rem 1rem; background: var(--bg-card); border: 1px solid var(--border); border-radius: 8px; color: var(--text-primary); font-size: 0.9rem; width: 100%;" autocomplete="off">
                    <div id="tagSuggestions" class="tag-suggestions"></div>
                </div>
                <button class="filter-btn" id="clearTagBtn" style="display: none;">✕ Xóa tag</button>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <main class="container">
        <h1 class="section-title">
            <span>🎭</span>
            <span>Khám Phá Nhân Vật</span>
        </h1>
        <p class="section-subtitle">Tìm hiểu và tương tác với hàng ngàn nhân vật đa dạng</p>

        <!-- Loading State -->
        <div id="loadingState" class="loading" style="display: none;">
            <div class="spinner"></div>
            <p>Đang tải nhân vật...</p>
        </div>

        <!-- Empty State -->
        <div id="emptyState" class="empty-state" style="display: none;">
            <div class="empty-icon">🔍</div>
            <h3>Không tìm thấy nhân vật</h3>
            <p>Thử thay đổi bộ lọc hoặc từ khóa tìm kiếm</p>
        </div>

        <!-- Character Grid -->
        <div id="characterGrid" class="character-grid"></div>

        <!-- Pagination -->
        <div id="pagination" class="pagination" style="display: none;"></div>
    </main>

    <script>
        // API Configuration
        const API_BASE = '/character/api.php';
        
        // State
        let currentPage = 1;
        let currentSort = 'message_count';
        let currentSortOrder = 'DESC';
        let currentGender = 'Female';
        let currentStyle = 'Realistic';
        let currentTag = '';
        let searchQuery = '';
        let searchTimeout = null;
        let tagTimeout = null;
        let allTags = [];
        let tagSuggestionsVisible = false;

        // Initialize
        document.addEventListener('DOMContentLoaded', () => {
            loadCharacters();
            loadTags();
            setupEventListeners();
        });

        // Setup Event Listeners
        function setupEventListeners() {
            // Search input
            document.getElementById('searchInput').addEventListener('input', (e) => {
                clearTimeout(searchTimeout);
                searchQuery = e.target.value.trim();
                searchTimeout = setTimeout(() => {
                    currentPage = 1;
                    loadCharacters();
                }, 500);
            });

            // Sort buttons
            document.querySelectorAll('[data-sort]').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    document.querySelectorAll('[data-sort]').forEach(b => b.classList.remove('active'));
                    e.target.classList.add('active');
                    currentSort = e.target.dataset.sort;
                    currentPage = 1;
                    loadCharacters();
                });
            });

            // Sort order buttons
            document.querySelectorAll('[data-order]').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    document.querySelectorAll('[data-order]').forEach(b => b.classList.remove('active'));
                    e.target.classList.add('active');
                    currentSortOrder = e.target.dataset.order.toUpperCase();
                    currentPage = 1;
                    loadCharacters();
                });
            });

            // Gender filter buttons
            document.querySelectorAll('[data-gender]').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    document.querySelectorAll('[data-gender]').forEach(b => b.classList.remove('active'));
                    e.target.classList.add('active');
                    currentGender = e.target.dataset.gender;
                    currentPage = 1;
                    loadCharacters();
                });
            });

            // Style filter buttons
            document.querySelectorAll('[data-style]').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    document.querySelectorAll('[data-style]').forEach(b => b.classList.remove('active'));
                    e.target.classList.add('active');
                    currentStyle = e.target.dataset.style;
                    currentPage = 1;
                    loadCharacters();
                });
            });

            // Tag input
            const tagInput = document.getElementById('tagInput');
            const tagSuggestions = document.getElementById('tagSuggestions');
            const clearTagBtn = document.getElementById('clearTagBtn');
            
            tagInput.addEventListener('input', (e) => {
                const value = e.target.value.trim();
                clearTagBtn.style.display = value ? 'inline-block' : 'none';
                
                // Show suggestions
                if (value.length > 0) {
                    showTagSuggestions(value);
                } else {
                    hideTagSuggestions();
                }
                
                // Debounce search
                clearTimeout(tagTimeout);
                tagTimeout = setTimeout(() => {
                    currentTag = value;
                    currentPage = 1;
                    loadCharacters();
                }, 500);
            });

            tagInput.addEventListener('focus', (e) => {
                const value = e.target.value.trim();
                if (value.length > 0) {
                    showTagSuggestions(value);
                }
            });

            tagInput.addEventListener('blur', () => {
                // Delay to allow click on suggestion
                setTimeout(hideTagSuggestions, 200);
            });

            clearTagBtn.addEventListener('click', () => {
                tagInput.value = '';
                currentTag = '';
                currentPage = 1;
                clearTagBtn.style.display = 'none';
                hideTagSuggestions();
                loadCharacters();
            });

            // Close suggestions when clicking outside
            document.addEventListener('click', (e) => {
                if (!tagInput.contains(e.target) && !tagSuggestions.contains(e.target)) {
                    hideTagSuggestions();
                }
            });
        }

        // Load all tags
        async function loadTags() {
            try {
                const response = await fetch('/character/api.php?action=tags&limit=200');
                const result = await response.json();
                if (result.success && result.data) {
                    allTags = result.data;
                }
            } catch (error) {
                console.error('Error loading tags:', error);
            }
        }

        // Show tag suggestions
        function showTagSuggestions(query) {
            const suggestions = document.getElementById('tagSuggestions');
            const lowerQuery = query.toLowerCase();
            
            const filtered = allTags.filter(item => 
                item.tag.toLowerCase().includes(lowerQuery)
            ).slice(0, 10);
            
            if (filtered.length === 0) {
                hideTagSuggestions();
                return;
            }
            
            suggestions.innerHTML = filtered.map(item => `
                <div class="tag-suggestion-item" onclick="selectTag('${escapeHtml(item.tag)}')">
                    <span class="tag-suggestion-name">${escapeHtml(item.tag)}</span>
                    <span class="tag-suggestion-count">${item.count}</span>
                </div>
            `).join('');
            
            suggestions.classList.add('show');
            tagSuggestionsVisible = true;
        }

        // Hide tag suggestions
        function hideTagSuggestions() {
            const suggestions = document.getElementById('tagSuggestions');
            suggestions.classList.remove('show');
            tagSuggestionsVisible = false;
        }

        // Select tag from suggestions
        function selectTag(tag) {
            const tagInput = document.getElementById('tagInput');
            const clearTagBtn = document.getElementById('clearTagBtn');
            
            tagInput.value = tag;
            currentTag = tag;
            currentPage = 1;
            clearTagBtn.style.display = 'inline-block';
            hideTagSuggestions();
            loadCharacters();
        }

        // Load Characters
        async function loadCharacters() {
            showLoading(true);
            
            const params = new URLSearchParams({
                action: 'list',
                page: currentPage,
                limit: 20,
                sort_by: currentSort,
                sort_order: currentSortOrder
            });

            if (searchQuery) {
                params.append('search', searchQuery);
            }
            if (currentGender) {
                params.append('gender', currentGender);
            }
            if (currentStyle) {
                params.append('style', currentStyle);
            }
            if (currentTag) {
                params.append('tag', currentTag);
            }

            try {
                const response = await fetch(`${API_BASE}?${params}`);
                const result = await response.json();

                if (result.success && result.data) {
                    displayCharacters(result.data);
                    displayPagination(result.pagination);
                    
                    if (result.data.length === 0) {
                        showEmptyState(true);
                    } else {
                        showEmptyState(false);
                    }
                } else {
                    showEmptyState(true);
                    displayPagination(null);
                }
            } catch (error) {
                console.error('Error loading characters:', error);
                showEmptyState(true);
                displayPagination(null);
            } finally {
                showLoading(false);
            }
        }

        // Display Characters
        function displayCharacters(characters) {
            const grid = document.getElementById('characterGrid');
            grid.innerHTML = '';

            characters.forEach(char => {
                const card = createCharacterCard(char);
                grid.appendChild(card);
            });
        }

        // Create Character Card
        function createCharacterCard(char) {
            const card = document.createElement('div');
            card.className = 'character-card';
            card.onclick = () => viewCharacter(char.id);

            // Separate images and videos
            const imageUrls = [];
            const videoUrls = [];
            
            if (char.display_image_urls && char.display_image_urls.length > 0) {
                for (const url of char.display_image_urls) {
                    // Check by domain first
                    if (url.includes('cdn.ourdream.ai/gen/')) {
                        imageUrls.push(url);
                    } else if (url.includes('vid.ourdream.ai/')) {
                        videoUrls.push(url);
                    } else {
                        // Check by file extension
                        const lowerUrl = url.toLowerCase();
                        if (lowerUrl.match(/\.(mp4|webm|mov|avi|mkv)(\?|$)/)) {
                            videoUrls.push(url);
                        } else if (lowerUrl.match(/\.(jpg|jpeg|png|gif|webp|bmp|svg)(\?|$)/)) {
                            imageUrls.push(url);
                        } else {
                            // Default to image if can't determine
                            imageUrls.push(url);
                        }
                    }
                }
            }

            // Use first image, or first video if no images
            const hasImages = imageUrls.length > 0;
            const displayUrl = hasImages ? imageUrls[0] : (videoUrls.length > 0 ? videoUrls[0] : 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="100" height="100"%3E%3Crect fill="%231a1a1a" width="100" height="100"/%3E%3C/svg%3E');

            const tags = char.tags || [];
            // Show more tags but they will be cut off nicely with ellipsis
            const tagsHtml = tags.slice(0, 10).map(tag => `<span class="tag" title="${escapeHtml(tag)}">${escapeHtml(tag)}</span>`).join('');

            const messageCount = formatNumber(char.message_count || 0);
            const likeCount = formatNumber(char.like_count || 0);

            // Count images and videos
            const imageCount = imageUrls.length;
            const videoCount = videoUrls.length;
            const totalMedia = imageCount + videoCount;
            
            // Media counter HTML
            const counterHtml = totalMedia > 1 ? `
                <div class="media-counter">
                    ${imageCount > 0 ? `<span class="media-counter-item"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg> ${imageCount}</span>` : ''}
                    ${videoCount > 0 ? `<span class="media-counter-item"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2"/></svg> ${videoCount}</span>` : ''}
                </div>
            ` : '';

            // Check if current URL is a video
            const isVideo = displayUrl.includes('vid.ourdream.ai/');
            const mediaHtml = isVideo 
                ? `<div class="character-media-container">
                    <video class="character-image" autoplay loop muted playsinline><source src="${escapeHtml(displayUrl)}" type="video/mp4"></video>
                    ${counterHtml}
                    ${totalMedia > 1 ? '<button class="media-nav prev" disabled>←</button><button class="media-nav next">→</button>' : ''}
                   </div>`
                : `<div class="character-media-container">
                    <img class="character-image" src="${escapeHtml(displayUrl)}" alt="${escapeHtml(char.name)}" loading="lazy" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22100%22 height=%22100%22%3E%3Crect fill=%22%231a1a1a%22 width=%22100%22 height=%22100%22/%3E%3C/svg%3E'">
                    ${counterHtml}
                    ${totalMedia > 1 ? '<button class="media-nav prev" disabled>←</button><button class="media-nav next">→</button>' : ''}
                   </div>`;

            // Additional info
            const ageText = char.age ? `👤 ${char.age} tuổi` : '';
            const idText = char.display_id ? `🆔 ${char.display_id}` : '';
            
            // Build additional attributes string
            let additionalAttrs = [];
            if (char.ethnicity) additionalAttrs.push(`🌍 ${char.ethnicity}`);
            if (char.body_type) additionalAttrs.push(`💪 ${char.body_type}`);
            if (char.hair_color) additionalAttrs.push(`💇 ${char.hair_color}`);
            if (char.eye_color) additionalAttrs.push(`👁️ ${char.eye_color}`);
            const attrsText = additionalAttrs.length > 0 ? additionalAttrs.join(' • ') : '';

            card.innerHTML = `
                ${mediaHtml}
                <div class="character-info">
                    <div style="display: flex; justify-content: space-between; align-items: start; gap: 0.5rem;">
                        <h3 class="character-name" style="flex: 1;">${escapeHtml(char.name)}</h3>
                        <div style="display: flex; gap: 0.35rem;">
                            <button class="view-media-btn" onclick="event.stopPropagation(); window.location.href='/character/images.php?id=${char.id}'" title="Xem ảnh">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                            </button>
                            <button class="view-media-btn" onclick="event.stopPropagation(); window.location.href='/videos.php?id=${char.id}'" title="Xem video">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>
                            </button>
                        </div>
                    </div>
                    <div class="character-stats">
                        <span class="stat">💬 ${messageCount}</span>
                        <span class="stat">❤️ ${likeCount}</span>
                        ${ageText ? `<span class="stat">${ageText}</span>` : ''}
                    </div>
                    ${idText ? `<div style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 0.5rem;">${idText}</div>` : ''}
                    ${attrsText ? `<div style="font-size: 0.7rem; color: var(--text-tertiary); margin-top: 0.5rem; line-height: 1.4;">${attrsText}</div>` : ''}
                    ${tags.length > 0 ? `<div class="character-tags" style="margin-top: 0.75rem;">${tagsHtml}</div>` : ''}
                    <p class="character-desc" style="margin-top: 0.75rem;">${escapeHtml(char.short_description || char.description || 'Chưa có mô tả')}</p>
                </div>
            `;

            // Add navigation button functionality
            if (totalMedia > 1) {
                const allMedia = [...imageUrls, ...videoUrls];
                let currentIndex = 0;
                const mediaContainer = card.querySelector('.character-media-container');
                const prevBtn = card.querySelector('.media-nav.prev');
                const nextBtn = card.querySelector('.media-nav.next');

                const updateMedia = () => {
                    const newUrl = allMedia[currentIndex];
                    const isNewVideo = newUrl.includes('vid.ourdream.ai/');
                    const currentMedia = mediaContainer.querySelector('.character-image');
                    
                    currentMedia.style.opacity = '0';
                    
                    setTimeout(() => {
                        if (isNewVideo) {
                            const video = document.createElement('video');
                            video.className = 'character-image';
                            video.autoplay = true;
                            video.loop = true;
                            video.muted = true;
                            video.playsInline = true;
                            const source = document.createElement('source');
                            source.src = newUrl;
                            source.type = 'video/mp4';
                            video.appendChild(source);
                            currentMedia.replaceWith(video);
                            setTimeout(() => video.style.opacity = '1', 50);
                        } else {
                            if (currentMedia.tagName === 'VIDEO') {
                                const img = document.createElement('img');
                                img.className = 'character-image';
                                img.src = newUrl;
                                img.alt = char.name;
                                img.loading = 'lazy';
                                currentMedia.replaceWith(img);
                                setTimeout(() => img.style.opacity = '1', 50);
                            } else {
                                currentMedia.src = newUrl;
                                setTimeout(() => currentMedia.style.opacity = '1', 50);
                            }
                        }
                    }, 150);

                    // Update button states
                    prevBtn.disabled = currentIndex === 0;
                    nextBtn.disabled = currentIndex === allMedia.length - 1;
                };

                prevBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    if (currentIndex > 0) {
                        currentIndex--;
                        updateMedia();
                    }
                });

                nextBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    if (currentIndex < allMedia.length - 1) {
                        currentIndex++;
                        updateMedia();
                    }
                });
            }

            return card;
        }

        // Display Pagination
        function displayPagination(pagination) {
            const paginationEl = document.getElementById('pagination');
            
            if (!pagination) {
                paginationEl.style.display = 'none';
                return;
            }

            paginationEl.style.display = 'flex';
            paginationEl.innerHTML = '';

            // Show page info
            const pageInfo = document.createElement('div');
            pageInfo.style.cssText = 'color: var(--text-secondary); font-size: 0.9rem; margin-right: auto;';
            pageInfo.textContent = `Trang ${pagination.current_page} / ${pagination.total_pages} (${pagination.total} nhân vật)`;
            paginationEl.appendChild(pageInfo);

            if (pagination.total_pages <= 1) {
                return;
            }

            // Previous button
            const prevBtn = document.createElement('button');
            prevBtn.className = 'page-btn';
            prevBtn.textContent = '← Trước';
            prevBtn.disabled = pagination.current_page === 1;
            prevBtn.onclick = () => {
                currentPage = pagination.current_page - 1;
                loadCharacters();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            };
            paginationEl.appendChild(prevBtn);

            // Page numbers (show max 5 pages)
            const maxPages = 5;
            let startPage = Math.max(1, pagination.current_page - Math.floor(maxPages / 2));
            let endPage = Math.min(pagination.total_pages, startPage + maxPages - 1);
            
            if (endPage - startPage < maxPages - 1) {
                startPage = Math.max(1, endPage - maxPages + 1);
            }

            for (let i = startPage; i <= endPage; i++) {
                const pageBtn = document.createElement('button');
                pageBtn.className = 'page-btn' + (i === pagination.current_page ? ' active' : '');
                pageBtn.textContent = i;
                pageBtn.onclick = () => {
                    currentPage = i;
                    loadCharacters();
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                };
                paginationEl.appendChild(pageBtn);
            }

            // Next button
            const nextBtn = document.createElement('button');
            nextBtn.className = 'page-btn';
            nextBtn.textContent = 'Sau →';
            nextBtn.disabled = !pagination.has_more;
            nextBtn.onclick = () => {
                currentPage = pagination.current_page + 1;
                loadCharacters();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            };
            paginationEl.appendChild(nextBtn);
        }

        // View Character Details
        function viewCharacter(id) {
            window.location.href = `/character/get.php?id=${id}`;
        }

        // Utility Functions
        function showLoading(show) {
            document.getElementById('loadingState').style.display = show ? 'block' : 'none';
            document.getElementById('characterGrid').style.display = show ? 'none' : 'grid';
        }

        function showEmptyState(show) {
            document.getElementById('emptyState').style.display = show ? 'block' : 'none';
            document.getElementById('characterGrid').style.display = show ? 'none' : 'grid';
            // Don't hide pagination in empty state - let displayPagination handle it
        }

        function formatNumber(num) {
            if (num >= 1000000) return (num / 1000000).toFixed(1) + 'M';
            if (num >= 1000) return (num / 1000).toFixed(1) + 'K';
            return num.toString();
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    </script>
</body>
</html>
