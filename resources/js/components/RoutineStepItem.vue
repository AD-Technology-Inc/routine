<script setup lang="ts">
import { Circle, CheckCircle2, Clock } from '@lucide/vue';
import type { RoutineStep } from '@/types';

defineProps<{
    step: RoutineStep;
    isCompleted: boolean;
    disabled?: boolean;
}>();

const emit = defineEmits<{
    complete: [stepId: number];
}>();
</script>

<template>
    <div
        class="flex items-center gap-3 px-5 py-3 transition-colors"
        :class="{ 'opacity-60': disabled || isCompleted }"
    >
        <button
            v-if="!isCompleted"
            class="shrink-0 text-muted-foreground transition-colors hover:text-primary"
            :aria-label="`Complete step: ${step.title}`"
            :disabled="disabled"
            @click="emit('complete', step.id)"
        >
            <Circle class="h-5 w-5" />
        </button>
        <CheckCircle2
            v-else
            class="h-5 w-5 shrink-0 text-emerald-500"
        />

        <span
            class="flex-1 text-sm font-medium"
            :class="{ 'line-through text-muted-foreground': isCompleted }"
        >
            {{ step.title }}
        </span>

        <span class="flex items-center gap-1 text-xs text-muted-foreground">
            <Clock class="h-3.5 w-3.5" />
            {{ step.estimated_minutes }}m
        </span>
    </div>
</template>
