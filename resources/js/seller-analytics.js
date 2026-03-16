import Chart from "chart.js/auto";

document.addEventListener("DOMContentLoaded", () => {
    const payload = window.__sellerAnalytics;
    if (!payload) {
        return;
    }

    const brand = String(payload.brand || "#27b105").trim() || "#27b105";
    const currency = String(payload.currency || "USD").trim() || "USD";
    const labels = normalizeSeries(payload.labels);
    const revenue = normalizeSeries(payload.revenue, true);
    const orders = normalizeSeries(payload.orders, true);
    const dowLabels = normalizeSeries(payload.dowLabels);
    const dowSeries = normalizeSeries(payload.dowSeries, true);

    bindRangeForm();
    initTrendPanel({ brand, currency, labels, revenue, orders });
    initDowPanel({ brand, currency, labels: dowLabels, values: dowSeries });
    initSparklines({ brand, labels, revenue, orders });
    initExports();
});

function bindRangeForm() {
    const rangeSelect = document.getElementById("rangeSelect");
    const customRange = document.getElementById("customRange");

    rangeSelect?.addEventListener("change", () => {
        const isCustom = rangeSelect.value === "custom";
        customRange?.classList.toggle("hidden", !isCustom);
        customRange?.classList.toggle("flex", isCustom);

        if (!isCustom) {
            rangeSelect.form?.submit();
        }
    });
}

function initTrendPanel({ brand, currency, labels, revenue, orders }) {
    const messageEl = document.getElementById("chartMessage");
    const chartWrap = document.getElementById("chartCanvasWrap");
    const revenueCanvas = document.getElementById("revenueChart");
    const ordersCanvas = document.getElementById("ordersChart");
    const revCtx = revenueCanvas?.getContext("2d");
    const ordCtx = ordersCanvas?.getContext("2d");

    if (!revCtx || !ordCtx || !labels.length || !hasAnyValue(revenue, orders)) {
        showMessage(
            messageEl,
            chartWrap,
            "No chart data in this range yet.",
            "Revenue tracks paid sales totals per day, and Orders tracks the number of paid orders per day."
        );
        return;
    }

    clearMessage(messageEl, chartWrap);

    const tooltipTitle = (items) => formatLongDate(items?.[0]?.label || "");

    const revenueChart = new Chart(revCtx, {
        type: "line",
        data: {
            labels,
            datasets: [
                {
                    label: "Revenue",
                    data: revenue,
                    borderColor: brand,
                    backgroundColor: createGradient(revCtx, brand, 0.28, 0.03),
                    fill: true,
                    borderWidth: 3,
                    tension: 0.35,
                    pointRadius: labels.length > 45 ? 0 : 3,
                    pointHoverRadius: 4,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: "index", intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        title: tooltipTitle,
                        label: (context) => formatCurrency(context.raw, currency),
                    },
                },
            },
            scales: {
                x: buildXAxis(labels),
                y: buildYAxis((value) => formatCompactCurrency(value, currency)),
            },
        },
    });

    const ordersChart = new Chart(ordCtx, {
        type: "bar",
        data: {
            labels,
            datasets: [
                {
                    label: "Orders",
                    data: orders,
                    backgroundColor: createGradient(ordCtx, brand, 0.7, 0.15),
                    borderColor: brand,
                    borderRadius: 999,
                    borderSkipped: false,
                    maxBarThickness: 18,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: "index", intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        title: tooltipTitle,
                        label: (context) => `${Number(context.raw || 0).toLocaleString()} orders`,
                    },
                },
            },
            scales: {
                x: buildXAxis(labels),
                y: buildYAxis((value) => Number(value || 0).toLocaleString()),
            },
        },
    });

    document.querySelectorAll("#chartToggle button").forEach((button) => {
        button.addEventListener("click", (event) => {
            document
                .querySelectorAll("#chartToggle button")
                .forEach((item) => item.classList.remove("active"));
            event.currentTarget.classList.add("active");

            const target = event.currentTarget.dataset.target;
            revenueCanvas.classList.toggle("hidden", target !== "revenue");
            ordersCanvas.classList.toggle("hidden", target !== "orders");

            requestAnimationFrame(() => {
                revenueChart.resize();
                ordersChart.resize();
            });
        });
    });
}

