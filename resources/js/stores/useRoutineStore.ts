import { ref } from 'vue';
import { apiRequest } from '@/lib/api';
import RoutineController from '@/actions/App/Http/Controllers/Api/RoutineController';
import type { Routine, RoutineInstance } from '@/types';

const routines = ref<Routine[]>([]);
const todayInstances = ref<RoutineInstance[]>([]);
const isLoading = ref(false);

export function useRoutineStore() {
    const fetchRoutines = async () => {
        isLoading.value = true;
        try {
            routines.value = await apiRequest<Routine[]>(RoutineController.index());
        } catch (error) {
            console.error('Failed to fetch routines:', error);
        } finally {
            isLoading.value = false;
        }
    };

    const fetchTodayInstances = async () => {
        isLoading.value = true;
        try {
            todayInstances.value = await apiRequest<RoutineInstance[]>(RoutineController.today());
        } catch (error) {
            console.error('Failed to fetch today routines:', error);
        } finally {
            isLoading.value = false;
        }
    };

    const completeStep = async (instanceId: number, stepId: number) => {
        try {
            const updated = await apiRequest<RoutineInstance>(
                RoutineController.completeStep({ instance: instanceId, step: stepId }),
            );
            const idx = todayInstances.value.findIndex((i) => i.id === instanceId);
            if (idx !== -1) {
                todayInstances.value[idx] = updated;
            }
            return updated;
        } catch (error) {
            console.error('Failed to complete step:', error);
            throw error;
        }
    };

    const skipInstance = async (instanceId: number) => {
        try {
            const updated = await apiRequest<RoutineInstance>(
                RoutineController.skipInstance({ instance: instanceId }),
            );
            const idx = todayInstances.value.findIndex((i) => i.id === instanceId);
            if (idx !== -1) {
                todayInstances.value[idx] = updated;
            }
            return updated;
        } catch (error) {
            console.error('Failed to skip routine instance:', error);
            throw error;
        }
    };

    return {
        routines,
        todayInstances,
        isLoading,
        fetchRoutines,
        fetchTodayInstances,
        completeStep,
        skipInstance,
    };
}
