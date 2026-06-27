<script setup lang="ts">
import { computed } from 'vue';
import { Skeleton } from '@/components/ui/skeleton';

const props = defineProps<{
    data: Record<string, number>;
    isLoading: boolean;
}>();

const weekdayOrder = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

const heatmapRows = computed(() =>
    weekdayOrder.map((day) => ({
        day,
        rate: props.data[day] ?? 0,
    })),
);

const maxRate = computed(() =>
    Math.max(...heatmapRows.value.map((r) => r.rate), 1),
);

const heatmapColor = (rate: number, max: number) => {
    const pct = max > 0 ? rate / max : 0;
    if (pct >= 0.8) return 'bg-emerald-500';
    if (pct >= 0.6) return 'bg-emerald-400/80';
    if (pct >= 0.4) return 'bg-amber-400/80';
    if (pct >= 0.2) return 'bg-orange-400/60';
    return 'bg-muted';
};
</script>

<template>
    <div class="rounded-xl border border-border/60 bg-card p-5">
        <h2 class="mb-4 text-sm font-semibold">Completion by Weekday</h2>

        <template v-if="isLoading">
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
                        :class="heatmapColor(row.rate, maxRate)"
                        :style="{ width: (row.rate / maxRate) * 100 + '%' }"
                    />
                </div>
                <span class="w-12 shrink-0 text-right text-sm font-medium">
                    {{ row.rate.toFixed(0) }}%
                </span>
            </div>
        </div>
    </div>
</template>