function initDowPanel({ brand, currency, labels, values }) {
    const messageEl = document.getElementById("dowMessage");
    const chartWrap = document.getElementById("dowChartWrap");
    const ctx = document.getElementById("dowChart")?.getContext("2d");

    if (!ctx || !labels.length || !values.some((value) => value > 0)) {
        showMessage(
            messageEl,
            chartWrap,
            "No weekday sales pattern yet.",
            "Once paid orders land in the selected range, this chart will show which days bring the most revenue."
        );
        return;
    }

    clearMessage(messageEl, chartWrap);

    new Chart(ctx, {
        type: "bar",
        data: {
            labels,
            datasets: [
                {
                    data: values,
                    backgroundColor: createGradient(ctx, brand, 0.72, 0.18),
                    borderColor: brand,
                    borderWidth: 1,
                    borderRadius: 16,
                    borderSkipped: false,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (context) => formatCurrency(context.raw, currency),
                    },
                },
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: {
                        color: "#64748b",
                        font: { size: 12, weight: "600" },
                    },
                },
                y: buildYAxis((value) => formatCompactCurrency(value, currency)),
            },
        },
    });
}

function initSparklines({ brand, labels, revenue, orders }) {
    const aovSeries = labels.map((_, index) => {
        const orderCount = Number(orders[index] || 0);
        const revenueValue = Number(revenue[index] || 0);
        return orderCount > 0 ? revenueValue / orderCount : 0;
    });

    const lastN = (series, count) => series.slice(Math.max(0, series.length - count));

    createSpark("sparkRevenue", lastN(revenue, 30), brand);
    createSpark("sparkOrders", lastN(orders, 30), brand);
    createSpark("sparkAov", lastN(aovSeries, 30), brand);
}

