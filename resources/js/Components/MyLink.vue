<script setup>
import { Link } from '@inertiajs/vue3'

const props = defineProps({
  href: String,
  colored: {
    type: Boolean,
    default: false
  }
})
</script>

<template>
  <Link
    :href="href"
    class="p-px rounded-md hover:text-blue-600 hover:underline focus:outline-none focus:ring-2 focus:ring-blue-700"
    :class="{ 'text-blue-500': colored }"
  >
  <slot />
  </Link>
</template>
