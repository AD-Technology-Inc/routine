<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    Plus,
    RefreshCw,
    Sparkles,
    CalendarDays,
    ChevronLeft,
    Loader2,
} from '@lucide/vue';
import { dashboard } from '@/routes';
import { useGoalStore } from '@/stores/useGoalStore';
import { useScheduleStore } from '@/stores/useScheduleStore';
import TaskItem from '@/components/TaskItem.vue';
import ScheduledSlotCard from '@/components/ScheduledSlotCard.vue';
import TaskAttributeForm from '@/components/TaskAttributeForm.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Skeleton } from '@/components/ui/skeleton';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogFooter,
} from '@/components/ui/dialog';
import { Badge } from '@/components/ui/badge';
import { apiRequest } from '@/lib/api';
import GoalController from '@/actions/App/Http/Controllers/Api/GoalController';
import TaskController from '@/actions/App/Http/Controllers/Api/TaskController';
import ScheduleController from '@/actions/App/Http/Controllers/Api/ScheduleController';
import type { Task, Goal } from '@/types';

const props = defineProps<{
    goalId: number;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Goals', href: '/goals' },
            { title: 'Workspace', href: '#' },
        ],
    },
});

const goalStore = useGoalStore();
const scheduleStore = useScheduleStore();

const goal = ref<Goal | null>(null);
const tasks = ref<Task[]>([]);
const goalSlots = ref<any[]>([]);
const isLoadingGoal = ref(false);
const isGeneratingSchedule = ref(false);
const isTriggeringAI = ref(false);
const showAddTask = ref(false);
const isAddingTask = ref(false);

const newTask = ref({
    title: '',
    estimated_minutes: 30,
    attributes: {} as Partial<Task>,
});

const pendingTasks = computed(() => tasks.value.filter((t) => t.status === 'pending'));
const completedTasks = computed(() => tasks.value.filter((t) => t.status === 'completed'));
const skippedTasks = computed(() => tasks.value.filter((t) => t.status === 'skipped'));

const accentColor = computed(() => goal.value?.color ?? '#6366f1');

const loadGoal = async () => {
    isLoadingGoal.value = true;
    try {
        goal.value = await apiRequest<Goal>(GoalController.show(props.goalId));
        const fetchedTasks = await apiRequest<Task[]>(
            TaskController.index({ goal: props.goalId }),
        );
        tasks.value = fetchedTasks;
    } catch (e) {
        console.error('Failed to load goal:', e);
    } finally {
        isLoadingGoal.value = false;
    }
};

const loadGoalSchedule = async () => {
    try {
        const slots = await apiRequest<any[]>(ScheduleController.show({ goal: props.goalId }));
        goalSlots.value = slots;
    } catch (e) {
        console.error('Failed to load goal schedule:', e);
    }
};

const generateSchedule = async () => {
    isGeneratingSchedule.value = true;
    try {
        await apiRequest(ScheduleController.generate({ goal: props.goalId }));
        await loadGoalSchedule();
    } catch (e) {
        console.error('Failed to generate schedule:', e);
    } finally {
        isGeneratingSchedule.value = false;
    }
};

const triggerAIPlan = async () => {
    isTriggeringAI.value = true;
    try {
        await goalStore.triggerGoalPlanning(props.goalId);
        // Reload tasks after AI planning queued (AI is async)
        setTimeout(loadGoal, 2000);
    } catch (e) {
        console.error('Failed to trigger AI plan:', e);
    } finally {
        isTriggeringAI.value = false;
    }
};

const addTask = async () => {
    if (!newTask.value.title.trim()) return;
    isAddingTask.value = true;
    try {
        const created = await apiRequest<Task>(TaskController.store({ goal: props.goalId }), {
            title: newTask.value.title,
            estimated_minutes: newTask.value.estimated_minutes,
            ...newTask.value.attributes,
        });
        tasks.value.push(created);
        newTask.value = { title: '', estimated_minutes: 30, attributes: {} };
        showAddTask.value = false;
    } catch (e) {
        console.error('Failed to add task:', e);
    } finally {
        isAddingTask.value = false;
    }
};

const handleCompleteTask = async (task: Task) => {
    try {
        await apiRequest(TaskController.complete(task.id), { duration_minutes: 0 });
        const idx = tasks.value.findIndex((t) => t.id === task.id);
        if (idx !== -1) tasks.value[idx] = { ...tasks.value[idx], status: 'completed' };
    } catch (e) {
        console.error('Failed to complete task:', e);
    }
};

