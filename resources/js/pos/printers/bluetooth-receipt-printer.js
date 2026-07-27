/**
 * Optional extension point for a paired Bluetooth ESC/POS thermal receipt
 * printer, via the Web Bluetooth API. Not wired into the receipt UI by
 * default — most cashiers can use the phone's own print/share dialog
 * (see resources/views/receipts/show.blade.php), which needs no pairing
 * and works on any device. Wire this in only if a store has a specific
 * thermal printer model to support.
 *
 * Usage sketch (per-printer service UUIDs vary by manufacturer — consult
 * your printer's ESC/POS Bluetooth LE documentation):
 *
 *   const printer = new BluetoothReceiptPrinter({ serviceUuid, characteristicUuid });
 *   await printer.connect();
 *   await printer.printText(receiptPlainText);
 */
export class BluetoothReceiptPrinter {
    constructor({ serviceUuid, characteristicUuid }) {
        this.serviceUuid = serviceUuid;
        this.characteristicUuid = characteristicUuid;
        this.characteristic = null;
    }

    async connect() {
        if (!navigator.bluetooth) {
            throw new Error('Web Bluetooth is not supported in this browser.');
        }

        const device = await navigator.bluetooth.requestDevice({
            filters: [{ services: [this.serviceUuid] }],
        });

        const server = await device.gatt.connect();
        const service = await server.getPrimaryService(this.serviceUuid);
        this.characteristic = await service.getCharacteristic(this.characteristicUuid);
    }

    async printText(text) {
        if (!this.characteristic) {
            throw new Error('Printer not connected — call connect() first.');
        }

        const encoder = new TextEncoder();
        const bytes = encoder.encode(text + '\n\n\n');

        // Most BLE characteristics cap writes around 20 bytes; chunk accordingly.
        const chunkSize = 20;
        for (let i = 0; i < bytes.length; i += chunkSize) {
            await this.characteristic.writeValue(bytes.slice(i, i + chunkSize));
        }
    }
}
