<script setup lang="ts">
/**
 * Criação e edição de solicitação (mesma tela; `id` presente = edição).
 */
import { computed, onMounted, ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import ErrorState from '@/components/common/ErrorState.vue';
import FormField from '@/components/common/FormField.vue';
import LoadingState from '@/components/common/LoadingState.vue';
import PageHeader from '@/components/common/PageHeader.vue';
import LocationPicker from '@/components/map/LocationPicker.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { NativeSelect } from '@/components/ui/native-select';
import { Textarea } from '@/components/ui/textarea';
import { useForm } from '@/composables/useForm';
import { useNotification } from '@/composables/useNotification';
import { requestService } from '@/services';
import type { HelpRequestPayload } from '@/services';
import { useAuthStore } from '@/stores/auth';
import { useCategoriesStore } from '@/stores/categories';
import {
    REQUEST_PRIORITY_LABELS,
    REQUEST_TYPE_LABELS,
    toOptions,
} from '@/types';
import type { ApiError, RequestType } from '@/types';

const props = defineProps<{ id?: number }>();
const router = useRouter();
const notify = useNotification();
const auth = useAuthStore();
const categories = useCategoriesStore();

const isEdit = computed(() => props.id !== undefined);
const loading = ref(false);
const loadError = ref<ApiError | null>(null);

const form = useForm<HelpRequestPayload & Record<string, unknown>>({
    type: 'donation',
    category_id: null,
    title: '',
    description: '',
    quantity: null,
    priority: 'medium',
    start_date: null,
    end_date: null,
    latitude: auth.organization?.latitude ?? null,
    longitude: auth.organization?.longitude ?? null,
});

const categoryOptions = computed(() =>
    categories
        .forType(form.data.type as RequestType)
        .map((c) => ({ value: c.id, label: c.name })),
);

// Ao trocar o tipo, a categoria anterior deixa de ser válida.
watch(
    () => form.data.type,
    (type, previous) => {
        if (previous && type !== previous) {
            form.data.category_id = null;
        }
    },
);

async function load(): Promise<void> {
    if (!props.id) {
        return;
    }

    loading.value = true;
    loadError.value = null;

    try {
        const r = await requestService.get(props.id);
        form.reset({
            type: r.type,
            category_id: r.category_id,
            title: r.title,
            description: r.description,
            quantity: r.quantity,
            priority: r.priority,
            start_date: r.start_date,
            end_date: r.end_date,
            latitude: r.latitude,
            longitude: r.longitude,
        });
    } catch (e) {
        loadError.value = e as ApiError;
    } finally {
        loading.value = false;
    }
}

onMounted(() => {
    void categories.load();
    void load();
});

async function submit(): Promise<void> {
    const payload: HelpRequestPayload = {
        ...form.data,
        category_id: form.data.category_id
            ? Number(form.data.category_id)
            : null,
        quantity: form.data.quantity ? Number(form.data.quantity) : null,
        start_date: form.data.start_date || null,
        end_date: form.data.end_date || null,
    } as HelpRequestPayload;

    const saved = await form.submit(() =>
        props.id
            ? requestService.update(props.id, payload)
            : requestService.create(payload),
    );

    if (saved) {
        notify.success(
            isEdit.value ? 'Solicitação atualizada.' : 'Solicitação publicada!',
        );
        await router.push({
            name: 'organization.request',
            params: { id: saved.id },
        });
    }
}
</script>

<template>
    <PageHeader
        :title="isEdit ? 'Editar solicitação' : 'Nova solicitação'"
        description="Descreva com clareza o que sua ONG precisa."
    />

    <ErrorState v-if="loadError" :message="loadError.message" @retry="load" />
    <LoadingState v-else-if="loading" />
    <form v-else class="grid gap-6" novalidate @submit.prevent="submit">
        <p
            v-if="form.message.value && !Object.keys(form.errors).length"
            role="alert"
            class="text-destructive text-sm"
        >
            {{ form.message.value }}
        </p>

        <Card>
            <CardHeader
                ><CardTitle as="h2">O que você precisa?</CardTitle></CardHeader
            >
            <CardContent class="grid gap-4 sm:grid-cols-2">
                <FormField
                    id="type"
                    v-slot="field"
                    label="Tipo"
                    :error="form.errors.type"
                    required
                >
                    <NativeSelect
                        v-bind="field"
                        v-model="form.data.type"
                        :options="toOptions(REQUEST_TYPE_LABELS)"
                    />
                </FormField>
                <FormField
                    id="category_id"
                    v-slot="field"
                    label="Categoria"
                    :error="form.errors.category_id"
                >
                    <NativeSelect
                        v-bind="field"
                        v-model="form.data.category_id"
                        placeholder="Sem categoria"
                        :options="categoryOptions"
                    />
                </FormField>
                <FormField
                    id="title"
                    v-slot="field"
                    label="Título"
                    :error="form.errors.title"
                    required
                    class="sm:col-span-2"
                >
                    <Input
                        v-bind="field"
                        v-model="form.data.title"
                        maxlength="255"
                        placeholder="Ex.: Arrecadação de agasalhos para o inverno"
                    />
                </FormField>
                <FormField
                    id="description"
                    v-slot="field"
                    label="Descrição"
                    hint="Mínimo de 20 caracteres. Explique o contexto, o que é necessário e como ajudar."
                    :error="form.errors.description"
                    required
                    class="sm:col-span-2"
                >
                    <Textarea
                        v-bind="field"
                        v-model="form.data.description"
                        rows="6"
                        maxlength="5000"
                    />
                </FormField>
                <FormField
                    id="quantity"
                    v-slot="field"
                    :label="
                        form.data.type === 'donation'
                            ? 'Quantidade de itens'
                            : 'Número de vagas'
                    "
                    :error="form.errors.quantity"
                >
                    <Input
                        v-bind="field"
                        v-model="form.data.quantity"
                        type="number"
                        min="1"
                        inputmode="numeric"
                    />
                </FormField>
                <FormField
                    id="priority"
                    v-slot="field"
                    label="Prioridade"
                    :error="form.errors.priority"
                >
                    <NativeSelect
                        v-bind="field"
                        v-model="form.data.priority"
                        :options="toOptions(REQUEST_PRIORITY_LABELS)"
                    />
                </FormField>
                <FormField
                    id="start_date"
                    v-slot="field"
                    label="Início"
                    :error="form.errors.start_date"
                >
                    <Input
                        v-bind="field"
                        v-model="form.data.start_date"
                        type="date"
                    />
                </FormField>
                <FormField
                    id="end_date"
                    v-slot="field"
                    label="Término"
                    :error="form.errors.end_date"
                >
                    <Input
                        v-bind="field"
                        v-model="form.data.end_date"
                        type="date"
                    />
                </FormField>
            </CardContent>
        </Card>

        <Card>
            <CardHeader>
                <CardTitle as="h2">Local</CardTitle>
                <CardDescription
                    >Por padrão usamos a localização da ONG. Ajuste se a
                    atividade acontece em outro lugar.</CardDescription
                >
            </CardHeader>
            <CardContent>
                <LocationPicker
                    v-model:latitude="form.data.latitude"
                    v-model:longitude="form.data.longitude"
                />
            </CardContent>
        </Card>

        <div class="flex justify-end gap-3">
            <Button type="button" variant="outline" @click="router.back()"
                >Cancelar</Button
            >
            <Button type="submit" :disabled="form.processing.value">
                {{
                    form.processing.value
                        ? 'Salvando…'
                        : isEdit
                          ? 'Salvar alterações'
                          : 'Publicar solicitação'
                }}
            </Button>
        </div>
    </form>
</template>
