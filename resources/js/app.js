import './bootstrap';
import Alpine from 'alpinejs';
import { registerCartStore } from './pos/cart';
import { scanPage } from './pos/scan-page';
import { cartPage } from './pos/cart-page';
import { loginPage } from './pos/login-page';
import { checkoutPage } from './pos/checkout-page';
import { initIdleLogout } from './pos/idle-logout';

window.Alpine = Alpine;
window.MobilePOS = { initIdleLogout };

registerCartStore(Alpine);
Alpine.data('scanPage', scanPage);
Alpine.data('cartPage', cartPage);
Alpine.data('loginPage', loginPage);
Alpine.data('checkoutPage', checkoutPage);
Alpine.start();

if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').catch((error) => {
            console.error('Service worker registration failed:', error);
        });
    });
}
