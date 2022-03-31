// @ts-nocheck
// I can't be arsed to type this fully right now.
import React, { useRef, useEffect } from "react"
import { createPortal } from "react-dom"
import { useImmerReducer } from "use-immer"

import postListingReducer from "../state/postlisting.reducer"
import { removeAllChildren } from "../lib/util"

const isAdmin = document.body.classList.contains("wp-admin")

// This is the way localization goes unfortunately.
// I could inject it as props but then I'd have to deal with passing it around...
const { i18n } = (window as any).wptheme

/**
 * The actual list part of this component is not managed by React.
 * The filters and the pagination are portaled next to it. This is done
 * because they need to share state. and to do that, they have to be under the same tree.
 */
const Portal = ({ children, node }) => createPortal(children, node)

function setNewUrl(relativePath) {
  window.history.pushState(null, "", relativePath)
}

/**
 * Fetches a bunch of HTML (that is sanitized!) and renders it in the roughest way possible.
 *
 * The list is not managed by React, but the state of it is and it's a long story.
 * TL;DR: I don't want to create the templates twice, in PHP and in React. So they're done in PHP.
 *
 * Errors are handled in the function that calls it.
 */
async function updatePostsInList(
  list: HTMLElement,
  query: string,
  template: string,
  fetchOptions: any = {}
) {
  const res = await fetch(
    `/wp-json/k1/v1/postlisting/query?template=${template}&args=${JSON.stringify(
      query
    )}`,
    fetchOptions
  )
  const json = await res.json()

  const noSignalOrSignalIsntAborted = fetchOptions.signal
    ? !fetchOptions.signal.aborted
    : true

  if (noSignalOrSignalIsntAborted) {
    /**
     * It works, and it's not like it's causing any performance issues.
     * XSS? TL;DR: no problem, whole site is one big xss playground for authenticated users already.
     */
    list.innerHTML = json.html

    await new Promise((resolve) => setTimeout(resolve)) // Waiting for 1 tick ensures the DOM is ready to be manipulated
  }

  // any dom manipulation that you need to do? Do it here.

  return json
}

/**
 * This could be a one-liner and in the utils but it has no use anywhere else.
 */
function produceTerms(acc: any, term: any) {
  const { taxonomy } = term

  if (!acc[taxonomy]) {
    acc[taxonomy] = [term]
  } else {
    acc[taxonomy].push(term)
  }

  return acc
}

/**
 * Progressive enhancement for PostListing
 *
 * I've resorted to innerHTML, because a HTML parser would be more
 * costly to use. Lots of bytes for converting HTML into React components.
 */
