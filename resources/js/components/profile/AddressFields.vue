<script setup lang="ts">
/**
 * Campos de endereço compartilhados entre perfil de ONG e voluntário.
 */
import FormField from '@/components/common/FormField.vue';
import { Input } from '@/components/ui/input';
import { formatZipCode } from '@/utils/format';

export interface AddressModel {
    address: string | null;
    number: string | null;
    neighborhood: string | null;
    city: string | null;
    state: string | null;
    zip_code: string | null;
}

const model = defineModel<AddressModel>({ required: true });
defineProps<{ errors: Record<string, string> }>();

const UF =
    'AC AL AP AM BA CE DF ES GO MA MT MS MG PA PB PR PE PI RJ RN RS RO RR SC SP SE TO'.split(
        ' ',
    );
</script>

<template>
    <fieldset class="grid gap-4 sm:grid-cols-6">
        <legend class="mb-2 text-sm font-medium">Endereço</legend>
        <FormField
            id="zip_code"
            v-slot="field"
            label="CEP"
            :error="errors.zip_code"
            class="sm:col-span-2"
        >
            <Input
                v-bind="field"
                :model-value="formatZipCode(model.zip_code)"
                inputmode="numeric"
                autocomplete="postal-code"
                placeholder="00000-000"
                @update:model-value="(v) => (model.zip_code = String(v))"
            />
        </FormField>
        <FormField
            id="address"
            v-slot="field"
            label="Logradouro"
            :error="errors.address"
            class="sm:col-span-3"
        >
            <Input
                v-bind="field"
                v-model="model.address"
                autocomplete="address-line1"
            />
        </FormField>
        <FormField
            id="number"
            v-slot="field"
            label="Número"
            :error="errors.number"
            class="sm:col-span-1"
        >
            <Input v-bind="field" v-model="model.number" />
        </FormField>
        <FormField
            id="neighborhood"
            v-slot="field"
            label="Bairro"
            :error="errors.neighborhood"
            class="sm:col-span-2"
        >
            <Input v-bind="field" v-model="model.neighborhood" />
        </FormField>
        <FormField
            id="city"
            v-slot="field"
            label="Cidade"
            :error="errors.city"
            class="sm:col-span-3"
        >
            <Input
                v-bind="field"
                v-model="model.city"
                autocomplete="address-level2"
            />
        </FormField>
        <FormField
            id="state"
            v-slot="field"
            label="UF"
            :error="errors.state"
            class="sm:col-span-1"
        >
            <select
                v-bind="field"
                v-model="model.state"
                class="border-input dark:bg-input/30 h-9 w-full rounded-md border bg-transparent px-2 text-sm"
            >
                <option :value="null">—</option>
                <option v-for="uf in UF" :key="uf" :value="uf">{{ uf }}</option>
            </select>
        </FormField>
    </fieldset>
</template>
