document.addEventListener('DOMContentLoaded', function () {
    // Sidebar Toggle Logic
    const toggleBtn = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.createElement('div');
    overlay.className = 'overlay';
    document.body.appendChild(overlay);

    if (toggleBtn) {
        toggleBtn.addEventListener('click', function () {
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        });
    }

    // Close sidebar when clicking overlay
    overlay.addEventListener('click', function () {
        sidebar.classList.remove('active');
        overlay.classList.remove('active');
    });

    // Chart.js Initialization (Safeguard check)
    if (typeof Chart !== 'undefined') {
        const salesChartCanvas = document.getElementById('salesChart');
        if (salesChartCanvas) {
            const ctx = salesChartCanvas.getContext('2d');
            // Data would typically be fetched via AJAX, but for this demo we'll use inline JSON or standard config
            // The actual data will be populated in the PHP file via a variable
            if (window.salesChartData) {
                new Chart(ctx, {
                    type: 'line',
                    data: window.salesChartData,
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'top',
                            },
                        }
                    }
                });
            }
        }
    }
});
