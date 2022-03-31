export function setNewUrl(relativePath: string) {
  window.history.pushState(null, "", relativePath)
}

export function removeAllChildren(parent: HTMLElement) {
  while (parent && parent.childNodes && parent.firstChild) {
    parent.removeChild(parent.firstChild)
  }
}

export function forEachNodeExecute(nodelist: NodeList, func: any) {
  Array.from(nodelist).forEach(func)
}
