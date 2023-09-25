<script setup>
import { Head } from '@inertiajs/vue3'
import MyLink from '@/Components/MyLink.vue'
import QuestionAndAnswer from '@/Components/QuestionAndAnswer.vue'
</script>

<script>
import AppLayout from "@/Layouts/AppLayout.vue";
export default {
  layout: AppLayout,
}
</script>

<template>
  <div class="pb-8">
    <Head title="Home" />

    <h1 class="text-2xl text-gray-800">CasinoGraph</h1>

    <div class="mt-2 max-w-xl">
      This site is a fusion of Cuban Casino and graph theory: you're seeing a representation of Casino as a cyclic directed graph in which the figures (edges) connect the positions (vertices).
      <a href="#more-info" class="p-px rounded-md text-blue-500 hover:text-blue-600 hover:underline focus:outline-none focus:ring-2 focus:ring-blue-700" >
        More info lower on this page.
      </a>
    </div>

    <div class="mt-2 overflow-auto">
      <object class="w-full" type="image/svg+xml" data="img/casinograph.svg"></object>
    </div>

    <div class="">
      <h2 class="text-xl text-gray-700" id="more-info">About the graph</h2>
      <ul class="mt-2 list-disc space-y-1">
        <li>
          <span class="font-semibold">It's clickable!</span>
          Click on any figure or position to learn more.
        </li>
        <li>
          <span class="font-semibold">Simple figures:</span>
          Only simple figures (one dancer's eight-count) are shown.
          Want to see compound figures?
          Visit the <MyLink :colored="true" :href="route('figures.index')">figures page</MyLink> and use the filter to show only compound figures.
        </li>
        <li>
          <span class="font-semibold">One figure per position pair:</span>
          Only one figure is drawn between each pair of positions—this is intentional, to avoid overcrowding the graph with parallel figures.
        </li>
        <li>
          <span class="font-semibold">Refresh the page for new parallel figures:</span>
          Of course, two positions are often connected by multiple figures (for example, Vacílala, Enchufa, and Dile que sí all take you from the Open position to Caida), so showing only one figure per position pair will leave other figures out.
          To keep things interesting, you can refresh the page to display a new (randomly chosen) set of parallel figures.
        </li>
        <li>
          <span class="font-semibold">Graphviz:</span>
          The graph is drawn using <MyLink :colored="true" href="https://graphviz.org/">Graphviz</MyLink>. Thank you, Graphviz!
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
              You'll need an authorized account—send me an email at <a href="mailto:admin@ejmastnak.com" class="p-px rounded-md text-blue-500 hover:text-blue-600 hover:underline focus:outline-none focus:ring-2 focus:ring-blue-700" >
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
              There definitely <span class="font-semibold">should</span> be be videos—dance is inherently visual and you can only get so far with plain-text descriptions.
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
              Yeah, definitely not well-suited to efficient graph operations, but very well-suited to web dev.
              I entertained the idea of using a graph database... but then realized this app only ever queries one level of adjacent vertices per web request,
              and doesn't actually require any of the classic graph algorithms (finding shortest paths, detecting cycles, etc.) that a graph database is meant to support.
              And so the convenience of using a relational database in the web development ecosystem justifies any slight performance penalty thus incurred.
              (The one exception is drawing the graph on this page, but Graphviz builds its own representation of the graph when doing that anyway.)
            </template>

          </QuestionAndAnswer>
        </li>
        <li>
          <QuestionAndAnswer>
            <template #question>Tell me more about your data structure.</template>
            <template #answer>
              <p>
                The basic idea is to represent Casino as a directed graph in which positions are vertices and figures are edges.
                The graph is cyclic (obviously) and permits both
                self-referencing edges (e.g., Guapea points from the Open position back to the Open position, Exhibela from Caida back to Caida, etc.) and parallel edges (e.g., Vacílala, Enchufa, and Dile que sí all take you from the Open position to Caida).
              </p>

              <p class="mt-2">Here are a few more notes:</p>

              <ul class="ml-10 mt-2 list-disc space-y-1">
                <li>
                  I've made a distinction between "simple figures" (which take a single dancer's eight-count) and "compound figures" (which take two or more eight-counts).
                  This then leads to a natural representation of compound figures like Setenta and Ochenta as a sequence of simple figures (assuming, of course, that successive figures have compatible starting and ending positions).
                </li>

                <li>
                  I've chosen to uniquely identify a simple figure by the <span class="font-semibold">combination of</span> its name, starting position, and ending position. This leads to overloaded figures names (e.g. this app has over ten figures called Enchufa), but I see this more as a feature than a bug—it shows the underlying simplicity of Casino figures (it's all just variations and combinations of a handful of foundational figures),
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


  </div>
</template>
