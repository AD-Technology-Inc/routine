export type GoalStatus = 'active' | 'paused' | 'completed' | 'archived';
export type TaskStatus = 'pending' | 'in_progress' | 'completed' | 'skipped' | 'archived';
export type Priority = 'low' | 'medium' | 'high' | 'critical';
export type TaskType = 'learning' | 'practice' | 'execution' | 'review' | 'planning';
export type Flexibility = 'fixed' | 'flexible' | 'optional';
export type EnergyLevel = 'low' | 'medium' | 'high';
export type TimeBlock = 'morning' | 'afternoon' | 'evening' | 'anytime';
export type SlotStatus = 'pending' | 'completed' | 'skipped';
export type RoutineFrequency = 'daily' | 'weekdays' | 'weekends' | 'weekly' | 'custom';

export interface TaskAttribute {
    priority: Priority;
    type: TaskType;
    flexibility: Flexibility;
    reschedule_policy: 'strict' | 'soft' | 'skip_allowed';
    energy_level: EnergyLevel;
    grouping_key: string | null;
    can_merge: boolean;
    can_split: boolean;
}

export interface Task {
    id: number;
    goal_id: number;
    parent_task_id: number | null;
    title: string;
    estimated_minutes: number;
    actual_minutes: number | null;
    order_index: number;
    status: TaskStatus;
    due_date: string | null;
    created_at: string;
    updated_at: string;
    // Flattened attributes (also nested under `attribute`)
    priority: Priority | null;
    type: TaskType | null;
    flexibility: Flexibility | null;
    reschedule_policy: string | null;
    energy_level: EnergyLevel | null;
    grouping_key: string | null;
    can_merge: boolean;
    can_split: boolean;
    attribute: TaskAttribute | null;
}

export interface Goal {
    id: number;
    user_id: number;
    title: string;
    description: string | null;
    status: GoalStatus;
    target_date: string | null;
    color: string | null;
    order_index: number;
    created_at: string;
    updated_at: string;
    tasks?: Task[];
    routines?: Routine[];
}

export interface ScheduledSlot {
    id: number;
    user_id: number;
    task_id: number | null;
    grouping_key: string | null;
    date: string;
    time_block: TimeBlock;
    allocated_minutes: number;
    slot_index: number;
    is_merged: boolean;
    merged_task_ids: number[] | null;
    status: SlotStatus;
    task?: Task;
}

export interface RoutineStep {
    id: number;
    routine_id: number;
    title: string;
    estimated_minutes: number;
    energy_level: EnergyLevel;
    order_index: number;
}

export interface Routine {
    id: number;
    goal_id: number | null;
    user_id: number;
    title: string;
    frequency: RoutineFrequency;
    custom_days: number[] | null;
    time_block: TimeBlock;
    is_active: boolean;
    created_at: string;
    updated_at: string;
    steps?: RoutineStep[];
}

export interface RoutineInstance {
    id: number;
    routine_id: number;
    user_id: number;
    date: string;
    status: 'pending' | 'completed' | 'partial' | 'skipped';
    completed_steps: number[];
    routine?: Routine;
}

export interface AnalyticsSnapshot {
    id: number;
    user_id: number;
    date: string;
    total_tasks_scheduled: number;
    total_tasks_completed: number;
    total_tasks_skipped: number;
    total_tasks_missed: number;
    completion_rate: number;
    avg_task_duration_minutes: number | null;
}
