(function () {
    'use strict';

    const payload = window.DASHBOARD_DATA || {};
    const barCanvas = document.getElementById('sales_purchases_chart');

    if (barCanvas && typeof Chart !== 'undefined') {
        const series = payload.chartSeries || { labels: [], sales: [], purchases: [] };
        new Chart(barCanvas.getContext('2d'), {
            type: 'bar',
            data: {
                labels: series.labels,
                datasets: [
                    {
                        label: 'Sales ($)',
                        backgroundColor: 'rgba(79, 70, 229, 0.85)',
                        borderColor: '#4f46e5',
                        borderWidth: 1,
                        data: series.sales
                    },
                    {
                        label: 'Purchases ($)',
                        backgroundColor: 'rgba(16, 185, 129, 0.78)',
                        borderColor: '#10b981',
                        borderWidth: 1,
                        data: series.purchases
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: {
                    position: 'top'
                },
                tooltips: {
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                        label: function (tooltipItem, data) {
                            const label = data.datasets[tooltipItem.datasetIndex].label || '';
                            const value = Number(tooltipItem.yLabel || 0).toFixed(2);
                            return label + ': $' + value;
                        }
                    }
                },
                scales: {
                    xAxes: [{
                        gridLines: {
                            display: false
                        }
                    }],
                    yAxes: [{
                        ticks: {
                            beginAtZero: true,
                            callback: function (value) {
                                return '$' + Number(value).toLocaleString();
                            }
                        },
                        gridLines: {
                            color: '#f1f5f9'
                        }
                    }]
                }
            }
        });
    }

    const doughnutCanvas = document.getElementById('payment_methods_chart');
    const breakdown = payload.paymentBreakdown || { labels: [], totals: [], colors: [] };

    if (doughnutCanvas && typeof Chart !== 'undefined' && Array.isArray(breakdown.totals) && breakdown.totals.length > 0) {
        new Chart(doughnutCanvas.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: breakdown.labels,
                datasets: [{
                    data: breakdown.totals,
                    backgroundColor: breakdown.colors,
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutoutPercentage: 65,
                legend: {
                    display: false
                },
                tooltips: {
                    callbacks: {
                        label: function (tooltipItem, data) {
                            const label = data.labels[tooltipItem.index] || '';
                            const val = Number(data.datasets[0].data[tooltipItem.index] || 0).toFixed(2);
                            return label + ': $' + val;
                        }
                    }
                }
            }
        });
    }
})();
