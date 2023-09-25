<script setup>
import { Head } from '@inertiajs/vue3'
import MyLink from '@/Components/MyLink.vue'
import { QuestionMarkCircleIcon, InformationCircleIcon } from '@heroicons/vue/24/outline'
import QuestionAndAnswer from '@/Components/QuestionAndAnswer.vue'
import { Popover, PopoverButton, PopoverPanel, PopoverOverlay } from '@headlessui/vue'
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

    <h1 class="text-2xl text-gray-800">CasinoGraph</h1>

    <div class="mt-2 flex">
      <div class="max-w-xl ">
        This site approaches Cuban Casino from a computer science perspective:
        you're seeing a representation of Casino as a directed cyclic graph in which the figures (edges) connect the positions (vertices).
        <a href="#more-info" class="p-px rounded-md text-blue-500 hover:text-blue-600 hover:underline focus:outline-none focus:ring-2 focus:ring-blue-700" >
          More info lower on this page.
        </a>
      </div>
      <div class="ml-auto w-fit">

        <!-- What is Casino? -->
        <div class="relative">
          <Popover>
            <PopoverButton class="w-full flex items-center px-3 py-1 bg-blue-50 border border-gray-300 rounded-lg text-sm text-gray-600 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150" >
              <InformationCircleIcon class="-ml-1 h-6 w-6 text-gray-500 shrink-0" />
              <p class="ml-1.5">Wait, what is Casino?</p>
            </PopoverButton>
            <PopoverOverlay class="fixed inset-0 bg-black opacity-20" />

            <PopoverPanel class="absolute right-0 mt-0.5 px-4 py-3 w-96 max-w-sm rounded-lg overflow-hidden bg-gray-50 shadow text-gray-800 z-50">
              <p>
                <MyLink :colored="true" href="https://en.wikipedia.org/wiki/Cuban_salsa">Casino</MyLink> is a social dance from Cuba, and more commonly goes by the name of Cuban salsa (much to the dismay of traditionalists, who insist Casino is the only proper name).
              </p>

              <p class="mt-2">
                It has a loyal following and tends to produce fans who become quite obsessed, and end up spending their free time dancing, nerding out about <MyLink :colored="true" href="https://en.wikipedia.org/wiki/Timba"> timba music</MyLink> and Cuban ethnomusicology, and succumbing to eccentric acts of dedication like building websites devoted to Casino ;)
              </p>

              <p class="mt-2">
                You can read more about Casino <MyLink :colored="true" href="https://en.wikipedia.org/wiki/Cuban_salsa">on Wikipedia</MyLink>.
              </p>
            </PopoverPanel>
          </Popover>
        </div>

        <!-- What is a graph? -->
        <div class="mt-1 relative">
          <Popover>
            <PopoverButton class="w-full flex items-center px-3 py-1 bg-white border border-gray-200 rounded-lg text-sm text-gray-600 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150" >
              <InformationCircleIcon class="-ml-1 h-6 w-6 text-gray-500 shrink-0" />
              <p class="ml-1.5">And what is a graph?</p>
            </PopoverButton>
            <PopoverOverlay class="fixed inset-0 bg-black opacity-20" />

            <PopoverPanel class="absolute right-0 mt-0.5 px-4 py-3 w-96 max-w-sm rounded-lg overflow-hidden bg-gray-50 shadow text-gray-800 z-50">
              <p>
                In the context of this website, a
