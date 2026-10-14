<?php
// blog-engine.php - Blog Engine with Custom Placeholder Parser & CRUD
require_once __DIR__ . '/config.php';

if (!is_dir(BLOG_IMAGES_DIR)) {
    mkdir(BLOG_IMAGES_DIR, 0777, true);
}

/**
 * Parses custom blog formatting placeholders into semantic HTML.
 * Strictly avoids any emojis; uses crisp SVG icons for callouts and tips.
 */
function parseBlogPlaceholders($content, $imagesMap = []) {
    if (is_string($imagesMap)) {
        $imagesMap = json_decode($imagesMap, true) ?: [];
    }

    $text = trim($content);

    // 1. Divider
    $text = preg_replace('/\{\{\s*divider\s*\}\}/i', '<hr class="blog-divider">', $text);

    // 2. Title and Headings (both tag-pair {{ h1 }}..{{ /h1 }} and inline {{ h1: ... }})
    $text = preg_replace('/\{\{\s*title\s*\}\}([\s\S]*?)\{\{\s*\/title\s*\}\}/i', '<h1 class="blog-display-title">$1</h1>', $text);
    $text = preg_replace('/\{\{\s*title\s*:\s*([^}\n]+)\s*\}\}/i', '<h1 class="blog-display-title">$1</h1>', $text);

    for ($i = 1; $i <= 6; $i++) {
        $text = preg_replace('/\{\{\s*h' . $i . '\s*\}\}([\s\S]*?)\{\{\s*\/h' . $i . '\s*\}\}/i', '<h' . $i . ' class="blog-h' . $i . '">$1</h' . $i . '>', $text);
        $text = preg_replace('/\{\{\s*h' . $i . '\s*:\s*([^}\n]+)\s*\}\}/i', '<h' . $i . ' class="blog-h' . $i . '">$1</h' . $i . '>', $text);
    }

    // 3. Text formatting
    $text = preg_replace('/\{\{\s*(?:bold|b)\s*\}\}([\s\S]*?)\{\{\s*\/(?:bold|b)\s*\}\}/i', '<strong class="blog-strong">$1</strong>', $text);
    $text = preg_replace('/\{\{\s*(?:italic|i)\s*\}\}([\s\S]*?)\{\{\s*\/(?:italic|i)\s*\}\}/i', '<em class="blog-em">$1</em>', $text);
    $text = preg_replace('/\{\{\s*(?:underline|u)\s*\}\}([\s\S]*?)\{\{\s*\/(?:underline|u)\s*\}\}/i', '<u class="blog-underline">$1</u>', $text);
    $text = preg_replace('/\{\{\s*highlight\s*\}\}([\s\S]*?)\{\{\s*\/highlight\s*\}\}/i', '<mark class="blog-mark">$1</mark>', $text);

    // 4. Quotes and Code
    $text = preg_replace_callback('/\{\{\s*quote\s*\}\}([\s\S]*?)\{\{\s*\/quote\s*\}\}/i', function($m) {
        $inner = trim($m[1]);
        $svgQuote = '<svg class="blog-quote-icon" viewBox="0 0 24 24" width="28" height="28" fill="currentColor" aria-hidden="true"><path d="M6 17h3l2-4V7H5v6h3zm8 0h3l2-4V7h-6v6h3z"/></svg>';
        return '<blockquote class="blog-quote">' . $svgQuote . '<div class="blog-quote-text">' . $inner . '</div></blockquote>';
    }, $text);

    $text = preg_replace_callback('/\{\{\s*code\s*\}\}([\s\S]*?)\{\{\s*\/code\s*\}\}/i', function($m) {
        return '<pre class="blog-code-block"><code>' . htmlspecialchars($m[1]) . '</code></pre>';
    }, $text);

    // 5. Callouts & Tips (Using SVGs - Zero Emojis)
    $text = preg_replace_callback('/\{\{\s*callout\s*:\s*([^|{}]+)(?:\|\s*([^}]+))?\}\}/i', function($m) {
        $title = trim($m[1]);
        $body = isset($m[2]) ? trim($m[2]) : '';
        $svgInfo = '<svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor" aria-hidden="true"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>';
        return '<aside class="blog-callout"><div class="blog-callout-icon">' . $svgInfo . '</div><div class="blog-callout-body"><h4>' . htmlspecialchars($title) . '</h4><p>' . $body . '</p></div></aside>';
    }, $text);

    $text = preg_replace_callback('/\{\{\s*callout\s*\}\}([\s\S]*?)\{\{\s*\/callout\s*\}\}/i', function($m) {
        $body = trim($m[1]);
        $svgInfo = '<svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor" aria-hidden="true"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>';
        return '<aside class="blog-callout"><div class="blog-callout-icon">' . $svgInfo . '</div><div class="blog-callout-body"><p>' . $body . '</p></div></aside>';
    }, $text);

    $text = preg_replace_callback('/\{\{\s*tip\s*\}\}([\s\S]*?)\{\{\s*\/tip\s*\}\}/i', function($m) {
        $inner = trim($m[1]);
        $svgTip = '<svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor" aria-hidden="true"><path d="M9 21c0 .55.45 1 1 1h4c.55 0 1-.45 1-1v-1H9v1zm3-19C8.14 2 5 5.14 5 9c0 2.38 1.19 4.47 3 5.74V17c0 .55.45 1 1 1h6c.55 0 1-.45 1-1v-2.26c1.81-1.27 3-3.36 3-5.74 0-3.86-3.14-7-7-7z"/></svg>';
        return '<div class="blog-tip-box"><div class="blog-tip-icon">' . $svgTip . '</div><div class="blog-tip-body">' . $inner . '</div></div>';
    }, $text);

    // 6. Interactive CTA Buttons & Links
    $text = preg_replace_callback('/\{\{\s*button\s*:\s*([^|{}]+)(?:\|\s*([^}]+))?\}\}/i', function($m) {
        $label = trim($m[1]);
        $url = isset($m[2]) ? trim($m[2]) : '#';
        $arrowSvg = '<svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor" aria-hidden="true"><path d="M12 4l-1.41 1.41L16.17 11H4v2h12.17l-5.58 5.59L12 20l8-8z"/></svg>';
        return '<div class="blog-btn-wrap"><a href="' . htmlspecialchars($url) . '" class="blog-action-btn" target="_blank" rel="noopener">' . htmlspecialchars($label) . ' ' . $arrowSvg . '</a></div>';
    }, $text);

    $text = preg_replace_callback('/\{\{\s*link\s*:\s*([^|{}]+)(?:\|\s*([^}]+))?\}\}/i', function($m) {
        $label = trim($m[1]);
        $url = isset($m[2]) ? trim($m[2]) : '#';
        return '<a href="' . htmlspecialchars($url) . '" class="blog-inline-link" target="_blank" rel="noopener">' . htmlspecialchars($label) . '</a>';
    }, $text);

    // 7. External / Online Images: {{ image_link: URL }} or {{ image_link: URL | Caption }}
    $text = preg_replace_callback('/\{\{\s*image_link\s*:\s*([^|{}]+)(?:\|\s*([^}]+))?\}\}/i', function($m) {
        $url = trim($m[1]);
        $caption = isset($m[2]) ? trim($m[2]) : '';
        $capHtml = $caption ? '<figcaption class="blog-fig-caption">' . htmlspecialchars($caption) . '</figcaption>' : '';
        return '<figure class="blog-figure"><img src="' . htmlspecialchars($url) . '" alt="' . htmlspecialchars($caption ?: 'Blog visual') . '" class="blog-responsive-img" loading="lazy">' . $capHtml . '</figure>';
    }, $text);

    // 8. Auto-generated Uploaded Images: {{ image_1 }}, {{ image_2 }}, etc. (with optional caption {{ image_1 | Caption }})
    $text = preg_replace_callback('/\{\{\s*(image_\d+)(?:\s*\|\s*([^}]+))?\s*\}\}/i', function($m) use ($imagesMap) {
        $key = strtolower(trim($m[1]));
        $caption = isset($m[2]) ? trim($m[2]) : '';
        $src = $imagesMap[$key] ?? null;

        if (!$src) {
            return '<div class="blog-img-missing"><span>[Image placeholder ' . htmlspecialchars($key) . ' not attached]</span></div>';
        }

        // Ensure root-relative or absolute URL so it resolves on /blog/{slug}
        if (!preg_match('#^(https?://|/)#i', $src)) {
            $src = '/' . ltrim($src, '/');
        }

        $capHtml = $caption ? '<figcaption class="blog-fig-caption">' . htmlspecialchars($caption) . '</figcaption>' : '';
        return '<figure class="blog-figure"><img src="' . htmlspecialchars($src) . '" alt="' . htmlspecialchars($caption ?: 'Blog illustration') . '" class="blog-responsive-img" loading="lazy">' . $capHtml . '</figure>';
    }, $text);

    // 9. Generic single {{ image }} or {{ image | Caption }}
    $text = preg_replace_callback('/\{\{\s*image(?:\s*\|\s*([^}]+))?\s*\}\}/i', function($m) use ($imagesMap) {
        $caption = isset($m[1]) ? trim($m[1]) : '';
        $src = !empty($imagesMap) ? reset($imagesMap) : null;
        if (!$src) {
            return '<div class="blog-img-missing"><span>[Image placeholder not attached]</span></div>';
        }

        // Ensure root-relative or absolute URL so it resolves on /blog/{slug}
        if (!preg_match('#^(https?://|/)#i', $src)) {
            $src = '/' . ltrim($src, '/');
        }

        $capHtml = $caption ? '<figcaption class="blog-fig-caption">' . htmlspecialchars($caption) . '</figcaption>' : '';
        return '<figure class="blog-figure"><img src="' . htmlspecialchars($src) . '" alt="' . htmlspecialchars($caption ?: 'Blog visual') . '" class="blog-responsive-img" loading="lazy">' . $capHtml . '</figure>';
    }, $text);

    // 10. Bullets & Numbered Lists
    // Group consecutive bullet items into <ul>
    $text = preg_replace_callback('/(?:\{\{\s*(?:bullet|li)\s*\}\}([\s\S]*?)\{\{\s*\/(?:bullet|li)\s*\}\}\s*)+/i', function($m) {
        preg_match_all('/\{\{\s*(?:bullet|li)\s*\}\}([\s\S]*?)\{\{\s*\/(?:bullet|li)\s*\}\}/i', $m[0], $items);
        $out = '<ul class="blog-bullet-list">';
        foreach ($items[1] as $item) {
            $out .= '<li>' . trim($item) . '</li>';
        }
        $out .= '</ul>';
        return $out;
    }, $text);

    // Group consecutive numbered items into <ol>
    $text = preg_replace_callback('/(?:\{\{\s*number\s*\}\}([\s\S]*?)\{\{\s*\/number\s*\}\}\s*)+/i', function($m) {
        preg_match_all('/\{\{\s*number\s*\}\}([\s\S]*?)\{\{\s*\/number\s*\}\}/i', $m[0], $items);
        $out = '<ol class="blog-ordered-list">';
        foreach ($items[1] as $item) {
            $out .= '<li>' . trim($item) . '</li>';
        }
        $out .= '</ol>';
        return $out;
    }, $text);

    // 11. Convert paragraphs for non-block segments
    $blocks = preg_split('/\n\s*\n/', $text);
    $processed = [];

    foreach ($blocks as $block) {
        $trimmed = trim($block);
        if ($trimmed === '') continue;

        // Check if block already starts with block-level HTML tags
        if (preg_match('/^<(?:h[1-6]|ul|ol|blockquote|pre|figure|aside|div|hr|table|section)/i', $trimmed)) {
            $processed[] = $trimmed;
        } else {
            // Replace single newlines with <br> inside regular paragraph
            $pContent = nl2br($trimmed);
            $processed[] = '<p class="blog-p">' . $pContent . '</p>';
        }
    }

    return implode("\n\n", $processed);
}

