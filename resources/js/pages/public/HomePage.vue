<script setup lang="ts">
import {
    HandHeartIcon,
    MapPinIcon,
    PackageIcon,
    WrenchIcon,
} from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { useAuthStore } from '@/stores/auth';

const auth = useAuthStore();

const types = [
    {
        icon: PackageIcon,
        title: 'Doações',
        text: 'Alimentos, roupas, material escolar e o que mais a ONG precisar.',
    },
    {
        icon: WrenchIcon,
        title: 'Serviços',
        text: 'Profissionais que doam seu conhecimento: reparos, jurídico, tecnologia.',
    },
    {
        icon: HandHeartIcon,
        title: 'Voluntariado',
        text: 'Tempo e presença em eventos, aulas, cuidados e ações comunitárias.',
    },
];
</script>

<template>
    <section
        class="mx-auto grid max-w-6xl gap-10 px-4 py-16 md:grid-cols-2 md:items-center md:py-24"
    >
        <div class="space-y-6">
            <p
                class="text-primary flex items-center gap-2 text-sm font-medium"
            >
                <MapPinIcon class="size-4" aria-hidden="true" /> Ajuda perto de
                você
            </p>
            <h1
                class="text-4xl font-bold tracking-tight text-balance md:text-5xl"
            >
                Conectamos ONGs a quem quer ajudar.
            </h1>
            <p class="text-muted-foreground text-lg">
                ONGs publicam o que precisam. Voluntários encontram
                oportunidades próximas e demonstram interesse em poucos cliques.
            </p>
            <div class="flex flex-wrap gap-3">
                <template v-if="auth.isAuthenticated">
                    <Button size="lg" as-child
                        ><RouterLink :to="{ name: auth.homeRouteName }"
                            >Ir para meu painel</RouterLink
                        ></Button
                    >
                </template>
                <template v-else>
                    <Button size="lg" as-child
                        ><RouterLink
                            :to="{
                                name: 'register',
                                query: { tipo: 'volunteer' },
                            }"
                            >Quero ser voluntário</RouterLink
                        ></Button
                    >
                    <Button size="lg" variant="outline" as-child
                        ><RouterLink
                            :to="{
                                name: 'register',
                                query: { tipo: 'organization' },
                            }"
                            >Sou uma ONG</RouterLink
                        ></Button
                    >
                </template>
            </div>
        </div>
        <ul class="grid gap-4" aria-label="Tipos de solicitação">
            <li
                v-for="item in types"
                :key="item.title"
                class="flex gap-4 rounded-xl border p-5"
            >
                <component
                    :is="item.icon"
                    class="text-primary size-6 shrink-0"
                    aria-hidden="true"
                />
                <div>
                    <h2 class="font-semibold">{{ item.title }}</h2>
                    <p class="text-muted-foreground text-sm">{{ item.text }}</p>
                </div>
            </li>
        </ul>
    </section>
</template>
