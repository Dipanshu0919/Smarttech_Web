<?php
// case-study-engine.php - Case Study Engine & Database Layer
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/blog-engine.php';

if (!is_dir(CASE_STUDY_IMAGES_DIR)) {
    mkdir(CASE_STUDY_IMAGES_DIR, 0777, true);
}

/**
 * Parses custom formatting placeholders into semantic HTML.
 * Supports all blog placeholders plus specialized case study blocks (metric stats, challenge/solution/results).
 * Zero emojis - uses clean, modern SVG icons.
 */
function parseCaseStudyPlaceholders($content, $imagesMap = []) {
    if (is_string($imagesMap)) {
        $imagesMap = json_decode($imagesMap, true) ?: [];
    }

    $text = trim($content);

    // 1. Metric / Stat Card: {{ stat: +180% | Direct Bookings Increased }}
    $text = preg_replace_callback('/\{\{\s*stat\s*:\s*([^|{}]+)\|\s*([^}]+)\s*\}\}/i', function($m) {
        $val = htmlspecialchars(trim($m[1]));
        $desc = htmlspecialchars(trim($m[2]));
        return '<div class="cs-stat-card"><div class="cs-stat-value">' . $val . '</div><div class="cs-stat-label">' . $desc . '</div></div>';
    }, $text);

    // Group multiple consecutive stat cards into a grid
    $text = preg_replace_callback('/(?:<div class="cs-stat-card">[\s\S]*?<\/div><\/div>\s*){2,}/i', function($m) {
        return '<div class="cs-stat-grid">' . trim($m[0]) . '</div>';
    }, $text);

    // 2. Challenge / Solution / Result Callout blocks
    $text = preg_replace_callback('/\{\{\s*challenge\s*\}\}([\s\S]*?)\{\{\s*\/challenge\s*\}\}/i', function($m) {
        $inner = trim($m[1]);
        $icon = '<svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor" aria-hidden="true"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>';
        return '<div class="cs-phase-box cs-phase-challenge"><div class="cs-phase-header">' . $icon . '<span>The Challenge</span></div><div class="cs-phase-body">' . $inner . '</div></div>';
    }, $text);

    $text = preg_replace_callback('/\{\{\s*solution\s*\}\}([\s\S]*?)\{\{\s*\/solution\s*\}\}/i', function($m) {
        $inner = trim($m[1]);
        $icon = '<svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor" aria-hidden="true"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>';
        return '<div class="cs-phase-box cs-phase-solution"><div class="cs-phase-header">' . $icon . '<span>The Solution</span></div><div class="cs-phase-body">' . $inner . '</div></div>';
    }, $text);

    $text = preg_replace_callback('/\{\{\s*results\s*\}\}([\s\S]*?)\{\{\s*\/results\s*\}\}/i', function($m) {
        $inner = trim($m[1]);
        $icon = '<svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor" aria-hidden="true"><path d="M16 6l2.29 2.29-4.88 4.88-4-4L2 16.59 3.41 18l6-6 4 4 6.3-6.29L22 12V6z"/></svg>';
        return '<div class="cs-phase-box cs-phase-results"><div class="cs-phase-header">' . $icon . '<span>The Impact &amp; Results</span></div><div class="cs-phase-body">' . $inner . '</div></div>';
    }, $text);

    // 3. Reuse the comprehensive blog placeholder parser for headings, quotes, callouts, lists, images, buttons
    return parseBlogPlaceholders($text, $imagesMap);
}

/**
 * Generates an SEO-friendly slug from a title string.
 */
function createCaseStudySlug($title, $id = null) {
    $slug = strtolower(trim($title));
    $slug = preg_replace('/[^a-z0-9-]+/', '-', $slug);
    $slug = trim($slug, '-');
    if (empty($slug)) $slug = 'case-study';

    $db = getDB();
    $uniqueSlug = $slug;
    $count = 1;

    while (true) {
        $stmt = $db->prepare("SELECT id FROM case_studies WHERE slug = ? LIMIT 1");
        $stmt->execute([$uniqueSlug]);
        $row = $stmt->fetch();
        if (!$row || ($id !== null && (string)$row['id'] === (string)$id)) {
            break;
        }
        $uniqueSlug = $slug . '-' . (++$count);
    }

    return $uniqueSlug;
}

/**
 * Fetch all case studies.
 */
function get_all_case_studies($only_published = false) {
    $db = getDB();
    if ($only_published) {
        $stmt = $db->query("SELECT * FROM case_studies WHERE status = 'published' ORDER BY created_at DESC, id DESC");
    } else {
        $stmt = $db->query("SELECT * FROM case_studies ORDER BY created_at DESC, id DESC");
    }
    return $stmt->fetchAll() ?: [];
}

