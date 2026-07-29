@once
    @push('styles')
        <style>
            .delete-confirm-dialog {
                max-width: 430px;
            }

            .delete-confirm-content {
                overflow: hidden;
                border: 0;
                border-radius: 16px;
                box-shadow: 0 24px 60px rgba(15, 23, 42, 0.2);
            }

            .delete-confirm-body {
                padding: 1.75rem 1.75rem 1.25rem;
                text-align: center;
            }

            .delete-confirm-icon {
                display: grid;
                width: 64px;
                height: 64px;
                margin: 0 auto 1rem;
                place-items: center;
                border-radius: 50%;
                background: #fef2f2;
                color: #dc2626;
                font-size: 1.8rem;
            }

            .delete-confirm-name {
                margin: 0.75rem 0;
                padding: 0.7rem 0.85rem;
                overflow-wrap: anywhere;
                border: 1px solid #e2e8f0;
                border-radius: 10px;
                background: #f8fafc;
                color: #1e293b;
                font-weight: 600;
            }

            .delete-confirm-actions {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 0.75rem;
                padding: 1rem 1.75rem 1.75rem;
            }

            .delete-confirm-acknowledgment {
                margin: 0 1.75rem;
                padding: 0.85rem;
                border: 1px solid #fecaca;
                border-radius: 10px;
                background: #fff7f7;
                text-align: left;
            }

            .delete-confirm-acknowledgment .form-check-label {
                color: #7f1d1d;
                font-size: 0.8rem;
                line-height: 1.4;
            }
        </style>
    @endpush

    <div class="modal fade" id="deleteConfirmationModal" tabindex="-1"
         aria-labelledby="deleteConfirmationTitle" aria-describedby="deleteConfirmationMessage"
         data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered delete-confirm-dialog">
            <div class="modal-content delete-confirm-content">
                <div class="delete-confirm-body">
                    <div class="delete-confirm-icon" aria-hidden="true">
                        <i class="mdi mdi-alert-outline"></i>
                    </div>
                    <h4 id="deleteConfirmationTitle" class="mb-2" data-delete-modal-title>Confirm deletion</h4>
                    <p id="deleteConfirmationMessage" class="mb-0 text-muted" data-delete-modal-message>
                        This action cannot be undone.
                    </p>
                    <div class="delete-confirm-name" data-delete-modal-name></div>
                </div>

                <form method="POST" data-delete-modal-form>
                    @csrf
                    @method('DELETE')
                    <div class="delete-confirm-acknowledgment">
                        <div class="form-check mb-0">
                            <input class="form-check-input" type="checkbox"
                                   id="deleteConfirmationAcknowledgment"
                                   data-delete-acknowledgment>
                            <label class="form-check-label" for="deleteConfirmationAcknowledgment">
                                I understand that this record will be permanently deleted.
                            </label>
                        </div>
                    </div>
                    <div class="delete-confirm-actions">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-danger" data-delete-modal-submit disabled>
                            <span data-delete-submit-label>Delete permanently</span>
                            <span class="spinner-border spinner-border-sm ms-1" data-delete-submit-spinner
                                  role="status" aria-hidden="true" hidden></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            (() => {
                const modalElement = document.getElementById('deleteConfirmationModal');
                const form = modalElement?.querySelector('[data-delete-modal-form]');
                const submit = modalElement?.querySelector('[data-delete-modal-submit]');
                const acknowledgment = modalElement?.querySelector('[data-delete-acknowledgment]');

                if (!modalElement || !form || !submit || !acknowledgment) {
                    return;
                }

                const modal = new bootstrap.Modal(modalElement, {
                    backdrop: 'static',
                    keyboard: false,
                });

                document.querySelectorAll('[data-delete-confirm]').forEach((trigger) => {
                    trigger.addEventListener('click', () => {
                        form.action = trigger.dataset.deleteAction;
                        modalElement.querySelector('[data-delete-modal-title]').textContent =
                            trigger.dataset.deleteTitle || 'Confirm deletion';
                        modalElement.querySelector('[data-delete-modal-message]').textContent =
                            trigger.dataset.deleteMessage || 'This action cannot be undone.';
                        modalElement.querySelector('[data-delete-modal-name]').textContent =
                            trigger.dataset.deleteName || 'Selected record';
                        acknowledgment.checked = false;
                        submit.disabled = true;
                        submit.querySelector('[data-delete-submit-label]').textContent = 'Delete permanently';
                        submit.querySelector('[data-delete-submit-spinner]').hidden = true;
                        modal.show();
                    });
                });

                acknowledgment.addEventListener('change', () => {
                    submit.disabled = !acknowledgment.checked;
                });

                form.addEventListener('submit', (event) => {
                    if (!acknowledgment.checked) {
                        event.preventDefault();
                        return;
                    }

                    submit.disabled = true;
                    submit.querySelector('[data-delete-submit-label]').textContent = 'Deleting…';
                    submit.querySelector('[data-delete-submit-spinner]').hidden = false;
                });
            })();
        </script>
    @endpush
@endonce
