<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { Plus, Target, Sparkles, FolderOpen, Calendar, HelpCircle } from '@lucide/vue';
import { useGoalStore } from '@/stores/useGoalStore';
import GoalCard from '@/components/GoalCard.vue';
import { Button } from '@/components/ui/button';
import { Skeleton } from '@/components/ui/skeleton';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogFooter,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: '/dashboard',
            },
            {
                title: 'Goals',
                href: '/goals',
            },
        ],
    },
});

const goalStore = useGoalStore();
const activeFilter = ref<'active' | 'archived'>('active');
const showCreateDialog = ref(false);
const isCreating = ref(false);

const newGoal = ref({
    title: '',
    description: '',
    target_date: '',
    color: '#6366f1',
});

const colorPresets = [
    '#6366f1', // Indigo
    '#3b82f6', // Blue
    '#10b981', // Emerald
    '#f59e0b', // Amber
    '#f43f5e', // Rose
    '#8b5cf6', // Violet
];

onMounted(() => {
    goalStore.fetchGoals();
});

const filteredGoals = computed(() => {
    return goalStore.goals.value.filter((g) => {
        if (activeFilter.value === 'active') {
            return g.status === 'active' || g.status === 'paused' || g.status === 'completed';
        } else {
            return g.status === 'archived';
        }
    });
});

const handleCreateGoal = async () => {
    if (!newGoal.value.title.trim()) return;
    isCreating.value = true;
    try {
        await goalStore.createGoal({
            title: newGoal.value.title,
            description: newGoal.value.description || null,
            target_date: newGoal.value.target_date || null,
            color: newGoal.value.color,
            status: 'active',
        });
        showCreateDialog.value = false;
        newGoal.value = {
            title: '',
            description: '',
            target_date: '',
            color: '#6366f1',
        };
    } catch (error) {
        console.error('Failed to create goal:', error);
    } finally {
        isCreating.value = false;
    }
};

const handleArchive = async (id: number) => {
    try {
        await goalStore.updateGoal(id, { status: 'archived' });
    } catch (error) {
        console.error('Failed to archive goal:', error);
    }
};

const handleDelete = async (id: number) => {
    if (confirm('Are you sure you want to permanently delete this goal and all its tasks?')) {
        try {
            await goalStore.deleteGoal(id);
        } catch (error) {
            console.error('Failed to delete goal:', error);
        }
    }
};
</script>

<template>
    <Head title="Goals Workspace" />

    <div class="flex flex-col gap-6 p-4 md:p-6">
        <!-- Header -->
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Goals Workspace</h1>
                <p class="text-sm text-muted-foreground">
                    Define high-level outcomes, break them down into execution lists, and schedule them deterministically.
                </p>
            </div>
            <div>
                <Button @click="showCreateDialog = true" size="sm" class="w-full sm:w-auto">
                    <Plus class="mr-1.5 h-4 w-4" />
                    New Goal
                </Button>
            </div>
        </div>

        <!-- Filter bar -->
        <div class="flex border-b border-border/60">
            <button
                class="px-4 py-2 text-sm font-medium border-b-2 -mb-[2px] transition-colors"
                :class="
                    activeFilter === 'active'
                        ? 'border-primary text-foreground'
                        : 'border-transparent text-muted-foreground hover:text-foreground'
                "
                @click="activeFilter = 'active'"
            >
                Active
            </button>
            <button
                class="px-4 py-2 text-sm font-medium border-b-2 -mb-[2px] transition-colors"
                :class="
                    activeFilter === 'archived'
                        ? 'border-primary text-foreground'
                        : 'border-transparent text-muted-foreground hover:text-foreground'
                "
                @click="activeFilter = 'archived'"
            >
                Archived
            </button>
        </div>

        <!-- Goals Grid -->
        <template v-if="goalStore.isLoading.value">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <Skeleton v-for="i in 3" :key="i" class="h-[160px] rounded-xl" />
            </div>
        </template>

        <template v-else-if="!filteredGoals.length">
            <div class="flex flex-col items-center justify-center rounded-xl border border-dashed border-border py-20 text-center bg-card">
                <Target class="mb-3 h-12 w-12 text-muted-foreground/40" />
                <h3 class="font-semibold text-lg">No goals found</h3>
                <p class="mt-1 text-sm text-muted-foreground max-w-md">
                    {{
                        activeFilter === 'active'
                            ? "You don't have any active goals. Let's create your first goal to set up your roadmap!"
                            : 'No archived goals found.'
                    }}
                </p>
                <Button v-if="activeFilter === 'active'" @click="showCreateDialog = true" variant="outline" class="mt-4">
                    <Plus class="mr-1 h-4 w-4" />
                    Create First Goal
                </Button>
            </div>
        </template>

        <div v-else class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <GoalCard
                v-for="goal in filteredGoals"
                :key="goal.id"
                :goal="goal"
                @archive="handleArchive"
                @delete="handleDelete"
            />
        </div>
    </div>

    <!-- Create Goal Dialog -->
    <Dialog v-model:open="showCreateDialog">
        <DialogContent class="max-w-md">
            <DialogHeader>
                <DialogTitle>New Goal</DialogTitle>
            </DialogHeader>

            <div class="space-y-4 py-2">
                <div class="space-y-1.5">
                    <Label for="goal-title">Title</Label>
                    <Input
                        id="goal-title"
                        v-model="newGoal.title"
                        placeholder="e.g. Master Frontend Architecture"
                        required
                    />
                </div>

                <div class="space-y-1.5">
                    <Label for="goal-desc">Description</Label>
                    <textarea
                        id="goal-desc"
                        v-model="newGoal.description"
                        placeholder="Add some context about what success looks like..."
                        rows="3"
                        class="form-textarea"
                    />
                </div>

                <div class="space-y-1.5">
                    <Label for="goal-date">Target Completion Date</Label>
                    <Input
                        id="goal-date"
                        v-model="newGoal.target_date"
                        type="date"
                    />
                </div>

                <div class="space-y-1.5">
                    <Label>Theme Color</Label>
                    <div class="flex items-center gap-2 mt-1">
                        <button
                            v-for="color in colorPresets"
                            :key="color"
                            type="button"
                            class="h-8 w-8 rounded-full border border-black/10 dark:border-white/10 transition-transform hover:scale-110"
                            :class="{ 'ring-2 ring-primary ring-offset-2 dark:ring-offset-background': newGoal.color === color }"
                            :style="{ backgroundColor: color }"
                            @click="newGoal.color = color"
                            :aria-label="`Color preset ${color}`"
                        />
                    </div>
                </div>
            </div>

            <DialogFooter>
                <Button variant="outline" @click="showCreateDialog = false" :disabled="isCreating">
                    Cancel
                </Button>
                <Button @click="handleCreateGoal" :disabled="isCreating || !newGoal.title.trim()">
                    {{ isCreating ? 'Creating...' : 'Create Goal' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
