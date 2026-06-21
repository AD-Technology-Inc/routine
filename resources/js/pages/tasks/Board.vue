<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import { dashboard } from '@/routes';
import { useGoalStore } from '@/stores/useGoalStore';
import TaskItem from '@/components/TaskItem.vue';
import { Skeleton } from '@/components/ui/skeleton';
import { Badge } from '@/components/ui/badge';
import { apiRequest } from '@/lib/api';
import TaskController from '@/actions/App/Http/Controllers/Api/TaskController';
import GoalController from '@/actions/App/Http/Controllers/Api/GoalController';
import type { Task, Goal } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Tasks', href: '/tasks' },
        ],
    },
});

const goalStore = useGoalStore();
const allTasks = ref<Task[]>([]);
const isLoading = ref(false);

const pending = computed(() => allTasks.value.filter((t) => t.status === 'pending'));
const inProgress = computed(() => allTasks.value.filter((t) => t.status === 'in_progress'));
const completed = computed(() => allTasks.value.filter((t) => t.status === 'completed'));
const skipped = computed(() => allTasks.value.filter((t) => t.status === 'skipped'));

const loadAllTasks = async () => {
    isLoading.value = true;
    try {
        // Load all goals then fetch tasks for each active goal
        const goals = await apiRequest<Goal[]>(GoalController.index());
        const activeGoals = goals.filter((g: Goal) => g.status === 'active');

        const taskArrays = await Promise.all(
            activeGoals.map((g: Goal) =>
                apiRequest<Task[]>(TaskController.index({ goal: g.id })).catch(() => []),
            ),
        );
        allTasks.value = taskArrays.flat();
    } catch (e) {
        console.error('Failed to load tasks:', e);
    } finally {
        isLoading.value = false;
    }
};

const handleComplete = async (task: Task) => {
    try {
        await apiRequest(TaskController.complete(task.id), { duration_minutes: 0 });
        const idx = allTasks.value.findIndex((t) => t.id === task.id);
        if (idx !== -1) allTasks.value[idx] = { ...allTasks.value[idx], status: 'completed' };
    } catch (e) {
        console.error('Failed to complete task:', e);
    }
};

const handleSkip = async (task: Task) => {
    try {
        await apiRequest(TaskController.skip(task.id));
        const idx = allTasks.value.findIndex((t) => t.id === task.id);
        if (idx !== -1) allTasks.value[idx] = { ...allTasks.value[idx], status: 'skipped' };
    } catch (e) {
        console.error('Failed to skip task:', e);
    }
};

onMounted(loadAllTasks);
</script>

<template>
    <Head title="Task Board" />

    <div class="flex h-full flex-col gap-4 p-4 md:p-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold tracking-tight">Task Board</h1>
            <span class="text-sm text-muted-foreground">{{ allTasks.length }} total tasks</span>
        </div>

        <div class="grid min-h-0 flex-1 grid-cols-1 gap-4 overflow-auto sm:grid-cols-2 xl:grid-cols-4">
            <!-- Pending -->
            <div class="flex flex-col overflow-hidden rounded-xl border border-border/60 bg-muted/30">
                <div class="flex items-center gap-2 border-b border-border/60 px-4 py-3">
                    <span class="h-2 w-2 rounded-full bg-blue-500"></span>
                    <span class="text-sm font-semibold">Pending</span>
                    <Badge variant="secondary" class="ml-auto">{{ pending.length }}</Badge>
                </div>
                <div class="flex-1 space-y-2 overflow-auto p-3">
                    <template v-if="isLoading">
                        <Skeleton v-for="i in 3" :key="i" class="h-16 w-full rounded-lg" />
                    </template>
                    <TaskItem
                        v-for="task in pending"
                        v-else
                        :key="task.id"
                        :task="task"
                        @complete="handleComplete"
                        @skip="handleSkip"
                        @edit="() => {}"
                    />
                    <p
                        v-if="!isLoading && !pending.length"
                        class="py-6 text-center text-sm text-muted-foreground"
                    >
                        No pending tasks
                    </p>
                </div>
            </div>

            <!-- In Progress -->
            <div class="flex flex-col overflow-hidden rounded-xl border border-border/60 bg-muted/30">
                <div class="flex items-center gap-2 border-b border-border/60 px-4 py-3">
                    <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                    <span class="text-sm font-semibold">In Progress</span>
                    <Badge variant="secondary" class="ml-auto">{{ inProgress.length }}</Badge>
                </div>
                <div class="flex-1 space-y-2 overflow-auto p-3">
                    <template v-if="isLoading">
                        <Skeleton v-for="i in 2" :key="i" class="h-16 w-full rounded-lg" />
                    </template>
                    <TaskItem
                        v-for="task in inProgress"
                        v-else
                        :key="task.id"
                        :task="task"
                        @complete="handleComplete"
                        @skip="handleSkip"
                        @edit="() => {}"
                    />
                    <p
                        v-if="!isLoading && !inProgress.length"
                        class="py-6 text-center text-sm text-muted-foreground"
                    >
                        Nothing in progress
                    </p>
                </div>
            </div>

            <!-- Completed -->
            <div class="flex flex-col overflow-hidden rounded-xl border border-emerald-500/20 bg-emerald-500/5">
                <div class="flex items-center gap-2 border-b border-emerald-500/20 px-4 py-3">
                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                    <span class="text-sm font-semibold">Completed</span>
                    <Badge variant="secondary" class="ml-auto">{{ completed.length }}</Badge>
                </div>
                <div class="flex-1 space-y-2 overflow-auto p-3">
                    <TaskItem
                        v-for="task in completed"
                        :key="task.id"
                        :task="task"
                        @complete="() => {}"
                        @skip="() => {}"
                        @edit="() => {}"
                    />
                    <p
                        v-if="!completed.length"
                        class="py-6 text-center text-sm text-muted-foreground"
                    >
                        Nothing completed yet
                    </p>
                </div>
            </div>

            <!-- Skipped -->
            <div class="flex flex-col overflow-hidden rounded-xl border border-border/60 bg-muted/30">
                <div class="flex items-center gap-2 border-b border-border/60 px-4 py-3">
                    <span class="h-2 w-2 rounded-full bg-muted-foreground"></span>
                    <span class="text-sm font-semibold">Skipped</span>
                    <Badge variant="secondary" class="ml-auto">{{ skipped.length }}</Badge>
                </div>
                <div class="flex-1 space-y-2 overflow-auto p-3">
                    <TaskItem
                        v-for="task in skipped"
                        :key="task.id"
                        :task="task"
                        compact
                        @complete="() => {}"
                        @skip="() => {}"
                        @edit="() => {}"
                    />
                    <p
                        v-if="!skipped.length"
                        class="py-6 text-center text-sm text-muted-foreground"
                    >
                        Nothing skipped
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
