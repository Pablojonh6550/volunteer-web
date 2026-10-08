<script setup lang="ts">
import type { HTMLAttributes } from "vue"
import { cn } from "@/lib/utils"

// `as` permite escolher a tag semântica (ex.: article, h1, h2).
const props = withDefaults(defineProps<{
  class?: HTMLAttributes["class"]
  as?: string
}>(), { as: "div" })
</script>

<template>
  <component
    :is="props.as"
    data-slot="card"
    :class="
      cn(
        'bg-card text-card-foreground flex flex-col gap-6 rounded-xl border py-6 shadow-sm',
        props.class,
      )
    "
  >
    <slot />
  </component>
</template>
