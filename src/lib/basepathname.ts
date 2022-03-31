/**
 * WordPress guarantees that pages that use pagination append /page/123/ to the end of the URL.
 * Every page can be paginated since every page can contain a paginated PostListing.
 * To generate working links for the pagination, we need to know get "pathname" of the page, and strip off
 * the pagination info. This does exactly that.
 *
 * I could've also used a regular expression or about 3 other things but I made this instead.
 */
export default function basepathname() {
  const { pathname } = window.location
  let basepathname = pathname.split("/")

  // splice already bit me once today, so...
  basepathname.pop()

  if (pathname.includes("/page/")) {
    basepathname.pop()
    basepathname.pop()

    // You can stop facepalming now, it works. 3 parts from the end of the array that needs to be removed, 100% of the time.
  }

  return basepathname.join("/")
}
