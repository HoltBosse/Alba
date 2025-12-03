
// biome-ignore lint/correctness/noUnusedVariables: required in
const Video = Node.create({
	name: 'video',

	group: 'block',
	draggable: true,

	addOptions() {
		return {
			HTMLAttributes: {},
		}
	},

	addAttributes() {
		return {
			src: { default: null },
			autoplay: { default: false },
			muted: { default: false },
			loop: { default: false },
			playsinline: { default: false },
			// `controls` should be null when not present in source HTML so
			// loading from HTML without the attribute doesn't force `true`.
			controls: { default: null },
		}
	},

	parseHTML() {
		return [
			{
				tag: 'video',
				getAttrs: dom => {
					if (!(dom instanceof HTMLElement)) return {}

					// Helper to map boolean attributes by presence
					const bool = name => (dom.hasAttribute(name) ? true : false)

					// Extract src from a <source> child if available
					let src = null
					const sourceEl = dom.querySelector('source')
					if (sourceEl?.getAttribute('src')) {
						src = sourceEl.getAttribute('src')
					} else if (dom.getAttribute('src')) {
						src = dom.getAttribute('src')
					}

					return {
						src,
						autoplay: bool('autoplay'),
						muted: bool('muted'),
						loop: bool('loop'),
						playsinline: bool('playsinline'),
						// For controls, we want `null` when attribute is absent
						controls: dom.hasAttribute('controls') ? true : null,
					}
				},
			},
		]
	},

	renderHTML({ node, HTMLAttributes }) {
		const attrs = mergeAttributes(this.options.HTMLAttributes, HTMLAttributes)
		const { src, autoplay, muted, loop, playsinline, controls } = node.attrs

		const videoAttrs = {
			autoplay: autoplay ? 'autoplay' : null,
			muted: muted ? 'muted' : null,
			loop: loop ? 'loop' : null,
			playsinline: playsinline ? 'playsinline' : null,
			// Only add `controls` attribute when it's explicitly true
			controls: controls === true ? 'controls' : null,
		}

		// Render a <video> element with a child <source src="..."> element
		return ['video', mergeAttributes(attrs, videoAttrs), ['source', { src: src || '' }]]
	},

	addCommands() {
		return {
			setVideo:
				(options) =>
				({ commands, tr, state }) => {
					const attrs = {
						src: options.src || null,
						autoplay: !!options.autoplay,
						muted: !!options.muted,
						loop: !!options.loop,
						playsinline: !!options.playsinline,
						controls: options.controls === undefined ? true : !!options.controls,
					}

					// If selection is inside an existing video node, update it
					const { selection } = state
					const { from, to } = selection
					let replaced = false

					state.doc.nodesBetween(from, to, (node, pos) => {
						if (node.type === this.type) {
							tr.setNodeMarkup(pos, undefined, attrs)
							replaced = true
							return false
						}
					})

					if (!replaced) {
						commands.insertContent({ type: this.name, attrs })
					} else {
						commands.dispatch(tr)
					}

					return true
				},
		}
	},

	addNodeView() {
		return ({ node }) => {
			const dom = document.createElement('video')
			if (node.attrs.autoplay) dom.setAttribute('autoplay', '')
			if (node.attrs.muted) dom.setAttribute('muted', '')
			if (node.attrs.loop) dom.setAttribute('loop', '')
			if (node.attrs.playsinline) dom.setAttribute('playsinline', '')
			if (node.attrs.controls) dom.setAttribute('controls', '')

			const source = document.createElement('source')
			source.setAttribute('src', node.attrs.src || '')
			dom.appendChild(source)

			dom.addEventListener('error', () => {
				dom.style.border = '1px solid red'
			})

			return {
				dom,
				contentDOM: null,
			}
		}
	},
})