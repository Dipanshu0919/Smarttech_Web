<?php
require_once 'config.php';
require_once 'blog-engine.php';

$blogs = get_all_blogs(true); // Fetch published blogs
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
    <title>Blog & Tech Insights | SmarTech Solutions Thane</title>
    <meta name="description"
        content="Read articles, technical guides, and industry case studies on School ERP, Resort Digital Solutions, Digital Grampanchayat, and custom mobile apps by SmarTech Solutions.">
    <meta name="keywords"
        content="tech blog Mumbai, school ERP articles, resort software guides, digital grampanchayat updates, SmarTech Solutions blog, web development tips Thane">
    <meta name="author" content="SmarTech Solutions">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    <link rel="canonical" href="https://smartechsolutions.in/blog.html">
    <link rel="icon" type="image/png" href="assets/smarttechlogo.png">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://smartechsolutions.in/blog.html">
    <meta property="og:title" content="Blog & Tech Insights | SmarTech Solutions">
    <meta property="og:description"
        content="Explore the latest articles on school management apps, hospitality technology, and government digitization.">
    <meta property="og:image" content="https://smartechsolutions.in/assets/smarttechlogo.png">
    <meta property="og:site_name" content="SmarTech Solutions">

    <!-- Twitter Cards -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="https://smartechsolutions.in/blog.html">
    <meta name="twitter:title" content="Blog & Tech Insights | SmarTech Solutions">
    <meta name="twitter:description"
        content="Explore the latest articles on school management apps, hospitality technology, and government digitization.">
    <meta name="twitter:image" content="https://smartechsolutions.in/assets/smarttechlogo.png">

    <script src="https://unpkg.com/htmx.org@1.9.12"></script>

    <style>
        :root {
            --green-dark: #0f381c;
            --green-main: #228b22;
            --green-light: #4cbb17;
            --green-accent: #a3e4d7;
            --white: #ffffff;
            --text-dark: #293235;
            --text-soft: #5f6c72;
            --bg-light: #f4f9f4;
            --surface: #ffffff;
            --surface-alt: #f9fbf9;
            --border: rgba(34, 139, 34, 0.12);
            --card-shadow: 0 12px 28px rgba(0, 0, 0, 0.08);
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
            padding: 3rem 2.5rem 4rem;
            position: relative;
            min-height: 100vh;
        }

        @media (max-width: 992px) {
            main {
                margin-left: 0;
                padding: 1.8rem 1rem 3rem;
                padding-top: 85px;
            }
        }

        .page-wrap {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }

        /* --- Hero Card --- */
        .hero-card {
            position: relative;
            border-radius: 24px;
            background: linear-gradient(135deg, #ffffff 0%, #f4faf4 100%);
            box-shadow: var(--card-shadow);
            padding: 3rem 2.5rem;
            border: 1px solid var(--border);
            overflow: hidden;
        }

        .hero-card::after {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, rgba(76, 187, 23, 0.15) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        .hero-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.45rem 1rem;
            border-radius: 999px;
            background: rgba(34, 139, 34, 0.1);
            color: var(--green-dark);
            font-size: 0.88rem;
            font-weight: 800;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            margin-bottom: 1rem;
        }

        .hero-pill svg {
            width: 16px;
            height: 16px;
            fill: var(--green-main);
        }

        .hero-title {
            margin: 0 0 1rem;
            font-size: clamp(2rem, 3.8vw, 3rem);
            color: var(--green-dark);
            letter-spacing: -0.03em;
            line-height: 1.15;
            font-weight: 800;
        }

        .hero-sub {
            margin: 0;
            color: var(--text-soft);
            font-size: 1.1rem;
            line-height: 1.6;
            max-width: 780px;
        }

        /* --- Filter & Search Controls --- */
        .controls-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1.2rem;
            flex-wrap: wrap;
            background: var(--surface);
            padding: 1rem 1.4rem;
            border-radius: 20px;
            border: 1px solid var(--border);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
        }

        .category-filters {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .filter-btn {
            background: rgba(34, 139, 34, 0.06);
            color: var(--green-dark);
            border: 1px solid rgba(34, 139, 34, 0.15);
            padding: 0.55rem 1.1rem;
            border-radius: 999px;
            font-weight: 700;
            font-size: 0.88rem;
            cursor: pointer;
            transition: all 0.2s ease;
            font-family: inherit;
        }

        .filter-btn:hover {
            background: rgba(34, 139, 34, 0.14);
        }

        .filter-btn.active {
            background: var(--green-main);
            color: var(--white);
            border-color: var(--green-main);
            box-shadow: 0 4px 12px rgba(34, 139, 34, 0.25);
        }

        .search-input-wrap {
            position: relative;
            min-width: 260px;
            flex-grow: 1;
            max-width: 380px;
        }

        .search-input-wrap svg {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            width: 18px;
            height: 18px;
            fill: var(--text-soft);
            pointer-events: none;
        }

        .search-input {
            width: 100%;
            padding: 0.65rem 1rem 0.65rem 2.5rem;
            border-radius: 999px;
            border: 1px solid rgba(34, 139, 34, 0.2);
            font-family: inherit;
            font-size: 0.95rem;
            outline: none;
            background: #ffffff;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .search-input:focus {
            border-color: var(--green-main);
            box-shadow: 0 0 0 3px rgba(34, 139, 34, 0.15);
        }

        /* --- Blog Posts Grid --- */
        .blog-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
            gap: 1.8rem;
        }

        .blog-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 22px;
            overflow: hidden;
            box-shadow: var(--card-shadow);
            transition: transform 0.25s ease, box-shadow 0.25s ease;
            display: flex;
            flex-direction: column;
        }

        .blog-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 16px 36px rgba(15, 56, 28, 0.12);
        }

        .blog-card-media {
            width: 100%;
            height: 210px;
            background: #eef8ee;
            position: relative;
            overflow: hidden;
        }

        .blog-card-media img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
            display: block;
        }

        .blog-card:hover .blog-card-media img {
            transform: scale(1.05);
        }

        .blog-card-media .no-thumb {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, rgba(34, 139, 34, 0.1), rgba(15, 56, 28, 0.2));
            color: var(--green-dark);
        }

        .blog-card-media .no-thumb svg {
            width: 48px;
            height: 48px;
            opacity: 0.5;
            fill: currentColor;
        }

        .blog-card-body {
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
            gap: 0.75rem;
        }

        .blog-meta-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
            font-size: 0.85rem;
        }

        .category-badge {
            background: rgba(34, 139, 34, 0.1);
            color: var(--green-dark);
            padding: 0.3rem 0.75rem;
            border-radius: 999px;
            font-weight: 800;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .date-text {
            color: var(--text-soft);
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
        }

        .date-text svg {
            width: 14px;
            height: 14px;
            fill: currentColor;
        }

        .blog-card-title {
            margin: 0;
            font-size: 1.25rem;
            line-height: 1.35;
            color: var(--green-dark);
            font-weight: 800;
        }

        .blog-card-title a {
            color: inherit;
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .blog-card-title a:hover {
            color: var(--green-main);
        }

        .blog-card-desc {
            margin: 0;
            color: var(--text-soft);
            font-size: 0.95rem;
            line-height: 1.55;
            flex-grow: 1;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .card-keywords-row {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            flex-wrap: wrap;
            margin-top: 0.2rem;
        }

        .card-keyword-pill {
            font-size: 0.74rem;
            color: var(--green-main);
            background: rgba(34, 139, 34, 0.08);
            border: 1px solid rgba(34, 139, 34, 0.12);
            padding: 0.18rem 0.55rem;
            border-radius: 999px;
            font-weight: 700;
        }

        .blog-card-footer {
            margin-top: 0.5rem;
            padding-top: 1rem;
            border-top: 1px solid rgba(34, 139, 34, 0.08);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .author-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.88rem;
            font-weight: 700;
            color: var(--green-dark);
        }

        .read-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            color: var(--green-main);
            font-weight: 800;
            font-size: 0.92rem;
            text-decoration: none;
            transition: gap 0.2s ease, color 0.2s ease;
        }

        .read-btn svg {
            width: 16px;
            height: 16px;
            fill: currentColor;
            transition: transform 0.2s ease;
        }

        .read-btn:hover {
            color: var(--green-dark);
        }

        .read-btn:hover svg {
            transform: translateX(4px);
        }

        /* --- Empty State --- */
        .empty-blog-box {
            grid-column: 1 / -1;
            background: var(--surface);
            border: 1px dashed rgba(34, 139, 34, 0.25);
            border-radius: 20px;
            padding: 3rem 1.5rem;
            text-align: center;
            color: var(--text-soft);
        }

        .empty-blog-box svg {
            width: 48px;
            height: 48px;
            fill: var(--green-main);
            margin-bottom: 0.8rem;
            opacity: 0.7;
        }

        .empty-blog-box h3 {
            margin: 0 0 0.5rem;
            color: var(--green-dark);
            font-size: 1.3rem;
        }

        @media (max-width: 768px) {
            main {
                padding: 1.8rem 1rem 3rem;
            }

            .hero-card {
                padding: 2rem 1.5rem;
            }

            .blog-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <!-- Navigation Sidebar from partial -->
    <?php require_once 'partials/header.php'; ?>

    <main>
        <div class="page-wrap">
            <!-- Hero Card -->
            <section class="hero-card">
                <div class="hero-pill">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/>
                    </svg>
                    Official Knowledge Hub
                </div>
                <h1 class="hero-title">Insights, Tech Innovations &amp; Industry Updates</h1>
                <p class="hero-sub">
                    Explore detailed articles on smart school applications, modern hotel &amp; resort platforms, grampanchayat digitization, and progressive software engineering by SmarTech Solutions.
                </p>
            </section>

            <!-- Search and Filter Bar -->
            <section class="controls-bar">
                <div class="category-filters" id="categoryFilters">
                    <button type="button" class="filter-btn active" data-category="all">All Articles</button>
                    <button type="button" class="filter-btn" data-category="School ERP">School ERP</button>
                    <button type="button" class="filter-btn" data-category="Resort Tech">Resort Tech</button>
                    <button type="button" class="filter-btn" data-category="Digital Grampanchayat">Grampanchayat</button>
                    <button type="button" class="filter-btn" data-category="Technology">Technology</button>
                </div>
                <div class="search-input-wrap">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M15.5 14h-.79l-.28-.27A6.471 6.471 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
                    </svg>
                    <input type="text" id="searchInput" class="search-input" placeholder="Search by title or topic..." aria-label="Search articles">
                </div>
            </section>

            <!-- Blog Grid -->
            <section class="blog-grid" id="blogGrid">
                <?php if (!empty($blogs)): ?>
                    <?php foreach ($blogs as $post): 
                        $postDate = !empty($post['created_at']) ? date('M d, Y', strtotime($post['created_at'])) : date('M d, Y');
                        $postSlug = htmlspecialchars($post['slug']);
                        $postUrl = '/blog/' . $postSlug;
                        $featuredImg = $post['featured_image'] ?? '';
                    ?>
                        <article class="blog-card" data-category="<?= htmlspecialchars($post['category'] ?? 'Technology') ?>" data-title="<?= htmlspecialchars(strtolower($post['title'])) ?>" data-keywords="<?= htmlspecialchars(strtolower($post['keywords'] ?? '')) ?>">
                            <div class="blog-card-media">
                                <?php if (!empty($featuredImg)): ?>
                                    <img src="<?= htmlspecialchars($featuredImg) ?>" alt="<?= htmlspecialchars($post['title']) ?>" loading="lazy">
                                <?php else: ?>
                                    <div class="no-thumb">
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/>
                                        </svg>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="blog-card-body">
                                <div class="blog-meta-row">
                                    <span class="category-badge"><?= htmlspecialchars($post['category'] ?? 'General') ?></span>
                                    <span class="date-text">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM7 10h5v5H7z"/></svg>
                                        <?= $postDate ?>
                                    </span>
                                </div>
                                <h2 class="blog-card-title">
                                    <a href="<?= $postUrl ?>"><?= htmlspecialchars($post['title']) ?></a>
                                </h2>
                                <p class="blog-card-desc">
                                    <?= htmlspecialchars($post['meta_description'] ?: substr(strip_tags($post['content']), 0, 160) . '...') ?>
                                </p>
                                <?php if (!empty($post['keywords'])): 
                                    $cardKws = array_slice(array_filter(array_map('trim', explode(',', $post['keywords']))), 0, 3);
                                ?>
                                    <div class="card-keywords-row">
                                        <?php foreach ($cardKws as $ckw): ?>
                                            <span class="card-keyword-pill">#<?= htmlspecialchars($ckw) ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                                <div class="blog-card-footer">
                                    <div class="author-info">
                                        <span><?= htmlspecialchars($post['author'] ?: 'SmarTech Team') ?></span>
                                    </div>
                                    <a href="<?= $postUrl ?>" class="read-btn" aria-label="Read full article: <?= htmlspecialchars($post['title']) ?>">
                                        Read Article
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 4l-1.41 1.41L16.17 11H4v2h12.17l-5.58 5.59L12 20l8-8z"/></svg>
                                    </a>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-blog-box">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>
                        <h3>No Articles Published Yet</h3>
                        <p>Check back shortly as our engineering and design teams share upcoming solutions and releases.</p>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </main>

    <!-- Footer & Chat partials -->
    <?php require_once 'partials/aichat.php'; ?>
    <?php require_once 'partials/footer.php'; ?>

    <script>
        // Interactive Client-Side Category Filtering & Instant Search
        (function() {
            const filterButtons = document.querySelectorAll('.filter-btn');
            const searchInput = document.getElementById('searchInput');
            const blogCards = document.querySelectorAll('.blog-card');
            let currentCategory = 'all';

            function applyFilter() {
                const query = (searchInput.value || '').trim().toLowerCase();
                let visibleCount = 0;

                blogCards.forEach(card => {
                    const cardCategory = card.getAttribute('data-category') || '';
                    const cardTitle = card.getAttribute('data-title') || '';
                    const cardKeywords = card.getAttribute('data-keywords') || '';
                    const categoryMatches = (currentCategory === 'all' || cardCategory.toLowerCase() === currentCategory.toLowerCase());
                    const searchMatches = (!query || cardTitle.includes(query) || cardKeywords.includes(query) || card.textContent.toLowerCase().includes(query));

                    if (categoryMatches && searchMatches) {
                        card.style.display = 'flex';
                        visibleCount++;
                    } else {
                        card.style.display = 'none';
                    }
                });
            }

            filterButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    filterButtons.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    currentCategory = this.getAttribute('data-category');
                    applyFilter();
                });
            });

            if (searchInput) {
                searchInput.addEventListener('input', applyFilter);
            }

            // Auto-filter if arriving from a tag pill with ?q=... or ?tag=...
            const urlParams = new URLSearchParams(window.location.search);
            const queryParam = urlParams.get('q') || urlParams.get('tag');
            if (queryParam && searchInput) {
                searchInput.value = queryParam;
                applyFilter();
            }
        })();
    </script>
</body>
</html>

