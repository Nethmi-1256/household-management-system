/* ==========================================================
   GN 759/A Galhena — Shared Theme JS (count-up + reveal)
   ========================================================== */
(function () {
    // ---- Animated number count-up for elements with [data-countup] ----
    // Usage: <h3 data-countup="1234">0</h3>
    function animateCount(el) {
        var target = parseFloat(el.getAttribute('data-countup'));
        if (isNaN(target)) return;
        var duration = 900;
        var start = null;
        var startVal = 0;
        var isFloat = target % 1 !== 0;

        function step(ts) {
            if (!start) start = ts;
            var progress = Math.min((ts - start) / duration, 1);
            var eased = 1 - Math.pow(1 - progress, 3); // ease-out-cubic
            var current = startVal + (target - startVal) * eased;
            el.textContent = isFloat
                ? current.toFixed(1)
                : Math.round(current).toLocaleString('en-US');
            if (progress < 1) {
                requestAnimationFrame(step);
            } else {
                el.textContent = isFloat ? target.toFixed(1) : target.toLocaleString('en-US');
            }
        }
        requestAnimationFrame(step);
    }

    function initCountUps(root) {
        (root || document).querySelectorAll('[data-countup]').forEach(function (el) {
            if (el.__gnCounted) return;
            el.__gnCounted = true;
            animateCount(el);
        });
    }

    // ---- Reveal-on-scroll for elements with [data-reveal] ----
    function initReveal() {
        var items = document.querySelectorAll('[data-reveal]');
        if (!items.length) return;
        if (!('IntersectionObserver' in window)) {
            items.forEach(function (el) { el.classList.add('gn-reveal'); });
            return;
        }
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('gn-reveal');
                    io.unobserve(entry.target);
                }
            });
        }, { threshold: .15 });
        items.forEach(function (el) { io.observe(el); });
    }

    // ---- Animated width bars for elements with [data-bar-fill] (0-100) ----
    function initBars() {
        document.querySelectorAll('[data-bar-fill]').forEach(function (el) {
            var pct = el.getAttribute('data-bar-fill');
            requestAnimationFrame(function () {
                el.style.width = pct + '%';
            });
        });
    }

    // ---- Ripple effect for buttons/links with class "gn-ripple" ----
    function initRipple() {
        document.addEventListener('click', function (e) {
            var target = e.target.closest('.gn-ripple');
            if (!target) return;
            var rect = target.getBoundingClientRect();
            var size = Math.max(rect.width, rect.height);
            var dot = document.createElement('span');
            dot.className = 'gn-ripple-dot';
            dot.style.width = dot.style.height = size + 'px';
            dot.style.left = (e.clientX - rect.left - size / 2) + 'px';
            dot.style.top = (e.clientY - rect.top - size / 2) + 'px';
            target.appendChild(dot);
            setTimeout(function () { dot.remove(); }, 650);
        });
    }

    // ---- Subtle 3D tilt for elements with [data-tilt] ----
    function initTilt() {
        var items = document.querySelectorAll('[data-tilt]');
        if (!items.length) return;
        items.forEach(function (el) {
            el.style.transformStyle = 'preserve-3d';
            el.addEventListener('mousemove', function (e) {
                var r = el.getBoundingClientRect();
                var x = (e.clientX - r.left) / r.width - 0.5;
                var y = (e.clientY - r.top) / r.height - 0.5;
                el.style.transform = 'perspective(700px) rotateY(' + (x * 6) + 'deg) rotateX(' + (y * -6) + 'deg) translateY(-3px)';
            });
            el.addEventListener('mouseleave', function () {
                el.style.transform = 'perspective(700px) rotateY(0) rotateX(0) translateY(0)';
            });
        });
    }

    // ---- Lightweight toast notifications: window.gnToast('message', 'success'|'danger'|'info') ----
    window.gnToast = function (msg, type) {
        type = type || 'success';
        var colors = {
            success: 'bg-emerald-600',
            danger: 'bg-rose-600',
            info: 'bg-blue-600'
        };
        var icons = {
            success: 'fa-circle-check',
            danger: 'fa-circle-exclamation',
            info: 'fa-circle-info'
        };
        var wrap = document.getElementById('gnToastWrap');
        if (!wrap) {
            wrap = document.createElement('div');
            wrap.id = 'gnToastWrap';
            wrap.style.cssText = 'position:fixed;top:1rem;right:1rem;z-index:9999;display:flex;flex-direction:column;gap:.5rem;';
            document.body.appendChild(wrap);
        }
        var toast = document.createElement('div');
        toast.className = (colors[type] || colors.success) + ' text-white text-sm font-semibold px-4 py-3 rounded-xl shadow-lg flex items-center gap-2';
        toast.style.cssText = 'animation:gnFadeIn .25s ease; opacity:0; transform:translateX(20px); transition:opacity .3s ease, transform .3s ease;';
        toast.innerHTML = '<i class="fa-solid ' + (icons[type] || icons.success) + '"></i><span>' + msg + '</span>';
        wrap.appendChild(toast);
        requestAnimationFrame(function () {
            toast.style.opacity = '1';
            toast.style.transform = 'translateX(0)';
        });
        setTimeout(function () {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(20px)';
            setTimeout(function () { toast.remove(); }, 300);
        }, 3200);
    };

    document.addEventListener('DOMContentLoaded', function () {
        initCountUps();
        initReveal();
        initBars();
        initRipple();
        initTilt();
    });
})();
