/**
 * Khosamphi Hospital Analytics - Chart.js Initializer & Dynamic Updater
 */

const HospitalCharts = {
    charts: {},

    colors: {
        teal: '#0aa2a3',
        tealLight: 'rgba(10, 162, 163, 0.15)',
        blue: '#0d6efd',
        blueLight: 'rgba(13, 110, 253, 0.15)',
        indigo: '#6610f2',
        purple: '#6f42c1',
        pink: '#d63384',
        red: '#dc3545',
        orange: '#fd7e14',
        yellow: '#ffc107',
        green: '#198754',
        cyan: '#0dcaf0',
        palette: [
            '#0aa2a3', '#0d6efd', '#fd7e14', '#198754', '#6f42c1',
            '#0dcaf0', '#d63384', '#ffc107', '#20c997', '#6c757d'
        ]
    },

    initDailyTrend(canvasId, labels, data) {
        const ctx = document.getElementById(canvasId);
        if (!ctx) return;

        if (this.charts[canvasId]) {
            this.charts[canvasId].destroy();
        }

        this.charts[canvasId] = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'จำนวนครั้งการเข้ารับบริการ (Visits)',
                    data: data,
                    borderColor: this.colors.teal,
                    backgroundColor: this.colors.tealLight,
                    borderWidth: 2.5,
                    fill: true,
                    tension: 0.35,
                    pointBackgroundColor: this.colors.teal,
                    pointRadius: 3,
                    pointHoverRadius: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            label: (ctx) => ` ${ctx.parsed.y.toLocaleString()} ครั้ง`
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0 }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    },

    initDepartmentBar(canvasId, labels, data) {
        const ctx = document.getElementById(canvasId);
        if (!ctx) return;

        if (this.charts[canvasId]) {
            this.charts[canvasId].destroy();
        }

        this.charts[canvasId] = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'จำนวนครั้ง (Visits)',
                    data: data,
                    backgroundColor: this.colors.blue,
                    borderRadius: 6,
                    maxBarThickness: 36,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => ` ${ctx.parsed.y.toLocaleString()} ครั้ง`
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0 }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    },

    initUsersByDepartment(canvasId, labels, data) {
        const ctx = document.getElementById(canvasId);
        if (!ctx) return;

        if (this.charts[canvasId]) {
            this.charts[canvasId].destroy();
        }

        this.charts[canvasId] = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'จำนวนผู้ใช้งาน',
                    data: data,
                    backgroundColor: this.colors.teal,
                    borderRadius: 6,
                    maxBarThickness: 36,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => ` ${ctx.parsed.y.toLocaleString()} บัญชี`
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0 }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    },

    initServiceTypeDoughnut(canvasId, labels, data) {
        const ctx = document.getElementById(canvasId);
        if (!ctx) return;

        if (this.charts[canvasId]) {
            this.charts[canvasId].destroy();
        }

        this.charts[canvasId] = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: this.colors.palette,
                    borderWidth: 2,
                    borderColor: '#ffffff',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            boxWidth: 12,
                            padding: 12,
                            font: { size: 11 }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => ` ${ctx.label}: ${ctx.parsed.toLocaleString()} ครั้ง`
                        }
                    }
                },
                cutout: '65%'
            }
        });
    },

    initMonthlyComparison(canvasId, labels, currentYear, currentData, prevYear, prevData) {
        const ctx = document.getElementById(canvasId);
        if (!ctx) return;

        if (this.charts[canvasId]) {
            this.charts[canvasId].destroy();
        }

        this.charts[canvasId] = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: `ปี พ.ศ. ${currentYear}`,
                        data: currentData,
                        backgroundColor: this.colors.teal,
                        borderRadius: 4,
                    },
                    {
                        label: `ปี พ.ศ. ${prevYear}`,
                        data: prevData,
                        backgroundColor: '#cbd5e1',
                        borderRadius: 4,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top' },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => ` ${ctx.dataset.label}: ${ctx.parsed.y.toLocaleString()} ครั้ง`
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0 }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    },

    initHourlyDistribution(canvasId, labels, data) {
        const ctx = document.getElementById(canvasId);
        if (!ctx) return;

        if (this.charts[canvasId]) {
            this.charts[canvasId].destroy();
        }

        // Color bars based on traffic intensity
        const maxVal = Math.max(...data, 1);
        const backgroundColors = data.map(v => {
            const ratio = v / maxVal;
            if (ratio > 0.75) return '#dc3545'; // Peak congestion (red)
            if (ratio > 0.45) return '#fd7e14'; // Moderate traffic (orange)
            return '#0aa2a3'; // Normal
        });

        this.charts[canvasId] = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'จำนวนครั้ง (Visits)',
                    data: data,
                    backgroundColor: backgroundColors,
                    borderRadius: 4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => ` ${ctx.label} น.: ${ctx.parsed.y.toLocaleString()} ครั้ง`
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0 }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { maxRotation: 45, minRotation: 45 }
                    }
                }
            }
        });
    }
};

window.HospitalCharts = HospitalCharts;
