<script setup>
import MyLink from '@/Components/MyLink.vue'

const props = defineProps({
  position: Object,
})
</script>

<template>
  <div class="mx-auto w-fit text-lg">
    <MyLink :href="route('positions.show', position.id)" >
      {{position.name}}
    </MyLink>
  </div>
</template>
