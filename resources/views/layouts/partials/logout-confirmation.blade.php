<form id="logoutConfirmForm" method="POST" action="{{ route('logout') }}" class="logout-confirm-form">
    @csrf
</form>

<div class="logout-confirm-modal" id="logoutConfirmModal" data-logout-modal hidden>
    <div class="logout-confirm-backdrop" data-logout-close></div>
    <section class="logout-confirm-dialog" role="dialog" aria-modal="true" aria-labelledby="logoutConfirmTitle" aria-describedby="logoutConfirmText">
        <div class="logout-confirm-icon" aria-hidden="true">
            <i class="ti-power-off"></i>
        </div>
        <h5 id="logoutConfirmTitle" class="logout-confirm-title">Confirm logout</h5>
        <p id="logoutConfirmText" class="logout-confirm-text">Do you want to log out of your account?</p>
        <div class="logout-confirm-actions">
            <button type="button" class="logout-confirm-no" data-logout-close>Cancel</button>
            <button type="button" class="logout-confirm-yes" data-logout-submit>Logout</button>
        </div>
    </section>
</div>

<style>
    .logout-confirm-form {
        display: none;
    }

    .logout-confirm-modal[hidden] {
        display: none !important;
    }

    .logout-confirm-modal {
        position: fixed;
        inset: 0;
        z-index: 100000;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }

    .logout-confirm-backdrop {
        position: absolute;
        inset: 0;
        background: rgba(15, 23, 42, 0.58);
    }

    .logout-confirm-dialog {
        position: relative;
        width: min(100%, 390px);
        border: 1px solid rgba(226, 232, 240, 0.9);
        border-radius: 8px;
        background: #fff;
        padding: 1.5rem;
        box-shadow: 0 24px 60px rgba(15, 23, 42, 0.28);
        text-align: center;
    }

    .logout-confirm-icon {
        display: inline-flex;
        width: 44px;
        height: 44px;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: #eef2ff;
        color: #4f46e5;
        font-size: 1.25rem;
        margin-bottom: 0.875rem;
    }

    .logout-confirm-title {
        margin: 0 0 0.5rem;
        font-size: 1.125rem;
        font-weight: 700;
        color: #1f2937;
    }

    .logout-confirm-text {
        margin: 0;
        color: #64748b;
        line-height: 1.5;
    }

    .logout-confirm-actions {
        display: flex;
        justify-content: center;
        gap: 0.625rem;
        margin-top: 1.35rem;
    }

    .logout-confirm-actions button {
        min-width: 96px;
        border-radius: 6px;
        padding: 0.6rem 1rem;
        font-weight: 700;
        line-height: 1.2;
        cursor: pointer;
    }

    .logout-confirm-no {
        border: 1px solid #cbd5e1;
        background: #fff;
        color: #334155;
    }

    .logout-confirm-no:hover,
    .logout-confirm-no:focus {
        background: #f8fafc;
    }

    .logout-confirm-yes {
        border: 1px solid #4f46e5;
        background: #4f46e5;
        color: #fff;
    }

    .logout-confirm-yes:hover,
    .logout-confirm-yes:focus {
        background: #4338ca;
    }
</style>

<script>
    (function () {
        let lastFocusedElement = null;

        function modal() {
            return document.getElementById('logoutConfirmModal');
        }

        window.openLogoutModal = function (event) {
            if (event) {
                event.preventDefault();
                event.stopPropagation();
            }

            const logoutModal = modal();
            if (!logoutModal) {
                return false;
            }

            lastFocusedElement = document.activeElement;
            logoutModal.hidden = false;

            const submitButton = logoutModal.querySelector('[data-logout-submit]');
            if (submitButton) {
                submitButton.focus();
            }

            return false;
        };

        window.closeLogoutModal = function () {
            const logoutModal = modal();
            if (logoutModal) {
                logoutModal.hidden = true;
            }

            if (lastFocusedElement && typeof lastFocusedElement.focus === 'function') {
                lastFocusedElement.focus();
            }
        };

        window.submitLogoutModal = function () {
            const form = document.getElementById('logoutConfirmForm');
            const submitButton = modal()?.querySelector('[data-logout-submit]');

            if (submitButton) {
                submitButton.disabled = true;
                submitButton.textContent = 'Logging out...';
            }

            if (form) {
                form.submit();
            }
        };

        document.addEventListener('click', function (event) {
            const openTrigger = event.target.closest('[data-logout-open]');
            if (openTrigger) {
                openLogoutModal(event);
                return;
            }

            if (event.target.closest('[data-logout-close]')) {
                event.preventDefault();
                closeLogoutModal();
                return;
            }

            if (event.target.closest('[data-logout-submit]')) {
                event.preventDefault();
                submitLogoutModal();
            }
        }, true);

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && modal() && !modal().hidden) {
                closeLogoutModal();
            }
        });
    })();
</script>