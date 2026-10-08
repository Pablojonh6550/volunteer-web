<script setup lang="ts">
import type { HTMLAttributes } from "vue"
import { cn } from "@/lib/utils"

// `as` permite escolher a tag semântica (ex.: article, h1, h2).
const props = withDefaults(defineProps<{
  class?: HTMLAttributes["class"]
  as?: string
}>(), { as: "h3" })
</script>

<template>
  <component
    :is="props.as"
    data-slot="card-title"
    :class="cn('leading-none font-semibold', props.class)"
  >
    <slot />
  </component>
</template>
