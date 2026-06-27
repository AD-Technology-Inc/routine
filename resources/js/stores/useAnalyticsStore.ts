import { ref } from 'vue';
import { apiRequest } from '@/lib/api';
import AnalyticsController from '@/actions/App/Http/Controllers/Api/AnalyticsController';
import type { AnalyticsSnapshot } from '@/types';

export interface Adaptation {
    type: string;
    message: string;
    action: string;
}

const snapshots = ref<AnalyticsSnapshot[]>([]);
const heatmap = ref<Record<string, number>>({});
const energyPerformance = ref<Record<string, number>>({});
const adaptations = ref<Adaptation[]>([]);
const isLoading = ref(false);

export function useAnalyticsStore() {
    const fetchSummary = async (days: number = 30) => {
        isLoading.value = true;
        try {
            snapshots.value = await apiRequest<AnalyticsSnapshot[]>(
                AnalyticsController.summary({ query: { days } }),
            );
        } catch (error) {
            console.error('Failed to fetch analytics summary:', error);
        } finally {
            isLoading.value = false;
        }
    };

    const fetchHeatmap = async () => {
        try {
            heatmap.value = await apiRequest<Record<string, number>>(AnalyticsController.heatmap());
        } catch (error) {
            console.error('Failed to fetch heatmap:', error);
        }
    };

    const fetchEnergyPerformance = async () => {
        try {
            energyPerformance.value = await apiRequest<Record<string, number>>(
                AnalyticsController.energyPerformance(),
            );
        } catch (error) {
            console.error('Failed to fetch energy performance:', error);
        }
    };

    const fetchAdaptations = async () => {
        try {
            adaptations.value = await apiRequest<Adaptation[]>(
                AnalyticsController.adaptations(),
            );
        } catch (error) {
            console.error('Failed to fetch adaptations:', error);
        }
    };

    const fetchAll = async (days: number = 30) => {
        await Promise.all([
            fetchSummary(days),
            fetchHeatmap(),
            fetchEnergyPerformance(),
            fetchAdaptations(),
        ]);
    };

    return {
        snapshots,
        heatmap,
        energyPerformance,
        adaptations,
        isLoading,
        fetchSummary,
        fetchHeatmap,
        fetchEnergyPerformance,
        fetchAdaptations,
        fetchAll,
    };
}
