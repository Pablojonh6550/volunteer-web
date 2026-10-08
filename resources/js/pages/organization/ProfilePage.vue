<script setup lang="ts">
/** Dados institucionais, contatos, endereço e localização da ONG. */
import FormField from '@/components/common/FormField.vue';
import PageHeader from '@/components/common/PageHeader.vue';
import LocationPicker from '@/components/map/LocationPicker.vue';
import AddressFields from '@/components/profile/AddressFields.vue';
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
import { useOrganization } from '@/composables/useOrganization';
import { useAuthStore } from '@/stores/auth';
import { formatCnpj } from '@/utils/format';

const auth = useAuthStore();
const notify = useNotification();
const { update } = useOrganization();
const o = auth.organization;

const form = useForm({
    name: o?.name ?? '',
    document: o?.document ?? null,
    description: o?.description ?? null,
    phone: o?.phone ?? null,
    email: o?.email ?? null,
    website: o?.website ?? null,
    latitude: o?.latitude ?? null,
    longitude: o?.longitude ?? null,
    address: {
        address: o?.address ?? null,
        number: o?.number ?? null,
        neighborhood: o?.neighborhood ?? null,
        city: o?.city ?? null,
        state: o?.state ?? null,
        zip_code: o?.zip_code ?? null,
    },
});

async function submit(): Promise<void> {
    const { address, ...rest } = form.data;

    if (await form.submit(() => update({ ...rest, ...address }))) {
        notify.success('Perfil da ONG atualizado.');
    }
}
</script>

<template>
    <PageHeader
        title="Perfil da ONG"
        description="Informações exibidas para os voluntários."
    />

    <form class="grid gap-6" novalidate @submit.prevent="submit">
        <Card>
            <CardHeader
                ><CardTitle as="h2">Dados institucionais</CardTitle></CardHeader
            >
            <CardContent class="grid gap-4 sm:grid-cols-2">
                <FormField
                    id="name"
                    v-slot="field"
                    label="Nome da ONG"
                    :error="form.errors.name"
                    required
                    class="sm:col-span-2"
                >
                    <Input
                        v-bind="field"
                        v-model="form.data.name"
                        autocomplete="organization"
                    />
                </FormField>
                <FormField
                    id="document"
                    v-slot="field"
                    label="CNPJ"
                    :error="form.errors.document"
                >
                    <Input
                        v-bind="field"
                        :model-value="formatCnpj(form.data.document)"
                        inputmode="numeric"
                        placeholder="00.000.000/0000-00"
                        @update:model-value="
                            (v) => (form.data.document = String(v))
                        "
                    />
                </FormField>
                <FormField
                    id="website"
                    v-slot="field"
                    label="Site"
                    :error="form.errors.website"
                >
                    <Input
                        v-bind="field"
                        v-model="form.data.website"
                        type="url"
                        placeholder="https://"
                    />
                </FormField>
                <FormField
                    id="description"
                    v-slot="field"
                    label="Sobre a ONG"
                    :error="form.errors.description"
                    class="sm:col-span-2"
                >
                    <Textarea
                        v-bind="field"
                        v-model="form.data.description"
                        rows="5"
                        placeholder="Missão, público atendido, projetos…"
                    />
                </FormField>
            </CardContent>
        </Card>

        <Card>
            <CardHeader
                ><CardTitle as="h2">Contatos</CardTitle
                ><CardDescription
                    >Contatos institucionais são públicos para
                    voluntários.</CardDescription
                ></CardHeader
            >
            <CardContent class="grid gap-4 sm:grid-cols-2">
                <FormField
                    id="org-email"
                    v-slot="field"
                    label="E-mail"
                    :error="form.errors.email"
                >
                    <Input
                        v-bind="field"
                        v-model="form.data.email"
                        type="email"
                    />
                </FormField>
                <FormField
                    id="org-phone"
                    v-slot="field"
                    label="Telefone"
                    :error="form.errors.phone"
                >
                    <Input
                        v-bind="field"
                        v-model="form.data.phone"
                        type="tel"
                    />
                </FormField>
            </CardContent>
        </Card>

        <Card>
            <CardHeader
                ><CardTitle as="h2">Endereço e localização</CardTitle
                ><CardDescription
                    >A localização define onde a ONG aparece no mapa e é usada
                    como padrão nas solicitações.</CardDescription
                ></CardHeader
            >
            <CardContent class="grid gap-6">
                <AddressFields
                    v-model="form.data.address"
                    :errors="form.errors"
                />
                <LocationPicker
                    v-model:latitude="form.data.latitude"
                    v-model:longitude="form.data.longitude"
                />
                <p
                    v-if="form.errors.latitude"
                    class="text-destructive text-sm"
                    role="alert"
                >
                    {{ form.errors.latitude }}
                </p>
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
                form.processing.value ? 'Salvando…' : 'Salvar'
            }}</Button>
        </div>
    </form>
</template>
