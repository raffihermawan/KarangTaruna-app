async function fetchJson(url) {
    const response = await fetch(url);
    if (!response.ok) {
        throw new Error('Gagal memuat data');
    }
    return response.json();
}

document.addEventListener('DOMContentLoaded', async () => {
    const cashflowEl = document.getElementById('chartArusKas');
    const kategoriEl = document.getElementById('chartKategori');

    if (!cashflowEl && !kategoriEl) {
        return;
    }

    try {
        if (cashflowEl) {
            const cashflow = await fetchJson(cashflowEl.dataset.url);
            new Chart(cashflowEl, {
                type: 'bar',
                data: {
                    labels: cashflow.labels,
                    datasets: [
                        {
                            label: 'Pemasukan',
                            data: cashflow.pemasukan,
                            backgroundColor: '#10B981',
                        },
                        {
                            label: 'Pengeluaran',
                            data: cashflow.pengeluaran,
                            backgroundColor: '#EF4444',
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                    },
                },
            });
        }

        if (kategoriEl) {
            const kategori = await fetchJson(kategoriEl.dataset.url);
            new Chart(kategoriEl, {
                type: 'doughnut',
                data: {
                    labels: kategori.labels,
                    datasets: [
                        {
                            data: kategori.values,
                            backgroundColor: ['#1e3a8a', '#0f172a', '#e8a020', '#10b981', '#ef4444'],
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '65%',
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                    },
                },
            });
        }
    } catch (error) {
        console.error(error);
    }
});
