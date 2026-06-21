<script setup lang="ts">
import { reactive, watch } from 'vue';
import type { Task } from '@/types';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';

const props = defineProps<{
    modelValue?: Partial<Task>;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: Partial<Task>];
}>();

const form = reactive<Partial<Task>>({
    priority: props.modelValue?.priority ?? 'medium',
    type: props.modelValue?.type ?? 'execution',
    flexibility: props.modelValue?.flexibility ?? 'flexible',
    reschedule_policy: props.modelValue?.reschedule_policy ?? 'soft',
    energy_level: props.modelValue?.energy_level ?? 'medium',
    grouping_key: props.modelValue?.grouping_key ?? '',
    can_merge: props.modelValue?.can_merge ?? false,
    can_split: props.modelValue?.can_split ?? false,
});

watch(form, (val) => emit('update:modelValue', { ...val }), { deep: true });
</script>

<template>
    <div class="grid gap-4 sm:grid-cols-2">
        <!-- Priority -->
        <div class="space-y-1.5">
            <Label for="attr-priority">Priority</Label>
            <Select v-model="form.priority">
                <SelectTrigger id="attr-priority">
                    <SelectValue placeholder="Priority" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="critical">🔴 Critical</SelectItem>
                    <SelectItem value="high">🟠 High</SelectItem>
                    <SelectItem value="medium">🔵 Medium</SelectItem>
                    <SelectItem value="low">⚪ Low</SelectItem>
                </SelectContent>
            </Select>
        </div>

        <!-- Type -->
        <div class="space-y-1.5">
            <Label for="attr-type">Task Type</Label>
            <Select v-model="form.type">
                <SelectTrigger id="attr-type">
                    <SelectValue placeholder="Type" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="learning">Learning</SelectItem>
                    <SelectItem value="practice">Practice</SelectItem>
                    <SelectItem value="execution">Execution</SelectItem>
                    <SelectItem value="review">Review</SelectItem>
                    <SelectItem value="planning">Planning</SelectItem>
                </SelectContent>
            </Select>
        </div>

        <!-- Flexibility -->
        <div class="space-y-1.5">
            <Label for="attr-flexibility">Flexibility</Label>
            <Select v-model="form.flexibility">
                <SelectTrigger id="attr-flexibility">
                    <SelectValue placeholder="Flexibility" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="fixed">Fixed</SelectItem>
                    <SelectItem value="flexible">Flexible</SelectItem>
                    <SelectItem value="optional">Optional</SelectItem>
                </SelectContent>
            </Select>
        </div>

        <!-- Energy Level -->
        <div class="space-y-1.5">
            <Label for="attr-energy">Energy Required</Label>
            <Select v-model="form.energy_level">
                <SelectTrigger id="attr-energy">
                    <SelectValue placeholder="Energy" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="high">⚡ High</SelectItem>
                    <SelectItem value="medium">🔥 Medium</SelectItem>
                    <SelectItem value="low">🌙 Low</SelectItem>
                </SelectContent>
            </Select>
        </div>

        <!-- Reschedule Policy -->
        <div class="space-y-1.5">
            <Label for="attr-reschedule">Reschedule Policy</Label>
            <Select v-model="form.reschedule_policy">
                <SelectTrigger id="attr-reschedule">
                    <SelectValue placeholder="Policy" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="strict">Strict</SelectItem>
                    <SelectItem value="soft">Soft</SelectItem>
                    <SelectItem value="skip_allowed">Skip Allowed</SelectItem>
                </SelectContent>
            </Select>
        </div>

        <!-- Grouping Key -->
        <div class="space-y-1.5">
            <Label for="attr-grouping">Grouping Key</Label>
            <Input
                id="attr-grouping"
                v-model="form.grouping_key"
                placeholder="e.g. interview_prep"
                class="font-mono text-sm"
            />
        </div>

        <!-- Booleans -->
        <div class="flex items-center gap-6 sm:col-span-2">
            <div class="flex items-center gap-2">
                <Checkbox id="attr-can-split" v-model:checked="form.can_split" />
                <Label for="attr-can-split" class="cursor-pointer text-sm">Can Split</Label>
            </div>
            <div class="flex items-center gap-2">
                <Checkbox id="attr-can-merge" v-model:checked="form.can_merge" />
                <Label for="attr-can-merge" class="cursor-pointer text-sm">Can Merge</Label>
            </div>
        </div>
    </div>
</template>
