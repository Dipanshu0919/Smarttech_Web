<?php
require_once 'config.php';
require_once 'case-study-engine.php';

$slug = $_GET['slug'] ?? '';
$id = $_GET['id'] ?? null;

$post = null;
if (!empty($slug)) {
    $post = get_case_study_by_slug($slug);
} elseif (!empty($id)) {
    $post = get_case_study_by_id($id);
}

// Fetch adjacent (Previous & Next) case studies
$adjacent = $post ? get_adjacent_case_studies($post['id'], true) : ['prev' => null, 'next' => null];

// Fetch other case studies for bottom recommendations
$allCS = get_all_case_studies(true);
$recentCS = array_filter($allCS, function($c) use ($post) {
    return !$post || (string)$c['id'] !== (string)$post['id'];
});
$recentCS = array_slice($recentCS, 0, 3);

$wordCount = $post ? str_word_count(strip_tags($post['content'])) : 0;
$readTime = max(1, ceil($wordCount / 200));
$postDate = $post && !empty($post['created_at']) ? date('M d, Y', strtotime($post['created_at'])) : date('M d, Y');
$shareUrl = $post ? 'https://smartechsolutions.in/case-study/' . htmlspecialchars($post['slug']) : 'https://smartechsolutions.in/case-study.html';
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
    <title><?= $post ? htmlspecialchars($post['title']) . ' | SmarTech Solutions Case Study' : 'Case Study Not Found | SmarTech Solutions' ?></title>
    <meta name="description" content="<?= $post ? htmlspecialchars(cleanContentExcerpt($post['content'], $post['meta_description'] ?? '', 160)) : 'Client Case Study on SmarTech Solutions' ?>">
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
    <meta property="og:title" content="<?= $post ? htmlspecialchars($post['title']) : 'Case Study' ?>">
    <meta property="og:description" content="<?= $post ? htmlspecialchars(cleanContentExcerpt($post['content'], $post['meta_description'] ?? '', 160)) : '' ?>">
    <meta property="og:image" content="<?= $post && !empty($post['featured_image']) ? htmlspecialchars($post['featured_image']) : 'https://smartechsolutions.in/assets/smarttechlogo.png' ?>">
    <meta property="og:site_name" content="SmarTech Solutions">

    <!-- Twitter Cards -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="<?= $shareUrl ?>">
    <meta name="twitter:title" content="<?= $post ? htmlspecialchars($post['title']) : 'Case Study' ?>">
    <meta name="twitter:description" content="<?= $post ? htmlspecialchars(cleanContentExcerpt($post['content'], $post['meta_description'] ?? '', 160)) : '' ?>">
    <meta name="twitter:image" content="<?= $post && !empty($post['featured_image']) ? htmlspecialchars($post['featured_image']) : 'https://smartechsolutions.in/assets/smarttechlogo.png' ?>">

    <?php if ($post): ?>
    <!-- Structured Data (JSON-LD) -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Article",
        "headline": "<?= addslashes($post['title']) ?>",
        "description": "<?= addslashes(cleanContentExcerpt($post['content'], $post['meta_description'] ?? '', 160)) ?>",
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
            --cs-dark: #0a2515;
            --cs-main: #187930;
            --cs-accent: #059669;
            --cs-emerald: #10b981;
            --cs-light-bg: #f3f8f4;
            --white: #ffffff;
            --text-dark: #1b2820;
            --text-soft: #55675c;
            --surface: #ffffff;
            --border: rgba(24, 121, 48, 0.14);
            --shadow: 0 16px 42px rgba(10, 37, 21, 0.08);
            --badge-bg: #eaf6ed;
        }

        * { box-sizing: border-box; }

        body, html {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--cs-light-bg);
            color: var(--text-dark);
            overflow-x: hidden;
            scroll-behavior: smooth;
        }

        main {
            margin-left: 250px;
            padding: 2.5rem 2.5rem 5rem;
            position: relative;
            min-height: 100vh;
            background-color: var(--cs-light-bg);
        }

        @media (max-width: 992px) {
            main {
                margin-left: 0;
                padding: 1.8rem 1rem 3.5rem;
                padding-top: 85px;
            }
        }

        .cs-container {
            max-width: 960px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }

        /* --- Breadcrumb --- */
        .breadcrumb-bar {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.88rem;
            color: var(--text-soft);
            flex-wrap: wrap;
        }

        .breadcrumb-bar a {
            color: var(--cs-main);
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
        }

        .breadcrumb-bar span {
            color: var(--text-dark);
            font-weight: 600;
            max-width: 320px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* --- Main Article Card --- */
        .cs-article-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 28px;
            box-shadow: var(--shadow);
            padding: 3.5rem 3.5rem;
            overflow: hidden;
            position: relative;
        }

        /* --- Top Header --- */
        .cs-header {
            margin-bottom: 2rem;
        }

        .cs-badges-strip {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            flex-wrap: wrap;
            margin-bottom: 1.2rem;
        }

        .category-pill {
            display: inline-flex;
            align-items: center;
            background: rgba(24, 121, 48, 0.1);
            color: var(--cs-dark);
            padding: 0.4rem 1rem;
            border-radius: 999px;
            font-size: 0.82rem;
            font-weight: 800;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            border: 1px solid rgba(24, 121, 48, 0.2);
        }

        .client-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            background: #eef7f0;
            color: var(--cs-dark);
            padding: 0.4rem 0.95rem;
            border-radius: 999px;
            font-size: 0.84rem;
            font-weight: 700;
            border: 1px solid rgba(24, 121, 48, 0.22);
        }

        .client-pill svg {
            width: 15px;
            height: 15px;
            fill: var(--cs-main);
        }

        .cs-title {
            margin: 0 0 1.2rem;
            font-size: clamp(2rem, 3.8vw, 2.9rem);
            font-weight: 800;
            color: var(--cs-dark);
            line-height: 1.22;
            letter-spacing: -0.02em;
        }

        .meta-strip {
            display: flex;
            align-items: center;
            gap: 1.4rem;
            color: var(--text-soft);
            font-size: 0.92rem;
            flex-wrap: wrap;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid rgba(24, 121, 48, 0.12);
        }

        .meta-item {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            font-weight: 600;
        }

        .meta-item svg {
            width: 17px;
            height: 17px;
            fill: var(--cs-main);
        }

        /* --- Featured Banner --- */
        .featured-banner {
            margin: 0 0 2.8rem;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 12px 28px rgba(10, 37, 21, 0.08);
            border: 1px solid var(--border);
            max-height: 480px;
            background: #0d2817;
        }

        .featured-banner img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        /* --- Content Styling --- */
        .cs-content {
            font-size: 1.08rem;
            line-height: 1.85;
            color: #27362e;
        }

        .cs-content .blog-p {
            margin: 1.2rem 0;
        }

        .cs-content .blog-h1,
        .cs-content .blog-h2,
        .cs-content .blog-h3,
        .cs-content .blog-h4,
        .cs-content .blog-h5,
        .cs-content .blog-h6 {
            color: var(--cs-dark);
            font-weight: 800;
            margin-top: 2.2rem;
            margin-bottom: 0.8rem;
            line-height: 1.28;
            letter-spacing: -0.015em;
        }

        .cs-content .blog-h1 { font-size: 2.1rem; }
        .cs-content .blog-h2 { font-size: 1.7rem; border-left: 4px solid var(--cs-main); padding-left: 0.85rem; }
        .cs-content .blog-h3 { font-size: 1.38rem; }
        .cs-content .blog-h4 { font-size: 1.2rem; }

        .cs-content .blog-strong {
            font-weight: 800;
            color: var(--cs-dark);
        }

        .cs-content .blog-quote {
            position: relative;
            margin: 2rem 0;
            padding: 1.4rem 1.8rem 1.4rem 3.4rem;
            background: #f1f8f3;
            border-left: 4px solid var(--cs-main);
            border-radius: 0 16px 16px 0;
            color: var(--cs-dark);
            font-style: italic;
            font-size: 1.12rem;
        }

        .cs-content .blog-quote-icon {
            position: absolute;
            left: 1rem;
            top: 1.2rem;
            fill: var(--cs-main);
            opacity: 0.45;
        }

        .cs-content .blog-callout {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            background: #eef7f1;
            border: 1px solid rgba(24, 121, 48, 0.22);
            border-radius: 16px;
            padding: 1.4rem 1.6rem;
            margin: 1.8rem 0;
        }

        .cs-content .blog-callout-icon {
            color: var(--cs-main);
            flex-shrink: 0;
            margin-top: 2px;
        }

        .cs-content .blog-tip-box {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            background: #fffcee;
            border: 1px solid rgba(220, 180, 0, 0.35);
            border-radius: 16px;
            padding: 1.4rem 1.6rem;
            margin: 1.8rem 0;
            color: #5c4900;
        }

        .cs-content .blog-tip-icon {
            color: #b89200;
            flex-shrink: 0;
            margin-top: 2px;
        }

        /* --- Distinctive Case Study Stat Grid --- */
        .cs-stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.2rem;
            margin: 2.2rem 0;
        }

        .cs-stat-card {
            background: linear-gradient(135deg, #f4faf6 0%, #e7f5ec 100%);
            border: 1px solid rgba(24, 121, 48, 0.2);
            border-radius: 18px;
            padding: 1.4rem 1.6rem;
            text-align: center;
            box-shadow: 0 4px 14px rgba(10, 37, 21, 0.04);
        }

        .cs-stat-value {
            font-size: 2.3rem;
            font-weight: 800;
            color: var(--cs-dark);
            line-height: 1.1;
            margin-bottom: 0.35rem;
        }

        .cs-stat-label {
            font-size: 0.88rem;
            font-weight: 700;
            color: var(--text-soft);
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        /* --- Distinctive Phase Blocks (Challenge / Solution / Results) --- */
        .cs-phase-box {
            border-radius: 20px;
            padding: 1.6rem 2rem;
            margin: 2rem 0;
            border: 1px solid transparent;
        }

        .cs-phase-challenge {
            background: #fdf5f5;
            border-color: rgba(220, 38, 38, 0.2);
        }

        .cs-phase-challenge .cs-phase-header {
            color: #b91c1c;
        }

        .cs-phase-solution {
            background: #f0fdf4;
            border-color: rgba(22, 163, 74, 0.25);
        }

        .cs-phase-solution .cs-phase-header {
            color: #15803d;
        }

        .cs-phase-results {
            background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
            border-color: rgba(5, 150, 105, 0.3);
        }

        .cs-phase-results .cs-phase-header {
            color: #047857;
        }

        .cs-phase-header {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            font-size: 1.15rem;
            font-weight: 800;
            margin-bottom: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .cs-phase-body {
            font-size: 1.05rem;
            line-height: 1.75;
            color: var(--text-dark);
        }

        .cs-content .blog-bullet-list,
        .cs-content .blog-ordered-list {
            padding-left: 1.8rem;
            margin: 1.4rem 0;
        }

        .cs-content .blog-bullet-list li,
        .cs-content .blog-ordered-list li {
            margin-bottom: 0.6rem;
            line-height: 1.7;
        }

        .cs-content .blog-divider {
            border: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(24, 121, 48, 0.3), transparent);
            margin: 2.8rem 0;
        }

        .cs-content .blog-figure {
            margin: 2rem 0;
            text-align: center;
        }

        .cs-content .blog-responsive-img {
            max-width: 100%;
            border-radius: 16px;
            box-shadow: 0 10px 24px rgba(0,0,0,0.08);
        }

        .cs-content .blog-fig-caption {
            font-size: 0.88rem;
            color: var(--text-soft);
            font-style: italic;
            margin-top: 0.5rem;
        }

        .cs-content .blog-btn-wrap {
            margin: 2.4rem 0;
            text-align: center;
        }

        .cs-content .blog-action-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            background: linear-gradient(135deg, var(--cs-dark), var(--cs-main));
            color: var(--white);
            padding: 0.95rem 2.2rem;
            border-radius: 999px;
            text-decoration: none;
            font-weight: 800;
            font-size: 1.05rem;
            box-shadow: 0 8px 24px rgba(10, 37, 21, 0.22);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .cs-content .blog-action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 30px rgba(10, 37, 21, 0.3);
        }

        /* --- Keywords / Tags --- */
        .cs-tags-wrap {
            margin-top: 2.4rem;
            padding-top: 1.8rem;
            border-top: 1px dashed rgba(24, 121, 48, 0.2);
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
            color: var(--cs-dark);
        }

        .tags-label svg {
            width: 16px;
            height: 16px;
            fill: var(--cs-main);
        }

        .tag-pill {
            display: inline-flex;
            align-items: center;
            background: #eef7f1;
            color: var(--cs-dark);
            border: 1px solid rgba(24, 121, 48, 0.2);
            border-radius: 999px;
            padding: 0.35rem 0.85rem;
            font-size: 0.82rem;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .tag-pill:hover {
            background: var(--cs-main);
            color: var(--white);
            border-color: var(--cs-main);
            transform: translateY(-1px);
        }

        /* ==========================================================================
           PREVIOUS / NEXT CASE STUDY BUTTON NAVIGATION (REQUESTED FEATURE)
           ========================================================================== */
        .cs-adjacent-nav {
            margin-top: 3rem;
            padding-top: 2.2rem;
            border-top: 1px solid rgba(24, 121, 48, 0.16);
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }

        .cs-nav-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1.4rem 1.6rem;
            background: #f7faf7;
            border: 1px solid rgba(24, 121, 48, 0.18);
            border-radius: 18px;
            text-decoration: none;
            color: var(--text-dark);
            transition: all 0.25s ease;
        }

        .cs-nav-item:hover {
            background: #ffffff;
            border-color: var(--cs-main);
            box-shadow: 0 8px 24px rgba(10, 37, 21, 0.09);
            transform: translateY(-3px);
        }

        .cs-nav-next {
            justify-content: flex-end;
            margin-left: auto;
            width: 100%;
        }

        .cs-nav-arrow {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: rgba(24, 121, 48, 0.1);
            color: var(--cs-main);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: all 0.2s ease;
        }

        .cs-nav-arrow svg {
            width: 20px;
            height: 20px;
            fill: currentColor;
        }

        .cs-nav-item:hover .cs-nav-arrow {
            background: var(--cs-main);
            color: #ffffff;
        }

        .cs-nav-text {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
            overflow: hidden;
        }

        .cs-nav-sub {
            font-size: 0.78rem;
            font-weight: 800;
            color: var(--cs-main);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .cs-nav-title {
            font-size: 0.98rem;
            font-weight: 700;
            color: var(--cs-dark);
            line-height: 1.35;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .cs-nav-disabled {
            background: #fafdfa;
            border: 1px dashed rgba(24, 121, 48, 0.12);
            color: var(--text-soft);
            opacity: 0.6;
            cursor: not-allowed;
            display: flex;
            align-items: center;
        }

        /* --- Social Share Bar --- */
        .share-section {
            margin-top: 2.2rem;
            padding-top: 1.8rem;
            border-top: 1px solid rgba(24, 121, 48, 0.12);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .share-label {
            font-weight: 800;
            color: var(--cs-dark);
            font-size: 0.96rem;
        }

        .share-links {
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }

        .share-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #f1f7f2;
            border: 1px solid rgba(24, 121, 48, 0.2);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: var(--cs-dark);
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .share-btn:hover {
            background: var(--cs-main);
            color: var(--white);
            border-color: var(--cs-main);
            transform: translateY(-2px);
        }

        .share-btn svg {
            width: 18px;
            height: 18px;
            fill: currentColor;
        }

        /* --- CTA Card --- */
        .cs-cta-box {
            margin-top: 2.5rem;
            background: linear-gradient(135deg, #0a2515 0%, #155e28 100%);
            border-radius: 22px;
            padding: 2.5rem;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1.8rem;
            flex-wrap: wrap;
            box-shadow: 0 12px 30px rgba(10, 37, 21, 0.2);
        }

        .cs-cta-text h3 {
            margin: 0 0 0.5rem;
            font-size: 1.6rem;
            font-weight: 800;
        }

        .cs-cta-text p {
            margin: 0;
            color: #c9e8d2;
            font-size: 1.02rem;
            max-width: 540px;
            line-height: 1.5;
        }

        .cs-cta-buttons {
            display: flex;
            gap: 0.8rem;
            flex-wrap: wrap;
        }

        .cta-btn-white {
            background: #ffffff;
            color: var(--cs-dark);
            padding: 0.8rem 1.6rem;
            border-radius: 999px;
            font-weight: 800;
            text-decoration: none;
            font-size: 0.95rem;
            transition: all 0.2s ease;
        }

        .cta-btn-white:hover {
            background: #eaf6ed;
            transform: translateY(-2px);
        }

        .cta-btn-outline {
            background: transparent;
            color: #ffffff;
            border: 2px solid rgba(255, 255, 255, 0.5);
            padding: 0.75rem 1.5rem;
            border-radius: 999px;
            font-weight: 700;
            text-decoration: none;
            font-size: 0.95rem;
            transition: all 0.2s ease;
        }

        .cta-btn-outline:hover {
            border-color: #ffffff;
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
        }

        /* --- Related Case Studies --- */
        .related-section {
            margin-top: 1rem;
        }

        .related-heading {
            font-size: 1.45rem;
            font-weight: 800;
            color: var(--cs-dark);
            margin: 0 0 1.2rem;
        }

        .related-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
        }

        .related-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 18px;
            padding: 1.5rem;
            text-decoration: none;
            color: var(--text-dark);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
            transition: all 0.25s ease;
            display: flex;
            flex-direction: column;
        }

        .related-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow);
            border-color: var(--cs-main);
        }

        .related-card h3 {
            margin: 0.6rem 0 0.5rem;
            font-size: 1.15rem;
            line-height: 1.4;
            color: var(--cs-dark);
            font-weight: 800;
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
            padding: 4.5rem 2rem;
            text-align: center;
            box-shadow: var(--shadow);
        }

        .not-found-card svg {
            width: 68px;
            height: 68px;
            fill: var(--cs-main);
            margin-bottom: 1rem;
        }

        .not-found-card h1 {
            color: var(--cs-dark);
            font-size: 2.2rem;
            margin: 0 0 0.8rem;
            font-weight: 800;
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
            background: var(--cs-main);
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

            .cs-article-card {
                padding: 2.2rem 1.4rem;
                border-radius: 22px;
            }

            .cs-adjacent-nav {
                grid-template-columns: 1fr;
            }

            .cs-nav-next {
                justify-content: flex-start;
                text-align: left;
            }

            .cs-nav-next .cs-nav-text {
                text-align: left !important;
            }

            .cs-cta-box {
                padding: 1.8rem;
            }
        }
    </style>
</head>

<body>
    <!-- Sidebar nav partial -->
    <?php require_once 'partials/header.php'; ?>

    <main>
        <div class="cs-container">
            <!-- Breadcrumbs -->
            <div class="breadcrumb-bar" aria-label="Breadcrumb">
                <a href="/">Home</a>
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6-1.41-1.41z"/></svg>
                <a href="/case-study.html">Case Studies</a>
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6-1.41-1.41z"/></svg>
                <span><?= $post ? htmlspecialchars($post['title']) : 'Case Study' ?></span>
            </div>

            <?php if ($post): 
                // Strip redundant leading title from body content
                $renderedContent = $post['content'];
                $renderedContent = preg_replace('/^\s*\{\{\s*title\s*\}\}[\s\S]*?\{\{\s*\/title\s*\}\}\s*/i', '', $renderedContent);
                $renderedContent = preg_replace('/^\s*\{\{\s*title\s*:[^}\n]+\}\}\s*/i', '', $renderedContent);
            ?>
                <article class="cs-article-card">
                    <!-- Top Case Study Header -->
                    <div class="cs-header">
                        <div class="cs-badges-strip">
                            <span class="category-pill"><?= htmlspecialchars($post['category'] ?? 'Case Study') ?></span>
                            <?php if (!empty($post['client_name'])): ?>
                                <span class="client-pill">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 7V3H2v18h20V7H12zM6 19H4v-2h2v2zm0-4H4v-2h2v2zm0-4H4V9h2v2zm0-4H4V5h2v2zm4 12H8v-2h2v2zm0-4H8v-2h2v2zm0-4H8V9h2v2zm0-4H8V5h2v2zm10 12h-8v-2h2v-2h-2v-2h2v-2h-2V9h8v10zm-2-8h-2v2h2v-2zm0 4h-2v2h2v-2z"/></svg>
                                    Client: <?= htmlspecialchars($post['client_name']) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <h1 class="cs-title"><?= htmlspecialchars($post['title']) ?></h1>
                        <div class="meta-strip">
                            <span class="meta-item">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                                Author: <?= htmlspecialchars($post['author'] ?: 'SmarTech Team') ?>
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

                    <!-- Parsed Case Study Body with Phase & Stat blocks -->
                    <div class="cs-content">
                        <?= parseCaseStudyPlaceholders($renderedContent, $post['images_json']) ?>
                    </div>

                    <!-- SEO Keywords / Tags -->
                    <?php if (!empty($post['keywords'])): 
                        $keywordsArr = array_filter(array_map('trim', explode(',', $post['keywords'])));
                    ?>
                        <?php if (!empty($keywordsArr)): ?>
                            <div class="cs-tags-wrap">
                                <span class="tags-label">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M21.41 11.58l-9-9C12.05 2.22 11.55 2 11 2H4c-1.1 0-2 .9-2 2v7c0 .55.22 1.05.59 1.42l9 9c.36.36.86.58 1.41.58.55 0 1.05-.22 1.41-.59l7-7c.37-.36.59-.86.59-1.41 0-.55-.23-1.06-.59-1.42zM5.5 7C4.67 7 4 6.33 4 5.5S4.67 4 5.5 4 7 4.67 7 5.5 6.33 7 5.5 7z"/></svg>
                                    Keywords:
                                </span>
                                <?php foreach ($keywordsArr as $kw): ?>
                                    <a href="/case-study.html?q=<?= urlencode($kw) ?>" class="tag-pill" title="View case studies tagged with <?= htmlspecialchars($kw) ?>">#<?= htmlspecialchars($kw) ?></a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <!-- =======================================================
                         PREVIOUS / NEXT CASE STUDY BUTTON NAVIGATION
                         ======================================================= -->
                    <nav class="cs-adjacent-nav" aria-label="Case Study Adjacent Navigation">
                        <?php if (!empty($adjacent['prev'])): ?>
                            <a href="/case-study/<?= htmlspecialchars($adjacent['prev']['slug']) ?>" class="cs-nav-item cs-nav-prev" title="Previous Case Study: <?= htmlspecialchars($adjacent['prev']['title']) ?>">
                                <div class="cs-nav-arrow">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/></svg>
                                </div>
                                <div class="cs-nav-text">
                                    <span class="cs-nav-sub">Previous Case Study</span>
                                    <span class="cs-nav-title"><?= htmlspecialchars($adjacent['prev']['title']) ?></span>
                                </div>
                            </a>
                        <?php else: ?>
                            <div class="cs-nav-item cs-nav-disabled">
                                <span class="cs-nav-sub">Beginning of Case Studies</span>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($adjacent['next'])): ?>
                            <a href="/case-study/<?= htmlspecialchars($adjacent['next']['slug']) ?>" class="cs-nav-item cs-nav-next" title="Next Case Study: <?= htmlspecialchars($adjacent['next']['title']) ?>">
                                <div class="cs-nav-text" style="text-align: right;">
                                    <span class="cs-nav-sub">Next Case Study</span>
                                    <span class="cs-nav-title"><?= htmlspecialchars($adjacent['next']['title']) ?></span>
                                </div>
                                <div class="cs-nav-arrow">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 4l-1.41 1.41L16.17 11H4v2h12.17l-5.58 5.59L12 20l8-8z"/></svg>
                                </div>
                            </a>
                        <?php else: ?>
                            <div class="cs-nav-item cs-nav-disabled" style="text-align: right; justify-content: flex-end;">
                                <span class="cs-nav-sub">Latest Case Study</span>
                            </div>
                        <?php endif; ?>
                    </nav>

                    <!-- Social Sharing -->
                    <section class="share-section">
                        <span class="share-label">Share this case study:</span>
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
                            <button class="share-btn" type="button" id="copyShareBtn" aria-label="Copy link to clipboard" title="Copy case study link">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg>
                            </button>
                        </div>
                    </section>

                    <!-- Client Solution CTA Card -->
                    <div class="cs-cta-box">
                        <div class="cs-cta-text">
                            <h3>Achieve Similar Results for Your Organization</h3>
                            <p>Partner with SmarTech Solutions to design, automate, and scale your digital systems with measurable ROI.</p>
                        </div>
                        <div class="cs-cta-buttons">
                            <a href="https://wa.me/919221204466?text=<?= urlencode('Hello SmarTech Solutions, I read your case study "' . $post['title'] . '" and would like to discuss a project.') ?>" target="_blank" rel="noopener" class="cta-btn-white">Discuss via WhatsApp</a>
                            <a href="/enquiry.html" class="cta-btn-outline">Send Project Enquiry</a>
                        </div>
                    </div>
                </article>

                <!-- Related Case Studies -->
                <?php if (!empty($recentCS)): ?>
                    <section class="related-section">
                        <h2 class="related-heading">More Case Studies &amp; Success Stories</h2>
                        <div class="related-grid">
                            <?php foreach ($recentCS as $rec): ?>
                                <a href="/case-study/<?= htmlspecialchars($rec['slug']) ?>" class="related-card">
                                    <span class="category-pill" style="font-size:0.75rem; padding:0.25rem 0.65rem; width:fit-content;"><?= htmlspecialchars($rec['category'] ?? 'Case Study') ?></span>
                                    <h3><?= htmlspecialchars($rec['title']) ?></h3>
                                    <?php if (!empty($rec['meta_description'])): ?>
                                        <p><?= htmlspecialchars(trim($rec['meta_description'])) ?></p>
                                    <?php endif; ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>

            <?php else: ?>
                <div class="not-found-card">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
                    <h1>Case Study Not Found</h1>
                    <p>The case study you requested could not be located or may have been unlisted.</p>
                    <a href="/case-study.html" class="back-link">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor" aria-hidden="true"><path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/></svg>
                        Back to All Case Studies
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
                        alert('Case study link copied to clipboard!');
                    }).catch(function() {
                        prompt('Copy link:', window.location.href);
                    });
                });
            }
        })();
    </script>
</body>
</html>

