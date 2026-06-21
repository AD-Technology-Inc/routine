<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import { CalendarCheck2, CheckCircle2, Circle, SkipForward, Clock } from '@lucide/vue';
import { dashboard } from '@/routes';
import { useRoutineStore } from '@/stores/useRoutineStore';
import { Skeleton } from '@/components/ui/skeleton';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import type { RoutineInstance } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Routines', href: '/routines' },
        ],
    },
});

const routineStore = useRoutineStore();

const todayDate = computed(() =>
    new Date().toLocaleDateString('en-US', {
        weekday: 'long',
        month: 'long',
        day: 'numeric',
    }),
);

const instanceProgress = (instance: RoutineInstance) => {
    const routine = instance.routine;
    if (!routine?.steps?.length) return { completed: 0, total: 0, pct: 0 };
    const completedCount = (instance.completed_steps ?? []).length;
    const total = routine.steps.length;
    return {
        completed: completedCount,
        total,
        pct: Math.round((completedCount / total) * 100),
    };
};

const isStepCompleted = (instance: RoutineInstance, stepId: number) =>
    (instance.completed_steps ?? []).includes(stepId);

const handleCompleteStep = async (instanceId: number, stepId: number) => {
    await routineStore.completeStep(instanceId, stepId);
};

const handleSkipInstance = async (instanceId: number) => {
    await routineStore.skipInstance(instanceId);
};

const statusVariant = (status: RoutineInstance['status']): 'default' | 'secondary' | 'outline' | 'destructive' => {
    const map: Record<string, 'default' | 'secondary' | 'outline' | 'destructive'> = {
        pending: 'secondary',
        partial: 'default',
        completed: 'outline',
        skipped: 'secondary',
    };
    return map[status] ?? 'secondary';
};

onMounted(() => routineStore.fetchTodayInstances());
</script>

<template>
    <Head title="Routines" />

    <div class="flex flex-col gap-6 p-4 md:p-6">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Routines</h1>
            <p class="text-sm text-muted-foreground">{{ todayDate }}</p>
        </div>

        <template v-if="routineStore.isLoading.value">
            <div class="space-y-4">
                <Skeleton v-for="i in 3" :key="i" class="h-40 w-full rounded-xl" />
            </div>
        </template>

        <div
            v-else-if="!routineStore.todayInstances.value.length"
            class="flex flex-col items-center justify-center rounded-xl border border-dashed border-border py-20 text-center"
        >
            <CalendarCheck2 class="mb-3 h-12 w-12 text-muted-foreground/40" />
            <p class="font-semibold">No routines for today</p>
            <p class="mt-1 text-sm text-muted-foreground">
                Routines are generated automatically based on their frequency.
            </p>
        </div>

        <div v-else class="space-y-4">
            <div
                v-for="instance in routineStore.todayInstances.value"
                :key="instance.id"
                class="overflow-hidden rounded-xl border border-border/70 bg-card"
                :class="{ 'opacity-60': instance.status === 'skipped' }"
            >
                <!-- Routine Header -->
                <div class="flex items-center justify-between border-b border-border/50 px-5 py-3">
                    <div class="flex items-center gap-3">
                        <h3 class="font-semibold">
                            {{ instance.routine?.title ?? 'Routine' }}
                        </h3>
                        <Badge :variant="statusVariant(instance.status)" class="capitalize">
                            {{ instance.status }}
                        </Badge>
                    </div>

                    <div class="flex items-center gap-2">
                        <!-- Progress -->
                        <span class="text-xs text-muted-foreground">
                            {{ instanceProgress(instance).completed }}/{{ instanceProgress(instance).total }}
                        </span>
                        <Button
                            v-if="instance.status !== 'skipped' && instance.status !== 'completed'"
                            variant="ghost"
                            size="sm"
                            class="text-muted-foreground"
                            @click="handleSkipInstance(instance.id)"
                        >
                            <SkipForward class="h-4 w-4" />
                        </Button>
                    </div>
                </div>

                <!-- Progress bar -->
                <div class="h-1 w-full bg-muted">
                    <div
                        class="h-full bg-primary transition-all duration-500"
                        :style="{ width: instanceProgress(instance).pct + '%' }"
                    />
                </div>

                <!-- Steps -->
                <div class="divide-y divide-border/40">
                    <div
                        v-for="step in instance.routine?.steps ?? []"
                        :key="step.id"
                        class="flex items-center gap-3 px-5 py-3"
                    >
                        <button
                            v-if="!isStepCompleted(instance, step.id)"
                            class="shrink-0 text-muted-foreground transition-colors hover:text-primary"
                            :aria-label="`Complete step: ${step.title}`"
                            :disabled="instance.status === 'skipped'"
                            @click="handleCompleteStep(instance.id, step.id)"
                        >
                            <Circle class="h-5 w-5" />
                        </button>
                        <CheckCircle2
                            v-else
                            class="h-5 w-5 shrink-0 text-emerald-500"
                        />

                        <span
                            class="flex-1 text-sm"
                            :class="{ 'line-through text-muted-foreground': isStepCompleted(instance, step.id) }"
                        >
                            {{ step.title }}
                        </span>

                        <span class="flex items-center gap-1 text-xs text-muted-foreground">
                            <Clock class="h-3.5 w-3.5" />
                            {{ step.estimated_minutes }}m
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