function PostListing({
  nodes,
  data,
  basepathname,
}: {
  nodes: Record<string, HTMLElement>
  data: any
  basepathname: string
}) {
  const {
    taxonomyTerms,
    totalPages,
    query,
    template,
    trackStateInUrl,
    autoscroll,
  } = data
  const [state, dispatch] = useImmerReducer(postListingReducer, {
    query,
    taxonomyTerms,
    totalPages,
    template,
  })
  const isFirstRender = useRef(true)
  const abortRef = useRef(null)

  // The list has the ability to render multiple taxonomies, but the terms and their statuses
  // are tracked under a single array. For display purposes, an object with the taxonomy name as key
  // works better.
  const taxonomyObject = state.taxonomyTerms
    ? state.taxonomyTerms.reduce(produceTerms, {})
    : null

  /**
   * Check the URL for query parameters that indicate which need to be turned on
   * upon loading.
   */
  useEffect(() => {
    if (!trackStateInUrl || isAdmin) return

    const urlParams = new URL(window.location.href).searchParams
    const args = JSON.parse(urlParams.get("args"))

    if (args && args.taxonomies) {
      const taxonomies = args.taxonomies

      // Restructure the data for the reducer which takes an array of term objects
      const arrayOfTerms = Object.keys(taxonomies)
        .map((taxonomy) => {
          const terms = taxonomies[taxonomy]
          const list = terms.map((term_id) => ({ term_id, taxonomy }))

          return list
        })
        .reduce((acc, list) => acc.concat(list), [])

      dispatch({ type: "addTerms", payload: arrayOfTerms })

      // Trigger immediate update to avoid showing wrong content
      // (which was server-rendered without any filters)
      isFirstRender.current = false
    }
  }, [trackStateInUrl])

  /**
   * Updates changes to the filters and pagination into the URL.
   * The URL is used by the above effect to re-set the filters. Pagination is set by the server already.
   */
  useEffect(() => {
    if (!trackStateInUrl || isAdmin) return

    const activeTerms = state.taxonomyTerms
      ? state.taxonomyTerms.filter((term) => term.active)
      : null
    const taxonomies = activeTerms ? activeTerms.reduce(produceTerms, {}) : {}

    Object.keys(taxonomies).forEach((taxName) => {
      taxonomies[taxName] = taxonomies[taxName].map((term) => {
        const { term_id } = term

        return term_id
      })
    })

    const enabledFilters =
      Object.keys(taxonomies).length > 0
        ? JSON.stringify({ taxonomies })
        : false

    const url = [
      basepathname,
      state.query.paged !== 0 ? `/page/${state.query.paged}/` : "/",
      enabledFilters ? `?args=${enabledFilters}` : "",
    ].join("")

    setNewUrl(url)
  }, [state.taxonomyTerms, state.query, trackStateInUrl])

  /**
   * Updates the list when the query changes.
   */
  useEffect(() => {
    console.log("updating")

    // This avoids running the code on first render before any selections. The first batch of content
    // is already rendered before this component even inits.
    if (isFirstRender.current) {
      isFirstRender.current = false

      return
    }

    async function update() {
      const query = state.query

      if (abortRef.current) {
        abortRef.current.abort()
      }

      abortRef.current = new AbortController()

      try {
        const json = await updatePostsInList(nodes.list, query, template, {
          signal: abortRef.current.signal,
        })

        // Don't want to scroll? Add an option to disable this.
        // autoscroll &&
        ;(nodes.list.parentNode as HTMLElement).scrollIntoView({
          block: "start",
          behavior: "smooth",
        })
        dispatch({ type: "updateTotalPageCount", payload: json.pages })
      } catch (e) {
        const { name } = e
        const error = document.createElement("h2")

        switch (name) {
          case "AbortError": {
            // Request was aborted. New one was made afterwards.
            error.textContent = i18n["Loading"]

            break
          }

          case "TypeError": {
            // fetch failed. Did the internet go down? Or is the server down?
            // error.textContent = 'Something went wrong! Try doing that again in a moment.'
            error.textContent = i18n["PostListFetchError"]

            break
          }

          case "SyntaxError": {
            // JSON.parse failed. Culprit is most likely a broken JSON response from server.
            // It does that when someone makes a mistake in PHP.
            error.textContent = i18n["PostListJsonError"]

            break
          }
        }

        removeAllChildren(nodes.list)
        nodes.list.appendChild(error)
      }

      abortRef.current = null
    }

    update()
  }, [
    // This is the only thing that changes. Every time the query changes, the update should run.
    state.query,
    nodes.list,
    template,
  ])

  return (
    <React.Fragment>
      {nodes.filters ? (
        <Portal node={nodes.filters}>
          <TaxonomyTermFilters
            dispatch={dispatch}
            taxonomies={taxonomyObject}
          />
        </Portal>
      ) : null}

      {nodes.pagination ? (
        <Portal node={nodes.pagination}>
          <Pagination
            basepathname={basepathname}
            dispatch={dispatch}
            state={state}
          />
        </Portal>
      ) : null}
    </React.Fragment>
  )
}

/**
 * Displays lists of taxonomy terms and dispatches actions that modify the state.query on interaction.
 */
