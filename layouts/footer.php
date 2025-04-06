    </div><!-- End of main content -->

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-10 mt-12">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Logo and About -->
                <div>
                    <a href="index.php" class="text-xl font-bold text-white flex items-center">
                        <i class="fas fa-pizza-slice mr-2"></i>Pizza Store
                    </a>
                    <p class="mt-4 text-gray-400">
                        Delicious pizza delivered to your doorstep. Fresh, hot, and right on time.
                    </p>
                </div>
                
                <!-- Quick Links -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Quick Links</h3>
                    <ul class="space-y-2">
                        <li><a href="index.php" class="text-gray-400 hover:text-white transition">Home</a></li>
                        <li><a href="menu.php" class="text-gray-400 hover:text-white transition">Menu</a></li>
                        <li><a href="about.php" class="text-gray-400 hover:text-white transition">About Us</a></li>
                        <li><a href="contact.php" class="text-gray-400 hover:text-white transition">Contact</a></li>
                    </ul>
                </div>
                
                <!-- Contact Info -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Contact Us</h3>
                    <ul class="space-y-2">
                        <li class="flex items-center">
                            <i class="fas fa-map-marker-alt w-6"></i>
                            <span class="text-gray-400">123 Pizza Street, Food City</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-phone w-6"></i>
                            <span class="text-gray-400">+1 234 567 8901</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-envelope w-6"></i>
                            <span class="text-gray-400">info@pizzastore.com</span>
                        </li>
                    </ul>
                </div>
                
                <!-- Opening Hours -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Opening Hours</h3>
                    <ul class="space-y-2">
                        <li class="flex justify-between">
                            <span class="text-gray-400">Monday - Friday</span>
                            <span class="text-gray-400">10:00 AM - 10:00 PM</span>
                        </li>
                        <li class="flex justify-between">
                            <span class="text-gray-400">Saturday</span>
                            <span class="text-gray-400">11:00 AM - 11:00 PM</span>
                        </li>
                        <li class="flex justify-between">
                            <span class="text-gray-400">Sunday</span>
                            <span class="text-gray-400">12:00 PM - 9:00 PM</span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="border-t border-gray-700 mt-8 pt-8 flex flex-col md:flex-row justify-between items-center">
                <div class="text-gray-400">
                    &copy; <?php echo date('Y'); ?> Pizza Store. All rights reserved.
                </div>
                <div class="flex space-x-4 mt-4 md:mt-0">
                    <a href="#" class="text-gray-400 hover:text-white transition"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="text-gray-400 hover:text-white transition"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="text-gray-400 hover:text-white transition"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Mobile Menu Javascript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuButton = document.querySelector('button.md\\:hidden');
            const mobileMenu = document.querySelector('div.md\\:hidden');
            
            mobileMenuButton.addEventListener('click', function() {
                if (mobileMenu.classList.contains('hidden')) {
                    mobileMenu.classList.remove('hidden');
                } else {
                    mobileMenu.classList.add('hidden');
                }
            });
        });
    </script>
</body>
</html> 