<script setup>
import { ref } from 'vue'
import { Head } from '@inertiajs/vue3'
import MyLink from '@/Components/MyLink.vue'
import QuestionAndAnswer from '@/Components/QuestionAndAnswer.vue'
import Warning from '@/Components/Warning.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import DangerButton from '@/Components/DangerButton.vue'
import PlainButton from '@/Components/PlainButton.vue'
import { QuestionMarkCircleIcon, InformationCircleIcon, ExclamationTriangleIcon, ArrowsUpDownIcon, ArrowsPointingOutIcon, XMarkIcon } from '@heroicons/vue/24/outline'
import {
  Dialog, DialogPanel, DialogTitle, DialogDescription,
  Popover, PopoverButton, PopoverPanel, PopoverOverlay
} from '@headlessui/vue'

const props = defineProps({
  graph_path: String,
  graph_is_nonempty: Boolean,
})

const graphIsFullscreen = ref(false)
function setGraphIsFullScreen(value) {
  graphIsFullscreen.value = value
}

</script>

<script>
import AppLayout from "@/Layouts/AppLayout.vue";
export default {
  layout: AppLayout,
}
</script>

<template>
  <div>
    <Head title="Home" />

    <h1 class="text-2xl text-gray-800">SonGraph</h1>

    <div class="mt-2">

      <!-- Popover buttons for What is Casino, What is a graph? -->
      <div class="w-fit h-fit float-right ml-2 xs:ml-4">

        <!-- What is Casino? -->
        <div class="relative">
          <Popover>
            <PopoverButton class="w-full flex items-center px-3 py-1 bg-blue-50 border border-gray-300 rounded-lg text-sm text-gray-600 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150" >
              <InformationCircleIcon class="-ml-1 h-6 w-6 text-gray-500 shrink-0" />
              <p class="md:hidden ml-1.5 whitespace-nowrap">What is Casino?</p>
              <p class="hidden md:block ml-1.5 whitespace-nowrap">Wait, what is Casino?</p>
            </PopoverButton>

            <PopoverOverlay class="fixed inset-0 bg-black opacity-20 z-40" />
            <PopoverPanel class="absolute right-0 w-[90vw] xs:w-96 mt-0.5 px-4 py-3 rounded-lg overflow-hidden border border-gray-400 bg-white shadow text-gray-800 z-50">
              <p>
                <MyLink :colored="true" href="https://en.wikipedia.org/wiki/Cuban_salsa">Casino</MyLink> is a social dance from Cuba.
                It is a style of salsa, and more commonly goes by the name of <span class="italic">Cuban salsa</span> (much to the dismay of traditionalists, who insist Casino is the only proper name).
              </p>

              <p class="mt-2">
                It has a loyal following, especially in Europe, and tends to produce fans who become quite obsessed, and end up spending their free time dancing, nerding out about <MyLink :colored="true" href="https://en.wikipedia.org/wiki/Timba"> timba music</MyLink> and Cuban ethnomusicology, and succumbing to eccentric acts of dedication like building websites that model Casino with directed cyclic graphs ;)
              </p>

              <p class="mt-2">
                You can read more about Casino <MyLink :colored="true" href="https://en.wikipedia.org/wiki/Cuban_salsa">on Wikipedia</MyLink>.
              </p>
            </PopoverPanel>
          </Popover>
        </div>

        <!-- What is a graph? -->
        <div class="mt-2 relative">
          <Popover>
            <PopoverButton class="w-full flex items-center px-3 py-1 bg-white border border-gray-200 rounded-lg text-sm text-gray-600 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150" >
              <InformationCircleIcon class="-ml-1 h-6 w-6 text-gray-500 shrink-0" />
              <p class="md:hidden ml-1.5 whitespace-nowrap">What is a graph?</p>
              <p class="hidden md:block ml-1.5 whitespace-nowrap">And what is a graph?</p>
            </PopoverButton>

            <PopoverOverlay class="fixed inset-0 bg-black opacity-20 z-40" />
            <PopoverPanel class="absolute right-0 w-[90vw] xs:w-96 mt-0.5 px-4 py-3 rounded-lg overflow-hidden border border-gray-400 bg-white shadow text-gray-800 z-50">
              <p>
                In the context of this website, a
                <MyLink :colored="true" href="https://en.wikipedia.org/wiki/Graph_(abstract_data_type)">graph</MyLink>
                is a data structure used in computer science to efficiently represent a group of connected data points.
                In the comp sci lingo, the data points are called <span class="italic">vertices</span> and the connections between them are called <span class="italic">edges</span>.
              </p>

              <p class="mt-2">
                Graphs are ubiquitous in the modern computational infrastructure—common real-life examples include
                social networks like Facebook and Instagram, where relationships (e.g., following and friending) act as edges connecting users (vertices);
                navigation applications like Google Maps and Apple Maps, where streets (edges) connect intersections (vertices);
                and the Web, where web sites (vertices) are connected by links (edges).
              </p>

              <p class="mt-2">
                You can read more about graphs <MyLink :colored="true" href="https://en.wikipedia.org/wiki/Graph_(abstract_data_type)">on Wikipedia</MyLink>.
              </p>
            </PopoverPanel>
          </Popover>
        </div>

      </div>

      <!-- Explanation of site -->
      <div class="max-w-lg text-gray-600">
        The goal of this website is to model Cuban Casino as a set of interconnected positions and figures and to interactively visualize the connections between them. In nerd speak, you're seeing Casino represented as a directed cyclic graph in which the figures (edges) connect the positions (vertices).
        <a href="#more-info" class="p-px rounded-md text-blue-500 hover:text-blue-600 hover:underline focus:outline-none focus:ring-2 focus:ring-blue-700" >
          More info below.
        </a>
      </div>

    </div>

    <!-- Graph -->
    <div v-if="graph_is_nonempty" class="relative mt-8 -mx-2">
      <div class="border overflow-auto border-gray-200 shadow rounded-lg h-96 sm:h-[36rem]">

        <!-- Enter full screen -->
        <PlainButton class="absolute left-2 top-2" @click="setGraphIsFullScreen(true)">
          <ArrowsPointingOutIcon class="-ml-1 w-6 h-6 text-gray-500 shrink-0" />
          <p class="ml-1">Full screen</p>
        </PlainButton>

        <!-- Scroll to explore -->
        <div class="absolute left-2 top-14 px-2 py-1 bg-white/95 flex items-center rounded">
          <ArrowsUpDownIcon class="-ml-1 w-6 h-6 text-gray-500 shrink-0" />
          <p class="ml-1 -mt-0.5 text-sm text-gray-600">Scroll to explore</p>
        </div>

        <!-- SVG -->
        <Transition name="zoom" appear>
          <object class="p-1 mx-auto max-w-xl md:max-w-3xl lg:max-w-4xl" type="image/svg+xml" :data="graph_path"></object>
        </Transition>
      </div>
    </div>

    <!-- About the graph -->
    <section v-if="graph_is_nonempty" class="mt-8">
      <h2 class="text-xl text-gray-700" id="more-info">About the graph</h2>
      <ul class="mt-2 list-disc space-y-1">
        <li>
          <span class="font-medium">It's clickable!</span>
          Click on any figure or position to learn more.
          <span v-if="!$page.props.auth.user">(Disclaimer: descriptions are currently minimal.)</span>
        </li>
        <li>
          <span class="font-medium">One figure per position pair:</span>
          Only one figure is drawn between each pair of positions—this is intentional, to avoid overcrowding the graph with parallel figures.
        </li>
        <li>
          <span class="font-medium">Refresh the page for new parallel figures:</span>
          Two positions are often connected by multiple figures (for example, Vacílala, Enchufa, and Dile que sí all take you from the Open position to Caida), so showing only one figure per position pair leaves many figures out.
          To keep things interesting, you can refresh the page to display a new (randomly chosen) figure from each group of parallel figures.
        </li>
        <li>
          <span class="font-medium">Foundational figures:</span>
          The graph only shows what I call "foundational" figures, which take one dancer's eight-count.
          The website also includes "compound figures", which are composed of multiple sequential foundational figures and span more than one eight-count.
          You can see both foundational and compound figures on the <MyLink :colored="true" :href="route('figures.index')">main figures page</MyLink>.
        </li>
        <li>
          <span class="font-medium">No orphaned positions:</span>
          The graph omits orphaned positions—positions without any incoming or outgoing figures.
          (In case you just created a position and are wondering why it's not showing up in the graph.)
        </li>
      </ul>
    </section>

    <!-- Notify user instead of showing an empty graph -->
    <Warning v-if="$page.props.auth.user && !graph_is_nonempty" class="mt-8 max-w-lg">
      You have not created any positions or figures yet.
      First <MyLink :colored="true" :href="route('positions.create')">create some positions</MyLink>, then connect the positions to <MyLink :colored="true" :href="route('figures.create')">create figures</MyLink>.
    </Warning>

    <!-- Questions and answers -->
    <div v-if="$page.props.auth.user === null" class="mt-8">
      <h2 class="text-xl text-gray-700" id="qa">Questions and answers</h2>
      <ul class="mt-3 space-y-5">
        <li>
          <QuestionAndAnswer>
            <template #question>Can I create new positions and figures?</template>
            <template #answer>
              Sure!
              You'll need an account for that—if you aren't already logged in, you can create one <MyLink :colored="true" :href="route('register')">here</MyLink>.
              You can then create your own set of positions and figures with complete control over names, descriptions, etc.
            </template>
          </QuestionAndAnswer>
        </li>
        <li>
          <QuestionAndAnswer>
            <template #question>I don't like your names.</template>
            <template #answer>
              Fair enough.
              Choosing names was tricky for me—even common figures often have many names (e.g. Enchufa or Enchufla?),
              and many figures and positions don't have well-accepted names at all!
              I've mixed the names I was originally taught with what I've most commonly found online, and done my best to think of sensible new names when I couldn't find an existing one.
              <br>
              You can also create your own set of positions and figures with any names you like—see above.
            </template>
          </QuestionAndAnswer>
        </li>
        <li>
          <QuestionAndAnswer>
            <template #question>What was the motivation for this site?</template>
            <template #answer>
              To explore the idea that Casino's structure is fundamentally simple—at its core lie a handful of fundamental figures connecting a handful of fundamental positions, which appear over and over in different variations.
              At least for me, the idea of chaining together variations on foundational figures made the dance more approachable and less overwhelming—and made it much easier to come up with interesting new combinations and figure sequences I would not have otherwise consider.
              <br>
              And as for the graph theme: as a part-time computer nerd, a graph data structure seemed a perfect fit for representating the interconnections of positions and figures.
            </template>
          </QuestionAndAnswer>
        </li>
      </ul>
    </div>

    <!-- For nerds -->
    <div class="mt-8">
      <h2 class="text-xl text-gray-700" id="nerds">For nerds</h2>
      <p class="mt-0.5 text-gray-600 text-sm">
        (Read: I want to geek out about the tech stack to anyone who will listen.)
      </p>
      <ul class="mt-3 space-y-5">
        <li>
          <QuestionAndAnswer>
            <template #question>I'm into web dev, what's the tech stack?</template>
            <template #answer>
              The web site is built with
              <MyLink :colored="true" href="https://laravel.com/">Laravel</MyLink> and <MyLink :colored="true" href="https://vuejs.org/">Vue</MyLink>;
              it uses an <MyLink :colored="true" href="https://www.sqlite.org/index.html">SQLite</MyLink> database and is powered by <MyLink :colored="true" href="https://www.nginx.com/">Nginx</MyLink>.
              The site is hosted on a Digital Ocean droplet running Ubuntu LTS 22.04.
              The graph on the home page is drawn with <MyLink :colored="true" href="https://graphviz.org/">Graphviz</MyLink>.
            </template>
          </QuestionAndAnswer>
        </li>
        <li>
          <QuestionAndAnswer>
            <template #question>Have source code available?</template>
            <template #answer>
              Sure, <MyLink :colored="true" href="https://github.com/ejmastnak/casinograph">github.com/ejmastnak/casinograph</MyLink>.
            </template>
          </QuestionAndAnswer>
        </li>
        <li>
          <QuestionAndAnswer>
            <template #question>Yuck, you're storing a graph in a relational database?!</template>
            <template #answer>
              I entertained the idea of using a graph-oriented database... but this app generally queries only one level of adjacent vertices per web request,
              and doesn't actually traverse the graph or require any of the classic graph algorithms (finding shortest paths, etc.) that a graph database efficiently supports.
              And so the convenience of using a relational database in the web development ecosystem justifies any (potential) slight performance penalty thus incurred.
              (The one exception is drawing the graph that appears higher up on this page, but Graphviz builds its own representation of the graph when doing that anyway.)
            </template>

          </QuestionAndAnswer>
        </li>
        <li>
          <QuestionAndAnswer>
            <template #question>Tell me more about the data structures.</template>
            <template #answer>
              <p>
                The basic idea is to represent Casino as a directed graph in which positions are vertices and figures are edges.
                The graph is cyclic (obviously—otherwise the dance would be over after a few figures) and permits both
                self-referencing edges (e.g., Guapea points from the Open position back to the Open position, Exhibela from Caida back to Caida, etc.) and parallel edges (e.g., Vacílala, Enchufa, and Dile que sí all take you from the Open position to Caida).
              </p>

              <p class="mt-2">Here are a few more notes:</p>

              <ul class="ml-10 mt-2 list-disc space-y-1">
                <li>
                  I've made a distinction between "foundational figures" (which take a single dancer's eight-count) and "compound figures" (which take two or more eight-counts).
                  This then leads to a natural representation of compound figures like Setenta and Ochenta as a sequence of foundational figures (assuming, of course, that successive figures have compatible starting and ending positions).
                </li>

                <li>
                  I've chosen to uniquely identify a foundational figure by the <span class="italic">combination of</span> its name, starting position, and ending position. This leads to overloaded figures names (e.g. this app has over ten figures called Enchufa), but I see this more as a feature than a bug—it shows the underlying simplicity of Casino figures (it's all just variations and combinations of a handful of foundational figures),
                  and we avoid all the awkward figure names that would result from requiring unique figure names (imagine "Enchufa from Open to Caida", "Enchufa from Uno to Sombrero", "Right-handed enchufa from Open to Caida", "Double-handed enchufa from Open to Caida", etc.).
                </li>

                <li>
                  There are also auxiliary "position families" and "figure families" to help organize related positions and figures.
                  You can filter by family when searching for positions and figures on the corresponding
                  <MyLink :colored="true" :href="route('positions.index')">position</MyLink>
                  and
                  <MyLink :colored="true" :href="route('positions.index')">figure</MyLink>
                  pages.
                </li>
              </ul>

            </template>


          </QuestionAndAnswer>
        </li>
      </ul>
    </div>

    <!-- Footer with copyright -->
    <div class="mt-10 text-sm sm:text-base px-4 xs:px-6 sm:px-8 py-2 text-gray-400 flex gap-x-2 border-t border-gray-200 overflow-x-auto">
      <p>©{{new Date().getFullYear()}}</p>
      <MyLink href="https://ejmastnak.com" class="whitespace-nowrap">Elijan Mastnak</MyLink>
      <a href="mailto:elijan@ejmastnak.com">elijan@ejmastnak.com</a>
    </div>

    <!-- Full screen graph dialog -->
    <Dialog :open="graphIsFullscreen" @close="setGraphIsFullScreen">
      <DialogPanel class="fixed inset-0 bg-white overflow-auto">

        <!-- Graph -->
        <Transition name="zoom" appear>
          <object class="mx-auto max-w-2xl sm:max-w-3xl md:max-w-5xl lg:max-w-7xl xl:max-w-none" type="image/svg+xml" :data="graph_path"></object>
        </Transition>

        <!-- Close button -->
        <Transition name="zoom" appear>
          <DangerButton class="fixed top-4 right-4" @click="setGraphIsFullScreen(false)" >
            <XMarkIcon class="-ml-1 w-6 h-6 text-white shrink-0"/>
            <p class="ml-1">Close</p>
          </DangerButton>
        </Transition>
      </DialogPanel>
    </Dialog>

  </div>
</template>

<style>
.zoom-enter-active {animation: zoom-in 1s ease-in-out;}
.zoom-leave-active {animation: zoom-out 1s ease-in-out;}
@keyframes zoom-in {
from {transform: scale(0,0);}
to {transform: scale(1,1);}
}
@keyframes zoom-out {
from {transform: scale(1,1);}
to {transform: scale(0,0);}
}
</style>
