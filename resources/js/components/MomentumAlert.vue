<script setup lang="ts">
import { computed } from 'vue';
import { Sparkles, ArrowRight, Zap, RefreshCw, Layers } from '@lucide/vue';
import type { Adaptation } from '@/stores/useAnalyticsStore';

const props = defineProps<{
    adaptations: Adaptation[];
}>();

const emit = defineEmits<{
    action: [action: string];
}>();

const iconMap = {
    load_reduction: RefreshCw,
    energy_shift: Zap,
    task_split: Layers,
};

const getIcon = (type: string) => {
    return iconMap[type as keyof typeof iconMap] || Sparkles;
};

const getTitle = (type: string) => {
    switch (type) {
        case 'load_reduction':
            return 'Load Reduction Suggestion';
        case 'energy_shift':
            return 'Energy Alignment Adaptation';
        case 'task_split':
            return 'Task Splitting Recommendation';
        default:
            return 'Momentum Adaptation';
    }
};
</script>

<template>
    <div v-if="adaptations.length" class="space-y-3">
        <div
            v-for="(adaptation, idx) in adaptations"
            :key="idx"
            class="relative flex flex-col sm:flex-row sm:items-center justify-between gap-4 rounded-xl border border-primary/20 bg-primary/5 p-4 shadow-sm"
        >
            <div class="flex items-start gap-3">
                <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary">
                    <component :is="getIcon(adaptation.type)" class="h-4 w-4" />
                </div>
                <div>
                    <h4 class="text-sm font-semibold text-foreground">
                        {{ getTitle(adaptation.type) }}
                    </h4>
                    <p class="text-xs text-muted-foreground mt-0.5">
                        {{ adaptation.message }}
                    </p>
                </div>
            </div>

            <button
                class="inline-flex h-9 items-center justify-center rounded-md border border-input bg-background px-3 text-xs font-medium shadow-sm transition-colors hover:bg-accent hover:text-accent-foreground sm:shrink-0"
                @click="emit('action', adaptation.action)"
            >
                Apply suggestion
                <ArrowRight class="ml-1.5 h-3.5 w-3.5" />
            </button>
        </div>
    </div>
</template>
