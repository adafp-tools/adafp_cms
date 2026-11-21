import Alpine from 'alpinejs';
import 'trix';

window.Alpine = Alpine;

// Embed click-to-load placeholder
Alpine.data('embedPlaceholder', () => ({
    loaded: false,
    load() {
        if (this.loaded) return;
        const el  = this.$el;
        const src = el.dataset.src;
        if (! src) return;
        const iframe = document.createElement('iframe');
        iframe.src             = src;
        iframe.setAttribute('loading', 'lazy');
        iframe.setAttribute('allowfullscreen', '');
        iframe.style.width     = '100%';
        iframe.style.minHeight = '315px';
        iframe.style.border    = 'none';
        el.replaceWith(iframe);
        this.loaded = true;
    },
}));

Alpine.start();
