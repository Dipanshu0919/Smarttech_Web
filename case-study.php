<?php
require_once 'config.php';
require_once 'case-study-engine.php';

$case_studies = get_all_case_studies(true); // Fetch published case studies
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
    <title>Client Case Studies &amp; Success Stories | SmarTech Solutions</title>
    <meta name="description"
        content="Explore real-world client case studies and digital transformation stories in School ERP, Resort Digital Solutions, and Digital Grampanchayat by SmarTech Solutions.">
    <meta name="keywords"
        content="case studies Thane, client success stories Mumbai, school ERP implementation, resort software results, digital grampanchayat case study, SmarTech Solutions portfolio">
    <meta name="author" content="SmarTech Solutions">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    <link rel="canonical" href="https://smartechsolutions.in/case-study.html">
    <link rel="icon" type="image/png" href="assets/smarttechlogo.png">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://smartechsolutions.in/case-study.html">
    <meta property="og:title" content="Client Case Studies &amp; Success Stories | SmarTech Solutions">
    <meta property="og:description"
        content="Read how SmarTech Solutions empowers schools, luxury resorts, and rural local bodies with scalable digital infrastructure.">
    <meta property="og:image" content="https://smartechsolutions.in/assets/smarttechlogo.png">
    <meta property="og:site_name" content="SmarTech Solutions">

    <!-- Twitter Cards -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="https://smartechsolutions.in/case-study.html">
    <meta name="twitter:title" content="Client Case Studies &amp; Success Stories | SmarTech Solutions">
    <meta name="twitter:description"
        content="Read how SmarTech Solutions empowers schools, luxury resorts, and rural local bodies with scalable digital infrastructure.">
    <meta name="twitter:image" content="https://smartechsolutions.in/assets/smarttechlogo.png">

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
            --card-shadow: 0 14px 32px rgba(10, 37, 21, 0.08);
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
            max-width: 1220px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 2.2rem;
        }

        /* --- Distinctive Case Study Hero Card --- */
        .cs-hero-card {
            position: relative;
            border-radius: 26px;
            background: linear-gradient(135deg, #ffffff 0%, #edf7f0 55%, #e2f2e7 100%);
            box-shadow: var(--card-shadow);
            padding: 3.2rem 2.8rem;
            border: 1px solid var(--border);
            overflow: hidden;
        }

        .cs-hero-card::after {
            content: '';
            position: absolute;
            top: -60px;
            right: -60px;
            width: 260px;
            height: 260px;
            background: radial-gradient(circle, rgba(16, 185, 129, 0.18) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        .cs-hero-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.45rem 1.1rem;
            border-radius: 999px;
            background: rgba(24, 121, 48, 0.12);
            color: var(--cs-dark);
            font-size: 0.85rem;
            font-weight: 800;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            margin-bottom: 1.1rem;
        }

        .cs-hero-pill svg {
            width: 17px;
            height: 17px;
            fill: var(--cs-main);
        }

        .cs-hero-title {
            margin: 0 0 1rem;
            font-size: clamp(2.1rem, 4vw, 3.2rem);
            color: var(--cs-dark);
            letter-spacing: -0.03em;
            line-height: 1.15;
            font-weight: 800;
        }

        .cs-hero-sub {
            margin: 0 0 1.6rem;
            color: var(--text-soft);
            font-size: 1.15rem;
            line-height: 1.65;
            max-width: 820px;
        }

        .cs-hero-metrics {
            display: flex;
            gap: 2rem;
            flex-wrap: wrap;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(24, 121, 48, 0.15);
        }

        .cs-metric-item {
            display: flex;
            flex-direction: column;
        }

        .cs-metric-num {
            font-size: 1.8rem;
            font-weight: 800;
            color: var(--cs-dark);
            line-height: 1.1;
        }

        .cs-metric-label {
            font-size: 0.86rem;
            color: var(--text-soft);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        /* --- Filter & Search Controls --- */
        .controls-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1.2rem;
            flex-wrap: wrap;
            background: var(--surface);
            padding: 1.1rem 1.6rem;
            border-radius: 20px;
            border: 1px solid var(--border);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.04);
        }

        .category-filters {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .filter-btn {
            background: rgba(24, 121, 48, 0.06);
            color: var(--cs-dark);
            border: 1px solid rgba(24, 121, 48, 0.15);
            padding: 0.55rem 1.15rem;
            border-radius: 999px;
            font-weight: 700;
            font-size: 0.88rem;
            cursor: pointer;
            transition: all 0.2s ease;
            font-family: inherit;
        }

        .filter-btn:hover {
            background: rgba(24, 121, 48, 0.14);
        }

        .filter-btn.active {
            background: var(--cs-main);
            color: var(--white);
            border-color: var(--cs-main);
            box-shadow: 0 4px 14px rgba(24, 121, 48, 0.28);
        }

        .search-input-wrap {
            position: relative;
            min-width: 270px;
            flex-grow: 1;
            max-width: 390px;
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
            padding: 0.68rem 1rem 0.68rem 2.6rem;
            border-radius: 999px;
            border: 1px solid rgba(24, 121, 48, 0.22);
            font-family: inherit;
            font-size: 0.95rem;
            outline: none;
            background: #ffffff;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .search-input:focus {
            border-color: var(--cs-main);
            box-shadow: 0 0 0 3px rgba(24, 121, 48, 0.16);
        }

        /* --- Case Study Cards Grid --- */
        .cs-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
            gap: 2rem;
        }

        .cs-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 24px;
            overflow: hidden;
            box-shadow: var(--card-shadow);
            transition: transform 0.28s ease, box-shadow 0.28s ease, border-color 0.28s ease;
            display: flex;
            flex-direction: column;
            position: relative;
        }

        .cs-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 20px 42px rgba(10, 37, 21, 0.14);
            border-color: rgba(24, 121, 48, 0.35);
        }

        .cs-card-media {
            position: relative;
            width: 100%;
            height: 220px;
            background: linear-gradient(135deg, #123d20, #0a2515);
            overflow: hidden;
        }

        .cs-card-media img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
        }

        .cs-card:hover .cs-card-media img {
            transform: scale(1.05);
        }

        .cs-no-thumb {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #103b1e, #0c2b16);
            color: rgba(255, 255, 255, 0.35);
        }

        .cs-no-thumb svg {
            width: 64px;
            height: 64px;
        }

        .cs-card-badge-overlay {
            position: absolute;
            top: 1rem;
            left: 1rem;
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .cs-badge-category {
            background: rgba(10, 37, 21, 0.88);
            color: #ffffff;
            backdrop-filter: blur(8px);
            font-size: 0.78rem;
            font-weight: 800;
            padding: 0.35rem 0.8rem;
            border-radius: 999px;
            letter-spacing: 0.03em;
            text-transform: uppercase;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .cs-card-body {
            padding: 1.8rem;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }

        .cs-meta-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.9rem;
            font-size: 0.84rem;
            color: var(--text-soft);
        }

        .cs-client-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            font-weight: 700;
            color: var(--cs-dark);
            background: var(--badge-bg);
            padding: 0.25rem 0.65rem;
            border-radius: 8px;
            border: 1px solid rgba(24, 121, 48, 0.15);
        }

        .cs-client-badge svg {
            width: 13px;
            height: 13px;
            fill: var(--cs-main);
        }

        .cs-date-text {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
        }

        .cs-date-text svg {
            width: 14px;
            height: 14px;
            fill: var(--text-soft);
        }

        .cs-card-title {
            margin: 0 0 0.85rem;
            font-size: 1.32rem;
            line-height: 1.35;
            font-weight: 800;
            color: var(--cs-dark);
        }

        .cs-card-title a {
            color: inherit;
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .cs-card-title a:hover {
            color: var(--cs-main);
        }

        .cs-card-desc {
            margin: 0 0 1.2rem;
            color: var(--text-soft);
            font-size: 0.96rem;
            line-height: 1.55;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            flex-grow: 1;
        }

        .cs-card-keywords {
            display: flex;
            gap: 0.4rem;
            flex-wrap: wrap;
            margin-bottom: 1.4rem;
        }

        .cs-keyword-pill {
            background: #f1f7f2;
            color: var(--cs-dark);
            font-size: 0.76rem;
            font-weight: 700;
            padding: 0.2rem 0.6rem;
            border-radius: 6px;
            border: 1px solid rgba(24, 121, 48, 0.12);
        }

        .cs-card-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 1.2rem;
            border-top: 1px solid rgba(24, 121, 48, 0.12);
            margin-top: auto;
        }

        .cs-author-text {
            font-size: 0.84rem;
            font-weight: 600;
            color: var(--text-soft);
        }

        .cs-read-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            background: var(--cs-main);
            color: var(--white);
            padding: 0.55rem 1.15rem;
            border-radius: 999px;
            font-size: 0.88rem;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .cs-read-btn:hover {
            background: var(--cs-dark);
            transform: translateX(2px);
        }

        .cs-read-btn svg {
            width: 15px;
            height: 15px;
            fill: currentColor;
            transition: transform 0.2s ease;
        }

        .cs-read-btn:hover svg {
            transform: translateX(3px);
        }

        /* --- Empty State --- */
        .empty-cs-box {
            grid-column: 1 / -1;
            background: var(--surface);
            border: 1px dashed rgba(24, 121, 48, 0.3);
            border-radius: 24px;
            padding: 4.5rem 2rem;
            text-align: center;
            color: var(--text-soft);
        }

        .empty-cs-box svg {
            width: 68px;
            height: 68px;
            fill: var(--cs-main);
            margin-bottom: 1.2rem;
            opacity: 0.85;
        }

        .empty-cs-box h3 {
            color: var(--cs-dark);
            font-size: 1.6rem;
            margin: 0 0 0.6rem;
            font-weight: 800;
        }

        .empty-cs-box p {
            font-size: 1.05rem;
            margin: 0;
            max-width: 520px;
            margin: 0 auto;
            line-height: 1.6;
        }

        @media (max-width: 768px) {
            .cs-hero-card {
                padding: 2.2rem 1.5rem;
            }

            .cs-grid {
                grid-template-columns: 1fr;
            }

            .controls-bar {
                flex-direction: column;
                align-items: stretch;
            }

            .search-input-wrap {
                max-width: 100%;
            }
        }
    </style>
