<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { dashboard, login, register } from '@/routes';
import { ref, computed } from 'vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Badge } from '@/components/ui/badge';

// Simulator state
const selectedCapacity = ref(240);
const simulatorTasks = [
    { id: 1, title: 'Topological Dependency Sorting', duration: 90, priority: 'critical', energy: 'high', dependsOn: null },
    { id: 2, title: 'Database Index Optimization', duration: 60, priority: 'high', energy: 'high', dependsOn: 1 },
    { id: 3, title: 'Pest Unit Testing: GroupingService', duration: 45, priority: 'medium', energy: 'medium', dependsOn: null },
    { id: 4, title: 'Inertia v3 CSR Form Refactor', duration: 30, priority: 'medium', energy: 'low', dependsOn: null },
    { id: 5, title: 'Update OpenAPI Specs', duration: 60, priority: 'low', energy: 'low', dependsOn: null },
    { id: 6, title: 'Write Engineering Architecture Notes', duration: 120, priority: 'low', energy: 'medium', dependsOn: null },
];

const scheduledTasks = computed(() => {
    let capacityLeft = selectedCapacity.value;
    const results = [];
    const sorted = [...simulatorTasks].sort((a, b) => {
        const priorityWeight = { critical: 4, high: 3, medium: 2, low: 1 };
        return priorityWeight[b.priority] - priorityWeight[a.priority];
    });

    const scheduledIds = new Set<number>();

    for (const task of sorted) {
        if (task.dependsOn && !scheduledIds.has(task.dependsOn)) {
            results.push({
                ...task,
                status: 'blocked',
                reason: 'Prerequisite task is deferred',
                allocatedMinutes: 0
            });
            continue;
        }

        if (capacityLeft <= 0) {
            results.push({
                ...task,
                status: 'deferred',
                reason: 'Exceeds daily capacity limit',
                allocatedMinutes: 0
            });
            continue;
        }

        if (task.duration <= capacityLeft) {
            capacityLeft -= task.duration;
            scheduledIds.add(task.id);
            results.push({
                ...task,
                status: 'scheduled',
                allocatedMinutes: task.duration
            });
        } else {
            const fill = capacityLeft;
            capacityLeft = 0;
            scheduledIds.add(task.id);
            results.push({
                ...task,
                status: 'split',
                allocatedMinutes: fill,
                reason: `Split: ${fill}m scheduled, ${task.duration - fill}m deferred`
            });
        }
    }
    return results;
});

const totalAllocated = computed(() => {
    return scheduledTasks.value.reduce((sum, t) => sum + t.allocatedMinutes, 0);
});

const capacityPercentage = computed(() => {
    return Math.round((totalAllocated.value / selectedCapacity.value) * 100);
});

// FAQ state
const faqItems = ref([
    {
        question: "How does the scheduling engine differ from a standard calendar?",
        answer: "Standard calendars force you to anchor tasks to strict times, leading to schedule debt when tasks overrun. AD. Routine uses a rolling-window capacity packer. It evaluates your available minutes, task priorities, energy requirements, and dependencies, then packs tasks dynamically into flexible morning, afternoon, or evening blocks."
    },
    {
        question: "What is topological dependency sorting?",
        answer: "It is a mathematical ordering of tasks based on their dependencies. Using Kahn's algorithm, the system guarantees that if Task B depends on Task A, Task B will never be scheduled until Task A is marked as completed, preventing blockages in your execution plan."
    },
    {
        question: "How are routines generated asynchronously?",
        answer: "Routines are defined with frequency rules (e.g. weekdays, weekends). A daily Cron job dispatches GenerateRoutineInstancesJob, which evaluates routine schedules and generates daily instances idempotently in the background, minimizing latency for the active user."
    },
    {
        question: "How does the system ensure reliability and isolation?",
        answer: "All heavy recalculations, AI roadmap generation, and weekly review compilations are executed asynchronously via Laravel Queue Workers. If external LLM APIs fail, the jobs retry with exponential backoff, and the main system remains fully functional using local database fallbacks."
    }
]);

