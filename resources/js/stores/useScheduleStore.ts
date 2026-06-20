import { ref } from 'vue';
import { apiRequest } from '@/lib/api';
import { today, window as fetchWindow } from '@/actions/App/Http/Controllers/Api/ScheduleController';
import { complete, skip } from '@/actions/App/Http/Controllers/Api/TaskController';

export interface ScheduledSlot {
    id: number;
    user_id: number;
    task_id: number | null;
    grouping_key: string | null;
    date: string;
    time_block: 'morning' | 'afternoon' | 'evening' | 'anytime';
    allocated_minutes: number;
    slot_index: number;
    is_merged: boolean;
    merged_task_ids: number[] | string | null;
    status: 'pending' | 'completed' | 'skipped' | 'missed';
    task?: any;
}

const todayPlan = ref<ScheduledSlot[]>([]);
const windowPlan = ref<ScheduledSlot[]>([]);
const isLoading = ref(false);

export function useScheduleStore() {
    const fetchTodayPlan = async () => {
        isLoading.value = true;
        try {
            todayPlan.value = await apiRequest<ScheduledSlot[]>(today());
        } catch (error) {
            console.error('Failed to fetch today\'s plan:', error);
        } finally {
            isLoading.value = false;
        }
    };

    const fetchWindowPlan = async (days: number = 7) => {
        isLoading.value = true;
        try {
            windowPlan.value = await apiRequest<ScheduledSlot[]>(fetchWindow({ query: { days } }));
        } catch (error) {
            console.error(`Failed to fetch ${days}-day window plan:`, error);
        } finally {
            isLoading.value = false;
        }
    };

    const completeTaskItem = async (taskId: number, durationMinutes: number = 0) => {
        try {
            const updatedTask = await apiRequest(complete(taskId), { duration_minutes: durationMinutes });
            // Update local plans status
            const updateStatus = (slots: ScheduledSlot[]) => {
                slots.forEach(slot => {
                    if (slot.task_id === taskId) {
                        slot.status = 'completed';
                    } else if (slot.is_merged && slot.merged_task_ids) {
                        const ids = typeof slot.merged_task_ids === 'string'
                            ? JSON.parse(slot.merged_task_ids)
                            : slot.merged_task_ids;
                        if (Array.isArray(ids) && ids.includes(taskId)) {
                            // Check if other merged tasks are also done (stub status update)
                            slot.status = 'completed';
                        }
                    }
                });
            };
            updateStatus(todayPlan.value);
            updateStatus(windowPlan.value);
            return updatedTask;
        } catch (error) {
            console.error(`Failed to complete task ${taskId}:`, error);
            throw error;
        }
    };

    const skipTaskItem = async (taskId: number, notes?: string) => {
        try {
            const updatedTask = await apiRequest(skip(taskId), { notes });
            const updateStatus = (slots: ScheduledSlot[]) => {
                slots.forEach(slot => {
                    if (slot.task_id === taskId) {
                        slot.status = 'skipped';
                    }
                });
            };
            updateStatus(todayPlan.value);
            updateStatus(windowPlan.value);
            return updatedTask;
        } catch (error) {
            console.error(`Failed to skip task ${taskId}:`, error);
            throw error;
        }
    };

    return {
        todayPlan,
        windowPlan,
        isLoading,
        fetchTodayPlan,
        fetchWindowPlan,
        completeTask: completeTaskItem,
        skipTask: skipTaskItem,
    };
}
