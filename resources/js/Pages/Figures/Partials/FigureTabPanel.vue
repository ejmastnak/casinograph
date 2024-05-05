<script setup>
import { ref, watch, computed, onBeforeUnmount, onMounted} from 'vue'
import { router } from '@inertiajs/vue3'
import fuzzysort from 'fuzzysort'
import throttle from "lodash/throttle";
import { MagnifyingGlassIcon, XMarkIcon, TrashIcon } from '@heroicons/vue/24/outline'
import DeleteDialog from "@/Components/DeleteDialog.vue";
import TextInput from '@/Components/TextInput.vue'
import MultiSelect from '@/Components/MultiSelect.vue'
import PlainButton from '@/Components/PlainButton.vue'
import MyLink from '@/Components/MyLink.vue'

const props = defineProps({
  figures: Array,
  figure_families: Array,
  compound: Boolean,  // is this panel for base or compound figures?
  can_delete: Boolean,
})

// For filtering Figures by FigureFamily
const selectedFigureFamilies = ref([])
const selectedFigureFamilyIDs = computed(() => {
  return selectedFigureFamilies.value.map(figureFamily => figureFamily.id)
})

function shouldDisplay(figure) {
  return (selectedFigureFamilies.value.length === 0) || selectedFigureFamilyIDs.value.includes(figure.figure_family_id)
}

function removeAccents(str) {
  return str.normalize('NFD').replace(/[\u0300-\u036f]/g, '')
}

const figureSearchInput = ref(null)
const figureSearchQuery = ref("")
const fuzzysortOptions = {
  keys: ['normalized_name'],
  all: true,       // return all items for empty search query
  limit: 100,      // maximum number of search results
  threshold: -1000  // omit results with scores below threshold
}
const normalized_figures = computed(() => {
  return props.figures.map(figure => ({
    id: figure.id,
    name: figure.name,
    weight: figure.weight,
    normalized_name: removeAccents(figure.name),
    figure_family_id: figure.figure_family_id,
    figure_family: figure.figure_family,
    from_position_id: figure.from_position_id,
    from_position: figure.from_position,
    to_position_id: figure.to_position_id,
    to_position: figure.to_position,
  }))
})
// Convert to fuzzysort format with nested obj key
const filteredFigures = ref(normalized_figures.value.map((figure) => ({
  obj: figure
})))

function search(query) {
  filteredFigures.value = fuzzysort.go(removeAccents(query.trim()), normalized_figures.value, fuzzysortOptions)
}
watch(figureSearchQuery, throttle(function (value) {
  search(value)
}, 400))

function clearFilters() {
  figureSearchQuery.value = ""
  selectedFigureFamilies.value = []
  search(figureSearchQuery.value)
  figureSearchInput.value.focus()
}

// Used to conditionally display a "No results found" message.
const numDisplayedFigures = computed(() => {
  return filteredFigures.value.filter(figure => shouldDisplay(figure.obj)).length
})

let idToDelete = ref(null)
const deleteDialog = ref(null)
function deleteFigure() {
  if (idToDelete.value) {
    router.delete(route((props.compound ? 'compound-figures.destroy' : 'figures.destroy'), idToDelete.value), {
      onSuccess: () => {
        // Set selectedFigureFamilies to only those previously selected
        // figure families still present in props.figure_families after
        // deleting the last figure. This handles the edge case where a user
        // filters by figure family and deletes last figure in a family
        // (causing figure family itself to be deleted in backend). Stored
        // selectedFigureFamilies then includes a figure family that no
        // longer exists in props.figure_families.
        const figureFamilyIds = props.figure_families.map(f => f.id);
        selectedFigureFamilies.value = selectedFigureFamilies.value.filter(f => figureFamilyIds.includes(f.id));
        search(figureSearchQuery.value)  // remove deleted item from display
      }
    });
  }
  idToDelete.value = null
}


// Preserve search queries when leaving page.
onBeforeUnmount(() => {
  sessionStorage.setItem((props.compound ? 'compoundFigures' : 'figures') + 'IndexSearchQuery', figureSearchQuery.value)
  sessionStorage.setItem((props.compound ? 'compoundFigures' : 'figures') + 'IndexSelectedFamilyIds', JSON.stringify(selectedFigureFamilies.value.map(family => family.id)))
})

// Preserve search queries on manual page reload.
window.onbeforeunload = function() {
  sessionStorage.setItem((props.compound ? 'compoundFigures' : 'figures') + 'IndexSearchQuery', figureSearchQuery.value)
  sessionStorage.setItem((props.compound ? 'compoundFigures' : 'figures') + 'IndexSelectedFamilyIds', JSON.stringify(selectedFigureFamilies.value.map(family => family.id)))
}

// Restore search queries when loading page
onMounted(() => {
  const storedSearchQuery = sessionStorage.getItem((props.compound ? 'compoundFigures' : 'figures') + 'IndexSearchQuery');
  if (storedSearchQuery) {
    figureSearchQuery.value = storedSearchQuery
    search(figureSearchQuery.value)
  }

  if (sessionStorage.getItem((props.compound ? 'compoundFigures' : 'figures') + 'IndexSelectedFamilyIds')) {
    const storedSelectedFigureFamilyIds = JSON.parse(sessionStorage.getItem((props.compound ? 'compoundFigures' : 'figures') + 'IndexSelectedFamilyIds'))
    props.figure_families.forEach((figureFamily) => {
      if (storedSelectedFigureFamilyIds.includes(figureFamily.id)) {
        selectedFigureFamilies.value.push(figureFamily)
      }
    })
  }
})


