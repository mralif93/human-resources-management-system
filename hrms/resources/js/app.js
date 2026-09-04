import { createIcons, icons } from 'lucide';

// Initialize Lucide icons on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    createIcons({ icons });
});

window.createIcons = createIcons;
window.lucideIcons = icons;

