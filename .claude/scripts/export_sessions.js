const fs = require('fs');
const path = require('path');
const os = require('os');

// 引数の解析
const args = process.argv[2] || '';
let limit = null;

if (args === 'latest') {
  limit = 1;
} else if (!isNaN(parseInt(args))) {
  limit = parseInt(args);
}

// 現在のプロジェクトディレクトリを取得
const cwd = process.cwd();
// プロジェクトキーを正しく生成（先頭の/を除いて、/と_と.を-に変換）
const projectKey = '-' + cwd.substring(1).replace(/[/_.]/g, '-');
const projectDir = path.join(os.homedir(), '.claude', 'projects', projectKey);

console.log(`📂 プロジェクトディレクトリ: ${projectDir}`);

if (!fs.existsSync(projectDir)) {
  console.log(`❌ プロジェクトディレクトリが見つかりません: ${projectDir}`);
  process.exit(1);
}

// セッションIDを取得する関数
function getSessionId(filePath) {
  try {
    const lines = fs.readFileSync(filePath, 'utf-8').split('\n').filter(l => l.trim());
    for (const line of lines) {
      try {
        const data = JSON.parse(line);
        if (data.sessionId) {
          return data.sessionId;
        }
      } catch (e) {
        // パースエラーは無視
      }
    }
  } catch (e) {
    // ファイル読み込みエラーは無視
  }
  return null;
}

// セッション開始時刻を取得する関数
function getSessionTimestamp(filePath) {
  try {
    const lines = fs.readFileSync(filePath, 'utf-8').split('\n').filter(l => l.trim());
    for (const line of lines) {
      try {
        const data = JSON.parse(line);
        if (data.timestamp) {
          return data.timestamp;
        }
      } catch (e) {
        // パースエラーは無視
      }
    }
  } catch (e) {
    // ファイル読み込みエラーは無視
  }
  return null;
}

// 全てのセッションファイルを取得してセッションIDごとにグループ化
const allFiles = fs.readdirSync(projectDir)
  .filter(f => f.endsWith('.jsonl'))
  .map(f => ({
    name: f,
    path: path.join(projectDir, f),
    mtime: fs.statSync(path.join(projectDir, f)).mtime,
    isAgent: f.startsWith('agent-')
  }));

// セッションIDでグループ化
const sessionGroups = new Map();
allFiles.forEach(file => {
  const sessionId = getSessionId(file.path);
  if (sessionId) {
    if (!sessionGroups.has(sessionId)) {
      sessionGroups.set(sessionId, {
        sessionId,
        parentFile: null,
        agentFiles: [],
        latestMtime: file.mtime
      });
    }
    const group = sessionGroups.get(sessionId);
    if (!file.isAgent) {
      // 親セッションファイル
      if (!group.parentFile || file.mtime > group.parentFile.mtime) {
        group.parentFile = file;
      }
    } else {
      // エージェントファイル
      group.agentFiles.push(file);
    }
    // 最新のmtimeを更新
    if (file.mtime > group.latestMtime) {
      group.latestMtime = file.mtime;
    }
  }
});

// 親セッションがあるグループのみをフィルタリング
const validGroups = Array.from(sessionGroups.values())
  .filter(group => group.parentFile !== null)
  .sort((a, b) => b.latestMtime - a.latestMtime); // 新しい順にソート

if (validGroups.length === 0) {
  console.log('❌ エクスポート可能なセッションが見つかりません（親セッションファイルが必要です）');
  process.exit(1);
}

// エクスポート対象のグループを決定
let groupsToExport = validGroups;
if (limit) {
  groupsToExport = validGroups.slice(0, limit);
}

console.log(`📊 ${groupsToExport.length}件のセッションをエクスポートします...`);

// エクスポートディレクトリの作成
const exportDir = path.join(cwd, '.claude', 'session_exports');
if (!fs.existsSync(exportDir)) {
  fs.mkdirSync(exportDir, { recursive: true });
}

// ======= 新規追加: メッセージ分類機能 =======

// メッセージタイプを分類する関数
function classifyMessage(msg) {
  if (msg.type === 'assistant') {
    return {
      category: 'assistant',
      isToolUse: msg.message?.content?.some(item => item.type === 'tool_use') || false
    };
  }

  if (msg.type === 'user') {
    const hasParent = msg.parentUuid !== null;
    const msgContent = typeof msg.message === 'string' ? msg.message : msg.message?.content;

    if (!hasParent) {
      return {
        category: 'user_prompt',
        hasSystemReminder: typeof msgContent === 'string' && msgContent.includes('<system-reminder>')
      };
    } else {
      if (Array.isArray(msgContent)) {
        const hasToolResult = msgContent.some(item => item.type === 'tool_result');
        if (hasToolResult) {
          return { category: 'tool_result' };
        }
      }
      return { category: 'other_context' };
    }
  }

  return { category: 'unknown' };
}

