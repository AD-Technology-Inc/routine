<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import { BarChart3, TrendingUp, Zap, Calendar } from '@lucide/vue';
import { dashboard } from '@/routes';
import { useAnalyticsStore } from '@/stores/useAnalyticsStore';
import { Skeleton } from '@/components/ui/skeleton';
import { Badge } from '@/components/ui/badge';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Analytics', href: '/analytics' },
        ],
    },
});

const analyticsStore = useAnalyticsStore();

const selectedDays = ref(30);

const avgCompletionRate = computed(() => {
    const snaps = analyticsStore.snapshots.value;
    if (!snaps.length) return 0;
    const sum = snaps.reduce((acc, s) => acc + Number(s.completion_rate), 0);
    return Math.round(sum / snaps.length);
});

const totalCompleted = computed(() =>
    analyticsStore.snapshots.value.reduce((sum, s) => sum + s.total_tasks_completed, 0),
);

const totalScheduled = computed(() =>
    analyticsStore.snapshots.value.reduce((sum, s) => sum + s.total_tasks_scheduled, 0),
);

// Weekday heatmap sorted
const weekdayOrder = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

const heatmapRows = computed(() =>
    weekdayOrder.map((day) => ({
        day,
        rate: analyticsStore.heatmap.value[day] ?? 0,
    })),
);

const maxHeatmapRate = computed(() =>
    Math.max(...heatmapRows.value.map((r) => r.rate), 1),
);

const energyRows = computed(() =>
    Object.entries(analyticsStore.energyPerformance.value).map(([level, rate]) => ({
        level,
        rate: Number(rate),
    })),
);

const heatmapColor = (rate: number, max: number) => {
    const pct = max > 0 ? rate / max : 0;
    if (pct >= 0.8) return 'bg-emerald-500';
    if (pct >= 0.6) return 'bg-emerald-400/80';
    if (pct >= 0.4) return 'bg-amber-400/80';
    if (pct >= 0.2) return 'bg-orange-400/60';
    return 'bg-muted';
};

// Last 14 days for bar chart
const recentSnaps = computed(() => analyticsStore.snapshots.value.slice(-14));

const barMax = computed(() =>
    Math.max(...recentSnaps.value.map((s) => s.total_tasks_scheduled), 1),
);

onMounted(() => analyticsStore.fetchAll(selectedDays.value));
</script>

