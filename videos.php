<?php
// Version: 2025-12-02-FIXED
// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');

// Database configuration
require_once __DIR__ . '/config/database.php';

// Get character ID from URL
$character_id = $_GET['id'] ?? '';

if (empty($character_id)) {
    die('Character ID is required');
}

// Get character info
$stmt = $pdo->prepare("SELECT * FROM characters WHERE id = ?");
$stmt->execute([$character_id]);
$character = $stmt->fetch();

if (!$character) {
    http_response_code(404);
    die('<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Character Not Found</title></head><body style="font-family: sans-serif; text-align: center; padding: 50px;"><h1>Character Not Found</h1><p>The character you are looking for does not exist.</p><a href="/" style="color: #8b5cf6;">Go back home</a></body></html>');
}

// Get filter parameters
$filter_action = $_GET['action'] ?? '';
$filter_outfit = $_GET['outfit'] ?? '';
$filter_background = $_GET['background'] ?? '';
$filter_pose = $_GET['pose'] ?? '';
$filter_duration = $_GET['duration'] ?? '';
$filter_width = $_GET['width'] ?? '';
$filter_height = $_GET['height'] ?? '';
$sort_by = $_GET['sort'] ?? 'created_at';
$sort_order = $_GET['order'] ?? 'DESC';

// Validate sort parameters
$allowed_sorts = ['created_at', 'duration', 'width', 'height'];
$allowed_orders = ['ASC', 'DESC'];
if (!in_array($sort_by, $allowed_sorts)) $sort_by = 'created_at';
if (!in_array($sort_order, $allowed_orders)) $sort_order = 'DESC';

// Build WHERE conditions for filters
$where_conditions = ["character_id = ?", "video_url IS NOT NULL", "video_url != ''"];
$params = [$character_id];

if (!empty($filter_action)) {
    $where_conditions[] = "action = ?";
    $params[] = $filter_action;
}
if (!empty($filter_outfit)) {
    $where_conditions[] = "outfit = ?";
    $params[] = $filter_outfit;
}
if (!empty($filter_background)) {
    $where_conditions[] = "background = ?";
    $params[] = $filter_background;
}
if (!empty($filter_pose)) {
    $where_conditions[] = "pose = ?";
    $params[] = $filter_pose;
}
if (!empty($filter_duration)) {
    switch($filter_duration) {
        case 'short':
            $where_conditions[] = "duration < 5";
            break;
        case 'medium':
            $where_conditions[] = "duration >= 5 AND duration <= 10";
            break;
        case 'long':
            $where_conditions[] = "duration > 10";
            break;
    }
}
if (!empty($filter_width)) {
    $where_conditions[] = "width = ?";
    $params[] = $filter_width;
}
if (!empty($filter_height)) {
    $where_conditions[] = "height = ?";
    $params[] = $filter_height;
}

$where_clause = implode(' AND ', $where_conditions);

// Pagination for infinite scroll
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 100;
$offset = ($page - 1) * $limit;

// Check if this is an AJAX request
$is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

// Get videos for this character
$safe_sort_by = $sort_by; // Already validated
$safe_sort_order = $sort_order; // Already validated
$safe_limit = (int)$limit;
$safe_offset = (int)$offset;

