import {$getRoot, $getSelection} from 'lexical';
import {useEffect} from 'react';

import React from 'react'
import ReactDOM from 'react-dom/client'

import {AutoFocusPlugin} from '@lexical/react/LexicalAutoFocusPlugin';
import {LexicalComposer} from '@lexical/react/LexicalComposer';
import {RichTextPlugin} from '@lexical/react/LexicalRichTextPlugin';
import {ContentEditable} from '@lexical/react/LexicalContentEditable';
import {HistoryPlugin} from '@lexical/react/LexicalHistoryPlugin';
import {LexicalErrorBoundary} from '@lexical/react/LexicalErrorBoundary';

import {MarkdownShortcutPlugin} from '@lexical/react/LexicalMarkdownShortcutPlugin';
import { AutoLinkNode, LinkNode } from "@lexical/link";
import { ListNode, ListItemNode } from "@lexical/list";
import { TableNode, TableCellNode, TableRowNode } from "@lexical/table";
import { CodeNode } from "@lexical/code";
import { HeadingNode, QuoteNode } from "@lexical/rich-text";
import { HorizontalRuleNode } from "@lexical/react/LexicalHorizontalRuleNode";

import ToolbarPlugin from './lexical-plugins/ToolbarPlugin';

const theme = {
  // Theme styling goes here
  //...
}

// Catch any errors that occur during Lexical updates and log them
// or throw them as needed. If you don't throw them, Lexical will
// try to recover gracefully without losing user data.
function onError(error) {
  console.error(error);
}

function Editor() {
  const initialConfig = {
    namespace: 'MyEditor',
    nodes: [
        LinkNode,
        AutoLinkNode,
        ListNode,
        ListItemNode,
        TableNode,
        TableCellNode,
        TableRowNode,
        HorizontalRuleNode,
        CodeNode,
        HeadingNode,
        LinkNode,
        ListNode,
        ListItemNode,
        QuoteNode,
    ],
    theme,
    onError,
  };

  return (
    <LexicalComposer initialConfig={initialConfig}>
      <ToolbarPlugin />
      <RichTextPlugin
        contentEditable={
          <ContentEditable
            aria-placeholder={'Enter some text...'}
            placeholder={<div>Enter some text...</div>}
          />
        }
        ErrorBoundary={LexicalErrorBoundary}
      />
      <HistoryPlugin />
      <AutoFocusPlugin />
      <MarkdownShortcutPlugin />
    </LexicalComposer>
  );
}

//https://lexical.dev/docs/react/plugins#lexicalmarkdownshortcutplugin add most of these
//toolbar from https://lexical.dev/docs/getting-started/react
//better toolbar: https://github.com/facebook/lexical/blob/5caf9231d456cadeb0f83fb69602b3bc6e433710/packages/lexical-playground/src/plugins/ToolbarPlugin.tsx
//latest: https://github.com/facebook/lexical/blob/main/packages/lexical-playground/src/plugins/ToolbarPlugin/index.tsx

ReactDOM.createRoot(document.getElementById('lexical-root')).render(
  <React.StrictMode>
    <Editor />
  </React.StrictMode>
)