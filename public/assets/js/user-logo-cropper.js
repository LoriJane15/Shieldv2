(() => {
    const fallbackLogo = '/assets/img/kc-logo.svg';
    const editors = new WeakMap();

    class UserLogoCropper {
        constructor(root) {
            this.root = root;
            this.input = root.querySelector('[data-logo-input]');
            this.preview = root.querySelector('[data-logo-preview]');
            this.status = root.querySelector('[data-logo-status]');
            this.workspace = root.querySelector('[data-logo-crop-workspace]');
            this.canvas = root.querySelector('[data-logo-canvas]');
            this.context = this.canvas.getContext('2d');
            this.zoom = root.querySelector('[data-logo-zoom]');
            this.error = root.querySelector('[data-logo-error]');
            this.recrop = root.querySelector('[data-logo-recrop]');
            this.image = null;
            this.imageUrl = null;
            this.existingUrl = fallbackLogo;
            this.hasExistingLogo = false;
            this.offsetX = 0;
            this.offsetY = 0;
            this.dragStart = null;
            this.cropAccepted = true;

            this.bindEvents();
        }

        bindEvents() {
            this.input.addEventListener('change', () => this.openSelectedFile());
            this.zoom.addEventListener('input', () => {
                this.clampOffsets();
                this.render();
            });

            this.root.querySelector('[data-logo-crop-accept]')
                .addEventListener('click', () => this.acceptCrop());
            this.root.querySelector('[data-logo-crop-cancel]')
                .addEventListener('click', () => this.cancelCrop());

            if (this.recrop) {
                this.recrop.addEventListener('click', () => this.openImage(this.existingUrl));
            }

            this.canvas.addEventListener('pointerdown', (event) => this.startDrag(event));
            this.canvas.addEventListener('pointermove', (event) => this.drag(event));
            this.canvas.addEventListener('pointerup', () => this.endDrag());
            this.canvas.addEventListener('pointercancel', () => this.endDrag());

            this.root.closest('form').addEventListener('submit', (event) => {
                if (!this.cropAccepted) {
                    event.preventDefault();
                    event.stopImmediatePropagation();
                    this.showError('Align the logo and select “Use cropped logo” before saving.');
                    this.workspace.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }, true);
        }

        setExistingLogo(url, hasLogo) {
            this.resetObjectUrl();
            this.existingUrl = url || fallbackLogo;
            this.hasExistingLogo = hasLogo;
            this.preview.src = this.existingUrl;
            this.status.textContent = hasLogo ? 'Current logo' : 'No custom logo';
            this.recrop?.toggleAttribute('hidden', !hasLogo);
            this.input.value = '';
            this.workspace.hidden = true;
            this.cropAccepted = true;
            this.hideError();
        }

        openSelectedFile() {
            const [file] = this.input.files;

            if (!file) {
                return;
            }

            if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
                this.input.value = '';
                this.showError('Choose a JPG, PNG, or WebP image.');
                return;
            }

            if (file.size > 5 * 1024 * 1024) {
                this.input.value = '';
                this.showError('The logo must not exceed 5 MB.');
                return;
            }

            this.resetObjectUrl();
            this.imageUrl = URL.createObjectURL(file);
            this.openImage(this.imageUrl);
        }

        openImage(url) {
            this.hideError();

            const image = new Image();
            image.onload = () => {
                this.image = image;
                this.zoom.value = '1';
                this.offsetX = 0;
                this.offsetY = 0;
                this.cropAccepted = false;
                this.workspace.hidden = false;
                this.render();
            };
            image.onerror = () => this.showError('The selected logo could not be loaded.');
            image.src = url;
        }

        render() {
            if (!this.image) {
                return;
            }

            const bounds = this.movementBounds();
            const { width, height } = this.canvas;
            const scale = this.scale();
            const drawWidth = this.image.naturalWidth * scale;
            const drawHeight = this.image.naturalHeight * scale;
            const x = (width - drawWidth) / 2 + this.offsetX;
            const y = (height - drawHeight) / 2 + this.offsetY;

            this.canvas.classList.toggle('is-draggable', bounds.maxX > 0 || bounds.maxY > 0);
            this.context.clearRect(0, 0, width, height);
            this.context.drawImage(this.image, x, y, drawWidth, drawHeight);
        }

        scale() {
            const coverScale = Math.max(
                this.canvas.width / this.image.naturalWidth,
                this.canvas.height / this.image.naturalHeight
            );

            return coverScale * Number(this.zoom.value);
        }

        movementBounds() {
            const scale = this.scale();
            const horizontalOverflow = (this.image.naturalWidth * scale - this.canvas.width) / 2;
            const verticalOverflow = (this.image.naturalHeight * scale - this.canvas.height) / 2;

            return {
                maxX: horizontalOverflow > 0.5 ? horizontalOverflow : 0,
                maxY: verticalOverflow > 0.5 ? verticalOverflow : 0,
            };
        }

        clampOffsets() {
            if (!this.image) {
                return;
            }

            const { maxX, maxY } = this.movementBounds();

            this.offsetX = Math.min(maxX, Math.max(-maxX, this.offsetX));
            this.offsetY = Math.min(maxY, Math.max(-maxY, this.offsetY));
        }

        startDrag(event) {
            if (!this.image) {
                return;
            }

            const { maxX, maxY } = this.movementBounds();

            if (maxX === 0 && maxY === 0) {
                return;
            }

            this.canvas.setPointerCapture(event.pointerId);
            this.dragStart = {
                x: event.clientX,
                y: event.clientY,
                offsetX: this.offsetX,
                offsetY: this.offsetY,
            };
        }

        drag(event) {
            if (!this.dragStart) {
                return;
            }

            const { maxX, maxY } = this.movementBounds();
            const ratio = this.canvas.width / this.canvas.getBoundingClientRect().width;
            this.offsetX = maxX > 0
                ? this.dragStart.offsetX + (event.clientX - this.dragStart.x) * ratio
                : 0;
            this.offsetY = maxY > 0
                ? this.dragStart.offsetY + (event.clientY - this.dragStart.y) * ratio
                : 0;
            this.clampOffsets();
            this.render();
        }

        endDrag() {
            this.dragStart = null;
        }

        acceptCrop() {
            this.canvas.toBlob((blob) => {
                if (!blob) {
                    this.showError('The cropped logo could not be prepared.');
                    return;
                }

                const file = new File([blob], `user-logo-${Date.now()}.jpg`, { type: 'image/jpeg' });
                const transfer = new DataTransfer();
                transfer.items.add(file);
                this.input.files = transfer.files;

                this.resetObjectUrl();
                this.imageUrl = URL.createObjectURL(blob);
                this.preview.src = this.imageUrl;
                this.status.textContent = 'Cropped logo ready';
                this.workspace.hidden = true;
                this.cropAccepted = true;
                this.hideError();
            }, 'image/jpeg', 0.9);
        }

        cancelCrop() {
            this.input.value = '';
            this.workspace.hidden = true;
            this.cropAccepted = true;
            this.preview.src = this.existingUrl;
            this.status.textContent = this.hasExistingLogo ? 'Current logo' : 'No custom logo';
            this.hideError();
        }

        showError(message) {
            this.error.textContent = message;
            this.error.hidden = false;
        }

        hideError() {
            this.error.textContent = '';
            this.error.hidden = true;
        }

        resetObjectUrl() {
            if (this.imageUrl) {
                URL.revokeObjectURL(this.imageUrl);
                this.imageUrl = null;
            }
        }
    }

    document.querySelectorAll('[data-logo-editor]').forEach((root) => {
        const cropper = new UserLogoCropper(root);
        editors.set(root, cropper);

        if (root.dataset.isEdit !== 'true') {
            cropper.setExistingLogo(fallbackLogo, false);
        }
    });

    window.UserLogoCropper = {
        setExistingLogo(root, url, hasLogo) {
            editors.get(root)?.setExistingLogo(url, hasLogo);
        },
    };
})();