$stmt = $pdo->prepare("
    SELECT 
        *
    FROM videos 
    WHERE {$where_clause}
    ORDER BY {$safe_sort_by} {$safe_sort_order}
    LIMIT {$safe_limit} OFFSET {$safe_offset}
");
$stmt->execute($params);
$videos = $stmt->fetchAll();

// Group videos by input_image_url
$grouped_videos = [];
foreach ($videos as $video) {
    $image_key = !empty($video['input_image_url']) ? $video['input_image_url'] : 'no_image_' . $video['id'];
    if (!isset($grouped_videos[$image_key])) {
        $grouped_videos[$image_key] = [
            'representative' => $video,
            'videos' => []
        ];
    }
    $grouped_videos[$image_key]['videos'][] = $video;
}

// Get total count with filters
$stmt = $pdo->prepare("
    SELECT COUNT(*) as total 
    FROM videos 
    WHERE {$where_clause}
");
$stmt->execute($params);
$total = $stmt->fetch()['total'];
$has_more = ($offset + count($videos)) < $total;

// If AJAX request, return JSON with grouped data
if ($is_ajax) {
    header('Content-Type: application/json');
    echo json_encode([
        'videos' => $videos,
        'grouped_videos' => array_values($grouped_videos),
        'has_more' => $has_more,
        'page' => $page,
        'total' => $total
    ]);
    exit;
}

// Get filter options with counts (for the base character, not filtered)
$base_where = "character_id = ? AND video_url IS NOT NULL AND video_url != ''";

// Get actions with counts
$stmt = $pdo->prepare("
    SELECT action, COUNT(*) as count 
    FROM videos 
    WHERE {$base_where} AND action IS NOT NULL AND action != ''
    GROUP BY action 
    ORDER BY count DESC, action ASC
");
$stmt->execute([$character_id]);
$actions_with_count = $stmt->fetchAll();

// Get outfits with counts
$stmt = $pdo->prepare("
    SELECT outfit, COUNT(*) as count 
    FROM videos 
    WHERE {$base_where} AND outfit IS NOT NULL AND outfit != ''
    GROUP BY outfit 
    ORDER BY count DESC, outfit ASC
");
$stmt->execute([$character_id]);
$outfits_with_count = $stmt->fetchAll();

// Get backgrounds with counts
$stmt = $pdo->prepare("
    SELECT background, COUNT(*) as count 
    FROM videos 
    WHERE {$base_where} AND background IS NOT NULL AND background != ''
    GROUP BY background 
    ORDER BY count DESC, background ASC
");
$stmt->execute([$character_id]);
$backgrounds_with_count = $stmt->fetchAll();

// Get poses with counts
$stmt = $pdo->prepare("
    SELECT pose, COUNT(*) as count 
    FROM videos 
    WHERE {$base_where} AND pose IS NOT NULL AND pose != ''
    GROUP BY pose 
    ORDER BY count DESC, pose ASC
");
$stmt->execute([$character_id]);
$poses_with_count = $stmt->fetchAll();

// Get widths with counts
$stmt = $pdo->prepare("
    SELECT width, COUNT(*) as count 
    FROM videos 
    WHERE {$base_where} AND width IS NOT NULL AND width > 0
    GROUP BY width 
    ORDER BY width DESC
");
$stmt->execute([$character_id]);
$widths_with_count = $stmt->fetchAll();

// Get heights with counts
$stmt = $pdo->prepare("
    SELECT height, COUNT(*) as count 
    FROM videos 
    WHERE {$base_where} AND height IS NOT NULL AND height > 0
    GROUP BY height 
    ORDER BY height DESC
");
$stmt->execute([$character_id]);
$heights_with_count = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="vi" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, viewport-fit=cover">
    <title>Videos - <?= htmlspecialchars($character['name']) ?></title>
    <!-- Build: 2025-12-02-FIXED -->
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --bg-primary: #0a0a0f;
            --bg-secondary: #13131a;
            --bg-card: #1a1a24;
            --bg-hover: #252532;
            --text-primary: #ffffff;
            --text-secondary: #a0a3bd;
            --text-tertiary: #6b7280;
            --accent: #8b5cf6;
            --accent-hover: #7c3aed;
            --accent-light: rgba(139, 92, 246, 0.12);
            --accent-glow: rgba(139, 92, 246, 0.4);
            --accent-pink: #ec4899;
            --accent-rose: #f43f5e;
            --secondary: #ec4899;
            --secondary-hover: #db2777;
            --border: rgba(255, 255, 255, 0.06);
            --border-hover: rgba(139, 92, 246, 0.5);
            --shadow: rgba(0, 0, 0, 0.6);
            --shadow-accent: rgba(139, 92, 246, 0.3);
            --gradient-primary: linear-gradient(135deg, #8b5cf6 0%, #ec4899 100%);
            --gradient-hover: linear-gradient(135deg, #7c3aed 0%, #db2777 100%);
            --gradient-card: linear-gradient(135deg, rgba(139, 92, 246, 0.15) 0%, rgba(236, 72, 153, 0.15) 100%);
            --glow-purple: rgba(139, 92, 246, 0.4);
            --glow-pink: rgba(236, 72, 153, 0.4);
            --gradient-secondary: linear-gradient(135deg, #06b6d4 0%, #3b82f6 100%);
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', sans-serif;
            background: linear-gradient(180deg, #0a0a0f 0%, #13131a 50%, #0a0a0f 100%);
            color: var(--text-primary);
            line-height: 1.6;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            position: relative;
        }

        /* Animated background effect */
        body::before {
            content: '';
            position: fixed;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(124, 58, 237, 0.08) 0%, transparent 50%);
            animation: rotate 60s linear infinite;
            pointer-events: none;
            z-index: 0;
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 12px;
        }

        ::-webkit-scrollbar-track {
            background: var(--bg-secondary);
        }

        ::-webkit-scrollbar-thumb {
            background: rgba(139, 92, 246, 0.3);
            border-radius: 6px;
            border: 2px solid var(--bg-secondary);
        }

        ::-webkit-scrollbar-thumb:hover {
            background: rgba(139, 92, 246, 0.5);
        }

        .header {
            position: sticky;
            top: 0;
            background: rgba(10, 10, 15, 0.85);
            backdrop-filter: blur(30px) saturate(180%);
            border-bottom: 1px solid var(--border);
            padding: 1rem 2rem;
            z-index: 100;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.4), 0 0 60px rgba(139, 92, 246, 0.1);
            transition: all 0.3s ease;
        }

        .header-content {
            max-width: 1600px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            gap: 1.5rem;
            position: relative;
            z-index: 1;
        }

        .back-btn {
            padding: 0.7rem 1.3rem;
            background: linear-gradient(135deg, var(--bg-card) 0%, var(--bg-secondary) 100%);
            border: 1px solid var(--border);
            border-radius: 12px;
            color: var(--text-primary);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-weight: 600;
            position: relative;
            overflow: hidden;
        }

        .back-btn::before {
            content: '';
            position: absolute;
            inset: 0;
            background: var(--gradient-primary);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .back-btn:hover::before {
            opacity: 0.15;
        }

        .back-btn:hover {
            border-color: var(--accent);
            transform: translateX(-4px);
            box-shadow: 0 4px 16px var(--shadow-accent);
        }

        .back-btn svg {
            position: relative;
            z-index: 1;
            transition: transform 0.3s ease;
        }

        .back-btn:hover svg {
            transform: translateX(-2px);
        }

        .character-info-header {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .character-name-header {
            font-size: 1.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--text-primary), var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .character-meta {
            font-size: 0.9rem;
            color: var(--text-secondary);
        }

        .character-banner {
            background: linear-gradient(135deg, #0f0f18 0%, #1a1a28 50%, #0f0f18 100%);
            border-bottom: 1px solid var(--border);
            padding: 4rem 2rem;
            position: relative;
            overflow: hidden;
        }

        .character-banner::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 20% 50%, rgba(124, 58, 237, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 80% 50%, rgba(236, 72, 153, 0.15) 0%, transparent 50%);
            opacity: 0.6;
            filter: blur(60px);
        }

        .character-banner::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--accent), var(--secondary), transparent);
            opacity: 0.7;
        }

        .character-banner-content {
            max-width: 1600px;
            margin: 0 auto;
            display: flex;
            gap: 2.5rem;
            align-items: flex-start;
            position: relative;
            z-index: 1;
        }

        .character-avatar {
            width: 140px;
            height: 200px;
            border-radius: 16px;
            object-fit: cover;
            border: 3px solid transparent;
            background: var(--gradient-primary);
            background-clip: padding-box;
            position: relative;
            box-shadow: 
                0 12px 32px rgba(124, 58, 237, 0.5), 
                0 0 60px rgba(124, 58, 237, 0.3),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
            flex-shrink: 0;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .character-avatar::before {
            content: '';
            position: absolute;
            inset: -3px;
            border-radius: 16px;
            padding: 3px;
            background: var(--gradient-primary);
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            opacity: 0.8;
        }

        .character-avatar:hover {
            transform: translateY(-6px) scale(1.03);
            box-shadow: 
                0 20px 48px rgba(124, 58, 237, 0.6), 
                0 0 80px rgba(124, 58, 237, 0.4),
                inset 0 1px 0 rgba(255, 255, 255, 0.15);
        }

        .character-details {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            padding-top: 0.5rem;
        }

        .character-banner-name {
            font-size: 2.75rem;
            font-weight: 900;
            background: linear-gradient(135deg, #ffffff 0%, #a78bfa 30%, var(--accent) 60%, var(--secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1.1;
            letter-spacing: -0.02em;
            margin-bottom: 0.25rem;
            position: relative;
            text-shadow: 0 0 40px rgba(124, 58, 237, 0.5);
            animation: gradientShift 8s ease infinite;
            background-size: 200% 100%;
        }

        @keyframes gradientShift {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        .character-banner-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            font-size: 0.9rem;
            color: var(--text-secondary);
        }

        .character-banner-meta-item {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.4rem 0.8rem;
            background: rgba(139, 92, 246, 0.08);
            border: 1px solid rgba(139, 92, 246, 0.15);
            border-radius: 6px;
            transition: all 0.2s ease;
        }

        .character-banner-meta-item:hover {
            background: rgba(139, 92, 246, 0.15);
            border-color: rgba(139, 92, 246, 0.3);
            transform: translateY(-1px);
        }

        .character-banner-meta-item svg {
            color: var(--accent);
        }

        .character-banner-meta-item strong {
            color: var(--text-primary);
            font-weight: 600;
        }

        .character-description {
            font-size: 1rem;
            color: var(--text-secondary);
            line-height: 1.7;
            max-width: 800px;
            padding: 1rem;
            background: rgba(139, 92, 246, 0.05);
            border-left: 3px solid var(--accent);
            border-radius: 4px;
        }

        .character-stats {
            display: flex;
            gap: 1.5rem;
            margin-top: 1.5rem;
        }

        .character-stat {
            display: flex;
            flex-direction: column;
            gap: 0.3rem;
            padding: 1rem 1.5rem;
            background: rgba(139, 92, 246, 0.08);
            border: 1px solid rgba(139, 92, 246, 0.2);
            border-radius: 8px;
            transition: all 0.3s ease;
            min-width: 100px;
            text-align: center;
        }

        .character-stat:hover {
            background: rgba(139, 92, 246, 0.15);
            border-color: var(--accent);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(139, 92, 246, 0.2);
        }

        .character-stat-value {
            font-size: 1.75rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--accent), #ec4899);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .character-stat-label {
            font-size: 0.75rem;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
        }

        .container {
            max-width: 1600px;
            margin: 0 auto;
            padding: 3rem 2rem;
            position: relative;
            z-index: 1;
        }

        .section-header {
            margin-bottom: 2.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(139, 92, 246, 0.1);
        }

        .section-title {
            font-size: 1.85rem;
            font-weight: 900;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            background: linear-gradient(135deg, var(--text-primary), var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .section-subtitle {
            color: var(--text-secondary);
            font-size: 1rem;
            font-weight: 500;
        }

        /* Video grid - optimized spacing with larger thumbnails */
        .video-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
            position: relative;
            z-index: 1;
            padding: 0.5rem;
            max-width: 1400px;
            margin-left: auto;
            margin-right: auto;
        }

        /* Stagger animation for cards */
        .video-thumb-card:nth-child(1) { animation-delay: 0s; }
        .video-thumb-card:nth-child(2) { animation-delay: 0.05s; }
        .video-thumb-card:nth-child(3) { animation-delay: 0.1s; }
        .video-thumb-card:nth-child(4) { animation-delay: 0.15s; }
        .video-thumb-card:nth-child(5) { animation-delay: 0.2s; }
        .video-thumb-card:nth-child(n+6) { animation-delay: 0.25s; }

        /* Video thumbnail card - enhanced style */
        .video-thumb-card {
            position: relative;
            aspect-ratio: 2/3;
            border-radius: 10px;
            overflow: hidden;
            background: linear-gradient(135deg, #0a0a0f 0%, #13131a 100%);
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid rgba(139, 92, 246, 0.15);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
            animation: cardFadeIn 0.4s ease-out;
            opacity: 0;
            animation-fill-mode: forwards;
        }

        @keyframes cardFadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .video-thumb-card:hover {
            border-color: rgba(139, 92, 246, 0.6);
            transform: translateY(-6px);
            box-shadow: 
                0 12px 28px rgba(0, 0, 0, 0.4),
                0 0 40px rgba(139, 92, 246, 0.2),
                inset 0 0 0 1px rgba(139, 92, 246, 0.3);
        }

        .video-thumb-card:hover .video-play-corner {
            background: linear-gradient(135deg, rgba(124, 58, 237, 1), rgba(139, 92, 246, 1));
            transform: scale(1.15);
            box-shadow: 0 4px 16px rgba(139, 92, 246, 0.6);
        }

        /* Enhanced overlay with beautiful gradient */
        .video-overlay-simple {
            position: absolute;
            inset: 0;
            background: linear-gradient(to bottom, 
                rgba(0, 0, 0, 0.3) 0%, 
                transparent 30%, 
                transparent 70%, 
                rgba(0, 0, 0, 0.8) 100%);
            pointer-events: none;
            opacity: 1;
            transition: all 0.3s ease;
        }

        .video-thumb-card:hover .video-overlay-simple {
            background: linear-gradient(to bottom, 
                rgba(139, 92, 246, 0.15) 0%, 
                rgba(236, 72, 153, 0.05) 30%, 
                rgba(236, 72, 153, 0.05) 70%, 
                rgba(0, 0, 0, 0.85) 100%);
        }

        /* Play button - enhanced with gradient and glow */
        .video-play-corner {
            position: absolute;
            bottom: 0.6rem;
            left: 0.6rem;
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, rgba(139, 92, 246, 0.95), rgba(168, 85, 247, 0.95));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 
                0 4px 12px rgba(139, 92, 246, 0.4),
                0 0 20px rgba(139, 92, 246, 0.2);
            z-index: 3;
            border: 2px solid rgba(255, 255, 255, 0.95);
            cursor: pointer;
        }

        .video-play-corner::before {
            content: '';
            position: absolute;
            inset: -6px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(139, 92, 246, 0.4) 0%, transparent 70%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .video-play-corner:hover::before {
            opacity: 1;
            animation: playGlow 1.5s ease-in-out infinite;
        }

        @keyframes playGlow {
            0%, 100% { transform: scale(1); opacity: 0.4; }
            50% { transform: scale(1.3); opacity: 0; }
        }

        .video-play-corner:hover {
            background: linear-gradient(135deg, #7c3aed, #a855f7);
            transform: scale(1.1);
            box-shadow: 
                0 6px 20px rgba(139, 92, 246, 0.6),
                0 0 30px rgba(139, 92, 246, 0.4);
        }

        .video-play-corner svg {
            margin-left: 2px;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.3));
            transition: transform 0.3s ease;
        }

        .video-play-corner:hover svg {
            transform: scale(1.1);
        }

        /* Video count badge on play button - enhanced */
        .video-count-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: linear-gradient(135deg, rgba(236, 72, 153, 1), rgba(244, 63, 94, 1));
            color: white;
            border-radius: 50%;
            width: 22px;
            height: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            font-weight: 800;
            border: 2.5px solid white;
            box-shadow: 
                0 2px 8px rgba(236, 72, 153, 0.5),
                0 0 12px rgba(236, 72, 153, 0.3);
            animation: countPulse 2s ease-in-out infinite;
        }

        @keyframes countPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        /* View image button - enhanced */
        .video-view-btn {
            position: absolute;
            bottom: 0.6rem;
            right: 0.6rem;
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, rgba(236, 72, 153, 0.95), rgba(244, 63, 94, 0.95));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 
                0 4px 12px rgba(236, 72, 153, 0.4),
                0 0 20px rgba(236, 72, 153, 0.2);
            z-index: 3;
            border: 2px solid rgba(255, 255, 255, 0.95);
            cursor: pointer;
        }

        .video-view-btn:hover {
            background: linear-gradient(135deg, #db2777, #f43f5e);
            transform: scale(1.1);
            box-shadow: 
                0 6px 20px rgba(236, 72, 153, 0.6),
                0 0 30px rgba(236, 72, 153, 0.4);
        }

        .video-view-btn svg {
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.3));
            transition: transform 0.3s ease;
        }

        .video-view-btn:hover svg {
            transform: scale(1.1);
        }

        /* Duration badge - enhanced */
        .video-time-badge {
            position: absolute;
            top: 0.6rem;
            right: 0.6rem;
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.9), rgba(20, 20, 30, 0.9));
            color: white;
            padding: 0.3rem 0.6rem;
            border-radius: 6px;
            font-size: 0.7rem;
            font-weight: 700;
            z-index: 2;
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
            transition: all 0.25s ease;
        }

        .video-thumb-card:hover .video-time-badge {
            background: linear-gradient(135deg, rgba(139, 92, 246, 0.9), rgba(168, 85, 247, 0.9));
            transform: translateY(-2px);
        }

        .slideshow-btn {
            padding: 0.7rem 1.4rem;
            background: var(--gradient-primary);
            border: 1px solid rgba(124, 58, 237, 0.4);
            border-radius: 12px;
            color: var(--text-primary);
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            font-weight: 600;
            font-size: 0.95rem;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 16px var(--shadow-accent);
        }

        .slideshow-btn::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transform: translateX(-100%);
            transition: transform 0.6s ease;
        }

        .slideshow-btn:hover::before {
            transform: translateX(100%);
        }

        .slideshow-btn:hover {
            border-color: var(--accent-hover);
            transform: translateY(-3px);
            box-shadow: 0 8px 24px rgba(124, 58, 237, 0.5);
        }

        .slideshow-btn svg {
            position: relative;
            z-index: 1;
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
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
            gap: 0.75rem;
            margin-top: 4rem;
            padding: 2rem;
            background: linear-gradient(135deg, rgba(19, 19, 26, 0.9), rgba(26, 26, 36, 0.9));
            backdrop-filter: blur(20px) saturate(180%);
            border-radius: 20px;
            border: 1px solid rgba(124, 58, 237, 0.2);
            box-shadow: 
                0 8px 32px rgba(0, 0, 0, 0.4),
                0 0 60px rgba(124, 58, 237, 0.15);
            position: relative;
            overflow: hidden;
        }

        .pagination::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at 50% 0%, rgba(124, 58, 237, 0.1) 0%, transparent 60%);
        }

        .page-btn {
            padding: 0.75rem 1.2rem;
            background: rgba(26, 26, 36, 0.9);
            border: 1px solid rgba(124, 58, 237, 0.25);
            border-radius: 12px;
            color: var(--text-primary);
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-weight: 600;
            min-width: 48px;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            font-size: 0.9rem;
            position: relative;
            z-index: 1;
        }

        .page-btn:hover:not(.disabled):not(.active) {
            background: rgba(124, 58, 237, 0.2);
            border-color: var(--accent);
            transform: translateY(-3px);
            box-shadow: 0 6px 16px rgba(124, 58, 237, 0.3);
        }

        .page-btn.disabled {
            opacity: 0.3;
            cursor: not-allowed;
        }

        .page-btn.active {
            background: var(--gradient-primary);
            border-color: transparent;
            box-shadow: 
                0 6px 20px var(--glow-purple),
                0 0 30px rgba(139, 92, 246, 0.15),
                inset 0 1px 0 rgba(255, 255, 255, 0.2);
            font-weight: 700;
            color: white;
            transform: scale(1.05);
        }

        .page-info {
            color: var(--text-secondary);
            font-size: 0.9rem;
            margin-right: auto;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.95);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal.show {
            display: flex;
        }

        .modal-content {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
            max-width: 100vw;
            max-height: 100vh;
        }

        .modal-video {
            max-width: 50vw;
            max-height: 90vh;
            border-radius: 0;
            object-fit: contain;
        }

        .modal-close {
            position: absolute;
            top: 2rem;
            left: 2rem;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 1.5rem;
            z-index: 13;
        }

        .modal-close:hover {
            background: var(--accent);
            border-color: var(--accent);
            transform: scale(1.1) rotate(90deg);
            box-shadow: 0 8px 24px rgba(139, 92, 246, 0.4);
        }

        .modal-info-btn {
            position: absolute;
            top: 2rem;
            right: 2rem;
            background: rgba(139, 92, 246, 0.2);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(139, 92, 246, 0.3);
            color: white;
            padding: 0.6rem 1rem;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            transition: all 0.2s ease;
            z-index: 13;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .modal-info-btn:hover {
            background: var(--accent);
            border-color: var(--accent);
            transform: translateY(-2px);
        }

        .modal-info-btn svg {
            width: 18px;
            height: 18px;
        }

        .modal-info {
            position: absolute;
            right: 0;
            top: 0;
            bottom: 0;
            width: 350px;
            background: rgba(26, 26, 26, 0.95);
            backdrop-filter: blur(20px);
            padding: 2rem;
            overflow-y: auto;
            transform: translateX(100%);
            transition: transform 0.3s ease;
            z-index: 12;
            border-left: 1px solid var(--border);
        }

        .modal-info.show {
            transform: translateX(0);
        }

        .modal-info-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border);
        }

        .modal-info-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-primary);
        }

        .modal-info-close {
            background: transparent;
            border: none;
            color: var(--text-secondary);
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 1.5rem;
        }

        .modal-info-close:hover {
            color: var(--accent);
        }

        .modal-info-content {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .info-item {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .info-label {
            font-size: 0.85rem;
            color: var(--text-secondary);
            font-weight: 600;
        }

        .info-value {
            font-size: 0.95rem;
            color: var(--text-primary);
            word-wrap: break-word;
        }

        .modal-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            z-index: 13;
        }

        .modal-nav:hover:not(:disabled) {
            background: var(--accent);
            border-color: var(--accent);
            transform: translateY(-50%) scale(1.15);
            box-shadow: 0 8px 24px rgba(139, 92, 246, 0.4);
        }

        .modal-nav:active {
            transform: translateY(-50%) scale(0.95);
        }

        .modal-nav.prev {
            left: 2rem;
        }

        .modal-nav.next {
            right: 2rem;
        }

        .modal-nav:disabled {
            opacity: 0.3;
            cursor: not-allowed;
        }

        .modal-nav:disabled:hover {
            color: white;
            transform: translateY(-50%);
        }

        /* Filter Panel Styles */
        .filter-panel {
            background: linear-gradient(135deg, rgba(19, 19, 26, 0.95), rgba(26, 26, 36, 0.95));
            backdrop-filter: blur(20px) saturate(180%);
            border: 1px solid rgba(124, 58, 237, 0.2);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 3rem;
            box-shadow: 
                0 8px 32px rgba(0, 0, 0, 0.4),
                0 0 60px rgba(124, 58, 237, 0.1);
            position: relative;
            overflow: hidden;
            z-index: 1;
        }

        .filter-panel::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at 50% 0%, rgba(124, 58, 237, 0.15) 0%, transparent 60%);
            pointer-events: none;
        }

        .filter-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            position: relative;
            z-index: 1;
        }

        .filter-title {
            font-size: 1.25rem;
            font-weight: 700;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            letter-spacing: -0.02em;
        }

        .filter-toggle {
            background: rgba(124, 58, 237, 0.15);
            border: 1px solid rgba(124, 58, 237, 0.3);
            border-radius: 10px;
            padding: 0.5rem 1rem;
            color: var(--text-primary);
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .filter-toggle:hover {
            background: rgba(124, 58, 237, 0.25);
            border-color: var(--accent);
            transform: translateY(-2px);
        }

        .filter-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            position: relative;
            z-index: 1;
        }

        .filter-content.collapsed {
            display: none;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .filter-label {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .filter-label svg {
            width: 14px;
            height: 14px;
            color: var(--accent);
        }

        .filter-input,
        .filter-select {
            background: rgba(26, 26, 36, 0.8);
            border: 1px solid rgba(124, 58, 237, 0.25);
            border-radius: 10px;
            padding: 0.75rem 1rem;
            color: var(--text-primary);
            font-size: 0.9rem;
            transition: all 0.3s ease;
            outline: none;
            font-family: inherit;
        }

        .filter-input:focus,
        .filter-select:focus {
            border-color: var(--accent);
            background: rgba(26, 26, 36, 0.95);
            box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.1);
        }

        .filter-select option {
            background: var(--bg-card);
            color: var(--text-primary);
            padding: 0.5rem;
        }

        .filter-select option:disabled {
            color: var(--text-tertiary);
            font-style: italic;
        }

        .filter-select option[data-count]::after {
            content: ' (' attr(data-count) ')';
            color: var(--accent);
            font-weight: 600;
            font-size: 0.85em;
        }

        .filter-actions {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
            position: relative;
            z-index: 1;
        }

        .filter-btn {
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            position: relative;
            overflow: hidden;
        }

        .filter-btn-primary {
            background: var(--gradient-primary);
            color: white;
            box-shadow: 0 4px 16px var(--shadow-accent);
        }

        .filter-btn-primary::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transform: translateX(-100%);
            transition: transform 0.6s ease;
        }

        .filter-btn-primary:hover::before {
            transform: translateX(100%);
        }

        .filter-btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(124, 58, 237, 0.5);
        }

        .filter-btn-secondary {
            background: rgba(124, 58, 237, 0.1);
            border: 1px solid rgba(124, 58, 237, 0.3);
            color: var(--text-primary);
        }

        .filter-btn-secondary:hover {
            background: rgba(124, 58, 237, 0.2);
            border-color: var(--accent);
            transform: translateY(-2px);
        }

        .active-filters {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-top: 1.5rem;
            position: relative;
            z-index: 1;
        }

        .filter-chip {
            background: linear-gradient(135deg, rgba(124, 58, 237, 0.2), rgba(236, 72, 153, 0.15));
            border: 1px solid rgba(124, 58, 237, 0.4);
            border-radius: 8px;
            padding: 0.4rem 0.8rem;
            font-size: 0.85rem;
            color: var(--text-primary);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .filter-chip:hover {
            background: linear-gradient(135deg, rgba(124, 58, 237, 0.3), rgba(236, 72, 153, 0.25));
            transform: translateY(-2px);
        }

        .filter-chip-remove {
            background: rgba(239, 68, 68, 0.2);
            border: none;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            color: #fca5a5;
            font-size: 0.9rem;
            font-weight: bold;
        }

        .filter-chip-remove:hover {
            background: rgba(239, 68, 68, 0.4);
            color: white;
        }

        .results-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding: 1rem 1.5rem;
            background: rgba(124, 58, 237, 0.08);
            border: 1px solid rgba(124, 58, 237, 0.2);
            border-radius: 12px;
            position: relative;
            z-index: 1;
        }

        .results-count {
            font-size: 0.95rem;
            color: var(--text-secondary);
            font-weight: 500;
        }

        .results-count strong {
            color: var(--accent);
            font-weight: 700;
            font-size: 1.1rem;
        }

        /* Video Selector Modal */
        #videoSelectorModal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.9);
            z-index: 2000;
            align-items: center;
            justify-content: center;
        }

        #videoSelectorModal.show {
            display: flex;
        }

        .video-selector-content {
            background: linear-gradient(135deg, rgba(26, 26, 36, 0.98), rgba(19, 19, 26, 0.98));
            backdrop-filter: blur(20px);
            border-radius: 16px;
            padding: 2rem;
            max-width: 600px;
            max-height: 80vh;
            overflow-y: auto;
            border: 1px solid rgba(139, 92, 246, 0.3);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
        }

        .video-selector-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(139, 92, 246, 0.2);
        }

        .video-selector-title {
            font-size: 1.25rem;
            font-weight: 700;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .video-selector-close {
            background: transparent;
            border: none;
            color: var(--text-secondary);
            font-size: 1.5rem;
            cursor: pointer;
            transition: all 0.2s ease;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }

        .video-selector-close:hover {
            background: rgba(139, 92, 246, 0.2);
            color: var(--accent);
        }

        .video-selector-list {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .video-selector-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: linear-gradient(135deg, rgba(139, 92, 246, 0.08), rgba(236, 72, 153, 0.05));
            border: 1px solid rgba(139, 92, 246, 0.2);
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .video-selector-item::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(139, 92, 246, 0.1), rgba(236, 72, 153, 0.1));
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .video-selector-item:hover::before {
            opacity: 1;
        }

        .video-selector-item:hover {
            border-color: var(--accent);
            transform: translateX(8px) scale(1.02);
            box-shadow: 
                0 8px 24px rgba(139, 92, 246, 0.3),
                inset 0 0 0 1px rgba(139, 92, 246, 0.3);
        }

        .video-selector-thumbnail {
            width: 60px;
            height: 80px;
            border-radius: 6px;
            object-fit: cover;
            flex-shrink: 0;
            border: 2px solid rgba(139, 92, 246, 0.3);
        }

        .video-selector-info {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .video-selector-action {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        .video-selector-details {
            font-size: 0.8rem;
            color: var(--text-secondary);
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .video-selector-icon {
            width: 32px;
            height: 32px;
            background: var(--gradient-primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        @media (max-width: 768px) {
            .header {
                padding: 1rem;
            }

            .header-content {
                flex-wrap: wrap;
                gap: 1rem;
            }

            .character-name-header {
                font-size: 1.25rem;
            }

            .container {
                padding: 1.5rem 1rem;
            }

            .video-grid {
                grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
                gap: 0.75rem;
                padding: 0.5rem;
            }

            .video-thumb-card {
                border-radius: 8px;
            }

            .video-thumb-card {
                border-radius: 4px;
            }

            .video-play-corner,
            .video-view-btn {
                width: 32px;
                height: 32px;
                bottom: 0.4rem;
            }

            .video-play-corner {
                left: 0.4rem;
            }

            .video-view-btn {
                right: 0.4rem;
            }

            .video-play-corner svg {
                width: 12px;
                height: 12px;
            }

            .video-view-btn svg {
                width: 14px;
                height: 14px;
            }

            .video-time-badge {
                font-size: 0.65rem;
                padding: 0.2rem 0.4rem;
                top: 0.4rem;
                right: 0.4rem;
            }

            .section-title {
                font-size: 1.5rem;
            }

            .modal-video {
                max-width: 95vw;
                max-height: 70vh;
            }

            .modal-close {
                top: 1rem;
                left: 1rem;
                width: 36px;
                height: 36px;
                font-size: 1.5rem;
            }

            .modal-nav {
                width: 40px;
                height: 40px;
            }

            .modal-nav.prev {
                left: 1rem;
            }

            .modal-nav.next {
                right: 1rem;
            }

            .modal-info-btn {
                top: 1rem;
                right: 1rem;
                padding: 0.5rem 0.8rem;
                font-size: 0.85rem;
            }

            .modal-info {
                width: 100%;
                max-width: 100vw;
                padding: 1.5rem 1rem;
            }

            .modal-info-title {
                font-size: 1.1rem;
            }

            .character-banner {
                padding: 2.5rem 1rem;
            }

            .character-banner-content {
                flex-direction: column;
                text-align: center;
                align-items: center;
                gap: 1.5rem;
            }

            .character-avatar {
                width: 120px;
                height: 170px;
            }

            .character-details {
                align-items: center;
                width: 100%;
            }

            .character-banner-name {
                font-size: 2rem;
            }

            .character-description {
                text-align: left;
                width: 100%;
            }

            .character-banner-meta {
                justify-content: center;
                gap: 0.5rem;
                font-size: 0.85rem;
            }

            .character-banner-meta-item {
                padding: 0.3rem 0.6rem;
            }

            .character-stats {
                justify-content: center;
                gap: 1rem;
                width: 100%;
                flex-wrap: wrap;
            }

            .character-stat {
                min-width: auto;
                flex: 1;
                padding: 0.75rem 1rem;
            }

            .filter-panel {
                padding: 1.5rem 1rem;
                border-radius: 16px;
            }

            .filter-content {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .filter-actions {
                flex-direction: column;
            }

            .filter-btn {
                width: 100%;
                justify-content: center;
            }

            .results-info {
                flex-direction: column;
                gap: 0.75rem;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <a href="/" class="back-btn">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
                Quay lại
            </a>
            <div class="character-info-header">
                <div class="character-name-header"><?= htmlspecialchars($character['name']) ?></div>
                <div class="character-meta">
                    <?= htmlspecialchars($character['gender']) ?> • <?= htmlspecialchars($character['style']) ?> • <?= $total ?> videos
                </div>
            </div>
            <button class="slideshow-btn" onclick="startSlideshow()">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                    <polygon points="5 3 19 12 5 21 5 3"/>
                </svg>
                Sideshow
            </button>
        </div>
    </header>

    <!-- Character Banner -->
    <section class="character-banner">
        <div class="character-banner-content">
            <?php 
            $avatar = '';
            if (!empty($character['display_image_urls'])) {
                // Handle both JSON string and array
                $images = is_string($character['display_image_urls']) 
                    ? json_decode($character['display_image_urls'], true) 
                    : $character['display_image_urls'];
                    
                if (is_array($images) && count($images) > 0) {
                    $avatar = $images[0];
                }
            }
            if (empty($avatar) && !empty($character['avatar_file_name'])) {
                $avatar = $character['avatar_file_name'];
            }
            ?>
            <?php if (!empty($avatar)): ?>
                <img src="<?= htmlspecialchars($avatar) ?>" alt="<?= htmlspecialchars($character['name']) ?>" class="character-avatar" onerror="this.style.display='none'">
            <?php else: ?>
                <div class="character-avatar" style="background: linear-gradient(135deg, var(--accent), #ec4899); display: flex; align-items: center; justify-content: center; font-size: 4rem; font-weight: 700; color: white;">
                    <?= mb_substr($character['name'], 0, 1) ?>
                </div>
            <?php endif; ?>
            
            <div class="character-details">
                <h1 class="character-banner-name"><?= htmlspecialchars($character['name']) ?></h1>
                
                <?php if (!empty($character['short_description']) || !empty($character['description'])): ?>
                    <p class="character-description">
                        <?= htmlspecialchars($character['short_description'] ?? $character['description'] ?? '') ?>
                    </p>
                <?php endif; ?>
                
                <div class="character-banner-meta">
                <?php if (!empty($character['gender'])): ?>
                    <div class="character-banner-meta-item">
                        <span><?= htmlspecialchars($character['gender']) ?></span>
                    </div>
                <?php endif; ?>
                <?php if (!empty($character['age'])): ?>
                    <div class="character-banner-meta-item">
                        <span><?= htmlspecialchars($character['age']) ?> tuổi</span>
                    </div>
                <?php endif; ?>
                    
                <?php if (!empty($character['style'])): ?>
                    <div class="character-banner-meta-item">
                        <span><?= htmlspecialchars($character['style']) ?></span>
                    </div>
                <?php endif; ?>

                    

                    

                    
                    <?php if (!empty($character['creator_username'])): ?>
                        <div class="character-banner-meta-item">
                            <span>@<?= htmlspecialchars($character['creator_username']) ?></span>
                        </div>
                    <?php endif; ?>
                    

                    

                </div>

                
                <?php if (!empty($character['tags'])): ?>
                    <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; margin-top: 1rem;">
                        <?php 
                        // Decode JSON if string, otherwise use as array
                        if (is_string($character['tags'])) {
                            // Try to decode as JSON first
                            $tags = json_decode($character['tags'], true);
                            // If not valid JSON, try comma-separated
                            if (!is_array($tags)) {
                                $tags = array_map('trim', explode(',', $character['tags']));
                            }
                        } else {
                            $tags = is_array($character['tags']) ? $character['tags'] : [];
                        }
                        
                        foreach ($tags as $tag): 
                            if (!empty($tag)):
                        ?>
                            <span class="tag" style="font-size: 0.85rem; padding: 0.4rem 0.9rem;"><?= htmlspecialchars($tag) ?></span>
                        <?php 
                            endif;
                        endforeach; 
                        ?>
                    </div>
                <?php endif; ?>
                
                <div class="character-stats">
                    <div class="character-stat">
                        <div class="character-stat-value"><?= number_format($total) ?></div>
                        <div class="character-stat-label">Videos</div>
                    </div>
                    <?php if (!empty($character['like_count'])): ?>
                        <div class="character-stat">
                            <div class="character-stat-value"><?= number_format($character['like_count']) ?></div>
                            <div class="character-stat-label">Likes</div>
                        </div>
                    <?php endif; ?>
                </div>
                

                
                <?php 
                // Display all character images and videos
                if (!empty($character['display_image_urls'])) {
                    $allMedia = is_string($character['display_image_urls']) 
                        ? json_decode($character['display_image_urls'], true) 
                        : $character['display_image_urls'];
                    
                    if (is_array($allMedia) && count($allMedia) > 1):
                        // Separate images and videos
                        $charImages = [];
                        $charVideos = [];
                        
                        foreach ($allMedia as $url) {
                            // Check if URL is a video
                            if (strpos($url, 'vid.ourdream.ai/') !== false || 
                                preg_match('/\.(mp4|webm|mov|avi|mkv)(\?|$)/i', $url)) {
                                $charVideos[] = $url;
                            } else {
                                $charImages[] = $url;
                            }
                        }
                        
                        $totalCount = count($charImages) + count($charVideos);
                ?>
                    <div style="margin-top: 1.5rem;">
                        <div style="font-size: 1rem; font-weight: 700; margin-bottom: 0.75rem; color: var(--text-primary);">
                            📸 Character Media (<?= $totalCount ?>) 
                            <?php if (count($charImages) > 0): ?>
                                <span style="font-size: 0.85rem; color: var(--text-secondary); font-weight: 500;">- <?= count($charImages) ?> ảnh</span>
                            <?php endif; ?>
                            <?php if (count($charVideos) > 0): ?>
                                <span style="font-size: 0.85rem; color: var(--text-secondary); font-weight: 500;">- <?= count($charVideos) ?> video</span>
                            <?php endif; ?>
                        </div>
                        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 0.75rem;">
                            <?php 
                            // Display images first
                            foreach ($charImages as $idx => $imgUrl): 
                            ?>
                                <div style="position: relative; aspect-ratio: 3/4; border-radius: 8px; overflow: hidden; border: 2px solid rgba(124, 58, 237, 0.3); cursor: pointer; transition: all 0.2s ease;" onclick="openCharacterMediaSlideshow(<?= $idx ?>)" onmouseover="this.style.transform='scale(1.05)'; this.style.borderColor='rgba(124, 58, 237, 0.6)';" onmouseout="this.style.transform='scale(1)'; this.style.borderColor='rgba(124, 58, 237, 0.3)';">
                                    <img src="<?= htmlspecialchars($imgUrl) ?>" alt="Character image" style="width: 100%; height: 100%; object-fit: cover;" loading="lazy" onerror="this.parentElement.style.display='none'">
                                </div>
                            <?php 
                            endforeach;
                            
                            // Display videos
                            foreach ($charVideos as $idx => $vidUrl): 
                                $videoIndex = count($charImages) + $idx;
                            ?>
                                <div style="position: relative; aspect-ratio: 3/4; border-radius: 8px; overflow: hidden; border: 2px solid rgba(236, 72, 153, 0.4); cursor: pointer; transition: all 0.2s ease; background: #000;" onclick="openCharacterMediaSlideshow(<?= $videoIndex ?>)" onmouseover="this.style.transform='scale(1.05)'; this.style.borderColor='rgba(236, 72, 153, 0.7)';" onmouseout="this.style.transform='scale(1)'; this.style.borderColor='rgba(236, 72, 153, 0.4)';">
                                    <video style="width: 100%; height: 100%; object-fit: cover;" loop muted playsinline onmouseover="this.play()" onmouseout="this.pause()">
                                        <source src="<?= htmlspecialchars($vidUrl) ?>" type="video/mp4">
                                    </video>
                                    <div style="position: absolute; top: 0.5rem; right: 0.5rem; background: rgba(236, 72, 153, 0.9); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.7rem; font-weight: 600;">
                                        🎥 VIDEO
                                    </div>
                                </div>
                            <?php 
                            endforeach; 
                            ?>
                        </div>
                        
                        <script>
                        // Store all character media for slideshow
                        const characterMedia = <?= json_encode(array_merge($charImages, $charVideos)) ?>;
                        const characterMediaTypes = <?= json_encode(array_merge(
                            array_fill(0, count($charImages), 'image'),
                            array_fill(0, count($charVideos), 'video')
                        )) ?>;
                        
                        let currentCharMediaIndex = 0;
                        
                        function openCharacterMediaSlideshow(startIndex) {
                            currentCharMediaIndex = startIndex;
                            const modal = document.getElementById('characterMediaModal');
                            modal.style.display = 'flex';
                            document.body.style.overflow = 'hidden';
                            showCharacterMedia(currentCharMediaIndex);
                        }
                        
                        function closeCharacterMediaModal() {
                            const modal = document.getElementById('characterMediaModal');
                            modal.style.display = 'none';
                            document.body.style.overflow = '';
                            
                            // Stop any playing video
                            const video = document.getElementById('charModalVideo');
                            if (video) video.pause();
                        }
                        
                        function showCharacterMedia(index) {
                            const mediaUrl = characterMedia[index];
                            const mediaType = characterMediaTypes[index];
                            const imageEl = document.getElementById('charModalImage');
                            const videoEl = document.getElementById('charModalVideo');
                            const counter = document.getElementById('charMediaCounter');
                            
                            counter.textContent = `${index + 1} / ${characterMedia.length}`;
                            
                            if (mediaType === 'video') {
                                imageEl.style.display = 'none';
                                videoEl.style.display = 'block';
                                videoEl.src = mediaUrl;
                                videoEl.load();
                                videoEl.play();
                            } else {
                                videoEl.style.display = 'none';
                                imageEl.style.display = 'block';
                                imageEl.src = mediaUrl;
                            }
                            
                            // Update nav buttons
                            document.getElementById('charModalPrev').disabled = index === 0;
                            document.getElementById('charModalNext').disabled = index === characterMedia.length - 1;
                        }
                        
                        function navigateCharacterMedia(direction) {
                            currentCharMediaIndex += direction;
                            if (currentCharMediaIndex < 0) currentCharMediaIndex = 0;
                            if (currentCharMediaIndex >= characterMedia.length) currentCharMediaIndex = characterMedia.length - 1;
                            showCharacterMedia(currentCharMediaIndex);
                        }
                        
                        // Keyboard navigation for character media
                        document.addEventListener('keydown', (e) => {
                            const modal = document.getElementById('characterMediaModal');
                            if (modal.style.display !== 'flex') return;
                            
                            if (e.key === 'Escape') {
                                closeCharacterMediaModal();
                            } else if (e.key === 'ArrowLeft') {
                                if (currentCharMediaIndex > 0) navigateCharacterMedia(-1);
                            } else if (e.key === 'ArrowRight') {
                                if (currentCharMediaIndex < characterMedia.length - 1) navigateCharacterMedia(1);
                            }
                        });
                        </script>
                    </div>
                <?php 
                    endif;
                }
                ?>
            </div>
        </div>
    </section>

    <main class="container">
        <div class="section-header">
            <h1 class="section-title">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polygon points="23 7 16 12 23 17 23 7"/>
                    <rect x="1" y="5" width="15" height="14" rx="2"/>
                </svg>
                <span>Video Gallery</span>
            </h1>
            <p class="section-subtitle">Tất cả video của nhân vật <?= htmlspecialchars($character['name']) ?></p>
        </div>

        <!-- Filter Panel -->
        <div class="filter-panel">
            <div class="filter-header">
                <div class="filter-title">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
                    </svg>
                    Bộ lọc video
                </div>
                <button class="filter-toggle" onclick="toggleFilters()">
                    <span id="filterToggleText">Ẩn bớt</span>
                </button>
            </div>
            
            <form method="GET" action="" id="filterForm">
                <input type="hidden" name="id" value="<?= htmlspecialchars($character_id) ?>">
                
                <div class="filter-content" id="filterContent">
                    <div class="filter-group">
                        <label class="filter-label">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
                            </svg>
                            Action
                        </label>
                        <select name="action" class="filter-select">
                            <option value="">Tất cả actions</option>
                            <?php foreach ($actions_with_count as $item): ?>
                                <option value="<?= htmlspecialchars($item['action']) ?>" <?= $filter_action === $item['action'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($item['action']) ?> (<?= number_format($item['count']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label class="filter-label">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/>
                            </svg>
                            Outfit
                        </label>
                        <select name="outfit" class="filter-select">
                            <option value="">Tất cả outfits</option>
                            <?php foreach ($outfits_with_count as $item): ?>
                                <option value="<?= htmlspecialchars($item['outfit']) ?>" <?= $filter_outfit === $item['outfit'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($item['outfit']) ?> (<?= number_format($item['count']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label class="filter-label">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                            </svg>
                            Background
                        </label>
                        <select name="background" class="filter-select">
                            <option value="">Tất cả backgrounds</option>
                            <?php foreach ($backgrounds_with_count as $item): ?>
                                <option value="<?= htmlspecialchars($item['background']) ?>" <?= $filter_background === $item['background'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($item['background']) ?> (<?= number_format($item['count']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label class="filter-label">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                            Pose
                        </label>
                        <select name="pose" class="filter-select">
                            <option value="">Tất cả poses</option>
                            <?php foreach ($poses_with_count as $item): ?>
                                <option value="<?= htmlspecialchars($item['pose']) ?>" <?= $filter_pose === $item['pose'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($item['pose']) ?> (<?= number_format($item['count']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label class="filter-label">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/>
                                <polyline points="12 6 12 12 16 14"/>
                            </svg>
                            Duration
                        </label>
                        <select name="duration" class="filter-select">
                            <option value="">Tất cả</option>
                            <option value="short" <?= $filter_duration === 'short' ? 'selected' : '' ?>>Ngắn (&lt; 5s)</option>
                            <option value="medium" <?= $filter_duration === 'medium' ? 'selected' : '' ?>>Trung bình (5-10s)</option>
                            <option value="long" <?= $filter_duration === 'long' ? 'selected' : '' ?>>Dài (&gt; 10s)</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label class="filter-label">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                                <line x1="3" y1="12" x2="21" y2="12"/>
                            </svg>
                            Width
                        </label>
                        <select name="width" class="filter-select">
                            <option value="">Tất cả width</option>
                            <?php foreach ($widths_with_count as $item): ?>
                                <option value="<?= htmlspecialchars($item['width']) ?>" <?= $filter_width == $item['width'] ? 'selected' : '' ?>>
                                    <?= $item['width'] ?>px (<?= number_format($item['count']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label class="filter-label">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                                <line x1="12" y1="3" x2="12" y2="21"/>
                            </svg>
                            Height
                        </label>
                        <select name="height" class="filter-select">
                            <option value="">Tất cả height</option>
                            <?php foreach ($heights_with_count as $item): ?>
                                <option value="<?= htmlspecialchars($item['height']) ?>" <?= $filter_height == $item['height'] ? 'selected' : '' ?>>
                                    <?= $item['height'] ?>px (<?= number_format($item['count']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label class="filter-label">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="4" y1="21" x2="4" y2="14"/>
                                <line x1="4" y1="10" x2="4" y2="3"/>
                                <line x1="12" y1="21" x2="12" y2="12"/>
                                <line x1="12" y1="8" x2="12" y2="3"/>
                                <line x1="20" y1="21" x2="20" y2="16"/>
                                <line x1="20" y1="12" x2="20" y2="3"/>
                                <line x1="1" y1="14" x2="7" y2="14"/>
                                <line x1="9" y1="8" x2="15" y2="8"/>
                                <line x1="17" y1="16" x2="23" y2="16"/>
                            </svg>
                            Sắp xếp theo
                        </label>
                        <select name="sort" class="filter-select">
                            <option value="created_at" <?= $sort_by === 'created_at' ? 'selected' : '' ?>>Ngày tạo</option>
                            <option value="duration" <?= $sort_by === 'duration' ? 'selected' : '' ?>>Độ dài</option>
                            <option value="width" <?= $sort_by === 'width' ? 'selected' : '' ?>>Độ rộng</option>
                            <option value="height" <?= $sort_by === 'height' ? 'selected' : '' ?>>Chiều cao</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label class="filter-label">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="12" y1="5" x2="12" y2="19"/>
                                <polyline points="19 12 12 19 5 12"/>
                            </svg>
                            Thứ tự
                        </label>
                        <select name="order" class="filter-select">
                            <option value="DESC" <?= $sort_order === 'DESC' ? 'selected' : '' ?>>Giảm dần</option>
                            <option value="ASC" <?= $sort_order === 'ASC' ? 'selected' : '' ?>>Tăng dần</option>
                        </select>
                    </div>
                </div>

                <div class="filter-actions">
                    <button type="submit" class="filter-btn filter-btn-primary">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"/>
                            <path d="m21 21-4.35-4.35"/>
                        </svg>
                        Áp dụng bộ lọc
                    </button>
                    <button type="button" class="filter-btn filter-btn-secondary" onclick="resetFilters()">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="1 4 1 10 7 10"/>
                            <polyline points="23 20 23 14 17 14"/>
                            <path d="M20.49 9A9 9 0 0 0 5.64 5.64L1 10m22 4l-4.64 4.36A9 9 0 0 1 3.51 15"/>
                        </svg>
                        Đặt lại
                    </button>
                </div>

                <?php if (!empty($filter_action) || !empty($filter_outfit) || !empty($filter_background) || !empty($filter_pose) || !empty($filter_duration) || !empty($filter_width) || !empty($filter_height)): ?>
                <div class="active-filters">
                    <?php if (!empty($filter_action)): ?>
                        <div class="filter-chip">
                            Action: <strong><?= htmlspecialchars($filter_action) ?></strong>
                            <button type="button" class="filter-chip-remove" onclick="removeFilter('action')">×</button>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($filter_outfit)): ?>
                        <div class="filter-chip">
                            Outfit: <strong><?= htmlspecialchars($filter_outfit) ?></strong>
                            <button type="button" class="filter-chip-remove" onclick="removeFilter('outfit')">×</button>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($filter_background)): ?>
                        <div class="filter-chip">
                            Background: <strong><?= htmlspecialchars($filter_background) ?></strong>
                            <button type="button" class="filter-chip-remove" onclick="removeFilter('background')">×</button>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($filter_pose)): ?>
                        <div class="filter-chip">
                            Pose: <strong><?= htmlspecialchars($filter_pose) ?></strong>
                            <button type="button" class="filter-chip-remove" onclick="removeFilter('pose')">×</button>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($filter_duration)): ?>
                        <div class="filter-chip">
                            Duration: <strong><?= ucfirst($filter_duration) ?></strong>
                            <button type="button" class="filter-chip-remove" onclick="removeFilter('duration')">×</button>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($filter_width)): ?>
                        <div class="filter-chip">
                            Width: <strong><?= $filter_width ?>px</strong>
                            <button type="button" class="filter-chip-remove" onclick="removeFilter('width')">×</button>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($filter_height)): ?>
                        <div class="filter-chip">
                            Height: <strong><?= $filter_height ?>px</strong>
                            <button type="button" class="filter-chip-remove" onclick="removeFilter('height')">×</button>
                        </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </form>
        </div>

        <!-- Results Info -->
        <?php if ($total > 0): ?>
        <div class="results-info">
            <div class="results-count">
                Hiển thị <strong><?= number_format(count($videos)) ?></strong> / <strong><?= number_format($total) ?></strong> video
                <?php if (!empty($filter_action) || !empty($filter_outfit) || !empty($filter_background) || !empty($filter_pose) || !empty($filter_duration) || !empty($filter_width) || !empty($filter_height)): ?>
                    với bộ lọc đã chọn
                <?php endif; ?>
            </div>
            <div style="font-size: 0.85rem; color: var(--text-tertiary);">
                Sắp xếp theo: <strong style="color: var(--accent);"><?= 
                    $sort_by === 'created_at' ? 'Ngày tạo' : 
                    ($sort_by === 'duration' ? 'Độ dài' : 
                    ($sort_by === 'width' ? 'Độ rộng' : 'Chiều cao'))
                ?></strong> (<?= $sort_order === 'DESC' ? 'Giảm dần' : 'Tăng dần' ?>)
            </div>
        </div>
        <?php endif; ?>

        <?php if (empty($videos)): ?>
            <div class="empty-state">
                <div class="empty-icon">🎥</div>
                <h3>Chưa có video nào</h3>
                <p>Nhân vật này chưa có video nào được tạo</p>
            </div>
        <?php else: ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 1rem; margin-bottom: 2rem; padding: 0.5rem;" id="videoGrid">
                <?php foreach ($grouped_videos as $group): ?>
                    <?php 
                    $video = $group['representative'];
                    $video_count = count($group['videos']);
                    $video_ids = array_map(function($v) { return $v['id']; }, $group['videos']);
                    $video_ids_json = htmlspecialchars(json_encode($video_ids));
                    ?>
                    <div class="video-thumb-card">
                        <!-- Thumbnail Image -->
                        <?php if (!empty($video['input_image_url'])): ?>
                            <img src="<?= htmlspecialchars($video['input_image_url']) ?>" alt="Video thumbnail" style="width: 100%; height: 100%; object-fit: contain; background: #000;" loading="lazy">
                        <?php else: ?>
                            <video style="width: 100%; height: 100%; object-fit: contain; background: #000;" preload="metadata" muted playsinline>
                                <source src="<?= htmlspecialchars($video['video_url']) ?>#t=0.1" type="video/mp4">
                            </video>
                        <?php endif; ?>
                        
                        <!-- Simple Overlay -->
                        <div class="video-overlay-simple"></div>
                        
                        <!-- Play Button - Bottom Left -->
                        <?php if ($video_count > 1): ?>
                            <div class="video-play-corner" onclick="event.stopPropagation(); showVideoSelector(<?= $video_ids_json ?>)" title="<?= $video_count ?> videos available">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="white">
                                    <polygon points="8 5 19 12 8 19 8 5"/>
                                </svg>
                                <div class="video-count-badge"><?= $video_count ?></div>
                            </div>
                        <?php else: ?>
                            <div class="video-play-corner" onclick="event.stopPropagation(); openModal('<?= htmlspecialchars($video['id']) ?>')">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="white">
                                    <polygon points="8 5 19 12 8 19 8 5"/>
                                </svg>
                            </div>
                        <?php endif; ?>
                        
                        <!-- View Image Button - Bottom Right -->
                        <?php if (!empty($video['input_image_url'])): ?>
                            <div class="video-view-btn" onclick="event.stopPropagation(); openImageModal('<?= htmlspecialchars($video['input_image_url']) ?>')" title="View original image">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="white" stroke="white" stroke-width="2">
                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2" fill="none"/>
                                    <circle cx="8.5" cy="8.5" r="1.5" fill="white"/>
                                    <polyline points="21 15 16 10 5 21" fill="none"/>
                                </svg>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Duration Badge - Top Right -->
                        <?php if (!empty($video['duration'])): ?>
                            <div class="video-time-badge">
                                <?= $video['duration'] ?>s
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

        <?php endif; ?>
    </main>

    <!-- Character Media Modal -->
    <div id="characterMediaModal" class="modal" style="display: none;">
        <div class="modal-content" onclick="event.stopPropagation()">
            <button class="modal-close" onclick="closeCharacterMediaModal()">×</button>
            <div style="position: absolute; top: 2rem; left: 50%; transform: translateX(-50%); background: rgba(0, 0, 0, 0.7); backdrop-filter: blur(10px); padding: 0.5rem 1rem; border-radius: 8px; color: white; font-weight: 600; z-index: 11;" id="charMediaCounter">1 / 1</div>
            <button class="modal-nav prev" id="charModalPrev" onclick="navigateCharacterMedia(-1)">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M15 18l-6-6 6-6"/>
                </svg>
            </button>
            <img id="charModalImage" class="modal-video" src="" alt="Character media" style="max-width: 90vw; max-height: 90vh; object-fit: contain; display: none;">
            <video id="charModalVideo" class="modal-video" controls autoplay loop style="max-width: 90vw; max-height: 90vh; object-fit: contain; display: none;">
                <source src="" type="video/mp4">
            </video>
            <button class="modal-nav next" id="charModalNext" onclick="navigateCharacterMedia(1)">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 18l6-6-6-6"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- Video Selector Modal -->
    <div id="videoSelectorModal">
        <div class="video-selector-content" onclick="event.stopPropagation()">
            <div class="video-selector-header">
                <div class="video-selector-title">Chọn video để xem</div>
                <button class="video-selector-close" onclick="closeVideoSelector()">×</button>
            </div>
            <div class="video-selector-list" id="videoSelectorList"></div>
        </div>
    </div>

    <!-- Video Modal -->
    <div id="videoModal" class="modal" onclick="closeModal(event)">
        <div class="modal-content" onclick="event.stopPropagation()">
            <button class="modal-close" onclick="closeModal()">×</button>
            <button class="modal-info-btn" onclick="toggleInfo()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="16" x2="12" y2="12"/>
                    <line x1="12" y1="8" x2="12.01" y2="8"/>
                </svg>
                Info
            </button>
            <button class="modal-nav prev" id="modalPrevBtn" onclick="navigateVideo(-1)">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M15 18l-6-6 6-6"/>
                </svg>
            </button>
            <video id="modalVideo" class="modal-video" controls autoplay loop>
                <source id="modalVideoSource" src="" type="video/mp4">
            </video>
            <button class="modal-nav next" id="modalNextBtn" onclick="navigateVideo(1)">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 18l6-6-6-6"/>
                </svg>
            </button>
            <div id="modalInfo" class="modal-info">
                <div class="modal-info-header">
                    <div class="modal-info-title">Video Information</div>
                    <button class="modal-info-close" onclick="toggleInfo()">×</button>
                </div>
                <div id="modalInfoContent" class="modal-info-content"></div>
            </div>
        </div>
    </div>

    <script>
        // Store video data for modal
        let videoData = <?= json_encode($videos) ?>;
        let groupedVideos = <?= json_encode(array_values($grouped_videos)) ?>;
        let currentPage = <?= $page ?>;
        let hasMore = <?= $has_more ? 'true' : 'false' ?>;
        let isLoading = false;

        // Video selector functions
        function showVideoSelector(videoIds) {
            const modal = document.getElementById('videoSelectorModal');
            const list = document.getElementById('videoSelectorList');
            list.innerHTML = '';
            
            videoIds.forEach(videoId => {
                const video = videoData.find(v => v.id === videoId);
                if (!video) return;
                
                const item = document.createElement('div');
                item.className = 'video-selector-item';
                item.onclick = function() {
                    closeVideoSelector();
                    openModal(videoId);
                };
                
                const thumbnail = video.input_image_url ? 
                    `<img src="${escapeHtml(video.input_image_url)}" class="video-selector-thumbnail" alt="Video thumbnail">` :
                    `<div class="video-selector-thumbnail" style="background: #000;"></div>`;
                
                const action = video.action || video.custom_prompt || 'Video #' + video.id.substring(0, 8);
                const duration = video.duration ? `${video.duration}s` : '';
                const resolution = (video.width && video.height) ? `${video.width}×${video.height}` : '';
                const outfit = video.outfit ? `👗 ${video.outfit}` : '';
                
                item.innerHTML = `
                    ${thumbnail}
                    <div class="video-selector-info">
                        <div class="video-selector-action">${escapeHtml(action)}</div>
                        <div class="video-selector-details">
                            ${duration ? `<span>⏱️ ${duration}</span>` : ''}
                            ${resolution ? `<span>📐 ${resolution}</span>` : ''}
                            ${outfit ? `<span>${outfit}</span>` : ''}
                        </div>
                    </div>
                    <div class="video-selector-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="white">
                            <polygon points="8 5 19 12 8 19 8 5"/>
                        </svg>
                    </div>
                `;
                
                list.appendChild(item);
            });
            
            modal.classList.add('show');
            document.body.style.overflow = 'hidden';
        }
        
        function closeVideoSelector() {
            const modal = document.getElementById('videoSelectorModal');
            modal.classList.remove('show');
            document.body.style.overflow = '';
        }
        
        // Close on outside click
        document.getElementById('videoSelectorModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeVideoSelector();
            }
        });
        
        // Infinite scroll functionality
        function checkScroll() {
            if (isLoading || !hasMore) return;
            
            const scrollPosition = window.innerHeight + window.scrollY;
            const threshold = document.documentElement.scrollHeight - 500; // Load 500px before bottom
            
            if (scrollPosition >= threshold) {
                loadMoreVideos();
            }
        }
        
        function loadMoreVideos() {
            if (isLoading || !hasMore) return;
            
            isLoading = true;
            currentPage++;
            
            // Show loading indicator
            const loadingDiv = document.createElement('div');
            loadingDiv.id = 'loading-indicator';
            loadingDiv.style.cssText = 'text-align: center; padding: 2rem; color: var(--text-secondary);';
            loadingDiv.innerHTML = '<div style="display: inline-block; width: 40px; height: 40px; border: 4px solid rgba(139, 92, 246, 0.2); border-top-color: var(--accent); border-radius: 50%; animation: spin 1s linear infinite;"></div><style>@keyframes spin { to { transform: rotate(360deg); } }</style>';
            document.querySelector('.container').appendChild(loadingDiv);
            
            // Build query string with current filters
            const urlParams = new URLSearchParams(window.location.search);
            urlParams.set('page', currentPage);
            
            fetch('?' + urlParams.toString(), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                // Remove loading indicator
                const loading = document.getElementById('loading-indicator');
                if (loading) loading.remove();
                
                // Add new videos to array
                videoData = videoData.concat(data.videos);
                hasMore = data.has_more;
                
                // Add new grouped videos
                if (data.grouped_videos) {
                    groupedVideos = groupedVideos.concat(data.grouped_videos);
                }
                
                // Render new video groups
                const grid = document.getElementById('videoGrid');
                if (grid && data.grouped_videos) {
                    data.grouped_videos.forEach((group) => {
                        const videoHtml = createGroupedVideoCard(group);
                        grid.insertAdjacentHTML('beforeend', videoHtml);
                    });
                }
                
                isLoading = false;
                
                // Update results count
                const resultsCount = document.querySelector('.results-count strong');
                if (resultsCount) {
                    resultsCount.textContent = new Intl.NumberFormat().format(videoData.length);
                }
            })
            .catch(error => {
                console.error('Error loading more videos:', error);
                const loading = document.getElementById('loading-indicator');
                if (loading) loading.remove();
                isLoading = false;
            });
        }
        
        function createGroupedVideoCard(group) {
            const video = group.representative;
            const videoCount = group.videos.length;
            const videoIds = group.videos.map(v => v.id);
            
            const thumbnailSrc = video.input_image_url || '';
            const videoSrc = video.video_url || '';
            const duration = video.duration || '';
            
            let thumbnailHtml = '';
            if (thumbnailSrc) {
                thumbnailHtml = `<img src="${escapeHtml(thumbnailSrc)}" alt="Video thumbnail" style="width: 100%; height: 100%; object-fit: contain; background: #000;" loading="lazy">`;
            } else {
                thumbnailHtml = `<video style="width: 100%; height: 100%; object-fit: contain; background: #000;" preload="metadata" muted playsinline><source src="${escapeHtml(videoSrc)}#t=0.1" type="video/mp4"></video>`;
            }
            
            const viewImageBtn = thumbnailSrc ? `
                <div class="video-view-btn" onclick="event.stopPropagation(); openImageModal('${escapeHtml(thumbnailSrc)}')" title="View original image">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="white" stroke="white" stroke-width="2">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2" fill="none"/>
                        <circle cx="8.5" cy="8.5" r="1.5" fill="white"/>
                        <polyline points="21 15 16 10 5 21" fill="none"/>
                    </svg>
                </div>
            ` : '';
            
            let playButton = '';
            if (videoCount > 1) {
                playButton = `
                    <div class="video-play-corner" onclick="event.stopPropagation(); showVideoSelector(${JSON.stringify(videoIds)})" title="${videoCount} videos available">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="white">
                            <polygon points="8 5 19 12 8 19 8 5"/>
                        </svg>
                        <div class="video-count-badge">${videoCount}</div>
                    </div>
                `;
            } else {
                playButton = `
                    <div class="video-play-corner" onclick="event.stopPropagation(); openModal('${escapeHtml(video.id)}')">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="white">
                            <polygon points="8 5 19 12 8 19 8 5"/>
                        </svg>
                    </div>
                `;
            }
            
            return `
                <div class="video-thumb-card">
                    ${thumbnailHtml}
                    <div class="video-overlay-simple"></div>
                    ${playButton}
                    ${viewImageBtn}
                    ${duration ? `<div class="video-time-badge">${duration}s</div>` : ''}
                </div>
            `;
        }
        
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        // Attach scroll listener
        let scrollTimeout;
        window.addEventListener('scroll', function() {
            clearTimeout(scrollTimeout);
            scrollTimeout = setTimeout(checkScroll, 100);
        });
        
        // Check on page load in case content doesn't fill screen
        setTimeout(checkScroll, 500);

        // Modal functions
        function openModal(videoId) {
            const videoIndex = videoData.findIndex(v => v.id === videoId);
            if (videoIndex === -1) return;
            
            currentSlideshowIndex = videoIndex;
            const video = videoData[videoIndex];

            const modal = document.getElementById('videoModal');
            const modalVideo = document.getElementById('modalVideo');
            const modalVideoSource = document.getElementById('modalVideoSource');
            const modalInfo = document.getElementById('modalInfo');

            // Hide image if showing, show video
            const modalImage = document.getElementById('modalImage');
            if (modalImage) modalImage.style.display = 'none';
            modalVideo.style.display = 'block';

            // Reset video state
            modalVideo.pause();
            modalVideoSource.src = video.video_url;
            modalVideo.load();
            
            // Handle video load error
            modalVideo.onerror = function() {
                console.error('Error loading video:', video.video_url);
                alert('Không thể tải video. Vui lòng thử lại.');
                closeModal();
            };

            // Build info HTML - Display selected video fields only
            const modalInfoContent = document.getElementById('modalInfoContent');
            let infoHtml = '';
            
            // Define field labels in display order (only showing requested fields)
            const fieldLabels = {
                'original_prompt': 'Original Prompt',
                'action': 'Action',
                'custom_prompt': 'Custom Prompt',
                'video_url': 'Video URL',
                'input_image_url': 'Input Image URL',
                'outfit': 'Outfit',
                'background': 'Background',
                'pose': 'Pose'
            };
            
            // Iterate through all video properties
            for (const [key, label] of Object.entries(fieldLabels)) {
                const value = video[key];
                
                // Check if value exists and is not empty
                if (value !== null && value !== undefined && value !== '') {
                    let displayValue = value;
                    
                    // Format special fields
                    if (key === 'video_url' || key === 'input_image_url') {
                        displayValue = `<a href="${escapeHtml(value)}" target="_blank" style="color: var(--accent); word-break: break-all;">${escapeHtml(value)}</a>`;
                    } else if (key === 'is_enhanced') {
                        displayValue = value ? 'Yes' : 'No';
                    } else if (key === 'duration') {
                        displayValue = `${value}s`;
                    } else if (key === 'width' || key === 'height') {
                        // Skip individual width/height, show resolution instead
                        if (key === 'height' && video.width && video.height) {
                            infoHtml += `<div class="info-item"><div class="info-label">Resolution</div><div class="info-value">${video.width} × ${video.height}</div></div>`;
                        }
                        continue;
                    } else {
                        displayValue = escapeHtml(String(value));
                    }
                    
                    infoHtml += `<div class="info-item"><div class="info-label">${label}</div><div class="info-value">${displayValue}</div></div>`;
                }
            }
            
            // Show message if no data available
            if (infoHtml === '') {
                infoHtml = '<div class="info-item"><div class="info-label" style="text-align: center; color: var(--text-secondary);">No information available</div></div>';
            }
            
            modalInfoContent.innerHTML = infoHtml;

            modal.classList.add('show');
            document.body.style.overflow = 'hidden';
            updateNavigationButtons();
        }

        function closeModal(event) {
            if (event && event.target !== event.currentTarget && event.type !== 'click') {
                return;
            }
            
            const modal = document.getElementById('videoModal');
            const modalVideo = document.getElementById('modalVideo');
            
            modalVideo.pause();
            modal.classList.remove('show');
            document.body.style.overflow = '';
        }

        // Keyboard navigation
        document.addEventListener('keydown', (e) => {
            const modal = document.getElementById('videoModal');
            if (!modal.classList.contains('show')) return;

            switch(e.key) {
                case 'Escape':
                    closeModal();
                    stopSlideshow();
                    break;
                case 'ArrowLeft':
                    navigateVideo(-1);
                    break;
                case 'ArrowRight':
                    navigateVideo(1);
                    break;
                case ' ': // Spacebar
                    e.preventDefault();
                    const video = document.getElementById('modalVideo');
                    if (video.paused) {
                        video.play();
                    } else {
                        video.pause();
                    }
                    break;
            }
        });

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Toggle info panel
        function toggleInfo() {
            const modalInfo = document.getElementById('modalInfo');
            modalInfo.classList.toggle('show');
        }

        // Slideshow functionality
        let slideshowInterval = null;
        let currentSlideshowIndex = 0;

        function startSlideshow() {
            if (videoData.length === 0) return;
            
            // Stop existing slideshow if running
            stopSlideshow();
            
            currentSlideshowIndex = 0;
            openModal(videoData[currentSlideshowIndex].id);
            
            // Auto-advance based on video duration or default 8s
            const video = document.getElementById('modalVideo');
            
            const startInterval = () => {
                const duration = video.duration > 0 ? Math.min(video.duration * 1000, 30000) : 8000;
                slideshowInterval = setInterval(() => {
                    navigateVideo(1);
                }, duration + 1000); // Add 1s buffer
            };
            
            if (video.readyState >= 1) {
                startInterval();
            } else {
                video.addEventListener('loadedmetadata', startInterval, { once: true });
            }
        }

        function stopSlideshow() {
            if (slideshowInterval) {
                clearInterval(slideshowInterval);
                slideshowInterval = null;
            }
        }

        // Navigate between videos or images
        function navigateVideo(direction) {
            // Check if we're in image mode
            const modalImage = document.getElementById('modalImage');
            if (modalImage && modalImage.style.display !== 'none') {
                navigateImage(direction);
                return;
            }
            
            if (videoData.length === 0) return;
            
            currentSlideshowIndex += direction;
            
            // Loop around
            if (currentSlideshowIndex >= videoData.length) {
                currentSlideshowIndex = 0;
            } else if (currentSlideshowIndex < 0) {
                currentSlideshowIndex = videoData.length - 1;
            }
            
            openModal(videoData[currentSlideshowIndex].id);
            updateNavigationButtons();
        }

        // Navigate between images in slideshow
        let imageGallery = [];
        let currentImageIndex = 0;
        
        function navigateImage(direction) {
            if (imageGallery.length === 0) return;
            
            currentImageIndex += direction;
            
            // Loop around
            if (currentImageIndex >= imageGallery.length) {
                currentImageIndex = 0;
            } else if (currentImageIndex < 0) {
                currentImageIndex = imageGallery.length - 1;
            }
            
            showImageInModal(imageGallery[currentImageIndex]);
            updateNavigationButtons();
        }
        
        function showImageInModal(imageUrl) {
            const modalImage = document.getElementById('modalImage');
            const modalInfoContent = document.getElementById('modalInfoContent');
            
            modalImage.src = imageUrl;
            
            modalInfoContent.innerHTML = `
                <div class="modal-info-row">
                    <strong>Image ${currentImageIndex + 1} of ${imageGallery.length}</strong>
                </div>
                <div class="modal-info-row">
                    <strong>Image URL:</strong>
                    <span><a href="${escapeHtml(imageUrl)}" target="_blank" style="color: var(--accent); word-break: break-all;">${escapeHtml(imageUrl)}</a></span>
                </div>
            `;
        }

        // Update navigation button states
        function updateNavigationButtons() {
            const prevBtn = document.getElementById('modalPrevBtn');
            const nextBtn = document.getElementById('modalNextBtn');
            
            // Always enable buttons for looping
            prevBtn.disabled = false;
            nextBtn.disabled = false;
        }

        // Stop slideshow when closing modal
        const originalCloseModal = closeModal;
        closeModal = function(event) {
            stopSlideshow();
            originalCloseModal(event);
        };

        // Open image in modal (like slideshow)
        function openImageModal(imageUrl) {
            const modal = document.getElementById('videoModal');
            const modalVideo = document.getElementById('modalVideo');
            const modalVideoSource = document.getElementById('modalVideoSource');

            // Build image gallery from all videos with same or different images
            imageGallery = [];
            const uniqueImages = new Set();
            
            videoData.forEach(video => {
                if (video.input_image_url && !uniqueImages.has(video.input_image_url)) {
                    uniqueImages.add(video.input_image_url);
                    imageGallery.push(video.input_image_url);
                }
            });
            
            // Find current image index
            currentImageIndex = imageGallery.indexOf(imageUrl);
            if (currentImageIndex === -1) {
                currentImageIndex = 0;
                if (!imageGallery.includes(imageUrl)) {
                    imageGallery.unshift(imageUrl);
                }
            }

            // Hide video, show image instead
            modalVideo.style.display = 'none';
            
            // Create or update image element
            let modalImage = document.getElementById('modalImage');
            if (!modalImage) {
                modalImage = document.createElement('img');
                modalImage.id = 'modalImage';
                modalImage.style.cssText = 'max-width: 90vw; max-height: 90vh; object-fit: contain; border-radius: 8px;';
                modalVideo.parentNode.insertBefore(modalImage, modalVideo);
            }
            
            modalImage.style.display = 'block';
            showImageInModal(imageUrl);

            modal.classList.add('show');
            document.body.style.overflow = 'hidden';
            updateNavigationButtons();
        }

        // Filter panel functions
        function toggleFilters() {
            const content = document.getElementById('filterContent');
            const toggleText = document.getElementById('filterToggleText');
            
            if (content.classList.contains('collapsed')) {
                content.classList.remove('collapsed');
                toggleText.textContent = 'Ẩn bớt';
            } else {
                content.classList.add('collapsed');
                toggleText.textContent = 'Mở rộng';
            }
        }

        function resetFilters() {
            const characterId = new URLSearchParams(window.location.search).get('id');
            window.location.href = `?id=${encodeURIComponent(characterId)}`;
        }

        function removeFilter(filterName) {
            const form = document.getElementById('filterForm');
            const input = form.querySelector(`[name="${filterName}"]`);
            if (input) {
                input.value = '';
                form.submit();
            }
        }

        // Auto-collapse filters on mobile
        if (window.innerWidth <= 768) {
            const content = document.getElementById('filterContent');
            const toggleText = document.getElementById('filterToggleText');
            if (content && toggleText) {
                content.classList.add('collapsed');
                toggleText.textContent = 'Mở rộng';
            }
        }
    </script>
</body>
</html>
