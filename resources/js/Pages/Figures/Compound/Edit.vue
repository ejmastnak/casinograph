<script setup>
import { Head } from '@inertiajs/vue3'
import CreateOrEdit from './Partials/CreateOrEdit.vue'

const props = defineProps({
  compound_figure: Object,
  figure_families: Array,
  figures: Array,
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
    <Head :title="'Edit ' + compound_figure.name " />
    <h1 class="text-xl">Edit {{compound_figure.name}}</h1>

    <CreateOrEdit
      action="edit"
      :compound_figure="compound_figure"
      :figure_families="figure_families"
      :figures="figures"
    />

  </div>
</template>
