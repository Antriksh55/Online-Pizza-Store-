        </main>
    </div>

    <!-- Mobile Menu Javascript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuButton = document.getElementById('mobile-menu-button');
            const closeMobileMenuButton = document.getElementById('close-mobile-menu');
            const mobileMenu = document.getElementById('mobile-menu');
            
            mobileMenuButton.addEventListener('click', function() {
                mobileMenu.classList.remove('hidden');
            });
            
            closeMobileMenuButton.addEventListener('click', function() {
                mobileMenu.classList.add('hidden');
            });
        });
    </script>
</body>
</html> 