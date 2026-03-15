#!/usr/bin/env node

import { Server } from '@modelcontextprotocol/sdk/server/index.js';
import { StdioServerTransport } from '@modelcontextprotocol/sdk/server/stdio.js';
import {
  CallToolRequestSchema,
  ListToolsRequestSchema,
} from '@modelcontextprotocol/sdk/types.js';
import mysql from 'mysql2/promise';

// ローカル接続のみ許可（リモートDB接続制限）
function validateLocalHost(host) {
  const allowedHosts = [
    'localhost',
    '127.0.0.1',
    '0.0.0.0',
    '::1'
  ];

  if (!allowedHosts.includes(host)) {
    throw new Error(`リモートホストへの接続は許可されていません: ${host}. ローカルホストのみ利用可能です。`);
  }

  return host;
}

// 環境変数の検証
function validateEnvironmentVariables() {
  const hosts = [
    process.env.MASTER_DB_HOST,
    process.env.MANAGE_DB_HOST,
    process.env.ADMIN_DB_HOST,
    process.env.TIDB_HOST
  ];

  hosts.forEach(host => {
    if (host) {
      validateLocalHost(host);
    }
  });
}

// 環境変数を検証
try {
  validateEnvironmentVariables();
  console.error('ホスト検証: ローカルホストのみの接続が確認されました');
} catch (error) {
  console.error('環境変数検証エラー:', error.message);
  process.exit(1);
}

// 書き込みクエリの有効化設定（デフォルト: false = 読み取り専用）
const ENABLE_WRITE_QUERIES = process.env.ENABLE_WRITE_QUERIES === 'true';

// デバッグ用：環境変数の確認
console.error('DB Environment variables:');
console.error('MASTER_DB_HOST:', process.env.MASTER_DB_HOST);
console.error('MASTER_DB_PORT:', process.env.MASTER_DB_PORT);
console.error('MASTER_DB_USERNAME:', process.env.MASTER_DB_USERNAME);
console.error('MASTER_DB_DATABASE:', process.env.MASTER_DB_DATABASE);
console.error('TIDB_HOST:', process.env.TIDB_HOST);
console.error('TIDB_PORT:', process.env.TIDB_PORT);
console.error('ENABLE_WRITE_QUERIES:', ENABLE_WRITE_QUERIES);


// データベース接続プール
const pools = {};

// データベース接続設定（各データベースごとに個別設定）
const dbConfigs = {
  mst: {
    host: process.env.MASTER_DB_HOST,
    port: parseInt(process.env.MASTER_DB_PORT),
    user: process.env.MASTER_DB_USERNAME,
    password: process.env.MASTER_DB_PASSWORD,
    database: process.env.MASTER_DB_DATABASE,
    charset: 'utf8mb4',
    connectionLimit: 10,
  },
  mng: {
    host: process.env.MANAGE_DB_HOST,
    port: parseInt(process.env.MANAGE_DB_PORT),
    user: process.env.MANAGE_DB_USERNAME,
    password: process.env.MANAGE_DB_PASSWORD,
    database: process.env.MANAGE_DB_DATABASE,
    charset: 'utf8mb4',
    connectionLimit: 10,
  },
  admin: {
    host: process.env.ADMIN_DB_HOST,
    port: parseInt(process.env.ADMIN_DB_PORT),
    user: process.env.ADMIN_DB_USERNAME,
    password: process.env.ADMIN_DB_PASSWORD,
    database: process.env.ADMIN_DB_DATABASE,
    charset: 'utf8mb4',
    connectionLimit: 10,
  },
  tidb: {
    host: process.env.TIDB_HOST,
    port: parseInt(process.env.TIDB_PORT),
    user: process.env.TIDB_USERNAME,
    password: process.env.TIDB_PASSWORD,
    database: process.env.TIDB_DATABASE,
    charset: 'utf8mb4',
    connectionLimit: 10,
  }
};

// データベース別の設定
const databaseMap = {
  mst: { configKey: 'mst' },
  mng: { configKey: 'mng' },
  admin: { configKey: 'admin' },
  usr: { configKey: 'tidb' },
  log: { configKey: 'tidb' },
  sys: { configKey: 'tidb' }
};

