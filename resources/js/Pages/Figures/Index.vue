<script setup>
import { ref, onBeforeUnmount, onMounted} from 'vue'
import { Head } from '@inertiajs/vue3'
import SecondaryLink from '@/Components/SecondaryLink.vue'
import { PlusCircleIcon } from '@heroicons/vue/24/outline'
import { 
  Popover, PopoverButton, PopoverPanel, PopoverOverlay,
  TabGroup, TabList, Tab, TabPanels, TabPanel,
} from '@headlessui/vue'
import FigureTabPanel from './Partials/FigureTabPanel.vue'

const props = defineProps({
  figures: Array,
  compound_figures: Array,
  figure_families: Array,
  positions: Array,
})

// I'm separating the figure families of figures and compound figures here.
// Motivation: only pass those figure families to FigureTabPanel for which at
// least one figure in the tab will be of that family.
const baseFigureFamilyIds = new Set(props.figures.map(f => f.figure_family_id));
const compoundFigureFamilyIds = new Set(props.compound_figures.map(f => f.figure_family_id));

const selectedTab = ref(sessionStorage.getItem('figuresIndexSelectedTab') ?? 0)
function changeTab(index) {
  selectedTab.value = index
}

// Preserve scroll position when leaving page.
onBeforeUnmount(() => {
  sessionStorage.setItem('figuresIndexScrollX', window.scrollX)
  sessionStorage.setItem('figuresIndexScrollY', window.scrollY)
  sessionStorage.setItem('figuresIndexSelectedTab', selectedTab.value);
})

// Preserve scroll position on manual page reload.
window.onbeforeunload = function() {
  sessionStorage.setItem('figuresIndexScrollX', window.scrollX)
  sessionStorage.setItem('figuresIndexScrollY', window.scrollY)
  sessionStorage.setItem('figuresIndexSelectedTab', selectedTab.value);
}

// Restore scroll position when loading page
onMounted(() => {
  const scrollX = sessionStorage.getItem('figuresIndexScrollX')
  const scrollY = sessionStorage.getItem('figuresIndexScrollY')
  if (scrollX && scrollY) {
    setTimeout(() => {
      window.scrollTo(scrollX, scrollY)
    })
  }

  const storedSelectedTab = sessionStorage.getItem('figuresIndexSelectedTab');
  if (storedSelectedTab) selectedTab.value = Number(storedSelectedTab);
})


</script>

<script>
import AppLayout from "@/Layouts/AppLayout.vue";
export default {
  layout: AppLayout,
}
</script>

<template>
  <div class="">
    <Head title="Figures" />

    <div class="flex">
      <div class="mr-2">
        <h1 class="text-xl">Figures</h1>
        <p class="mt-1 text-sm text-gray-500 max-w-xs">This is a list of all figures. You can use this page to view, edit, delete, or add new figures.</p>
      </div>

      <!-- Desktop: show both Figure and CompoundFigure create buttons -->
      <div class="hidden sm:flex flex-col space-y-1 ml-auto">
        <SecondaryLink class="w-fit" :href="route('figures.create')" >
          <PlusCircleIcon class="-ml-1 text-gray-600 h-6 w-6 shrink-0" />
          <p class="ml-1 whitespace-nowrap">New figure</p>
        </SecondaryLink>
        <SecondaryLink :href="route('compound-figures.create')" >
          <PlusCircleIcon class="-ml-1 text-gray-600 h-6 w-6 shrink-0" />
          <p class="ml-1 whitespace-nowrap">New compound figure</p>
        </SecondaryLink>
      </div>
      <!-- Mobile: show a popover with choice of Figure/CompoundFigure -->
      <div class="sm:hidden relative ml-auto">
        <Popover>
          <PopoverButton class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150" >
            <PlusCircleIcon class="-ml-1 h-6 w-6 text-gray-500 shrink-0" />
          <p class="ml-1 whitespace-nowrap">New</p>
          </PopoverButton>

          <PopoverOverlay class="fixed inset-0 bg-black opacity-20 z-40" />
          <PopoverPanel class="absolute right-0 w-64 mt-0.5 px-4 py-5 rounded-xl overflow-hidden border border-gray-400 bg-white shadow text-gray-800 z-50">
            <div class="flex flex-col space-y-1.5">
              <SecondaryLink class="w-fit" :href="route('figures.create')" >
                New figure
              </SecondaryLink>
              <SecondaryLink class="w-fit" :href="route('compound-figures.create')" >
                New compound figure
              </SecondaryLink>
            </div>
          </PopoverPanel>
        </Popover>
      </div>

    </div>

    <TabGroup @change="changeTab" :defaultIndex="Number(selectedTab)">

      <TabList class="mt-4 rounded border-b space-x-2 whitespace-nowrap flex">

        <!-- Figures tab button -->
        <Tab as="template" v-slot="{ selected }">
          <button
            class="px-4 py-2 text-sm text-gray-600 focus:outline-none transition ease-in-out duration-150"
            :class="{
              'text-gray-800 font-semibold border-b-2 border-blue-500': selected,
              'hover:border-b-2 hover:border-gray-300': !selected
            }" >
            Figures
          </button>
        </Tab>

        <!-- CompoundFigures tab button -->
        <Tab as="template" v-slot="{ selected }">
          <button
            class="px-4 py-2 text-sm text-gray-600 focus:outline-none transition ease-in-out duration-150"
            :class="{
              'text-gray-800 font-semibold border-b-2 border-blue-500': selected,
              'hover:border-b-2 hover:border-gray-300': !selected
            }" >
            Compound figures
          </button>
        </Tab>

      </TabList>

      <TabPanels class="mt-2">

        <!-- Figures panel -->
        <TabPanel class="focus:outline-none focus:ring-1 focus:ring-blue-500 rounded p-1">
          <FigureTabPanel
            :figures="figures"
            :figure_families="figure_families.filter(f => baseFigureFamilyIds.has(f.id))"
            :positions="positions"
            :compound="false"
          />
        </TabPanel>

        <!-- CompoundFigures panel -->
        <TabPanel class="focus:outline-none focus:ring-1 focus:ring-blue-500 rounded-md p-1">
          <FigureTabPanel
            :figures="compound_figures"
            :figure_families="figure_families.filter(f => compoundFigureFamilyIds.has(f.id))"
            :positions="positions"
            :compound="true"
          />
        </TabPanel>

      </TabPanels>
    </TabGroup>

  </div>
</template>
