<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import { Bot, Send, Trash2, Sparkles, User, Loader2, ArrowLeft } from '@lucide/vue';
import { useAIStore } from '@/stores/useAIStore';
import { useGoalStore } from '@/stores/useGoalStore';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Card, CardContent } from '@/components/ui/card';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: '/dashboard',
            },
            {
                title: 'AI Coach',
                href: '/ai-coach',
            },
        ],
    },
});

const aiStore = useAIStore();
const goalStore = useGoalStore();

const messageText = ref('');
const selectedGoalId = ref<string>('all');
const chatContainer = ref<HTMLDivElement | null>(null);

onMounted(() => {
    goalStore.fetchGoals();
    scrollToBottom();
});

const activeGoals = computed(() =>
    goalStore.goals.value.filter((g) => g.status === 'active'),
);

const scrollToBottom = () => {
    setTimeout(() => {
        if (chatContainer.value) {
            chatContainer.value.scrollTop = chatContainer.value.scrollHeight;
        }
    }, 50);
};

const handleSend = async () => {
    if (!messageText.value.trim() || aiStore.isSending.value) return;

    const content = messageText.value;
    messageText.value = '';

    // Build context
    let context: any[] | undefined = undefined;
    if (selectedGoalId.value !== 'all') {
        const goal = goalStore.goals.value.find((g) => g.id.toString() === selectedGoalId.value);
        if (goal) {
            context = [{ id: goal.id, title: goal.title, description: goal.description }];
        }
    } else {
        context = activeGoals.value.map((g) => ({
            id: g.id,
            title: g.title,
            description: g.description,
        }));
    }

    try {
        await aiStore.sendMessage(content, context);
        scrollToBottom();
    } catch (e) {
        console.error('Failed to send message:', e);
    }
};

const handleClear = () => {
    if (confirm('Are you sure you want to clear your chat history?')) {
        aiStore.clearHistory();
    }
};
</script>

<template>
    <Head title="AI Coach" />

    <div class="flex h-[calc(100vh-4rem)] flex-col">
        <!-- Top controls bar -->
        <div class="flex items-center justify-between border-b border-border/60 p-4 shrink-0">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-primary">
                    <Bot class="h-5 w-5" />
                </div>
                <div>
                    <h1 class="text-base font-semibold leading-none">AI Coach</h1>
                    <p class="text-xs text-muted-foreground mt-1">
                        Get personalized feedback, roadmaps, and scheduling optimizations.
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <!-- Context selection -->
                <div class="flex items-center gap-2">
                    <span class="text-xs text-muted-foreground hidden sm:inline">Context:</span>
                    <Select v-model="selectedGoalId">
                        <SelectTrigger class="h-9 w-[150px] sm:w-[200px]">
                            <SelectValue placeholder="Goal context" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All Active Goals</SelectItem>
                            <SelectItem
                                v-for="goal in activeGoals"
                                :key="goal.id"
                                :value="goal.id.toString()"
                            >
                                {{ goal.title }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <Button
                    variant="ghost"
                    size="icon"
                    class="text-muted-foreground hover:text-destructive"
                    title="Clear history"
                    @click="handleClear"
                >
                    <Trash2 class="h-4 w-4" />
                </Button>
            </div>
        </div>

        <!-- Chat messages area -->
        <div
            ref="chatContainer"
            class="flex-1 overflow-y-auto p-4 md:p-6 space-y-4 bg-muted/10"
        >
            <div v-if="!aiStore.messages.value.length" class="mx-auto max-w-2xl py-12 text-center space-y-4">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-primary/10 text-primary">
                    <Sparkles class="h-6 w-6" />
                </div>
                <h2 class="text-lg font-semibold">Welcome to your AI Goal Coach</h2>
                <p class="text-sm text-muted-foreground max-w-md mx-auto">
                    Ask me to generate a task roadmap, advise you on capacity adjustments, or help structure your routines for maximum energy alignment.
                </p>
                <div class="grid gap-2 max-w-sm mx-auto pt-4">
                    <button
                        class="rounded-lg border border-border bg-card p-3 text-left text-xs font-medium hover:bg-muted/50 transition-colors"
                        @click="messageText = 'How can I optimize my high-energy morning slots?'; handleSend()"
                    >
                        "How can I optimize my high-energy morning slots?"
                    </button>
                    <button
                        class="rounded-lg border border-border bg-card p-3 text-left text-xs font-medium hover:bg-muted/50 transition-colors"
                        @click="messageText = 'Can you suggest a task roadmap for my selected goal?'; handleSend()"
                    >
                        "Can you suggest a task roadmap for my selected goal?"
                    </button>
                </div>
            </div>

            <div v-else class="mx-auto max-w-3xl space-y-4">
                <div
                    v-for="msg in aiStore.messages.value"
                    :key="msg.id"
                    class="flex gap-3"
                    :class="msg.role === 'user' ? 'justify-end' : 'justify-start'"
                >
                    <!-- Avatar left -->
                    <div
                        v-if="msg.role === 'assistant'"
                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-primary/15 text-primary"
                    >
                        <Bot class="h-4 w-4" />
                    </div>

                    <!-- Message Bubble -->
                    <div
                        class="rounded-2xl px-4 py-2.5 max-w-[85%] text-sm shadow-sm"
                        :class="
                            msg.role === 'user'
                                ? 'bg-primary text-primary-foreground rounded-tr-none'
                                : 'bg-card border border-border/50 text-foreground rounded-tl-none'
                        "
                    >
                        {{ msg.content }}
                    </div>

                    <!-- Avatar right -->
                    <div
                        v-if="msg.role === 'user'"
                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-muted text-muted-foreground"
                    >
                        <User class="h-4 w-4" />
                    </div>
                </div>

                <!-- Thinking indicator -->
                <div v-if="aiStore.isSending.value" class="flex gap-3 justify-start">
                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-primary/15 text-primary">
                        <Bot class="h-4 w-4" />
                    </div>
                    <div class="bg-card border border-border/50 rounded-2xl rounded-tl-none px-4 py-3 flex items-center gap-2">
                        <Loader2 class="h-4 w-4 animate-spin text-primary" />
                        <span class="text-xs text-muted-foreground">Thinking...</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chat input area -->
        <div class="border-t border-border/60 p-4 shrink-0 bg-card">
            <form @submit.prevent="handleSend" class="mx-auto max-w-3xl flex items-center gap-2">
                <Input
                    v-model="messageText"
                    placeholder="Ask the AI Coach..."
                    :disabled="aiStore.isSending.value"
                    class="flex-1"
                    aria-label="Message prompt"
                />
                <Button type="submit" :disabled="!messageText.trim() || aiStore.isSending.value">
                    <Send class="h-4 w-4" />
                </Button>
            </form>
        </div>
    </div>
</template>
