<template>
  <div class="bg-white rounded-xl shadow p-6">
    <div class="flex justify-between items-center mb-4">
      <h2 class="text-lg font-semibold">Vendas ao Longo do Tempo</h2>

      <select
        v-model="groupBy"
        @change="loadData"
        class="border rounded px-3 py-1 text-sm"
      >
        <option value="daily">Diário</option>
        <option value="weekly">Semanal</option>
        <option value="monthly">Mensal</option>
      </select>
    </div>

    <canvas ref="chartEl" height="120"></canvas>
  </div>
</template>

<script>
export default {
  data() {
    return {
      chart: null,
      groupBy: 'daily',
      loading: false
    }
  },
  mounted() {
    this.loadData()
  },
  methods: {
    async loadData() {
      this.loading = true

      const url = `http://127.0.0.1:8000/metrics/sales-over-time?group_by=${this.groupBy}`

      const res = await fetch(url)
      const json = await res.json()

      const chartData = json.chart_data

      if (this.chart) {
        this.chart.destroy()
      }

      const ctx = this.$refs.chartEl.getContext('2d')

      this.chart = new Chart(ctx, {
        type: 'line',
        data: {
          labels: chartData.labels,
          datasets: chartData.datasets
        },
        options: {
          responsive: true,
          interaction: {
            mode: 'index',
            intersect: false
          },
          scales: {
            y: {
              type: 'linear',
              position: 'left',
              ticks: {
                callback: (value) => 'R$ ' + value.toLocaleString()
              }
            },
            y1: {
              type: 'linear',
              position: 'right',
              grid: {
                drawOnChartArea: false
              }
            }
          }
        }
      })

      this.loading = false
    }
  }
}
</script>
