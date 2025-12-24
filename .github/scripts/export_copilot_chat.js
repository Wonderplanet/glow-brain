const fs = require('fs');
const path = require('path');

// 引数の解析
const inputFile = process.argv[2];

if (!inputFile) {
  console.log('使い方: node export_copilot_chat.js <chat.jsonファイルのパス>');
  console.log('例: node export_copilot_chat.js ./chat.json');
  process.exit(1);
}

if (!fs.existsSync(inputFile)) {
  console.log(`❌ ファイルが見つかりません: ${inputFile}`);
  process.exit(1);
}

console.log(`📂 入力ファイル: ${inputFile}`);

// JSONファイルを読み込み
let chatData;
try {
  const fileContent = fs.readFileSync(inputFile, 'utf-8');
  chatData = JSON.parse(fileContent);
} catch (e) {
  console.log(`❌ JSONファイルの読み込みに失敗しました: ${e.message}`);
  process.exit(1);
}

// ======= カラー設定 =======

const COLORS = {
  user: '#E1B941',           // ゴールド - ユーザープロンプト
  assistant: '#9AADEF',      // 薄い青 - Assistant応答
  thinking: '#B19CD9',       // 薄い紫 - 思考プロセス
  toolExecution: '#4169E1',  // ロイヤルブルー - ツール実行結果
  textEdit: '#90EE90'        // ライトグリーン - テキスト編集
};

// 背景色より少し濃い色をボーダーに使用
function darkenColor(hexColor) {
  const r = parseInt(hexColor.slice(1, 3), 16);
  const g = parseInt(hexColor.slice(3, 5), 16);
  const b = parseInt(hexColor.slice(5, 7), 16);

  const factor = 0.7;
  const newR = Math.floor(r * factor);
  const newG = Math.floor(g * factor);
  const newB = Math.floor(b * factor);

  return `#${newR.toString(16).padStart(2, '0')}${newG.toString(16).padStart(2, '0')}${newB.toString(16).padStart(2, '0')}`;
}

// コンテンツをHTMLのdivタグで囲んで色を付ける
function wrapWithColor(content, backgroundColor) {
  return `<div style="background-color: ${backgroundColor}; color: #1a1a1a; padding: 15px; margin: 10px 0; border-radius: 8px; border-left: 6px solid ${darkenColor(backgroundColor)};">

${content}

</div>

`;
}

// URLデコード関数（file://のパスをデコード）
function decodeFileUrls(text) {
  if (typeof text !== 'string') return text;

  // file:// で始まるURLをデコード
  return text.replace(/file:\/\/\/[^\s)]+/g, (url) => {
    try {
      return decodeURIComponent(url);
    } catch (e) {
      return url;
    }
  });
}

// 安全なフォーマット関数
function safeFormat(content, maxLines = 100, maxChars = 20000) {
  try {
    if (typeof content !== 'string') {
      content = JSON.stringify(content, null, 2);
    }

    if (content.length > maxChars) {
      const truncated = content.substring(0, maxChars);
      return `${truncated}\n\n... (残り${content.length - maxChars}文字を省略)`;
    }

    const lines = content.split('\n');
    if (lines.length > maxLines) {
      const truncated = lines.slice(0, maxLines).join('\n');
      return `${truncated}\n\n... (残り${lines.length - maxLines}行を省略)`;
    }

    return content;
  } catch (e) {
    return '[フォーマットエラー: コンテンツを表示できません]';
  }
}

// ツール名を取得する関数
function getToolIcon(toolId) {
  if (!toolId) return '🔧';

  if (toolId.includes('readFile')) return '📖';
  if (toolId.includes('writeFile') || toolId.includes('createFile')) return '📝';
  if (toolId.includes('editFile') || toolId.includes('applyEdits')) return '✏️';
  if (toolId.includes('findTextInFiles') || toolId.includes('search')) return '🔍';
  if (toolId.includes('listDirectory')) return '📁';
  if (toolId.includes('terminal') || toolId.includes('run_in_terminal')) return '⚙️';
  if (toolId.includes('web') || toolId.includes('fetch')) return '🌐';

  return '🔧';
}