// system-reminderタグを抽出する関数
function extractSystemReminder(content) {
  if (typeof content !== 'string') return null;
  const reminderRegex = /<system-reminder>([\s\S]*?)<\/system-reminder>/g;
  const matches = [];
  let match;
  while ((match = reminderRegex.exec(content)) !== null) {
    matches.push(match[1].trim());
  }
  return matches.length > 0 ? matches : null;
}

// system-reminderタグを除去する関数
function removeSystemReminder(content) {
  if (typeof content !== 'string') return content;
  return content.replace(/<system-reminder>[\s\S]*?<\/system-reminder>/g, '').trim();
}

// 安全なフォーマット関数（長いコンテンツを処理）
function safeFormat(content, maxLines = 50, maxChars = 10000) {
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
function getToolName(assistantMsg, toolUseId) {
  const content = assistantMsg?.message?.content;
  if (Array.isArray(content)) {
    const toolUse = content.find(item => item.type === 'tool_use' && item.id === toolUseId);
    if (toolUse) {
      // ツール名とインプットから読みやすい表示を生成
      if (toolUse.name === 'Read') {
        const filePath = toolUse.input.file_path || '';
        const displayPath = filePath.length > 60 ? '...' + filePath.substring(filePath.length - 57) : filePath;
        return `📖 Read: ${displayPath}`;
      } else if (toolUse.name === 'Bash') {
        const cmd = (toolUse.input.command || '').split('\n')[0];
        return `⚙️ Bash: ${cmd.length > 50 ? cmd.substring(0, 50) + '...' : cmd}`;
      } else if (toolUse.name === 'Grep') {
        return `🔍 Grep: ${toolUse.input.pattern || ''}`;
      } else if (toolUse.name === 'Glob') {
        return `📁 Glob: ${toolUse.input.pattern || ''}`;
      } else if (toolUse.name === 'Edit') {
        return `✏️ Edit: ${toolUse.input.file_path || ''}`;
      } else if (toolUse.name === 'Write') {
        return `📝 Write: ${toolUse.input.file_path || ''}`;
      } else if (toolUse.name === 'Task') {
        return `🤖 Task: ${toolUse.input.subagent_type || ''}`;
      } else if (toolUse.name === 'WebFetch') {
        return `🌐 WebFetch: ${toolUse.input.url || ''}`;
      } else if (toolUse.name === 'WebSearch') {
        return `🔎 WebSearch: ${toolUse.input.query || ''}`;
      }
      return `🔧 ${toolUse.name}`;
    }
  }
  return '🔧 Tool';
}

// ツール実行結果をグループ化する関数
function groupToolResults(messages) {
  const grouped = [];
  let currentGroup = null;

  messages.forEach((msg) => {
    const classification = classifyMessage(msg);

    if (classification.category === 'assistant' && classification.isToolUse) {
      // 新しいツール使用グループの開始
      currentGroup = {
        assistantMsg: msg,
        toolResults: [],
        timestamp: msg.timestamp
      };
    } else if (classification.category === 'tool_result' && currentGroup) {
      // ツール実行結果をグループに追加
      currentGroup.toolResults.push(msg);
    } else {
      // グループを保存して新しいメッセージを処理
      if (currentGroup && currentGroup.toolResults.length > 0) {
        grouped.push({ type: 'tool_group', data: currentGroup });
        currentGroup = null;
      }
      grouped.push({ type: 'single', data: msg });
    }
  });

  // 最後のグループを保存
  if (currentGroup && currentGroup.toolResults.length > 0) {
    grouped.push({ type: 'tool_group', data: currentGroup });
  }

  return grouped;
}

// ユーザープロンプトをフォーマットする関数
function formatUserPrompt(msg, timestamp) {
  const msgContent = typeof msg.message === 'string' ? msg.message : msg.message?.content;
  const systemReminders = extractSystemReminder(msgContent);
  const cleanContent = removeSystemReminder(msgContent);

  let markdown = `### [${timestamp}] 👤 User\n\n`;

  if (cleanContent) {
    markdown += `${cleanContent}\n\n`;
  }

  if (systemReminders && systemReminders.length > 0) {
    markdown += `<details>\n<summary>📋 システムコンテキスト (${systemReminders.length}件)</summary>\n\n`;
    systemReminders.forEach((reminder, idx) => {
      if (systemReminders.length > 1) {
        markdown += `#### コンテキスト ${idx + 1}\n\n`;
      }
      markdown += `\`\`\`\n${reminder}\n\`\`\`\n\n`;
    });
    markdown += `</details>\n\n`;
  }

  return markdown;
}

// Assistantメッセージをフォーマットする関数
function formatAssistant(msg, timestamp) {
  let markdown = `### [${timestamp}] 🤖 Assistant\n\n`;

  const msgContent = msg.message?.content;
  if (Array.isArray(msgContent)) {
    msgContent.forEach(item => {
      if (item.type === 'text') {
        markdown += `${item.text}\n\n`;
      } else if (item.type === 'tool_use') {
        markdown += `**🔧 ツール使用**: \`${item.name}\`\n\n`;
        if (item.input) {
          markdown += `\`\`\`json\n${JSON.stringify(item.input, null, 2)}\n\`\`\`\n\n`;
        }
      }
    });
  }

  // 使用量情報
  if (msg.message?.usage) {
    const usage = msg.message.usage;
    markdown += `<details>\n<summary>トークン使用量</summary>\n\n`;
    markdown += `- Input: ${usage.input_tokens || 0}\n`;
    markdown += `- Output: ${usage.output_tokens || 0}\n`;
    if (usage.cache_read_input_tokens) {
      markdown += `- Cache Read: ${usage.cache_read_input_tokens}\n`;
    }
    if (usage.cache_creation_input_tokens) {
      markdown += `- Cache Creation: ${usage.cache_creation_input_tokens}\n`;
    }
    markdown += `\n</details>\n\n`;
  }

  return markdown;
}

// ツール実行結果をフォーマットする関数
function formatToolExecution(toolGroup, timestamp) {
  let markdown = `### [${timestamp}] 🔧 Tool Execution\n\n`;

  toolGroup.toolResults.forEach(result => {
    const content = result.message.content;

    if (Array.isArray(content)) {
      content.forEach(item => {
        if (item.type === 'tool_result') {
          // ツール名を取得（対応するtool_useから）
          const toolName = getToolName(toolGroup.assistantMsg, item.tool_use_id);

          markdown += `<details>\n`;
          markdown += `<summary>${toolName}</summary>\n\n`;

          // コンテンツのタイプに応じた表示
          if (typeof item.content === 'string') {
            const formattedContent = safeFormat(item.content);
            markdown += `\`\`\`\n${formattedContent}\n\`\`\`\n\n`;
          } else if (Array.isArray(item.content)) {
            // contentが配列の場合（複雑な構造）
            markdown += `\`\`\`json\n${JSON.stringify(item.content, null, 2)}\n\`\`\`\n\n`;
          }

          if (item.is_error) {
            markdown += `**⚠️ エラー**\n\n`;
          }

          markdown += `</details>\n\n`;
        }
      });
    }
  });

  return markdown;
}

// ファイルをマークダウンに変換する関数（全面書き換え）
function convertToMarkdown(file) {
  const lines = fs.readFileSync(file.path, 'utf-8').split('\n').filter(l => l.trim());
  const messages = [];
  let sessionInfo = null;

  // JSONLをパース
  lines.forEach(line => {
    try {
      const data = JSON.parse(line);
      if (data.type === 'user' || data.type === 'assistant') {
        messages.push(data);
        if (!sessionInfo && data.sessionId) {
          sessionInfo = {
            sessionId: data.sessionId,
            cwd: data.cwd,
            gitBranch: data.gitBranch,
            version: data.version,
            timestamp: data.timestamp
          };
        }
      }
    } catch (e) {
      // パースエラーは無視
    }
  });

  if (!sessionInfo) {
    return null;
  }

  // マークダウン生成
  let markdown = `# セッションログ: ${sessionInfo.sessionId}\n\n`;
  markdown += `## セッション情報\n\n`;
  markdown += `- **セッションID**: \`${sessionInfo.sessionId}\`\n`;
  markdown += `- **開始時刻**: ${new Date(sessionInfo.timestamp).toLocaleString('ja-JP')}\n`;
  markdown += `- **作業ディレクトリ**: \`${sessionInfo.cwd}\`\n`;
  markdown += `- **Gitブランチ**: \`${sessionInfo.gitBranch || 'N/A'}\`\n`;
  markdown += `- **Claudeバージョン**: ${sessionInfo.version}\n\n`;

  // 統計情報を計算
  let userPromptCount = 0;
  let toolExecutionCount = 0;
  let assistantResponseCount = 0;

  messages.forEach(msg => {
    const classification = classifyMessage(msg);
    if (classification.category === 'user_prompt') {
      userPromptCount++;
    } else if (classification.category === 'tool_result') {
      toolExecutionCount++;
    } else if (classification.category === 'assistant') {
      assistantResponseCount++;
    }
  });

  markdown += `## 統計情報\n\n`;
  markdown += `- **総メッセージ数**: ${messages.length}件\n`;
  markdown += `- **実際のユーザー指示**: ${userPromptCount}回\n`;
  markdown += `- **ツール実行回数**: ${toolExecutionCount}回\n`;
  markdown += `- **Assistant応答**: ${assistantResponseCount}回\n\n`;
  markdown += `---\n\n`;

  // メッセージをグループ化して処理
  const groupedMessages = groupToolResults(messages);

  groupedMessages.forEach(item => {
    if (item.type === 'single') {
      const msg = item.data;
      const timestamp = new Date(msg.timestamp).toLocaleTimeString('ja-JP');
      const classification = classifyMessage(msg);

      if (classification.category === 'user_prompt') {
        markdown += formatUserPrompt(msg, timestamp);
      } else if (classification.category === 'assistant') {
        markdown += formatAssistant(msg, timestamp);
      }
      // tool_resultは単独では表示しない（グループ化される）
    } else if (item.type === 'tool_group') {
      const toolGroup = item.data;
      const timestamp = new Date(toolGroup.timestamp).toLocaleTimeString('ja-JP');
      markdown += formatToolExecution(toolGroup, timestamp);
    }

    markdown += `---\n\n`;
  });

  return markdown;
}

// 各セッショングループをエクスポート
groupsToExport.forEach((group, index) => {
  const allGroupFiles = [group.parentFile, ...group.agentFiles];
  console.log(`📝 [${index + 1}/${groupsToExport.length}] セッション ${group.sessionId} を処理中... (${allGroupFiles.length}ファイル)`);

  // 親セッションの開始時刻を取得してyyyymmddHHMMSS形式に変換
  const sessionTimestamp = getSessionTimestamp(group.parentFile.path);
  let datePrefix = '';
  if (sessionTimestamp) {
    const date = new Date(sessionTimestamp);
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    const seconds = String(date.getSeconds()).padStart(2, '0');
    datePrefix = `${year}${month}${day}${hours}${minutes}${seconds}_`;
  }

  // セッションディレクトリを作成（yyyymmddHHMMSS_セッションID形式）
  const sessionDirName = `${datePrefix}${group.sessionId}`;
  const sessionDir = path.join(exportDir, sessionDirName);
  if (!fs.existsSync(sessionDir)) {
    fs.mkdirSync(sessionDir, { recursive: true });
  }

  // 親セッションをエクスポート
  const parentMarkdown = convertToMarkdown(group.parentFile);
  if (parentMarkdown) {
    const fileName = path.basename(group.parentFile.name, '.jsonl');
    const outputPath = path.join(sessionDir, `${fileName}.md`);
    fs.writeFileSync(outputPath, parentMarkdown);
    console.log(`  ✅ 親セッション: ${outputPath}`);
  }

  // エージェントファイルをエクスポート
  group.agentFiles.forEach(agentFile => {
    const agentMarkdown = convertToMarkdown(agentFile);
    if (agentMarkdown) {
      // エージェントセッションの開始時刻を取得してyyyymmddHHMMSS形式に変換
      const agentTimestamp = getSessionTimestamp(agentFile.path);
      let agentDatePrefix = '';
      if (agentTimestamp) {
        const date = new Date(agentTimestamp);
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');
        const seconds = String(date.getSeconds()).padStart(2, '0');
        agentDatePrefix = `${year}${month}${day}${hours}${minutes}${seconds}_`;
      }
      const fileName = path.basename(agentFile.name, '.jsonl');
      const outputPath = path.join(sessionDir, `${agentDatePrefix}${fileName}.md`);
      fs.writeFileSync(outputPath, agentMarkdown);
      console.log(`  ✅ エージェント: ${outputPath}`);
    }
  });
});

console.log(`\n🎉 エクスポートが完了しました！`);
console.log(`📂 出力先: ${exportDir}`);
