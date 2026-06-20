import { ref } from 'vue';
import { apiRequest } from '@/lib/api';
import { index, store, show, update, destroy } from '@/actions/App/Http/Controllers/Api/GoalController';
import { planGoal } from '@/actions/App/Http/Controllers/Api/AIPlannerController';
import { generate } from '@/actions/App/Http/Controllers/Api/ScheduleController';

export interface Goal {
    id: number;
    title: string;
    description: string | null;
    status: 'active' | 'paused' | 'completed' | 'archived';
    target_date: string | null;
    color: string | null;
    order_index: number;
    tasks?: any[];
    routines?: any[];
}

const goals = ref<Goal[]>([]);
const activeGoal = ref<Goal | null>(null);
const isLoading = ref(false);

export function useGoalStore() {
    const fetchGoals = async () => {
        isLoading.value = true;
        try {
            goals.value = await apiRequest<Goal[]>(index());
        } catch (error) {
            console.error('Failed to fetch goals:', error);
        } finally {
            isLoading.value = false;
        }
    };

    const fetchGoal = async (id: number) => {
        isLoading.value = true;
        try {
            activeGoal.value = await apiRequest<Goal>(show(id));
            return activeGoal.value;
        } catch (error) {
            console.error(`Failed to fetch goal ${id}:`, error);
            throw error;
        } finally {
            isLoading.value = false;
        }
    };

    const createGoal = async (data: Partial<Goal>) => {
        isLoading.value = true;
        try {
            const newGoal = await apiRequest<Goal>(store(), data);
            goals.value.push(newGoal);
            return newGoal;
        } catch (error) {
            console.error('Failed to create goal:', error);
            throw error;
        } finally {
            isLoading.value = false;
        }
    };

    const updateGoalData = async (id: number, data: Partial<Goal>) => {
        isLoading.value = true;
        try {
            const updated = await apiRequest<Goal>(update(id), data);
            const idx = goals.value.findIndex(g => g.id === id);
            if (idx !== -1) {
                goals.value[idx] = updated;
            }
            if (activeGoal.value?.id === id) {
                activeGoal.value = updated;
            }
            return updated;
        } catch (error) {
            console.error(`Failed to update goal ${id}:`, error);
            throw error;
        } finally {
            isLoading.value = false;
        }
    };

    const deleteGoalData = async (id: number) => {
        isLoading.value = true;
        try {
            await apiRequest(destroy(id));
            goals.value = goals.value.filter(g => g.id !== id);
            if (activeGoal.value?.id === id) {
                activeGoal.value = null;
            }
        } catch (error) {
            console.error(`Failed to delete goal ${id}:`, error);
            throw error;
        } finally {
            isLoading.value = false;
        }
    };

    const triggerGoalPlanning = async (goalId: number) => {
        try {
            await apiRequest(planGoal(goalId));
        } catch (error) {
            console.error(`Failed to trigger AI plan for goal ${goalId}:`, error);
            throw error;
        }
    };

    const generateGoalSchedule = async (goalId: number) => {
        try {
            return await apiRequest(generate(goalId));
        } catch (error) {
            console.error(`Failed to generate schedule for goal ${goalId}:`, error);
            throw error;
        }
    };

    return {
        goals,
        activeGoal,
        isLoading,
        fetchGoals,
        fetchGoal,
        createGoal,
        updateGoal: updateGoalData,
        deleteGoal: deleteGoalData,
        triggerGoalPlanning,
        generateGoalSchedule,
    };
}
