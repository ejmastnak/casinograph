<script setup>
import MyLink from '@/Components/MyLink.vue'

const props = defineProps({
  compound_figure: Object,
})
</script>

<template>
  <div class="mx-auto w-fit">
    <MyLink
      class="text-xl italic rounded-xl !px-3 !py-2 bg-purple-100"
      :href="route('compound-figures.show', compound_figure.id)"
    >
      {{compound_figure.name}}
    </MyLink>
  </div>
</template>
