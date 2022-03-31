// import "vite/modulepreload-polyfill"
import componentInitializer from "./lib/render-component"

import "normalize.css"
import "./styl/index.styl"

componentInitializer(document.documentElement)

async function main() {
  console.log("Hello world")

  const test = await import("./lib/test-dynamic-import")

  test.default()
}

console.log("Starting...")
main()
