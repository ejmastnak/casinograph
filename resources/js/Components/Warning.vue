<script setup>
import { ExclamationTriangleIcon } from '@heroicons/vue/24/outline'
</script>

<template>
  <div class="bg-yellow-100 px-3 py-2 rounded-xl border border-gray-300 flex">
    <ExclamationTriangleIcon class="w-8 h-8 text-yellow-900 shrink-0 mr-3 translate-y-1" />
    <p>
      <slot />
    </p>
  </div>
</template>
