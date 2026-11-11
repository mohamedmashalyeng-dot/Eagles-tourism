(function () {
    'use strict';

    /* Sticky header */
    var header = document.getElementById('siteHeader');
    var onScroll = function () {
        if (header) header.classList.toggle('is-sticky', window.scrollY > 120);
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();

    /* Mobile nav */
    var toggle = document.getElementById('navToggle');
    var nav = document.getElementById('mainNav');
    if (toggle && nav) {
        toggle.addEventListener('click', function () {
            var open = nav.classList.toggle('is-open');
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
        nav.addEventListener('click', function (e) {
            if (e.target.closest('a')) nav.classList.remove('is-open');
        });
    }

    /* Booking total */
    var form = document.getElementById('bookForm');
    if (form) {
        var adult = parseFloat(form.dataset.adult) || 0;
        var child = parseFloat(form.dataset.child) || 0;
        var cur = form.dataset.currency || '';
        var out = document.getElementById('bookTotal');
        var a = form.querySelector('[name=adults]');
        var c = form.querySelector('[name=children]');
        var calc = function () {
            var total = Math.max(1, parseInt(a.value, 10) || 0) * adult + Math.max(0, parseInt(c.value, 10) || 0) * child;
            out.textContent = total.toLocaleString('en-US') + ' ' + cur;
        };
        a.addEventListener('input', calc);
        c.addEventListener('input', calc);
        calc();
    }

    /* Lightbox */
    var lb = document.getElementById('lightbox');
    if (lb) {
        var img = lb.querySelector('img');
        var cap = lb.querySelector('figcaption');
        var items = [], idx = 0;
        var show = function (i) {
            idx = (i + items.length) % items.length;
            var el = items[idx];
            img.src = el.getAttribute('href');
            img.alt = el.dataset.caption || '';
            cap.textContent = el.dataset.caption || '';
        };
        var close = function () { lb.hidden = true; document.body.style.overflow = ''; };
        document.addEventListener('click', function (e) {
            var link = e.target.closest('[data-lightbox]');
            if (!link) return;
            e.preventDefault();
            var group = link.closest('[data-lightbox-group]');
            items = Array.prototype.slice.call((group || document).querySelectorAll('[data-lightbox]'));
            show(items.indexOf(link));
            lb.hidden = false;
            document.body.style.overflow = 'hidden';
        });
        lb.querySelector('.lightbox__close').addEventListener('click', close);
        lb.querySelector('.lightbox__next').addEventListener('click', function () { show(idx + 1); });
        lb.querySelector('.lightbox__prev').addEventListener('click', function () { show(idx - 1); });
        lb.addEventListener('click', function (e) { if (e.target === lb) close(); });
        document.addEventListener('keydown', function (e) {
            if (lb.hidden) return;
            if (e.key === 'Escape') close();
            if (e.key === 'ArrowLeft') show(idx + 1);
            if (e.key === 'ArrowRight') show(idx - 1);
        });
        img.addEventListener('error', function () { if (lb.dataset.placeholder && img.src.indexOf('placeholder') < 0) img.src = lb.dataset.placeholder; });
    }

    /* Reveal on scroll */
    if ('IntersectionObserver' in window) {
        var targets = document.querySelectorAll('.trip-card, .cat-card, .place-tile, .review, .gallery-item, .stat, .features li');
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (en) {
                if (en.isIntersecting) { en.target.classList.add('is-visible'); io.unobserve(en.target); }
            });
        }, { rootMargin: '0px 0px -40px 0px' });
        targets.forEach(function (t) { t.classList.add('reveal'); io.observe(t); });
    }
})();
