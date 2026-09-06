/**
 * SweetAlert confirmation dialogs, restoring the legacy look.
 *
 * The legacy app used the `swal()` global (SweetAlert 2.x, loaded from
 * assets/vendors/sweetalert/) for confirmations and submit feedback. Mark a
 * form with `data-confirm="message"` — optionally `data-confirm-title` and
 * `data-confirm-action` for the button label — and it is intercepted here.
 *
 * Falls back to the native confirm() if the vendor script is unavailable, so a
 * destructive action is never silently allowed through unconfirmed.
 */
export function initConfirmDialogs() {
    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (e) => {
            if (form.dataset.confirmed === 'yes') return;

            e.preventDefault();

            const message = form.dataset.confirm;
            const title = form.dataset.confirmTitle || 'Are you sure?';
            const action = form.dataset.confirmAction || 'Confirm';
            const danger = form.dataset.confirmDanger !== 'false';

            const proceed = () => {
                form.dataset.confirmed = 'yes';
                form.submit();
            };

            if (typeof window.swal !== 'function') {
                if (window.confirm(message)) proceed();
                return;
            }

            window.swal({
                title,
                text: message,
                icon: danger ? 'warning' : 'info',
                buttons: {
                    cancel: { text: 'Cancel', value: null, visible: true, className: 'btn btn-light' },
                    confirm: {
                        text: action,
                        value: true,
                        visible: true,
                        className: danger ? 'btn btn-danger' : 'btn btn-primary',
                    },
                },
                dangerMode: danger,
            }).then((ok) => {
                if (ok) proceed();
            });
        });
    });
}
