import React from "react"
import ReactDOM from "react-dom"

import { forEachNodeExecute, removeAllChildren } from "./util"
import basepathname from "./basepathname"

import PostListing from "../components/PostListing"

function ExampleComponent({ x, data }: { x: string; data: any }) {
  return (
    <div>
      <h1>Hello world!</h1>

      <h2>x: {x}</h2>

      <pre>{data}</pre>
    </div>
  )
}

export function exampleRenderer(root: HTMLElement) {
  forEachNodeExecute(
    root.querySelectorAll(".react-example"),
    async (node: HTMLElement) => {
      // data-data attribute value
      const data = node.dataset.data
      // the element in which you want the component
      // const root = node.querySelector(".root")
      const root = node

      if (!root) {
        throw new Error("Root missing!")
      }

      // if you need to remove the current contents
      // ie. you're taking the progressive enchantment approach, do it here.
      // removeAllChildren(node)

      ReactDOM.render(
        <React.StrictMode>
          <ExampleComponent x="y" data={data} />
        </React.StrictMode>,
        root
      )

      // cleanup
      node.removeAttribute("data-data")
    }
  )
}

export function postListingRenderer(root: HTMLElement) {
  forEachNodeExecute(
    root.querySelectorAll(".k1-postlisting"),
    async (listing: HTMLElement) => {
      const filters = listing.querySelector(
        ".k1-postlisting__filters"
      ) as HTMLElement
      const list = listing.querySelector(".k1-postlisting__list") as HTMLElement
      const pagination = listing.querySelector(
        ".k1-postlisting__pagination"
      ) as HTMLElement
      const root = listing.querySelector(".react-root")

      const nodes = { filters, pagination, list }
      const data = {
        taxonomyTerms: filters
          ? JSON.parse(filters.dataset.taxterms as string)
          : null,
        totalPages: pagination
          ? parseInt(pagination.dataset.total as string, 10)
          : null,
        template: list.dataset.template,
        query: JSON.parse(list.dataset.query as string),
        trackStateInUrl: list.dataset.trackstateinurl === "true",
      }

      // clear out the old children, we are portaling there in a moment
      removeAllChildren(pagination)

      ReactDOM.render(
        <PostListing nodes={nodes} data={data} basepathname={basepathname()} />,
        root
      )

      filters && filters.removeAttribute("data-taxterms")
      pagination && pagination.removeAttribute("data-total")
      list && list.removeAttribute("data-query")
    }
  )
}

export const Renderers: Record<string, typeof exampleRenderer> = {
  exampleRenderer,
  postListingRenderer,
}

export default function autoinitializer(root: HTMLElement) {
  Object.values(Renderers).forEach((renderer) => renderer(root))
}
