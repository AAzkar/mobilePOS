import Alpine from 'alpinejs';
import { CameraScanner, listenForKeyboardWedge, beep } from './scanner';

export function scanPage() {
    return {
        cameraStatus: 'starting', // starting | running | denied | error
        errorMessage: '',
        flash: false,
        lastScan: null, // { name, qty, price }
        unknownBarcode: null,
        manualCode: '',
        scanner: null,
        stopWedgeListener: null,
        _lastCode: null,
        _lastCodeAt: 0,

        init() {
            this.scanner = new CameraScanner('camera-scanner');
            this.startCamera();

            this.stopWedgeListener = listenForKeyboardWedge((code) => this.handleCode(code));

            const params = new URLSearchParams(window.location.search);
            const added = params.get('added');
            if (added) {
                this.handleCode(added);
                params.delete('added');
                const clean = window.location.pathname + (params.toString() ? `?${params}` : '');
                window.history.replaceState({}, '', clean);
            }

            window.addEventListener('beforeunload', () => this.scanner?.stop());
        },

        async startCamera() {
            this.cameraStatus = 'starting';
            await this.scanner.start({
                onDecode: (code) => this.handleCode(code),
                onPermissionDenied: () => {
                    this.cameraStatus = 'denied';
                },
                onError: (error) => {
                    this.cameraStatus = 'error';
                    this.errorMessage = String(error?.message || error);
                },
            });
            if (this.cameraStatus === 'starting') {
                this.cameraStatus = 'running';
            }
        },

        async retryCamera() {
            await this.scanner.stop();
            await this.startCamera();
        },

        submitManual() {
            const code = this.manualCode.trim();
            if (!code) return;
            this.handleCode(code);
            this.manualCode = '';
        },

        async handleCode(code) {
            const now = performance.now();
            if (code === this._lastCode && now - this._lastCodeAt < 1500) {
                return; // debounce duplicate reads of the same item
            }
            this._lastCode = code;
            this._lastCodeAt = now;

            try {
                const response = await fetch(`/api/products/lookup/${encodeURIComponent(code)}`);
                const data = await response.json();

                if (data.found) {
                    Alpine.store('cart').addProduct(data.product);
                    this.lastScan = { name: data.product.name, price: data.product.price };
                    this.triggerFlash();
                    beep();
                } else {
                    this.unknownBarcode = code;
                }
            } catch (e) {
                this.errorMessage = 'Could not reach the server to look up that barcode.';
            }
        },

        dismissUnknown() {
            this.unknownBarcode = null;
        },

        triggerFlash() {
            this.flash = true;
            setTimeout(() => {
                this.flash = false;
            }, 400);
        },
    };
}
