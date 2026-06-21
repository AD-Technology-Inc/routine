<script setup lang="ts">
import { computed } from 'vue';
import { CheckCircle2, Circle, SkipForward, Clock, Layers } from '@lucide/vue';
import type { ScheduledSlot } from '@/types';

const props = defineProps<{
    slot: ScheduledSlot;
}>();

const emit = defineEmits<{
    complete: [slot: ScheduledSlot];
    skip: [slot: ScheduledSlot];
}>();

const timeBlockConfig = {
    morning: { label: 'Morning', emoji: '🌅', class: 'text-amber-500' },
    afternoon: { label: 'Afternoon', emoji: '☀️', class: 'text-orange-500' },
    evening: { label: 'Evening', emoji: '🌙', class: 'text-indigo-400' },
    anytime: { label: 'Anytime', emoji: '⏰', class: 'text-muted-foreground' },
};

const timeBlock = computed(() => timeBlockConfig[props.slot.time_block] ?? timeBlockConfig.anytime);

const isCompleted = computed(() => props.slot.status === 'completed');
const isSkipped = computed(() => props.slot.status === 'skipped');
const isPending = computed(() => props.slot.status === 'pending');

const duration = computed(() => {
    const m = props.slot.allocated_minutes;
    if (m >= 60) {
        const h = Math.floor(m / 60);
        const rem = m % 60;
        return rem ? `${h}h ${rem}m` : `${h}h`;
    }
    return `${m}m`;
});

const title = computed(() => {
    if (props.slot.task) return props.slot.task.title;
    if (props.slot.grouping_key) return `Group: ${props.slot.grouping_key}`;
    return 'Merged block';
});
</script>

<template>
    <div
        class="group flex items-start gap-3 rounded-lg border bg-card px-4 py-3 transition-all"
        :class="{
            'border-emerald-500/40 bg-emerald-500/5': isCompleted,
            'border-border/40 opacity-60': isSkipped,
            'border-border/60 hover:border-border hover:shadow-sm': isPending,
        }"
    >
        <!-- Action button -->
        <button
            v-if="isPending"
            class="mt-0.5 shrink-0 text-muted-foreground transition-colors hover:text-primary"
            :aria-label="`Complete ${title}`"
            @click="emit('complete', slot)"
        >
            <Circle class="h-5 w-5" />
        </button>
        <CheckCircle2
            v-else-if="isCompleted"
            class="mt-0.5 h-5 w-5 shrink-0 text-emerald-500"
        />
        <SkipForward v-else class="mt-0.5 h-5 w-5 shrink-0 text-muted-foreground" />

        <!-- Content -->
        <div class="min-w-0 flex-1">
            <div class="flex items-center gap-2">
                <Layers v-if="slot.is_merged" class="h-3.5 w-3.5 shrink-0 text-muted-foreground" />
                <span
                    class="text-sm font-medium"
                    :class="{ 'line-through text-muted-foreground': isSkipped }"
                >
                    {{ title }}
                </span>
            </div>

            <div class="mt-1 flex items-center gap-3 text-xs text-muted-foreground">
                <span :class="timeBlock.class" class="flex items-center gap-1">
                    {{ timeBlock.emoji }} {{ timeBlock.label }}
                </span>
                <span class="flex items-center gap-1">
                    <Clock class="h-3.5 w-3.5" />
                    {{ duration }}
                </span>
            </div>
        </div>

        <!-- Skip action -->
        <button
            v-if="isPending"
            class="mt-0.5 shrink-0 rounded p-1 text-muted-foreground opacity-0 transition-all hover:text-foreground group-hover:opacity-100"
            title="Skip"
            :aria-label="`Skip ${title}`"
            @click="emit('skip', slot)"
        >
            <SkipForward class="h-4 w-4" />
        </button>
    </div>
</template>