/**
 * Fetch a single case study by slug.
 */
function get_case_study_by_slug($slug) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM case_studies WHERE slug = ? LIMIT 1");
    $stmt->execute([$slug]);
    return $stmt->fetch() ?: null;
}

/**
 * Fetch a single case study by ID.
 */
function get_case_study_by_id($id) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM case_studies WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

/**
 * Get adjacent (previous and next) case studies for single-post navigation.
 * Returns ['prev' => $prevRow, 'next' => $nextRow].
 */
function get_adjacent_case_studies($current_id, $only_published = true) {
    $all = get_all_case_studies($only_published);
    $currentIndex = null;

    foreach ($all as $idx => $item) {
        if ((string)$item['id'] === (string)$current_id) {
            $currentIndex = $idx;
            break;
        }
    }

    if ($currentIndex === null) {
        return ['prev' => null, 'next' => null];
    }

    // Previous case study in sequence (earlier / next older or next item in sequence)
    $prev = ($currentIndex > 0) ? $all[$currentIndex - 1] : null;
    // Next case study in sequence (later in sequence)
    $next = ($currentIndex < count($all) - 1) ? $all[$currentIndex + 1] : null;

    return ['prev' => $prev, 'next' => $next];
}

/**
 * Save (create or update) a case study.
 */
function save_case_study($data) {
    $db = getDB();
    $id = !empty($data['id']) ? (int)$data['id'] : null;
    $title = trim($data['title'] ?? '');
    $client_name = trim($data['client_name'] ?? '');
    $category = trim($data['category'] ?? 'Technology') ?: 'Technology';
    $author = trim($data['author'] ?? 'SmarTech Team') ?: 'SmarTech Team';
    $meta_description = trim($data['meta_description'] ?? '');
    $keywords = trim($data['keywords'] ?? '');
    $featured_image = trim($data['featured_image'] ?? '');
    $content = trim($data['content'] ?? '');
    $images_json = is_array($data['images_json'] ?? null) ? json_encode($data['images_json']) : ($data['images_json'] ?? '{}');
    $status = in_array($data['status'] ?? '', ['published', 'draft']) ? $data['status'] : 'published';
    $now = date('Y-m-d H:i:s');

    if (empty($title)) {
        throw new InvalidArgumentException('Case study title is required.');
    }
    if (empty($content)) {
        throw new InvalidArgumentException('Case study content is required.');
    }

    $customSlug = trim($data['slug'] ?? '');
    $slug = !empty($customSlug) ? createCaseStudySlug($customSlug, $id) : createCaseStudySlug($title, $id);

    if ($id) {
        $stmt = $db->prepare("
            UPDATE case_studies 
            SET title = ?, slug = ?, client_name = ?, meta_description = ?, keywords = ?, author = ?, category = ?, featured_image = ?, content = ?, images_json = ?, status = ?, updated_at = ?
            WHERE id = ?
        ");
        $stmt->execute([$title, $slug, $client_name, $meta_description, $keywords, $author, $category, $featured_image, $content, $images_json, $status, $now, $id]);
        return $id;
    } else {
        $stmt = $db->prepare("
            INSERT INTO case_studies (title, slug, client_name, meta_description, keywords, author, category, featured_image, content, images_json, status, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$title, $slug, $client_name, $meta_description, $keywords, $author, $category, $featured_image, $content, $images_json, $status, $now, $now]);
        return $db->lastInsertId();
    }
}

/**
 * Delete a case study.
 */
function delete_case_study($id) {
    $db = getDB();
    $stmt = $db->prepare("DELETE FROM case_studies WHERE id = ?");
    $stmt->execute([$id]);
}

/**
 * Handle image uploads for case studies.
 */
function handle_case_study_image_upload($file) {
    if (!$file || empty($file['name']) || empty($file['tmp_name'])) {
        return null;
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'svg', 'gif'];
    if (!in_array($ext, $allowed)) {
        throw new InvalidArgumentException('Invalid image extension. Allowed: jpg, jpeg, png, webp, svg, gif');
    }

    $safeName = 'cs_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
    $targetPath = CASE_STUDY_IMAGES_DIR . '/' . $safeName;

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        throw new RuntimeException('Failed to move uploaded case study image.');
    }

    return '/assets/case_study_images/' . $safeName;
}

/**
 * Seeds default high-value case studies if table is currently empty.
 */
function seed_default_case_studies_if_empty() {
    // Disabled: Do not auto-seed so deleted case studies stay deleted
    return;
}

