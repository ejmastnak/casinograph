# CasinoGraph

This repository contains the source code for [casinograph.ejmastnak.com](https://casinograph.ejmastnak.com/), a website dedicated to modeling Cuban Casino (i.e. the Cuban social dance commonly know as [Cuban salsa](https://en.wikipedia.org/wiki/Cuban_salsa)) as a directed cyclic graph in which the figures (edges) connect the positions (vertices).

See [the home page](https://casinograph.ejmastnak.com/) for more information about the project; there's also [a section for nerds](https://casinograph.ejmastnak.com/#nerds) discussing a bit about the technical side and graph analogy.

## Tech stack

Your typical CRUD web app with a JavaScript frontend and a PHP backend talking to a relational database:

- Backend: [Laravel](https://laravel.com/)
- Database: [SQLite](https://www.sqlite.org/index.html)
- Frontend: [Vue.js](https://vuejs.org/), 
- CSS: [Tailwind CSS](https://tailwindcss.com/)

Additional dependency: the graph on the home page is drawn using [Graphviz](https://www.graphviz.org/), so if you want to fully replicate the website you'll need Graphviz installed on your server. Most Linux distros should provide a `graphviz` package satisfying this dependency.

## License

CasinoGraph is open-sourced software licensed under the GNU General Public License v3.0.
