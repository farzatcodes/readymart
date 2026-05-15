</main>
    </div> <script>
        document.addEventListener('DOMContentLoaded', () => {
            const sidebar = document.getElementById('sidebar');
            const openBtn = document.getElementById('openSidebar');
            const closeBtn = document.getElementById('closeSidebar');
            const overlay = document.getElementById('sidebarOverlay');

            function openMenu() {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
            }

            function closeMenu() {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
            }

            if(openBtn) openBtn.addEventListener('click', openMenu);
            if(closeBtn) closeBtn.addEventListener('click', closeMenu);
            if(overlay) overlay.addEventListener('click', closeMenu);
        });
    </script>
</body>
</html>