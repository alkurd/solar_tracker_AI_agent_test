import Chart from 'chart.js/auto';
import zoomPlugin from 'chartjs-plugin-zoom';
import 'hammerjs';

Chart.register(zoomPlugin);

const canvas = document.querySelector('#yield-chart');

if (canvas instanceof HTMLCanvasElement) {
    const points = JSON.parse(canvas.dataset.points ?? '[]');
    const latestTimestamp = points.at(-1)?.x ?? Date.now();
    const sevenDays = 7 * 24 * 60 * 60 * 1000;
    const dateFormatter = new Intl.DateTimeFormat('nl-NL', {
        day: '2-digit',
        month: 'short',
        hour: '2-digit',
        minute: '2-digit',
    });

    const chart = new Chart(canvas, {
        type: 'line',
        data: {
            datasets: [{
                label: 'Opbrengst',
                data: points,
                parsing: false,
                borderColor: '#176b4a',
                backgroundColor: 'rgba(23, 107, 74, 0.12)',
                borderWidth: 2,
                fill: true,
                tension: 0.22,
                pointRadius: 0,
                pointHoverRadius: 4,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: { duration: 250 },
            interaction: { mode: 'index', intersect: false },
            scales: {
                x: {
                    type: 'linear',
                    min: latestTimestamp - sevenDays,
                    max: latestTimestamp,
                    grid: { color: 'rgba(32, 41, 35, 0.07)' },
                    ticks: {
                        maxTicksLimit: 8,
                        callback: (value) => dateFormatter.format(new Date(Number(value))),
                    },
                },
                y: {
                    beginAtZero: true,
                    title: { display: true, text: 'Opbrengst' },
                    grid: { color: 'rgba(32, 41, 35, 0.08)' },
                },
            },
            plugins: {
                legend: { display: true, position: 'top', align: 'end' },
                tooltip: {
                    callbacks: {
                        title: (items) => dateFormatter.format(new Date(items[0].parsed.x)),
                        label: (item) => `${item.dataset.label}: ${new Intl.NumberFormat('nl-NL', { maximumFractionDigits: 3 }).format(item.parsed.y)}`,
                    },
                },
                zoom: {
                    pan: { enabled: true, mode: 'x', modifierKey: 'shift' },
                    zoom: {
                        mode: 'x',
                        wheel: { enabled: true, modifierKey: 'ctrl' },
                        pinch: { enabled: true },
                        drag: {
                            enabled: true,
                            borderColor: '#176b4a',
                            borderWidth: 1,
                            backgroundColor: 'rgba(23, 107, 74, 0.12)',
                        },
                    },
                },
            },
        },
    });

    document.querySelectorAll('[data-chart-range]').forEach((button) => {
        button.addEventListener('click', () => {
            const range = button.dataset.chartRange;
            const min = range === 'all'
                ? points[0].x
                : latestTimestamp - (Number(range) * 24 * 60 * 60 * 1000);

            chart.zoomScale('x', { min, max: latestTimestamp });
            document.querySelectorAll('[data-chart-range]').forEach((rangeButton) => {
                rangeButton.setAttribute('aria-pressed', String(rangeButton === button));
            });
        });
    });

    document.querySelectorAll('[data-chart-zoom]').forEach((button) => {
        button.addEventListener('click', () => {
            if (button.dataset.chartZoom === 'reset') {
                chart.zoomScale('x', { min: latestTimestamp - sevenDays, max: latestTimestamp });
                document.querySelector('[data-chart-range="7"]')?.setAttribute('aria-pressed', 'true');
                document.querySelectorAll('[data-chart-range]:not([data-chart-range="7"])').forEach((rangeButton) => {
                    rangeButton.setAttribute('aria-pressed', 'false');
                });
                return;
            }

            chart.zoom({ x: button.dataset.chartZoom === 'in' ? 1.4 : 0.7 });
        });
    });
}