/**
 * Reducer manages the state of the PostListing.
 * The state looks like { query, taxonomyTerms, totalPages, template }.
 * When the query changes, new content is requested. The query changes in response to changes in other props.
 */
const postListReducer = (draft: any, action: any) => {
  switch (action.type) {
    case "removeTerm": {
      const { taxonomyTerms, query } = draft
      const term = taxonomyTerms.find(
        (x: any) => x.term_id === action.payload.term_id
      )
      const { taxonomy, term_id } = term

      term.active = false

      if (taxonomy !== "post_tag" && taxonomy !== "category") {
        // At the time of implementation no other taxonomies exist. These taxonomies have easy-to-use params that can be used.
        // Custom taxonomies require a bit more work, entirely doable though.

        throw new Error(`Unsupported taxonomy ${taxonomy}`)
      } else if (taxonomy === "post_tag") {
        if (Array.isArray(query.tag__in)) {
          query.tag__in.splice(query.tag__in.indexOf(term_id), 1)
        } else {
          query.tag__in = false
        }
      } else if (taxonomy === "category") {
        if (Array.isArray(query.category__in)) {
          query.category__in.splice(query.category__in.indexOf(term_id), 1)
        } else {
          query.category__in = false
        }
      }

      query.paged = 0
      break
    }

    case "addTerm": {
      const { taxonomyTerms } = draft
      const term = taxonomyTerms.find(
        (x: any) => x.term_id === action.payload.term_id
      )

      addTermCase(draft, term)
      break
    }

    case "addTerms": {
      const { taxonomyTerms } = draft
      const arrayOfTerms = action.payload

      for (let i = 0; i < arrayOfTerms.length; i++) {
        const term = taxonomyTerms.find(
          (x: any) => x.term_id === arrayOfTerms[i].term_id
        )

        // taxonomyTerms.find() will fail if the ID doesn't exist in the list anymore.
        // This should "fail" silently in that case.
        if (term) {
          addTermCase(draft, term)
        }
      }

      break
    }

    case "changePage": {
      const { query } = draft

      query.paged = action.payload

      break
    }

    case "updateTotalPageCount": {
      draft.totalPages = action.payload

      break
    }

    default:
      throw new Error("Invalid action " + action.type)
  }
}

/**
 * This part of the reducer is used in two cases.
 */
function addTermCase(draft: any, term: any) {
  const { query } = draft
  const { taxonomy, term_id } = term

  term.active = true

  if (taxonomy !== "post_tag" && taxonomy !== "category") {
    // See comment in removeTerm
    throw new Error(`Unsupported taxonomy ${taxonomy}`)
  } else if (taxonomy === "post_tag") {
    if (Array.isArray(query.tag__in)) {
      query.tag__in.push(term_id)
    } else {
      query.tag__in = [term_id]
    }
  } else if (taxonomy === "category") {
    if (Array.isArray(query.category__in)) {
      query.category__in.push(term_id)
    } else {
      query.category__in = [term_id]
    }
  }

  query.paged = 0
}

export default postListReducer
