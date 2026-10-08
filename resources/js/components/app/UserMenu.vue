<script setup lang="ts">
import { ChevronsUpDownIcon, LogOutIcon, SettingsIcon } from '@lucide/vue';
import { computed } from 'vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { SidebarMenuButton } from '@/components/ui/sidebar';
import { useAuth } from '@/composables/useAuth';
import { getInitials } from '@/composables/useInitials';
import { USER_TYPE_LABELS } from '@/types';

const { user, isOrganization, logout } = useAuth();

const settingsRoute = computed(() => ({
    name: isOrganization.value ? 'organization.settings' : 'volunteer.settings',
}));
</script>

<template>
    <DropdownMenu v-if="user">
        <DropdownMenuTrigger as-child>
            <SidebarMenuButton size="lg" aria-label="Menu da conta">
                <Avatar class="size-8 rounded-lg">
                    <AvatarImage
                        v-if="user.avatar_url"
                        :src="user.avatar_url"
                        alt=""
                    />
                    <AvatarFallback class="rounded-lg">{{
                        getInitials(user.name)
                    }}</AvatarFallback>
                </Avatar>
                <span class="grid flex-1 text-left text-sm leading-tight">
                    <span class="truncate font-medium">{{ user.name }}</span>
                    <span class="text-muted-foreground truncate text-xs">{{
                        USER_TYPE_LABELS[user.type]
                    }}</span>
                </span>
                <ChevronsUpDownIcon class="ml-auto size-4" aria-hidden="true" />
            </SidebarMenuButton>
        </DropdownMenuTrigger>
        <DropdownMenuContent class="w-56" side="top" align="end">
            <DropdownMenuLabel class="truncate">{{
                user.email
            }}</DropdownMenuLabel>
            <DropdownMenuSeparator />
            <DropdownMenuItem as-child>
                <RouterLink :to="settingsRoute"
                    ><SettingsIcon aria-hidden="true" />
                    Configurações</RouterLink
                >
            </DropdownMenuItem>
            <DropdownMenuItem @select="logout"
                ><LogOutIcon aria-hidden="true" /> Sair</DropdownMenuItem
            >
        </DropdownMenuContent>
    </DropdownMenu>
</template>