/**
 * Generates an SEO-friendly slug from a title string.
 */
function createBlogSlug($title, $id = null) {
    $slug = strtolower(trim($title));
    $slug = preg_replace('/[^a-z0-9-]+/', '-', $slug);
    $slug = trim($slug, '-');
    if (empty($slug)) $slug = 'post';

    $db = getDB();
    $uniqueSlug = $slug;
    $count = 1;

    while (true) {
        $stmt = $db->prepare("SELECT id FROM blogs WHERE slug = ? LIMIT 1");
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
 * Fetch all blogs.
 */
function get_all_blogs($only_published = false) {
    $db = getDB();
    if ($only_published) {
        $stmt = $db->query("SELECT * FROM blogs WHERE status = 'published' ORDER BY created_at DESC, id DESC");
    } else {
        $stmt = $db->query("SELECT * FROM blogs ORDER BY created_at DESC, id DESC");
    }
    return $stmt->fetchAll() ?: [];
}

/**
 * Fetch a single blog by slug.
 */
function get_blog_by_slug($slug) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM blogs WHERE slug = ? LIMIT 1");
    $stmt->execute([$slug]);
    return $stmt->fetch() ?: null;
}

/**
 * Fetch a single blog by ID.
 */
function get_blog_by_id($id) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM blogs WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

/**
 * Save (create or update) a blog post.
 */
function save_blog_post($data) {
    $db = getDB();
    $id = !empty($data['id']) ? (int)$data['id'] : null;
    $title = trim($data['title'] ?? '');
    $author = trim($data['author'] ?? 'SmarTech Team') ?: 'SmarTech Team';
    $category = trim($data['category'] ?? 'Technology') ?: 'Technology';
    $meta_description = trim($data['meta_description'] ?? '');
    $keywords = trim($data['keywords'] ?? '');
    $featured_image = trim($data['featured_image'] ?? '');
    $content = trim($data['content'] ?? '');
    $images_json = is_array($data['images_json'] ?? null) ? json_encode($data['images_json']) : ($data['images_json'] ?? '{}');
    $status = in_array($data['status'] ?? '', ['published', 'draft']) ? $data['status'] : 'published';
    $now = date('Y-m-d H:i:s');

    if (empty($title)) {
        throw new InvalidArgumentException('Blog title is required.');
    }
    if (empty($content)) {
        throw new InvalidArgumentException('Blog content is required.');
    }

    $customSlug = trim($data['slug'] ?? '');
    $slug = !empty($customSlug) ? createBlogSlug($customSlug, $id) : createBlogSlug($title, $id);

    if ($id) {
        $stmt = $db->prepare("
            UPDATE blogs 
            SET title = ?, slug = ?, meta_description = ?, keywords = ?, author = ?, category = ?, featured_image = ?, content = ?, images_json = ?, status = ?, updated_at = ?
            WHERE id = ?
        ");
        $stmt->execute([$title, $slug, $meta_description, $keywords, $author, $category, $featured_image, $content, $images_json, $status, $now, $id]);
        return $id;
    } else {
        $stmt = $db->prepare("
            INSERT INTO blogs (title, slug, meta_description, keywords, author, category, featured_image, content, images_json, status, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$title, $slug, $meta_description, $keywords, $author, $category, $featured_image, $content, $images_json, $status, $now, $now]);
        return $db->lastInsertId();
    }
}

/**
 * Delete a blog post.
 */
function delete_blog_post($id) {
    $db = getDB();
    $stmt = $db->prepare("DELETE FROM blogs WHERE id = ?");
    $stmt->execute([$id]);
}

/**
 * Handle image upload for blogs.
 */
function handle_blog_image_upload($file) {
    if (!$file || empty($file['name']) || empty($file['tmp_name'])) {
        return null;
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'svg', 'gif'];
    if (!in_array($ext, $allowed)) {
        throw new InvalidArgumentException('Invalid image extension. Allowed: jpg, jpeg, png, webp, svg, gif');
    }

    $safeName = 'blog_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
    $targetPath = BLOG_IMAGES_DIR . '/' . $safeName;

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        throw new RuntimeException('Failed to move uploaded image.');
    }

    return '/assets/blog_images/' . $safeName;
}

