<script setup lang="ts">
import FormField from '@/components/common/FormField.vue';
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

const { login } = useAuth();
const form = useForm({ email: '', password: '', remember: false });

const submit = () => form.submit(() => login({ ...form.data }));
</script>

<template>
    <Card>
        <CardHeader>
            <CardTitle as="h1" class="text-xl">Entrar</CardTitle>
            <CardDescription
                >Acesse sua conta de ONG ou voluntário.</CardDescription
            >
        </CardHeader>
        <CardContent>
            <form class="grid gap-4" novalidate @submit.prevent="submit">
                <p
                    v-if="form.message.value && !form.errors.email"
                    role="alert"
                    class="text-destructive text-sm"
                >
                    {{ form.message.value }}
                </p>
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
                        autofocus
                    />
                </FormField>
                <FormField
                    id="password"
                    v-slot="field"
                    label="Senha"
                    :error="form.errors.password"
                    required
                >
                    <Input
                        v-bind="field"
                        v-model="form.data.password"
                        type="password"
                        autocomplete="current-password"
                    />
                </FormField>
                <label class="flex items-center gap-2 text-sm">
                    <input
                        v-model="form.data.remember"
                        type="checkbox"
                        class="accent-primary size-4"
                    />
                    Manter conectado
                </label>
                <Button type="submit" :disabled="form.processing.value">
                    {{ form.processing.value ? 'Entrando…' : 'Entrar' }}
                </Button>
                <p class="text-muted-foreground text-center text-sm">
                    Ainda não tem conta?
                    <RouterLink
                        :to="{ name: 'register' }"
                        class="text-primary underline-offset-4 hover:underline"
                        >Cadastre-se</RouterLink
                    >
                </p>
            </form>
        </CardContent>
    </Card>
</template>