</head>

<body>
    <!-- Sidebar nav partial -->
    <?php require_once 'partials/header.php'; ?>

    <main>
        <div class="page-wrap">
            <!-- Hero Card -->
            <section class="cs-hero-card">
                <div class="cs-hero-pill">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M19 5h-2V3H7v2H5c-1.1 0-2 .9-2 2v1c0 2.55 1.92 4.63 4.39 4.94A5.01 5.01 0 0 0 11 15.9V19H7v2h10v-2h-4v-3.1a5.01 5.01 0 0 0 3.61-4.96C19.08 10.63 21 8.55 21 6V5c0-1.1-.9-2-2-2zM5 8V7h2v3.82C5.84 10.4 5 9.3 5 8zm14 0c0 1.3-.84 2.4-2 2.82V7h2v1z"/>
                    </svg>
                    Proven Client Impact &amp; Results
                </div>
                <h1 class="cs-hero-title">Client Case Studies &amp; Proven Solutions</h1>
                <p class="cs-hero-sub">
                    Explore how SmarTech Solutions engineers high-impact software, school ERP ecosystems, resort booking platforms, and rural civic governance portals across India.
                </p>
                <div class="cs-hero-metrics">
                    <div class="cs-metric-item">
                        <div class="cs-metric-num">500+</div>
                        <div class="cs-metric-label">Completed Deployments</div>
                    </div>
                    <div class="cs-metric-item">
                        <div class="cs-metric-num">98.9%</div>
                        <div class="cs-metric-label">Client Satisfaction</div>
                    </div>
                    <div class="cs-metric-item">
                        <div class="cs-metric-num">100k+</div>
                        <div class="cs-metric-label">End-Users Powered</div>
                    </div>
                </div>
            </section>

            <!-- Controls: Category Filters & Live Search -->
            <section class="controls-bar">
                <div class="category-filters" id="categoryFilters">
                    <button type="button" class="filter-btn active" data-category="all">All Case Studies</button>
                    <button type="button" class="filter-btn" data-category="School App Solution">School ERP</button>
                    <button type="button" class="filter-btn" data-category="Resort Digital Solution">Resort Tech</button>
                    <button type="button" class="filter-btn" data-category="Digital Grampanchayat">Grampanchayat</button>
                    <button type="button" class="filter-btn" data-category="Technology">Web &amp; Apps</button>
                </div>
                <div class="search-input-wrap">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M15.5 14h-.79l-.28-.27A6.471 6.471 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
                    </svg>
                    <input type="text" id="searchInput" class="search-input" placeholder="Search case studies by client or topic..." aria-label="Search case studies">
                </div>
            </section>

            <!-- Case Study Grid -->
            <section class="cs-grid" id="csGrid">
                <?php if (!empty($case_studies)): ?>
                    <?php foreach ($case_studies as $cs): 
                        $csDate = !empty($cs['created_at']) ? date('M d, Y', strtotime($cs['created_at'])) : date('M d, Y');
                        $csSlug = htmlspecialchars($cs['slug']);
                        $csUrl = '/case-study/' . $csSlug;
                        $featuredImg = $cs['featured_image'] ?? '';
                    ?>
                        <article class="cs-card" data-category="<?= htmlspecialchars($cs['category'] ?? 'Technology') ?>" data-title="<?= htmlspecialchars(strtolower($cs['title'])) ?>" data-client="<?= htmlspecialchars(strtolower($cs['client_name'] ?? '')) ?>" data-keywords="<?= htmlspecialchars(strtolower($cs['keywords'] ?? '')) ?>">
                            <div class="cs-card-media">
                                <?php if (!empty($featuredImg)): ?>
                                    <img src="<?= htmlspecialchars($featuredImg) ?>" alt="<?= htmlspecialchars($cs['title']) ?>" loading="lazy">
                                <?php else: ?>
                                    <div class="cs-no-thumb">
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/>
                                        </svg>
                                    </div>
                                <?php endif; ?>
                                <div class="cs-card-badge-overlay">
                                    <span class="cs-badge-category"><?= htmlspecialchars($cs['category'] ?? 'Case Study') ?></span>
                                </div>
                            </div>
                            <div class="cs-card-body">
                                <div class="cs-meta-row">
                                    <?php if (!empty($cs['client_name'])): ?>
                                        <span class="cs-client-badge">
                                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 7V3H2v18h20V7H12zM6 19H4v-2h2v2zm0-4H4v-2h2v2zm0-4H4V9h2v2zm0-4H4V5h2v2zm4 12H8v-2h2v2zm0-4H8v-2h2v2zm0-4H8V9h2v2zm0-4H8V5h2v2zm10 12h-8v-2h2v-2h-2v-2h2v-2h-2V9h8v10zm-2-8h-2v2h2v-2zm0 4h-2v2h2v-2z"/></svg>
                                            <?= htmlspecialchars($cs['client_name']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="cs-client-badge">Client Project</span>
                                    <?php endif; ?>
                                    <span class="cs-date-text">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM7 10h5v5H7z"/></svg>
                                        <?= $csDate ?>
                                    </span>
                                </div>
                                <h2 class="cs-card-title">
                                    <a href="<?= $csUrl ?>"><?= htmlspecialchars($cs['title']) ?></a>
                                </h2>
                                <?php if (!empty($cs['meta_description'])): ?>
                                    <p class="cs-card-desc">
                                        <?= htmlspecialchars(trim($cs['meta_description'])) ?>
                                    </p>
                                <?php endif; ?>
                                <?php if (!empty($cs['keywords'])): 
                                    $cardKws = array_slice(array_filter(array_map('trim', explode(',', $cs['keywords']))), 0, 3);
                                ?>
                                    <div class="cs-card-keywords">
                                        <?php foreach ($cardKws as $ckw): ?>
                                            <span class="cs-keyword-pill">#<?= htmlspecialchars($ckw) ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                                <div class="cs-card-footer">
                                    <span class="cs-author-text"><?= htmlspecialchars($cs['author'] ?: 'SmarTech Team') ?></span>
                                    <a href="<?= $csUrl ?>" class="cs-read-btn" aria-label="Explore Case Study: <?= htmlspecialchars($cs['title']) ?>">
                                        View Case Study
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 4l-1.41 1.41L16.17 11H4v2h12.17l-5.58 5.59L12 20l8-8z"/></svg>
                                    </a>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-cs-box">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>
                        <h3>No Case Studies Published Yet</h3>
                        <p>Our product and solution architects are preparing in-depth case studies. Please check back shortly.</p>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </main>

    <!-- Footer & Chat partials -->
    <?php require_once 'partials/aichat.php'; ?>
    <?php require_once 'partials/footer.php'; ?>

    <script>
        (function() {
            const filterButtons = document.querySelectorAll('.filter-btn');
            const searchInput = document.getElementById('searchInput');
            const cards = document.querySelectorAll('.cs-card');
            let currentCategory = 'all';

            function applyFilter() {
                const query = (searchInput.value || '').trim().toLowerCase();

                cards.forEach(card => {
                    const cardCat = card.getAttribute('data-category') || '';
                    const cardTitle = card.getAttribute('data-title') || '';
                    const cardClient = card.getAttribute('data-client') || '';
                    const cardKws = card.getAttribute('data-keywords') || '';
                    const catMatches = (currentCategory === 'all' || cardCat.toLowerCase() === currentCategory.toLowerCase());
                    const searchMatches = (!query || cardTitle.includes(query) || cardClient.includes(query) || cardKws.includes(query) || card.textContent.toLowerCase().includes(query));

                    if (catMatches && searchMatches) {
                        card.style.display = 'flex';
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

