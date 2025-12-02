<!DOCTYPE html>
<html lang="vi" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, viewport-fit=cover">
    <title>Character Images</title>
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
            --border: rgba(255, 255, 255, 0.08);
            --border-hover: rgba(139, 92, 246, 0.4);
            --shadow: rgba(0, 0, 0, 0.5);
            --shadow-accent: rgba(139, 92, 246, 0.2);
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

        .header {
            position: sticky;
            top: 0;
            background: rgba(10, 10, 10, 0.98);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border);
            padding: 1.25rem 2rem;
            z-index: 100;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.3);
        }

        .header-content {
            max-width: 1600px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 2rem;
        }

        .back-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 8px;
            color: var(--text-primary);
            text-decoration: none;
            transition: all 0.2s ease;
            font-weight: 500;
        }

        .back-btn:hover {
            background: var(--bg-hover);
            border-color: var(--border-hover);
            transform: translateY(-1px);
        }

        .character-info-header {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .character-name {
            font-size: 1.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--text-primary), var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .container {
            max-width: 1600px;
            margin: 0 auto;
            padding: 2.5rem 2rem;
        }

        .stats-bar {
            background: var(--bg-secondary);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            display: flex;
            gap: 2rem;
            flex-wrap: wrap;
        }

        .stat-item {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .stat-label {
            font-size: 0.8rem;
            color: var(--text-secondary);
        }

        .stat-value {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--accent);
        }

        .images-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.25rem;
            margin-bottom: 3rem;
        }

        .image-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 12px;
            overflow: hidden;
            cursor: pointer;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .image-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 20px 40px rgba(139, 92, 246, 0.15), 0 8px 16px var(--shadow);
            border-color: rgba(139, 92, 246, 0.5);
        }

        .image-container {
            position: relative;
            width: 100%;
            overflow: hidden;
            background: linear-gradient(135deg, #1a1a1a, #252525);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .image-card:hover .image-container img {
            transform: scale(1.03);
        }

        .image-container img {
            transition: transform 0.3s ease;
        }

        .image-info {
            padding: 1rem;
        }

        .image-caption {
            font-size: 0.85rem;
            color: var(--text-secondary);
            line-height: 1.5;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            margin-bottom: 0.75rem;
        }

        .image-meta {
            display: flex;
            gap: 1rem;
            font-size: 0.75rem;
            color: var(--text-tertiary);
            padding-top: 0.75rem;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
        }

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

        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.5rem;
            margin-top: 3rem;
            padding: 1.5rem;
            background: var(--bg-secondary);
            border-radius: 12px;
            border: 1px solid var(--border);
        }

        .page-btn {
            padding: 0.6rem 1rem;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 8px;
            color: var(--text-primary);
            cursor: pointer;
            transition: all 0.2s ease;
            font-weight: 500;
            min-width: 40px;
        }

        .page-btn:hover:not(:disabled) {
            background: var(--bg-hover);
            border-color: var(--accent);
            transform: translateY(-2px);
        }

        .page-btn:disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }

        .page-btn.active {
            background: var(--accent);
            border-color: var(--accent);
            box-shadow: 0 4px 12px rgba(139, 92, 246, 0.3);
            font-weight: 700;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.95);
            z-index: 1000;
            padding: 2rem;
        }

        .modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            max-width: 90vw;
            max-height: 90vh;
            position: relative;
        }

        .modal-image {
            max-width: 100%;
            max-height: 90vh;
            object-fit: contain;
            border-radius: 12px;
        }

        .modal-close {
            position: absolute;
            top: -3rem;
            right: 0;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 8px;
            color: var(--text-primary);
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 1.5rem;
            transition: all 0.2s ease;
        }

        .modal-close:hover {
            background: var(--accent);
            border-color: var(--accent);
        }

        @media (max-width: 768px) {
            .container {
                padding: 1.5rem 1rem;
            }

            .images-grid {
                grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
                gap: 0.875rem;
            }

            .header-content {
                flex-direction: column;
                gap: 1rem;
            }

            .stats-bar {
                gap: 1rem;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <a href="../explore.php" class="back-btn">← Quay lại</a>
            <div class="character-info-header">
                <h1 class="character-name" id="characterName">Loading...</h1>
            </div>
        </div>
    </header>

    <main class="container">
        <div class="stats-bar" id="statsBar" style="display: none;">
            <div class="stat-item">
                <span class="stat-label">Tổng ảnh</span>
                <span class="stat-value" id="totalImages">0</span>
            </div>
            <div class="stat-item">
                <span class="stat-label">Character ID</span>
                <span class="stat-value" id="characterId">-</span>
            </div>
        </div>

        <div id="loadingState" class="loading">
            <div class="spinner"></div>
            <p>Đang tải ảnh...</p>
        </div>

        <div id="emptyState" class="empty-state" style="display: none;">
            <div class="empty-icon">🖼️</div>
            <h3>Không có ảnh</h3>
            <p>Nhân vật này chưa có ảnh nào</p>
        </div>

        <div id="imagesGrid" class="images-grid"></div>

        <div id="pagination" class="pagination" style="display: none;"></div>
    </main>

    <!-- Modal -->
    <div id="imageModal" class="modal">
        <div class="modal-content">
            <button class="modal-close" onclick="closeModal()">×</button>
            <img id="modalImage" class="modal-image" src="" alt="">
        </div>
    </div>

    <script>
        const urlParams = new URLSearchParams(window.location.search);
        const characterId = urlParams.get('id');
        let currentPage = 1;
        let characterData = null;

        document.addEventListener('DOMContentLoaded', () => {
            if (!characterId) {
                window.location.href = '../explore.php';
                return;
            }
            loadCharacterInfo();
            loadImages();
        });

        async function loadCharacterInfo() {
            try {
                const response = await fetch(`../character/api.php?action=get&id=${characterId}`);
                const result = await response.json();
                if (result.success && result.data) {
                    characterData = result.data;
                    document.getElementById('characterName').textContent = characterData.name || 'Unknown';
                    document.getElementById('characterId').textContent = characterData.display_id || characterId;
                }
            } catch (error) {
                console.error('Error loading character:', error);
            }
        }

        async function loadImages() {
            showLoading(true);
            
            try {
                const response = await fetch(`../image/api.php?action=list&character_id=${characterId}&page=${currentPage}&limit=50000`);
                const result = await response.json();

                if (result.success && result.data) {
                    displayImages(result.data);
                    displayPagination(result.pagination);
                    
                    document.getElementById('statsBar').style.display = 'flex';
                    document.getElementById('totalImages').textContent = result.pagination.total || 0;
                    
                    if (result.data.length === 0) {
                        showEmptyState(true);
                    } else {
                        showEmptyState(false);
                    }
                } else {
                    showEmptyState(true);
                }
            } catch (error) {
                console.error('Error loading images:', error);
                showEmptyState(true);
            } finally {
                showLoading(false);
            }
        }

        function displayImages(images) {
            const grid = document.getElementById('imagesGrid');
            grid.innerHTML = '';

            images.forEach(img => {
                const card = createImageCard(img);
                grid.appendChild(card);
            });
        }

        function createImageCard(img) {
            const card = document.createElement('div');
            card.className = 'image-card';
            card.onclick = () => openModal(img.image_url);

            const dimensions = img.width && img.height ? `${img.width}×${img.height}` : '';
            const pose = img.pose || '';
            const outfit = img.outfit || '';
            const aspectRatio = img.width && img.height ? `${img.width}/${img.height}` : '3/4';

            card.innerHTML = `
                <div class="image-container" style="aspect-ratio: ${aspectRatio};">
                    <img src="${escapeHtml(img.image_url)}" alt="${escapeHtml(img.caption || 'Image')}" loading="lazy" style="width: 100%; height: 100%; object-fit: contain;" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22100%22 height=%22100%22%3E%3Crect fill=%22%231a1a1a%22 width=%22100%22 height=%22100%22/%3E%3C/svg%3E'">
                </div>
                <div class="image-info">
                    ${img.caption ? `<p class="image-caption">${escapeHtml(img.caption)}</p>` : ''}
                    <div class="image-meta">
                        ${dimensions ? `<span>📐 ${dimensions}</span>` : ''}
                        ${pose ? `<span>🧍 ${escapeHtml(pose)}</span>` : ''}
                        ${outfit ? `<span>👔 ${escapeHtml(outfit)}</span>` : ''}
                    </div>
                </div>
            `;

            return card;
        }

        function displayPagination(pagination) {
            const paginationEl = document.getElementById('pagination');
            
            if (!pagination) {
                paginationEl.style.display = 'none';
                return;
            }

            paginationEl.style.display = 'flex';
            paginationEl.innerHTML = '';

            const pageInfo = document.createElement('div');
            pageInfo.style.cssText = 'color: var(--text-secondary); font-size: 0.9rem; margin-right: auto;';
            pageInfo.textContent = `Trang ${pagination.page} / ${pagination.total_pages} (${pagination.total} ảnh)`;
            paginationEl.appendChild(pageInfo);

            if (pagination.total_pages <= 1) {
                return;
            }

            const prevBtn = document.createElement('button');
            prevBtn.className = 'page-btn';
            prevBtn.textContent = '← Trước';
            prevBtn.disabled = pagination.page === 1;
            prevBtn.onclick = () => {
                currentPage = pagination.page - 1;
                loadImages();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            };
            paginationEl.appendChild(prevBtn);

            const maxPages = 5;
            let startPage = Math.max(1, pagination.page - Math.floor(maxPages / 2));
            let endPage = Math.min(pagination.total_pages, startPage + maxPages - 1);
            
            if (endPage - startPage < maxPages - 1) {
                startPage = Math.max(1, endPage - maxPages + 1);
            }

            for (let i = startPage; i <= endPage; i++) {
                const pageBtn = document.createElement('button');
                pageBtn.className = 'page-btn' + (i === pagination.page ? ' active' : '');
                pageBtn.textContent = i;
                pageBtn.onclick = () => {
                    currentPage = i;
                    loadImages();
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                };
                paginationEl.appendChild(pageBtn);
            }

            const nextBtn = document.createElement('button');
            nextBtn.className = 'page-btn';
            nextBtn.textContent = 'Sau →';
            nextBtn.disabled = pagination.page >= pagination.total_pages;
            nextBtn.onclick = () => {
                currentPage = pagination.page + 1;
                loadImages();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            };
            paginationEl.appendChild(nextBtn);
        }

        function openModal(imageUrl) {
            const modal = document.getElementById('imageModal');
            const modalImage = document.getElementById('modalImage');
            modalImage.src = imageUrl;
            modal.classList.add('show');
        }

        function closeModal() {
            const modal = document.getElementById('imageModal');
            modal.classList.remove('show');
        }

        document.getElementById('imageModal').addEventListener('click', (e) => {
            if (e.target.id === 'imageModal') {
                closeModal();
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeModal();
            }
        });

        function showLoading(show) {
            document.getElementById('loadingState').style.display = show ? 'block' : 'none';
            document.getElementById('imagesGrid').style.display = show ? 'none' : 'grid';
        }

        function showEmptyState(show) {
            document.getElementById('emptyState').style.display = show ? 'block' : 'none';
            document.getElementById('imagesGrid').style.display = show ? 'none' : 'grid';
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    </script>
</body>
</html>
