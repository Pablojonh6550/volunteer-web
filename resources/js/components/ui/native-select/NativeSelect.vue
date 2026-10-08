<script setup lang="ts">
/**
 * <select> nativo com o visual do design system.
 * Preferido em formulários e filtros: acessível por padrão (teclado,
 * leitores de tela e seletor nativo em dispositivos móveis).
 */
import type { HTMLAttributes } from "vue"
import { ChevronDownIcon } from "@lucide/vue"
import { cn } from "@/lib/utils"

defineOptions({ inheritAttrs: false })

const props = defineProps<{
  options: { value: string | number; label: string }[]
  placeholder?: string
  class?: HTMLAttributes["class"]
}>()

const model = defineModel<string | number | null>()
</script>

<template>
  <div :class="cn('relative', props.class)">
    <select
      v-model="model"
      v-bind="$attrs"
      data-slot="native-select"
      class="border-input dark:bg-input/30 h-9 w-full appearance-none rounded-md border bg-transparent py-1 pr-8 pl-3 text-base shadow-xs outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] disabled:cursor-not-allowed disabled:opacity-50 aria-invalid:border-destructive md:text-sm"
    >
      <option v-if="placeholder !== undefined" :value="model === null ? null : ''">{{ placeholder }}</option>
      <option v-for="option in options" :key="option.value" :value="option.value">
        {{ option.label }}
      </option>
    </select>
    <ChevronDownIcon class="text-muted-foreground pointer-events-none absolute top-1/2 right-2.5 size-4 -translate-y-1/2" aria-hidden="true" />
  </div>
</template>
