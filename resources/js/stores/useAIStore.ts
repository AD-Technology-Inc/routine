import { ref } from 'vue';
import { apiRequest } from '@/lib/api';
import { aiChat } from '@/routes';

export interface ChatMessage {
    id: string;
    role: 'user' | 'assistant';
    content: string;
    timestamp: Date;
}

export interface WeeklyReview {
    summary: string;
    suggestions: string[];
    encouragement: string;
}

const messages = ref<ChatMessage[]>([]);
const weeklyReview = ref<WeeklyReview | null>(null);
const isSending = ref(false);
const isLoadingReview = ref(false);

export function useAIStore() {
    const sendMessage = async (content: string, goalContext?: Record<string, unknown>[]) => {
        const userMessage: ChatMessage = {
            id: crypto.randomUUID(),
            role: 'user',
            content,
            timestamp: new Date(),
        };
        messages.value.push(userMessage);
        isSending.value = true;

        try {
            const payload: Record<string, unknown> = { message: content };
            if (goalContext) {
                payload.context = { goals: goalContext };
            }

            const data = await apiRequest<{ reply: string }>(aiChat(), payload);

            const assistantMessage: ChatMessage = {
                id: crypto.randomUUID(),
                role: 'assistant',
                content: data.reply,
                timestamp: new Date(),
            };
            messages.value.push(assistantMessage);
            return assistantMessage;
        } catch (error) {
            console.error('Failed to send chat message:', error);
            const errorMessage: ChatMessage = {
                id: crypto.randomUUID(),
                role: 'assistant',
                content: 'Sorry, I encountered an error. Please try again.',
                timestamp: new Date(),
            };
            messages.value.push(errorMessage);
            throw error;
        } finally {
            isSending.value = false;
        }
    };

    const clearHistory = () => {
        messages.value = [];
    };

    return {
        messages,
        weeklyReview,
        isSending,
        isLoadingReview,
        sendMessage,
        clearHistory,
    };
}
