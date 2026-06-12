/**
 * Barcode Scanner Handler
 * Detects USB/hardware barcode scanner input (keyboard wedge mode).
 * Scanners typically send characters rapidly followed by 'Enter'.
 * Usage: new BarcodeScanner({ onScan: (barcode) => {}, minLength: 3, interval: 50 })
 */
class BarcodeScanner {
    constructor(options = {}) {
        this.onScan = options.onScan || ((barcode) => console.log('Scanned:', barcode));
        this.onKey = options.onKey || null;
        this.minLength = options.minLength || 3;
        this.interval = options.interval || 50;
        this.timeout = options.timeout || 300;

        this.buffer = '';
        this.lastTime = 0;
        this.eventHandler = null;
    }

    attach(element = document) {
        this.eventHandler = (e) => {
            if (this.onKey) this.onKey(e);

            if (e.key === 'Enter') {
                const barcode = this.buffer.trim();
                if (barcode.length >= this.minLength) {
                    this.onScan(barcode);
                    e.preventDefault();
                }
                this.buffer = '';
                this.lastTime = 0;
                return;
            }

            if (e.key.length === 1 || e.code.startsWith('Digit')) {
                const now = Date.now();
                if (now - this.lastTime > this.timeout) {
                    this.buffer = '';
                }
                this.buffer += e.key;
                this.lastTime = now;
            }
        };

        element.addEventListener('keydown', this.eventHandler);
        return this;
    }

    detach(element = document) {
        if (this.eventHandler) {
            element.removeEventListener('keydown', this.eventHandler);
        }
        return this;
    }
}

window.BarcodeScanner = BarcodeScanner;
