<script setup lang="ts">
import { BuildingIcon, UserIcon } from '@lucide/vue';
import { useRoute } from 'vue-router';
import FormField from '@/components/common/FormField.vue';
import InputError from '@/components/common/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { useAuth } from '@/composables/useAuth';
import { useForm } from '@/composables/useForm';
import type { UserType } from '@/types';
import { cn } from '@/lib/utils';

const route = useRoute();
const { register } = useAuth();

const initialType: UserType =
    route.query.tipo === 'organization' ? 'organization' : 'volunteer';

const form = useForm({
    type: initialType as UserType,
    name: '',
    organization_name: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
    terms: false,
});

const profiles = [
    { value: 'volunteer' as const, label: 'Sou voluntário', icon: UserIcon },
    {
        value: 'organization' as const,
        label: 'Sou uma ONG',
        icon: BuildingIcon,
    },
];

const submit = () =>
    form.submit(() =>
        register({
            ...form.data,
            organization_name:
                form.data.type === 'organization'
                    ? form.data.organization_name
                    : undefined,
        }),
    );
</script>

<template>
    <Card>
        <CardHeader>
            <CardTitle as="h1" class="text-xl">Criar conta</CardTitle>
            <CardDescription>Escolha seu perfil para começar.</CardDescription>
        </CardHeader>
        <CardContent>
            <form class="grid gap-4" novalidate @submit.prevent="submit">
                <fieldset>
                    <legend class="mb-2 text-sm font-medium">
                        Tipo de conta
                    </legend>
                    <div class="grid grid-cols-2 gap-2">
                        <label
                            v-for="profile in profiles"
                            :key="profile.value"
                            :class="
                                cn(
                                    'has-focus-visible:ring-ring/50 flex cursor-pointer items-center gap-2 rounded-lg border p-3 text-sm has-focus-visible:ring-[3px]',
                                    form.data.type === profile.value &&
                                        'border-primary bg-primary/5',
                                )
                            "
                        >
                            <input
                                v-model="form.data.type"
                                type="radio"
                                name="type"
                                :value="profile.value"
                                class="sr-only"
                            />
                            <component
                                :is="profile.icon"
                                class="size-4"
                                aria-hidden="true"
                            />
                            {{ profile.label }}
                        </label>
                    </div>
                    <InputError :message="form.errors.type" />
                </fieldset>

                <FormField
                    v-if="form.data.type === 'organization'"
                    id="organization_name"
                    v-slot="field"
                    label="Nome da ONG"
                    :error="form.errors.organization_name"
                    required
                >
                    <Input
                        v-bind="field"
                        v-model="form.data.organization_name"
                        autocomplete="organization"
                    />
                </FormField>
                <FormField
                    id="name"
                    v-slot="field"
                    :label="
                        form.data.type === 'organization'
                            ? 'Nome do responsável'
                            : 'Nome completo'
                    "
                    :error="form.errors.name"
                    required
                >
                    <Input
                        v-bind="field"
                        v-model="form.data.name"
                        autocomplete="name"
                    />
                </FormField>
                <FormField
                    id="email"
                    v-slot="field"
                    label="E-mail"
                    :error="form.errors.email"
                    required
                >
                    <Input
                        v-bind="field"
                        v-model="form.data.email"
                        type="email"
                        autocomplete="email"
                    />
                </FormField>
                <FormField
                    id="phone"
                    v-slot="field"
                    label="Telefone"
                    :error="form.errors.phone"
                >
                    <Input
                        v-bind="field"
                        v-model="form.data.phone"
                        type="tel"
                        autocomplete="tel"
                    />
                </FormField>
                <FormField
                    id="password"
                    v-slot="field"
                    label="Senha"
                    hint="Mínimo de 8 caracteres."
                    :error="form.errors.password"
                    required
                >
                    <Input
                        v-bind="field"
                        v-model="form.data.password"
                        type="password"
                        autocomplete="new-password"
                    />
                </FormField>
                <FormField
                    id="password_confirmation"
                    v-slot="field"
                    label="Confirme a senha"
                    :error="form.errors.password_confirmation"
                    required
                >
                    <Input
                        v-bind="field"
                        v-model="form.data.password_confirmation"
                        type="password"
                        autocomplete="new-password"
                    />
                </FormField>

                <div>
                    <label class="flex items-start gap-2 text-sm">
                        <input
                            v-model="form.data.terms"
                            type="checkbox"
                            class="accent-primary mt-0.5 size-4"
                            :aria-invalid="form.errors.terms ? true : undefined"
                        />
                        <span
                            >Li e aceito os termos de uso e a política de
                            privacidade, incluindo o tratamento dos meus dados
                            pessoais conforme a LGPD.</span
                        >
                    </label>
                    <InputError :message="form.errors.terms" />
                </div>

                <Button type="submit" :disabled="form.processing.value">
                    {{
                        form.processing.value ? 'Criando conta…' : 'Criar conta'
                    }}
                </Button>
                <p class="text-muted-foreground text-center text-sm">
                    Já tem conta?
                    <RouterLink
                        :to="{ name: 'login' }"
                        class="text-primary underline-offset-4 hover:underline"
                        >Entrar</RouterLink
                    >
                </p>
            </form>
        </CardContent>
    </Card>
</template>