function createSpark(id, data, color) {
    const ctx = document.getElementById(id)?.getContext("2d");
    if (!ctx || !data.length) {
        return;
    }

    new Chart(ctx, {
        type: "line",
        data: {
            labels: data.map((_, index) => index + 1),
            datasets: [
                {
                    data,
                    borderColor: color,
                    backgroundColor: "transparent",
                    pointRadius: 0,
                    borderWidth: 2,
                    tension: 0.35,
                    fill: false,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { enabled: false },
            },
            scales: {
                x: { display: false },
                y: { display: false },
            },
        },
    });
}

function initExports() {
    document
        .getElementById("exportTopCsv")
        ?.addEventListener("click", () => tableToCSV("topProductsTable", "top-products.csv"));
    document
        .getElementById("exportPerfCsv")
        ?.addEventListener("click", () => tableToCSV("performanceTable", "listing-performance.csv"));
}

function tableToCSV(tableId, filename) {
    const rows = Array.from(document.querySelectorAll(`#${tableId} tr`));
    const csv = rows
        .map((row) =>
            Array.from(row.querySelectorAll("th,td"))
                .map((cell) => {
                    const text = cell.innerText.replace(/\s+/g, " ").trim();
                    return `"${text.replace(/"/g, "\"\"")}"`;
                })
                .join(",")
        )
        .join("\n");

    const blob = new Blob([csv], { type: "text/csv;charset=utf-8;" });
    const link = document.createElement("a");
    link.href = URL.createObjectURL(blob);
    link.download = filename;
    link.click();
    URL.revokeObjectURL(link.href);
}

function buildXAxis(labels) {
    const maxTicksLimit = labels.length > 90 ? 6 : labels.length > 45 ? 8 : 10;

    return {
        grid: { display: false },
        ticks: {
            autoSkip: true,
            maxTicksLimit,
            maxRotation: 0,
            color: "#64748b",
            font: { size: 12, weight: "600" },
            callback: (_, index) => formatShortDate(labels[index], labels.length),
        },
    };
}

function buildYAxis(labelFormatter) {
    return {
        beginAtZero: true,
        grid: {
            color: "rgba(148, 163, 184, 0.18)",
            drawBorder: false,
        },
        ticks: {
            color: "#64748b",
            font: { size: 12, weight: "600" },
            callback: (value) => labelFormatter(value),
        },
    };
}

function createGradient(ctx, color, startAlpha, endAlpha) {
    const gradient = ctx.createLinearGradient(0, 0, 0, 260);
    gradient.addColorStop(0, hexToRgba(color, startAlpha));
    gradient.addColorStop(1, hexToRgba(color, endAlpha));
    return gradient;
}

function hexToRgba(color, alpha) {
    const normalized = String(color || "").trim();
    const hex = normalized.startsWith("#") ? normalized.slice(1) : normalized;

    if (hex.length !== 3 && hex.length !== 6) {
        return `rgba(39, 177, 5, ${alpha})`;
    }

    const fullHex =
        hex.length === 3
            ? hex
                  .split("")
                  .map((char) => `${char}${char}`)
                  .join("")
            : hex;

    const red = Number.parseInt(fullHex.slice(0, 2), 16);
    const green = Number.parseInt(fullHex.slice(2, 4), 16);
    const blue = Number.parseInt(fullHex.slice(4, 6), 16);

    return `rgba(${red}, ${green}, ${blue}, ${alpha})`;
}

function formatShortDate(label, totalPoints) {
    const date = parseDateLabel(label);
    if (!date) {
        return label;
    }

    const options =
        totalPoints > 90
            ? { month: "short", year: "2-digit" }
            : { month: "short", day: "numeric" };

    return new Intl.DateTimeFormat(undefined, options).format(date);
}

function formatLongDate(label) {
    const date = parseDateLabel(label);
    if (!date) {
        return label;
    }

    return new Intl.DateTimeFormat(undefined, {
        month: "short",
        day: "numeric",
        year: "numeric",
    }).format(date);
}

function parseDateLabel(label) {
    const date = new Date(`${label}T00:00:00`);
    return Number.isNaN(date.getTime()) ? null : date;
}

function formatCompactCurrency(value, currency) {
    return `${currency} ${abbreviateNumber(value)}`;
}

function formatCurrency(value, currency) {
    const amount = Number(value || 0);

    try {
        return new Intl.NumberFormat(undefined, {
            style: "currency",
            currency,
            maximumFractionDigits: 2,
        }).format(amount);
    } catch (_) {
        return `${currency} ${amount.toLocaleString(undefined, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        })}`;
    }
}

function abbreviateNumber(value) {
    const amount = Number(value || 0);
    const absolute = Math.abs(amount);

    if (absolute >= 1000000) {
        return `${(amount / 1000000).toFixed(1).replace(/\.0$/, "")}M`;
    }

    if (absolute >= 1000) {
        return `${(amount / 1000).toFixed(1).replace(/\.0$/, "")}K`;
    }

    return amount.toLocaleString();
}

function normalizeSeries(values, numeric = false) {
    if (!Array.isArray(values)) {
        return [];
    }

    return values.map((value) => (numeric ? Number(value || 0) : String(value)));
}

function hasAnyValue(...series) {
    return series.some((items) => items.some((value) => Number(value || 0) > 0));
}

function showMessage(messageEl, chartWrap, title, body) {
    if (!messageEl || !chartWrap) {
        return;
    }

    messageEl.innerHTML = `<p>${title}</p><p>${body}</p>`;
    messageEl.classList.remove("hidden");
    chartWrap.classList.add("hidden");
}

function clearMessage(messageEl, chartWrap) {
    if (!messageEl || !chartWrap) {
        return;
    }

    messageEl.classList.add("hidden");
    chartWrap.classList.remove("hidden");
}
