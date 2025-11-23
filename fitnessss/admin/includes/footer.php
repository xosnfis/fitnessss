    </main>
    <footer class="text-white text-center py-4" style="background: var(--dark-color); margin-top: auto; flex-shrink: 0;">
        <div class="container">
            <p class="mb-0">
                &copy; <?php echo date('Y'); ?> Админ-панель Фитнес-центра. Все права защищены.
            </p>
            <p class="mb-0 mt-2">
                <small>Вы вошли как: <strong><?php echo htmlspecialchars($_SESSION['username'] ?? 'Администратор'); ?></strong></small>
            </p>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
</body>
</html>

