import { N as Node3, aB as nodeInputRule, ay as mergeAttributes } from "../index-C5rYLlkZ.mjs";
var inputRegex = /(?:^|\s)(!\[(.+|:?)]\((\S+)(?:(?:\s+)["'](\S+)["'])?\))$/;
var Image = Node3.create({
  name: "image",
  addOptions() {
    return {
      inline: false,
      allowBase64: false,
      HTMLAttributes: {}
    };
  },
  inline() {
    return this.options.inline;
  },
  group() {
    return this.options.inline ? "inline" : "block";
  },
  draggable: true,
  addAttributes() {
    return {
      src: {
        default: null
      },
      alt: {
        default: null
      },
      title: {
        default: null
      },
      width: {
        default: null
      },
      height: {
        default: null
      }
    };
  },
  parseHTML() {
    return [
      {
        tag: this.options.allowBase64 ? "img[src]" : 'img[src]:not([src^="data:"])'
      }
    ];
  },
  renderHTML({ HTMLAttributes }) {
    return ["img", mergeAttributes(this.options.HTMLAttributes, HTMLAttributes)];
  },
  parseMarkdown: (token, helpers) => {
    return helpers.createNode("image", {
      src: token.href,
      title: token.title,
      alt: token.text
    });
  },
  renderMarkdown: (node) => {
    var _a, _b, _c, _d, _e, _f;
    const src = (_b = (_a = node.attrs) == null ? void 0 : _a.src) != null ? _b : "";
    const alt = (_d = (_c = node.attrs) == null ? void 0 : _c.alt) != null ? _d : "";
    const title = (_f = (_e = node.attrs) == null ? void 0 : _e.title) != null ? _f : "";
    return title ? `![${alt}](${src} "${title}")` : `![${alt}](${src})`;
  },
  addCommands() {
    return {
      setImage: (options) => ({ commands }) => {
        return commands.insertContent({
          type: this.name,
          attrs: options
        });
      }
    };
  },
  addInputRules() {
    return [
      nodeInputRule({
        find: inputRegex,
        type: this.type,
        getAttributes: (match) => {
          const [, , alt, src, title] = match;
          return { src, alt, title };
        }
      })
    ];
  }
});
var index_default = Image;
export {
  Image,
  index_default as default,
  inputRegex
};
