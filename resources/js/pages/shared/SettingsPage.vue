<script setup lang="ts">
/**
 * Configurações da conta (ONG e voluntário): dados de acesso, avatar,
 * senha, aparência e exclusão da conta (direito de eliminação — LGPD).
 */
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import ConfirmDialog from '@/components/common/ConfirmDialog.vue';
import FormField from '@/components/common/FormField.vue';
import InputError from '@/components/common/InputError.vue';
import PageHeader from '@/components/common/PageHeader.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useAppearance } from '@/composables/useAppearance';
import { useForm } from '@/composables/useForm';
import { getInitials } from '@/composables/useInitials';
import { useNotification } from '@/composables/useNotification';
import { accountService, isApiError } from '@/services';
import { useAuthStore } from '@/stores/auth';
import type { Appearance } from '@/types';

const auth = useAuthStore();
const router = useRouter();
const notify = useNotification();
const { appearance, updateAppearance } = useAppearance();

const account = useForm({
    name: auth.user?.name ?? '',
    email: auth.user?.email ?? '',
    phone: auth.user?.phone ?? '',
});
const password = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});
const deletion = useForm({ password: '' });
const confirmDelete = ref(false);
const avatarError = ref<string | null>(null);
const uploading = ref(false);

async function saveAccount(): Promise<void> {
    const user = await account.submit(() =>
        accountService.update({ ...account.data }),
    );

    if (user) {
        auth.setUser({ ...auth.user!, ...user });
        notify.success('Dados atualizados.');
    }
}

async function savePassword(): Promise<void> {
    const ok = await password.submit(() =>
        accountService.updatePassword({ ...password.data }).then(() => true),
    );

    if (ok) {
        password.reset();
        notify.success('Senha alterada.');
    }
}

async function onAvatar(event: Event): Promise<void> {
    const file = (event.target as HTMLInputElement).files?.[0];
    avatarError.value = null;

    if (!file) {
        return;
    }

    // Validação rápida no cliente; a validação definitiva é feita na API.
    if (file.size > 2 * 1024 * 1024) {
        avatarError.value = 'A imagem deve ter no máximo 2 MB.';

        return;
    }

    uploading.value = true;

    try {
        const user = await accountService.uploadAvatar(file);
        auth.setUser({ ...auth.user!, avatar_url: user.avatar_url });
        notify.success('Foto atualizada.');
    } catch (e) {
        avatarError.value = isApiError(e)
            ? (e.errors.avatar?.[0] ?? e.message)
            : 'Falha no envio.';
    } finally {
        uploading.value = false;
    }
}

async function deleteAccount(): Promise<void> {
    const ok = await deletion.submit(() =>
        accountService.deleteAccount(deletion.data.password).then(() => true),
    );

    if (ok) {
        confirmDelete.value = false;
        notify.info('Sua conta e seus dados foram excluídos.');
        auth.clear();
        await router.push({ name: 'home' });
    }
}

const appearanceOptions: { value: Appearance; label: string }[] = [
    { value: 'light', label: 'Claro' },
    { value: 'dark', label: 'Escuro' },
    { value: 'system', label: 'Sistema' },
];
</script>

