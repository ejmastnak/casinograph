<script setup>
import { Head } from '@inertiajs/vue3'
import CreateOrEdit from './Partials/CreateOrEdit.vue'

const props = defineProps({
  figure: Object,
  figure_families: Array,
  positions: Array,
});
</script>

<script>
import AppLayout from "@/Layouts/AppLayout.vue";
export default {
  layout: AppLayout,
}
</script>

<template>
  <div class="">
    <Head :title="'Edit ' + figure.name " />
    <h1 class="text-xl">Edit {{figure.name}}</h1>

    <CreateOrEdit
      action="edit"
      :figure="figure"
      :positions="positions"
      :figure_families="figure_families"
    />

  </div>
</template>
