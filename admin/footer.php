<?php
/**
 * Admin Dashboard Footer Template
 * Azumi Movie Reviews - Sidebar Layout
 */
?>
            <footer class="admin-footer">
                <div class="admin-footer-inner">
                    <p>&copy; <?= date('Y') ?> Azumi Movie Reviews &bull; Editorial Content Management Panel</p>
                </div>
            </footer>
        </div> <!-- /.admin-main -->
    </div> <!-- /.admin-layout -->

    <!-- Sidebar Mobile Toggle & Interactive Logic -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggleBtn = document.getElementById('sidebarToggleBtn');
        const closeBtn = document.getElementById('sidebarCloseBtn');
        const backdrop = document.getElementById('sidebarBackdrop');
        const sidebar = document.getElementById('adminSidebar');

        function openNav() {
            if (sidebar) sidebar.classList.add('active');
            if (backdrop) backdrop.classList.add('active');
            document.body.classList.add('sidebar-open');
        }

        function closeNav() {
            if (sidebar) sidebar.classList.remove('active');
            if (backdrop) backdrop.classList.remove('active');
            document.body.classList.remove('sidebar-open');
        }

        if (toggleBtn) toggleBtn.addEventListener('click', openNav);
        if (closeBtn) closeBtn.addEventListener('click', closeNav);
        if (backdrop) backdrop.addEventListener('click', closeNav);
    });
    </script>
</body>
</html>
