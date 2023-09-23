<script setup>
const props = defineProps({
  text: String,
})
</script>
<template>
  <p class="rounded-full px-3 py-px h-fit bg-blue-100 border border-gray-400 text-gray-800 w-fit">{{text}}</p>
</template>