// ユーザーメッセージをフォーマット
function formatUserMessage(request, timestamp) {
  let markdown = `### [${timestamp}] 👤 User\n\n`;

  const messageText = request.message?.text || '';
  markdown += `${messageText}\n\n`;

  // 変数データ（プロンプトファイルなど）を表示
  const variables = request.variableData?.variables || [];
  const promptFiles = variables.filter(v => v.kind === 'promptFile');

  if (promptFiles.length > 0) {
    markdown += `<details>\n<summary>📋 プロンプトファイル (${promptFiles.length}件)</summary>\n\n`;
    promptFiles.forEach(pf => {
      const filePath = pf.value?.path || pf.value?.fsPath || '';
      const fileName = path.basename(filePath);
      markdown += `- **${pf.name || fileName}**: \`${filePath}\`\n`;
    });
    markdown += `\n</details>\n\n`;
  }

  return wrapWithColor(markdown, COLORS.user);
}

// Assistantのテキスト応答をフォーマット
function formatAssistantText(responseItems, timestamp) {
  let markdown = `### [${timestamp}] 🤖 Assistant\n\n`;

  const textItems = responseItems.filter(item =>
    (item.kind === null || item.kind === undefined) && item.value
  );

  if (textItems.length === 0) {
    return '';
  }

  textItems.forEach(item => {
    const text = item.value?.value || item.value || '';
    if (typeof text === 'string' && text.trim()) {
      markdown += `${text}\n\n`;
    }
  });

  return wrapWithColor(markdown, COLORS.assistant);
}

// 思考プロセスをフォーマット
function formatThinking(responseItems, timestamp) {
  const thinkingItems = responseItems.filter(item => item.kind === 'thinking');

  if (thinkingItems.length === 0) {
    return '';
  }

  let markdown = `### [${timestamp}] 💭 Thinking\n\n`;

  thinkingItems.forEach((item, idx) => {
    const text = item.value?.value || item.value || '';
    if (typeof text === 'string' && text.trim()) {
      if (thinkingItems.length > 1) {
        markdown += `#### 思考 ${idx + 1}\n\n`;
      }
      markdown += `${safeFormat(text, 50)}\n\n`;
    }
  });

  return wrapWithColor(markdown, COLORS.thinking);
}

// ツール実行をフォーマット
function formatToolExecutions(responseItems, timestamp) {
  const toolItems = responseItems.filter(item =>
    item.kind === 'toolInvocationSerialized' ||
    item.kind === 'prepareToolInvocation'
  );

  if (toolItems.length === 0) {
    return '';
  }

  let markdown = `### [${timestamp}] 🔧 Tool Execution\n\n`;

  toolItems.forEach(item => {
    if (item.kind === 'toolInvocationSerialized') {
      const toolIcon = getToolIcon(item.toolId);
      const toolName = item.toolId || 'Unknown Tool';
      let invocationMsg = item.invocationMessage?.value || item.invocationMessage || '';
      let pastMsg = item.pastTenseMessage?.value || item.pastTenseMessage || '';

      // URLエンコードされたパスをデコード
      invocationMsg = decodeFileUrls(invocationMsg);
      pastMsg = decodeFileUrls(pastMsg);

      markdown += `<details>\n`;
      markdown += `<summary>${toolIcon} ${toolName}</summary>\n\n`;

      if (invocationMsg && typeof invocationMsg === 'string') {
        markdown += `**実行**: ${invocationMsg}\n\n`;
      }

      if (pastMsg && typeof pastMsg === 'string' && pastMsg !== invocationMsg) {
        markdown += `**結果**: ${pastMsg}\n\n`;
      }

      // ターミナル出力がある場合
      if (item.toolSpecificData?.terminalCommandOutput) {
        const output = item.toolSpecificData.terminalCommandOutput.text;
        if (output) {
          markdown += `**出力**:\n\`\`\`\n${safeFormat(output)}\n\`\`\`\n\n`;
        }
      }

      // 完了ステータス
      if (item.isComplete) {
        markdown += `✅ 完了\n\n`;
      }

      markdown += `</details>\n\n`;
    }
  });

  return wrapWithColor(markdown, COLORS.toolExecution);
}

