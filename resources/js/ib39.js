import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);


export function initIb39Dashboard() {
    const el = document.getElementById('ib39Dashboard');
    if (!el) return;
    const data = JSON.parse(el.dataset.status || '{}');
    const labels = ['Konsolidado', 'Rekonsilida', 'Expansion', 'Recovery'];
    const colors = ['#ef4444', '#fb923c', '#facc15', '#22c55e'];
    const ctx = document.getElementById('statusChart');
    if (!ctx) return;
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels,
            datasets: [{ data: labels.map((l) => data[l] || 0), backgroundColor: colors, borderWidth: 0 }],
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } },
    });
}
