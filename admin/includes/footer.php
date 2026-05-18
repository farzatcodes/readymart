    </main>

    <!-- ═══ MOBILE BOTTOM NAV ════════════════════════════════════════ -->
    <nav class="md:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 z-40">
        <div class="flex">
            <?php $cp = basename($_SERVER['PHP_SELF']); ?>
            <a href="index.php"
               class="flex-1 flex flex-col items-center justify-center py-2 gap-0.5 <?= $cp==='index.php' ? 'text-red-500' : 'text-gray-400' ?> active:scale-90 transition-transform">
                <i class="fas fa-tachometer-alt text-lg"></i>
                <span class="text-[10px] font-semibold">Home</span>
            </a>
            <a href="orders.php"
               class="flex-1 flex flex-col items-center justify-center py-2 gap-0.5 <?= in_array($cp,['orders.php','view_order.php']) ? 'text-red-500' : 'text-gray-400' ?> active:scale-90 transition-transform">
                <i class="fas fa-box-open text-lg"></i>
                <span class="text-[10px] font-semibold">Orders</span>
            </a>
            <a href="products.php"
               class="flex-1 flex flex-col items-center justify-center py-2 gap-0.5 <?= in_array($cp,['products.php','add_product.php','edit_product.php']) ? 'text-red-500' : 'text-gray-400' ?> active:scale-90 transition-transform">
                <i class="fas fa-tags text-lg"></i>
                <span class="text-[10px] font-semibold">Products</span>
            </a>
            <a href="landing_pages.php"
               class="flex-1 flex flex-col items-center justify-center py-2 gap-0.5 <?= in_array($cp,['landing_pages.php','manage_landing.php']) ? 'text-red-500' : 'text-gray-400' ?> active:scale-90 transition-transform">
                <i class="fas fa-rocket text-lg"></i>
                <span class="text-[10px] font-semibold">Landing</span>
            </a>
            <a href="settings.php"
               class="flex-1 flex flex-col items-center justify-center py-2 gap-0.5 <?= $cp==='settings.php' ? 'text-red-500' : 'text-gray-400' ?> active:scale-90 transition-transform">
                <i class="fas fa-cog text-lg"></i>
                <span class="text-[10px] font-semibold">Settings</span>
            </a>
        </div>
    </nav>

</div><!-- /.main content wrapper -->

<script>
(function () {
    var sidebar  = document.getElementById('sidebar');
    var openBtn  = document.getElementById('openSidebar');
    var closeBtn = document.getElementById('closeSidebar');
    var overlay  = document.getElementById('sidebarOverlay');
    if (!sidebar) return;

    function openSidebarFn() {
        sidebar.classList.remove('-translate-x-full');
        overlay.classList.remove('hidden');
        requestAnimationFrame(function(){ overlay.classList.remove('opacity-0'); });
        document.body.style.overflow = 'hidden';
    }
    window.closeSidebarFn = function () {
        sidebar.classList.add('-translate-x-full');
        overlay.classList.add('opacity-0');
        setTimeout(function(){ overlay.classList.add('hidden'); }, 280);
        document.body.style.overflow = '';
    };

    if (openBtn)  openBtn.addEventListener('click', openSidebarFn);
    if (closeBtn) closeBtn.addEventListener('click', window.closeSidebarFn);
    if (overlay)  overlay.addEventListener('click', window.closeSidebarFn);

    var touchStartX = 0;
    sidebar.addEventListener('touchstart', function(e){ touchStartX = e.changedTouches[0].clientX; }, {passive:true});
    sidebar.addEventListener('touchend',   function(e){
        if (touchStartX - e.changedTouches[0].clientX > 60) window.closeSidebarFn();
    }, {passive:true});
})();
</script>
</body>
</html>
