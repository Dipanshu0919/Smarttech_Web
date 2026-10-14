<?php
require_once 'config.php';
require_once 'index.php';
require_once 'blog-engine.php';
require_once 'case-study-engine.php';

$is_admin = dashboard_session_is_valid();
$orders = fetch_rows("orders");
$enquiries = fetch_rows("enquiries");
$blogs = get_all_blogs();
$case_studies = get_all_case_studies();
$order_count = count($orders);
$enquiry_count = count($enquiries);
$blog_count = count($blogs);
$case_study_count = count($case_studies);
$latest_order = $orders[0] ?? null;
$latest_enquiry = $enquiries[0] ?? null;
$login_error = $_SESSION['login_error'] ?? null;
unset($_SESSION['login_error']);

$dashboard_link_visible = $is_admin;
$admin_management_link_visible = isset($_SESSION['superadmin_access']) && $_SESSION['superadmin_access'] === true;
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
    <title>Dashboard | SmarTech Solutions</title>
    <meta name="description" content="Admin dashboard for orders, enquiries, and management portals.">
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

        .auth-shell {
            max-width: 720px;
            margin: 0 auto;
            padding-top: 4.5rem;
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
            font-size: clamp(1.8rem, 3vw, 2.8rem);
            line-height: 1.05;
            letter-spacing: -0.04em;
            color: var(--green-dark);
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

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(6, minmax(0, 1fr));
            gap: 1rem;
        }

        .stat-card,
        .section-card {
            background: var(--surface);
            backdrop-filter: blur(12px);
            border: 1px solid var(--border);
            border-radius: 24px;
            box-shadow: var(--shadow);
        }

        .stat-card {
            padding: 1.1rem 1.2rem;
            display: grid;
            gap: 0.35rem;
            transition: transform 0.2s ease, border-color 0.2s ease;
        }

        .stat-card.clickable:hover {
            transform: translateY(-2px);
            border-color: var(--green-main);
        }

        .stat-label {
            color: var(--text-soft);
            font-size: 0.88rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .stat-value {
            font-size: clamp(1.4rem, 2.2vw, 2rem);
            font-weight: 900;
            color: var(--green-dark);
            letter-spacing: -0.04em;
        }

        .stat-meta {
            color: var(--text-soft);
            line-height: 1.4;
            font-size: 0.82rem;
        }

        /* --- Content Management Modules Grid --- */
        .management-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(460px, 1fr));
            gap: 1.5rem;
        }

        .module-card {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 1.8rem;
            border-left: 4px solid var(--green-main);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .module-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 24px 56px rgba(15, 56, 28, 0.12);
        }

        .module-card-header {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .module-icon-box {
            width: 56px;
            height: 56px;
            border-radius: 18px;
            background: linear-gradient(135deg, rgba(34, 139, 34, 0.12), rgba(15, 56, 28, 0.18));
            color: var(--green-dark);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .module-actions {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            flex-wrap: wrap;
            padding-top: 1.2rem;
            border-top: 1px solid rgba(34, 139, 34, 0.1);
        }

        .section-card {
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
            font-size: 1.4rem;
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

        .message-cell {
            max-width: 320px;
            white-space: pre-wrap;
            word-break: break-word;
        }

        .filename {
            color: var(--green-dark);
            font-weight: 700;
            text-decoration: underline;
        }

        .muted {
            color: var(--text-soft);
            font-style: italic;
        }

        .delete-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 0;
            border-radius: 999px;
            padding: 0.6rem 0.95rem;
            background: #fee4e2;
            color: #b42318;
            font: inherit;
            font-weight: 800;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .delete-btn:hover {
            background: #b42318;
            color: var(--white);
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

        .login-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 28px;
            box-shadow: var(--shadow);
            padding: 2rem;
        }

        .login-card h1 {
            margin: 0 0 0.5rem;
            color: var(--green-dark);
        }

        .login-card p {
            margin: 0 0 1.2rem;
            color: var(--text-soft);
        }

        .login-form {
            display: flex;
            flex-direction: column;
            gap: 1rem;
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

        .form-input {
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

        .form-input:focus {
            border-color: var(--green-main);
            box-shadow: 0 0 0 3px rgba(34, 139, 34, 0.15);
        }

        .login-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-top: 0.5rem;
        }

        .login-button, .logout-button {
            background: var(--green-main);
            color: #ffffff;
            border: 0;
            padding: 0.75rem 1.4rem;
            border-radius: 999px;
            font-weight: 800;
            cursor: pointer;
            font-family: inherit;
            transition: all 0.2s ease;
        }

        .logout-button {
            background: rgba(180, 35, 24, 0.1);
            color: #b42318;
            border: 1px solid rgba(180, 35, 24, 0.2);
            padding: 0.65rem 1.1rem;
        }

        .logout-button:hover {
            background: #b42318;
            color: #ffffff;
        }

        .status-message {
            font-size: 0.92rem;
            font-weight: 700;
        }
        .status-error { color: #b42318; }

        @media (max-width: 1100px) {
            .stats-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        @media (max-width: 900px) {
            .management-grid {
                grid-template-columns: 1fr;
            }
            .stats-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 600px) {
            main {
                padding: 1rem;
            }
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <?php require_once 'partials/header.php'; ?>

    <main>
        <?php if ($is_admin): ?>
            <section class="dashboard-shell">
                <!-- Topbar Navigation -->
                <div class="topbar">
                    <h1 class="title">Admin Dashboard</h1>
                    <div class="nav-pills">
                        <a href="admin-blogs.php">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor" aria-hidden="true"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>
                            Manage Blogs (<?= $blog_count ?>)
                        </a>
                        <a href="admin-case-studies.php">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor" aria-hidden="true"><path d="M19 5h-2V3H7v2H5c-1.1 0-2 .9-2 2v1c0 2.55 1.92 4.63 4.39 4.94A5.01 5.01 0 0 0 11 15.9V19H7v2h10v-2h-4v-3.1a5.01 5.01 0 0 0 3.61-4.96C19.08 10.63 21 8.55 21 6V5c0-1.1-.9-2-2-2zM5 8V7h2v3.82C5.84 10.4 5 9.3 5 8zm14 0c0 1.3-.84 2.4-2 2.82V7h2v1z"/></svg>
                            Manage Case Studies (<?= $case_study_count ?>)
                        </a>
                        <a href="#orders">Orders (<?= $order_count ?>)</a>
                        <a href="#enquiries">Enquiries (<?= $enquiry_count ?>)</a>
                        <form class="logout-form" method="post" action="dashboard.php?action=logout" style="margin:0;">
                            <button class="logout-button" type="submit">Sign Out</button>
                        </form>
                    </div>
                </div>

                <!-- Stats Grid -->
                <div class="stats-grid">
                    <a href="admin-blogs.php" style="text-decoration:none; color:inherit;">
                        <article class="stat-card clickable">
                            <div class="stat-label">
                                Blog Posts
                                <svg viewBox="0 0 24 24" width="14" height="14" fill="var(--green-main)"><path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6-1.41-1.41z"/></svg>
                            </div>
                            <div class="stat-value"><?= $blog_count ?></div>
                            <div class="stat-meta">Published articles &amp; drafts.</div>
                        </article>
                    </a>

                    <a href="admin-case-studies.php" style="text-decoration:none; color:inherit;">
                        <article class="stat-card clickable">
                            <div class="stat-label">
                                Case Studies
                                <svg viewBox="0 0 24 24" width="14" height="14" fill="var(--green-main)"><path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6-1.41-1.41z"/></svg>
                            </div>
                            <div class="stat-value"><?= $case_study_count ?></div>
                            <div class="stat-meta">Client success stories.</div>
                        </article>
                    </a>

                    <article class="stat-card">
                        <div class="stat-label">Total Orders</div>
                        <div class="stat-value"><?= $order_count ?></div>
                        <div class="stat-meta">Stored order submissions.</div>
                    </article>

                    <article class="stat-card">
                        <div class="stat-label">Total Enquiries</div>
                        <div class="stat-value"><?= $enquiry_count ?></div>
                        <div class="stat-meta">Stored enquiry submissions.</div>
                    </article>

                    <article class="stat-card">
                        <div class="stat-label">Latest Order</div>
                        <div class="stat-value">#<?= $latest_order ? $latest_order['id'] : '-' ?></div>
                        <div class="stat-meta"><?= $latest_order ? date('M d, H:i', strtotime($latest_order['timestamp'])) : 'No orders yet.' ?></div>
                    </article>

                    <article class="stat-card">
                        <div class="stat-label">Latest Enquiry</div>
                        <div class="stat-value">#<?= $latest_enquiry ? $latest_enquiry['id'] : '-' ?></div>
                        <div class="stat-meta"><?= $latest_enquiry ? date('M d, H:i', strtotime($latest_enquiry['timestamp'])) : 'No enquiries yet.' ?></div>
                    </article>
                </div>

                <!-- Dedicated Content Hub Modules (Redirect Cards) -->
                <div class="management-grid">
                    <!-- Blog Posts Portal Card -->
                    <section class="section-card module-card">
                        <div>
                            <div class="module-card-header">
                                <div class="module-icon-box">
                                    <svg viewBox="0 0 24 24" width="28" height="28" fill="currentColor"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>
                                </div>
                                <div>
                                    <h2 style="margin:0; font-size:1.35rem; color:var(--green-dark);">Blog Posts Management</h2>
                                    <span class="badge" style="margin-top:0.35rem;"><?= $blog_count ?> total posts</span>
                                </div>
                            </div>
                            <p style="color:var(--text-soft); font-size:0.92rem; line-height:1.6; margin:1rem 0 1.4rem;">
                                Dedicated management page to compose, edit, and publish SEO blog posts with real-time live typing preview, top multi-image upload dropzone, and custom formatting tag palette.
                            </p>
                        </div>
                        <div class="module-actions">
                            <a href="admin-blogs.php" class="btn-action btn-primary" style="padding:0.75rem 1.4rem;">
                                Open Blog Manager &rarr;
                            </a>
                            <a href="admin-blogs.php#blog-editor" class="btn-action">
                                + Create New Blog
                            </a>
                            <a href="/blog.html" target="_blank" class="btn-action" style="margin-left:auto;">
                                View Live Blog &nearr;
                            </a>
                        </div>
                    </section>

                    <!-- Case Studies Portal Card -->
                    <section class="section-card module-card">
                        <div>
                            <div class="module-card-header">
                                <div class="module-icon-box">
                                    <svg viewBox="0 0 24 24" width="28" height="28" fill="currentColor"><path d="M19 5h-2V3H7v2H5c-1.1 0-2 .9-2 2v1c0 2.55 1.92 4.63 4.39 4.94A5.01 5.01 0 0 0 11 15.9V19H7v2h10v-2h-4v-3.1a5.01 5.01 0 0 0 3.61-4.96C19.08 10.63 21 8.55 21 6V5c0-1.1-.9-2-2-2zM5 8V7h2v3.82C5.84 10.4 5 9.3 5 8zm14 0c0 1.3-.84 2.4-2 2.82V7h2v1z"/></svg>
                                </div>
                                <div>
                                    <h2 style="margin:0; font-size:1.35rem; color:var(--green-dark);">Case Studies Management</h2>
                                    <span class="badge" style="margin-top:0.35rem;"><?= $case_study_count ?> total case studies</span>
                                </div>
                            </div>
                            <p style="color:var(--text-soft); font-size:0.92rem; line-height:1.6; margin:1rem 0 1.4rem;">
                                Dedicated management page for client success stories with real-time live preview, key ROI impact metrics, challenge/solution architecture blocks, and client organization metadata.
                            </p>
                        </div>
                        <div class="module-actions">
                            <a href="admin-case-studies.php" class="btn-action btn-primary" style="padding:0.75rem 1.4rem;">
                                Open Case Studies Manager &rarr;
                            </a>
                            <a href="admin-case-studies.php#case-study-editor" class="btn-action">
                                + Create New Case Study
                            </a>
                            <a href="/case-study.html" target="_blank" class="btn-action" style="margin-left:auto;">
                                View Live Case Studies &nearr;
                            </a>
                        </div>
                    </section>
                </div>

                <!-- Stored Orders Section -->
                <section class="section-card" id="orders">
                    <div class="section-head">
                        <h2>
                            <svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor"><path d="M19 3h-4.18C14.4 1.84 13.3 1 12 1c-1.3 0-2.4.84-2.82 2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 0c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm2 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>
                            Order Form Submissions
                        </h2>
                        <span class="badge"><?= $order_count ?> total</span>
                    </div>

                    <?php if (!empty($orders)): ?>
                        <div class="table-wrap">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Representative</th>
                                        <th>Order For</th>
                                        <th>Phone</th>
                                        <th>Email</th>
                                        <th>Date</th>
                                        <th>Remark</th>
                                        <th>File</th>
                                        <th>Received At</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($order['id']) ?></td>
                                            <td><?= htmlspecialchars($order['representative_name']) ?></td>
                                            <td><?= htmlspecialchars($order['order_for']) ?></td>
                                            <td><?= htmlspecialchars($order['phone']) ?></td>
                                            <td><?= htmlspecialchars($order['email']) ?></td>
                                            <td><?= htmlspecialchars($order['date']) ?></td>
                                            <td class="message-cell"><?= htmlspecialchars($order['remark']) ?></td>
                                            <td>
                                                <?php if ($order['filename']): 
                                                    $filename = basename($order['filename']);
                                                ?>
                                                    <a class="filename" href="dashboard.php?action=order_file&id=<?= $order['id'] ?>"><?= $filename ?></a>
                                                <?php else: ?>
                                                    <span class="muted">No file</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars($order['timestamp']) ?></td>
                                            <td>
                                                <form method="post" action="dashboard.php?action=delete_order&id=<?= $order['id'] ?>" onsubmit="return confirm('Delete this order submission?');">
                                                    <button class="delete-btn" type="submit">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <strong>No orders yet</strong>
                            No order submissions have been stored.
                        </div>
                    <?php endif; ?>
                </section>

                <!-- Stored Enquiries Section -->
                <section class="section-card" id="enquiries">
                    <div class="section-head">
                        <h2>
                            <svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor"><path d="M20 2H4c-1.1 0-1.99.9-1.99 2L2 22l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zM6 9h12v2H6V9zm8 5H6v-2h8v2zm4-6H6V6h12v2z"/></svg>
                            Enquiry Submissions
                        </h2>
                        <span class="badge"><?= $enquiry_count ?> total</span>
                    </div>

                    <?php if (!empty($enquiries)): ?>
                        <div class="table-wrap">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Mobile</th>
                                        <th>Message</th>
                                        <th>Received At</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($enquiries as $enquiry): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($enquiry['id']) ?></td>
                                            <td><?= htmlspecialchars($enquiry['name']) ?></td>
                                            <td><?= htmlspecialchars($enquiry['email']) ?></td>
                                            <td><?= htmlspecialchars($enquiry['phone']) ?></td>
                                            <td class="message-cell"><?= htmlspecialchars($enquiry['message']) ?></td>
                                            <td><?= htmlspecialchars($enquiry['timestamp']) ?></td>
                                            <td>
                                                <form method="post" action="dashboard.php?action=delete_enquiry&id=<?= $enquiry['id'] ?>" onsubmit="return confirm('Delete this enquiry submission?');">
                                                    <button class="delete-btn" type="submit">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <strong>No enquiries yet</strong>
                            No enquiry submissions have been stored.
                        </div>
                    <?php endif; ?>
                </section>
            </section>
        <?php else: ?>
            <section class="auth-shell">
                <div class="login-card">
                    <h1>Admin Access</h1>
                    <p>Enter your admin credentials to access the management portal.</p>

                    <form class="login-form" method="post" action="dashboard.php">
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input id="username" class="form-input" name="username" type="text" autocomplete="username" required>
                        </div>

                        <div class="form-group">
                            <label for="password">Password</label>
                            <input id="password" class="form-input" name="password" type="password" autocomplete="current-password" required>
                        </div>

                        <?php if ($login_error): ?>
                            <p class="status-message status-error"><?= htmlspecialchars($login_error) ?></p>
                        <?php endif; ?>

                        <div class="login-actions">
                            <button class="login-button" type="submit">Sign In</button>
                            <span style="color:var(--text-soft); font-size:0.88rem;">Protected SmarTech Admin Portal</span>
                        </div>
                    </form>
                </div>
            </section>
        <?php endif; ?>
    </main>

    <?php require_once 'partials/aichat.php'; ?>
    <?php require_once 'partials/footer.php'; ?>
</body>

</html>