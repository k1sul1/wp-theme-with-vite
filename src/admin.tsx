// import "vite/modulepreload-polyfill"
import componentInitializer, {
  exampleRenderer,
  postListingRenderer,
} from "./lib/render-component"
// import { attachObservers } from './lib/util'

// Typings for WP probably exist but you need to find those yourself if you care about those.
// @ts-expect-error
const wp = window.wp

// @ts-expect-error
const acf = window.acf

async function main() {
  wp &&
    wp.domReady(() => {
      componentInitializer(document.documentElement)
    })

  // Components need to be re-inited after they change.
  acf &&
    acf.addAction(
      "render_block_preview",
      (element: HTMLElement[], data: any) => {
        const { name } = data

        switch (name) {
          case "acf/example": {
            exampleRenderer(element[0])
            break
          }

          case "acf/postlisting": {
            postListingRenderer(element[0])
            break
          }

          // no default
        }
      }
    )
}

main()
