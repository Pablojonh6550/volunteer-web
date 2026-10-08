<script setup lang="ts">
/**
 * Perfil do voluntário: dados pessoais, áreas de interesse, disponibilidade
 * e localização (com consentimento explícito — LGPD).
 */
import { ShieldCheckIcon } from '@lucide/vue';
import FormField from '@/components/common/FormField.vue';
import PageHeader from '@/components/common/PageHeader.vue';
import LocationPicker from '@/components/map/LocationPicker.vue';
import AddressFields from '@/components/profile/AddressFields.vue';
import CategoryPicker from '@/components/profile/CategoryPicker.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { useForm } from '@/composables/useForm';
import { useNotification } from '@/composables/useNotification';
import { useVolunteer } from '@/composables/useVolunteer';
import { useAuthStore } from '@/stores/auth';

const auth = useAuthStore();
const notify = useNotification();
const { update } = useVolunteer();
const v = auth.volunteer;

const form = useForm({
    birth_date: v?.birth_date ?? null,
    phone: v?.phone ?? null,
    bio: v?.bio ?? null,
    available: v?.available ?? true,
    category_ids: (v?.categories ?? []).map((c) => c.id),
    location_consent: v?.location_consent ?? false,
    latitude: v?.latitude ?? null,
    longitude: v?.longitude ?? null,
    address: {
        address: v?.address ?? null,
        number: v?.number ?? null,
        neighborhood: v?.neighborhood ?? null,
        city: v?.city ?? null,
        state: v?.state ?? null,
        zip_code: v?.zip_code ?? null,
    },
});

async function submit(): Promise<void> {
    const { address, ...rest } = form.data;
    const saved = await form.submit(() =>
        update({
            ...rest,
            ...address,
            latitude: rest.location_consent ? rest.latitude : null,
            longitude: rest.location_consent ? rest.longitude : null,
        }),
    );

    if (saved) {
        form.data.latitude = saved.latitude ?? null;
        form.data.longitude = saved.longitude ?? null;
        notify.success('Perfil atualizado.');
    }
}
</script>

<template>
    <PageHeader
        title="Meu perfil"
        description="Essas informações ajudam as ONGs a conhecer você."
    />

    <form class="grid gap-6" novalidate @submit.prevent="submit">
        <Card>
            <CardHeader><CardTitle as="h2">Sobre você</CardTitle></CardHeader>
            <CardContent class="grid gap-4 sm:grid-cols-2">
                <FormField
                    id="birth_date"
                    v-slot="field"
                    label="Data de nascimento"
                    :error="form.errors.birth_date"
                >
                    <Input
                        v-bind="field"
                        v-model="form.data.birth_date"
                        type="date"
                        autocomplete="bday"
                    />
                </FormField>
                <FormField
                    id="phone"
                    v-slot="field"
                    label="Telefone"
                    :error="form.errors.phone"
                    hint="Compartilhado apenas com ONGs que aprovarem sua participação."
                >
                    <Input
                        v-bind="field"
                        v-model="form.data.phone"
                        type="tel"
                        autocomplete="tel"
                    />
                </FormField>
                <FormField
                    id="bio"
                    v-slot="field"
                    label="Apresentação"
                    :error="form.errors.bio"
                    class="sm:col-span-2"
                >
                    <Textarea
                        v-bind="field"
                        v-model="form.data.bio"
                        rows="4"
                        placeholder="Conte suas habilidades, disponibilidade de horários…"
                    />
                </FormField>
                <label class="flex items-center gap-2 text-sm sm:col-span-2">
                    <input
                        v-model="form.data.available"
                        type="checkbox"
                        class="accent-primary size-4"
                    />
                    Estou disponível para novas atividades
                </label>
            </CardContent>
        </Card>

        <Card>
            <CardHeader><CardTitle as="h2">Interesses</CardTitle></CardHeader>
            <CardContent
                ><CategoryPicker
                    v-model="form.data.category_ids"
                    :error="form.errors.category_ids"
            /></CardContent>
        </Card>

        <Card>
            <CardHeader>
                <CardTitle as="h2">Endereço e localização</CardTitle>
                <CardDescription
                    >Seu endereço completo nunca é exibido para ONGs — apenas
                    cidade e UF.</CardDescription
                >
            </CardHeader>
            <CardContent class="grid gap-6">
                <AddressFields
                    v-model="form.data.address"
                    :errors="form.errors"
                />
                <div class="bg-muted/50 grid gap-3 rounded-lg p-4">
                    <label class="flex items-start gap-3 text-sm">
                        <input
                            v-model="form.data.location_consent"
                            type="checkbox"
                            class="accent-primary mt-0.5 size-4"
                        />
                        <span>
                            <span class="flex items-center gap-1 font-medium"
                                ><ShieldCheckIcon
                                    class="size-4"
                                    aria-hidden="true"
                                />
                                Autorizo o uso da minha localização</span
                            >
                            <span class="text-muted-foreground"
                                >Usada apenas para mostrar ONGs e solicitações
                                próximas. Você pode revogar a qualquer momento;
                                ao revogar, as coordenadas são apagadas.</span
                            >
                        </span>
                    </label>
                    <LocationPicker
                        v-if="form.data.location_consent"
                        v-model:latitude="form.data.latitude"
                        v-model:longitude="form.data.longitude"
                    />
                </div>
            </CardContent>
        </Card>

        <div class="flex items-center justify-end gap-3">
            <p
                v-if="form.recentlySuccessful.value"
                class="text-muted-foreground text-sm"
                role="status"
            >
                Salvo.
            </p>
            <Button type="submit" :disabled="form.processing.value">{{
                form.processing.value ? 'Salvando…' : 'Salvar perfil'
            }}</Button>
        </div>
    </form>
</template>
