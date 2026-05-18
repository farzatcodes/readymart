    </main>

    <!-- ═══ MOBILE BOTTOM NAV ════════════════════════════════════════ -->
    <nav class="md:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 z-40">
        <div class="flex">
            <?php
            $btmItems2 = [
                ['href'=>'index.php',        'icon'=>'fa-tachometer-alt','label'=>'Home',     'pages'=>['index.php']],
                ['href'=>'orders.php',       'icon'=>'fa-box-open',      'label'=>'Orders',   'pages'=>['orders.php','view_order.php']],
                ['href'=>'customers.php',    'icon'=>'fa-users',         'label'=>'Customers','pages'=>['customers.php']],
                ['href'=>'landing_pages.php','icon'=>'fa-rocket',        'label'=>'Landing',  'pages'=>['landing_pages.php','manage_landing.php']],
                ['href'=>'settings.php',     'icon'=>'fa-cog',           'label'=>'More',     'pages'=>['settings.php','employees.php','pixel.php','products.php','add_product.php','edit_product.php']],
            ];
            $cp = basename($_SERVER['PHP_SELF']);
            foreach($btmItems2 as $b):
                $active = in_array($cp, $b['pages']) ? 'text-red-500' : 'text-gray-400';
            ?>
            <a href="<?= $b['href'] ?>" class="flex-1 flex flex-col items-center justify-center py-2 gap-0.5 <?= $active ?> active:scale-90 transition-transform">
                <i class="fas <?= $b['icon'] ?> text-lg"></i>
                <span class="text-[10px] font-semibold"><?= $b['label'] ?></span>
            </a>
            <?php endforeach; ?>
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
