<script setup>
</script>

<template>
  <details open class="py-2.5 px-4 border border-gray-500 rounded-lg overflow-hidden bg-blue-50">
    <summary class="text-gray-800 font-medium cursor-pointer">
      <slot name="question"/>
    </summary>
    <div class="mt-1">
      <slot name="answer"/>
    </div>
  </details>
</template>
