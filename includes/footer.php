    <!-- Bootstrap JS Bundle with Popper -->
    <script src="<?= APP_URL ?>/assets/js/bootstrap.bundle.min.js"></script>
    
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Custom JS -->
    <script src="<?= APP_URL ?>/assets/js/script.js"></script>
    
    <script>
        // Inisialisasi DataTable
        $(document).ready(function() {
            $('.table').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/Indonesian.json"
                }
            });
            
            // Inisialisasi chart
            <?php if (isset($chart_data)): ?>
            const ctx = document.getElementById('paymentChart').getContext('2d');
            const paymentChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?= json_encode($chart_data['labels']) ?>,
                    datasets: [{
                        label: 'Jumlah Pembayaran',
                        data: <?= json_encode($chart_data['data']) ?>,
                        backgroundColor: 'rgba(52, 152, 219, 0.7)',
                        borderColor: 'rgba(52, 152, 219, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
            <?php endif; ?>
        });
    </script>
</body>
</html>