<template>
    <Head title="Analytics" />

    <div class="flex flex-col gap-6 p-4 md:p-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold tracking-tight">Analytics</h1>
            <div class="flex items-center gap-2">
                <button
                    v-for="days in [7, 14, 30, 90]"
                    :key="days"
                    class="rounded-lg border px-3 py-1 text-sm transition-colors"
                    :class="
                        selectedDays === days
                            ? 'border-primary bg-primary text-primary-foreground'
                            : 'border-border hover:border-border/80 hover:bg-muted'
                    "
                    @click="
                        selectedDays = days;
                        analyticsStore.fetchSummary(days);
                    "
                >
                    {{ days }}d
                </button>
            </div>
        </div>

        <!-- Summary cards -->
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
            <div class="rounded-xl border border-border/60 bg-card p-4">
                <div class="flex items-center gap-2 text-sm text-muted-foreground">
                    <TrendingUp class="h-4 w-4" />
                    Avg. Completion
                </div>
                <div class="mt-1 text-2xl font-bold">
                    <Skeleton v-if="analyticsStore.isLoading.value" class="h-8 w-14" />
                    <template v-else>{{ avgCompletionRate }}%</template>
                </div>
            </div>

            <div class="rounded-xl border border-border/60 bg-card p-4">
                <div class="flex items-center gap-2 text-sm text-muted-foreground">
                    <BarChart3 class="h-4 w-4" />
                    Total Scheduled
                </div>
                <div class="mt-1 text-2xl font-bold">
                    <Skeleton v-if="analyticsStore.isLoading.value" class="h-8 w-14" />
                    <template v-else>{{ totalScheduled }}</template>
                </div>
            </div>

            <div class="rounded-xl border border-border/60 bg-card p-4">
                <div class="flex items-center gap-2 text-sm text-muted-foreground">
                    <Calendar class="h-4 w-4" />
                    Total Completed
                </div>
                <div class="mt-1 text-2xl font-bold text-emerald-500">
                    <Skeleton v-if="analyticsStore.isLoading.value" class="h-8 w-14" />
                    <template v-else>{{ totalCompleted }}</template>
                </div>
            </div>

            <div class="rounded-xl border border-border/60 bg-card p-4">
                <div class="flex items-center gap-2 text-sm text-muted-foreground">
                    <Zap class="h-4 w-4" />
                    Days Tracked
                </div>
                <div class="mt-1 text-2xl font-bold">
                    <Skeleton v-if="analyticsStore.isLoading.value" class="h-8 w-14" />
                    <template v-else>{{ analyticsStore.snapshots.value.length }}</template>
                </div>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <!-- Daily completion bar chart (last 14 days) -->
            <div class="rounded-xl border border-border/60 bg-card p-5">
                <h2 class="mb-4 text-sm font-semibold">Daily Completion (Last 14 days)</h2>

                <template v-if="analyticsStore.isLoading.value">
                    <div class="flex h-40 items-end gap-1">
                        <Skeleton v-for="i in 14" :key="i" class="flex-1 rounded-t" :style="{ height: Math.random() * 80 + 20 + '%' }" />
                    </div>
                </template>

                <div v-else-if="!recentSnaps.length" class="py-10 text-center text-sm text-muted-foreground">
                    No data yet
                </div>

                <div v-else class="flex h-40 items-end gap-1">
                    <div
                        v-for="snap in recentSnaps"
                        :key="snap.id"
                        class="group relative flex flex-1 flex-col items-center"
                    >
                        <!-- Scheduled bar (background) -->
                        <div
                            class="w-full rounded-t bg-muted"
                            :style="{ height: (snap.total_tasks_scheduled / barMax) * 100 + '%' }"
                        />
                        <!-- Completed overlay -->
                        <div
                            class="absolute bottom-0 w-full rounded-t bg-emerald-500/70 transition-all"
                            :style="{ height: (snap.total_tasks_completed / barMax) * 100 + '%' }"
                        />
                        <!-- Tooltip -->
                        <div class="absolute -top-8 hidden rounded bg-popover px-2 py-1 text-xs shadow group-hover:block">
                            {{ snap.date }}: {{ snap.completion_rate }}%
                        </div>
                    </div>
                </div>

                <div class="mt-2 flex justify-between text-xs text-muted-foreground">
                    <span>{{ recentSnaps[0]?.date }}</span>
                    <span>{{ recentSnaps[recentSnaps.length - 1]?.date }}</span>
                </div>
            </div>

            <!-- Weekday Heatmap -->
            <div class="rounded-xl border border-border/60 bg-card p-5">
                <h2 class="mb-4 text-sm font-semibold">Completion by Weekday</h2>

                <template v-if="analyticsStore.isLoading.value">
                    <div class="space-y-2">
                        <Skeleton v-for="i in 7" :key="i" class="h-8 w-full rounded" />
                    </div>
                </template>

                <div v-else class="space-y-2">
                    <div
                        v-for="row in heatmapRows"
                        :key="row.day"
                        class="flex items-center gap-3"
                    >
                        <span class="w-24 shrink-0 text-sm text-muted-foreground">{{ row.day }}</span>
                        <div class="flex-1 overflow-hidden rounded-full bg-muted">
                            <div
                                class="h-5 rounded-full transition-all duration-700"
                                :class="heatmapColor(row.rate, maxHeatmapRate)"
                                :style="{ width: (row.rate / maxHeatmapRate) * 100 + '%' }"
                            />
                        </div>
                        <span class="w-12 shrink-0 text-right text-sm font-medium">
                            {{ row.rate.toFixed(0) }}%
                        </span>
                    </div>
                </div>
            </div>

            <!-- Energy Performance -->
            <div class="rounded-xl border border-border/60 bg-card p-5 lg:col-span-2">
                <h2 class="mb-4 text-sm font-semibold">Completion Rate by Energy Level</h2>

                <template v-if="analyticsStore.isLoading.value">
                    <div class="flex gap-4">
                        <Skeleton v-for="i in 3" :key="i" class="h-24 flex-1 rounded-xl" />
                    </div>
                </template>

                <div v-else-if="!energyRows.length" class="py-6 text-center text-sm text-muted-foreground">
                    No energy data yet
                </div>

                <div v-else class="flex gap-4">
                    <div
                        v-for="row in energyRows"
                        :key="row.level"
                        class="flex flex-1 flex-col items-center rounded-xl border border-border/60 bg-muted/30 p-4"
                    >
                        <span class="text-2xl">
                            {{ row.level === 'high' ? '⚡' : row.level === 'medium' ? '🔥' : '🌙' }}
                        </span>
                        <span class="mt-1 text-lg font-bold">{{ row.rate.toFixed(0) }}%</span>
                        <span class="text-xs capitalize text-muted-foreground">{{ row.level }} energy</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
