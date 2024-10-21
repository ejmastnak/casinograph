<script setup>
import MyLink from '@/Components/MyLink.vue'

const props = defineProps({
  figure: Object,
})
</script>

<template>
  <div class="mx-auto w-fit">
    <MyLink
      class="text-xl italic rounded-xl !px-3 !py-2 bg-blue-50"
      :href="route('figures.show', figure.id)"
    >
      {{figure.name}}
    </MyLink>
  </div>
</template>
