    </main>

        <!-- Footer -->
        <footer class="bg-white border-t border-slate-200 py-4 px-4 sm:px-8 text-center text-xs text-slate-500 no-print">
            <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-1">
                <span>759/A ගල්හේන ග්‍රාම නිලධාරී වසම් තොරතුරු කළමනාකරණ පද්ධතිය &copy; <?php echo date('Y'); ?></span>
                <span class="text-slate-400">Logged in as <strong class="text-slate-500"><?php echo htmlspecialchars($_SESSION['full_name'] ?? ''); ?></strong></span>
            </div>
        </footer>
    </div>

    <!-- Shared UI Scripts (sidebar, dropdowns, alerts) -->
    <script>
        (function () {
            const sidebar = document.getElementById('appSidebar');
            const overlay = document.getElementById('sidebarOverlay');
            const openBtn = document.getElementById('sidebarOpenBtn');
            const closeBtn = document.getElementById('sidebarCloseBtn');

            function openSidebar() {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
            }
            function closeSidebar() {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
            }
            if (openBtn) openBtn.addEventListener('click', openSidebar);
            if (closeBtn) closeBtn.addEventListener('click', closeSidebar);
            if (overlay) overlay.addEventListener('click', closeSidebar);

            // Dropdown helper
            function setupDropdown(btnId, menuId) {
                const btn = document.getElementById(btnId);
                const menu = document.getElementById(menuId);
                if (!btn || !menu) return;
                btn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    document.querySelectorAll('.js-dropdown-open').forEach(function (el) {
                        if (el !== menu) el.classList.add('hidden'), el.classList.remove('js-dropdown-open');
                    });
                    menu.classList.toggle('hidden');
                    menu.classList.toggle('js-dropdown-open');
                });
            }
            setupDropdown('notifBtn', 'notifDropdown');
            setupDropdown('userMenuBtn', 'userMenuDropdown');

            document.addEventListener('click', function () {
                document.querySelectorAll('.js-dropdown-open').forEach(function (el) {
                    el.classList.add('hidden');
                    el.classList.remove('js-dropdown-open');
                });
            });

            // Auto-dismiss flash alerts
            setTimeout(function () {
                document.querySelectorAll('.flash-alert').forEach(function (el) {
                    el.style.transition = 'opacity .5s ease';
                    el.style.opacity = '0';
                    setTimeout(function () { el.remove(); }, 500);
                });
            }, 5000);
        })();
    </script>
</body>
</html>
