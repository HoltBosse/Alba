
// biome-ignore lint/correctness/noUnusedVariables: required in
const Figcaption = Node.create({
  name: 'figcaption',

  content: 'paragraph+',
  group: 'block',
  selectable: false,
  draggable: false,

  parseHTML() {
    return [
      {
        tag: 'figcaption',
      },
    ]
  },

  renderHTML({ HTMLAttributes }) {
    return ['figcaption', mergeAttributes(HTMLAttributes), 0]
  },

  addProseMirrorPlugins() {
    return [
      new Plugin({
        key: new PluginKey('figcaption-enter'),
        props: {
          handleKeyDown(view, event) {
            if (event.key === 'Enter') {
              const { state, dispatch } = view
              const { selection } = state
              const $pos = selection.$from
              let debug = ''
              // Find the parent figure node
              let figureDepth = null
              for (let d = $pos.depth; d > 0; d--) {
                if ($pos.node(d).type.name === 'figure') {
                  figureDepth = d
                  break
                }
              }
              const parentIsFigcaption = $pos.node($pos.depth - 1).type.name === 'figcaption'
              debug += `figureDepth: ${figureDepth}, currentNode: ${$pos.node($pos.depth).type.name}, parentIsFigcaption: ${parentIsFigcaption}\n`
              if (figureDepth !== null && parentIsFigcaption) {
                // Only trigger if at end of last paragraph in figcaption
                const figcaptionNode = $pos.node($pos.depth - 1)
                const isLastParagraph = $pos.index($pos.depth - 1) === figcaptionNode.childCount - 1
                const atEnd = $pos.parentOffset === $pos.parent.content.size
                debug += `isLastParagraph: ${isLastParagraph}, atEnd: ${atEnd}\n`
                // Only break out if current paragraph is empty
                const currentParagraphIsEmpty = $pos.parent.textContent === ''
                debug += `currentParagraphIsEmpty: ${currentParagraphIsEmpty}\n`
                if (isLastParagraph && atEnd && currentParagraphIsEmpty) {
                  const figurePos = $pos.after(figureDepth)
                  debug += `figurePos: ${figurePos}\n`
                  // Find the empty paragraph position and size before any changes
                  const figcaptionNode = $pos.node($pos.depth - 1)
                  const paraIndex = $pos.index($pos.depth - 1)
                  const paraNode = figcaptionNode.child(paraIndex)
                  const paraPos = $pos.start($pos.depth - 1)
                  let tr = state.tr
                  // Always check for and delete any empty paragraph at the end of figcaption
                  // Use the already declared figcaptionNode
                  let lastParaIndex = figcaptionNode.childCount - 1
                  let lastParaNode = figcaptionNode.child(lastParaIndex)
                  let lastParaPos = $pos.start($pos.depth - 1)
                  for (let i = 0; i < lastParaIndex; i++) {
                    lastParaPos += figcaptionNode.child(i).nodeSize
                  }
                  if (lastParaNode && lastParaNode.type.name === 'paragraph' && lastParaNode.textContent === '') {
                    tr = tr.delete(lastParaPos, lastParaPos + lastParaNode.nodeSize)
                  }
                  // Recalculate figure position after delete
                  const resolved = tr.doc.resolve(lastParaPos)
                  let newFigureDepth = null
                  for (let d = resolved.depth; d > 0; d--) {
                    if (resolved.node(d).type.name === 'figure') {
                      newFigureDepth = d
                      break
                    }
                  }
                  if (newFigureDepth !== null) {
                    const newFigurePos = resolved.after(newFigureDepth)
                    tr = tr.insert(newFigurePos, tr.doc.type.schema.nodes.paragraph.create())
                    tr.setSelection(state.selection.constructor.near(tr.doc.resolve(newFigurePos + 1)))
                  }
                  dispatch(tr)
                  event.preventDefault()
                  console.log('[figcaption enter handler]', debug)
                  return true
                }
              }
              console.log('[figcaption enter handler - not triggered]', debug)
            }
            return false
          },
        },
      }),
    ]
  },
})
