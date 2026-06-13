# GoalOS Build Tasks

## Phase 1 — Migrations + Models + Factories
- [/] Create migrations (goals, tasks, task_attributes, task_dependencies, task_logs, user_capacity_profiles, scheduled_slots, routines, routine_steps, routine_instances, analytics_snapshots)
- [/] Create Eloquent models with relations, scopes, casts, PHPDoc
- [/] Create factories for all models
- [/] Run migrations

## Phase 2 — Core Services
- [/] GoalService
- [/] TaskService
- [/] DependencyService (topological sort, cycle detection)
- [/] CapacityService
- [/] GroupingService

## Phase 3 — SchedulingService (deterministic engine)
- [/] loadPendingTasks
- [/] applyConstraints
- [/] groupTasks
- [/] packSchedule (knapsack heuristic, split/defer)
- [/] persistSlots + Redis caching
- [/] handleMissedTask

## Phase 4 — RoutineService + MomentumService + AnalyticsService
- [/] RoutineService (generate instances, complete step, skip)
- [/] MomentumService (pattern detection, adaptation rules)
- [/] AnalyticsService (snapshots, heatmap, energy performance)

## Phase 5 — Events + Jobs + Cron
- [ ] Events: GoalCreated, TaskCompleted, TaskSkipped, ScheduleGenerated, RoutineInstanceCreated
- [ ] Listeners: OnTaskCompleted, OnTaskSkipped, OnScheduleGenerated
- [ ] Jobs: GenerateUserScheduleJob, GenerateRoutineInstancesJob, ComputeAnalyticsSnapshotJob, AIPlanGoalJob, AIReviewWeekJob
- [ ] Console scheduling (cron entries)

## Phase 6 — HTTP Layer
- [ ] Form Requests (Goal, Task, Routine)
- [ ] API Resources (Goal, Task, ScheduledSlot, Routine, RoutineInstance)
- [ ] Controllers (Goal, Task, Schedule, Routine, AIPlan, Analytics)
- [ ] routes/api.php
- [ ] routes/web.php (Inertia page routes)
- [ ] AIPlannerService (stubbed)

## Phase 7 — Vue Frontend: Layout + Dashboard + Goal Workspace
- [ ] AppLayout redesign (dark sidebar + top bar)
- [ ] Dashboard.vue
- [ ] goals/Show.vue (3-column workspace)
- [ ] GoalCard.vue, TaskItem.vue, TaskAttributeForm.vue components
- [ ] Pinia stores: useGoalStore, useScheduleStore

## Phase 8 — Vue Frontend: Task Board + Routine View + Analytics + AI Chat
- [ ] tasks/Board.vue (kanban)
- [ ] routines/Index.vue
- [ ] analytics/Index.vue
- [ ] ai/Chat.vue
- [ ] Remaining components: ScheduledSlot, RoutineStepItem, MomentumAlert, EnergyHeatmap, AIChat
- [ ] Pinia stores: useRoutineStore, useAnalyticsStore, useAIStore

## Phase 9 — Wayfinder + Pint + Final Polish
- [ ] Run wayfinder:generate
- [ ] Run vendor/bin/pint --dirty
- [ ] Fix TypeScript types/errors
- [ ] npm run build verify

## Phase 10 — Tests
- [ ] GoalServiceTest, TaskServiceTest
- [ ] DependencyServiceTest, GroupingServiceTest
- [ ] SchedulingServiceTest
- [ ] CapacityServiceTest, RoutineServiceTest, MomentumServiceTest
- [ ] API Feature Tests (all controllers)
