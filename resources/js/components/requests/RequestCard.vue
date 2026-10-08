<script setup lang="ts">
import { CalendarIcon, MapPinIcon, UsersIcon } from '@lucide/vue';
import type { RouteLocationRaw } from 'vue-router';
import StatusBadge from '@/components/common/StatusBadge.vue';
import {
    Card,
    CardContent,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import type { HelpRequest } from '@/types';
import { formatDistance, formatPeriod } from '@/utils/format';

defineProps<{
    request: HelpRequest;
    to: RouteLocationRaw;
    /** Exibe o status (útil para a ONG; voluntários só veem abertas). */
    showStatus?: boolean;
}>();
</script>

<template>
    <Card
        as="article"
        class="focus-within:ring-ring/50 relative gap-3 transition-shadow focus-within:ring-[3px] hover:shadow-md"
    >
        <CardHeader class="gap-2">
            <div class="flex flex-wrap items-center gap-2">
                <StatusBadge kind="type" :value="request.type" />
                <StatusBadge kind="priority" :value="request.priority" />
                <StatusBadge
                    v-if="showStatus"
                    kind="status"
                    :value="request.status"
                />
            </div>
            <CardTitle as="h2" class="text-base leading-snug">
                <!-- O link cobre o card inteiro (::after) mantendo um único alvo de foco. -->
                <RouterLink
                    :to="to"
                    class="after:absolute after:inset-0 focus-visible:outline-none"
                >
                    {{ request.title }}
                </RouterLink>
            </CardTitle>
            <p
                v-if="request.organization"
                class="text-muted-foreground text-sm"
            >
                {{ request.organization.name }}
            </p>
        </CardHeader>
        <CardContent>
            <p class="text-muted-foreground line-clamp-2 text-sm">
                {{ request.description }}
            </p>
        </CardContent>
        <CardFooter
            class="text-muted-foreground flex flex-wrap gap-x-4 gap-y-1 text-xs"
        >
            <span class="flex items-center gap-1"
                ><CalendarIcon class="size-3.5" aria-hidden="true" />{{
                    formatPeriod(request.start_date, request.end_date)
                }}</span
            >
            <span
                v-if="request.distance_km != null"
                class="flex items-center gap-1"
            >
                <MapPinIcon class="size-3.5" aria-hidden="true" /><span
                    class="sr-only"
                    >Distância:</span
                >{{ formatDistance(request.distance_km) }}
            </span>
            <span
                v-if="request.interested_count != null"
                class="flex items-center gap-1"
            >
                <UsersIcon class="size-3.5" aria-hidden="true" />{{
                    request.interested_count
                }}
                interessado(s)
            </span>
            <span v-if="request.category">{{ request.category.name }}</span>
        </CardFooter>
    </Card>
</template>
