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

    <embed src="img/casinograph.svg">

    <svg class="hidden" width="355pt" height="305pt"
      viewBox="0.00 0.00 354.70 305.00" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
      <g id="graph0" class="graph" transform="scale(1 1) rotate(0) translate(4 301)">
        <title>CasinoGraph</title>
        <polygon fill="white" stroke="none" points="-4,4 -4,-301 350.7,-301 350.7,4 -4,4"/>
        <!-- 2 -->
        <g id="node1" class="node">
          <title>2</title>
          <g id="a_node1"><a xlink:href="https://casinograph.ejmastnak.com/positions/2" xlink:title="Caida">
            <ellipse fill="#eff6ff" stroke="#172554" cx="142.7" cy="-279" rx="33.31" ry="18"/>
            <text text-anchor="middle" x="142.7" y="-273.2" font-family="Figtree" font-size="14.00" fill="#172554">Caida</text>
          </a>
          </g>
        </g>
        <!-- 2&#45;&gt;2 -->
        <g id="edge3" class="edge">
          <title>2&#45;&gt;2</title>
          <g id="a_edge3"><a xlink:href="https://casinograph.ejmastnak.com/figures/3" xlink:title=" Exhíbela ">
            <path fill="none" stroke="#172554" d="M173,-287.13C184.5,-287.45 194.01,-284.73 194.01,-279 194.01,-275.24 189.91,-272.78 183.85,-271.62"/>
            <polygon fill="#172554" stroke="#172554" points="184.22,-268.06 174,-270.87 183.73,-275.05 184.22,-268.06"/>
          </a>
          </g>
          <g id="a_edge3&#45;label"><a xlink:href="https://casinograph.ejmastnak.com/figures/3" xlink:title=" Exhíbela ">
            <text text-anchor="middle" x="224.01" y="-273.2" font-family="Figtree" font-size="14.00" fill="#172554"> Exhíbela </text>
          </a>
          </g>
        </g>
        <!-- 3 -->
        <g id="node2" class="node">
          <title>3</title>
          <g id="a_node2"><a xlink:href="https://casinograph.ejmastnak.com/positions/3" xlink:title="Open">
            <ellipse fill="#eff6ff" stroke="#172554" cx="73.7" cy="-192" rx="32.33" ry="18"/>
            <text text-anchor="middle" x="73.7" y="-186.2" font-family="Figtree" font-size="14.00" fill="#172554">Open</text>
          </a>
          </g>
        </g>
        <!-- 2&#45;&gt;3 -->
        <g id="edge1" class="edge">
          <title>2&#45;&gt;3</title>
          <g id="a_edge1"><a xlink:href="https://casinograph.ejmastnak.com/figures/1" xlink:title=" Díle que no ">
            <path fill="none" stroke="#172554" d="M110.05,-274.35C73.31,-269.49 17.08,-259.53 4.2,-243 -8.47,-226.73 13.01,-213.41 35.34,-204.62"/>
            <polygon fill="#172554" stroke="#172554" points="36.35,-207.61 44.55,-200.9 33.95,-201.04 36.35,-207.61"/>
          </a>
          </g>
          <g id="a_edge1&#45;label"><a xlink:href="https://casinograph.ejmastnak.com/figures/1" xlink:title=" Díle que no ">
            <text text-anchor="middle" x="42.95" y="-229.7" font-family="Figtree" font-size="14.00" fill="#172554"> Díle que no </text>
          </a>
          </g>
        </g>
        <!-- 3&#45;&gt;2 -->
        <g id="edge4" class="edge">
          <title>3&#45;&gt;2</title>
          <g id="a_edge4"><a xlink:href="https://casinograph.ejmastnak.com/figures/4" xlink:title=" Enchufa ">
            <path fill="none" stroke="#172554" d="M82.01,-209.57C87.49,-219.72 95.2,-232.69 103.7,-243 107.54,-247.65 112,-252.24 116.51,-256.48"/>
            <polygon fill="#172554" stroke="#172554" points="113.76,-258.62 123.55,-262.69 118.44,-253.41 113.76,-258.62"/>
          </a>
          </g>
          <g id="a_edge4&#45;label"><a xlink:href="https://casinograph.ejmastnak.com/figures/4" xlink:title=" Enchufa ">
            <text text-anchor="middle" x="132.2" y="-229.7" font-family="Figtree" font-size="14.00" fill="#172554"> Enchufa </text>
          </a>
          </g>
        </g>
        <!-- 3&#45;&gt;2 -->
        <g id="edge7" class="edge">
          <title>3&#45;&gt;2</title>
          <g id="a_edge7"><a xlink:href="https://casinograph.ejmastnak.com/figures/7" xlink:title=" Díle que sí ">
            <path fill="none" stroke="#172554" d="M104.96,-196.77C124.53,-200.92 148.31,-209.65 160.7,-228 165.69,-235.39 164.35,-244.33 160.74,-252.6"/>
            <polygon fill="#172554" stroke="#172554" points="157.22,-250.74 155.49,-261.19 163.36,-254.1 157.22,-250.74"/>
          </a>
          </g>
          <g id="a_edge7&#45;label"><a xlink:href="https://casinograph.ejmastnak.com/figures/7" xlink:title=" Díle que sí ">
            <text text-anchor="middle" x="200.7" y="-229.7" font-family="Figtree" font-size="14.00" fill="#172554"> Díle que sí </text>
          </a>
          </g>
        </g>
        <!-- 3&#45;&gt;3 -->
        <g id="edge2" class="edge">
          <title>3&#45;&gt;3</title>
          <g id="a_edge2"><a xlink:href="https://casinograph.ejmastnak.com/figures/2" xlink:title=" Guapea ">
            <path fill="none" stroke="#172554" d="M105.65,-196.08C115.91,-196.04 124.04,-194.68 124.04,-192 124.04,-190.37 121.02,-189.23 116.34,-188.57"/>
            <polygon fill="#172554" stroke="#172554" points="116.84,-185.04 106.65,-187.92 116.42,-192.03 116.84,-185.04"/>
          </a>
          </g>
          <g id="a_edge2&#45;label"><a xlink:href="https://casinograph.ejmastnak.com/figures/2" xlink:title=" Guapea ">
            <text text-anchor="middle" x="152.16" y="-186.2" font-family="Figtree" font-size="14.00" fill="#172554"> Guapea </text>
          </a>
          </g>
        </g>
        <!-- 3&#45;&gt;3 -->
        <g id="edge8" class="edge">
          <title>3&#45;&gt;3</title>
          <g id="a_edge8"><a xlink:href="https://casinograph.ejmastnak.com/figures/8" xlink:title=" Díle que sí ">
            <path fill="none" stroke="#172554" d="M104.18,-198.6C136.99,-202.01 180.29,-199.82 180.29,-192 180.29,-185.01 145.64,-182.51 114.85,-184.51"/>
            <polygon fill="#172554" stroke="#172554" points="114.85,-181.08 105.18,-185.4 115.44,-188.06 114.85,-181.08"/>
          </a>
          </g>
          <g id="a_edge8&#45;label"><a xlink:href="https://casinograph.ejmastnak.com/figures/8" xlink:title=" Díle que sí ">
            <text text-anchor="middle" x="216.29" y="-186.2" font-family="Figtree" font-size="14.00" fill="#172554"> Díle que sí </text>
          </a>
          </g>
        </g>
        <!-- 8 -->
        <g id="node4" class="node">
          <title>8</title>
          <g id="a_node4"><a xlink:href="https://casinograph.ejmastnak.com/positions/8" xlink:title="Setenta">
            <ellipse fill="#eff6ff" stroke="#172554" cx="117.7" cy="-105" rx="40.62" ry="18"/>
            <text text-anchor="middle" x="117.7" y="-99.2" font-family="Figtree" font-size="14.00" fill="#172554">Setenta</text>
          </a>
          </g>
        </g>
        <!-- 3&#45;&gt;8 -->
        <g id="edge10" class="edge">
          <title>3&#45;&gt;8</title>
          <g id="a_edge10"><a xlink:href="https://casinograph.ejmastnak.com/figures/10" xlink:title=" Díle que sí ">
            <path fill="none" stroke="#172554" d="M82.39,-174.21C88.56,-162.29 96.96,-146.06 104.02,-132.42"/>
            <polygon fill="#172554" stroke="#172554" points="107.39,-134.54 108.88,-124.05 101.17,-131.32 107.39,-134.54"/>
          </a>
          </g>
          <g id="a_edge10&#45;label"><a xlink:href="https://casinograph.ejmastnak.com/figures/10" xlink:title=" Díle que sí ">
            <text text-anchor="middle" x="134.7" y="-142.7" font-family="Figtree" font-size="14.00" fill="#172554"> Díle que sí </text>
          </a>
          </g>
        </g>
        <!-- 5 -->
        <g id="node3" class="node">
          <title>5</title>
          <g id="a_node3"><a xlink:href="https://casinograph.ejmastnak.com/positions/5" xlink:title="Open DH">
            <ellipse fill="#eff6ff" stroke="#172554" cx="197.7" cy="-18" rx="47.45" ry="18"/>
            <text text-anchor="middle" x="197.7" y="-12.2" font-family="Figtree" font-size="14.00" fill="#172554">Open DH</text>
          </a>
          </g>
        </g>
        <!-- 5&#45;&gt;2 -->
        <g id="edge6" class="edge">
          <title>5&#45;&gt;2</title>
          <g id="a_edge6"><a xlink:href="https://casinograph.ejmastnak.com/figures/6" xlink:title=" Enchufa ">
            <path fill="none" stroke="#172554" d="M232.29,-30.75C243.33,-36.16 254.52,-43.72 261.7,-54 292.6,-98.25 296.69,-173.36 279.7,-210 262.97,-246.08 218.91,-263.22 185.24,-271.23"/>
            <polygon fill="#172554" stroke="#172554" points="184.74,-267.97 175.72,-273.52 186.23,-274.81 184.74,-267.97"/>
          </a>
          </g>
          <g id="a_edge6&#45;label"><a xlink:href="https://casinograph.ejmastnak.com/figures/6" xlink:title=" Enchufa ">
            <text text-anchor="middle" x="318.2" y="-142.7" font-family="Figtree" font-size="14.00" fill="#172554"> Enchufa </text>
          </a>
          </g>
        </g>
        <!-- 5&#45;&gt;8 -->
        <g id="edge9" class="edge">
          <title>5&#45;&gt;8</title>
          <g id="a_edge9"><a xlink:href="https://casinograph.ejmastnak.com/figures/9" xlink:title=" Díle que sí ">
            <path fill="none" stroke="#172554" d="M193.03,-36.21C189.56,-46.6 183.97,-59.6 175.7,-69 169.91,-75.58 162.58,-81.3 155.09,-86.1"/>
            <polygon fill="#172554" stroke="#172554" points="153.46,-83.58 146.63,-91.68 157.02,-89.61 153.46,-83.58"/>
          </a>
          </g>
          <g id="a_edge9&#45;label"><a xlink:href="https://casinograph.ejmastnak.com/figures/9" xlink:title=" Díle que sí ">
            <text text-anchor="middle" x="221.7" y="-55.7" font-family="Figtree" font-size="14.00" fill="#172554"> Díle que sí </text>
          </a>
          </g>
        </g>
        <!-- 8&#45;&gt;5 -->
        <g id="edge5" class="edge">
          <title>8&#45;&gt;5</title>
          <g id="a_edge5"><a xlink:href="https://casinograph.ejmastnak.com/figures/5" xlink:title=" Enchufa ">
            <path fill="none" stroke="#172554" d="M113.71,-86.89C112.29,-76.54 112.35,-63.54 118.7,-54 125.38,-43.96 135.65,-36.77 146.61,-31.64"/>
            <polygon fill="#172554" stroke="#172554" points="147.84,-34.5 155.77,-27.48 145.19,-28.03 147.84,-34.5"/>
          </a>
          </g>
          <g id="a_edge5&#45;label"><a xlink:href="https://casinograph.ejmastnak.com/figures/5" xlink:title=" Enchufa ">
            <text text-anchor="middle" x="147.2" y="-55.7" font-family="Figtree" font-size="14.00" fill="#172554"> Enchufa </text>
          </a>
          </g>
        </g>
      </g>
    </svg>

  </div>
</template>
