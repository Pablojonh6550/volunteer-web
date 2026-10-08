<script setup lang="ts">
/**
 * Navegação principal do ambiente logado, montada conforme o perfil.
 */
import {
    ActivityIcon,
    ClipboardListIcon,
    LayoutDashboardIcon,
    MapIcon,
    PlusCircleIcon,
    SearchIcon,
    SettingsIcon,
    UserIcon,
} from '@lucide/vue';
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import AppLogo from '@/components/app/AppLogo.vue';
import UserMenu from '@/components/app/UserMenu.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarGroup,
    SidebarGroupLabel,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { useAuthStore } from '@/stores/auth';
import type { NavItem } from '@/types';

const auth = useAuthStore();
const route = useRoute();

const items = computed<NavItem[]>(() =>
    auth.isOrganization
        ? [
              {
                  title: 'Painel',
                  to: { name: 'organization.dashboard' },
                  icon: LayoutDashboardIcon,
              },
              {
                  title: 'Solicitações',
                  to: { name: 'organization.requests' },
                  icon: ClipboardListIcon,
              },
              {
                  title: 'Nova solicitação',
                  to: { name: 'organization.requests.create' },
                  icon: PlusCircleIcon,
              },
              {
                  title: 'Perfil da ONG',
                  to: { name: 'organization.profile' },
                  icon: UserIcon,
              },
              {
                  title: 'Configurações',
                  to: { name: 'organization.settings' },
                  icon: SettingsIcon,
              },
          ]
        : [
              {
                  title: 'Painel',
                  to: { name: 'volunteer.dashboard' },
                  icon: LayoutDashboardIcon,
              },
              {
                  title: 'Pesquisar solicitações',
                  to: { name: 'volunteer.requests' },
                  icon: SearchIcon,
              },
              {
                  title: 'Mapa de ONGs',
                  to: { name: 'volunteer.map' },
                  icon: MapIcon,
              },
              {
                  title: 'Minhas atividades',
                  to: { name: 'volunteer.activities' },
                  icon: ActivityIcon,
              },
              {
                  title: 'Meu perfil',
                  to: { name: 'volunteer.profile' },
                  icon: UserIcon,
              },
              {
                  title: 'Configurações',
                  to: { name: 'volunteer.settings' },
                  icon: SettingsIcon,
              },
          ],
);

/** Item ativo: mesma rota ou rota "filha" (ex.: detalhes dentro de Solicitações). */
function isActive(item: NavItem): boolean {
    const name =
        typeof item.to === 'object' && 'name' in item.to
            ? String(item.to.name)
            : '';
    const current = String(route.name ?? '');

    if (name.endsWith('dashboard')) {
        return current === name;
    }

    return (
        current === name ||
        (current.startsWith(name.replace(/s$/, '')) &&
            !current.endsWith('create'))
    );
}
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <RouterLink
                :to="{ name: auth.homeRouteName }"
                class="p-2"
                aria-label="Ir para o painel"
            >
                <AppLogo />
            </RouterLink>
        </SidebarHeader>
        <SidebarContent>
            <SidebarGroup>
                <SidebarGroupLabel>{{
                    auth.isOrganization ? 'ONG' : 'Voluntário'
                }}</SidebarGroupLabel>
                <nav aria-label="Navegação principal">
                    <SidebarMenu>
                        <SidebarMenuItem
                            v-for="item in items"
                            :key="item.title"
                        >
                            <SidebarMenuButton
                                as-child
                                :is-active="isActive(item)"
                                :tooltip="item.title"
                            >
                                <RouterLink
                                    :to="item.to"
                                    :aria-current="
                                        isActive(item) ? 'page' : undefined
                                    "
                                >
                                    <component
                                        :is="item.icon"
                                        aria-hidden="true"
                                    />
                                    <span>{{ item.title }}</span>
                                </RouterLink>
                            </SidebarMenuButton>
                        </SidebarMenuItem>
                    </SidebarMenu>
                </nav>
            </SidebarGroup>
        </SidebarContent>
        <SidebarFooter>
            <SidebarMenu>
                <SidebarMenuItem><UserMenu /></SidebarMenuItem>
            </SidebarMenu>
        </SidebarFooter>
    </Sidebar>
</template>