const openFaqIndex = ref<number | null>(null);
const toggleFaq = (index: number) => {
    openFaqIndex.value = openFaqIndex.value === index ? null : index;
};

const scrollToSection = (id: string) => {
    const el = document.getElementById(id);
    if (el) {
        el.scrollIntoView({ behavior: 'smooth' });
    }
};
</script>

<template>
    <div class="min-h-screen bg-background text-foreground transition-colors duration-300">
        <Head title="AD. Routine - Adaptive AI Goal & Routine Operating System">
            <link rel="preconnect" href="https://fonts.googleapis.com" />
            <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="true" />
            <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:ital,wght@0,400..700;1,400..700&display=swap" rel="stylesheet" />
        </Head>

        <!-- Navigation -->
        <header class="sticky top-0 z-50 border-b border-border/80 bg-background/80 backdrop-blur-md">
            <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-6">
                <div class="flex items-center gap-2">
                    <AppLogoIcon class="size-6 text-primary" />
                    <span class="font-sans text-lg font-bold tracking-tight text-foreground">AD. Routine <span class="text-xs font-normal text-muted-foreground">Operating System</span></span>
                </div>
                <nav class="hidden items-center gap-8 md:flex">
                    <button @click="scrollToSection('architecture')" class="cursor-pointer text-sm font-medium text-muted-foreground hover:text-foreground">Architecture</button>
                    <button @click="scrollToSection('guarantees')" class="cursor-pointer text-sm font-medium text-muted-foreground hover:text-foreground">Guarantees</button>
                    <button @click="scrollToSection('deep-dive')" class="cursor-pointer text-sm font-medium text-muted-foreground hover:text-foreground">Deep Dive</button>
                    <button @click="scrollToSection('faq')" class="cursor-pointer text-sm font-medium text-muted-foreground hover:text-foreground">FAQ</button>
                </nav>
                <div class="flex items-center gap-4">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="dashboard()"
                        class="button button-primary"
                    >
                        Go to Dashboard
                    </Link>
                    <template v-else>
                        <Link
                            :href="login()"
                            class="button button-link text-muted-foreground hover:text-foreground text-sm font-semibold"
                        >
                            Log in
                        </Link>
                        <Link
                            :href="register()"
                            class="button button-primary"
                        >
                            Register
                        </Link>
                    </template>
                </div>
            </div>
        </header>

        <main class="relative overflow-hidden">
            <!-- Background Grid Pattern -->
            <div class="absolute inset-0 -z-10 bg-[linear-gradient(to_right,#8080800a_1px,transparent_1px),linear-gradient(to_bottom,#8080800a_1px,transparent_1px)] bg-[size:14px_24px] [mask-image:radial-gradient(ellipse_60%_50%_at_50%_0%,#000_70%,transparent_100%)]"></div>

            <!-- Hero Section -->
            <section class="mx-auto max-w-7xl px-6 pt-20 pb-16 text-center lg:pt-32">
                <div class="inline-flex items-center gap-2 rounded-full border border-primary/20 bg-primary/10 px-3 py-1 text-xs font-semibold text-primary">
                    <span class="relative flex h-2 w-2">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-primary opacity-75"></span>
                        <span class="relative inline-flex h-2 w-2 rounded-full bg-primary"></span>
                    </span>
                    Now Powered by Deterministic Capacity Packing
                </div>
                <h1 class="mx-auto mt-6 max-w-4xl font-sans text-4xl font-extrabold tracking-tight sm:text-6xl text-foreground">
                    Goals are outcomes. <br />
                    <span class="bg-gradient-to-r from-foreground via-foreground/90 to-foreground/75 bg-clip-text text-transparent">Schedules are decisions.</span>
                </h1>
                <p class="mx-auto mt-6 max-w-2xl text-lg text-muted-foreground">
                    AD. Routine is a production-grade execution platform that combines deterministic packing algorithms, dependency graphing, and behavior analysis. It continuously compiles hierarchical goals into optimized, rolling daily schedules.
                </p>
                <div class="mt-10 flex flex-wrap justify-center gap-4">
                    <Link
                        v-if="!$page.props.auth.user"
                        :href="register()"
                        class="button button-primary button-lg shadow-md"
                    >
                        Start Executing
                    </Link>
                    <Link
                        v-else
                        :href="dashboard()"
                        class="button button-primary button-lg shadow-md"
                    >
                        Open Dashboard
                    </Link>
                    <button
                        @click="scrollToSection('deep-dive')"
                        class="button button-outline button-lg shadow-sm"
                    >
                        Read Engineering Notes
                    </button>
                    <a
                        href="https://github.com"
                        target="_blank"
                        class="button button-outline button-lg shadow-sm inline-flex items-center gap-2"
                    >
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M12 2C6.477 2 2 6.477 2 12c0 4.42 2.865 8.166 6.839 9.489.5.092.682-.217.682-.482 0-.237-.008-.866-.013-1.7-2.782.603-3.369-1.34-3.369-1.34-.454-1.156-1.11-1.464-1.11-1.464-.908-.62.069-.608.069-.608 1.003.07 1.531 1.03 1.531 1.03.892 1.529 2.341 1.087 2.91.831.092-.646.35-1.086.636-1.336-2.22-.253-4.555-1.11-4.555-4.943 0-1.091.39-1.984 1.029-2.683-.103-.253-.446-1.27.098-2.647 0 0 .84-.269 2.75 1.025A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.294 2.747-1.025 2.747-1.025.546 1.377.203 2.394.1 2.647.64.699 1.028 1.592 1.028 2.683 0 3.842-2.339 4.687-4.566 4.935.359.309.678.919.678 1.852 0 1.336-.012 2.415-.012 2.743 0 .267.18.579.688.481C19.137 20.162 22 16.418 22 12c0-5.523-4.477-10-10-10z" clip-rule="evenodd" /></svg>
                        GitHub
                    </a>
                </div>
            </section>

            <!-- Problem vs Solution -->
            <section class="mx-auto max-w-7xl px-6 py-16">
                <div class="grid gap-8 md:grid-cols-2">
                    <div class="rounded-2xl border border-destructive/10 bg-destructive/5 p-8">
                        <h3 class="text-lg font-bold text-destructive">The Planning Trap</h3>
                        <p class="mt-2 text-sm text-muted-foreground">Why traditional systems break:</p>
                        <ul class="mt-4 space-y-3">
                            <li class="flex items-start gap-2 text-sm text-muted-foreground">
                                <span class="mt-1 font-bold text-destructive">✕</span>
                                <div><strong>Calendar Fatigue:</strong> Anchoring tasks to strict times causes immediate gridlock the moment a meeting runs late.</div>
                            </li>
                            <li class="flex items-start gap-2 text-sm text-muted-foreground">
                                <span class="mt-1 font-bold text-destructive">✕</span>
                                <div><strong>Backlog Accumulation:</strong> Todo lists grow infinitely without regard for the finite hours in a day.</div>
                            </li>
                            <li class="flex items-start gap-2 text-sm text-muted-foreground">
                                <span class="mt-1 font-bold text-destructive">✕</span>
                                <div><strong>Dependency Blindness:</strong> Scheduling software places high-priority blockers alongside their prerequisites.</div>
                            </li>
                        </ul>
                    </div>

                    <div class="rounded-2xl border border-primary/10 bg-primary/5 p-8">
                        <h3 class="text-lg font-bold text-primary">The AD. Routine Solution</h3>
                        <p class="mt-2 text-sm text-muted-foreground">How AD. Routine approaches execution:</p>
                        <ul class="mt-4 space-y-3">
                            <li class="flex items-start gap-2 text-sm text-muted-foreground">
                                <span class="mt-1 font-bold text-primary">✓</span>
                                <div><strong>Rolling capacity blocks:</strong> Tasks populate flexible morning, afternoon, and evening blocks based on energy profiles.</div>
                            </li>
                            <li class="flex items-start gap-2 text-sm text-muted-foreground">
                                <span class="mt-1 font-bold text-primary">✓</span>
                                <div><strong>Constraint-aware packing:</strong> Greedy Knapsack algorithms ensure you never schedule more minutes than you have available.</div>
                            </li>
                            <li class="flex items-start gap-2 text-sm text-muted-foreground">
                                <span class="mt-1 font-bold text-primary">✓</span>
                                <div><strong>Topological ordering:</strong> Kahn's algorithm resolves prerequisites to guarantee work is executed in correct order.</div>
                            </li>
                        </ul>
                    </div>
                </div>
            </section>

            <!-- Interactive Scheduler Simulator -->
            <section class="mx-auto max-w-7xl px-6 py-16">
                <div class="rounded-3xl border border-border bg-card p-8 text-card-foreground shadow-sm">
                    <div class="lg:flex lg:items-center lg:justify-between lg:gap-12">
                        <div class="max-w-md lg:shrink-0">
                            <h2 class="text-2xl font-bold tracking-tight text-card-foreground sm:text-3xl">
                                Test the Pack Engine
                            </h2>
                            <p class="mt-4 text-sm text-muted-foreground">
                                Adjust the available daily minutes capacity to see how the mathematical packing engine prioritizes, splits, and defers tasks.
                            </p>
                            <div class="mt-8">
                                <label class="text-sm font-semibold text-foreground">
                                    Daily Available Capacity: <span class="text-primary font-bold">{{ selectedCapacity }} minutes</span>
                                </label>
                                <input
                                    type="range"
                                    min="60"
                                    max="480"
                                    step="15"
                                    v-model.number="selectedCapacity"
                                    class="mt-2 w-full accent-primary"
                                />
                                <div class="mt-2 flex justify-between text-xs text-muted-foreground">
                                    <span>1 hour</span>
                                    <span>4 hours</span>
                                    <span>8 hours</span>
                                </div>
                            </div>

                            <div class="mt-8 border-t border-border pt-6">
                                <h4 class="text-sm font-bold text-foreground">Allocation Metrics</h4>
                                <div class="mt-4 flex items-center gap-4">
                                    <div class="relative flex h-16 w-16 items-center justify-center rounded-full border-4 border-border">
                                        <span class="text-sm font-bold text-foreground">{{ capacityPercentage }}%</span>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-foreground">{{ totalAllocated }}m scheduled</div>
                                        <div class="text-xs text-muted-foreground">{{ selectedCapacity - totalAllocated }}m remaining capacity</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-8 grow lg:mt-0">
                            <div class="rounded-2xl border border-border/60 bg-muted/40 p-6">
                                <div class="flex items-center justify-between border-b border-border pb-4">
                                    <span class="text-xs font-bold uppercase tracking-wider text-muted-foreground">Simulation Task queue</span>
                                    <span class="text-xs text-primary font-semibold">Priority & Dependency Sorted</span>
                                </div>
                                <div class="mt-4 space-y-3">
                                    <div
                                        v-for="task in scheduledTasks"
                                        :key="task.id"
                                        class="flex items-center justify-between rounded-xl border border-border bg-card p-4 transition-all duration-300 text-card-foreground hover:bg-accent/30"
                                    >
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <span class="text-sm font-semibold text-card-foreground">{{ task.title }}</span>
                                                <span class="rounded-md px-1.5 py-0.5 text-[10px] font-bold uppercase tracking-wider bg-muted text-muted-foreground border border-border">
                                                    {{ task.priority }}
                                                </span>
                                            </div>
                                            <div class="mt-1 flex items-center gap-4 text-xs text-muted-foreground">
                                                <span>Estimated: {{ task.duration }}m</span>
                                                <span v-if="task.dependsOn" class="text-primary font-medium">Depends on #{{ task.dependsOn }}</span>
                                            </div>
                                        </div>
                                        <div>
                                            <Badge
                                                v-if="task.status === 'scheduled'"
                                                variant="default"
                                            >
                                                Scheduled ({{ task.allocatedMinutes }}m)
                                            </Badge>
                                            <Badge
                                                v-else-if="task.status === 'split'"
                                                variant="secondary"
                                            >
                                                Split ({{ task.allocatedMinutes }}m)
                                            </Badge>
                                            <Badge
                                                v-else-if="task.status === 'deferred'"
                                                variant="outline"
                                            >
                                                Deferred
                                            </Badge>
                                            <Badge
                                                v-else
                                                variant="destructive"
                                            >
                                                Blocked
                                            </Badge>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Features Section -->
            <section class="mx-auto max-w-7xl px-6 py-16">
                <div class="text-center">
                    <h2 class="text-3xl font-bold tracking-tight text-foreground">Core Engine Features</h2>
                    <p class="mt-4 text-muted-foreground">Strictly mapped to the underlying application architecture.</p>
                </div>
                <div class="mt-12 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                    <div class="rounded-2xl border border-border bg-card p-6 shadow-sm text-card-foreground">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10 text-primary">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                        </div>
                        <h3 class="mt-4 font-semibold text-card-foreground">Constraint Scheduler</h3>
                        <p class="mt-2 text-sm text-muted-foreground">Greedy Knapsack-based packing algorithm matches priorities and energy levels directly against available daily minute profiles.</p>
                    </div>

                    <div class="rounded-2xl border border-border bg-card p-6 shadow-sm text-card-foreground">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10 text-primary">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11.5V14m0-2.5v-6a1.5 1.5 0 113 0m-3 6a1.5 1.5 0 00-3 0v2a7.5 7.5 0 0015 0v-5a1.5 1.5 0 013 0m-6-3V11m0-5.5v-1a1.5 1.5 0 013 0v1" /></svg>
                        </div>
                        <h3 class="mt-4 font-semibold text-card-foreground">Topological Sorting</h3>
                        <p class="mt-2 text-sm text-muted-foreground">Resolves task prerequisite hierarchies via Kahn's algorithm to prevent dependencies from violating execution order.</p>
                    </div>

                    <div class="rounded-2xl border border-border bg-card p-6 shadow-sm text-card-foreground">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10 text-primary">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <h3 class="mt-4 font-semibold text-card-foreground">Routine Automation</h3>
                        <p class="mt-2 text-sm text-muted-foreground">Generates routine step checklist instances automatically at 00:01 daily based on week/weekend frequency configuration rules.</p>
                    </div>

                    <div class="rounded-2xl border border-border bg-card p-6 shadow-sm text-card-foreground">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10 text-primary">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 002 2h2a2 2 0 002-2z" /></svg>
                        </div>
                        <h3 class="mt-4 font-semibold text-card-foreground">Momentum Analytics</h3>
                        <p class="mt-2 text-sm text-muted-foreground">Pre-computed analytics snapshots track weekly completion rates, weekday heatmaps, and energy performance mismatch metrics.</p>
                    </div>

                    <div class="rounded-2xl border border-border bg-card p-6 shadow-sm text-card-foreground">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10 text-primary">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <h3 class="mt-4 font-semibold text-card-foreground">AI Roadmap Planner</h3>
                        <p class="mt-2 text-sm text-muted-foreground">Decomposes high-level goal definitions into logical task trees asynchronously via LLM queues when requested.</p>
                    </div>

                    <div class="rounded-2xl border border-border bg-card p-6 shadow-sm text-card-foreground">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10 text-primary">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                        </div>
                        <h3 class="mt-4 font-semibold text-card-foreground">Enterprise Fortify Auth</h3>
                        <p class="mt-2 text-sm text-muted-foreground">Full passkey authentication, multi-device management, and TOTP-based two-factor security out of the box.</p>
                    </div>
                </div>
            </section>

            <!-- Under the Hood (Architecture Diagram) -->
            <section id="architecture" class="mx-auto max-w-7xl px-6 py-16 scroll-mt-16">
                <div class="text-center">
                    <h2 class="text-3xl font-bold tracking-tight text-foreground">Under the Hood</h2>
                    <p class="mt-4 text-muted-foreground">How data moves through the core scheduling pipeline.</p>
                </div>
                <div class="mt-12 rounded-3xl border border-border bg-muted/40 p-8">
                    <div class="grid gap-8 md:grid-cols-4">
                        <div class="rounded-2xl bg-card border border-border/80 p-6 text-card-foreground">
                            <span class="text-xs font-bold text-primary">STEP 1</span>
                            <h4 class="mt-2 font-bold">State Request</h4>
                            <p class="mt-2 text-xs text-muted-foreground">Inertia client triggers `POST /api/tasks/{id}/complete` requesting task mutation. Request validation is handled via custom FormRequests.</p>
                        </div>
                        <div class="rounded-2xl bg-card border border-border/80 p-6 text-card-foreground">
                            <span class="text-xs font-bold text-primary">STEP 2</span>
                            <h4 class="mt-2 font-bold">Event Dispatch</h4>
                            <p class="mt-2 text-xs text-muted-foreground">`TaskCompleted` event fires. DB writes log. Laravel Event Bus pushes recalculation payload onto Redis-backed Queue.</p>
                        </div>
                        <div class="rounded-2xl bg-card border border-border/80 p-6 text-card-foreground">
                            <span class="text-xs font-bold text-primary">STEP 3</span>
                            <h4 class="mt-2 font-bold">Queue Processing</h4>
                            <p class="mt-2 text-xs text-muted-foreground">Queue Worker executes `GenerateUserScheduleJob`. Eager loads dependencies and calls `SchedulingService::packSchedule()`.</p>
                        </div>
                        <div class="rounded-2xl bg-card border border-border/80 p-6 text-card-foreground">
                            <span class="text-xs font-bold text-primary">STEP 4</span>
                            <h4 class="mt-2 font-bold">Persistence & Cache</h4>
                            <p class="mt-2 text-xs text-muted-foreground">Old slots are cleared, new slots are persisted to MySQL and cached under `schedule:{user_id}:{date}` in Redis. Client is updated via Inertia refresh.</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Reliability & System Guarantees -->
            <section id="guarantees" class="mx-auto max-w-7xl px-6 py-16 scroll-mt-16">
                <div class="text-center">
                    <h2 class="text-3xl font-bold tracking-tight text-foreground">Reliability & System Guarantees</h2>
                    <p class="mt-4 text-muted-foreground">AD. Routine is engineered with fault tolerance, isolation, and consistency in mind.</p>
                </div>
                <div class="mt-12 grid gap-8 md:grid-cols-2">
                    <div class="flex gap-4">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                        </div>
                        <div>
                            <h4 class="font-bold text-foreground">Idempotent Generation</h4>
                            <p class="mt-2 text-sm text-muted-foreground">
                                Routine instance generation uses database-level constraints and unique keys (`routine_id`, `date`) via Laravel's `firstOrCreate`. This guarantees that daily routine checklists are never generated twice, even if multiple schedules or retries run.
                            </p>
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                        </div>
                        <div>
                            <h4 class="font-bold text-foreground">Failure Isolation</h4>
                            <p class="mt-2 text-sm text-muted-foreground">
                                AI planning services and Weekly review requests interact with external LLM endpoints asynchronously. If an endpoint becomes unavailable, jobs are isolated and retried with exponential backoff. The core deterministic scheduler remains active.
                            </p>
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                        </div>
                        <div>
                            <h4 class="font-bold text-foreground">Cache Fallback</h4>
                            <p class="mt-2 text-sm text-muted-foreground">
                                Today's plan is cached in Redis for fast retrieval. In the event of a Redis node failure, the application automatically falls back to reading the persisted `scheduled_slots` from MySQL directly, maintaining high availability.
                            </p>
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 7.89M9 11l3-3 3 3m-3-3v12" /></svg>
                        </div>
                        <div>
                            <h4 class="font-bold text-foreground">Automatic Backpressure</h4>
                            <p class="mt-2 text-sm text-muted-foreground">
                                Massive calculations like rolling-window updates are decoupled from HTTP request lifecycles. They are handled by Laravel queue workers, preventing high web request execution times from causing DB lock contention.
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Engineering Deep Dive -->
            <section id="deep-dive" class="mx-auto max-w-7xl px-6 py-16 scroll-mt-16">
                <div class="rounded-3xl border border-border bg-card p-8 text-card-foreground shadow-sm">
                    <h2 class="text-2xl font-bold tracking-tight text-foreground sm:text-3xl">Engineering Deep Dive</h2>
                    <p class="mt-2 text-sm text-primary">Implementation strategies, algorithms, and tradeoffs</p>

                    <div class="mt-12 space-y-8">
                        <div>
                            <h4 class="text-lg font-bold text-foreground">1. Capacity-Aware Knapsack Heuristic</h4>
                            <p class="mt-2 text-sm text-muted-foreground">
                                The engine models schedules using a variation of the bounded knapsack problem. Available capacity minutes represent the knapsack capacity. Tasks, weighted by priority (Critical = 4, High = 3, Medium = 2, Low = 1) and energy level, are placed greedily. To handle larger tasks, the algorithm checks `can_split` flags. If enabled, it schedules a portion of the task to fill the remaining daily slot, marks the split in `task_logs`, and carries the remaining minutes over as a new deferred task on the subsequent day.
                            </p>
                        </div>

                        <div>
                            <h4 class="text-lg font-bold text-foreground">2. Topological Graph Resolution</h4>
                            <p class="mt-2 text-sm text-muted-foreground">
                                Task execution lists are validated using Kahn's algorithm for topological sorting prior to schedule packing. Dependencies defined in `task_dependencies` must be resolved. Cycles are caught during task creation (`hasCyclicDependency`) to prevent infinite calculation loops. In the scheduling loop, any task whose dependency has not been scheduled within the current window is flagged as blocked and skipped for packing.
                            </p>
                        </div>

                        <div>
                            <h4 class="text-lg font-bold text-foreground">3. Concurrency Protection & User Isolation</h4>
                            <p class="mt-2 text-sm text-muted-foreground">
                                Schedule recalculation is user-scoped. To prevent race conditions from double-clicks or concurrent REST updates, the `GenerateUserScheduleJob` obtains an atomic Redis lock on `schedule_lock:{user_id}` for up to 10 seconds. If a lock is held, subsequent recalculation requests are discarded, protecting the scheduling service from calculation loops and lock contention.
                            </p>
                        </div>

                        <div>
                            <h4 class="text-lg font-bold text-foreground">4. Eager Loading & N+1 Prevention</h4>
                            <p class="mt-2 text-sm text-muted-foreground">
                                The `SchedulingService::loadPendingTasks()` pipeline relies on database relationships. To keep response times fast, we eager load `taskAttribute` and `dependencies` when pulling the pending task collection. This reduces database queries from O(N) to O(1) during the constraint validation step.
                            </p>
                        </div>

                        <div>
                            <h4 class="text-lg font-bold text-foreground">5. Tradeoff: Determinism vs. Flexibility</h4>
                            <p class="mt-2 text-sm text-muted-foreground">
                                A major design decision was choosing deterministic constraint-based scheduling over heuristic estimations. Rather than squeezing tasks into overfilled days, the engine strictly defers tasks if capacity is exceeded. While this ensures daily schedules are realistic and achievable, it requires users to actively change their capacity overrides in `user_capacity_profiles` if they want to load more work.
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Tech Stack Section -->
            <section class="mx-auto max-w-7xl px-6 py-16">
                <div class="text-center">
                    <h2 class="text-3xl font-bold tracking-tight text-foreground">Technical Stack</h2>
                    <p class="mt-4 text-muted-foreground">Standardized stack mapping exactly to the production codebase.</p>
                </div>
                <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="rounded-2xl border border-border bg-card p-6 text-card-foreground shadow-sm">
                        <span class="text-xs font-bold text-muted-foreground">BACKEND</span>
                        <h4 class="mt-2 font-bold text-card-foreground">Laravel 13 & PHP 8.3+</h4>
                        <p class="mt-2 text-xs text-muted-foreground">Clean service layer architecture, Form Requests, API Resources, and event-driven listeners.</p>
                    </div>
                    <div class="rounded-2xl border border-border bg-card p-6 text-card-foreground shadow-sm">
                        <span class="text-xs font-bold text-muted-foreground">FRONTEND</span>
                        <h4 class="mt-2 font-bold text-card-foreground">Vue 3 & Inertia.js v3</h4>
                        <p class="mt-2 text-xs text-muted-foreground">Single Page App experience with deferred props, polling, and direct layout prop bindings.</p>
                    </div>
                    <div class="rounded-2xl border border-border bg-card p-6 text-card-foreground shadow-sm">
                        <span class="text-xs font-bold text-muted-foreground">INFRASTRUCTURE</span>
                        <h4 class="mt-2 font-bold text-card-foreground">MySQL & Redis</h4>
                        <p class="mt-2 text-xs text-muted-foreground">Fully normalized schemas with foreign keys, Redis schedule caching, and background queues.</p>
                    </div>
                    <div class="rounded-2xl border border-border bg-card p-6 text-card-foreground shadow-sm">
                        <span class="text-xs font-bold text-muted-foreground">STYLING</span>
                        <h4 class="mt-2 font-bold text-card-foreground">Tailwind CSS v4</h4>
                        <p class="mt-2 text-xs text-muted-foreground">Pure Tailwind styling conforming to the custom CSS theme and system-preference dark mode.</p>
                    </div>
                </div>
            </section>

            <!-- FAQ Section -->
            <section id="faq" class="mx-auto max-w-4xl px-6 py-16 scroll-mt-16">
                <div class="text-center">
                    <h2 class="text-3xl font-bold tracking-tight text-foreground">Frequently Asked Questions</h2>
                    <p class="mt-4 text-muted-foreground">Engineering answers for how the platform operates.</p>
                </div>
                <div class="mt-12 space-y-4">
                    <div
                        v-for="(item, index) in faqItems"
                        :key="index"
                        class="rounded-xl border border-border bg-card text-card-foreground"
                    >
                        <button
                            @click="toggleFaq(index)"
                            class="flex w-full items-center justify-between p-6 text-left font-semibold text-foreground hover:bg-accent/40 rounded-xl transition-colors duration-200"
                        >
                            <span>{{ item.question }}</span>
                            <span class="ml-6 shrink-0 text-muted-foreground">
                                <svg
                                    class="h-5 w-5 transform transition-transform duration-200"
                                    :class="{ 'rotate-180': openFaqIndex === index }"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </span>
                        </button>
                        <div
                            v-show="openFaqIndex === index"
                            class="border-t border-border p-6 text-sm leading-relaxed text-muted-foreground"
                        >
                            {{ item.answer }}
                        </div>
                    </div>
                </div>
            </section>

            <!-- CTA Section -->
            <section class="mx-auto max-w-7xl px-6 py-16">
                <div class="relative overflow-hidden rounded-3xl border border-border bg-card px-6 py-20 text-center shadow-xl sm:px-12 text-card-foreground">
                    <div class="absolute inset-0 -z-10 bg-[radial-gradient(circle_at_center,rgba(20,184,166,0.15),transparent)]"></div>
                    <h2 class="mx-auto max-w-2xl text-3xl font-bold tracking-tight text-foreground sm:text-4xl">
                        Optimize Your Execution Pipeline Today
                    </h2>
                    <p class="mx-auto mt-6 max-w-xl text-lg text-muted-foreground">
                        Stop managing lists. Start executing commitments on a schedule that adapts to your capacity and energy.
                    </p>
                    <div class="mt-10 flex justify-center gap-4">
                        <Link
                            v-if="!$page.props.auth.user"
                            :href="register()"
                            class="button button-primary button-lg shadow-md"
                        >
                            Get Started
                        </Link>
                        <Link
                            v-else
                            :href="dashboard()"
                            class="button button-primary button-lg shadow-md"
                        >
                            Go to Dashboard
                        </Link>
                    </div>
                </div>
            </section>
        </main>

        <footer class="border-t border-border bg-muted/30 py-12">
            <div class="mx-auto max-w-7xl px-6 text-center text-sm text-muted-foreground">
                <div class="flex justify-center gap-2 mb-4">
                    <AppLogoIcon class="size-5 text-muted-foreground" />
                    <span class="font-sans font-bold text-foreground">AD. Routine</span>
                </div>
                <p>&copy; 2026 AD-Technology-Inc. All rights reserved.</p>
            </div>
        </footer>
    </div>
</template>
