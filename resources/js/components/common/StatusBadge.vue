<script setup lang="ts">
/**
 * Badge de status/tipo/prioridade. A cor nunca é o único indicador:
 * o texto do rótulo sempre acompanha (acessibilidade).
 */
import { computed } from 'vue';
import {
    REQUEST_PRIORITY_LABELS,
    REQUEST_STATUS_LABELS,
    REQUEST_TYPE_LABELS,
    REQUEST_VOLUNTEER_STATUS_LABELS,
} from '@/types';
import type {
    RequestPriority,
    RequestStatus,
    RequestType,
    RequestVolunteerStatus,
} from '@/types';
import { cn } from '@/lib/utils';

type Kind = 'status' | 'participation' | 'type' | 'priority';

const props = defineProps<{
    kind: Kind;
    value:
        | RequestStatus
        | RequestVolunteerStatus
        | RequestType
        | RequestPriority;
}>();

const COLORS: Record<string, string> = {
    // status da solicitação
    open: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/15 dark:text-emerald-300',
    in_progress: 'bg-sky-100 text-sky-800 dark:bg-sky-500/15 dark:text-sky-300',
    completed:
        'bg-violet-100 text-violet-800 dark:bg-violet-500/15 dark:text-violet-300',
    cancelled:
        'bg-zinc-200 text-zinc-700 dark:bg-zinc-500/20 dark:text-zinc-300',
    // participação
    interested:
        'bg-amber-100 text-amber-800 dark:bg-amber-500/15 dark:text-amber-300',
    approved:
        'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/15 dark:text-emerald-300',
    rejected:
        'bg-rose-100 text-rose-800 dark:bg-rose-500/15 dark:text-rose-300',
    // tipo
    donation:
        'bg-orange-100 text-orange-800 dark:bg-orange-500/15 dark:text-orange-300',
    service: 'bg-blue-100 text-blue-800 dark:bg-blue-500/15 dark:text-blue-300',
    volunteering:
        'bg-teal-100 text-teal-800 dark:bg-teal-500/15 dark:text-teal-300',
    // prioridade
    low: 'bg-zinc-100 text-zinc-700 dark:bg-zinc-500/15 dark:text-zinc-300',
    medium: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-500/15 dark:text-yellow-300',
    high: 'bg-orange-100 text-orange-800 dark:bg-orange-500/15 dark:text-orange-300',
    urgent: 'bg-red-100 text-red-800 dark:bg-red-500/15 dark:text-red-300',
};

const label = computed(() => {
    const maps: Record<Kind, Record<string, string>> = {
        status: REQUEST_STATUS_LABELS,
        participation: REQUEST_VOLUNTEER_STATUS_LABELS,
        type: REQUEST_TYPE_LABELS,
        priority: REQUEST_PRIORITY_LABELS,
    };

    return maps[props.kind][props.value] ?? props.value;
});

// "completed" em participação usa a mesma cor que em solicitação.
const color = computed(() => COLORS[props.value] ?? COLORS.cancelled);
</script>

<template>
    <span
        :class="
            cn(
                'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium whitespace-nowrap',
                color,
            )
        "
    >
        <span v-if="kind === 'priority'" class="sr-only">Prioridade:&nbsp;</span
        >{{ label }}
    </span>
</template>
