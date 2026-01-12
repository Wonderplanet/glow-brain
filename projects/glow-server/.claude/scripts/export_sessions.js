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

// ファイルをマークダウンに変換する関数
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

  // Userの指示回数をカウント（テキストコンテンツがあるものだけ）
  const userInstructionCount = messages.filter(msg => {
    if (msg.type !== 'user') return false;
    const msgContent = typeof msg.message === 'string' ? msg.message : msg.message?.content;

    if (typeof msgContent === 'string') return true;
    if (Array.isArray(msgContent)) {
      return msgContent.some(item => item.type === 'text');
    }
    return false;
  }).length;

  markdown += `## 会話履歴\n\n`;
  markdown += `**総メッセージ数**: ${messages.length}件\n`;
  markdown += `**User指示回数**: ${userInstructionCount}回\n\n`;
  markdown += `---\n\n`;

  messages.forEach((msg, idx) => {
    const timestamp = new Date(msg.timestamp).toLocaleTimeString('ja-JP');

    if (msg.type === 'user') {
      const msgContent = typeof msg.message === 'string' ? msg.message : msg.message?.content;

      // テキストコンテンツがあるかチェック
      let hasTextContent = false;
      let textContent = '';
      let hasImage = false;

      if (typeof msgContent === 'string') {
        hasTextContent = true;
        textContent = msgContent;
      } else if (Array.isArray(msgContent)) {
        msgContent.forEach(item => {
          if (item.type === 'text') {
            hasTextContent = true;
            textContent += item.text;
          } else if (item.type === 'image') {
            hasImage = true;
          }
        });
      }

      // テキストコンテンツまたは画像がある場合のみ出力
      if (hasTextContent || hasImage) {
        markdown += `### [${timestamp}] 👤 User\n\n`;

        if (textContent) {
          markdown += `${textContent}\n\n`;
        }
        if (hasImage) {
          markdown += `**🖼️ 画像添付**\n\n`;
        }
      }
    } else if (msg.type === 'assistant') {
      markdown += `### [${timestamp}] 🤖 Assistant\n\n`;

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
        markdown += `\n</details>\n\n`;
      }
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