<template>
    <PageHeader
        title="Configurações"
        description="Gerencie sua conta e preferências."
    />

    <div class="grid gap-6">
        <Card>
            <CardHeader><CardTitle as="h2">Conta</CardTitle></CardHeader>
            <CardContent class="grid gap-6">
                <div class="flex items-center gap-4">
                    <Avatar class="size-16">
                        <AvatarImage
                            v-if="auth.user?.avatar_url"
                            :src="auth.user.avatar_url"
                            alt="Sua foto"
                        />
                        <AvatarFallback>{{
                            getInitials(auth.user?.name)
                        }}</AvatarFallback>
                    </Avatar>
                    <div class="grid gap-1">
                        <Label for="avatar">Foto de perfil</Label>
                        <input
                            id="avatar"
                            type="file"
                            accept="image/jpeg,image/png,image/webp"
                            class="text-sm"
                            :disabled="uploading"
                            aria-describedby="avatar-hint"
                            @change="onAvatar"
                        />
                        <p
                            id="avatar-hint"
                            class="text-muted-foreground text-xs"
                        >
                            JPG, PNG ou WebP, até 2 MB.
                        </p>
                        <InputError :message="avatarError" />
                    </div>
                </div>
                <form
                    class="grid gap-4 sm:grid-cols-2"
                    novalidate
                    @submit.prevent="saveAccount"
                >
                    <FormField
                        id="account-name"
                        v-slot="field"
                        label="Nome"
                        :error="account.errors.name"
                        required
                    >
                        <Input
                            v-bind="field"
                            v-model="account.data.name"
                            autocomplete="name"
                        />
                    </FormField>
                    <FormField
                        id="account-email"
                        v-slot="field"
                        label="E-mail"
                        :error="account.errors.email"
                        required
                    >
                        <Input
                            v-bind="field"
                            v-model="account.data.email"
                            type="email"
                            autocomplete="email"
                        />
                    </FormField>
                    <FormField
                        id="account-phone"
                        v-slot="field"
                        label="Telefone"
                        :error="account.errors.phone"
                    >
                        <Input
                            v-bind="field"
                            v-model="account.data.phone"
                            type="tel"
                            autocomplete="tel"
                        />
                    </FormField>
                    <div class="flex items-end justify-end sm:col-span-2">
                        <Button
                            type="submit"
                            :disabled="account.processing.value"
                            >Salvar</Button
                        >
                    </div>
                </form>
            </CardContent>
        </Card>

        <Card>
            <CardHeader><CardTitle as="h2">Senha</CardTitle></CardHeader>
            <CardContent>
                <form
                    class="grid gap-4 sm:grid-cols-3"
                    novalidate
                    @submit.prevent="savePassword"
                >
                    <FormField
                        id="current_password"
                        v-slot="field"
                        label="Senha atual"
                        :error="password.errors.current_password"
                        required
                    >
                        <Input
                            v-bind="field"
                            v-model="password.data.current_password"
                            type="password"
                            autocomplete="current-password"
                        />
                    </FormField>
                    <FormField
                        id="new_password"
                        v-slot="field"
                        label="Nova senha"
                        :error="password.errors.password"
                        required
                    >
                        <Input
                            v-bind="field"
                            v-model="password.data.password"
                            type="password"
                            autocomplete="new-password"
                        />
                    </FormField>
                    <FormField
                        id="new_password_confirmation"
                        v-slot="field"
                        label="Confirmar nova senha"
                        :error="password.errors.password_confirmation"
                        required
                    >
                        <Input
                            v-bind="field"
                            v-model="password.data.password_confirmation"
                            type="password"
                            autocomplete="new-password"
                        />
                    </FormField>
                    <div class="flex justify-end sm:col-span-3">
                        <Button
                            type="submit"
                            :disabled="password.processing.value"
                            >Alterar senha</Button
                        >
                    </div>
                </form>
            </CardContent>
        </Card>

        <Card>
            <CardHeader><CardTitle as="h2">Aparência</CardTitle></CardHeader>
            <CardContent>
                <div
                    role="radiogroup"
                    aria-label="Tema"
                    class="flex flex-wrap gap-2"
                >
                    <Button
                        v-for="option in appearanceOptions"
                        :key="option.value"
                        role="radio"
                        :aria-checked="appearance === option.value"
                        :variant="
                            appearance === option.value ? 'default' : 'outline'
                        "
                        size="sm"
                        @click="updateAppearance(option.value)"
                    >
                        {{ option.label }}
                    </Button>
                </div>
            </CardContent>
        </Card>

        <Card class="border-destructive/40">
            <CardHeader>
                <CardTitle as="h2">Excluir conta</CardTitle>
                <CardDescription
                    >Remove definitivamente sua conta, perfil e dados pessoais.
                    Esta ação não pode ser desfeita.</CardDescription
                >
            </CardHeader>
            <CardContent>
                <Button variant="destructive" @click="confirmDelete = true"
                    >Excluir minha conta</Button
                >
            </CardContent>
        </Card>
    </div>

    <ConfirmDialog
        v-model:open="confirmDelete"
        title="Excluir sua conta?"
        description="Digite sua senha para confirmar. Todos os seus dados serão removidos."
        confirm-label="Excluir definitivamente"
        destructive
        :loading="deletion.processing.value"
        @confirm="deleteAccount"
    >
        <FormField
            id="delete-password"
            v-slot="field"
            label="Senha"
            :error="deletion.errors.password"
            required
        >
            <Input
                v-bind="field"
                v-model="deletion.data.password"
                type="password"
                autocomplete="current-password"
            />
        </FormField>
    </ConfirmDialog>
</template>
