const section = document.querySelector('[data-dashboard-chart]');
const plot = section.querySelector('[data-chart-plot]');
const canvas = plot.querySelector('canvas');
const days = [...plot.querySelectorAll('[data-chart-day]')];
const controls = section.querySelector('[data-chart-controls]');
const seriesButtons = [...controls.querySelectorAll('button[data-series]')];
const chartArea = section.querySelector('[data-chart-area]');
const table = section.querySelector('details');
const empty = section.querySelector('[data-chart-empty]');
const fixedSeries = plot.dataset.fixedSeries;
const scale = Number(plot.dataset.scale);
const style = getComputedStyle(section);

function drawLine(activeSeries) {
    const { width, height } = canvas.getBoundingClientRect();
    const context = canvas.getContext('2d');
    if (!context || width === 0 || height === 0) {
        return;
    }

    const pixels = window.devicePixelRatio || 1;
    canvas.width = Math.round(width * pixels);
    canvas.height = Math.round(height * pixels);
    context.scale(pixels, pixels);
    context.lineWidth = 2;
    context.lineJoin = 'round';

    const padding = 4;
    for (const series of activeSeries) {
        const color = style.getPropertyValue('--dashboard-' + series).trim();
        const coordinates = days.map((day) => [
            day.offsetLeft + day.offsetWidth / 2,
            padding + (1 - Number(day.dataset[series]) / scale) * (height - 2 * padding),
        ]);

        const projection = days.at(-1).hasAttribute('data-projection') ? coordinates.pop() : null;
        const projectionColor = style.getPropertyValue('--dashboard-expenses').trim();
        context.strokeStyle = color;
        context.fillStyle = color;
        context.beginPath();
        context.moveTo(...coordinates[0]);
        for (const point of coordinates.slice(1)) {
            context.lineTo(...point);
        }
        context.stroke();

        if (projection !== null) {
            context.save();
            context.setLineDash([5, 4]);
            context.strokeStyle = projectionColor;
            context.beginPath();
            context.moveTo(...coordinates.at(-1));
            context.lineTo(...projection);
            context.stroke();
            context.restore();
            coordinates.push(projection);
        }

        for (const point of coordinates) {
            const [x, y] = point;
            context.fillStyle = point === projection ? projectionColor : color;
            context.beginPath();
            context.arc(x, y, 3, 0, 2 * Math.PI);
            context.fill();
        }
    }
}

function render() {
    const type = controls.querySelector('input:checked').value;
    const active = fixedSeries ? [fixedSeries] : seriesButtons
        .filter((button) => button.getAttribute('aria-pressed') === 'true')
        .map((button) => button.dataset.series);

    for (const item of section.querySelectorAll('[data-chart-series]')) {
        item.hidden = !active.includes(item.dataset.chartSeries);
    }

    plot.dataset.type = type;
    chartArea.hidden = active.length === 0;
    table.hidden = active.length === 0;
    if (empty) {
        empty.hidden = active.length !== 0;
    }
    canvas.hidden = type !== 'line';

    if (type === 'line' && active.length > 0) {
        drawLine(active);
    }
}

for (const button of seriesButtons) {
    button.addEventListener('click', () => {
        const isOn = button.getAttribute('aria-pressed') === 'true';
        button.setAttribute('aria-pressed', String(!isOn));
        render();
    });
}

controls.addEventListener('change', render);
new ResizeObserver(render).observe(plot);
controls.hidden = false;
section.querySelector('[data-chart-legend]').hidden = seriesButtons.length > 0;
render();