// 接続プールを取得または作成
function getPool(configKey) {
  if (!pools[configKey]) {
    const config = dbConfigs[configKey];
    if (!config) {
      throw new Error(`Unknown config key: ${configKey}`);
    }
    pools[configKey] = mysql.createPool(config);
  }

  return pools[configKey];
}

// データベース操作関数
async function executeQuery(dbName, query, params = []) {
  const dbConfig = databaseMap[dbName];
  if (!dbConfig) {
    throw new Error(`Unknown database: ${dbName}. Available: ${Object.keys(databaseMap).join(', ')}`);
  }

  const pool = getPool(dbConfig.configKey);

  try {
    const [rows, fields] = await pool.execute(query, params);
    return { rows, fields, database: dbName, query };
  } catch (error) {
    throw new Error(`Database query failed on ${dbName}: ${error.message}`);
  }
}

async function getTables(dbName) {
  const query = 'SHOW TABLES';
  const result = await executeQuery(dbName, query);
  return result.rows.map(row => Object.values(row)[0]);
}

async function getTableStructure(dbName, tableName) {
  // TiDBでDESCRIBE文にプレースホルダーを使うと構文エラーになるため、直接テーブル名を文字列として組み込む
  // SQLインジェクション対策として、テーブル名をサニタイズ
  const sanitizedTableName = tableName.replace(/[^a-zA-Z0-9_$]/g, '');
  const query = `DESCRIBE \`${sanitizedTableName}\``;
  const result = await executeQuery(dbName, query, []);
  return result.rows;
}

async function getTableIndexes(dbName, tableName) {
  // TiDBでSHOW INDEX文にプレースホルダーを使うと構文エラーになるため、直接テーブル名を文字列として組み込む
  // SQLインジェクション対策として、テーブル名をサニタイズ
  const sanitizedTableName = tableName.replace(/[^a-zA-Z0-9_$]/g, '');
  const query = `SHOW INDEX FROM \`${sanitizedTableName}\``;
  const result = await executeQuery(dbName, query, []);
  return result.rows;
}

// MCPサーバーの設定
const server = new Server(
  {
    name: 'glow-server-local-db',
    version: '1.0.0',
  },
  {
    capabilities: {
      tools: {},
    },
  }
);

// ツールの定義
server.setRequestHandler(ListToolsRequestSchema, async () => {
  const tools = [
    {
      name: 'list_databases',
      description: 'List all available databases',
      inputSchema: {
        type: 'object',
        properties: {},
        required: [],
      },
    },
    {
      name: 'list_tables',
      description: 'List all tables in a specific database',
      inputSchema: {
        type: 'object',
        properties: {
          database: {
            type: 'string',
            description: 'Database name (mst, mng, admin, usr, log, sys)',
            enum: Object.keys(databaseMap)
          },
        },
        required: ['database'],
      },
    },
    {
      name: 'describe_table',
      description: 'Get table structure (columns, types, constraints)',
      inputSchema: {
        type: 'object',
        properties: {
          database: {
            type: 'string',
            description: 'Database name (mst, mng, admin, usr, log, sys)',
            enum: Object.keys(databaseMap)
          },
          table: {
            type: 'string',
            description: 'Table name',
          },
        },
        required: ['database', 'table'],
      },
    },
    {
      name: 'show_indexes',
      description: 'Show indexes for a specific table',
      inputSchema: {
        type: 'object',
        properties: {
          database: {
            type: 'string',
            description: 'Database name (mst, mng, admin, usr, log, sys)',
            enum: Object.keys(databaseMap)
          },
          table: {
            type: 'string',
            description: 'Table name',
          },
        },
        required: ['database', 'table'],
      },
    },
    {
      name: 'query_database',
      description: 'Execute a SELECT query on the database',
      inputSchema: {
        type: 'object',
        properties: {
          database: {
            type: 'string',
            description: 'Database name (mst, mng, admin, usr, log, sys)',
            enum: Object.keys(databaseMap)
          },
          query: {
            type: 'string',
            description: 'SQL SELECT query to execute',
          },
          limit: {
            type: 'number',
            description: 'Maximum number of rows to return (default: 100)',
            default: 100,
          },
        },
        required: ['database', 'query'],
      },
    },
  ];

  // ENABLE_WRITE_QUERIESがtrueの場合のみexecute_queryツールを追加
  if (ENABLE_WRITE_QUERIES) {
    tools.push({
      name: 'execute_query',
      description: 'Execute a write query (INSERT, UPDATE, DELETE) or any SQL query on the database',
      inputSchema: {
        type: 'object',
        properties: {
          database: {
            type: 'string',
            description: 'Database name (mst, mng, admin, usr, log, sys)',
            enum: Object.keys(databaseMap)
          },
          query: {
            type: 'string',
            description: 'SQL query to execute (INSERT, UPDATE, DELETE, or SELECT)',
          },
        },
        required: ['database', 'query'],
      },
    });
  }

  return { tools };
});

