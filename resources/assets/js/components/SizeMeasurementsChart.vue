<template>
    <canvas :width="ratio.x" :height="ratio.y"></canvas>
</template>

<script>
import Chart from 'chart.js';

export default {
  props: {
    labels: {
      type: Array,
      required: true,
    },
    values: {
      type: Array,
      required: true,
    },
    colorRGB: {
      default() {
        return {
          r: 52,
          g: 144,
          b: 220,
        };
      },
    },
    showGridLines: {
      type: Object,
      default() {
        return {
          x: true,
          y: true,
        };
      },
    },
    showAxisLabels: {
      type: Object,
      default() {
        return {
          x: true,
          y: true,
        };
      },
    },
    ratio: {
      type: Object,
      default() {
        return {
          x: 16,
          y: 9,
        };
      },
    },
    showLegend: {
      type: Boolean,
      default: false,
    },
    padding: {
      type: Object,
      default() {
        return {
          t: 25,
          r: 10,
          b: 10,
          l: 10,
        };
      },
    },
    showPoints: {
      type: Boolean,
      default: true,
    },
    lineThickness: {
      type: Number,
      default: 1,
    },
  },
  methods: {
    roundFunction(value, divider) {
      return Math.round((value / divider) * 10) / 10;
    },
    getPrettyDataSizeString(value) {
      if (value > 1024 * 1024 * 1024) {
        return this.roundFunction(value, 1024 * 1024 * 1024) + ' TiB';
      }
      if (value > 1024 * 1024) {
        return this.roundFunction(value, 1024 * 1024) + ' GiB';
      }
      if (value > 1024) {
        return this.roundFunction(value, 1024) + ' MiB';
      }
      return value + ' KiB';
    },
  },
  mounted() {
    let ctx = this.$el.getContext('2d');
    new Chart(ctx, {
      type: 'line',
      data: {
        labels: this.labels,
        datasets: [
          {
            data: this.values,
            backgroundColor:
              'rgba(' +
              this.colorRGB.r +
              ',' +
              this.colorRGB.g +
              ',' +
              this.colorRGB.b +
              ',0.3)',
            borderColor:
              'rgba(' +
              this.colorRGB.r +
              ',' +
              this.colorRGB.g +
              ',' +
              this.colorRGB.b +
              ',1)',
            pointBackgroundColor:
              'rgba(' +
              this.colorRGB.r +
              ',' +
              this.colorRGB.g +
              ',' +
              this.colorRGB.b +
              ',0.3)',
            pointBorderColor:
              'rgba(' +
              this.colorRGB.r +
              ',' +
              this.colorRGB.g +
              ',' +
              this.colorRGB.b +
              ',1)',
            pointHoverBackgroundColor:
              'rgba(' +
              this.colorRGB.r +
              ',' +
              this.colorRGB.g +
              ',' +
              this.colorRGB.b +
              ',1)',
            pointHoverBorderColor:
              'rgba(' +
              this.colorRGB.r +
              ',' +
              this.colorRGB.g +
              ',' +
              this.colorRGB.b +
              ',1)',
            cubicInterpolationMode: 'monotone',
            pointRadius: this.showPoints ? 4 : 0,
            borderWidth: this.lineThickness,
            pointHoverRadius: this.showPoints ? 4 : 0,
          },
        ],
      },
      options: {
        layout: {
          padding: {
            left: this.padding.l,
            right: this.padding.r,
            top: this.padding.t,
            bottom: this.padding.b,
          },
        },
        legend: {
          display: this.showLegend,
        },
        scales: {
          xAxes: [
            {
              type: 'time',
              ticks: {
                padding: this.showAxisLabels.x ? 4 : 0,
                display: this.showAxisLabels.x,
              },
              gridLines: {
                display: this.showGridLines.x,
                color: '#f1f5f8',
                drawBorder: this.showAxisLabels.x,
              },
            },
          ],
          yAxes: [
            {
              ticks: {
                callback: (value, index, values) => {
                  return this.getPrettyDataSizeString(value);
                },
                padding: this.showAxisLabels.y ? 4 : 0,
                display: this.showAxisLabels.y,
              },
              gridLines: {
                display: this.showGridLines.y,
                color: '#f1f5f8',
                drawBorder: this.showAxisLabels.y,
              },
            },
          ],
        },
        tooltips: {
          enabled: this.showPoints,
          callbacks: {
            label: (tooltipItem, data) => {
              return this.getPrettyDataSizeString(tooltipItem.yLabel);
            },
          },
        },
      },
    });
  },
};
</script>