</script>

<template>
  <!-- Main panel for table and search -->
  <div>

    <!-- Search and filter components -->
    <div class="flex flex-wrap items-end space-y-2 gap-x-4">

      <!-- Fuzzy search by name -->
      <div>
        <label :for="(compound ? 'compound-' : '') + 'figure-search-query'" class="ml-1 text-sm text-gray-500">
          Search by name
        </label>
        <div class="relative">
          <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
            <MagnifyingGlassIcon class="w-5 h-5 text-gray-500" />
          </div>
          <TextInput
            class="py-2 pl-10 text-gray-700 w-80 bg-gray-50"
            type="text"
            :id="(compound ? 'compound-' : '') + 'figure-search-query'"
            v-model="figureSearchQuery"
            ref="figureSearchInput"
          />
        </div>
      </div>

      <!-- FigureFamily Filter -->
      <MultiSelect
        :options="figure_families"
        width="w-44"
        labelText="Filter by family"
        inputClasses="!py-2.5"
        :modelValue="selectedFigureFamilies"
        @update:modelValue="newValue => selectedFigureFamilies = newValue"
      />

      <!-- Clear filters buttom -->
      <div class="flex items-center h-fit">
        <label :for="(compound ? 'compound-' : '') + 'clear-filters'" class="sr-only">
          Clear filters
        </label>
        <PlainButton
          :id="(compound ? 'compound-' : '') + 'clear-filters'"
          class="!bg-gray-50"
          @click="clearFilters"
        >
          <XMarkIcon class="-ml-2 w-6 h-6 text-gray-500 shrink-0" />
          <div class="ml-0.5 text-gray-600 whitespace-nowrap">
            Clear filters
          </div>
        </PlainButton>
      </div>

    </div>

    <!-- Listing of Figures -->
    <!-- Layouts: -->
    <!-- pre-sm: (name, family) (7, 5) -->
    <!-- sm, !$user: (name, family) (8, 4) -->
    <!-- sm, $user: (name, family, edit/delete) (8, 3, 1) -->
    <div class="mt-6  min-w-[400px]">
      <!-- Table header -->
      <div class="grid grid-cols-12 text-xs uppercase text-gray-700 font-semibold">
        <p class="col-span-7 sm:col-span-8 px-3 sm:px-5 py-3 bg-blue-50">Name</p>
        <p 
          class="px-3 sm:px-5 py-3 bg-blue-100"
          :class="can_delete ? 'col-span-5 sm:col-span-3' : 'col-span-5 sm:col-span-4'"
        >
          Family
        </p>
        <p v-if="can_delete" class="hidden sm:block sm:col-span-1 py-3 bg-blue-50" />
      </div>

      <div
        v-for="(figure, idx) in filteredFigures"
        :key="figure.obj.id"
        v-show="shouldDisplay(figure.obj)"
        class="grid grid-cols-12 text-gray-800 text-sm sm:text-base border-b py-1.5"
      >
        <!-- Name -->
        <div class="col-span-7 sm:col-span-8 px-3 sm:px-4">
          <MyLink class="inline-block" :href="route(compound ? 'compound-figures.show' : 'figures.show', figure.obj.id)">
            {{figure.obj.name}}
          </MyLink>
          <p class="text-sm text-gray-500">
            <MyLink class="font-medium" :href="route('positions.show', figure.obj.from_position_id)" >{{figure.obj.from_position.name}}</MyLink>
            to
            <MyLink class="font-medium" :href="route('positions.show', figure.obj.to_position_id)" >{{figure.obj.to_position.name}}</MyLink>
          </p>
        </div>
        <!-- Figure family -->
        <div
          class="px-4 text-gray-600  flex items-center"
          :class="can_delete ? 'col-span-5 sm:col-span-3' : 'col-span-5 sm:col-span-4'"
        >
          <MyLink
            v-if="figure.obj.figure_family"
            :href="route('figure-families.show', figure.obj.figure_family.id)"
          >
            {{figure.obj.figure_family.name}}
          </MyLink>
        </div>
        <!-- Delete button -->
        <div v-if="can_delete" class="hidden sm:block sm:col-span-1 px-1">
          <PlainButton
            class="text-gray-500 hover:text-red-600"
            @click="idToDelete = figure.obj.id; deleteDialog.open()"
          >
            <TrashIcon class="h-5 w-5"/>
          </PlainButton>
        </div>
      </div>
    </div>

    <p v-show="numDisplayedFigures === 0" class="px-5 py-4" >
      No results found. Try a less restrictive filter or search?
    </p>

    <DeleteDialog
      ref="deleteDialog"
      description="figure"
      @delete="deleteFigure"
      @cancel="idToDelete = null; deleteCompound = null"
    />

  </div>
</template>
