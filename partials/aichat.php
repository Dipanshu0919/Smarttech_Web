<style>
    .aichat-widget {
        position: fixed;
        right: 18px;
        bottom: 18px;
        z-index: 10050;
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 12px;
    }

    .floating-contact-desktop {
        display: none;
        flex-direction: column;
        align-items: center;
        gap: 12px;
    }

    @media (min-width: 993px) {
        .floating-contact-desktop {
            display: flex;
            position: fixed;
            top: 50%;
            right: 18px;
            transform: translateY(-50%);
            z-index: 1204;
        }
    }

    .floating-social-fab {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 2px solid rgba(255, 255, 255, 0.92);
        transition: transform 0.2s ease, filter 0.2s ease, box-shadow 0.2s ease;
        text-decoration: none;
        box-shadow: 0 10px 22px rgba(15, 56, 28, 0.16);
    }

    .floating-social-fab:hover,
    .floating-social-fab:focus-visible {
        transform: translateY(-2px);
        filter: saturate(1.05);
    }

    .floating-social-fab--facebook {
        background: linear-gradient(135deg, #1877f2, #0c63d4);
        color: #ffffff;
    }

    .floating-social-fab--facebook svg {
        width: 24px;
        height: 24px;
        fill: currentColor;
    }

    .floating-social-fab--whatsapp {
        background: linear-gradient(135deg, #25d366, #1ebe5d);
        color: #ffffff;
    }

    .floating-social-fab--whatsapp svg {
        width: 24px;
        height: 24px;
        fill: currentColor;
        transform: translateX(0.5px);
    }

    .aichat-stack {
        position: relative;
        display: inline-flex;
        flex-direction: column;
        align-items: flex-end;
    }

    .aichat-toggle {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .aichat-launcher {
        display: inline-flex;
        align-items: center;
        gap: 0.6rem;
        border: 0;
        border-radius: 999px;
        padding: 0.85rem 1.15rem;
        background: linear-gradient(135deg, #0f381c, #228b22);
        color: #ffffff;
        font-weight: 800;
        letter-spacing: 0.02em;
        cursor: pointer;
        box-shadow: 0 14px 28px rgba(15, 56, 28, 0.24);
        user-select: none;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .aichat-launcher:hover {
        transform: translateY(-2px);
        box-shadow: 0 18px 30px rgba(15, 56, 28, 0.3);
    }

    .aichat-launcher::before {
        content: "";
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #4cbb17;
        box-shadow: 0 0 0 6px rgba(76, 187, 23, 0.15);
    }

    .aichat-panel {
        position: absolute;
        right: 0;
        bottom: calc(100% + 14px);
        width: min(420px, calc(100vw - 36px));
        height: min(650px, calc(100vh - 110px), calc(100dvh - 110px));
        background: #ffffff;
        border: 1px solid rgba(34, 139, 34, 0.16);
        border-radius: 22px;
        box-shadow: 0 24px 56px rgba(15, 56, 28, 0.24);
        overflow: hidden;
        transform: translateY(14px) scale(0.96);
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.22s cubic-bezier(0.16, 1, 0.3, 1), transform 0.22s cubic-bezier(0.16, 1, 0.3, 1);
        display: flex;
        flex-direction: column;
        z-index: 10060;
    }

    .aichat-toggle:checked ~ .aichat-panel {
        transform: translateY(0) scale(1);
        opacity: 1;
        pointer-events: auto;
    }

    .aichat-panel-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.6rem;
        padding: 0.85rem 1rem;
        background: linear-gradient(135deg, #0f381c, #228b22);
        color: #ffffff;
        flex-shrink: 0;
    }

    .aichat-panel-title {
        display: grid;
        gap: 0.15rem;
        min-width: 0;
        flex: 1;
    }

    .aichat-panel-title strong {
        font-size: 0.98rem;
        font-weight: 700;
        letter-spacing: 0.01em;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .aichat-panel-title span {
        font-size: 0.76rem;
        opacity: 0.9;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .aichat-header-actions {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        flex-shrink: 0;
    }

    .aichat-fullpage-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.32rem;
        padding: 0.35rem 0.68rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.18);
        color: #ffffff;
        text-decoration: none;
        font-size: 0.78rem;
        font-weight: 700;
        border: 1px solid rgba(255, 255, 255, 0.32);
        transition: background-color 0.2s ease, transform 0.15s ease, border-color 0.2s ease;
        white-space: nowrap;
        cursor: pointer;
        user-select: none;
    }

    .aichat-fullpage-btn svg {
        width: 12px;
        height: 12px;
        fill: currentColor;
        flex-shrink: 0;
    }

    .aichat-fullpage-btn:hover {
        background: rgba(255, 255, 255, 0.28);
        border-color: rgba(255, 255, 255, 0.55);
        transform: translateY(-1px);
        color: #ffffff;
    }

    .aichat-close {
        cursor: pointer;
        width: 30px;
        height: 30px;
        flex: 0 0 30px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.16);
        color: #ffffff;
        font-size: 1.35rem;
        line-height: 1;
        user-select: none;
        transition: background-color 0.2s ease, transform 0.15s ease;
    }

    .aichat-close:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: scale(1.06);
    }

    .aichat-panel iframe {
        width: 100%;
        height: 100%;
        border: 0;
        background: #ffffff;
        flex: 1;
        display: block;
    }

    body.aichat-open .scroll-fab-group {
        opacity: 0 !important;
        visibility: hidden !important;
        pointer-events: none !important;
        transform: scale(0.85);
    }

    @media (max-width: 640px) {
        .aichat-widget {
            right: 12px;
            bottom: 12px;
            left: auto;
        }

        .floating-contact-desktop {
            display: none;
        }

        .aichat-launcher {
            padding: 0.72rem 1rem;
            font-size: 0.92rem;
            box-shadow: 0 10px 22px rgba(15, 56, 28, 0.22);
        }

        .aichat-panel {
            position: fixed;
            left: 12px;
            right: 12px;
            bottom: 74px;
            width: auto;
            max-width: 440px;
            margin: 0 0 0 auto;
            height: min(650px, calc(100vh - 90px), calc(100dvh - 90px));
            border-radius: 18px;
        }

        .aichat-panel-header {
            padding: 0.72rem 0.85rem;
            gap: 0.5rem;
        }

        .aichat-panel-title strong {
            font-size: 0.92rem;
        }

        .aichat-panel-title span {
            font-size: 0.72rem;
        }

        .aichat-fullpage-btn {
            padding: 0.28rem 0.58rem;
            font-size: 0.73rem;
            gap: 0.25rem;
        }

        .aichat-close {
            width: 28px;
            height: 28px;
            flex-basis: 28px;
            font-size: 1.25rem;
        }
    }

    @media (max-width: 460px) {
        .aichat-panel {
            left: 8px;
            right: 8px;
            bottom: 68px;
            max-width: 100%;
            height: calc(100dvh - 80px);
            max-height: calc(100vh - 80px);
        }
    }
</style>

<div class="aichat-widget">
    <div class="floating-contact-desktop">
        <a class="floating-social-fab floating-social-fab--facebook"
            href="https://www.facebook.com/profile.php?id=61571731650827" target="_blank"
            rel="noopener noreferrer" aria-label="Visit our Facebook page">
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path
                    d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
            </svg>
        </a>
        <a class="floating-social-fab floating-social-fab--whatsapp" href="https://wa.me/919221204466"
            target="_blank" rel="noopener noreferrer" aria-label="Chat on WhatsApp">
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path
                    d="M20.52 3.48A11.84 11.84 0 0 0 12.09 0C5.55 0 .23 5.32.23 11.86c0 2.09.55 4.14 1.59 5.95L0 24l6.38-1.67a11.78 11.78 0 0 0 5.7 1.45h.01c6.54 0 11.86-5.32 11.86-11.86 0-3.17-1.24-6.14-3.43-8.44zM12.1 21.78h-.01a9.83 9.83 0 0 1-5.01-1.37l-.36-.21-3.78.99 1.01-3.68-.23-.38a9.85 9.85 0 0 1-1.52-5.27c0-5.44 4.43-9.87 9.88-9.87 2.64 0 5.13 1.03 6.98 2.9a9.8 9.8 0 0 1 2.88 6.98c0 5.45-4.43 9.88-9.87 9.88zm5.41-7.4c-.3-.15-1.78-.88-2.06-.98-.27-.1-.47-.15-.67.15-.2.3-.77.98-.94 1.18-.17.2-.35.23-.65.08-.3-.15-1.26-.46-2.39-1.46-.89-.79-1.49-1.76-1.66-2.06-.17-.3-.02-.46.13-.61.14-.14.3-.35.45-.52.15-.18.2-.3.3-.5.1-.2.05-.38-.02-.53-.08-.15-.67-1.62-.92-2.22-.24-.58-.48-.5-.67-.51l-.57-.01c-.2 0-.52.08-.79.38-.27.3-1.04 1.01-1.04 2.45s1.07 2.84 1.22 3.04c.15.2 2.1 3.2 5.08 4.49.71.31 1.27.49 1.71.63.72.23 1.37.2 1.89.12.58-.09 1.78-.73 2.03-1.44.25-.71.25-1.32.18-1.44-.08-.12-.28-.2-.58-.35z" />
            </svg>
        </a>
    </div>

    <div class="aichat-stack">
        <input class="aichat-toggle" type="checkbox" id="aichat-toggle">
        <label class="aichat-launcher" for="aichat-toggle">AI Chat</label>

        <div class="aichat-panel" role="dialog" aria-label="AI chat widget">
            <div class="aichat-panel-header">
                <div class="aichat-panel-title">
                    <strong>SmarTech AI Chat</strong>
                    <span>Ask about services, projects, and company details</span>
                </div>
                <div class="aichat-header-actions">
                    <a class="aichat-fullpage-btn" href="aichat.html" target="_top" title="Open Full Page Chat" aria-label="Open Full Page Chat">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M19 19H5V5h7V3H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2v-7h-2v7zM14 3v2h3.59l-9.83 9.83 1.41 1.41L19 6.41V10h2V3h-7z"/>
                        </svg>
                        <span>Full Page</span>
                    </a>
                    <label class="aichat-close" for="aichat-toggle" aria-label="Close AI chat">&times;</label>
                </div>
            </div>
            <iframe src="aichat.html?embed=1" title="AI Chat"></iframe>
        </div>
    </div>
</div>

<script>
    (function() {
        var toggle = document.getElementById('aichat-toggle');

        function syncAichatState() {
            if (toggle && toggle.checked) {
                document.body.classList.add('aichat-open');
            } else {
                document.body.classList.remove('aichat-open');
            }
        }

        if (toggle) {
            toggle.addEventListener('change', syncAichatState);
            syncAichatState();
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                if (toggle && toggle.checked) {
                    toggle.checked = false;
                    syncAichatState();
                }
            }
        });
    })();
</script>