// ツールの実行
server.setRequestHandler(CallToolRequestSchema, async (request) => {
  const { name, arguments: args } = request.params;

  try {
    switch (name) {
      case 'list_databases':
        return {
          content: [
            {
              type: 'text',
              text: `Available databases:\n${Object.keys(databaseMap).map(db => {
                const config = databaseMap[db];
                const dbConfig = dbConfigs[config.configKey];
                return `- ${db}: ${dbConfig.host}:${dbConfig.port} (${dbConfig.database})`;
              }).join('\n')}`,
            },
          ],
        };

      case 'list_tables':
        {
          const { database } = args;
          const tables = await getTables(database);
          return {
            content: [
              {
                type: 'text',
                text: `Tables in ${database}:\n${tables.map(table => `- ${table}`).join('\n')}`,
              },
            ],
          };
        }

      case 'describe_table':
        {
          const { database, table } = args;
          const structure = await getTableStructure(database, table);

          const tableInfo = structure.map(col =>
            `${col.Field}: ${col.Type}${col.Null === 'NO' ? ' NOT NULL' : ''}${col.Key ? ` (${col.Key})` : ''}${col.Default !== null ? ` DEFAULT ${col.Default}` : ''}${col.Extra ? ` ${col.Extra}` : ''}`
          ).join('\n');

          return {
            content: [
              {
                type: 'text',
                text: `Table structure for ${database}.${table}:\n\n${tableInfo}`,
              },
            ],
          };
        }

      case 'show_indexes':
        {
          const { database, table } = args;
          const indexes = await getTableIndexes(database, table);

          if (indexes.length === 0) {
            return {
              content: [
                {
                  type: 'text',
                  text: `No indexes found for ${database}.${table}`,
                },
              ],
            };
          }

          const indexInfo = indexes.map(idx =>
            `${idx.Key_name}: ${idx.Column_name}${idx.Non_unique === 0 ? ' (UNIQUE)' : ''}${idx.Index_type ? ` [${idx.Index_type}]` : ''}`
          ).join('\n');

          return {
            content: [
              {
                type: 'text',
                text: `Indexes for ${database}.${table}:\n\n${indexInfo}`,
              },
            ],
          };
        }

      case 'query_database':
        {
          const { database, query, limit = 100 } = args;

          // SELECTクエリのみ許可
          const trimmedQuery = query.trim().toLowerCase();
          if (!trimmedQuery.startsWith('select') && !trimmedQuery.startsWith('show') && !trimmedQuery.startsWith('describe') && !trimmedQuery.startsWith('explain')) {
            throw new Error('Only SELECT, SHOW, DESCRIBE, and EXPLAIN queries are allowed');
          }

          // LIMITを追加（既にLIMITがある場合は追加しない）
          let finalQuery = query;
          if (!trimmedQuery.includes('limit') && trimmedQuery.startsWith('select')) {
            finalQuery = `${query} LIMIT ${limit}`;
          }

          const result = await executeQuery(database, finalQuery);

          if (result.rows.length === 0) {
            return {
              content: [
                {
                  type: 'text',
                  text: `Query executed successfully on ${database}, but no rows returned.\n\nQuery: ${finalQuery}`,
                },
              ],
            };
          }

          // 結果をテーブル形式で表示
          const headers = result.fields.map(field => field.name);
          const maxWidths = headers.map(header => header.length);

          // 各列の最大幅を計算
          result.rows.forEach(row => {
            headers.forEach((header, index) => {
              const value = row[header];
              const stringValue = value === null ? 'NULL' : String(value);
              maxWidths[index] = Math.max(maxWidths[index], stringValue.length);
            });
          });

          // テーブルヘッダーを作成
          const headerRow = headers.map((header, index) =>
            header.padEnd(maxWidths[index])
          ).join(' | ');

          const separatorRow = maxWidths.map(width => '-'.repeat(width)).join('-+-');

          // データ行を作成
          const dataRows = result.rows.map(row =>
            headers.map((header, index) => {
              const value = row[header];
              const stringValue = value === null ? 'NULL' : String(value);
              return stringValue.padEnd(maxWidths[index]);
            }).join(' | ')
          );

          const tableText = [headerRow, separatorRow, ...dataRows].join('\n');

          return {
            content: [
              {
                type: 'text',
                text: `Query results from ${database} (${result.rows.length} rows):\n\nQuery: ${finalQuery}\n\n${tableText}`,
              },
            ],
          };
        }

      case 'execute_query':
        {
          const { database, query } = args;

          const result = await executeQuery(database, query);

          // SELECTクエリの場合は結果セットを返す
          const trimmedQuery = query.trim().toLowerCase();
          if (trimmedQuery.startsWith('select')) {
            if (result.rows.length === 0) {
              return {
                content: [
                  {
                    type: 'text',
                    text: `Query executed successfully on ${database}, but no rows returned.\n\nQuery: ${query}`,
                  },
                ],
              };
            }

            // 結果をテーブル形式で表示
            const headers = result.fields.map(field => field.name);
            const maxWidths = headers.map(header => header.length);

            // 各列の最大幅を計算
            result.rows.forEach(row => {
              headers.forEach((header, index) => {
                const value = row[header];
                const stringValue = value === null ? 'NULL' : String(value);
                maxWidths[index] = Math.max(maxWidths[index], stringValue.length);
              });
            });

            // テーブルヘッダーを作成
            const headerRow = headers.map((header, index) =>
              header.padEnd(maxWidths[index])
            ).join(' | ');

            const separatorRow = maxWidths.map(width => '-'.repeat(width)).join('-+-');

            // データ行を作成
            const dataRows = result.rows.map(row =>
              headers.map((header, index) => {
                const value = row[header];
                const stringValue = value === null ? 'NULL' : String(value);
                return stringValue.padEnd(maxWidths[index]);
              }).join(' | ')
            );

            const tableText = [headerRow, separatorRow, ...dataRows].join('\n');

            return {
              content: [
                {
                  type: 'text',
                  text: `Query results from ${database} (${result.rows.length} rows):\n\nQuery: ${query}\n\n${tableText}`,
                },
              ],
            };
          }

          // INSERT, UPDATE, DELETEクエリの場合は影響を受けた行数を返す
          const affectedRows = result.rows.affectedRows || 0;
          const insertId = result.rows.insertId || null;

          let message = `Query executed successfully on ${database}.\n\nQuery: ${query}\n\nAffected rows: ${affectedRows}`;
          if (insertId) {
            message += `\nInsert ID: ${insertId}`;
          }

          return {
            content: [
              {
                type: 'text',
                text: message,
              },
            ],
          };
        }

      default:
        throw new Error(`Unknown tool: ${name}`);
    }
  } catch (error) {
    return {
      content: [
        {
          type: 'text',
          text: `Error: ${error.message}`,
        },
      ],
      isError: true,
    };
  }
});

// サーバーの開始
async function main() {
  const transport = new StdioServerTransport();
  await server.connect(transport);
  console.error('Glow DB MCP server running on stdio');
}

// エラーハンドリング
process.on('unhandledRejection', (reason, promise) => {
  console.error('Unhandled Rejection at:', promise, 'reason:', reason);
  process.exit(1);
});

process.on('uncaughtException', (error) => {
  console.error('Uncaught Exception:', error);
  process.exit(1);
});

// プロセス終了時にコネクションプールを閉じる
process.on('SIGINT', async () => {
  console.error('Shutting down...');
  for (const pool of Object.values(pools)) {
    await pool.end();
  }
  process.exit(0);
});

main().catch((error) => {
  console.error('Failed to start server:', error);
  process.exit(1);
});