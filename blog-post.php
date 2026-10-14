<?php
require_once 'config.php';
require_once 'blog-engine.php';

$slug = $_GET['slug'] ?? '';
$id = $_GET['id'] ?? null;

$post = null;
if (!empty($slug)) {
    $post = get_blog_by_slug($slug);
} elseif (!empty($id)) {
    $post = get_blog_by_id($id);
}

// Fetch recent posts for bottom recommendations
$allBlogs = get_all_blogs(true);
$recentBlogs = array_filter($allBlogs, function($b) use ($post) {
    return !$post || (string)$b['id'] !== (string)$post['id'];
});
$recentBlogs = array_slice($recentBlogs, 0, 3);

$wordCount = $post ? str_word_count(strip_tags($post['content'])) : 0;
$readTime = max(1, ceil($wordCount / 200));
$postDate = $post && !empty($post['created_at']) ? date('M d, Y', strtotime($post['created_at'])) : date('M d, Y');
$shareUrl = $post ? 'https://smartechsolutions.in/blog/' . htmlspecialchars($post['slug']) : 'https://smartechsolutions.in/blog.html';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-V67RXH5F50"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());

      gtag('config', 'G-V67RXH5F50');
    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $post ? htmlspecialchars($post['title']) . ' | SmarTech Solutions' : 'Article Not Found | SmarTech Solutions' ?></title>
    <meta name="description" content="<?= $post ? htmlspecialchars($post['meta_description'] ?: substr(strip_tags($post['content']), 0, 160)) : 'Article on SmarTech Solutions' ?>">
    <?php if ($post && !empty($post['keywords'])): ?>
    <meta name="keywords" content="<?= htmlspecialchars($post['keywords']) ?>">
    <?php endif; ?>
    <meta name="author" content="<?= $post ? htmlspecialchars($post['author'] ?: 'SmarTech Team') : 'SmarTech Solutions' ?>">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    <link rel="canonical" href="<?= $shareUrl ?>">
    <link rel="icon" type="image/png" href="/assets/smarttechlogo.png">

    <!-- Open Graph -->
    <meta property="og:type" content="article">
    <meta property="og:url" content="<?= $shareUrl ?>">
    <meta property="og:title" content="<?= $post ? htmlspecialchars($post['title']) : 'Blog Article' ?>">
    <meta property="og:description" content="<?= $post ? htmlspecialchars($post['meta_description'] ?: substr(strip_tags($post['content']), 0, 160)) : '' ?>">
    <meta property="og:image" content="<?= $post && !empty($post['featured_image']) ? htmlspecialchars($post['featured_image']) : 'https://smartechsolutions.in/assets/smarttechlogo.png' ?>">
    <meta property="og:site_name" content="SmarTech Solutions">

    <!-- Twitter Cards -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="<?= $shareUrl ?>">
    <meta name="twitter:title" content="<?= $post ? htmlspecialchars($post['title']) : 'Blog Article' ?>">
    <meta name="twitter:description" content="<?= $post ? htmlspecialchars($post['meta_description'] ?: substr(strip_tags($post['content']), 0, 160)) : '' ?>">
    <meta name="twitter:image" content="<?= $post && !empty($post['featured_image']) ? htmlspecialchars($post['featured_image']) : 'https://smartechsolutions.in/assets/smarttechlogo.png' ?>">

    <?php if ($post): ?>
    <!-- Structured Data (JSON-LD) -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "BlogPosting",
        "headline": "<?= addslashes($post['title']) ?>",
        "description": "<?= addslashes($post['meta_description'] ?: '') ?>",
        <?php if (!empty($post['keywords'])): ?>
        "keywords": "<?= addslashes($post['keywords']) ?>",
        <?php endif; ?>
        "image": "<?= addslashes($post['featured_image'] ?: 'https://smartechsolutions.in/assets/smarttechlogo.png') ?>",
        "author": {
            "@type": "Organization",
            "name": "<?= addslashes($post['author'] ?: 'SmarTech Team') ?>"
        },
        "publisher": {
            "@type": "Organization",
            "name": "SmarTech Solutions",
            "logo": {
                "@type": "ImageObject",
                "url": "https://smartechsolutions.in/assets/smarttechlogo.png"
            }
        },
        "datePublished": "<?= $post['created_at'] ?>",
        "dateModified": "<?= $post['updated_at'] ?>"
    }
    </script>
    <?php endif; ?>

    <script src="https://unpkg.com/htmx.org@1.9.12"></script>

    <style>
        :root {
            --green-dark: #0f381c;
            --green-main: #228b22;
            --green-light: #4cbb17;
            --white: #ffffff;
            --text-dark: #203026;
            --text-soft: #5a6a60;
            --bg-light: #f4faf4;
            --surface: #ffffff;
            --border: rgba(34, 139, 34, 0.12);
            --shadow: 0 16px 40px rgba(15, 56, 28, 0.08);
        }

        * { box-sizing: border-box; }

        body, html {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bg-light);
            color: var(--text-dark);
            overflow-x: hidden;
            scroll-behavior: smooth;
        }

        main {
            margin-left: 250px;
            padding: 2.5rem 2.5rem 5rem;
            position: relative;
            min-height: 100vh;
            background-color: var(--bg-light);
        }

        @media (max-width: 992px) {
            main {
                margin-left: 0;
                padding: 1.5rem 1rem 3.5rem;
                padding-top: 85px;
            }
        }

        .article-container {
            max-width: 960px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            width: 100%;
        }

        /* --- Breadcrumb Bar --- */
        .breadcrumb-bar {
            display: flex;
            flex-direction: row !important;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.88rem;
            color: var(--text-soft);
            flex-wrap: wrap;
            padding: 0.2rem 0;
            width: 100%;
        }

        .breadcrumb-bar a {
            color: var(--green-dark);
            text-decoration: none;
            font-weight: 700;
        }

        .breadcrumb-bar a:hover {
            text-decoration: underline;
        }

        .breadcrumb-bar svg {
            width: 14px;
            height: 14px;
            fill: var(--text-soft);
            flex-shrink: 0;
        }

        /* --- Article Card Wrap --- */
        .article-card {
            background: var(--surface);
            border-radius: 28px;
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
            padding: 2.8rem 3rem;
            overflow: hidden;
            width: 100%;
        }

        .article-header {
            display: flex;
            flex-direction: column;
            gap: 1.1rem;
            margin-bottom: 2rem;
            border-bottom: 1px solid rgba(34, 139, 34, 0.12);
            padding-bottom: 1.8rem;
            position: static !important;
            width: 100% !important;
            background: transparent !important;
            box-shadow: none !important;
            z-index: auto !important;
        }

        .category-pill {
            display: inline-flex;
            align-self: flex-start;
            background: rgba(34, 139, 34, 0.1);
            color: var(--green-dark);
            padding: 0.4rem 1rem;
            border-radius: 999px;
            font-weight: 800;
            font-size: 0.84rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .article-title {
            margin: 0;
            font-size: clamp(2rem, 3.6vw, 2.8rem);
            color: var(--green-dark);
            line-height: 1.22;
            letter-spacing: -0.03em;
            font-weight: 800;
        }

        .meta-strip {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            color: var(--text-soft);
            font-size: 0.95rem;
            flex-wrap: wrap;
        }

        .meta-item {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            font-weight: 600;
        }

        .meta-item svg {
            width: 18px;
            height: 18px;
            fill: var(--green-main);
            flex-shrink: 0;
        }

        .featured-banner {
            width: 100%;
            max-height: 480px;
            border-radius: 20px;
            overflow: hidden;
            margin-bottom: 2.2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06);
        }

        .featured-banner img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        /* --- Parsed Content Typography & Elements --- */
        .article-content {
            font-size: 1.1rem;
            line-height: 1.8;
            color: var(--text-dark);
        }

        .article-content .blog-p {
            margin: 1.4rem 0;
        }

        .article-content .blog-display-title {
            font-size: clamp(1.8rem, 3vw, 2.4rem);
            color: var(--green-dark);
            margin: 2.2rem 0 1rem;
            line-height: 1.25;
            font-weight: 800;
        }

        .article-content .blog-h1 { font-size: 2.2rem; color: var(--green-dark); margin: 2.2rem 0 0.8rem; font-weight: 800; }
        .article-content .blog-h2 { font-size: 1.8rem; color: var(--green-dark); margin: 2rem 0 0.8rem; font-weight: 800; }
        .article-content .blog-h3 { font-size: 1.5rem; color: var(--green-dark); margin: 1.8rem 0 0.6rem; font-weight: 700; }
        .article-content .blog-h4 { font-size: 1.3rem; color: var(--green-dark); margin: 1.5rem 0 0.5rem; font-weight: 700; }
        .article-content .blog-h5 { font-size: 1.15rem; color: var(--green-dark); margin: 1.3rem 0 0.5rem; font-weight: 700; }
        .article-content .blog-h6 { font-size: 1.05rem; color: var(--green-dark); margin: 1.2rem 0 0.4rem; font-weight: 700; }

        .article-content .blog-strong { font-weight: 700; color: var(--green-dark); }
        .article-content .blog-em { font-style: italic; }
        .article-content .blog-underline { text-decoration: underline; }
        .article-content .blog-mark { background: #e8f5e9; color: var(--green-dark); padding: 0.15rem 0.45rem; border-radius: 4px; }

        .article-content .blog-quote {
            display: flex;
            gap: 1rem;
            background: #f4faf4;
            border-left: 4px solid var(--green-main);
            border-radius: 0 18px 18px 0;
            padding: 1.4rem 1.8rem;
            margin: 2rem 0;
            color: var(--green-dark);
            font-style: italic;
        }

        .article-content .blog-quote svg {
            flex-shrink: 0;
            fill: var(--green-main);
        }

        .article-content .blog-quote-text {
            font-size: 1.15rem;
            line-height: 1.7;
        }

        .article-content .blog-code-block {
            background: #101c13;
            color: #8dfd98;
            padding: 1.2rem 1.5rem;
            border-radius: 16px;
            overflow-x: auto;
            font-family: 'Consolas', 'Courier New', monospace;
            font-size: 0.95rem;
            margin: 1.8rem 0;
            border: 1px solid rgba(34, 139, 34, 0.2);
        }

        .article-content .blog-callout {
            display: flex;
            gap: 1.2rem;
            background: #eef8ee;
            border: 1px solid rgba(34, 139, 34, 0.22);
            border-radius: 18px;
            padding: 1.4rem 1.6rem;
            margin: 2rem 0;
        }

        .article-content .blog-callout-icon {
            flex-shrink: 0;
            color: var(--green-main);
            margin-top: 2px;
        }

        .article-content .blog-callout-body h4 {
            margin: 0 0 0.35rem;
            color: var(--green-dark);
            font-size: 1.15rem;
        }

        .article-content .blog-callout-body p {
            margin: 0;
            color: var(--text-dark);
            line-height: 1.65;
        }

        .article-content .blog-tip-box {
            display: flex;
            gap: 1rem;
            background: #fffbe8;
            border: 1px solid rgba(220, 180, 0, 0.35);
            border-radius: 16px;
            padding: 1.2rem 1.5rem;
            margin: 1.8rem 0;
            color: #634f00;
        }

        .article-content .blog-tip-icon {
            flex-shrink: 0;
            color: #b58d00;
        }

        .article-content .blog-tip-body {
            line-height: 1.6;
            font-size: 1rem;
        }

        .article-content .blog-figure {
            margin: 2.2rem 0;
            text-align: center;
        }

        .article-content .blog-responsive-img {
            max-width: 100%;
            height: auto;
            border-radius: 18px;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.08);
            display: block;
            margin: 0 auto;
        }

        .article-content .blog-fig-caption {
            margin-top: 0.75rem;
            font-size: 0.9rem;
            color: var(--text-soft);
            font-style: italic;
        }

        .article-content .blog-btn-wrap {
            margin: 2rem 0;
            text-align: center;
        }

        .article-content .blog-action-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            background: linear-gradient(135deg, var(--green-dark), var(--green-main));
            color: var(--white);
            padding: 0.95rem 2rem;
            border-radius: 999px;
            text-decoration: none;
            font-weight: 800;
            font-size: 1.05rem;
            box-shadow: 0 8px 22px rgba(15, 56, 28, 0.2);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .article-content .blog-action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(15, 56, 28, 0.28);
        }

        .article-content .blog-inline-link {
            color: var(--green-main);
            font-weight: 700;
            text-decoration: underline;
        }

        .article-content .blog-bullet-list,
        .article-content .blog-ordered-list {
            padding-left: 1.8rem;
            margin: 1.4rem 0;
        }

        .article-content .blog-bullet-list li,
        .article-content .blog-ordered-list li {
            margin-bottom: 0.6rem;
            line-height: 1.7;
        }

        .article-content .blog-divider {
            border: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(34, 139, 34, 0.3), transparent);
            margin: 2.8rem 0;
        }

        /* --- Keywords & Tags Section --- */
        .article-tags-wrap {
            margin-top: 2.2rem;
            padding-top: 1.8rem;
            border-top: 1px dashed rgba(34, 139, 34, 0.16);
            display: flex;
            align-items: center;
            gap: 0.8rem;
            flex-wrap: wrap;
        }

        .tags-label {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.9rem;
            font-weight: 700;
            color: var(--green-dark);
        }

        .tags-label svg {
            width: 16px;
            height: 16px;
            fill: var(--green-main);
            flex-shrink: 0;
        }

        .tags-list {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .tag-pill {
            display: inline-flex;
            align-items: center;
            background: #eef8ee;
            color: var(--green-dark);
            border: 1px solid rgba(34, 139, 34, 0.2);
            border-radius: 999px;
            padding: 0.35rem 0.85rem;
            font-size: 0.82rem;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .tag-pill:hover {
            background: var(--green-main);
            color: var(--white);
            border-color: var(--green-main);
            transform: translateY(-1px);
        }

        /* --- Social Share Bar --- */
        .share-section {
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 1px solid rgba(34, 139, 34, 0.12);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .share-label {
            font-weight: 800;
            color: var(--green-dark);
            font-size: 1rem;
        }

        .share-links {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            flex-wrap: wrap;
        }

        .share-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(34, 139, 34, 0.08);
            color: var(--green-dark);
            text-decoration: none;
            transition: all 0.2s ease;
            border: 1px solid rgba(34, 139, 34, 0.12);
            cursor: pointer;
        }

        .share-btn svg {
            width: 18px;
            height: 18px;
            fill: currentColor;
        }

        .share-btn:hover {
            background: var(--green-main);
            color: var(--white);
            transform: translateY(-2px);
        }

        /* --- Author Bio Box --- */
        .author-box {
            margin-top: 2.5rem;
            background: #f7fbf7;
            border: 1px solid rgba(34, 139, 34, 0.14);
            border-radius: 20px;
            padding: 1.8rem;
            display: flex;
            align-items: center;
            gap: 1.4rem;
        }

        .author-avatar {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: var(--green-dark);
            color: var(--white);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 900;
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .author-details h4 {
            margin: 0 0 0.3rem;
            color: var(--green-dark);
            font-size: 1.15rem;
        }

        .author-details p {
            margin: 0;
            color: var(--text-soft);
            font-size: 0.95rem;
            line-height: 1.55;
        }

        /* --- Recent Posts Section --- */
        .related-section {
            margin-top: 3rem;
        }

        .related-heading {
            font-size: 1.6rem;
            color: var(--green-dark);
            margin: 0 0 1.5rem;
            font-weight: 800;
        }

        .related-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.5rem;
        }

        .related-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 18px;
            padding: 1.4rem;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.04);
            display: flex;
            flex-direction: column;
            gap: 0.6rem;
            text-decoration: none;
            color: inherit;
            transition: transform 0.2s ease;
        }

        .related-card:hover {
            transform: translateY(-3px);
        }

        .related-card h3 {
            margin: 0;
            font-size: 1.1rem;
            color: var(--green-dark);
            line-height: 1.4;
        }

        .related-card p {
            margin: 0;
            color: var(--text-soft);
            font-size: 0.9rem;
            line-height: 1.5;
        }

        /* --- 404 Box --- */
        .not-found-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 28px;
            padding: 4rem 2rem;
            text-align: center;
            box-shadow: var(--shadow);
        }

        .not-found-card svg {
            width: 64px;
            height: 64px;
            fill: var(--green-main);
            margin-bottom: 1rem;
        }

        .not-found-card h1 {
            color: var(--green-dark);
            font-size: 2.2rem;
            margin: 0 0 0.8rem;
        }

        .not-found-card p {
            color: var(--text-soft);
            font-size: 1.1rem;
            margin: 0 0 2rem;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: var(--green-main);
            color: var(--white);
            padding: 0.85rem 1.8rem;
            border-radius: 999px;
            text-decoration: none;
            font-weight: 700;
        }

        @media (max-width: 768px) {
            main {
                padding: 1.5rem 1rem 3.5rem;
            }

            .article-card {
                padding: 2rem 1.4rem;
                border-radius: 20px;
            }

            .article-title {
                font-size: 1.8rem;
            }

            .article-content {
                font-size: 1rem;
            }

            .author-box {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
</head>

<body>
    <!-- Sidebar nav partial -->
    <?php require_once 'partials/header.php'; ?>

    <main>
        <div class="article-container">
            <!-- Breadcrumbs -->
            <div class="breadcrumb-bar" aria-label="Breadcrumb">
                <a href="/">Home</a>
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6-1.41-1.41z"/></svg>
                <a href="/blog.html">Blog</a>
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6-1.41-1.41z"/></svg>
                <span><?= $post ? htmlspecialchars($post['title']) : 'Article' ?></span>
            </div>

            <?php if ($post): 
                // Strip redundant leading title from body content so it appears once cleanly on top
                $renderedContent = $post['content'];
                $renderedContent = preg_replace('/^\s*\{\{\s*title\s*\}\}[\s\S]*?\{\{\s*\/title\s*\}\}\s*/i', '', $renderedContent);
                $renderedContent = preg_replace('/^\s*\{\{\s*title\s*:[^}\n]+\}\}\s*/i', '', $renderedContent);
            ?>
                <article class="article-card">
                    <!-- Top Article Header (Category, Title, Author & Timing on Top) -->
                    <div class="article-header">
                        <div class="category-pill"><?= htmlspecialchars($post['category'] ?? 'Technology') ?></div>
                        <h1 class="article-title"><?= htmlspecialchars($post['title']) ?></h1>
                        <div class="meta-strip">
                            <span class="meta-item">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                                <?= htmlspecialchars($post['author'] ?: 'SmarTech Team') ?>
                            </span>
                            <span class="meta-item">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM7 10h5v5H7z"/></svg>
                                <?= $postDate ?>
                            </span>
                            <span class="meta-item">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/></svg>
                                <?= $readTime ?> min read
                            </span>
                        </div>
                    </div>

                    <!-- Featured Image Banner -->
                    <?php if (!empty($post['featured_image'])): ?>
                        <div class="featured-banner">
                            <img src="<?= htmlspecialchars($post['featured_image']) ?>" alt="<?= htmlspecialchars($post['title']) ?>">
                        </div>
                    <?php endif; ?>

                    <!-- Parsed Article Body with all custom placeholders -->
                    <div class="article-content">
                        <?= parseBlogPlaceholders($renderedContent, $post['images_json']) ?>
                    </div>

                    <!-- SEO Keywords / Tags Pills -->
                    <?php if (!empty($post['keywords'])): 
                        $keywordsArr = array_filter(array_map('trim', explode(',', $post['keywords'])));
                    ?>
                        <?php if (!empty($keywordsArr)): ?>
                            <div class="article-tags-wrap">
                                <span class="tags-label">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M21.41 11.58l-9-9C12.05 2.22 11.55 2 11 2H4c-1.1 0-2 .9-2 2v7c0 .55.22 1.05.59 1.42l9 9c.36.36.86.58 1.41.58.55 0 1.05-.22 1.41-.59l7-7c.37-.36.59-.86.59-1.41 0-.55-.23-1.06-.59-1.42zM5.5 7C4.67 7 4 6.33 4 5.5S4.67 4 5.5 4 7 4.67 7 5.5 6.33 7 5.5 7z"/></svg>
                                    Keywords &amp; Tags:
                                </span>
                                <div class="tags-list">
                                    <?php foreach ($keywordsArr as $kw): ?>
                                        <a href="/blog.html?q=<?= urlencode($kw) ?>" class="tag-pill" title="View articles tagged with <?= htmlspecialchars($kw) ?>">#<?= htmlspecialchars($kw) ?></a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <!-- Social Sharing Section -->
                    <section class="share-section">
                        <span class="share-label">Share this article:</span>
                        <div class="share-links">
                            <!-- WhatsApp -->
                            <a class="share-btn" href="https://api.whatsapp.com/send?text=<?= urlencode($post['title'] . ' ' . $shareUrl) ?>" target="_blank" rel="noopener" aria-label="Share on WhatsApp">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.52 3.48A11.84 11.84 0 0 0 12.09 0C5.55 0 .23 5.32.23 11.86c0 2.09.55 4.14 1.59 5.95L0 24l6.38-1.67a11.78 11.78 0 0 0 5.7 1.45h.01c6.54 0 11.86-5.32 11.86-11.86 0-3.17-1.24-6.14-3.43-8.44zM12.1 21.78h-.01a9.83 9.83 0 0 1-5.01-1.37l-.36-.21-3.78.99 1.01-3.68-.23-.38a9.85 9.85 0 0 1-1.52-5.27c0-5.44 4.43-9.87 9.88-9.87 2.64 0 5.13 1.03 6.98 2.9a9.8 9.8 0 0 1 2.88 6.98c0 5.45-4.43 9.88-9.87 9.88z"/></svg>
                            </a>
                            <!-- Facebook -->
                            <a class="share-btn" href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($shareUrl) ?>" target="_blank" rel="noopener" aria-label="Share on Facebook">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                            </a>
                            <!-- LinkedIn -->
                            <a class="share-btn" href="https://www.linkedin.com/sharing/share-offsite/?url=<?= urlencode($shareUrl) ?>" target="_blank" rel="noopener" aria-label="Share on LinkedIn">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19 3a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h14m-.5 15.5v-5.3a3.26 3.26 0 0 0-3.26-3.26c-.85 0-1.84.52-2.28 1.3v-1.11h-2.79v8.37h2.79v-4.93c0-.77.62-1.4 1.39-1.4a1.4 1.4 0 0 1 1.4 1.4v4.93h2.75M6.46 10.9h2.77v8.37H6.46v-8.37M7.84 6.2a1.62 1.62 0 1 0 1.63 1.62A1.62 1.62 0 0 0 7.84 6.2z"/></svg>
                            </a>
                            <!-- Copy Link -->
                            <button class="share-btn" type="button" id="copyShareBtn" aria-label="Copy link to clipboard" title="Copy article link">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg>
                            </button>
                        </div>
                    </section>

                    <!-- Author Box -->
                    <div class="author-box">
                        <div class="author-avatar">ST</div>
                        <div class="author-details">
                            <h4><?= htmlspecialchars($post['author'] ?: 'SmarTech Team') ?></h4>
                            <p>Delivering digital transformation, school management applications, resort portals, and robust cloud platforms across Maharashtra and India.</p>
                        </div>
                    </div>
                </article>

                <!-- Related Posts -->
                <?php if (!empty($recentBlogs)): ?>
                    <section class="related-section">
                        <h2 class="related-heading">More Articles &amp; Case Studies</h2>
                        <div class="related-grid">
                            <?php foreach ($recentBlogs as $rec): ?>
                                <a href="/blog/<?= htmlspecialchars($rec['slug']) ?>" class="related-card">
                                    <span class="category-pill" style="font-size:0.75rem; padding:0.25rem 0.6rem;"><?= htmlspecialchars($rec['category'] ?? 'General') ?></span>
                                    <h3><?= htmlspecialchars($rec['title']) ?></h3>
                                    <p><?= htmlspecialchars($rec['meta_description'] ?: substr(strip_tags($rec['content']), 0, 110) . '...') ?></p>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>

            <?php else: ?>
                <div class="not-found-card">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
                    <h1>Article Not Found</h1>
                    <p>The blog post you requested could not be located or may have been unlisted.</p>
                    <a href="/blog.html" class="back-link">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor" aria-hidden="true"><path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/></svg>
                        Back to All Articles
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Footer & Chat partials -->
    <?php require_once 'partials/aichat.php'; ?>
    <?php require_once 'partials/footer.php'; ?>

    <script>
        (function() {
            const copyBtn = document.getElementById('copyShareBtn');
            if (copyBtn) {
                copyBtn.addEventListener('click', function() {
                    navigator.clipboard.writeText(window.location.href).then(function() {
                        alert('Article link copied to clipboard!');
                    }).catch(function() {
                        prompt('Copy link:', window.location.href);
                    });
                });
            }
        })();
    </script>
</body>
</html>

