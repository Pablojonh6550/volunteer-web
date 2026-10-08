<script setup lang="ts">
/**
 * Campo de formulário acessível: associa <label>, dica e mensagem de erro
 * ao controle via `id`, `aria-describedby` e `aria-invalid`.
 *
 * <FormField id="email" label="E-mail" :error="form.errors.email" v-slot="field">
 *   <Input v-bind="field" v-model="form.data.email" type="email" />
 * </FormField>
 */
import { computed } from 'vue';
import { Label } from '@/components/ui/label';
import InputError from './InputError.vue';

const props = defineProps<{
    id: string;
    label: string;
    error?: string;
    hint?: string;
    required?: boolean;
}>();

const describedBy = computed(
    () =>
        [
            props.hint ? `${props.id}-hint` : null,
            props.error ? `${props.id}-error` : null,
        ]
            .filter(Boolean)
            .join(' ') || undefined,
);

// Passado via v-bind de objeto: props de slot com hífen declaradas uma a uma
// seriam convertidas para camelCase (ariaDescribedby) e não virariam atributos.
const controlAttrs = computed(() => ({
    id: props.id,
    'aria-describedby': describedBy.value,
    'aria-invalid': props.error ? true : undefined,
    required: props.required,
}));
</script>

<template>
    <div class="grid gap-2">
        <Label :for="id">
            {{ label }}
            <span v-if="required" class="text-destructive" aria-hidden="true"
                >*</span
            >
            <span v-if="required" class="sr-only">(obrigatório)</span>
        </Label>
        <slot v-bind="controlAttrs" />
        <p v-if="hint" :id="`${id}-hint`" class="text-muted-foreground text-xs">
            {{ hint }}
        </p>
        <InputError :id="`${id}-error`" :message="error" />
    </div>
</template>
