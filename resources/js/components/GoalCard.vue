<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Target, CheckCircle2, Clock, MoreHorizontal, Trash2, Archive } from '@lucide/vue';
import type { Goal } from '@/types';
import { Badge } from '@/components/ui/badge';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

const props = defineProps<{
    goal: Goal;
}>();

const emit = defineEmits<{
    archive: [id: number];
    delete: [id: number];
}>();

const statusLabel: Record<Goal['status'], string> = {
    active: 'Active',
    paused: 'Paused',
    completed: 'Completed',
    archived: 'Archived',
};

const statusVariant = computed((): 'default' | 'secondary' | 'outline' | 'destructive' => {
    const map = {
        active: 'default',
        paused: 'secondary',
        completed: 'outline',
        archived: 'secondary',
    } as const;
    return map[props.goal.status] ?? 'default';
});

const completedTasks = computed(() =>
    (props.goal.tasks ?? []).filter((t) => t.status === 'completed').length,
);

const totalTasks = computed(() => (props.goal.tasks ?? []).length);

const progress = computed(() =>
    totalTasks.value > 0 ? Math.round((completedTasks.value / totalTasks.value) * 100) : 0,
);

const accentColor = computed(() => props.goal.color ?? '#6366f1');

const daysRemaining = computed(() => {
    if (!props.goal.target_date) return null;
    const diff = Math.ceil(
        (new Date(props.goal.target_date).getTime() - Date.now()) / (1000 * 60 * 60 * 24),
    );
    return diff;
});
</script>

<template>
    <div
        class="group relative flex flex-col overflow-hidden rounded-xl border border-sidebar-border/70 bg-card transition-all hover:border-sidebar-border hover:shadow-md dark:border-sidebar-border"
    >
        <!-- Color accent bar -->
        <div class="h-1 w-full" :style="{ backgroundColor: accentColor }" />

        <div class="flex flex-1 flex-col p-5">
            <!-- Header row -->
            <div class="mb-3 flex items-start justify-between gap-2">
                <div class="flex min-w-0 flex-1 items-start gap-3">
                    <div
                        class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg"
                        :style="{ backgroundColor: accentColor + '20' }"
                    >
                        <Target class="h-4 w-4" :style="{ color: accentColor }" />
                    </div>
                    <div class="min-w-0">
                        <Link
                            :href="`/goals/${goal.id}`"
                            class="truncate text-sm font-semibold leading-tight hover:underline"
                        >
                            {{ goal.title }}
                        </Link>
                        <p
                            v-if="goal.description"
                            class="mt-0.5 line-clamp-1 text-xs text-muted-foreground"
                        >
                            {{ goal.description }}
                        </p>
                    </div>
                </div>

                <div class="flex shrink-0 items-center gap-2">
                    <Badge :variant="statusVariant">{{ statusLabel[goal.status] }}</Badge>
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <button
                                class="rounded p-1 text-muted-foreground opacity-0 transition-opacity hover:text-foreground group-hover:opacity-100"
                                aria-label="Goal actions"
                            >
                                <MoreHorizontal class="h-4 w-4" />
                            </button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end">
                            <DropdownMenuItem @click="emit('archive', goal.id)">
                                <Archive class="mr-2 h-4 w-4" />
                                Archive
                            </DropdownMenuItem>
                            <DropdownMenuItem
                                class="text-destructive"
                                @click="emit('delete', goal.id)"
                            >
                                <Trash2 class="mr-2 h-4 w-4" />
                                Delete
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
            </div>

            <!-- Progress bar -->
            <div class="mb-3">
                <div class="mb-1 flex items-center justify-between text-xs text-muted-foreground">
                    <span class="flex items-center gap-1">
                        <CheckCircle2 class="h-3.5 w-3.5" />
                        {{ completedTasks }}/{{ totalTasks }} tasks
                    </span>
                    <span>{{ progress }}%</span>
                </div>
                <div class="h-1.5 w-full overflow-hidden rounded-full bg-muted">
                    <div
                        class="h-full rounded-full transition-all duration-500"
                        :style="{ width: progress + '%', backgroundColor: accentColor }"
                    />
                </div>
            </div>

            <!-- Footer -->
            <div class="mt-auto flex items-center gap-3 text-xs text-muted-foreground">
                <span v-if="daysRemaining !== null" class="flex items-center gap-1">
                    <Clock class="h-3.5 w-3.5" />
                    <span v-if="daysRemaining > 0">{{ daysRemaining }}d left</span>
                    <span v-else-if="daysRemaining === 0" class="text-amber-500">Due today</span>
                    <span v-else class="text-destructive">{{ Math.abs(daysRemaining) }}d overdue</span>
                </span>
            </div>
        </div>
    </div>
</template>