// テキスト編集をフォーマット
function formatTextEdits(responseItems, timestamp) {
  const editItems = responseItems.filter(item => item.kind === 'textEditGroup');

  if (editItems.length === 0) {
    return '';
  }

  let markdown = `### [${timestamp}] ✏️ Text Edits\n\n`;

  editItems.forEach((item, idx) => {
    markdown += `<details>\n`;
    markdown += `<summary>編集 ${idx + 1}</summary>\n\n`;

    // 編集の詳細を表示
    markdown += `\`\`\`json\n${JSON.stringify(item, null, 2)}\n\`\`\`\n\n`;

    markdown += `</details>\n\n`;
  });

  return wrapWithColor(markdown, COLORS.textEdit);
}

// メインの変換関数
function convertToMarkdown() {
  const requests = chatData.requests || [];

  if (requests.length === 0) {
    console.log('❌ リクエストが見つかりません');
    return null;
  }

  // 最初のリクエストから情報を取得
  const firstRequest = requests[0];
  const sessionId = firstRequest.requestId || 'unknown';
  const firstTimestamp = firstRequest.timestamp || Date.now();

  let markdown = `# GitHub Copilot Chat ログ\n\n`;
  markdown += `## セッション情報\n\n`;
  markdown += `- **Responder**: ${chatData.responderUsername || 'GitHub Copilot'}\n`;
  markdown += `- **開始時刻**: ${new Date(firstTimestamp).toLocaleString('ja-JP')}\n`;
  markdown += `- **Location**: ${chatData.initialLocation || 'panel'}\n\n`;

  // 統計情報
  markdown += `## 統計情報\n\n`;
  markdown += `- **総リクエスト数**: ${requests.length}件\n`;

  let totalToolCalls = 0;
  let totalThinking = 0;
  let totalTextResponses = 0;

  requests.forEach(req => {
    const response = req.response || [];
    totalToolCalls += response.filter(r => r.kind === 'toolInvocationSerialized').length;
    totalThinking += response.filter(r => r.kind === 'thinking').length;
    totalTextResponses += response.filter(r => !r.kind && r.value).length;
  });

  markdown += `- **ツール実行回数**: ${totalToolCalls}回\n`;
  markdown += `- **思考プロセス**: ${totalThinking}回\n`;
  markdown += `- **テキスト応答**: ${totalTextResponses}回\n\n`;
  markdown += `---\n\n`;

  // 各リクエストを処理
  requests.forEach((request, idx) => {
    const timestamp = new Date(request.timestamp || Date.now()).toLocaleTimeString('ja-JP');

    // ユーザーメッセージ
    markdown += formatUserMessage(request, timestamp);
    markdown += `---\n\n`;

    const response = request.response || [];

    // 思考プロセス
    const thinkingMd = formatThinking(response, timestamp);
    if (thinkingMd) {
      markdown += thinkingMd;
      markdown += `---\n\n`;
    }

    // ツール実行
    const toolMd = formatToolExecutions(response, timestamp);
    if (toolMd) {
      markdown += toolMd;
      markdown += `---\n\n`;
    }

    // テキスト編集
    const editMd = formatTextEdits(response, timestamp);
    if (editMd) {
      markdown += editMd;
      markdown += `---\n\n`;
    }

    // Assistant応答
    const assistantMd = formatAssistantText(response, timestamp);
    if (assistantMd) {
      markdown += assistantMd;
      markdown += `---\n\n`;
    }
  });

  return markdown;
}

// エクスポート実行
console.log('📊 GitHub Copilot Chat履歴を変換中...');

const markdown = convertToMarkdown();

if (!markdown) {
  console.log('❌ 変換に失敗しました');
  process.exit(1);
}

// 出力ファイル名を生成
const inputBaseName = path.basename(inputFile, '.json');
const inputDir = path.dirname(inputFile);
const outputFileName = `${inputBaseName}.md`;
const outputPath = path.join(inputDir, outputFileName);

// マークダウンファイルを書き込み
fs.writeFileSync(outputPath, markdown, 'utf-8');

console.log(`✅ エクスポート完了: ${outputPath}`);
console.log(`📄 ファイルサイズ: ${(markdown.length / 1024).toFixed(2)} KB`);