function TaxonomyTermFilters({
  taxonomies,
  dispatch,
}: {
  taxonomies: any
  dispatch: any
}) {
  return (
    <div>
      {Object.keys(taxonomies).map((taxName) => {
        const terms = Object.values(taxonomies[taxName])

        const on = (term) => () => dispatch({ type: "addTerm", payload: term })
        const off = (term) => () =>
          dispatch({ type: "removeTerm", payload: term })

        return (
          <div key={taxName}>
            <h5>
              <strong>{i18n[taxName]}</strong>
            </h5>

            {terms.map((term: any) => (
              <FilterButton
                term={term}
                onClick={term.active ? off(term) : on(term)}
                key={term.term_id}
              />
            ))}
          </div>
        )
      })}
    </div>
  )
}

/**
 * Displays a button in TaxonomyTermFilters
 */
function FilterButton({ term, onClick }) {
  const { name, term_id, taxonomy, active } = term
  const className = `k1-button term ${active ? "active" : "inactive"}`

  return (
    <a
      data-id={term_id}
      data-taxonomy={taxonomy}
      role="button"
      className={className}
      onClick={onClick}
    >
      {name}
    </a>
  )
}

/**
 * Displays the current pagination based on the total page amount and current page.
 * Also dispatches actions that modify state.query.
 */
function Pagination({ state, dispatch, basepathname }) {
  const { totalPages, query } = state

  // Page 0 is actually page 1. Don't ask, WordPress did it this way.
  // It goes from 0 to 2 for some reason I do not know.
  const currentPage = query.paged === 0 ? 1 : query.paged

  const Button = ({
    children,
    href,
    pageNum,
    className = "pagination-button",
    title = null,
    ...rest
  }: {
    children: React.ReactNode
    href: string
    pageNum: number
    className: string
    title?: string
    rest?: any
  }) => {
    const changePage = (e) => {
      e.preventDefault()

      dispatch({ type: "changePage", payload: pageNum })
    }

    return (
      <a href={href} className={className} {...rest} onClick={changePage}>
        {children}
      </a>
    )
  }

  const First = () => {
    if (currentPage === 0 || currentPage === 1) return null

    const href = basepathname

    return (
      <Button
        href={href}
        pageNum={1}
        className="pagination-button"
        title={i18n["Pagination: First"]}
      >
        {i18n["Pagination: First"]}
      </Button>
    )
  }

  const Last = () => {
    if (currentPage === totalPages || totalPages === 0) return null

    const href = basepathname + `/page/${totalPages}/`

    return (
      <Button
        href={href}
        pageNum={totalPages}
        className="pagination-button"
        title={i18n["Pagination: Last"]}
      >
        {i18n["Pagination: Last"]}
      </Button>
    )
  }

  const Next = () => {
    if (currentPage === totalPages || totalPages === 0) return null

    const nextPage = currentPage + 1
    const href = basepathname + `/page/${nextPage}/`

    return (
      <Button
        href={href}
        pageNum={nextPage}
        className="pagination-button"
        title={i18n["Pagination: Next"]}
      >
        {i18n["Pagination: Next"]}
      </Button>
    )
  }

  const Previous = () => {
    if (currentPage === 0 || currentPage === 1) return null

    const previousPage = currentPage - 1
    const href = basepathname + `/page/${previousPage}/`

    return (
      <Button
        href={href}
        pageNum={previousPage}
        className="pagination-button"
        title={i18n["Pagination: Previous"]}
      >
        {i18n["Pagination: Previous"]}
      </Button>
    )
  }

  const Numbers = () => (
    <React.Fragment>
      {Array(totalPages)
        .fill(1)
        .map((useless, index) => {
          let number = index + 1

          const href = basepathname + `/page/${number}/`

          return (
            <Button
              href={href}
              pageNum={number}
              key={href}
              className={`pagination-button number ${
                number === currentPage ? "active" : ""
              }`}
            >
              {number}
            </Button>
          )
        })}
    </React.Fragment>
  )

  return (
    <div className="k1-container">
      <First />
      <Previous />
      <Numbers />
      <Next />
      <Last />
    </div>
  )
}

export default PostListing
