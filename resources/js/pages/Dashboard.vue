<script setup lang="ts">
import { computed, onMounted } from 'vue';
import { Head, Link, usePoll } from '@inertiajs/vue3';
import { Target, CalendarDays, TrendingUp, Zap, RefreshCw, Plus } from '@lucide/vue';
import { dashboard } from '@/routes';
import { useGoalStore } from '@/stores/useGoalStore';
import { useScheduleStore } from '@/stores/useScheduleStore';
import { useAnalyticsStore } from '@/stores/useAnalyticsStore';
import GoalCard from '@/components/GoalCard.vue';
import ScheduledSlotCard from '@/components/ScheduledSlotCard.vue';
import MomentumAlert from '@/components/MomentumAlert.vue';
import { Button } from '@/components/ui/button';
import { Skeleton } from '@/components/ui/skeleton';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    },
});

const goalStore = useGoalStore();
const scheduleStore = useScheduleStore();
const analyticsStore = useAnalyticsStore();

const activeGoals = computed(() =>
    goalStore.goals.value.filter((g) => g.status === 'active'),
);

const todayProgress = computed(() => {
    const slots = scheduleStore.todayPlan.value;
    if (!slots.length) return { completed: 0, total: 0, pct: 0 };
    const completed = slots.filter((s) => s.status === 'completed').length;
    return {
        completed,
        total: slots.length,
        pct: Math.round((completed / slots.length) * 100),
    };
});

const totalMinutesToday = computed(() =>
    scheduleStore.todayPlan.value
        .filter((s) => s.status === 'pending')
        .reduce((sum, s) => sum + s.allocated_minutes, 0),
);

const todayDate = computed(() =>
    new Date().toLocaleDateString('en-US', {
        weekday: 'long',
        month: 'long',
        day: 'numeric',
    }),
);

const handleCompleteSlot = async (slot: any) => {
    await scheduleStore.completeTask(slot.task_id!, 0);
};

const handleSkipSlot = async (slot: any) => {
    await scheduleStore.skipTask(slot.task_id!);
};

const handleArchiveGoal = async (id: number) => {
    await goalStore.updateGoal(id, { status: 'archived' });
};

const handleDeleteGoal = async (id: number) => {
    await goalStore.deleteGoal(id);
};

const refreshSchedule = async () => {
    await Promise.all([
        scheduleStore.fetchTodayPlan(),
        analyticsStore.fetchAdaptations()
    ]);
};

// Poll today's schedule every 30s
usePoll(30_000, { only: [] });