<MyLink :colored="true" href="https://en.wikipedia.org/wiki/Graph_(abstract_data_type)">graph</MyLink>
                is a data structure used in computer science to efficiently represent a group of connected data points.
                In the comp sci lingo, the data points are called <span class="italic">vertices</span> and the connections between them are called <span class="italic">edges</span>.
              </p>

              <p class="mt-2">
                Graphs are ubiquitous in the modern computational infrastructure—common real-life examples include
                social networks like Facebook and Instagram, where following and friending act as edges connecting users (vertices);
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
    </div>

    <div class="mt-2 overflow-auto">
      <object class="w-full" type="image/svg+xml" data="img/casinograph.svg"></object>
    </div>

    <div class="">
      <h2 class="text-xl text-gray-700" id="more-info">About the graph</h2>
      <ul class="mt-2 list-disc space-y-1">
        <li>
          <span class="font-medium">It's clickable!</span>
          Click on any figure or position to learn more.
        </li>
        <li>
          <span class="font-medium">Simple figures:</span>
          Only simple figures (one dancer's eight-count) are shown.
          Want to see compound figures?
          Visit the <MyLink :colored="true" :href="route('figures.index')">figures page</MyLink> and use the filter to show only compound figures.
        </li>
        <li>
          <span class="font-medium">One figure per position pair:</span>
          Only one figure is drawn between each pair of positions—this is intentional, to avoid overcrowding the graph with parallel figures.
        </li>
        <li>
          <span class="font-medium">Refresh the page for new parallel figures:</span>
          Of course, two positions are often connected by multiple figures (for example, Vacílala, Enchufa, and Dile que sí all take you from the Open position to Caida), so showing only one figure per position pair will leave other figures out.
          To keep things interesting, you can refresh the page to display a new (randomly chosen) set of parallel figures.
        </li>
      </ul>
    </div>

    <div class="mt-8">
      <h2 class="text-xl text-gray-700" id="qa">Questions and answers</h2>
      <ul class="mt-2 list-disc space-y-1">
        <li>
          <QuestionAndAnswer>
            <template #question>Can I add new positions and figures?</template>
            <template #answer>
              That would be cool, please do!
              You'll need an account—send me an email at <a href="mailto:admin@ejmastnak.com" class="p-px rounded-md text-blue-500 hover:text-blue-600 hover:underline focus:outline-none focus:ring-2 focus:ring-blue-700" >
                admin@ejmastnak.com
              </a> (in English, Slovene, or Spanish) and I'll set you up.
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
            </template>
          </QuestionAndAnswer>
        </li>
        <li>
          <QuestionAndAnswer>
            <template #question>There's no videos!</template>
            <template #answer>
              I know.
              There definitely <span class="italic">should</span> be be videos—dance is inherently visual and you can only get so far with plain-text descriptions.
              But that's a project beyond the scope of what I can currently commit to.
            </template>
          </QuestionAndAnswer>
        </li>
        <li>
          <QuestionAndAnswer>
            <template #question>Why do this?</template>
            <template #answer>
              It seemed cool, and it simplifies the dance into variations on a handful of fundamental figures connecting a handful of fundamental positions.
              At least for me, this made Casino more approachable and less overwhelming; maybe it will be helpful and/or interesting to you, too.
            </template>
          </QuestionAndAnswer>
        </li>
      </ul>
    </div>

    <div class="mt-8">
      <h2 class="text-xl text-gray-700" id="nerds">For nerds</h2>
      <ul class="mt-2 list-disc space-y-1">
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
            <template #question>I'm into web dev, what's your text stack?</template>
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
            <template #question>What, you're storing a graph in a relational database?!</template>
            <template #answer>
              I entertained the idea of using a graph-oriented database... but then realized this app generally queries only one level of adjacent vertices per web request,
              and doesn't actually traverse the graph or require any of the classic graph algorithms (finding shortest paths, etc.) that a graph database efficiently supports.
              And so the convenience of using a relational database in the web development ecosystem justifies any (potential) slight performance penalty thus incurred.
              (The one exception is drawing the graph that appears higher up on this page, but Graphviz builds its own representation of the graph when doing that anyway.)
            </template>

          </QuestionAndAnswer>
        </li>
        <li>
          <QuestionAndAnswer>
            <template #question>Tell me more about your data structure.</template>
            <template #answer>
              <p>
                The basic idea is to represent Casino as a directed graph in which positions are vertices and figures are edges.
                The graph is cyclic (obviously—otherwise the dance would be over after a few figures) and permits both
                self-referencing edges (e.g., Guapea points from the Open position back to the Open position, Exhibela from Caida back to Caida, etc.) and parallel edges (e.g., Vacílala, Enchufa, and Dile que sí all take you from the Open position to Caida).
              </p>

              <p class="mt-2">Here are a few more notes:</p>

              <ul class="ml-10 mt-2 list-disc space-y-1">
                <li>
                  I've made a distinction between "simple figures" (which take a single dancer's eight-count) and "compound figures" (which take two or more eight-counts).
                  This then leads to a natural representation of compound figures like Setenta and Ochenta as a sequence of simple figures (assuming, of course, that successive figures have compatible starting and ending positions).
                </li>

                <li>
                  I've chosen to uniquely identify a simple figure by the <span class="italic">combination of</span> its name, starting position, and ending position. This leads to overloaded figures names (e.g. this app has over ten figures called Enchufa), but I see this more as a feature than a bug—it shows the underlying simplicity of Casino figures (it's all just variations and combinations of a handful of foundational figures),
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
      <p class="whitespace-nowrap">Elijan Mastnak</p>
      <a href="mailto:elijan@ejmastnak.com">elijan@ejmastnak.com</a>
    </div>

  </div>
</template>
