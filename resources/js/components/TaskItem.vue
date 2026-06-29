<script setup lang="ts">
import { computed } from 'vue';
import {
    CheckCircle2,
    Circle,
    Clock,
    Zap,
    SkipForward,
    ChevronDown,
    ChevronUp,
} from '@lucide/vue';
import type { Task } from '@/types';
import { Badge } from '@/components/ui/badge';

const props = defineProps<{
    task: Task;
    compact?: boolean;
}>();

const emit = defineEmits<{
    complete: [task: Task];
    skip: [task: Task];
    edit: [task: Task];
}>();

const priorityConfig = {
    critical: { label: 'Critical', class: 'bg-red-500/15 text-red-500 border-red-500/30' },
    high: { label: 'High', class: 'bg-orange-500/15 text-orange-500 border-orange-500/30' },
    medium: { label: 'Medium', class: 'bg-blue-500/15 text-blue-500 border-blue-500/30' },
    low: { label: 'Low', class: 'bg-muted text-muted-foreground border-border' },
};

const energyConfig = {
    high: { label: 'High', icon: '⚡', class: 'text-yellow-500' },
    medium: { label: 'Med', icon: '🔥', class: 'text-orange-400' },
    low: { label: 'Low', icon: '🌙', class: 'text-blue-400' },
};

const priority = computed(() => props.task.priority ?? 'medium');
const energy = computed(() => props.task.energy_level ?? 'medium');

const isCompleted = computed(() => props.task.status === 'completed');
const isSkipped = computed(() => props.task.status === 'skipped');
const isPending = computed(() => props.task.status === 'pending');

const estimatedHours = computed(() => {
    const mins = props.task.estimated_minutes;
    if (mins >= 60) {
        const h = Math.floor(mins / 60);
        const m = mins % 60;
        return m > 0 ? `${h}h ${m}m` : `${h}h`;
    }
    return `${mins}m`;
});
</script>

<template>
    <div
        class="group flex items-start gap-3 rounded-lg border border-border/50 bg-card px-4 py-3 transition-all hover:border-border hover:shadow-sm"
        :class="{
            'opacity-60': isCompleted || isSkipped,
        }"
    >
        <!-- Completion toggle -->
        <button
            v-if="isPending"
            class="mt-0.5 shrink-0 text-muted-foreground transition-colors hover:text-primary"
            :aria-label="`Complete ${task.title}`"
            @click="emit('complete', task)"
        >
            <Circle class="h-5 w-5" />
        </button>
        <CheckCircle2
            v-else-if="isCompleted"
            class="mt-0.5 h-5 w-5 shrink-0 text-success"
        />
        <SkipForward
            v-else
            class="mt-0.5 h-5 w-5 shrink-0 text-muted-foreground"
        />

        <!-- Content -->
        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2">
                <span
                    class="text-sm font-medium leading-tight"
                    :class="{ 'line-through text-muted-foreground': isCompleted || isSkipped }"
                >
                    {{ task.title }}
                </span>

                <!-- Priority badge -->
                <span
                    class="rounded border px-1.5 py-0.5 text-xs font-medium"
                    :class="priorityConfig[priority].class"
                >
                    {{ priorityConfig[priority].label }}
                </span>
            </div>

            <!-- Metadata row -->
            <div
                v-if="!compact"
                class="mt-1.5 flex flex-wrap items-center gap-3 text-xs text-muted-foreground"
            >
                <span class="flex items-center gap-1">
                    <Clock class="h-3.5 w-3.5" />
                    {{ estimatedHours }}
                </span>
                <span :class="energyConfig[energy].class">
                    {{ energyConfig[energy].icon }} {{ energyConfig[energy].label }} energy
                </span>
                <Badge v-if="task.type" variant="outline" class="text-xs">
                    {{ task.type }}
                </Badge>
                <Badge v-if="task.flexibility === 'fixed'" variant="outline" class="text-xs">
                    Fixed
                </Badge>
                <span v-if="task.grouping_key" class="text-muted-foreground">
                    #{{ task.grouping_key }}
                </span>
            </div>
        </div>

        <!-- Actions (visible on hover) -->
        <div
            v-if="isPending"
            class="flex shrink-0 items-center gap-1 opacity-0 transition-opacity group-hover:opacity-100"
        >
            <button
                class="rounded p-1 text-muted-foreground transition-colors hover:text-foreground"
                :aria-label="`Skip ${task.title}`"
                title="Skip"
                @click="emit('skip', task)"
            >
                <SkipForward class="h-4 w-4" />
            </button>
        </div>
    </div>
</template>
