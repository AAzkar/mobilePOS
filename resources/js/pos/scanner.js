import { Html5Qrcode, Html5QrcodeSupportedFormats } from 'html5-qrcode';

const SUPPORTED_FORMATS = [
    Html5QrcodeSupportedFormats.EAN_13,
    Html5QrcodeSupportedFormats.EAN_8,
    Html5QrcodeSupportedFormats.UPC_A,
    Html5QrcodeSupportedFormats.UPC_E,
    Html5QrcodeSupportedFormats.CODE_128,
    Html5QrcodeSupportedFormats.CODE_39,
    Html5QrcodeSupportedFormats.QR_CODE,
];

/**
 * Wraps html5-qrcode to scan continuously from the rear camera.
 * `onDecode(text)` fires per successful read; the camera keeps running
 * afterwards so the next item can be scanned immediately.
 */
export class CameraScanner {
    constructor(elementId) {
        this.elementId = elementId;
        this.html5Qrcode = null;
        this.running = false;
    }

    async start({ onDecode, onError, onPermissionDenied }) {
        // Defensive: if something calls start() twice without an intervening
        // stop() (e.g. a lifecycle hook firing more than once), tear down any
        // existing instance first so we never end up with two <video>
        // elements stacked in the same container.
        await this.stop();
        document.getElementById(this.elementId)?.replaceChildren();

        this.html5Qrcode = new Html5Qrcode(this.elementId, {
            formatsToSupport: SUPPORTED_FORMATS,
            verbose: false,
        });

        const config = {
            fps: 10,
            qrbox: { width: 250, height: 150 },
            aspectRatio: 1.7777778,
        };

        try {
            await this.html5Qrcode.start(
                { facingMode: 'environment' },
                config,
                (decodedText) => onDecode(decodedText),
                () => {
                    // Per-frame "not found" noise — intentionally ignored.
                },
            );
            this.running = true;
        } catch (error) {
            const name = error?.name || '';
            const message = String(error?.message || error);
            const denied = name === 'NotAllowedError' || /permission/i.test(message);

            if (denied && onPermissionDenied) {
                onPermissionDenied(error);
            } else if (onError) {
                onError(error);
            }
        }
    }

    async stop() {
        if (this.html5Qrcode && this.running) {
            try {
                await this.html5Qrcode.stop();
                this.html5Qrcode.clear();
            } catch (e) {
                // Already stopped/torn down — nothing to do.
            }
            this.running = false;
        }
    }
}

/**
 * Detects Bluetooth/USB HID "keyboard wedge" barcode scanners: they type
 * digits fast (well under human typing speed) and terminate with Enter.
 * Attaches a document-level keydown listener and calls onScan(code) when a
 * fast burst + Enter is detected. Returns a function to remove the listener.
 */
export function listenForKeyboardWedge(onScan, options = {}) {
    const maxIntervalMs = options.maxIntervalMs ?? 40;
    const minLength = options.minLength ?? 3;

    let buffer = '';
    let lastKeyTime = 0;

    function isTypingIntoField(target) {
        const tag = target?.tagName;
        return tag === 'INPUT' || tag === 'TEXTAREA' || target?.isContentEditable;
    }

    function handler(event) {
        // If a text field already has focus, leave it alone entirely — its own
        // value + the manual-entry form's Enter-to-submit handles that case,
        // whether the keystrokes come from a human or a scanner typing into it.
        // Intercepting here too would double-fire and, worse, swallow keystrokes
        // mid-barcode once the buffer looked "burst-like". The wedge detector
        // only needs to catch scans that arrive while nothing is focused (e.g.
        // the cashier is just looking at the camera view).
        if (isTypingIntoField(event.target)) {
            return;
        }

        const now = performance.now();
        const elapsed = now - lastKeyTime;
        lastKeyTime = now;

        if (event.key === 'Enter') {
            const code = buffer;
            buffer = '';
            if (code.length >= minLength && elapsed <= maxIntervalMs) {
                event.preventDefault();
                onScan(code);
            }
            return;
        }

        if (event.key.length !== 1) {
            // Ignore modifier/navigation keys but don't break the buffer timing.
            return;
        }

        if (elapsed > maxIntervalMs) {
            // Gap too long since last keystroke — starting a new burst.
            buffer = event.key;
        } else {
            buffer += event.key;
        }
    }

    document.addEventListener('keydown', handler, true);

    return () => document.removeEventListener('keydown', handler, true);
}

let audioCtx = null;

/**
 * Short confirmation beep via Web Audio — no static asset required.
 */
export function beep() {
    try {
        audioCtx = audioCtx || new (window.AudioContext || window.webkitAudioContext)();
        const oscillator = audioCtx.createOscillator();
        const gain = audioCtx.createGain();
        oscillator.type = 'sine';
        oscillator.frequency.value = 880;
        gain.gain.setValueAtTime(0.2, audioCtx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.15);
        oscillator.connect(gain);
        gain.connect(audioCtx.destination);
        oscillator.start();
        oscillator.stop(audioCtx.currentTime + 0.15);
    } catch (e) {
        // Audio isn't essential to the scan flow — fail silently.
    }
}