onMounted(async () => {
    await Promise.all([
        goalStore.fetchGoals(),
        scheduleStore.fetchTodayPlan(),
        analyticsStore.fetchAdaptations()
    ]);
});
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex flex-col gap-6 p-4 md:p-6">
        <!-- Header -->
        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Dashboard</h1>
                <p class="text-sm text-muted-foreground">{{ todayDate }}</p>
            </div>
            <div class="flex items-center gap-2">
                <Button variant="outline" size="sm" @click="refreshSchedule">
                    <RefreshCw class="mr-1.5 h-4 w-4" />
                    Refresh
                </Button>
                <Button as-child size="sm">
                    <Link href="/goals">
                        <Plus class="mr-1.5 h-4 w-4" />
                        New Goal
                    </Link>
                </Button>
            </div>
        </div>

        <MomentumAlert :adaptations="analyticsStore.adaptations.value" />

        <!-- Stats row -->
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
            <div class="rounded-xl border border-border/60 bg-card p-4">
                <div class="flex items-center gap-2 text-sm text-muted-foreground">
                    <Target class="h-4 w-4" />
                    Active Goals
                </div>
                <div class="mt-1 text-2xl font-bold">
                    <template v-if="goalStore.isLoading.value">
                        <Skeleton class="h-8 w-12" />
                    </template>
                    <template v-else>{{ activeGoals.length }}</template>
                </div>
            </div>

            <div class="rounded-xl border border-border/60 bg-card p-4">
                <div class="flex items-center gap-2 text-sm text-muted-foreground">
                    <CalendarDays class="h-4 w-4" />
                    Today's Tasks
                </div>
                <div class="mt-1 text-2xl font-bold">
                    <template v-if="scheduleStore.isLoading.value">
                        <Skeleton class="h-8 w-12" />
                    </template>
                    <template v-else>{{ todayProgress.total }}</template>
                </div>
            </div>

            <div class="rounded-xl border border-border/60 bg-card p-4">
                <div class="flex items-center gap-2 text-sm text-muted-foreground">
                    <TrendingUp class="h-4 w-4" />
                    Done Today
                </div>
                <div class="mt-1 text-2xl font-bold text-emerald-500">
                    <template v-if="scheduleStore.isLoading.value">
                        <Skeleton class="h-8 w-12" />
                    </template>
                    <template v-else>{{ todayProgress.pct }}%</template>
                </div>
            </div>

            <div class="rounded-xl border border-border/60 bg-card p-4">
                <div class="flex items-center gap-2 text-sm text-muted-foreground">
                    <Zap class="h-4 w-4" />
                    Mins Remaining
                </div>
                <div class="mt-1 text-2xl font-bold">
                    <template v-if="scheduleStore.isLoading.value">
                        <Skeleton class="h-8 w-12" />
                    </template>
                    <template v-else>{{ totalMinutesToday }}</template>
                </div>
            </div>
        </div>

        <!-- Main content: two-column on lg -->
        <div class="grid gap-6 lg:grid-cols-3">
            <!-- Today's Schedule (2/3) -->
            <div class="lg:col-span-2">
                <div class="mb-3 flex items-center justify-between">
                    <h2 class="font-semibold">Today's Plan</h2>
                    <span class="text-xs text-muted-foreground">
                        {{ todayProgress.completed }}/{{ todayProgress.total }} complete
                    </span>
                </div>

                <!-- Loading skeleton -->
                <template v-if="scheduleStore.isLoading.value">
                    <div class="space-y-2">
                        <Skeleton v-for="i in 4" :key="i" class="h-16 w-full rounded-lg" />
                    </div>
                </template>

                <!-- Empty state -->
                <div
                    v-else-if="!scheduleStore.todayPlan.value.length"
                    class="flex flex-col items-center justify-center rounded-xl border border-dashed border-border py-16 text-center"
                >
                    <CalendarDays class="mb-3 h-10 w-10 text-muted-foreground/50" />
                    <p class="font-medium">No tasks scheduled today</p>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Generate a schedule from your goals to get started.
                    </p>
                    <Button variant="outline" class="mt-4" as-child>
                        <Link href="/goals">Go to Goals</Link>
                    </Button>
                </div>

                <!-- Schedule list -->
                <div v-else class="space-y-2">
                    <ScheduledSlotCard
                        v-for="slot in scheduleStore.todayPlan.value"
                        :key="slot.id"
                        :slot="slot"
                        @complete="handleCompleteSlot"
                        @skip="handleSkipSlot"
                    />
                </div>
            </div>

            <!-- Active Goals (1/3) -->
            <div>
                <div class="mb-3 flex items-center justify-between">
                    <h2 class="font-semibold">Active Goals</h2>
                    <Link href="/goals" class="text-xs text-muted-foreground hover:underline">
                        View all
                    </Link>
                </div>

                <!-- Loading skeleton -->
                <template v-if="goalStore.isLoading.value">
                    <div class="space-y-3">
                        <Skeleton v-for="i in 3" :key="i" class="h-32 w-full rounded-xl" />
                    </div>
                </template>

                <!-- Empty state -->
                <div
                    v-else-if="!activeGoals.length"
                    class="flex flex-col items-center justify-center rounded-xl border border-dashed border-border py-10 text-center"
                >
                    <Target class="mb-3 h-8 w-8 text-muted-foreground/50" />
                    <p class="text-sm font-medium">No active goals</p>
                    <Button variant="outline" size="sm" class="mt-3" as-child>
                        <Link href="/goals">Create a Goal</Link>
                    </Button>
                </div>

                <!-- Goals list -->
                <div v-else class="space-y-3">
                    <GoalCard
                        v-for="goal in activeGoals.slice(0, 5)"
                        :key="goal.id"
                        :goal="goal"
                        @archive="handleArchiveGoal"
                        @delete="handleDeleteGoal"
                    />
                </div>
            </div>
        </div>
    </div>
</template>