const handleSkipTask = async (task: Task) => {
    try {
        await apiRequest(TaskController.skip(task.id));
        const idx = tasks.value.findIndex((t) => t.id === task.id);
        if (idx !== -1) tasks.value[idx] = { ...tasks.value[idx], status: 'skipped' };
    } catch (e) {
        console.error('Failed to skip task:', e);
    }
};

const handleCompleteSlot = async (slot: any) => {
    if (slot.task_id) {
        await scheduleStore.completeTask(slot.task_id, 0);
        const idx = goalSlots.value.findIndex((s) => s.id === slot.id);
        if (idx !== -1) goalSlots.value[idx] = { ...goalSlots.value[idx], status: 'completed' };
    }
};

const handleSkipSlot = async (slot: any) => {
    if (slot.task_id) {
        await scheduleStore.skipTask(slot.task_id);
        const idx = goalSlots.value.findIndex((s) => s.id === slot.id);
        if (idx !== -1) goalSlots.value[idx] = { ...goalSlots.value[idx], status: 'skipped' };
    }
};

onMounted(async () => {
    await loadGoal();
    await loadGoalSchedule();
});
</script>

<template>
    <Head :title="goal?.title ?? 'Goal Workspace'" />

    <div class="flex h-full flex-col">
        <!-- Goal header -->
        <div
            class="border-b border-border/60 px-4 py-3 md:px-6"
            :style="{ borderTopColor: accentColor, borderTopWidth: '2px' }"
        >
            <div class="flex items-center gap-3">
                <Link
                    href="/goals"
                    class="text-muted-foreground transition-colors hover:text-foreground"
                >
                    <ChevronLeft class="h-5 w-5" />
                </Link>

                <template v-if="isLoadingGoal">
                    <Skeleton class="h-6 w-48" />
                </template>
                <template v-else-if="goal">
                    <h1 class="text-lg font-bold">{{ goal.title }}</h1>
                    <Badge variant="outline" class="capitalize">{{ goal.status }}</Badge>
                </template>

                <div class="ml-auto flex items-center gap-2">
                    <Button
                        variant="outline"
                        size="sm"
                        :disabled="isTriggeringAI"
                        @click="triggerAIPlan"
                    >
                        <Loader2 v-if="isTriggeringAI" class="mr-1.5 h-4 w-4 animate-spin" />
                        <Sparkles v-else class="mr-1.5 h-4 w-4" />
                        AI Plan
                    </Button>
                    <Button
                        variant="outline"
                        size="sm"
                        :disabled="isGeneratingSchedule"
                        @click="generateSchedule"
                    >
                        <Loader2
                            v-if="isGeneratingSchedule"
                            class="mr-1.5 h-4 w-4 animate-spin"
                        />
                        <RefreshCw v-else class="mr-1.5 h-4 w-4" />
                        Generate Schedule
                    </Button>
                    <Button size="sm" @click="showAddTask = true">
                        <Plus class="mr-1.5 h-4 w-4" />
                        Add Task
                    </Button>
                </div>
            </div>
        </div>

        <!-- 3-column layout -->
        <div class="flex min-h-0 flex-1 divide-x divide-border/60 overflow-auto">
            <!-- Column 1: Pending Tasks -->
            <div class="flex w-full min-w-[260px] flex-col overflow-auto lg:w-1/3">
                <div class="border-b border-border/40 px-4 py-2.5">
                    <h2 class="text-sm font-semibold text-muted-foreground">
                        PENDING
                        <span class="ml-1 text-foreground">{{ pendingTasks.length }}</span>
                    </h2>
                </div>
                <div class="flex-1 space-y-2 overflow-auto p-3">
                    <template v-if="isLoadingGoal">
                        <Skeleton v-for="i in 4" :key="i" class="h-16 w-full rounded-lg" />
                    </template>
                    <div
                        v-else-if="!pendingTasks.length"
                        class="flex flex-col items-center py-10 text-center"
                    >
                        <p class="text-sm text-muted-foreground">No pending tasks</p>
                        <Button
                            variant="ghost"
                            size="sm"
                            class="mt-2"
                            @click="showAddTask = true"
                        >
                            <Plus class="mr-1 h-4 w-4" />
                            Add task
                        </Button>
                    </div>
                    <TaskItem
                        v-for="task in pendingTasks"
                        v-else
                        :key="task.id"
                        :task="task"
                        @complete="handleCompleteTask"
                        @skip="handleSkipTask"
                        @edit="() => {}"
                    />
                </div>
            </div>

            <!-- Column 2: Schedule Window -->
            <div class="flex w-full min-w-[260px] flex-col overflow-auto lg:w-1/3">
                <div class="border-b border-border/40 px-4 py-2.5">
                    <h2 class="text-sm font-semibold text-muted-foreground">
                        SCHEDULED
                        <span class="ml-1 text-foreground">{{ goalSlots.length }}</span>
                    </h2>
                </div>
                <div class="flex-1 space-y-2 overflow-auto p-3">
                    <div
                        v-if="!goalSlots.length"
                        class="flex flex-col items-center py-10 text-center"
                    >
                        <CalendarDays class="mb-2 h-8 w-8 text-muted-foreground/50" />
                        <p class="text-sm text-muted-foreground">No slots scheduled</p>
                        <Button
                            variant="ghost"
                            size="sm"
                            class="mt-2"
                            :disabled="isGeneratingSchedule"
                            @click="generateSchedule"
                        >
                            <RefreshCw class="mr-1 h-4 w-4" />
                            Generate
                        </Button>
                    </div>
                    <ScheduledSlotCard
                        v-for="slot in goalSlots"
                        v-else
                        :key="slot.id"
                        :slot="slot"
                        @complete="handleCompleteSlot"
                        @skip="handleSkipSlot"
                    />
                </div>
            </div>

            <!-- Column 3: Completed/Skipped -->
            <div class="flex w-full min-w-[260px] flex-col overflow-auto lg:w-1/3">
                <div class="border-b border-border/40 px-4 py-2.5">
                    <h2 class="text-sm font-semibold text-muted-foreground">
                        DONE
                        <span class="ml-1 text-emerald-500">{{ completedTasks.length }}</span>
                        <span class="ml-1.5 text-muted-foreground/60">
                            · SKIPPED
                            <span class="text-muted-foreground">{{ skippedTasks.length }}</span>
                        </span>
                    </h2>
                </div>
                <div class="flex-1 space-y-2 overflow-auto p-3">
                    <TaskItem
                        v-for="task in completedTasks"
                        :key="task.id"
                        :task="task"
                        @complete="() => {}"
                        @skip="() => {}"
                        @edit="() => {}"
                    />
                    <TaskItem
                        v-for="task in skippedTasks"
                        :key="task.id"
                        :task="task"
                        @complete="() => {}"
                        @skip="() => {}"
                        @edit="() => {}"
                    />
                    <div
                        v-if="!completedTasks.length && !skippedTasks.length"
                        class="py-10 text-center text-sm text-muted-foreground"
                    >
                        No completed tasks yet
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Task Dialog -->
    <Dialog v-model:open="showAddTask">
        <DialogContent class="max-w-lg">
            <DialogHeader>
                <DialogTitle>Add Task</DialogTitle>
            </DialogHeader>

            <div class="space-y-4">
                <div class="space-y-1.5">
                    <Label for="task-title">Title</Label>
                    <Input
                        id="task-title"
                        v-model="newTask.title"
                        placeholder="What needs to be done?"
                        autofocus
                        @keydown.enter="addTask"
                    />
                </div>

                <div class="space-y-1.5">
                    <Label for="task-minutes">Estimated Minutes</Label>
                    <Input
                        id="task-minutes"
                        v-model.number="newTask.estimated_minutes"
                        type="number"
                        min="5"
                        step="5"
                    />
                </div>

                <div class="rounded-lg border border-border/60 p-3">
                    <p class="mb-3 text-sm font-medium text-muted-foreground">
                        Scheduling Attributes
                    </p>
                    <TaskAttributeForm v-model="newTask.attributes" />
                </div>
            </div>

            <DialogFooter>
                <Button variant="outline" @click="showAddTask = false">Cancel</Button>
                <Button :disabled="isAddingTask || !newTask.title.trim()" @click="addTask">
                    <Loader2 v-if="isAddingTask" class="mr-1.5 h-4 w-4 animate-spin" />
                    Add Task
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
