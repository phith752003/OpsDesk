<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-xl font-bold tracking-tight text-text-primary">
          Service Desk Analytics &amp; Reports
        </h1>
        <p class="text-xs text-text-muted mt-0.5 font-mono">
          Empirical metrics derived directly from production database transactions
        </p>
      </div>
      <div class="text-xs text-text-muted font-mono bg-bg-surface px-3 py-1.5 rounded border border-border-default">
        Reporting Period: <span class="text-text-primary">Last 7 Days</span>
      </div>
    </div>

    <!-- Analytics Charts Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
      <!-- 7-Day Ticket Flow Trend (8 cols) -->
      <div class="lg:col-span-8 bg-bg-surface border border-border-default rounded-lg p-5">
        <h2 class="text-sm font-semibold text-text-primary uppercase tracking-wider mb-2">
          Weekly Incident Inflow vs. Resolution Velocity
        </h2>
        <div class="text-xs text-text-muted font-mono mb-4">
          Daily volume of incoming requests compared to resolved tickets
        </div>

        <div v-if="dashboardStore.isLoading" class="h-64 flex items-center justify-center text-xs text-text-muted font-mono">
          Loading analytics...
        </div>
        <div v-else class="h-64">
          <v-chart class="w-full h-full" :option="volumeChartOption" autoresize />
        </div>
      </div>

      <!-- Incidents by Category (4 cols) -->
      <div class="lg:col-span-4 bg-bg-surface border border-border-default rounded-lg p-5">
        <h2 class="text-sm font-semibold text-text-primary uppercase tracking-wider mb-2">
          Domain Breakdown
        </h2>
        <div class="text-xs text-text-muted font-mono mb-4">
          Incident volume distribution across departments
        </div>

        <div v-if="dashboardStore.isLoading" class="h-64 flex items-center justify-center text-xs text-text-muted font-mono">
          Loading analytics...
        </div>
        <div v-else class="h-64">
          <v-chart class="w-full h-full" :option="categoryChartOption" autoresize />
        </div>
      </div>

      <!-- SLA Compliance by Priority Level (12 cols) -->
      <div class="lg:col-span-12 bg-bg-surface border border-border-default rounded-lg p-5">
        <h2 class="text-sm font-semibold text-text-primary uppercase tracking-wider mb-2">
          SLA Compliance Rates by Incident Severity
        </h2>
        <div class="text-xs text-text-muted font-mono mb-4">
          Percentage of tickets resolved within contract targets per priority tier
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 pt-2">
          <div
            v-for="sla in dashboardStore.trends?.sla_by_priority || []"
            :key="sla.priority"
            class="p-4 rounded bg-bg-surface-raised border border-border-subtle"
          >
            <div class="text-xs font-mono text-text-muted uppercase">{{ sla.priority }} Priority</div>
            <div class="mt-2 text-2xl font-bold font-mono tabular-nums" :class="sla.compliance_rate >= 95 ? 'text-emerald-400' : 'text-amber-400'">
              {{ sla.compliance_rate }}%
            </div>
            <div class="mt-1 text-[11px] text-text-muted">
              {{ sla.total_resolved }} tickets closed within threshold
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { useDashboardStore } from '@/stores/dashboard'
import VChart from 'vue-echarts'
import { use } from 'echarts/core'
import { CanvasRenderer } from 'echarts/renderers'
import { BarChart, PieChart, LineChart } from 'echarts/charts'
import {
  GridComponent,
  TooltipComponent,
  LegendComponent,
  TitleComponent,
} from 'echarts/components'

use([
  CanvasRenderer,
  BarChart,
  PieChart,
  LineChart,
  GridComponent,
  TooltipComponent,
  LegendComponent,
  TitleComponent,
])

const dashboardStore = useDashboardStore()

const volumeChartOption = computed(() => {
  const trend = dashboardStore.trends?.volume_trend || []
  return {
    backgroundColor: 'transparent',
    tooltip: {
      trigger: 'axis',
      backgroundColor: '#1F2937',
      borderColor: '#374151',
      textStyle: { color: '#F9FAFB', fontSize: 12 },
    },
    legend: {
      data: ['Created', 'Resolved'],
      textStyle: { color: '#9CA3AF' },
      bottom: 0,
    },
    grid: {
      left: '3%',
      right: '4%',
      bottom: '12%',
      top: '5%',
      containLabel: true,
    },
    xAxis: {
      type: 'category',
      data: trend.map((t) => t.date),
      axisLine: { lineStyle: { color: '#374151' } },
      axisLabel: { color: '#9CA3AF', fontSize: 11 },
    },
    yAxis: {
      type: 'value',
      splitLine: { lineStyle: { color: '#1F2937' } },
      axisLabel: { color: '#9CA3AF', fontSize: 11 },
    },
    series: [
      {
        name: 'Created',
        type: 'bar',
        data: trend.map((t) => t.created),
        itemStyle: { color: '#3B82F6', borderRadius: [3, 3, 0, 0] },
      },
      {
        name: 'Resolved',
        type: 'line',
        data: trend.map((t) => t.resolved),
        itemStyle: { color: '#10B981' },
        lineStyle: { width: 3 },
      },
    ],
  }
})

const categoryChartOption = computed(() => {
  const cats = dashboardStore.trends?.category_breakdown || []
  return {
    backgroundColor: 'transparent',
    tooltip: {
      trigger: 'item',
      backgroundColor: '#1F2937',
      borderColor: '#374151',
      textStyle: { color: '#F9FAFB', fontSize: 12 },
    },
    series: [
      {
        name: 'Incidents by Category',
        type: 'pie',
        radius: ['45%', '70%'],
        avoidLabelOverlap: false,
        label: { show: false },
        data: cats.map((c, idx) => ({
          name: c.name,
          value: c.count,
          itemStyle: {
            color: ['#3B82F6', '#6366F1', '#10B981', '#F59E0B', '#EC4899', '#8B5CF6'][idx % 6],
          },
        })),
      },
    ],
  }
})

onMounted(async () => {
  await dashboardStore.fetchTrends()
})
</script>
