<script setup lang="ts">
import { Button } from '@/components/ui/button';

const organizationSteps = [
    'Cadastre a ONG e informe endereço, contatos e localização.',
    'Publique solicitações de doação, serviço ou voluntariado.',
    'Receba interessados, analise o perfil e aprove ou recuse.',
    'Após a atividade, conclua a solicitação e registre a participação.',
];

const volunteerSteps = [
    'Crie seu perfil e escolha suas áreas de interesse.',
    'Autorize (se quiser) o uso da sua localização para ver ONGs próximas.',
    'Pesquise solicitações por tipo, categoria, prioridade e distância.',
    'Demonstre interesse e acompanhe suas atividades e histórico.',
];
</script>

<template>
    <div class="mx-auto max-w-4xl space-y-12 px-4 py-16">
        <header class="space-y-3 text-center">
            <h1 class="text-3xl font-bold tracking-tight">Como funciona</h1>
            <p class="text-muted-foreground">
                Um fluxo simples, do pedido de ajuda à atividade concluída.
            </p>
        </header>

        <div class="grid gap-8 md:grid-cols-2">
            <section
                aria-labelledby="ong-title"
                class="space-y-4 rounded-xl border p-6"
            >
                <h2 id="ong-title" class="text-xl font-semibold">Para ONGs</h2>
                <ol class="list-decimal space-y-2 pl-5 text-sm">
                    <li v-for="step in organizationSteps" :key="step">
                        {{ step }}
                    </li>
                </ol>
            </section>
            <section
                aria-labelledby="vol-title"
                class="space-y-4 rounded-xl border p-6"
            >
                <h2 id="vol-title" class="text-xl font-semibold">
                    Para voluntários
                </h2>
                <ol class="list-decimal space-y-2 pl-5 text-sm">
                    <li v-for="step in volunteerSteps" :key="step">
                        {{ step }}
                    </li>
                </ol>
            </section>
        </div>

        <section
            aria-labelledby="privacy-title"
            class="bg-muted/50 space-y-2 rounded-xl p-6 text-sm"
        >
            <h2 id="privacy-title" class="font-semibold">Privacidade (LGPD)</h2>
            <p>
                Seus dados de contato só são compartilhados com a ONG depois que
                ela aprova sua participação. A localização é usada apenas com
                seu consentimento e pode ser removida a qualquer momento no
                perfil. Você pode excluir sua conta e seus dados nas
                configurações.
            </p>
        </section>

        <div class="text-center">
            <Button size="lg" as-child
                ><RouterLink :to="{ name: 'register' }"
                    >Criar minha conta</RouterLink
                ></Button
            >
        </div>
    </div>
</template>
