<script setup>
import { Head } from '@inertiajs/vue3'
import MyLink from '@/Components/MyLink.vue'
</script>

<script>
import AppLayout from "@/Layouts/AppLayout.vue";
export default {
  layout: AppLayout,
}
</script>

<template>
  <div class="">
    <Head title="Home" />

    <h1 class="text-xl">CasinoGraph</h1>

    <ul class="mt-4">
      <li><MyLink :colored="true" :href="route('positions.index')">Positions</MyLink></li>
      <li><MyLink :colored="true" :href="route('figures.index')">Figures</MyLink></li>
    </ul>

    Hello, world!

  </div>
</template>
