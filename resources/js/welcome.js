/* ============================================
   SIDOCMA Welcome - Custom Cursor + Interactions
   ============================================ */

(function () {
    'use strict';

    // ===== Detect touch device =====
    const isTouch = 'ontouchstart' in window || navigator.maxTouchPoints > 0;
    if (isTouch) return;

    // ===== Custom Cursor =====
    const cursorDot = document.querySelector('.cursor-dot');
    const cursorRing = document.querySelector('.cursor-ring');
    const cursorTrail = document.querySelector('.cursor-trail');

    let mouseX = window.innerWidth / 2;
    let mouseY = window.innerHeight / 2;
    let trailX = mouseX;
    let trailY = mouseY;
    let ringX = mouseX;
    let ringY = mouseY;
    let dotX = mouseX;
    let dotY = mouseY;

    document.addEventListener('mousemove', (e) => {
        mouseX = e.clientX;
        mouseY = e.clientY;
    });

    function animateCursor() {
        // Dot follows mouse instantly
        dotX += (mouseX - dotX) * 1;
        dotY += (mouseY - dotY) * 1;

        // Ring follows with easing
        ringX += (mouseX - ringX) * 0.2;
        ringY += (mouseY - ringY) * 0.2;

        // Trail follows with delay
        trailX += (mouseX - trailX) * 0.08;
        trailY += (mouseY - trailY) * 0.08;

        if (cursorDot) cursorDot.style.transform = `translate(${dotX}px, ${dotY}px) translate(-50%, -50%)`;
        if (cursorRing) cursorRing.style.transform = `translate(${ringX}px, ${ringY}px) translate(-50%, -50%)`;
        if (cursorTrail) cursorTrail.style.transform = `translate(${trailX}px, ${trailY}px) translate(-50%, -50%)`;

        requestAnimationFrame(animateCursor);
    }
    animateCursor();

    // ===== Hover state =====
    document.querySelectorAll('.cursor-hover').forEach(el => {
        el.addEventListener('mouseenter', () => {
            cursorRing?.classList.add('hover');
            cursorTrail?.classList.add('hover');
        });
        el.addEventListener('mouseleave', () => {
            cursorRing?.classList.remove('hover');
            cursorTrail?.classList.remove('hover');
        });
    });

    // ===== Cursor follows interactive elements (a, button, .magnetic-btn) =====
    document.querySelectorAll('a, button, .magnetic-btn').forEach(el => {
        if (!el.classList.contains('cursor-hover')) {
            el.classList.add('cursor-hover');
            el.addEventListener('mouseenter', () => {
                cursorRing?.classList.add('hover');
                cursorTrail?.classList.add('hover');
            });
            el.addEventListener('mouseleave', () => {
                cursorRing?.classList.remove('hover');
                cursorTrail?.classList.remove('hover');
            });
        }
    });

    // ===== Magnetic Buttons =====
    document.querySelectorAll('.magnetic-btn').forEach(btn => {
        btn.addEventListener('mousemove', (e) => {
            const rect = btn.getBoundingClientRect();
            const x = e.clientX - rect.left - rect.width / 2;
            const y = e.clientY - rect.top - rect.height / 2;
            btn.style.transform = `translate(${x * 0.3}px, ${y * 0.3}px)`;
        });

        btn.addEventListener('mouseleave', () => {
            btn.style.transform = 'translate(0, 0)';
        });
    });

    // ===== Reveal on Load =====
    setTimeout(() => {
        document.querySelectorAll('.reveal-left, .reveal-right, .reveal-up').forEach(el => {
            el.classList.add('visible');
        });
    }, 200);

    // ===== Theme Toggle (default = dark) =====
    const html = document.documentElement;
    const themeIcon = document.getElementById('theme-icon');
    const themeIconMobile = document.getElementById('theme-icon-mobile');
    const themeToggle = document.getElementById('theme-toggle');
    const themeToggleMobile = document.getElementById('theme-toggle-mobile');

    function applyTheme(theme) {
        if (theme === 'light') {
            html.classList.remove('dark');
            if (themeIcon) { themeIcon.classList.remove('fa-moon'); themeIcon.classList.add('fa-sun'); }
            if (themeIconMobile) { themeIconMobile.classList.remove('fa-moon'); themeIconMobile.classList.add('fa-sun'); }
        } else {
            html.classList.add('dark');
            if (themeIcon) { themeIcon.classList.remove('fa-sun'); themeIcon.classList.add('fa-moon'); }
            if (themeIconMobile) { themeIconMobile.classList.remove('fa-sun'); themeIconMobile.classList.add('fa-moon'); }
        }
    }

    // Read saved theme (default dark)
    const savedTheme = localStorage.getItem('theme') || 'dark';
    applyTheme(savedTheme);

    function toggleTheme() {
        const newTheme = html.classList.contains('dark') ? 'light' : 'dark';
        localStorage.setItem('theme', newTheme);
        applyTheme(newTheme);
    }

    themeToggle?.addEventListener('click', toggleTheme);
    themeToggleMobile?.addEventListener('click', toggleTheme);

    // ===== Navbar Scroll =====
    const navbar = document.getElementById('navbar');
    const handleScroll = () => {
        if (window.scrollY > 50) {
            navbar?.classList.add('navbar-scrolled');
        } else {
            navbar?.classList.remove('navbar-scrolled');
        }
    };
    window.addEventListener('scroll', handleScroll);
    handleScroll();

    // ===== Hide cursor when leaving window =====
    document.addEventListener('mouseleave', () => {
        cursorDot?.style.setProperty('opacity', '0');
        cursorRing?.style.setProperty('opacity', '0');
        cursorTrail?.style.setProperty('opacity', '0');
    });

    document.addEventListener('mouseenter', () => {
        cursorDot?.style.setProperty('opacity', '1');
        cursorRing?.style.setProperty('opacity', '1');
        cursorTrail?.style.setProperty('opacity', '1');
    });

    // ===== Cursor "click" effect =====
    document.addEventListener('mousedown', () => {
        cursorRing?.classList.add('hover');
        cursorTrail?.classList.add('hover');
    });

    document.addEventListener('mouseup', () => {
        const stillHovering = document.querySelector(':hover.cursor-hover');
        if (!stillHovering) {
            cursorRing?.classList.remove('hover');
            cursorTrail?.classList.remove('hover');
        }
    });

})();