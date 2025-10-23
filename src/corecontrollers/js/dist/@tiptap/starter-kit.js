import { N as Node3, aV as wrappingInputRule, ay as mergeAttributes, M as Mark, aw as markPasteRule, av as markInputRule, aT as textblockTypeInputRule, aB as nodeInputRule, h as canInsertNode, ao as isNodeSelection, aJ as renderNestedMarkdownContent, c as Extension, aF as parseIndentedBlocks, am as isNodeActive, af as isAtStartOfNode, ae as isAtEndOfNode, $ as getNodeType, Z as getNodeAtPosition } from "../index-C5rYLlkZ.mjs";
import { P as Plugin, c as PluginKey, T as TextSelection, b as Selection, N as NodeSelection } from "../tiptap-pm-entry-CDghfMav.mjs";
import { H as Heading } from "../index-BLoaGNOQ.mjs";
import { L as Link } from "../index-G4lzJV12.mjs";
import { P as Paragraph } from "../index-pTU3zJzP.mjs";
import { D as Dropcursor, G as Gapcursor, U as UndoRedo, T as TrailingNode } from "../index-BrLiHPUA.mjs";
var h = (tag, attributes) => {
  if (tag === "slot") {
    return 0;
  }
  if (tag instanceof Function) {
    return tag(attributes);
  }
  const { children, ...rest } = attributes != null ? attributes : {};
  if (tag === "svg") {
    throw new Error("SVG elements are not supported in the JSX syntax, use the array syntax instead");
  }
  return [tag, rest, children];
};
var inputRegex$3 = /^\s*>\s$/;
var Blockquote = Node3.create({
  name: "blockquote",
  addOptions() {
    return {
      HTMLAttributes: {}
    };
  },
  content: "block+",
  group: "block",
  defining: true,
  parseHTML() {
    return [{ tag: "blockquote" }];
  },
  renderHTML({ HTMLAttributes }) {
    return /* @__PURE__ */ h("blockquote", { ...mergeAttributes(this.options.HTMLAttributes, HTMLAttributes), children: /* @__PURE__ */ h("slot", {}) });
  },
  parseMarkdown: (token, helpers) => {
    return helpers.createNode("blockquote", void 0, helpers.parseChildren(token.tokens || []));
  },
  renderMarkdown: (node, h2) => {
    if (!node.content) {
      return "";
    }
    const lines = [];
    node.content.forEach((child) => {
      const lineContent = h2.renderChildren(child);
      const withPrefix = lineContent.split("\n").map((line) => `> ${line}`).join("\n");
      lines.push(withPrefix);
    });
    const linesWithSeparators = lines.flatMap((line) => [line, "> "]);
    return linesWithSeparators.slice(0, -1).join("\n");
  },
  addCommands() {
    return {
      setBlockquote: () => ({ commands }) => {
        return commands.wrapIn(this.name);
      },
      toggleBlockquote: () => ({ commands }) => {
        return commands.toggleWrap(this.name);
      },
      unsetBlockquote: () => ({ commands }) => {
        return commands.lift(this.name);
      }
    };
  },
  addKeyboardShortcuts() {
    return {
      "Mod-Shift-b": () => this.editor.commands.toggleBlockquote()
    };
  },
  addInputRules() {
    return [
      wrappingInputRule({
        find: inputRegex$3,
        type: this.type
      })
    ];
  }
});
var starInputRegex$1 = /(?:^|\s)(\*\*(?!\s+\*\*)((?:[^*]+))\*\*(?!\s+\*\*))$/;
var starPasteRegex$1 = /(?:^|\s)(\*\*(?!\s+\*\*)((?:[^*]+))\*\*(?!\s+\*\*))/g;
var underscoreInputRegex$1 = /(?:^|\s)(__(?!\s+__)((?:[^_]+))__(?!\s+__))$/;
var underscorePasteRegex$1 = /(?:^|\s)(__(?!\s+__)((?:[^_]+))__(?!\s+__))/g;
var Bold = Mark.create({
  name: "bold",
  addOptions() {
    return {
      HTMLAttributes: {}
    };
  },
  parseHTML() {
    return [
      {
        tag: "strong"
      },
      {
        tag: "b",
        getAttrs: (node) => node.style.fontWeight !== "normal" && null
      },
      {
        style: "font-weight=400",
        clearMark: (mark) => mark.type.name === this.name
      },
      {
        style: "font-weight",
        getAttrs: (value) => /^(bold(er)?|[5-9]\d{2,})$/.test(value) && null
      }
    ];
  },
  renderHTML({ HTMLAttributes }) {
    return /* @__PURE__ */ h("strong", { ...mergeAttributes(this.options.HTMLAttributes, HTMLAttributes), children: /* @__PURE__ */ h("slot", {}) });
  },
  markdownTokenName: "strong",
  parseMarkdown: (token, helpers) => {
    return helpers.applyMark("bold", helpers.parseInline(token.tokens || []));
  },
  renderMarkdown: (node, h2) => {
    return `**${h2.renderChildren(node)}**`;
  },
  addCommands() {
    return {
      setBold: () => ({ commands }) => {
        return commands.setMark(this.name);
      },
      toggleBold: () => ({ commands }) => {
        return commands.toggleMark(this.name);
      },
      unsetBold: () => ({ commands }) => {
        return commands.unsetMark(this.name);
      }
    };
  },
  addKeyboardShortcuts() {
    return {
      "Mod-b": () => this.editor.commands.toggleBold(),
      "Mod-B": () => this.editor.commands.toggleBold()
    };
  },
  addInputRules() {
    return [
      markInputRule({
        find: starInputRegex$1,
        type: this.type
      }),
      markInputRule({
        find: underscoreInputRegex$1,
        type: this.type
      })
    ];
  },
  addPasteRules() {
    return [
      markPasteRule({
        find: starPasteRegex$1,
        type: this.type
      }),
      markPasteRule({
        find: underscorePasteRegex$1,
        type: this.type
      })
    ];
  }
});
var inputRegex$2 = /(^|[^`])`([^`]+)`(?!`)$/;
var pasteRegex$1 = /(^|[^`])`([^`]+)`(?!`)/g;
var Code = Mark.create({
  name: "code",
  addOptions() {
    return {
      HTMLAttributes: {}
    };
  },
  excludes: "_",
  code: true,
  exitable: true,
  parseHTML() {
    return [{ tag: "code" }];
  },
  renderHTML({ HTMLAttributes }) {
    return ["code", mergeAttributes(this.options.HTMLAttributes, HTMLAttributes), 0];
  },
  markdownTokenName: "codespan",
  parseMarkdown: (token, helpers) => {
    return helpers.applyMark("code", [{ type: "text", text: token.text || "" }]);
  },
  renderMarkdown: (node, h2) => {
    if (!node.content) {
      return "";
    }
    return `\`${h2.renderChildren(node.content)}\``;
  },
  addCommands() {
    return {
      setCode: () => ({ commands }) => {
        return commands.setMark(this.name);
      },
      toggleCode: () => ({ commands }) => {
        return commands.toggleMark(this.name);
      },
      unsetCode: () => ({ commands }) => {
        return commands.unsetMark(this.name);
      }
    };
  },
  addKeyboardShortcuts() {
    return {
      "Mod-e": () => this.editor.commands.toggleCode()
    };
  },
  addInputRules() {
    return [
      markInputRule({
        find: inputRegex$2,
        type: this.type
      })
    ];
  },
  addPasteRules() {
    return [
      markPasteRule({
        find: pasteRegex$1,
        type: this.type
      })
    ];
  }
});
var DEFAULT_TAB_SIZE = 4;
var backtickInputRegex = /^```([a-z]+)?[\s\n]$/;
var tildeInputRegex = /^~~~([a-z]+)?[\s\n]$/;
var CodeBlock = Node3.create({
  name: "codeBlock",
  addOptions() {
    return {
      languageClassPrefix: "language-",
      exitOnTripleEnter: true,
      exitOnArrowDown: true,
      defaultLanguage: null,
      enableTabIndentation: false,
      tabSize: DEFAULT_TAB_SIZE,
      HTMLAttributes: {}
    };
  },
  content: "text*",
  marks: "",
  group: "block",
  code: true,
  defining: true,
  addAttributes() {
    return {
      language: {
        default: this.options.defaultLanguage,
        parseHTML: (element) => {
          var _a;
          const { languageClassPrefix } = this.options;
          if (!languageClassPrefix) {
            return null;
          }
          const classNames = [...((_a = element.firstElementChild) == null ? void 0 : _a.classList) || []];
          const languages = classNames.filter((className) => className.startsWith(languageClassPrefix)).map((className) => className.replace(languageClassPrefix, ""));
          const language = languages[0];
          if (!language) {
            return null;
          }
          return language;
        },
        rendered: false
      }
    };
  },
  parseHTML() {
    return [
      {
        tag: "pre",
        preserveWhitespace: "full"
      }
    ];
  },
  renderHTML({ node, HTMLAttributes }) {
    return [
      "pre",
      mergeAttributes(this.options.HTMLAttributes, HTMLAttributes),
      [
        "code",
        {
          class: node.attrs.language ? this.options.languageClassPrefix + node.attrs.language : null
        },
        0
      ]
    ];
  },
  markdownTokenName: "code",
  parseMarkdown: (token, helpers) => {
    var _a;
    if (((_a = token.raw) == null ? void 0 : _a.startsWith("```")) === false && token.codeBlockStyle !== "indented") {
      return [];
    }
    return helpers.createNode(
      "codeBlock",
      { language: token.lang || null },
      token.text ? [helpers.createTextNode(token.text)] : []
    );
  },
  renderMarkdown: (node, h2) => {
    var _a;
    let output = "";
    const language = ((_a = node.attrs) == null ? void 0 : _a.language) || "";
    if (!node.content) {
      output = `\`\`\`${language}

\`\`\``;
    } else {
      const lines = [`\`\`\`${language}`, h2.renderChildren(node.content), "```"];
      output = lines.join("\n");
    }
    return output;
  },
  addCommands() {
    return {
      setCodeBlock: (attributes) => ({ commands }) => {
        return commands.setNode(this.name, attributes);
      },
      toggleCodeBlock: (attributes) => ({ commands }) => {
        return commands.toggleNode(this.name, "paragraph", attributes);
      }
    };
  },
  addKeyboardShortcuts() {
    return {
      "Mod-Alt-c": () => this.editor.commands.toggleCodeBlock(),
      // remove code block when at start of document or code block is empty
      Backspace: () => {
        const { empty, $anchor } = this.editor.state.selection;
        const isAtStart = $anchor.pos === 1;
        if (!empty || $anchor.parent.type.name !== this.name) {
          return false;
        }
        if (isAtStart || !$anchor.parent.textContent.length) {
          return this.editor.commands.clearNodes();
        }
        return false;
      },
      // handle tab indentation
      Tab: ({ editor }) => {
        var _a;
        if (!this.options.enableTabIndentation) {
          return false;
        }
        const tabSize = (_a = this.options.tabSize) != null ? _a : DEFAULT_TAB_SIZE;
        const { state } = editor;
        const { selection } = state;
        const { $from, empty } = selection;
        if ($from.parent.type !== this.type) {
          return false;
        }
        const indent = " ".repeat(tabSize);
        if (empty) {
          return editor.commands.insertContent(indent);
        }
        return editor.commands.command(({ tr }) => {
          const { from, to } = selection;
          const text = state.doc.textBetween(from, to, "\n", "\n");
          const lines = text.split("\n");
          const indentedText = lines.map((line) => indent + line).join("\n");
          tr.replaceWith(from, to, state.schema.text(indentedText));
          return true;
        });
      },
      // handle shift+tab reverse indentation
      "Shift-Tab": ({ editor }) => {
        var _a;
        if (!this.options.enableTabIndentation) {
          return false;
        }
        const tabSize = (_a = this.options.tabSize) != null ? _a : DEFAULT_TAB_SIZE;
        const { state } = editor;
        const { selection } = state;
        const { $from, empty } = selection;
        if ($from.parent.type !== this.type) {
          return false;
        }
        if (empty) {
          return editor.commands.command(({ tr }) => {
            var _a2;
            const { pos } = $from;
            const codeBlockStart = $from.start();
            const codeBlockEnd = $from.end();
            const allText = state.doc.textBetween(codeBlockStart, codeBlockEnd, "\n", "\n");
            const lines = allText.split("\n");
            let currentLineIndex = 0;
            let charCount = 0;
            const relativeCursorPos = pos - codeBlockStart;
            for (let i = 0; i < lines.length; i += 1) {
              if (charCount + lines[i].length >= relativeCursorPos) {
                currentLineIndex = i;
                break;
              }
              charCount += lines[i].length + 1;
            }
            const currentLine = lines[currentLineIndex];
            const leadingSpaces = ((_a2 = currentLine.match(/^ */)) == null ? void 0 : _a2[0]) || "";
            const spacesToRemove = Math.min(leadingSpaces.length, tabSize);
            if (spacesToRemove === 0) {
              return true;
            }
            let lineStartPos = codeBlockStart;
            for (let i = 0; i < currentLineIndex; i += 1) {
              lineStartPos += lines[i].length + 1;
            }
            tr.delete(lineStartPos, lineStartPos + spacesToRemove);
            const cursorPosInLine = pos - lineStartPos;
            if (cursorPosInLine <= spacesToRemove) {
              tr.setSelection(TextSelection.create(tr.doc, lineStartPos));
            }
            return true;
          });
        }
        return editor.commands.command(({ tr }) => {
          const { from, to } = selection;
          const text = state.doc.textBetween(from, to, "\n", "\n");
          const lines = text.split("\n");
          const reverseIndentText = lines.map((line) => {
            var _a2;
            const leadingSpaces = ((_a2 = line.match(/^ */)) == null ? void 0 : _a2[0]) || "";
            const spacesToRemove = Math.min(leadingSpaces.length, tabSize);
            return line.slice(spacesToRemove);
          }).join("\n");
          tr.replaceWith(from, to, state.schema.text(reverseIndentText));
          return true;
        });
      },
      // exit node on triple enter
      Enter: ({ editor }) => {
        if (!this.options.exitOnTripleEnter) {
          return false;
        }
        const { state } = editor;
        const { selection } = state;
        const { $from, empty } = selection;
        if (!empty || $from.parent.type !== this.type) {
          return false;
        }
        const isAtEnd = $from.parentOffset === $from.parent.nodeSize - 2;
        const endsWithDoubleNewline = $from.parent.textContent.endsWith("\n\n");
        if (!isAtEnd || !endsWithDoubleNewline) {
          return false;
        }
        return editor.chain().command(({ tr }) => {
          tr.delete($from.pos - 2, $from.pos);
          return true;
        }).exitCode().run();
      },
      // exit node on arrow down
      ArrowDown: ({ editor }) => {
        if (!this.options.exitOnArrowDown) {
          return false;
        }
        const { state } = editor;
        const { selection, doc } = state;
        const { $from, empty } = selection;
        if (!empty || $from.parent.type !== this.type) {
          return false;
        }
        const isAtEnd = $from.parentOffset === $from.parent.nodeSize - 2;
        if (!isAtEnd) {
          return false;
        }
        const after = $from.after();
        if (after === void 0) {
          return false;
        }
        const nodeAfter = doc.nodeAt(after);
        if (nodeAfter) {
          return editor.commands.command(({ tr }) => {
            tr.setSelection(Selection.near(doc.resolve(after)));
            return true;
          });
        }
        return editor.commands.exitCode();
      }
    };
  },
  addInputRules() {
    return [
      textblockTypeInputRule({
        find: backtickInputRegex,
        type: this.type,
        getAttributes: (match) => ({
          language: match[1]
        })
      }),
      textblockTypeInputRule({
        find: tildeInputRegex,
        type: this.type,
        getAttributes: (match) => ({
          language: match[1]
        })
      })
    ];
  },
  addProseMirrorPlugins() {
    return [
      // this plugin creates a code block for pasted content from VS Code
      // we can also detect the copied code language
      new Plugin({
        key: new PluginKey("codeBlockVSCodeHandler"),
        props: {
          handlePaste: (view, event) => {
            if (!event.clipboardData) {
              return false;
            }
            if (this.editor.isActive(this.type.name)) {
              return false;
            }
            const text = event.clipboardData.getData("text/plain");
            const vscode = event.clipboardData.getData("vscode-editor-data");
            const vscodeData = vscode ? JSON.parse(vscode) : void 0;
            const language = vscodeData == null ? void 0 : vscodeData.mode;
            if (!text || !language) {
              return false;
            }
            const { tr, schema } = view.state;
            const textNode = schema.text(text.replace(/\r\n?/g, "\n"));
            tr.replaceSelectionWith(this.type.create({ language }, textNode));
            if (tr.selection.$from.parent.type !== this.type) {
              tr.setSelection(TextSelection.near(tr.doc.resolve(Math.max(0, tr.selection.from - 2))));
            }
            tr.setMeta("paste", true);
            view.dispatch(tr);
            return true;
          }
        }
      })
    ];
  }
});
var Document = Node3.create({
  name: "doc",
  topNode: true,
  content: "block+",
  renderMarkdown: (node, h2) => {
    if (!node.content) {
      return "";
    }
    return h2.renderChildren(node.content, "\n\n");
  }
});
var HardBreak = Node3.create({
  name: "hardBreak",
  addOptions() {
    return {
      keepMarks: true,
      HTMLAttributes: {}
    };
  },
  inline: true,
  group: "inline",
  selectable: false,
  linebreakReplacement: true,
  parseHTML() {
    return [{ tag: "br" }];
  },
  renderHTML({ HTMLAttributes }) {
    return ["br", mergeAttributes(this.options.HTMLAttributes, HTMLAttributes)];
  },
  renderText() {
    return "\n";
  },
  renderMarkdown: (_node, h2) => h2.indent(`
`),
  addCommands() {
    return {
      setHardBreak: () => ({ commands, chain, state, editor }) => {
        return commands.first([
          () => commands.exitCode(),
          () => commands.command(() => {
            const { selection, storedMarks } = state;
            if (selection.$from.parent.type.spec.isolating) {
              return false;
            }
            const { keepMarks } = this.options;
            const { splittableMarks } = editor.extensionManager;
            const marks = storedMarks || selection.$to.parentOffset && selection.$from.marks();
            return chain().insertContent({ type: this.name }).command(({ tr, dispatch }) => {
              if (dispatch && marks && keepMarks) {
                const filteredMarks = marks.filter((mark) => splittableMarks.includes(mark.type.name));
                tr.ensureMarks(filteredMarks);
              }
              return true;
            }).run();
          })
        ]);
      }
    };
  },
  addKeyboardShortcuts() {
    return {
      "Mod-Enter": () => this.editor.commands.setHardBreak(),
      "Shift-Enter": () => this.editor.commands.setHardBreak()
    };
  }
});
var HorizontalRule = Node3.create({
  name: "horizontalRule",
  addOptions() {
    return {
      HTMLAttributes: {},
      nextNodeType: "paragraph"
    };
  },
  group: "block",
  parseHTML() {
    return [{ tag: "hr" }];
  },
  renderHTML({ HTMLAttributes }) {
    return ["hr", mergeAttributes(this.options.HTMLAttributes, HTMLAttributes)];
  },
  markdownTokenName: "hr",
  parseMarkdown: (token, helpers) => {
    return helpers.createNode("horizontalRule");
  },
  renderMarkdown: () => {
    return "---";
  },
  addCommands() {
    return {
      setHorizontalRule: () => ({ chain, state }) => {
        if (!canInsertNode(state, state.schema.nodes[this.name])) {
          return false;
        }
        const { selection } = state;
        const { $to: $originTo } = selection;
        const currentChain = chain();
        if (isNodeSelection(selection)) {
          currentChain.insertContentAt($originTo.pos, {
            type: this.name
          });
        } else {
          currentChain.insertContent({ type: this.name });
        }
        return currentChain.command(({ state: chainState, tr, dispatch }) => {
          if (dispatch) {
            const { $to } = tr.selection;
            const posAfter = $to.end();
            if ($to.nodeAfter) {
              if ($to.nodeAfter.isTextblock) {
                tr.setSelection(TextSelection.create(tr.doc, $to.pos + 1));
              } else if ($to.nodeAfter.isBlock) {
                tr.setSelection(NodeSelection.create(tr.doc, $to.pos));
              } else {
                tr.setSelection(TextSelection.create(tr.doc, $to.pos));
              }
            } else {
              const nodeType = chainState.schema.nodes[this.options.nextNodeType] || $to.parent.type.contentMatch.defaultType;
              const node = nodeType == null ? void 0 : nodeType.create();
              if (node) {
                tr.insert(posAfter, node);
                tr.setSelection(TextSelection.create(tr.doc, posAfter + 1));
              }
            }
            tr.scrollIntoView();
          }
          return true;
        }).run();
      }
    };
  },
  addInputRules() {
    return [
      nodeInputRule({
        find: /^(?:---|—-|___\s|\*\*\*\s)$/,
        type: this.type
      })
    ];
  }
});
var starInputRegex = /(?:^|\s)(\*(?!\s+\*)((?:[^*]+))\*(?!\s+\*))$/;
var starPasteRegex = /(?:^|\s)(\*(?!\s+\*)((?:[^*]+))\*(?!\s+\*))/g;
var underscoreInputRegex = /(?:^|\s)(_(?!\s+_)((?:[^_]+))_(?!\s+_))$/;
var underscorePasteRegex = /(?:^|\s)(_(?!\s+_)((?:[^_]+))_(?!\s+_))/g;
var Italic = Mark.create({
  name: "italic",
  addOptions() {
    return {
      HTMLAttributes: {}
    };
  },
  parseHTML() {
    return [
      {
        tag: "em"
      },
      {
        tag: "i",
        getAttrs: (node) => node.style.fontStyle !== "normal" && null
      },
      {
        style: "font-style=normal",
        clearMark: (mark) => mark.type.name === this.name
      },
      {
        style: "font-style=italic"
      }
    ];
  },
  renderHTML({ HTMLAttributes }) {
    return ["em", mergeAttributes(this.options.HTMLAttributes, HTMLAttributes), 0];
  },
  addCommands() {
    return {
      setItalic: () => ({ commands }) => {
        return commands.setMark(this.name);
      },
      toggleItalic: () => ({ commands }) => {
        return commands.toggleMark(this.name);
      },
      unsetItalic: () => ({ commands }) => {
        return commands.unsetMark(this.name);
      }
    };
  },
  markdownTokenName: "em",
  parseMarkdown: (token, helpers) => {
    return helpers.applyMark("italic", helpers.parseInline(token.tokens || []));
  },
  renderMarkdown: (node, h2) => {
    return `*${h2.renderChildren(node)}*`;
  },
  addKeyboardShortcuts() {
    return {
      "Mod-i": () => this.editor.commands.toggleItalic(),
      "Mod-I": () => this.editor.commands.toggleItalic()
    };
  },
  addInputRules() {
    return [
      markInputRule({
        find: starInputRegex,
        type: this.type
      }),
      markInputRule({
        find: underscoreInputRegex,
        type: this.type
      })
    ];
  },
  addPasteRules() {
    return [
      markPasteRule({
        find: starPasteRegex,
        type: this.type
      }),
      markPasteRule({
        find: underscorePasteRegex,
        type: this.type
      })
    ];
  }
});
var __defProp = Object.defineProperty;
var __export = (target, all) => {
  for (var name in all)
    __defProp(target, name, { get: all[name], enumerable: true });
};
var ListItemName = "listItem";
var TextStyleName = "textStyle";
var bulletListInputRegex = /^\s*([-+*])\s$/;
var BulletList = Node3.create({
  name: "bulletList",
  addOptions() {
    return {
      itemTypeName: "listItem",
      HTMLAttributes: {},
      keepMarks: false,
      keepAttributes: false
    };
  },
  group: "block list",
  content() {
    return `${this.options.itemTypeName}+`;
  },
  parseHTML() {
    return [{ tag: "ul" }];
  },
  renderHTML({ HTMLAttributes }) {
    return ["ul", mergeAttributes(this.options.HTMLAttributes, HTMLAttributes), 0];
  },
  markdownTokenName: "list",
  parseMarkdown: (token, helpers) => {
    if (token.type !== "list" || token.ordered) {
      return [];
    }
    return {
      type: "bulletList",
      content: token.items ? helpers.parseChildren(token.items) : []
    };
  },
  renderMarkdown: (node, h2) => {
    if (!node.content) {
      return "";
    }
    return h2.renderChildren(node.content, "\n");
  },
  markdownOptions: {
    indentsContent: true
  },
  addCommands() {
    return {
      toggleBulletList: () => ({ commands, chain }) => {
        if (this.options.keepAttributes) {
          return chain().toggleList(this.name, this.options.itemTypeName, this.options.keepMarks).updateAttributes(ListItemName, this.editor.getAttributes(TextStyleName)).run();
        }
        return commands.toggleList(this.name, this.options.itemTypeName, this.options.keepMarks);
      }
    };
  },
  addKeyboardShortcuts() {
    return {
      "Mod-Shift-8": () => this.editor.commands.toggleBulletList()
    };
  },
  addInputRules() {
    let inputRule = wrappingInputRule({
      find: bulletListInputRegex,
      type: this.type
    });
    if (this.options.keepMarks || this.options.keepAttributes) {
      inputRule = wrappingInputRule({
        find: bulletListInputRegex,
        type: this.type,
        keepMarks: this.options.keepMarks,
        keepAttributes: this.options.keepAttributes,
        getAttributes: () => {
          return this.editor.getAttributes(TextStyleName);
        },
        editor: this.editor
      });
    }
    return [inputRule];
  }
});
var ListItem = Node3.create({
  name: "listItem",
  addOptions() {
    return {
      HTMLAttributes: {},
      bulletListTypeName: "bulletList",
      orderedListTypeName: "orderedList"
    };
  },
  content: "paragraph block*",
  defining: true,
  parseHTML() {
    return [
      {
        tag: "li"
      }
    ];
  },
  renderHTML({ HTMLAttributes }) {
    return ["li", mergeAttributes(this.options.HTMLAttributes, HTMLAttributes), 0];
  },
  markdownTokenName: "list_item",
  parseMarkdown: (token, helpers) => {
    if (token.type !== "list_item") {
      return [];
    }
    let content = [];
    if (token.tokens && token.tokens.length > 0) {
      const hasParagraphTokens = token.tokens.some((t) => t.type === "paragraph");
      if (hasParagraphTokens) {
        content = helpers.parseChildren(token.tokens);
      } else {
        const firstToken = token.tokens[0];
        if (firstToken && firstToken.type === "text" && firstToken.tokens && firstToken.tokens.length > 0) {
          const inlineContent = helpers.parseInline(firstToken.tokens);
          content = [
            {
              type: "paragraph",
              content: inlineContent
            }
          ];
          if (token.tokens.length > 1) {
            const remainingTokens = token.tokens.slice(1);
            const additionalContent = helpers.parseChildren(remainingTokens);
            content.push(...additionalContent);
          }
        } else {
          content = helpers.parseChildren(token.tokens);
        }
      }
    }
    if (content.length === 0) {
      content = [
        {
          type: "paragraph",
          content: []
        }
      ];
    }
    return {
      type: "listItem",
      content
    };
  },
  renderMarkdown: (node, h2, ctx) => {
    return renderNestedMarkdownContent(
      node,
      h2,
      (context) => {
        if (context.parentType === "bulletList") {
          return "- ";
        }
        if (context.parentType === "orderedList") {
          return `${context.index + 1}. `;
        }
        return "- ";
      },
      ctx
    );
  },
  addKeyboardShortcuts() {
    return {
      Enter: () => this.editor.commands.splitListItem(this.name),
      Tab: () => this.editor.commands.sinkListItem(this.name),
      "Shift-Tab": () => this.editor.commands.liftListItem(this.name)
    };
  }
});
var listHelpers_exports = {};
__export(listHelpers_exports, {
  findListItemPos: () => findListItemPos,
  getNextListDepth: () => getNextListDepth,
  handleBackspace: () => handleBackspace,
  handleDelete: () => handleDelete,
  hasListBefore: () => hasListBefore,
  hasListItemAfter: () => hasListItemAfter,
  hasListItemBefore: () => hasListItemBefore,
  listItemHasSubList: () => listItemHasSubList,
  nextListIsDeeper: () => nextListIsDeeper,
  nextListIsHigher: () => nextListIsHigher
});
var findListItemPos = (typeOrName, state) => {
  const { $from } = state.selection;
  const nodeType = getNodeType(typeOrName, state.schema);
  let currentNode = null;
  let currentDepth = $from.depth;
  let currentPos = $from.pos;
  let targetDepth = null;
  while (currentDepth > 0 && targetDepth === null) {
    currentNode = $from.node(currentDepth);
    if (currentNode.type === nodeType) {
      targetDepth = currentDepth;
    } else {
      currentDepth -= 1;
      currentPos -= 1;
    }
  }
  if (targetDepth === null) {
    return null;
  }
  return { $pos: state.doc.resolve(currentPos), depth: targetDepth };
};
var getNextListDepth = (typeOrName, state) => {
  const listItemPos = findListItemPos(typeOrName, state);
  if (!listItemPos) {
    return false;
  }
  const [, depth] = getNodeAtPosition(state, typeOrName, listItemPos.$pos.pos + 4);
  return depth;
};
var hasListBefore = (editorState, name, parentListTypes) => {
  const { $anchor } = editorState.selection;
  const previousNodePos = Math.max(0, $anchor.pos - 2);
  const previousNode = editorState.doc.resolve(previousNodePos).node();
  if (!previousNode || !parentListTypes.includes(previousNode.type.name)) {
    return false;
  }
  return true;
};
var hasListItemBefore = (typeOrName, state) => {
  var _a;
  const { $anchor } = state.selection;
  const $targetPos = state.doc.resolve($anchor.pos - 2);
  if ($targetPos.index() === 0) {
    return false;
  }
  if (((_a = $targetPos.nodeBefore) == null ? void 0 : _a.type.name) !== typeOrName) {
    return false;
  }
  return true;
};
var listItemHasSubList = (typeOrName, state, node) => {
  if (!node) {
    return false;
  }
  const nodeType = getNodeType(typeOrName, state.schema);
  let hasSubList = false;
  node.descendants((child) => {
    if (child.type === nodeType) {
      hasSubList = true;
    }
  });
  return hasSubList;
};
var handleBackspace = (editor, name, parentListTypes) => {
  if (editor.commands.undoInputRule()) {
    return true;
  }
  if (editor.state.selection.from !== editor.state.selection.to) {
    return false;
  }
  if (!isNodeActive(editor.state, name) && hasListBefore(editor.state, name, parentListTypes)) {
    const { $anchor } = editor.state.selection;
    const $listPos = editor.state.doc.resolve($anchor.before() - 1);
    const listDescendants = [];
    $listPos.node().descendants((node, pos) => {
      if (node.type.name === name) {
        listDescendants.push({ node, pos });
      }
    });
    const lastItem = listDescendants.at(-1);
    if (!lastItem) {
      return false;
    }
    const $lastItemPos = editor.state.doc.resolve($listPos.start() + lastItem.pos + 1);
    return editor.chain().cut({ from: $anchor.start() - 1, to: $anchor.end() + 1 }, $lastItemPos.end()).joinForward().run();
  }
  if (!isNodeActive(editor.state, name)) {
    return false;
  }
  if (!isAtStartOfNode(editor.state)) {
    return false;
  }
  const listItemPos = findListItemPos(name, editor.state);
  if (!listItemPos) {
    return false;
  }
  const $prev = editor.state.doc.resolve(listItemPos.$pos.pos - 2);
  const prevNode = $prev.node(listItemPos.depth);
  const previousListItemHasSubList = listItemHasSubList(name, editor.state, prevNode);
  if (hasListItemBefore(name, editor.state) && !previousListItemHasSubList) {
    return editor.commands.joinItemBackward();
  }
  return editor.chain().liftListItem(name).run();
};
var nextListIsDeeper = (typeOrName, state) => {
  const listDepth = getNextListDepth(typeOrName, state);
  const listItemPos = findListItemPos(typeOrName, state);
  if (!listItemPos || !listDepth) {
    return false;
  }
  if (listDepth > listItemPos.depth) {
    return true;
  }
  return false;
};
var nextListIsHigher = (typeOrName, state) => {
  const listDepth = getNextListDepth(typeOrName, state);
  const listItemPos = findListItemPos(typeOrName, state);
  if (!listItemPos || !listDepth) {
    return false;
  }
  if (listDepth < listItemPos.depth) {
    return true;
  }
  return false;
};
var handleDelete = (editor, name) => {
  if (!isNodeActive(editor.state, name)) {
    return false;
  }
  if (!isAtEndOfNode(editor.state, name)) {
    return false;
  }
  const { selection } = editor.state;
  const { $from, $to } = selection;
  if (!selection.empty && $from.sameParent($to)) {
    return false;
  }
  if (nextListIsDeeper(name, editor.state)) {
    return editor.chain().focus(editor.state.selection.from + 4).lift(name).joinBackward().run();
  }
  if (nextListIsHigher(name, editor.state)) {
    return editor.chain().joinForward().joinBackward().run();
  }
  return editor.commands.joinItemForward();
};
var hasListItemAfter = (typeOrName, state) => {
  var _a;
  const { $anchor } = state.selection;
  const $targetPos = state.doc.resolve($anchor.pos - $anchor.parentOffset - 2);
  if ($targetPos.index() === $targetPos.parent.childCount - 1) {
    return false;
  }
  if (((_a = $targetPos.nodeAfter) == null ? void 0 : _a.type.name) !== typeOrName) {
    return false;
  }
  return true;
};
var ListKeymap = Extension.create({
  name: "listKeymap",
  addOptions() {
    return {
      listTypes: [
        {
          itemName: "listItem",
          wrapperNames: ["bulletList", "orderedList"]
        },
        {
          itemName: "taskItem",
          wrapperNames: ["taskList"]
        }
      ]
    };
  },
  addKeyboardShortcuts() {
    return {
      Delete: ({ editor }) => {
        let handled = false;
        this.options.listTypes.forEach(({ itemName }) => {
          if (editor.state.schema.nodes[itemName] === void 0) {
            return;
          }
          if (handleDelete(editor, itemName)) {
            handled = true;
          }
        });
        return handled;
      },
      "Mod-Delete": ({ editor }) => {
        let handled = false;
        this.options.listTypes.forEach(({ itemName }) => {
          if (editor.state.schema.nodes[itemName] === void 0) {
            return;
          }
          if (handleDelete(editor, itemName)) {
            handled = true;
          }
        });
        return handled;
      },
      Backspace: ({ editor }) => {
        let handled = false;
        this.options.listTypes.forEach(({ itemName, wrapperNames }) => {
          if (editor.state.schema.nodes[itemName] === void 0) {
            return;
          }
          if (handleBackspace(editor, itemName, wrapperNames)) {
            handled = true;
          }
        });
        return handled;
      },
      "Mod-Backspace": ({ editor }) => {
        let handled = false;
        this.options.listTypes.forEach(({ itemName, wrapperNames }) => {
          if (editor.state.schema.nodes[itemName] === void 0) {
            return;
          }
          if (handleBackspace(editor, itemName, wrapperNames)) {
            handled = true;
          }
        });
        return handled;
      }
    };
  }
});
var ORDERED_LIST_ITEM_REGEX = /^(\s*)(\d+)\.\s+(.*)$/;
var INDENTED_LINE_REGEX = /^\s/;
function collectOrderedListItems(lines) {
  const listItems = [];
  let currentLineIndex = 0;
  let consumed = 0;
  while (currentLineIndex < lines.length) {
    const line = lines[currentLineIndex];
    const match = line.match(ORDERED_LIST_ITEM_REGEX);
    if (!match) {
      break;
    }
    const [, indent, number, content] = match;
    const indentLevel = indent.length;
    let itemContent = content;
    let nextLineIndex = currentLineIndex + 1;
    const itemLines = [line];
    while (nextLineIndex < lines.length) {
      const nextLine = lines[nextLineIndex];
      const nextMatch = nextLine.match(ORDERED_LIST_ITEM_REGEX);
      if (nextMatch) {
        break;
      }
      if (nextLine.trim() === "") {
        itemLines.push(nextLine);
        itemContent += "\n";
        nextLineIndex += 1;
      } else if (nextLine.match(INDENTED_LINE_REGEX)) {
        itemLines.push(nextLine);
        itemContent += `
${nextLine.slice(indentLevel + 2)}`;
        nextLineIndex += 1;
      } else {
        break;
      }
    }
    listItems.push({
      indent: indentLevel,
      number: parseInt(number, 10),
      content: itemContent.trim(),
      raw: itemLines.join("\n")
    });
    consumed = nextLineIndex;
    currentLineIndex = nextLineIndex;
  }
  return [listItems, consumed];
}
function buildNestedStructure(items, baseIndent, lexer) {
  var _a;
  const result = [];
  let currentIndex = 0;
  while (currentIndex < items.length) {
    const item = items[currentIndex];
    if (item.indent === baseIndent) {
      const contentLines = item.content.split("\n");
      const mainText = ((_a = contentLines[0]) == null ? void 0 : _a.trim()) || "";
      const tokens = [];
      if (mainText) {
        tokens.push({
          type: "paragraph",
          raw: mainText,
          tokens: lexer.inlineTokens(mainText)
        });
      }
      const additionalContent = contentLines.slice(1).join("\n").trim();
      if (additionalContent) {
        const blockTokens = lexer.blockTokens(additionalContent);
        tokens.push(...blockTokens);
      }
      let lookAheadIndex = currentIndex + 1;
      const nestedItems = [];
      while (lookAheadIndex < items.length && items[lookAheadIndex].indent > baseIndent) {
        nestedItems.push(items[lookAheadIndex]);
        lookAheadIndex += 1;
      }
      if (nestedItems.length > 0) {
        const nextIndent = Math.min(...nestedItems.map((nestedItem) => nestedItem.indent));
        const nestedListItems = buildNestedStructure(nestedItems, nextIndent, lexer);
        tokens.push({
          type: "list",
          ordered: true,
          start: nestedItems[0].number,
          items: nestedListItems,
          raw: nestedItems.map((nestedItem) => nestedItem.raw).join("\n")
        });
      }
      result.push({
        type: "list_item",
        raw: item.raw,
        tokens
      });
      currentIndex = lookAheadIndex;
    } else {
      currentIndex += 1;
    }
  }
  return result;
}
function parseListItems(items, helpers) {
  return items.map((item) => {
    if (item.type !== "list_item") {
      return helpers.parseChildren([item])[0];
    }
    const content = [];
    if (item.tokens && item.tokens.length > 0) {
      item.tokens.forEach((itemToken) => {
        if (itemToken.type === "paragraph" || itemToken.type === "list" || itemToken.type === "blockquote" || itemToken.type === "code") {
          content.push(...helpers.parseChildren([itemToken]));
        } else if (itemToken.type === "text" && itemToken.tokens) {
          const inlineContent = helpers.parseChildren([itemToken]);
          content.push({
            type: "paragraph",
            content: inlineContent
          });
        } else {
          const parsed = helpers.parseChildren([itemToken]);
          if (parsed.length > 0) {
            content.push(...parsed);
          }
        }
      });
    }
    return {
      type: "listItem",
      content
    };
  });
}
var ListItemName2 = "listItem";
var TextStyleName2 = "textStyle";
var orderedListInputRegex = /^(\d+)\.\s$/;
var OrderedList = Node3.create({
  name: "orderedList",
  addOptions() {
    return {
      itemTypeName: "listItem",
      HTMLAttributes: {},
      keepMarks: false,
      keepAttributes: false
    };
  },
  group: "block list",
  content() {
    return `${this.options.itemTypeName}+`;
  },
  addAttributes() {
    return {
      start: {
        default: 1,
        parseHTML: (element) => {
          return element.hasAttribute("start") ? parseInt(element.getAttribute("start") || "", 10) : 1;
        }
      },
      type: {
        default: null,
        parseHTML: (element) => element.getAttribute("type")
      }
    };
  },
  parseHTML() {
    return [
      {
        tag: "ol"
      }
    ];
  },
  renderHTML({ HTMLAttributes }) {
    const { start, ...attributesWithoutStart } = HTMLAttributes;
    return start === 1 ? ["ol", mergeAttributes(this.options.HTMLAttributes, attributesWithoutStart), 0] : ["ol", mergeAttributes(this.options.HTMLAttributes, HTMLAttributes), 0];
  },
  markdownTokenName: "list",
  parseMarkdown: (token, helpers) => {
    if (token.type !== "list" || !token.ordered) {
      return [];
    }
    const startValue = token.start || 1;
    const content = token.items ? parseListItems(token.items, helpers) : [];
    if (startValue !== 1) {
      return {
        type: "orderedList",
        attrs: { start: startValue },
        content
      };
    }
    return {
      type: "orderedList",
      content
    };
  },
  renderMarkdown: (node, h2) => {
    if (!node.content) {
      return "";
    }
    return h2.renderChildren(node.content, "\n");
  },
  markdownTokenizer: {
    name: "orderedList",
    level: "block",
    start: (src) => {
      const match = src.match(/^(\s*)(\d+)\.\s+/);
      const index = match == null ? void 0 : match.index;
      return index !== void 0 ? index : -1;
    },
    tokenize: (src, _tokens, lexer) => {
      var _a;
      const lines = src.split("\n");
      const [listItems, consumed] = collectOrderedListItems(lines);
      if (listItems.length === 0) {
        return void 0;
      }
      const items = buildNestedStructure(listItems, 0, lexer);
      if (items.length === 0) {
        return void 0;
      }
      const startValue = ((_a = listItems[0]) == null ? void 0 : _a.number) || 1;
      return {
        type: "list",
        ordered: true,
        start: startValue,
        items,
        raw: lines.slice(0, consumed).join("\n")
      };
    }
  },
  markdownOptions: {
    indentsContent: true
  },
  addCommands() {
    return {
      toggleOrderedList: () => ({ commands, chain }) => {
        if (this.options.keepAttributes) {
          return chain().toggleList(this.name, this.options.itemTypeName, this.options.keepMarks).updateAttributes(ListItemName2, this.editor.getAttributes(TextStyleName2)).run();
        }
        return commands.toggleList(this.name, this.options.itemTypeName, this.options.keepMarks);
      }
    };
  },
  addKeyboardShortcuts() {
    return {
      "Mod-Shift-7": () => this.editor.commands.toggleOrderedList()
    };
  },
  addInputRules() {
    let inputRule = wrappingInputRule({
      find: orderedListInputRegex,
      type: this.type,
      getAttributes: (match) => ({ start: +match[1] }),
      joinPredicate: (match, node) => node.childCount + node.attrs.start === +match[1]
    });
    if (this.options.keepMarks || this.options.keepAttributes) {
      inputRule = wrappingInputRule({
        find: orderedListInputRegex,
        type: this.type,
        keepMarks: this.options.keepMarks,
        keepAttributes: this.options.keepAttributes,
        getAttributes: (match) => ({ start: +match[1], ...this.editor.getAttributes(TextStyleName2) }),
        joinPredicate: (match, node) => node.childCount + node.attrs.start === +match[1],
        editor: this.editor
      });
    }
    return [inputRule];
  }
});
var inputRegex$1 = /^\s*(\[([( |x])?\])\s$/;
var TaskItem = Node3.create({
  name: "taskItem",
  addOptions() {
    return {
      nested: false,
      HTMLAttributes: {},
      taskListTypeName: "taskList",
      a11y: void 0
    };
  },
  content() {
    return this.options.nested ? "paragraph block*" : "paragraph+";
  },
  defining: true,
  addAttributes() {
    return {
      checked: {
        default: false,
        keepOnSplit: false,
        parseHTML: (element) => {
          const dataChecked = element.getAttribute("data-checked");
          return dataChecked === "" || dataChecked === "true";
        },
        renderHTML: (attributes) => ({
          "data-checked": attributes.checked
        })
      }
    };
  },
  parseHTML() {
    return [
      {
        tag: `li[data-type="${this.name}"]`,
        priority: 51
      }
    ];
  },
  renderHTML({ node, HTMLAttributes }) {
    return [
      "li",
      mergeAttributes(this.options.HTMLAttributes, HTMLAttributes, {
        "data-type": this.name
      }),
      [
        "label",
        [
          "input",
          {
            type: "checkbox",
            checked: node.attrs.checked ? "checked" : null
          }
        ],
        ["span"]
      ],
      ["div", 0]
    ];
  },
  parseMarkdown: (token, h2) => {
    const content = [];
    if (token.tokens && token.tokens.length > 0) {
      content.push(h2.createNode("paragraph", {}, h2.parseInline(token.tokens)));
    } else if (token.text) {
      content.push(h2.createNode("paragraph", {}, [h2.createNode("text", { text: token.text })]));
    } else {
      content.push(h2.createNode("paragraph", {}, []));
    }
    if (token.nestedTokens && token.nestedTokens.length > 0) {
      const nestedContent = h2.parseChildren(token.nestedTokens);
      content.push(...nestedContent);
    }
    return h2.createNode("taskItem", { checked: token.checked || false }, content);
  },
  renderMarkdown: (node, h2) => {
    var _a;
    const checkedChar = ((_a = node.attrs) == null ? void 0 : _a.checked) ? "x" : " ";
    const prefix = `- [${checkedChar}] `;
    return renderNestedMarkdownContent(node, h2, prefix);
  },
  addKeyboardShortcuts() {
    const shortcuts = {
      Enter: () => this.editor.commands.splitListItem(this.name),
      "Shift-Tab": () => this.editor.commands.liftListItem(this.name)
    };
    if (!this.options.nested) {
      return shortcuts;
    }
    return {
      ...shortcuts,
      Tab: () => this.editor.commands.sinkListItem(this.name)
    };
  },
  addNodeView() {
    return ({ node, HTMLAttributes, getPos, editor }) => {
      const listItem = document.createElement("li");
      const checkboxWrapper = document.createElement("label");
      const checkboxStyler = document.createElement("span");
      const checkbox = document.createElement("input");
      const content = document.createElement("div");
      const updateA11Y = (currentNode) => {
        var _a, _b;
        checkbox.ariaLabel = ((_b = (_a = this.options.a11y) == null ? void 0 : _a.checkboxLabel) == null ? void 0 : _b.call(_a, currentNode, checkbox.checked)) || `Task item checkbox for ${currentNode.textContent || "empty task item"}`;
      };
      updateA11Y(node);
      checkboxWrapper.contentEditable = "false";
      checkbox.type = "checkbox";
      checkbox.addEventListener("mousedown", (event) => event.preventDefault());
      checkbox.addEventListener("change", (event) => {
        if (!editor.isEditable && !this.options.onReadOnlyChecked) {
          checkbox.checked = !checkbox.checked;
          return;
        }
        const { checked } = event.target;
        if (editor.isEditable && typeof getPos === "function") {
          editor.chain().focus(void 0, { scrollIntoView: false }).command(({ tr }) => {
            const position = getPos();
            if (typeof position !== "number") {
              return false;
            }
            const currentNode = tr.doc.nodeAt(position);
            tr.setNodeMarkup(position, void 0, {
              ...currentNode == null ? void 0 : currentNode.attrs,
              checked
            });
            return true;
          }).run();
        }
        if (!editor.isEditable && this.options.onReadOnlyChecked) {
          if (!this.options.onReadOnlyChecked(node, checked)) {
            checkbox.checked = !checkbox.checked;
          }
        }
      });
      Object.entries(this.options.HTMLAttributes).forEach(([key, value]) => {
        listItem.setAttribute(key, value);
      });
      listItem.dataset.checked = node.attrs.checked;
      checkbox.checked = node.attrs.checked;
      checkboxWrapper.append(checkbox, checkboxStyler);
      listItem.append(checkboxWrapper, content);
      Object.entries(HTMLAttributes).forEach(([key, value]) => {
        listItem.setAttribute(key, value);
      });
      return {
        dom: listItem,
        contentDOM: content,
        update: (updatedNode) => {
          if (updatedNode.type !== this.type) {
            return false;
          }
          listItem.dataset.checked = updatedNode.attrs.checked;
          checkbox.checked = updatedNode.attrs.checked;
          updateA11Y(updatedNode);
          return true;
        }
      };
    };
  },
  addInputRules() {
    return [
      wrappingInputRule({
        find: inputRegex$1,
        type: this.type,
        getAttributes: (match) => ({
          checked: match[match.length - 1] === "x"
        })
      })
    ];
  }
});
var TaskList = Node3.create({
  name: "taskList",
  addOptions() {
    return {
      itemTypeName: "taskItem",
      HTMLAttributes: {}
    };
  },
  group: "block list",
  content() {
    return `${this.options.itemTypeName}+`;
  },
  parseHTML() {
    return [
      {
        tag: `ul[data-type="${this.name}"]`,
        priority: 51
      }
    ];
  },
  renderHTML({ HTMLAttributes }) {
    return ["ul", mergeAttributes(this.options.HTMLAttributes, HTMLAttributes, { "data-type": this.name }), 0];
  },
  parseMarkdown: (token, h2) => {
    return h2.createNode("taskList", {}, h2.parseChildren(token.items || []));
  },
  renderMarkdown: (node, h2) => {
    if (!node.content) {
      return "";
    }
    return h2.renderChildren(node.content, "\n");
  },
  markdownTokenizer: {
    name: "taskList",
    level: "block",
    start(src) {
      var _a;
      const index = (_a = src.match(/^\s*[-+*]\s+\[([ xX])\]\s+/)) == null ? void 0 : _a.index;
      return index !== void 0 ? index : -1;
    },
    tokenize(src, tokens, lexer) {
      const parseTaskListContent = (content) => {
        const nestedResult = parseIndentedBlocks(
          content,
          {
            itemPattern: /^(\s*)([-+*])\s+\[([ xX])\]\s+(.*)$/,
            extractItemData: (match) => ({
              indentLevel: match[1].length,
              mainContent: match[4],
              checked: match[3].toLowerCase() === "x"
            }),
            createToken: (data, nestedTokens) => ({
              type: "taskItem",
              raw: "",
              mainContent: data.mainContent,
              indentLevel: data.indentLevel,
              checked: data.checked,
              text: data.mainContent,
              tokens: lexer.inlineTokens(data.mainContent),
              nestedTokens
            }),
            // Allow recursive nesting
            customNestedParser: parseTaskListContent
          },
          lexer
        );
        if (nestedResult) {
          return [
            {
              type: "taskList",
              raw: nestedResult.raw,
              items: nestedResult.items
            }
          ];
        }
        return lexer.blockTokens(content);
      };
      const result = parseIndentedBlocks(
        src,
        {
          itemPattern: /^(\s*)([-+*])\s+\[([ xX])\]\s+(.*)$/,
          extractItemData: (match) => ({
            indentLevel: match[1].length,
            mainContent: match[4],
            checked: match[3].toLowerCase() === "x"
          }),
          createToken: (data, nestedTokens) => ({
            type: "taskItem",
            raw: "",
            mainContent: data.mainContent,
            indentLevel: data.indentLevel,
            checked: data.checked,
            text: data.mainContent,
            tokens: lexer.inlineTokens(data.mainContent),
            nestedTokens
          }),
          // Use the recursive parser for nested content
          customNestedParser: parseTaskListContent
        },
        lexer
      );
      if (!result) {
        return void 0;
      }
      return {
        type: "taskList",
        raw: result.raw,
        items: result.items
      };
    }
  },
  markdownOptions: {
    indentsContent: true
  },
  addCommands() {
    return {
      toggleTaskList: () => ({ commands }) => {
        return commands.toggleList(this.name, this.options.itemTypeName);
      }
    };
  },
  addKeyboardShortcuts() {
    return {
      "Mod-Shift-9": () => this.editor.commands.toggleTaskList()
    };
  }
});
Extension.create({
  name: "listKit",
  addExtensions() {
    const extensions = [];
    if (this.options.bulletList !== false) {
      extensions.push(BulletList.configure(this.options.bulletList));
    }
    if (this.options.listItem !== false) {
      extensions.push(ListItem.configure(this.options.listItem));
    }
    if (this.options.listKeymap !== false) {
      extensions.push(ListKeymap.configure(this.options.listKeymap));
    }
    if (this.options.orderedList !== false) {
      extensions.push(OrderedList.configure(this.options.orderedList));
    }
    if (this.options.taskItem !== false) {
      extensions.push(TaskItem.configure(this.options.taskItem));
    }
    if (this.options.taskList !== false) {
      extensions.push(TaskList.configure(this.options.taskList));
    }
    return extensions;
  }
});
var inputRegex = /(?:^|\s)(~~(?!\s+~~)((?:[^~]+))~~(?!\s+~~))$/;
var pasteRegex = /(?:^|\s)(~~(?!\s+~~)((?:[^~]+))~~(?!\s+~~))/g;
var Strike = Mark.create({
  name: "strike",
  addOptions() {
    return {
      HTMLAttributes: {}
    };
  },
  parseHTML() {
    return [
      {
        tag: "s"
      },
      {
        tag: "del"
      },
      {
        tag: "strike"
      },
      {
        style: "text-decoration",
        consuming: false,
        getAttrs: (style) => style.includes("line-through") ? {} : false
      }
    ];
  },
  renderHTML({ HTMLAttributes }) {
    return ["s", mergeAttributes(this.options.HTMLAttributes, HTMLAttributes), 0];
  },
  markdownTokenName: "del",
  parseMarkdown: (token, helpers) => {
    return helpers.applyMark("strike", helpers.parseInline(token.tokens || []));
  },
  renderMarkdown: (node, h2) => {
    return `~~${h2.renderChildren(node)}~~`;
  },
  addCommands() {
    return {
      setStrike: () => ({ commands }) => {
        return commands.setMark(this.name);
      },
      toggleStrike: () => ({ commands }) => {
        return commands.toggleMark(this.name);
      },
      unsetStrike: () => ({ commands }) => {
        return commands.unsetMark(this.name);
      }
    };
  },
  addKeyboardShortcuts() {
    return {
      "Mod-Shift-s": () => this.editor.commands.toggleStrike()
    };
  },
  addInputRules() {
    return [
      markInputRule({
        find: inputRegex,
        type: this.type
      })
    ];
  },
  addPasteRules() {
    return [
      markPasteRule({
        find: pasteRegex,
        type: this.type
      })
    ];
  }
});
var Text = Node3.create({
  name: "text",
  group: "inline",
  parseMarkdown: (token) => {
    return {
      type: "text",
      text: token.text || ""
    };
  },
  renderMarkdown: (node) => node.text || ""
});
var Underline = Mark.create({
  name: "underline",
  addOptions() {
    return {
      HTMLAttributes: {}
    };
  },
  parseHTML() {
    return [
      {
        tag: "u"
      },
      {
        style: "text-decoration",
        consuming: false,
        getAttrs: (style) => style.includes("underline") ? {} : false
      }
    ];
  },
  renderHTML({ HTMLAttributes }) {
    return ["u", mergeAttributes(this.options.HTMLAttributes, HTMLAttributes), 0];
  },
  parseMarkdown(token, helpers) {
    return helpers.applyMark(this.name || "underline", helpers.parseInline(token.tokens || []));
  },
  renderMarkdown(node, helpers) {
    return `++${helpers.renderChildren(node)}++`;
  },
  markdownTokenizer: {
    name: "underline",
    level: "inline",
    start(src) {
      return src.indexOf("++");
    },
    tokenize(src, _tokens, lexer) {
      const rule = /^(\+\+)([\s\S]+?)(\+\+)/;
      const match = rule.exec(src);
      if (!match) {
        return void 0;
      }
      const innerContent = match[2].trim();
      return {
        type: "underline",
        raw: match[0],
        text: innerContent,
        tokens: lexer.inlineTokens(innerContent)
      };
    }
  },
  addCommands() {
    return {
      setUnderline: () => ({ commands }) => {
        return commands.setMark(this.name);
      },
      toggleUnderline: () => ({ commands }) => {
        return commands.toggleMark(this.name);
      },
      unsetUnderline: () => ({ commands }) => {
        return commands.unsetMark(this.name);
      }
    };
  },
  addKeyboardShortcuts() {
    return {
      "Mod-u": () => this.editor.commands.toggleUnderline(),
      "Mod-U": () => this.editor.commands.toggleUnderline()
    };
  }
});
var StarterKit = Extension.create({
  name: "starterKit",
  addExtensions() {
    var _a, _b, _c, _d;
    const extensions = [];
    if (this.options.bold !== false) {
      extensions.push(Bold.configure(this.options.bold));
    }
    if (this.options.blockquote !== false) {
      extensions.push(Blockquote.configure(this.options.blockquote));
    }
    if (this.options.bulletList !== false) {
      extensions.push(BulletList.configure(this.options.bulletList));
    }
    if (this.options.code !== false) {
      extensions.push(Code.configure(this.options.code));
    }
    if (this.options.codeBlock !== false) {
      extensions.push(CodeBlock.configure(this.options.codeBlock));
    }
    if (this.options.document !== false) {
      extensions.push(Document.configure(this.options.document));
    }
    if (this.options.dropcursor !== false) {
      extensions.push(Dropcursor.configure(this.options.dropcursor));
    }
    if (this.options.gapcursor !== false) {
      extensions.push(Gapcursor.configure(this.options.gapcursor));
    }
    if (this.options.hardBreak !== false) {
      extensions.push(HardBreak.configure(this.options.hardBreak));
    }
    if (this.options.heading !== false) {
      extensions.push(Heading.configure(this.options.heading));
    }
    if (this.options.undoRedo !== false) {
      extensions.push(UndoRedo.configure(this.options.undoRedo));
    }
    if (this.options.horizontalRule !== false) {
      extensions.push(HorizontalRule.configure(this.options.horizontalRule));
    }
    if (this.options.italic !== false) {
      extensions.push(Italic.configure(this.options.italic));
    }
    if (this.options.listItem !== false) {
      extensions.push(ListItem.configure(this.options.listItem));
    }
    if (this.options.listKeymap !== false) {
      extensions.push(ListKeymap.configure((_a = this.options) == null ? void 0 : _a.listKeymap));
    }
    if (this.options.link !== false) {
      extensions.push(Link.configure((_b = this.options) == null ? void 0 : _b.link));
    }
    if (this.options.orderedList !== false) {
      extensions.push(OrderedList.configure(this.options.orderedList));
    }
    if (this.options.paragraph !== false) {
      extensions.push(Paragraph.configure(this.options.paragraph));
    }
    if (this.options.strike !== false) {
      extensions.push(Strike.configure(this.options.strike));
    }
    if (this.options.text !== false) {
      extensions.push(Text.configure(this.options.text));
    }
    if (this.options.underline !== false) {
      extensions.push(Underline.configure((_c = this.options) == null ? void 0 : _c.underline));
    }
    if (this.options.trailingNode !== false) {
      extensions.push(TrailingNode.configure((_d = this.options) == null ? void 0 : _d.trailingNode));
    }
    return extensions;
  }
});
var index_default = StarterKit;
export {
  StarterKit,
  index_default as default
};
