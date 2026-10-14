<?php
require_once 'config.php';
require_once 'index.php';
require_once 'blog-engine.php';

// Ensure user is authenticated as admin
if (!dashboard_session_is_valid()) {
    header('Location: dashboard.php?error=login_required');
    exit;
}

$blogs = get_all_blogs();
$blog_count = count($blogs);
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
    <title>Blog Posts Management | SmarTech Solutions Admin</title>
    <meta name="description" content="Dedicated Blog Posts Management and Interactive Editor for SmarTech Solutions.">
    <style>
        :root {
            --green-dark: #0f381c;
            --green-main: #228b22;
            --green-light: #4cbb17;
            --white: #ffffff;
            --text-dark: #203026;
            --text-soft: #5a6a60;
            --bg-light: #f4faf4;
            --surface: rgba(255, 255, 255, 0.96);
            --border: rgba(34, 139, 34, 0.12);
            --shadow: 0 20px 52px rgba(15, 56, 28, 0.1);
        }

        * { box-sizing: border-box; }

        html, body {
            margin: 0;
            min-height: 100%;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background:
                radial-gradient(circle at top left, rgba(76, 187, 23, 0.16), transparent 24%),
                radial-gradient(circle at bottom right, rgba(15, 56, 28, 0.1), transparent 30%),
                linear-gradient(180deg, #f9fef9 0%, var(--bg-light) 100%);
            color: var(--text-dark);
        }

        main {
            padding: 2rem;
        }

        .dashboard-shell {
            max-width: 1360px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .topbar {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 0.5rem;
        }

        .title {
            margin: 0;
            font-size: clamp(1.8rem, 3vw, 2.5rem);
            line-height: 1.1;
            letter-spacing: -0.04em;
            color: var(--green-dark);
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }

        .nav-pills {
            display: flex;
            gap: 0.6rem;
            flex-wrap: wrap;
            align-items: center;
        }

        .nav-pills a, .btn-action {
            text-decoration: none;
            color: var(--green-dark);
            background: rgba(34, 139, 34, 0.08);
            border: 1px solid rgba(34, 139, 34, 0.14);
            padding: 0.65rem 1.1rem;
            border-radius: 999px;
            font-weight: 800;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            font-family: inherit;
        }

        .nav-pills a:hover, .btn-action:hover {
            background: var(--green-main);
            color: var(--white);
            border-color: var(--green-main);
        }

        .btn-primary {
            background: var(--green-main) !important;
            color: var(--white) !important;
            border-color: var(--green-main) !important;
        }

        .btn-primary:hover {
            background: var(--green-dark) !important;
        }

        .logout-button {
            background: rgba(180, 35, 24, 0.1);
            color: #b42318;
            border: 1px solid rgba(180, 35, 24, 0.2);
            padding: 0.65rem 1.1rem;
            border-radius: 999px;
            font-weight: 800;
            cursor: pointer;
            font-family: inherit;
        }

        .logout-button:hover {
            background: #b42318;
            color: #ffffff;
        }

        .section-card {
            background: var(--surface);
            backdrop-filter: blur(12px);
            border: 1px solid var(--border);
            border-radius: 24px;
            box-shadow: var(--shadow);
            padding: 1.5rem;
        }

        .section-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 1.2rem;
        }

        .section-head h2 {
            margin: 0;
            color: var(--green-dark);
            font-size: 1.35rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.35rem 0.75rem;
            border-radius: 999px;
            background: rgba(34, 139, 34, 0.08);
            color: var(--green-dark);
            font-weight: 800;
            font-size: 0.85rem;
        }

        .badge-published {
            background: #e8f5e9;
            color: #1b5e20;
            border: 1px solid rgba(27, 94, 32, 0.2);
        }

        .badge-draft {
            background: #fff8e1;
            color: #f57f17;
            border: 1px solid rgba(245, 127, 23, 0.2);
        }

        .table-wrap {
            overflow: auto;
            border-radius: 18px;
            border: 1px solid rgba(34, 139, 34, 0.08);
            background: #ffffff;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 980px;
        }

        thead th {
            background: linear-gradient(180deg, #f8fcf8, #eef8ee);
            color: var(--green-dark);
            text-align: left;
            font-size: 0.88rem;
            padding: 0.95rem 1rem;
            border-bottom: 1px solid rgba(34, 139, 34, 0.12);
            white-space: nowrap;
        }

        tbody td {
            padding: 0.95rem 1rem;
            border-bottom: 1px solid rgba(34, 139, 34, 0.08);
            vertical-align: top;
            line-height: 1.5;
            font-size: 0.95rem;
        }

        tbody tr:hover {
            background: rgba(34, 139, 34, 0.03);
        }

        .action-cell {
            display: flex;
            gap: 0.4rem;
            align-items: center;
            white-space: nowrap;
        }

        .btn-table-action {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            border: 0;
            border-radius: 999px;
            padding: 0.5rem 0.85rem;
            font-size: 0.84rem;
            font-weight: 800;
            cursor: pointer;
            text-decoration: none;
            font-family: inherit;
        }

        .btn-edit {
            background: rgba(34, 139, 34, 0.1);
            color: var(--green-dark);
        }
        .btn-edit:hover {
            background: var(--green-main);
            color: var(--white);
        }

        .btn-view {
            background: rgba(15, 56, 28, 0.08);
            color: var(--green-dark);
        }
        .btn-view:hover {
            background: var(--green-dark);
            color: var(--white);
        }

        .btn-delete {
            background: #fee4e2;
            color: #b42318;
        }
        .btn-delete:hover {
            background: #b42318;
            color: #ffffff;
        }

        .empty-state {
            padding: 2.5rem 1rem;
            text-align: center;
            color: var(--text-soft);
        }

        .empty-state strong {
            display: block;
            color: var(--green-dark);
            margin-bottom: 0.35rem;
            font-size: 1.1rem;
        }

        /* --- Editor Styling --- */
        .editor-container {
            display: flex;
            flex-direction: column;
            gap: 1.4rem;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 1.2rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
        }

        .form-group label {
            font-size: 0.88rem;
            font-weight: 700;
            color: var(--green-dark);
        }

        .form-input, .form-select, .form-textarea {
            width: 100%;
            padding: 0.75rem 1rem;
            border-radius: 12px;
            border: 1px solid rgba(34, 139, 34, 0.2);
            font-family: inherit;
            font-size: 0.95rem;
            outline: none;
            background: #ffffff;
            transition: all 0.2s ease;
        }

        .form-input:focus, .form-select:focus, .form-textarea:focus {
            border-color: var(--green-main);
            box-shadow: 0 0 0 3px rgba(34, 139, 34, 0.15);
        }

        /* Top Image Upload Dropzone */
        .upload-section-box {
            background: #f8fbf8;
            border: 1px solid rgba(34, 139, 34, 0.18);
            border-radius: 18px;
            padding: 1.4rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .upload-section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.6rem;
        }

        .upload-section-header h3 {
            margin: 0;
            font-size: 1.1rem;
            color: var(--green-dark);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .upload-dropzone {
            border: 2px dashed rgba(34, 139, 34, 0.3);
            border-radius: 14px;
            padding: 1.6rem 1rem;
            text-align: center;
            background: #ffffff;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .upload-dropzone:hover, .upload-dropzone.dragover {
            border-color: var(--green-main);
            background: #f2faf2;
        }

        .upload-dropzone svg {
            width: 36px;
            height: 36px;
            fill: var(--green-main);
            margin-bottom: 0.4rem;
        }

        .upload-dropzone p {
            margin: 0;
            color: var(--text-dark);
            font-weight: 700;
            font-size: 0.95rem;
        }

        .upload-dropzone span {
            color: var(--text-soft);
            font-size: 0.82rem;
        }

        .images-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
            gap: 1rem;
        }

        .image-card-chip {
            background: #ffffff;
            border: 1px solid rgba(34, 139, 34, 0.18);
            border-radius: 14px;
            padding: 0.75rem;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
        }

        .image-thumb-wrap {
            width: 100%;
            height: 110px;
            border-radius: 10px;
            overflow: hidden;
            background: #f0f0f0;
        }

        .image-thumb-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .image-tag-badge {
            background: var(--green-dark);
            color: #ffffff;
            padding: 0.25rem 0.6rem;
            border-radius: 6px;
            font-family: monospace;
            font-weight: 700;
            font-size: 0.85rem;
            text-align: center;
        }

        .image-actions-row {
            display: flex;
            gap: 0.4rem;
        }

        .image-action-btn {
            flex: 1;
            padding: 0.4rem 0.5rem;
            border-radius: 8px;
            border: 0;
            font-size: 0.78rem;
            font-weight: 700;
            cursor: pointer;
            font-family: inherit;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.25rem;
        }

        .btn-insert-tag {
            background: rgba(34, 139, 34, 0.12);
            color: var(--green-dark);
        }
        .btn-insert-tag:hover {
            background: var(--green-main);
            color: #ffffff;
        }

        .btn-del-img {
            background: #fee4e2;
            color: #b42318;
            max-width: 32px;
        }
        .btn-del-img:hover {
            background: #b42318;
            color: #ffffff;
        }

        /* External Image Helper */
        .external-img-box {
            display: flex;
            gap: 0.8rem;
            align-items: center;
            flex-wrap: wrap;
            background: #ffffff;
            padding: 0.8rem 1rem;
            border-radius: 12px;
            border: 1px solid rgba(34, 139, 34, 0.15);
        }

        /* Tap to Paste Palette */
        .palette-box {
            background: #fdfefe;
            border: 1px solid rgba(34, 139, 34, 0.16);
            border-radius: 16px;
            padding: 1.2rem;
            display: flex;
            flex-direction: column;
            gap: 0.8rem;
        }

        .palette-title-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .palette-title-row strong {
            color: var(--green-dark);
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .palette-groups {
            display: flex;
            flex-direction: column;
            gap: 0.65rem;
        }

        .palette-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .group-label {
            font-size: 0.78rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: var(--text-soft);
            min-width: 85px;
        }

        .placeholder-pill {
            background: rgba(34, 139, 34, 0.08);
            color: var(--green-dark);
            border: 1px solid rgba(34, 139, 34, 0.14);
            border-radius: 8px;
            padding: 0.35rem 0.7rem;
            font-size: 0.82rem;
            font-weight: 700;
            font-family: monospace;
            cursor: pointer;
            transition: all 0.15s ease;
            user-select: none;
        }

        .placeholder-pill:hover {
            background: var(--green-main);
            color: var(--white);
            border-color: var(--green-main);
            transform: translateY(-1px);
        }

        /* Split Editor Layout */
        .split-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.4rem;
            align-items: start;
        }

        .editor-pane, .preview-pane {
            display: flex;
            flex-direction: column;
            gap: 0.6rem;
        }

        .pane-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.2rem 0.2rem 0.4rem;
        }

        .pane-header h4 {
            margin: 0;
            color: var(--green-dark);
            font-size: 0.95rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .content-editor-textarea {
            width: 100%;
            height: 520px;
            font-family: 'Consolas', 'Courier New', monospace;
            font-size: 0.95rem;
            line-height: 1.6;
            padding: 1.1rem;
            border-radius: 16px;
            border: 1px solid rgba(34, 139, 34, 0.2);
            background: #ffffff;
            color: #1a271e;
            outline: none;
            resize: vertical;
        }

        .content-editor-textarea:focus {
            border-color: var(--green-main);
            box-shadow: 0 0 0 3px rgba(34, 139, 34, 0.15);
        }

        /* Live Preview Container */
        .preview-content-box {
            width: 100%;
            height: 520px;
            overflow-y: auto;
            background: #ffffff;
            border-radius: 16px;
            border: 1px solid rgba(34, 139, 34, 0.18);
            padding: 1.8rem;
            box-shadow: inset 0 2px 8px rgba(0, 0, 0, 0.02);
            line-height: 1.75;
        }

        /* Preview Typography */
        .preview-content-box .blog-display-title { font-size: 1.9rem; color: var(--green-dark); margin: 0 0 1rem; font-weight: 800; }
        .preview-content-box .blog-h1 { font-size: 1.8rem; color: var(--green-dark); margin: 1.5rem 0 0.6rem; font-weight: 800; }
        .preview-content-box .blog-h2 { font-size: 1.5rem; color: var(--green-dark); margin: 1.3rem 0 0.5rem; font-weight: 800; }
        .preview-content-box .blog-h3 { font-size: 1.3rem; color: var(--green-dark); margin: 1.2rem 0 0.4rem; font-weight: 700; }
        .preview-content-box .blog-h4 { font-size: 1.15rem; color: var(--green-dark); margin: 1rem 0 0.4rem; font-weight: 700; }
        .preview-content-box .blog-h5, .preview-content-box .blog-h6 { font-size: 1.05rem; color: var(--green-dark); margin: 0.9rem 0 0.3rem; font-weight: 700; }
        .preview-content-box .blog-p { margin: 1rem 0; }
        .preview-content-box .blog-strong { font-weight: 700; color: var(--green-dark); }
        .preview-content-box .blog-em { font-style: italic; }
        .preview-content-box .blog-underline { text-decoration: underline; }
        .preview-content-box .blog-mark { background: #e8f5e9; color: var(--green-dark); padding: 0.1rem 0.4rem; border-radius: 4px; }
        .preview-content-box .blog-quote { background: #f4faf4; border-left: 4px solid var(--green-main); border-radius: 0 12px 12px 0; padding: 1rem 1.4rem; margin: 1.2rem 0; font-style: italic; color: var(--green-dark); }
        .preview-content-box .blog-code-block { background: #101c13; color: #8dfd98; padding: 1rem 1.2rem; border-radius: 12px; font-family: monospace; font-size: 0.9rem; overflow-x: auto; margin: 1.2rem 0; }
        .preview-content-box .blog-callout { display: flex; gap: 0.8rem; background: #eef8ee; border: 1px solid rgba(34, 139, 34, 0.2); border-radius: 14px; padding: 1rem 1.2rem; margin: 1.2rem 0; }
        .preview-content-box .blog-tip-box { display: flex; gap: 0.8rem; background: #fffbe8; border: 1px solid rgba(220, 180, 0, 0.35); border-radius: 14px; padding: 1rem 1.2rem; margin: 1.2rem 0; color: #634f00; }
        .preview-content-box .blog-figure { margin: 1.5rem 0; text-align: center; }
        .preview-content-box .blog-responsive-img { max-width: 100%; max-height: 400px; width: auto; height: auto; object-fit: contain; border-radius: 12px; box-shadow: 0 8px 20px rgba(0,0,0,0.06); display: inline-block; }
        .preview-content-box .blog-fig-caption { font-size: 0.85rem; color: var(--text-soft); font-style: italic; margin-top: 0.4rem; }
        .preview-content-box .blog-action-btn { display: inline-flex; align-items: center; gap: 0.4rem; background: var(--green-main); color: #ffffff; padding: 0.65rem 1.4rem; border-radius: 999px; text-decoration: none; font-weight: 700; font-size: 0.95rem; }
        .preview-content-box .blog-inline-link { color: var(--green-main); font-weight: 700; text-decoration: underline; }
        .preview-content-box .blog-bullet-list, .preview-content-box .blog-ordered-list { padding-left: 1.6rem; margin: 1rem 0; }
        .preview-content-box .blog-bullet-list li, .preview-content-box .blog-ordered-list li { margin-bottom: 0.4rem; }
        .preview-content-box .blog-divider { border: 0; height: 1px; background: rgba(34, 139, 34, 0.2); margin: 2rem 0; }
        .preview-content-box .blog-img-missing { background: #fff3cd; color: #856404; padding: 0.8rem 1rem; border-radius: 8px; border: 1px dashed #ffeeba; font-size: 0.9rem; text-align: center; margin: 1rem 0; }

        .editor-footer-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
            padding-top: 1rem;
            border-top: 1px solid rgba(34, 139, 34, 0.1);
        }

        .status-message {
            font-size: 0.92rem;
            font-weight: 700;
        }
        .status-success { color: #1b5e20; }
        .status-error { color: #b42318; }

        @media (max-width: 992px) {
            .split-layout {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 600px) {
            main {
                padding: 1rem;
            }
        }
    </style>
</head>

<body>
    <?php require_once 'partials/header.php'; ?>

    <main>
        <section class="dashboard-shell">
            <!-- Topbar Navigation -->
            <div class="topbar">
                <div>
                    <h1 class="title">
                        <svg viewBox="0 0 24 24" width="30" height="30" fill="currentColor" aria-hidden="true"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>
                        Blog Posts Management
                    </h1>
                    <div style="color:var(--text-soft); font-size:0.9rem; margin-top:0.3rem;">
                        Manage articles, keywords, and live previews for the public blog.
                    </div>
                </div>

                <div class="nav-pills">
                    <a href="dashboard.php">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>
                        Dashboard
                    </a>
                    <a href="admin-case-studies.php">
                        Case Studies
                        <svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor"><path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6-1.41-1.41z"/></svg>
                    </a>
                    <button type="button" class="btn-action btn-primary" onclick="openNewBlog()">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
                        Create New Blog
                    </button>
                    <a href="/blog.html" target="_blank">
                        View Live Blog
                        <svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor"><path d="M19 19H5V5h7V3H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2v-7h-2v7zM14 3v2h3.59l-9.83 9.83 1.41 1.41L19 6.41V10h2V3h-7z"/></svg>
                    </a>
                    <form method="post" action="dashboard.php?action=logout" style="margin:0;">
                        <button class="logout-button" type="submit">Sign Out</button>
                    </form>
                </div>
            </div>

            <!-- Blog Posts Table Section -->
            <section class="section-card" id="blogs">
                <div class="section-head">
                    <h2>
                        <svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor" aria-hidden="true"><path d="M4 6H2v14c0 1.1.9 2 2 2h14v-2H4V6zm16-4H8c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H8V4h12v12z"/></svg>
                        All Blog Posts
                    </h2>
                    <span class="badge"><?= $blog_count ?> total posts</span>
                </div>

                <?php if (!empty($blogs)): ?>
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Title &amp; Slug</th>
                                    <th>Category</th>
                                    <th>Author</th>
                                    <th>Status</th>
                                    <th>Published Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($blogs as $b): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($b['id']) ?></td>
                                        <td>
                                            <strong style="color:var(--green-dark);"><?= htmlspecialchars($b['title']) ?></strong>
                                            <div style="color:var(--text-soft); font-size:0.8rem; font-family:monospace;">/blog/<?= htmlspecialchars($b['slug']) ?></div>
                                            <?php if (!empty($b['keywords'])): ?>
                                                <div style="color:var(--text-soft); font-size:0.75rem; margin-top:3px; word-break:break-word;">
                                                    <span style="font-weight:700; color:var(--green-main);">Tags:</span> <?= htmlspecialchars($b['keywords']) ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td><span class="badge"><?= htmlspecialchars($b['category'] ?? 'General') ?></span></td>
                                        <td><?= htmlspecialchars($b['author'] ?: 'SmarTech Team') ?></td>
                                        <td>
                                            <span class="badge <?= ($b['status'] ?? 'published') === 'published' ? 'badge-published' : 'badge-draft' ?>">
                                                <?= ucfirst(htmlspecialchars($b['status'] ?? 'published')) ?>
                                            </span>
                                        </td>
                                        <td style="white-space:nowrap;"><?= !empty($b['created_at']) ? date('M d, Y', strtotime($b['created_at'])) : '-' ?></td>
                                        <td>
                                            <div class="action-cell">
                                                <button type="button" class="btn-table-action btn-edit" onclick="editBlog(<?= $b['id'] ?>)">
                                                    <svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
                                                    Edit
                                                </button>
                                                <a href="/blog/<?= htmlspecialchars($b['slug']) ?>" target="_blank" class="btn-table-action btn-view">
                                                    View
                                                </a>
                                                <form method="post" action="admin-blogs.php?action=delete_blog&id=<?= $b['id'] ?>" onsubmit="return confirm('Permanently delete blog post: <?= addslashes($b['title']) ?>?');" style="margin:0;">
                                                    <button class="btn-table-action btn-delete" type="submit">Delete</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <strong>No blog posts found</strong>
                        Click "Create New Blog" below to write and publish your first article.
                    </div>
                <?php endif; ?>
            </section>

            <!-- Full Interactive Blog Post Editor -->
            <section class="section-card" id="blog-editor">
                <div class="section-head">
                    <h2 id="editorHeading">
                        <svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor" aria-hidden="true"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
                        Create New Blog Post
                    </h2>
                    <button type="button" class="btn-action" onclick="openNewBlog()">Reset to Blank Post</button>
                </div>

                <form id="blogPostForm" onsubmit="saveBlogPost(event)">
                    <input type="hidden" id="blogId" name="id" value="">
                    <input type="hidden" id="blogImagesJson" name="images_json" value="{}">

                    <div class="editor-container">
                        <!-- Basic Metadata Inputs -->
                        <div class="form-row">
                            <div class="form-group" style="flex:2;">
                                <label for="postTitle">Blog Title *</label>
                                <input type="text" id="postTitle" class="form-input" placeholder="e.g. 10 Reasons Your School Needs an ERP System" required oninput="handleTitleInput(this.value)">
                            </div>
                            <div class="form-group" style="flex:1;">
                                <label for="postSlug">URL Slug (Auto-generated)</label>
                                <input type="text" id="postSlug" class="form-input" placeholder="e.g. 10-reasons-school-erp-system">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="postCategory">Category</label>
                                <input type="text" id="postCategory" class="form-input" list="categoryOptions" placeholder="e.g. Technology, Education" value="Technology" onchange="updateLivePreview()">
                                <datalist id="categoryOptions">
                                    <option value="Education">
                                    <option value="School App Solution">
                                    <option value="Hospitality &amp; Resorts">
                                    <option value="Grampanchayat &amp; Civic">
                                    <option value="Web Development">
                                    <option value="Technology">
                                </datalist>
                            </div>
                            <div class="form-group">
                                <label for="postAuthor">Author</label>
                                <input type="text" id="postAuthor" class="form-input" value="SmarTech Team" oninput="updateLivePreview()">
                            </div>
                            <div class="form-group">
                                <label for="postStatus">Publication Status</label>
                                <select id="postStatus" class="form-select">
                                    <option value="published">Published (Live on Website)</option>
                                    <option value="draft">Draft (Private in Admin Panel)</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group" style="flex:2;">
                                <label for="postMetaDesc">Short Description / SEO Excerpt</label>
                                <input type="text" id="postMetaDesc" class="form-input" placeholder="Brief 1-2 sentence summary for Google search results and preview cards...">
                            </div>
                            <div class="form-group" style="flex:1;">
                                <label for="postFeaturedImg">Featured Image URL (Banner)</label>
                                <div style="display:flex; gap:0.4rem;">
                                    <input type="url" id="postFeaturedImg" class="form-input" placeholder="https://... (or leave blank to use attached image)" oninput="updateLivePreview()">
                                    <button type="button" class="btn-action" style="padding:0.4rem 0.8rem; font-size:0.8rem; white-space:nowrap;" onclick="document.getElementById('postFeaturedImg').value=''; updateLivePreview();" title="Clear Featured Banner">Clear</button>
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group" style="flex:1;">
                                <label for="postKeywords">
                                    SEO Keywords &amp; Tags
                                    <span style="font-size:0.8rem; font-weight:normal; color:var(--text-soft);">(Comma-separated keywords for SEO meta tags)</span>
                                </label>
                                <input type="text" id="postKeywords" class="form-input" placeholder="school software, erp system, palghar tech solutions, smart education">
                            </div>
                        </div>

                        <!-- 1. Top Image Upload Section with Auto-Generated Placeholders -->
                        <div class="upload-section-box">
                            <div class="upload-section-header">
                                <h3>
                                    <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/></svg>
                                    Upload Post Images (Auto-Generates Placeholders: {{ image_1 }}, {{ image_2 }}...)
                                </h3>
                                <span style="font-size:0.84rem; color:var(--text-soft);">Upload multiple images at the top; place them anywhere in the content using their tags.</span>
                            </div>

                            <!-- Dropzone -->
                            <div class="upload-dropzone" id="imageDropzone" onclick="document.getElementById('multiImageInput').click()">
                                <input type="file" id="multiImageInput" style="display:none;" multiple accept="image/*" onchange="handleMultipleImageUpload(this.files)">
                                <svg viewBox="0 0 24 24"><path d="M19.35 10.04C18.67 6.59 15.64 4 12 4 9.11 4 6.6 5.64 5.35 8.04 2.34 8.36 0 10.91 0 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96zM14 13v4h-4v-4H7l5-5 5 5h-3z"/></svg>
                                <p>Click or drag &amp; drop images here to upload</p>
                                <span>Supports JPG, PNG, WebP, SVG. Automatically assigns {{ image_1 }}, {{ image_2 }}, etc.</span>
                            </div>

                            <!-- Attached Images Gallery -->
                            <div class="images-grid" id="uploadedImagesGrid"></div>

                            <!-- Online External Image Link Option -->
                            <div class="external-img-box">
                                <strong style="color:var(--green-dark); font-size:0.88rem; display:flex; align-items:center; gap:0.3rem;">
                                    <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M3.9 12c0-1.71 1.39-3.1 3.1-3.1h4V7H7c-2.76 0-5 2.24-5 5s2.24 5 5 5h4v-1.9H7c-1.71 0-3.1-1.39-3.1-3.1zM8 13h8v-2H8v2zm9-6h-4v1.9h4c1.71 0 3.1 1.39 3.1 3.1s-1.39 3.1-3.1 3.1h-4V17h4c2.76 0 5-2.24 5-5s-2.24-5-5-5z"/></svg>
                                    Insert Online Image Link:
                                </strong>
                                <input type="url" id="onlineImgUrl" class="form-input" style="flex:2; min-width:200px;" placeholder="https://example.com/photo.jpg">
                                <input type="text" id="onlineImgCaption" class="form-input" style="flex:1; min-width:160px;" placeholder="Optional caption...">
                                <button type="button" class="btn-action btn-primary" onclick="insertOnlineImagePlaceholder()" style="padding:0.6rem 1rem;">
                                    Insert {{ image_link }}
                                </button>
                            </div>
                        </div>

                        <!-- 2. Tap to Paste Placeholders Palette Toolbar -->
                        <div class="palette-box">
                            <div class="palette-title-row">
                                <strong>
                                    <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M19 2h-4.18C14.4 .84 13.3 0 12 0c-1.3 0-2.4.84-2.82 2H5c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-7 0c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm7 18H5V4h2v3h10V4h2v16z"/></svg>
                                    Tap to Paste Placeholders
                                </strong>
                                <span style="font-size:0.82rem; color:var(--text-soft);">Tap any button to insert at cursor or wrap selected text in the content editor.</span>
                            </div>

                            <div class="palette-groups">
                                <!-- Headings -->
                                <div class="palette-group">
                                    <span class="group-label">Headings:</span>
                                    <button type="button" class="placeholder-pill" onclick="wrapPlaceholder('title', 'Main Blog Title')">+ {{ title }}</button>
                                    <button type="button" class="placeholder-pill" onclick="wrapPlaceholder('h1', 'Heading 1')">+ {{ h1 }}</button>
                                    <button type="button" class="placeholder-pill" onclick="wrapPlaceholder('h2', 'Heading 2')">+ {{ h2 }}</button>
                                    <button type="button" class="placeholder-pill" onclick="wrapPlaceholder('h3', 'Heading 3')">+ {{ h3 }}</button>
                                    <button type="button" class="placeholder-pill" onclick="wrapPlaceholder('h4', 'Heading 4')">+ {{ h4 }}</button>
                                </div>

                                <!-- Text styles -->
                                <div class="palette-group">
                                    <span class="group-label">Formatting:</span>
                                    <button type="button" class="placeholder-pill" onclick="wrapPlaceholder('bold', 'Bold text')">+ {{ bold }}</button>
                                    <button type="button" class="placeholder-pill" onclick="wrapPlaceholder('italic', 'Italic text')">+ {{ italic }}</button>
                                    <button type="button" class="placeholder-pill" onclick="wrapPlaceholder('underline', 'Underlined text')">+ {{ underline }}</button>
                                    <button type="button" class="placeholder-pill" onclick="wrapPlaceholder('highlight', 'Highlighted text')">+ {{ highlight }}</button>
                                </div>

                                <!-- Blocks & Quotes -->
                                <div class="palette-group">
                                    <span class="group-label">Blocks:</span>
                                    <button type="button" class="placeholder-pill" onclick="wrapPlaceholder('bullet', 'List item detail')">+ {{ bullet }}</button>
                                    <button type="button" class="placeholder-pill" onclick="wrapPlaceholder('number', 'Step 1 description')">+ {{ number }}</button>
                                    <button type="button" class="placeholder-pill" onclick="wrapPlaceholder('quote', 'A meaningful quote or client statement.')">+ {{ quote }}</button>
                                    <button type="button" class="placeholder-pill" onclick="wrapPlaceholder('code', 'console.log(&quot;Hello&quot;);')">+ {{ code }}</button>
                                    <button type="button" class="placeholder-pill" onclick="insertRawPlaceholder('{{ divider }}\n\n')">+ {{ divider }}</button>
                                </div>

                                <!-- Callouts & Interactive -->
                                <div class="palette-group">
                                    <span class="group-label">Callouts:</span>
                                    <button type="button" class="placeholder-pill" onclick="insertRawPlaceholder('{{ callout: Key Advantage | Centralized data saves 60% operational time. }}\n\n')">+ {{ callout }}</button>
                                    <button type="button" class="placeholder-pill" onclick="wrapPlaceholder('tip', 'Pro Tip: Regular notifications increase parent engagement.')">+ {{ tip }}</button>
                                    <button type="button" class="placeholder-pill" onclick="insertRawPlaceholder('{{ button: Explore Solutions | /smartech-school-app.html }}\n\n')">+ {{ button }}</button>
                                    <button type="button" class="placeholder-pill" onclick="insertRawPlaceholder('{{ link: Contact Our Team | /enquiry.html }}')">+ {{ link }}</button>
                                </div>

                                <!-- Attached Image Pills -->
                                <div class="palette-group" id="attachedImagePillsGroup">
                                    <span class="group-label">Images:</span>
                                    <button type="button" class="placeholder-pill" onclick="promptImageLink()">+ {{ image_link }}</button>
                                </div>
                            </div>
                        </div>

                        <!-- 3. Split Layout: Code/Placeholder Textarea on Left, Live Preview on Right -->
                        <div class="split-layout">
                            <div class="editor-pane">
                                <div class="pane-header">
                                    <h4>
                                        <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>
                                        Content Editor (With Placeholders)
                                    </h4>
                                    <span id="charCount" style="font-size:0.8rem; color:var(--text-soft);">0 chars</span>
                                </div>
                                <textarea id="postContent" class="content-editor-textarea" placeholder="Type your blog content here or tap placeholders from above..." oninput="updateLivePreview()" required></textarea>
                            </div>

                            <div class="preview-pane">
                                <div class="pane-header">
                                    <h4>
                                        <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/></svg>
                                        Live Rendered Preview
                                    </h4>
                                    <span style="font-size:0.8rem; color:var(--green-main); font-weight:700;">Real-time update</span>
                                </div>
                                <div class="preview-content-box" id="livePreviewContainer">
                                    <div style="color:var(--text-soft); text-align:center; padding-top:4rem;">
                                        Start typing or select a post to see the live rendered preview here.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Editor Footer Action Bar -->
                        <div class="editor-footer-bar">
                            <div style="display:flex; align-items:center; gap:0.8rem;">
                                <button type="submit" class="btn-action btn-primary" id="savePostBtn" style="padding:0.75rem 2rem; font-size:1rem;">
                                    <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M17 3H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V7l-4-4zm-5 16c-1.66 0-3-1.34-3-3s1.34-3 3-3 3 1.34 3 3-1.34 3-3 3zm3-10H5V5h10v4z"/></svg>
                                    Save Blog Post
                                </button>
                                <button type="button" class="btn-action" onclick="openNewBlog()">Clear</button>
                            </div>
                            <div id="saveStatusMessage" class="status-message"></div>
                        </div>
                    </div>
                </form>
            </section>
        </section>
    </main>

    <?php require_once 'partials/aichat.php'; ?>
    <?php require_once 'partials/footer.php'; ?>

    <script>
        // Global in-memory map of attached blog images: { "image_1": "url", "image_2": "url" }
        let attachedImages = {};

        function escapeHtml(str) {
            if (!str) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        }

        // Live Slug generator from Title
        function handleTitleInput(title) {
            const slugInput = document.getElementById('postSlug');
            if (slugInput && (!slugInput.dataset.manual || slugInput.value === '')) {
                slugInput.value = title.toLowerCase()
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/(^-|-$)/g, '');
            }
            updateLivePreview();
        }

        document.getElementById('postSlug')?.addEventListener('input', function() {
            this.dataset.manual = 'true';
        });

        // Tap to Paste: Wraps selection or inserts template
        function wrapPlaceholder(tag, sampleText) {
            const textarea = document.getElementById('postContent');
            if (!textarea) return;

            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            const text = textarea.value;
            const selected = text.substring(start, end);

            const openTag = `{{ ${tag} }}`;
            const closeTag = `{{ /${tag} }}`;

            let replacement;
            if (selected) {
                replacement = `${openTag}${selected}${closeTag}`;
            } else {
                replacement = `${openTag}${sampleText}${closeTag}`;
            }

            textarea.value = text.substring(0, start) + replacement + text.substring(end);
            textarea.focus();

            if (selected) {
                textarea.selectionStart = start;
                textarea.selectionEnd = start + replacement.length;
            } else {
                textarea.selectionStart = start + openTag.length;
                textarea.selectionEnd = start + openTag.length + sampleText.length;
            }

            updateLivePreview();
        }

        // Tap to Paste: Raw insertion
        function insertRawPlaceholder(tagString) {
            const textarea = document.getElementById('postContent');
            if (!textarea) return;

            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            const text = textarea.value;

            textarea.value = text.substring(0, start) + tagString + text.substring(end);
            textarea.focus();
            textarea.selectionStart = textarea.selectionEnd = start + tagString.length;

            updateLivePreview();
        }

        // Online Image Link Insert
        function insertOnlineImagePlaceholder() {
            const urlInput = document.getElementById('onlineImgUrl');
            const capInput = document.getElementById('onlineImgCaption');
            const url = (urlInput.value || '').trim();
            const cap = (capInput.value || '').trim();

            if (!url) {
                alert('Please enter an image URL.');
                urlInput.focus();
                return;
            }

            let tag = `{{ image_link: ${url}`;
            if (cap) tag += ` | ${cap}`;
            tag += ` }}\n\n`;

            insertRawPlaceholder(tag);
            urlInput.value = '';
            capInput.value = '';
        }

        function promptImageLink() {
            const url = prompt('Enter image URL (e.g., https://yourdomain.com/photo.jpg):');
            if (!url || !url.trim()) return;
            const cap = prompt('Optional caption (or press Enter to skip):');
            let tag = `{{ image_link: ${url.trim()}`;
            if (cap && cap.trim()) tag += ` | ${cap.trim()}`;
            tag += ` }}\n\n`;
            insertRawPlaceholder(tag);
        }

        // Multi-image upload at top of editor
        function handleMultipleImageUpload(files) {
            if (!files || files.length === 0) return;

            const dropzone = document.getElementById('imageDropzone');
            if (dropzone) dropzone.style.opacity = '0.5';

            let uploadsRemaining = files.length;

            Array.from(files).forEach(file => {
                const formData = new FormData();
                formData.append('image', file);

                fetch('/api/blog/upload-image', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.url) {
                        let imgUrl = data.url;
                        if (imgUrl && !imgUrl.startsWith('http://') && !imgUrl.startsWith('https://') && !imgUrl.startsWith('/')) {
                            imgUrl = '/' + imgUrl;
                        }
                        // Determine next sequential key: image_1, image_2, etc.
                        let nextIndex = 1;
                        while (attachedImages['image_' + nextIndex]) {
                            nextIndex++;
                        }
                        const key = 'image_' + nextIndex;
                        attachedImages[key] = imgUrl;

                        renderAttachedImages();
                        updateLivePreview();
                    } else {
                        alert(data.error || 'Failed to upload image.');
                    }
                })
                .catch(err => {
                    console.error('Upload error:', err);
                    alert('Error uploading image.');
                })
                .finally(() => {
                    uploadsRemaining--;
                    if (uploadsRemaining === 0 && dropzone) {
                        dropzone.style.opacity = '1';
                    }
                });
            });
        }

        // Set an uploaded image as the Featured Banner
        function setAsFeaturedBanner(url) {
            const input = document.getElementById('postFeaturedImg');
            if (input) {
                input.value = url;
                updateLivePreview();
            }
        }

        // Drag & Drop listeners
        const dropzone = document.getElementById('imageDropzone');
        if (dropzone) {
            ['dragenter', 'dragover'].forEach(evt => {
                dropzone.addEventListener(evt, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    dropzone.classList.add('dragover');
                });
            });
            ['dragleave', 'drop'].forEach(evt => {
                dropzone.addEventListener(evt, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    dropzone.classList.remove('dragover');
                });
            });
            dropzone.addEventListener('drop', (e) => {
                const dt = e.dataTransfer;
                if (dt && dt.files && dt.files.length) {
                    handleMultipleImageUpload(dt.files);
                }
            });
        }

        // Render uploaded image cards & dynamic palette chips
        function renderAttachedImages() {
            const grid = document.getElementById('uploadedImagesGrid');
            const pillsGroup = document.getElementById('attachedImagePillsGroup');
            if (!grid) return;

            grid.innerHTML = '';
            document.getElementById('blogImagesJson').value = JSON.stringify(attachedImages);

            // Rebuild palette pills for attached images
            if (pillsGroup) {
                pillsGroup.innerHTML = `
                    <span class="group-label">Images:</span>
                    <button type="button" class="placeholder-pill" onclick="promptImageLink()">+ {{ image_link }}</button>
                `;
            }

            Object.entries(attachedImages).forEach(([key, url]) => {
                // Card in upload section
                const card = document.createElement('div');
                card.className = 'image-card-chip';
                card.innerHTML = `
                    <div class="image-thumb-wrap">
                        <img src="${url}" alt="${key}">
                    </div>
                    <div class="image-tag-badge">{{ ${key} }}</div>
                    <div class="image-actions-row">
                        <button type="button" class="image-action-btn btn-insert-tag" onclick="insertRawPlaceholder('{{ ${key} }}\\n\\n')" title="Insert {{ ${key} }} into post">
                            Insert Tag
                        </button>
                        <button type="button" class="image-action-btn" style="background:rgba(34,139,34,0.1); color:var(--green-dark);" onclick="setAsFeaturedBanner('${url}')" title="Set as Featured Banner">
                            Banner
                        </button>
                        <button type="button" class="image-action-btn btn-del-img" onclick="removeAttachedImage('${key}')" title="Remove image">
                            &times;
                        </button>
                    </div>
                `;
                grid.appendChild(card);

                // Add to placeholder toolbar
                if (pillsGroup) {
                    const pillBtn = document.createElement('button');
                    pillBtn.type = 'button';
                    pillBtn.className = 'placeholder-pill';
                    pillBtn.style.background = 'rgba(15, 56, 28, 0.15)';
                    pillBtn.innerText = `+ {{ ${key} }}`;
                    pillBtn.onclick = () => insertRawPlaceholder(`{{ ${key} }}\n\n`);
                    pillsGroup.appendChild(pillBtn);
                }
            });
        }

        function removeAttachedImage(key) {
            delete attachedImages[key];
            renderAttachedImages();
            updateLivePreview();
        }

        // Client-side parser for Live Real-Time Interactive Preview
        function parsePlaceholdersJS(content, imagesMap) {
            if (!content) return '';
            let text = content;

            // Divider
            text = text.replace(/\{\{\s*divider\s*\}\}/gi, '<hr class="blog-divider">');

            // Title & Headings
            text = text.replace(/\{\{\s*title\s*\}\}([\s\S]*?)\{\{\s*\/title\s*\}\}/gi, '<h1 class="blog-display-title">$1</h1>');
            text = text.replace(/\{\{\s*title\s*:\s*([^}\n]+)\s*\}\}/gi, '<h1 class="blog-display-title">$1</h1>');

            for (let i = 1; i <= 6; i++) {
                const reTag = new RegExp('\\{\\{\\s*h' + i + '\\s*\\}\\}([\\s\\S]*?)\\{\\{\\s*\\/h' + i + '\\s*\\}\\}', 'gi');
                const reLine = new RegExp('\\{\\{\\s*h' + i + '\\s*:\\s*([^}\\n]+)\\s*\\}\\}', 'gi');
                text = text.replace(reTag, `<h${i} class="blog-h${i}">$1</h${i}>`);
                text = text.replace(reLine, `<h${i} class="blog-h${i}">$1</h${i}>`);
            }

            // Formatting
            text = text.replace(/\{\{\s*(?:bold|b)\s*\}\}([\s\S]*?)\{\{\s*\/(?:bold|b)\s*\}\}/gi, '<strong class="blog-strong">$1</strong>');
            text = text.replace(/\{\{\s*(?:italic|i)\s*\}\}([\s\S]*?)\{\{\s*\/(?:italic|i)\s*\}\}/gi, '<em class="blog-em">$1</em>');
            text = text.replace(/\{\{\s*(?:underline|u)\s*\}\}([\s\S]*?)\{\{\s*\/(?:underline|u)\s*\}\}/gi, '<u class="blog-underline">$1</u>');
            text = text.replace(/\{\{\s*highlight\s*\}\}([\s\S]*?)\{\{\s*\/highlight\s*\}\}/gi, '<mark class="blog-mark">$1</mark>');

            // Quotes & Code
            text = text.replace(/\{\{\s*quote\s*\}\}([\s\S]*?)\{\{\s*\/quote\s*\}\}/gi, (m, p1) => {
                return `<blockquote class="blog-quote">${p1.trim()}</blockquote>`;
            });
            text = text.replace(/\{\{\s*code\s*\}\}([\s\S]*?)\{\{\s*\/code\s*\}\}/gi, (m, p1) => {
                return `<pre class="blog-code-block"><code>${escapeHtml(p1)}</code></pre>`;
            });

            // Callouts & Tips
            text = text.replace(/\{\{\s*callout\s*:\s*([^|{}]+)(?:\|\s*([^}]+))?\}\}/gi, (m, title, body) => {
                return `<aside class="blog-callout"><div><strong>${escapeHtml(title.trim())}</strong><p style="margin:4px 0 0;">${(body || '').trim()}</p></div></aside>`;
            });
            text = text.replace(/\{\{\s*callout\s*\}\}([\s\S]*?)\{\{\s*\/callout\s*\}\}/gi, (m, p1) => {
                return `<aside class="blog-callout"><div>${p1.trim()}</div></aside>`;
            });
            text = text.replace(/\{\{\s*tip\s*\}\}([\s\S]*?)\{\{\s*\/tip\s*\}\}/gi, (m, p1) => {
                return `<div class="blog-tip-box"><div>${p1.trim()}</div></div>`;
            });

            // CTA Buttons & Links
            text = text.replace(/\{\{\s*button\s*:\s*([^|{}]+)(?:\|\s*([^}]+))?\}\}/gi, (m, label, url) => {
                return `<div style="margin:1.4rem 0; text-align:center;"><a href="${escapeHtml((url || '#').trim())}" class="blog-action-btn" target="_blank">${escapeHtml(label.trim())} &rarr;</a></div>`;
            });
            text = text.replace(/\{\{\s*link\s*:\s*([^|{}]+)(?:\|\s*([^}]+))?\}\}/gi, (m, label, url) => {
                return `<a href="${escapeHtml((url || '#').trim())}" class="blog-inline-link" target="_blank">${escapeHtml(label.trim())}</a>`;
            });

            // External Images
            text = text.replace(/\{\{\s*image_link\s*:\s*([^|{}]+)(?:\|\s*([^}]+))?\}\}/gi, (m, url, cap) => {
                const c = cap ? `<figcaption class="blog-fig-caption">${escapeHtml(cap.trim())}</figcaption>` : '';
                return `<figure class="blog-figure"><img src="${escapeHtml(url.trim())}" class="blog-responsive-img" alt="Blog Image">${c}</figure>`;
            });

            // Attached sequential images {{ image_1 }}, {{ image_2 }}...
            text = text.replace(/\{\{\s*(image_\d+)(?:\s*\|\s*([^}]+))?\s*\}\}/gi, (m, tag, cap) => {
                const k = tag.toLowerCase().trim();
                let src = imagesMap[k];
                if (!src) return `<div class="blog-img-missing"><span>[Attached placeholder ${escapeHtml(k)} pending upload]</span></div>`;
                if (!src.startsWith('http://') && !src.startsWith('https://') && !src.startsWith('/')) {
                    src = '/' + src;
                }
                const c = cap ? `<figcaption class="blog-fig-caption">${escapeHtml(cap.trim())}</figcaption>` : '';
                return `<figure class="blog-figure"><img src="${escapeHtml(src)}" class="blog-responsive-img" alt="Uploaded Visual">${c}</figure>`;
            });

            // Generic {{ image }}
            text = text.replace(/\{\{\s*image(?:\s*\|\s*([^}]+))?\s*\}\}/gi, (m, cap) => {
                let first = Object.values(imagesMap)[0];
                if (!first) return `<div class="blog-img-missing"><span>[No image attached]</span></div>`;
                if (!first.startsWith('http://') && !first.startsWith('https://') && !first.startsWith('/')) {
                    first = '/' + first;
                }
                const c = cap ? `<figcaption class="blog-fig-caption">${escapeHtml(cap.trim())}</figcaption>` : '';
                return `<figure class="blog-figure"><img src="${escapeHtml(first)}" class="blog-responsive-img" alt="Uploaded Visual">${c}</figure>`;
            });

            // Bullets
            text = text.replace(/(?:\{\{\s*(?:bullet|li)\s*\}\}([\s\S]*?)\{\{\s*\/(?:bullet|li)\s*\}\}\s*)+/gi, (match) => {
                let itemsHtml = '';
                const itemRegex = /\{\{\s*(?:bullet|li)\s*\}\}([\s\S]*?)\{\{\s*\/(?:bullet|li)\s*\}\}/gi;
                let itemMatch;
                while ((itemMatch = itemRegex.exec(match)) !== null) {
                    itemsHtml += `<li>${itemMatch[1].trim()}</li>`;
                }
                return `<ul class="blog-bullet-list">${itemsHtml}</ul>`;
            });

            // Numbers
            text = text.replace(/(?:\{\{\s*number\s*\}\}([\s\S]*?)\{\{\s*\/number\s*\}\}\s*)+/gi, (match) => {
                let itemsHtml = '';
                const itemRegex = /\{\{\s*number\s*\}\}([\s\S]*?)\{\{\s*\/number\s*\}\}/gi;
                let itemMatch;
                while ((itemMatch = itemRegex.exec(match)) !== null) {
                    itemsHtml += `<li>${itemMatch[1].trim()}</li>`;
                }
                return `<ol class="blog-ordered-list">${itemsHtml}</ol>`;
            });

            // Paragraphs
            const blocks = text.split(/\n\s*\n/);
            const result = blocks.map(b => {
                const trimmed = b.trim();
                if (!trimmed) return '';
                if (/^<(?:h[1-6]|ul|ol|blockquote|pre|figure|aside|div|hr)/i.test(trimmed)) {
                    return trimmed;
                }
                return `<p class="blog-p">${trimmed.replace(/\n/g, '<br>')}</p>`;
            }).filter(Boolean);

            return result.join('\n\n');
        }

        // Updates Live Rendered Preview
        function updateLivePreview() {
            const container = document.getElementById('livePreviewContainer');
            const textarea = document.getElementById('postContent');
            const charCount = document.getElementById('charCount');
            if (!container || !textarea) return;

            const content = textarea.value;
            if (charCount) charCount.innerText = `${content.length} chars`;

            const title = (document.getElementById('postTitle')?.value || '').trim();
            const featuredImg = (document.getElementById('postFeaturedImg')?.value || '').trim();
            const category = (document.getElementById('postCategory')?.value || 'Technology').trim();
            const author = (document.getElementById('postAuthor')?.value || 'SmarTech Team').trim();

            let headerHtml = '';
            if (title) {
                headerHtml += `
                    <div style="border-bottom: 1px solid rgba(34, 139, 34, 0.12); padding-bottom: 1rem; margin-bottom: 1.5rem;">
                        <span class="badge" style="margin-bottom:0.6rem;">${escapeHtml(category)}</span>
                        <h2 style="margin: 0.4rem 0 0.6rem; color: var(--green-dark); font-size: 1.8rem; line-height: 1.25;">${escapeHtml(title)}</h2>
                        <div style="color:var(--text-soft); font-size:0.88rem;">By ${escapeHtml(author)} &bull; ${new Date().toLocaleDateString('en-US', {month:'short', day:'numeric', year:'numeric'})}</div>
                    </div>
                `;
            }

            if (featuredImg) {
                headerHtml += `
                    <div style="margin-bottom:1.5rem; border-radius:12px; overflow:hidden; max-height:260px; background:#f4f8f4; text-align:center;">
                        <img src="${escapeHtml(featuredImg)}" style="max-width:100%; max-height:260px; width:auto; height:auto; object-fit:contain; display:inline-block;" alt="Featured banner">
                    </div>
                `;
            }

            // Strip redundant leading title from body content if top title is already provided
            let cleanContent = content;
            if (title) {
                cleanContent = cleanContent.replace(/^\s*\{\{\s*title\s*\}\}[\s\S]*?\{\{\s*\/title\s*\}\}\s*/i, '');
                cleanContent = cleanContent.replace(/^\s*\{\{\s*title\s*:[^}\n]+\}\}\s*/i, '');
            }

            const parsedBody = parsePlaceholdersJS(cleanContent, attachedImages);
            container.innerHTML = headerHtml + (parsedBody || '<p style="color:var(--text-soft); font-style:italic;">[Blog post body will appear here]</p>');
        }

        // Save Blog Post via AJAX
        function saveBlogPost(e) {
            e.preventDefault();
            const btn = document.getElementById('savePostBtn');
            const statusMsg = document.getElementById('saveStatusMessage');
            
            btn.disabled = true;
            btn.innerText = 'Saving...';
            statusMsg.className = 'status-message';
            statusMsg.innerText = '';

            const payload = {
                id: document.getElementById('blogId').value || null,
                title: document.getElementById('postTitle').value,
                slug: document.getElementById('postSlug').value,
                category: document.getElementById('postCategory').value,
                author: document.getElementById('postAuthor').value,
                status: document.getElementById('postStatus').value,
                meta_description: document.getElementById('postMetaDesc').value,
                keywords: document.getElementById('postKeywords').value,
                featured_image: document.getElementById('postFeaturedImg').value,
                content: document.getElementById('postContent').value,
                images_json: document.getElementById('blogImagesJson').value
            };

            fetch('/api/blog/save', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    statusMsg.className = 'status-message status-success';
                    statusMsg.innerText = 'Blog post saved successfully!';
                    setTimeout(() => {
                        window.location.href = 'admin-blogs.php';
                    }, 800);
                } else {
                    statusMsg.className = 'status-message status-error';
                    statusMsg.innerText = data.error || 'Failed to save blog post.';
                }
            })
            .catch(err => {
                console.error('Save error:', err);
                statusMsg.className = 'status-message status-error';
                statusMsg.innerText = 'Network error saving blog post.';
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerText = 'Save Blog Post';
            });
        }

        // Edit existing blog post
        function editBlog(id) {
            fetch('/api/blog/get?id=' + id)
            .then(res => res.json())
            .then(data => {
                if (data.success && data.blog) {
                    const b = data.blog;
                    document.getElementById('blogId').value = b.id;
                    document.getElementById('postTitle').value = b.title || '';
                    document.getElementById('postSlug').value = b.slug || '';
                    document.getElementById('postCategory').value = b.category || 'Technology';
                    document.getElementById('postAuthor').value = b.author || 'SmarTech Team';
                    document.getElementById('postStatus').value = b.status || 'published';
                    document.getElementById('postMetaDesc').value = b.meta_description || '';
                    document.getElementById('postKeywords').value = b.keywords || '';
                    document.getElementById('postFeaturedImg').value = b.featured_image || '';
                    document.getElementById('postContent').value = b.content || '';

                    try {
                        attachedImages = typeof b.images_json === 'string' ? JSON.parse(b.images_json) : (b.images_json || {});
                    } catch(e) {
                        attachedImages = {};
                    }
                    renderAttachedImages();

                    document.getElementById('editorHeading').innerHTML = `
                        <svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
                        Editing Post #${b.id}: ${escapeHtml(b.title)}
                    `;

                    updateLivePreview();
                    document.getElementById('blog-editor').scrollIntoView({ behavior: 'smooth' });
                } else {
                    alert('Could not fetch blog post details.');
                }
            })
            .catch(err => {
                console.error(err);
                alert('Error loading blog post.');
            });
        }

        // Reset to Blank Post
        function openNewBlog() {
            document.getElementById('blogPostForm').reset();
            document.getElementById('blogId').value = '';
            document.getElementById('postKeywords').value = '';
            document.getElementById('postSlug').dataset.manual = '';
            attachedImages = {};
            renderAttachedImages();
            document.getElementById('editorHeading').innerHTML = `
                <svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
                Create New Blog Post
            `;
            document.getElementById('postAuthor').value = 'SmarTech Team';
            document.getElementById('saveStatusMessage').innerText = '';
            updateLivePreview();
            document.getElementById('blog-editor').scrollIntoView({ behavior: 'smooth' });
        }

        // Initial preview render on page load
        window.addEventListener('DOMContentLoaded', () => {
            renderAttachedImages();
            updateLivePreview();

            // Support query param ?edit=123
            const urlParams = new URLSearchParams(window.location.search);
            const editId = urlParams.get('edit');
            if (editId) {
                editBlog(editId);
            }
        });
    </script>
</body>

</html>

