/**
 * 設計書チェックツール - リファクタリング＋診断統合版
 * 
 * 主な機能:
 * 1. 設計書からマスターデータ形式への変換
 * 2. 生成データとマスターデータの照合
 * 3. ダイレクトチェック（高速版）
 * 4. 書き出し失敗/0件時の推定原因ダイアログ表示（NEW）
 * 5. 🩺 事前診断メニュー（NEW）
 */

// ==================== 定数定義 ====================

const SHEET_NAMES = {
  MASTER_CONFIG: 'master_config',
  CHECK_MANAGEMENT: 'チェック対象管理',
  DEFAULT_MAPPING: 'マッピング設定シート',
  ALT_MAPPING: '設計書_マッピング'
};

const COLUMN_NAMES = {
  CHECK: 'チェック',
  SHEET_NAME: 'シート名',
  MAPPING: 'マッピング',
  MASTER_URL: 'マスターURL',
  LAST_CHECK: '最終チェック日時',
  RESULT: '結果'
};

const MAPPING_COLUMNS = {
  ITEM_NAME: '項目名',
  DIRECTION: '方向',
  DESIGN_RANGE: '設計書範囲',
  MASTER_NAME: 'マスター名',
  MASTER_COLUMN: 'マスター列名',
  CHECK_IGNORE: 'チェック無視',
  UNIQUE_KEY: 'ユニークキー',
  EXPAND_TYPE: '展開タイプ',
  IGNORE_BLANK: '空白無視'
};

const DIRECTIONS = {
  DOWN: ['▼', '↓'],
  RIGHT: ['▶', '▶︎', '→']
};

const EXPAND_TYPES = {
  INHERIT: '下方継承',
  NORMAL: '通常'
};

// ==================== 初期化・メニュー ====================

/**
 * スプレッドシートを開いた時にメニューを追加（診断メニューを追加済み）
 */
function onOpen() {
  const ui = SpreadsheetApp.getUi();
  ui.createMenu('🔧 設計書チェック')
    .addItem('🔄 データ生成/更新', 'menuUpdateGeneratedData')
    .addItem('🔍 生成済みデータのチェックのみ', 'menuCheckOnly')
    .addItem('⚡ データ生成＋チェック実行', 'menuGenerateAndCheck')
    .addItem('🗑️ 生成データの削除', 'menuDeleteGeneratedData')
    .addSeparator()
    .addItem('🚀 ダイレクトチェック（高速）', 'menuDirectCheck')
    .addSeparator()
    .addItem('🔧 マスターデータ上書き(実験的)', 'menuOverwriteMasterData')
    .addSeparator()
    .addItem('🩺 事前診断（書き出ししない）', 'menuPreflightDiagnose')
    .addToUi();
}

// ==================== 設定管理 ====================

/**
 * マスターデータの開始位置設定を取得
 * @returns {Object} マスター名をキーとした開始位置設定
 */
function getMasterDataConfig() {
  const ss = SpreadsheetApp.getActiveSpreadsheet();
  const configSheet = ss.getSheetByName(SHEET_NAMES.MASTER_CONFIG);
  
  if (!configSheet) {
    console.warn('⚠️ master_configシートが見つかりません。デフォルト設定を使用します。');
    return { 'MstAutoPlayerSequence': { startRow: 3, startCol: 2 } };
  }
  
  const configData = configSheet.getDataRange().getValues();
  const config = {};
  
  for (let i = 1; i < configData.length; i++) {
    const row = configData[i];
    if (row[0]) {
      config[row[0]] = {
        startRow: row[1] || 1,
        startCol: row[2] || 1
      };
    }
  }
  
  return config;
}

/**
 * 設計書ごとのマッピングシートを取得
 * @param {string} designSheetName - 設計書名
 * @returns {Sheet|null} マッピングシート
 */
function getMappingSheetForDesign(designSheetName) {
  const ss = SpreadsheetApp.getActiveSpreadsheet();
  const checkSheet = ss.getSheetByName(SHEET_NAMES.CHECK_MANAGEMENT);
  
  if (!checkSheet) {
    console.error('❌ チェック対象管理シートが見つかりません');
    return null;
  }
  
  const checkData = checkSheet.getDataRange().getValues();
  const checkHeaders = checkData[0];
  
  const sheetNameIdx = checkHeaders.indexOf(COLUMN_NAMES.SHEET_NAME);
  const mappingIdx = checkHeaders.indexOf(COLUMN_NAMES.MAPPING);
  
  for (let i = 1; i < checkData.length; i++) {
    const row = checkData[i];
    if (row[sheetNameIdx] === designSheetName && row[mappingIdx]) {
      return ss.getSheetByName(row[mappingIdx]);
    }
  }
  
  // デフォルトのマッピングシートを返す
  return ss.getSheetByName(SHEET_NAMES.DEFAULT_MAPPING) || 
         ss.getSheetByName(SHEET_NAMES.ALT_MAPPING);
}

// ==================== データ変換・生成 ====================

/**
 * 設計書からマスターデータ形式への変換処理（メイン）
 * @returns {Object} チェック結果
 */
function convertDesignDocsToMasterFormat() {
  const ss = SpreadsheetApp.getActiveSpreadsheet();
  const targetSheets = getCheckTargetSheets(ss);
  
  if (targetSheets.length === 0) {
    console.log('⚠️ チェック対象の設計書がありません。');
    return {};
  }
  
  const masterGroups = groupMappingsByMaster(targetSheets);
  generateMasterSheets(ss, masterGroups);
  
  return checkGeneratedDataAgainstMaster();
}

/**
 * チェック対象の設計書を取得
 * @param {Spreadsheet} ss - スプレッドシート
 * @returns {Array<string>} 設計書名の配列
 */
function getCheckTargetSheets(ss) {
  const checkSheet = ss.getSheetByName(SHEET_NAMES.CHECK_MANAGEMENT);
  const checkData = checkSheet.getDataRange().getValues();
  const checkHeaders = checkData[0];
  
  const checkIdx = checkHeaders.indexOf(COLUMN_NAMES.CHECK);
  const sheetNameIdx = checkHeaders.indexOf(COLUMN_NAMES.SHEET_NAME);
  
  const targetSheets = [];
  for (let i = 1; i < checkData.length; i++) {
    if (checkData[i][checkIdx]) {
      targetSheets.push(checkData[i][sheetNameIdx]);
    }
  }
  
  return targetSheets;
}

/**
 * マッピングをマスター名でグループ化
 * @param {Array<string>} targetSheets - 対象設計書名の配列
 * @returns {Object} マスター名をキーとしたグループ
 */
function groupMappingsByMaster(targetSheets) {
  const masterGroups = {};
  
  targetSheets.forEach(sheetName => {
    const mappingSheet = getMappingSheetForDesign(sheetName);
    if (!mappingSheet) return;
    
    const mappingData = mappingSheet.getDataRange().getValues();
    const mappings = parseMappingData(mappingData);
    
    mappings.forEach(mapping => {
      if (!masterGroups[mapping.masterName]) {
        masterGroups[mapping.masterName] = {
          mappings: [],
          designSheets: []
        };
      }
      
      // 重複チェック
      const exists = masterGroups[mapping.masterName].mappings.some(m => 
        m.masterColumn === mapping.masterColumn && m.checkIgnore === mapping.checkIgnore
      );
      
      if (!exists) {
        masterGroups[mapping.masterName].mappings.push(mapping);
      }
      
      if (!masterGroups[mapping.masterName].designSheets.includes(sheetName)) {
        masterGroups[mapping.masterName].designSheets.push(sheetName);
      }
    });
  });
  
  return masterGroups;
}

/**
 * マスターシートを生成（シート名付き版）
 * @param {Spreadsheet} ss - スプレッドシート
 * @param {Object} masterGroups - マスターグループ
 */
function generateMasterSheets(ss, masterGroups) {
  Object.entries(masterGroups).forEach(([masterName, groupData]) => {
    console.log(`📋 ${masterName}の処理を開始...`);
    
    const masterSheet = getOrCreateSheet(ss, masterName);
    const recordsData = collectRecordsForMaster(ss, masterName, groupData);
    
    if (recordsData.records.length > 0) {
      writeMasterData(masterSheet, groupData.mappings, recordsData.records, recordsData.sourceSheetNames);
      console.log(`✅ ${masterName}の生成が完了（${recordsData.records.length}件）`);
    } else {
      console.warn(`⚠️ ${masterName}のレコードが0件です`);
    }
  });
}

/**
 * シートを取得または作成
 * @param {Spreadsheet} ss - スプレッドシート
 * @param {string} sheetName - シート名
 * @returns {Sheet} シート
 */
function getOrCreateSheet(ss, sheetName) {
  let sheet = ss.getSheetByName(sheetName);
  if (!sheet) {
    sheet = ss.insertSheet(sheetName);
  } else {
    sheet.clear();
  }
  return sheet;
}

/**
 * マスター用のレコードを収集（シート名付き版）
 * @param {Spreadsheet} ss - スプレッドシート
 * @param {string} masterName - マスター名
 * @param {Object} groupData - グループデータ
 * @returns {Object} レコードと元シート名の配列
 */
function collectRecordsForMaster(ss, masterName, groupData) {
  const allRecords = [];
  const sourceSheetNames = [];
  
  groupData.designSheets.forEach(sheetName => {
    const designSheet = ss.getSheetByName(sheetName);
    if (!designSheet) return;
    
    const mappingSheet = getMappingSheetForDesign(sheetName);
    const mappingData = mappingSheet.getDataRange().getValues();
    const mappings = parseMappingData(mappingData);
    const masterMappings = mappings.filter(m => m.masterName === masterName);
    
    const records = extractRecordsFromDesignSheet(designSheet, masterMappings);
    allRecords.push(...records);
    
    // 各レコードに対応する元シート名を記録
    records.forEach(() => sourceSheetNames.push(sheetName));
  });
  
  return { records: allRecords, sourceSheetNames };
}

/**
 * マスターデータを書き込み（シート名付き版）
 * @param {Sheet} sheet - 書き込み先シート
 * @param {Array} mappings - マッピング情報
 * @param {Array} records - レコード
 * @param {Array} sourceSheetNames - 元設計書名の配列
 */
function writeMasterData(sheet, mappings, records, sourceSheetNames = []) {
  const headers = getUniqueHeaders(mappings);
  
  // シート名列を最初に追加
  const allHeaders = ['シート名', ...headers];
  const values = [allHeaders];
  
  records.forEach((record, index) => {
    const row = [
      // シート名を最初の列に設定（更新・削除用のキー）
      sourceSheetNames[index] || '',
      // 既存のデータ列
      ...headers.map(header => {
        const value = record[header];
        return (value !== null && value !== undefined) ? value : '';
      })
    ];
    values.push(row);
  });
  
  sheet.getRange(1, 1, values.length, values[0].length).setValues(values);
}

/**
 * ユニークなヘッダーリストを取得
 * @param {Array} mappings - マッピング情報
 * @returns {Array<string>} ヘッダーの配列
 */
function getUniqueHeaders(mappings) {
  const headers = [];
  const addedColumns = new Set();
  
  mappings.forEach(mapping => {
    if (!addedColumns.has(mapping.masterColumn)) {
      headers.push(mapping.masterColumn);
      addedColumns.add(mapping.masterColumn);
    }
  });
  
  return headers;
}

// ==================== データ抽出 ====================

/**
 * 設計書シートからレコードを抽出（改良版）
 * 空白セルを含むデータも正しく処理
 * @param {Sheet} designSheet - 設計書シート
 * @param {Array} masterMappings - マッピング情報
 * @returns {Array<Object>} レコードの配列
 */
function extractRecordsFromDesignSheet(designSheet, masterMappings) {
  const dataByMapping = {};
  let maxRecordCount = 0;

  // シート全体を1回で取得（性能改善）
  const sheetData = designSheet.getDataRange().getValues();
  // 空白無視列に基づく走査境界
  const bounds = computeBoundsForIgnoreBlank(sheetData, masterMappings);

  // 各マッピングのデータを収集
  masterMappings.forEach(mapping => {
    if (mapping.checkIgnore) {
      // データ生成時は通常通りデータを抽出（チェック時に無視する）
      const values = extractDataForMapping(designSheet, mapping, { sheetData, bounds });
      dataByMapping[mapping.masterColumn] = values;
      return;
    }

    const values = extractDataForMapping(designSheet, mapping, { sheetData, bounds });
    dataByMapping[mapping.masterColumn] = values;

    // 通常タイプの最大レコード数を更新
    if (mapping.expandType === EXPAND_TYPES.NORMAL || mapping.expandType === '通常') {
      if (values.length > maxRecordCount) {
        maxRecordCount = values.length;
      }
    }
  });

  // すべてが下方継承（または通常が0件）の場合は1レコードを最低生成
  if (maxRecordCount === 0) {
    const hasInherit = masterMappings.some(m => !m.checkIgnore && (m.expandType === EXPAND_TYPES.INHERIT || m.expandType === '下方継承'));
    if (hasInherit) {
      maxRecordCount = 1;
    }
  }

  // チェック無視列の処理
  fillIgnoredColumns(masterMappings, dataByMapping, maxRecordCount);

  // レコード生成
  return createRecords(masterMappings, dataByMapping, maxRecordCount);
}

/**
 * マッピングに基づいてデータを抽出
 * @param {Sheet} sheet - シート
 * @param {Object} mapping - マッピング情報
 * @param {Object} ctx - { sheetData, bounds }
 * @returns {Array} 値の配列
 */
function extractDataForMapping(sheet, mapping, ctx) {
  if (mapping.direction) {
    return extractByDirection(sheet, mapping, ctx);
  } else if (mapping.designRange) {
    return extractByRange(sheet, mapping, ctx);
  }
  return [];
}

/**
 * 方向指定でデータを抽出
 * @param {Sheet} sheet - シート
 * @param {Object} mapping - マッピング情報
 * @param {Object} ctx - { sheetData, bounds }
 * @returns {Array} 値の配列
 */
function extractByDirection(sheet, mapping, ctx) {
  const data = (ctx && ctx.sheetData) ? ctx.sheetData : sheet.getDataRange().getValues();
  const itemPos = findItemInData(data, mapping.itemName);
  if (!itemPos) {
    console.warn(`⚠️ 項目「${mapping.itemName}」が見つかりません`);
    return [];
  }
  
  const dataStartPos = getDataStartPosition(itemPos, mapping.direction);
  const bounds = (ctx && ctx.bounds) ? ctx.bounds : {};
  return getDataFromDirectionCached(data, dataStartPos, mapping.direction, mapping.expandType, bounds);
}

/**
 * 範囲指定でデータを抽出
 * @param {Sheet} sheet - シート
 * @param {Object} mapping - マッピング情報
 * @returns {Array} 値の配列
 */
function extractByRange(sheet, mapping, _ctx) {
  const values = [];
  
  if (mapping.expandType === EXPAND_TYPES.INHERIT || mapping.expandType === '下方継承') {
    const value = getCellValueFromSheet(sheet, mapping.designRange);
    values.push(value);
  } else {
    const rangeValues = getRangeValuesFromSheet(sheet, mapping.designRange);
    // 空白はここでは保持し、後段のignoreBlankで間引く
    values.push(...rangeValues);
  }
  
  return values;
}

/**
 * データ取得開始位置を決定
 * @param {Object} itemPos - 項目位置
 * @param {string} direction - 方向
 * @returns {Object} 開始位置
 */
function getDataStartPosition(itemPos, direction) {
  if (DIRECTIONS.DOWN.includes(direction)) {
    return { row: itemPos.row + 1, col: itemPos.col };
  } else if (DIRECTIONS.RIGHT.includes(direction)) {
    return { row: itemPos.row, col: itemPos.col + 1 };
  }
  return { row: itemPos.row + 1, col: itemPos.col };
}

/**
 * チェック無視列を埋める（修正版）
 * データ生成時はチェック無視列もデータを生成し、チェック時のみ無視する
 * @param {Array} mappings - マッピング情報
 * @param {Object} dataByMapping - マッピングごとのデータ
 * @param {number} maxRecordCount - 最大レコード数
 */
function fillIgnoredColumns(mappings, dataByMapping, maxRecordCount) {
  mappings.forEach(_mapping => {
    // データ生成時はチェック無視列も通常通り処理する（ここでは何もしない）
  });
}

/**
 * レコードを生成（改良版）
 * 空白を含むデータも正しく処理
 * @param {Array} mappings - マッピング情報
 * @param {Object} dataByMapping - マッピングごとのデータ
 * @param {number} maxRecordCount - 最大レコード数
 * @returns {Array<Object>} レコードの配列
 */
function createRecords(mappings, dataByMapping, maxRecordCount) {
  const records = [];
  const ignoreBlankColumns = mappings
    .filter(m => m.ignoreBlank === true)
    .map(m => m.masterColumn);
  
  for (let i = 0; i < maxRecordCount; i++) {
    const record = {};
    let shouldSkip = false;
    
    mappings.forEach(mapping => {
      const values = dataByMapping[mapping.masterColumn];
      
      if (mapping.expandType === EXPAND_TYPES.INHERIT || mapping.expandType === '下方継承') {
        // 下方継承：全レコードで同じ値を使用
        record[mapping.masterColumn] = values[0] || '';
      } else {
        // 通常：対応する行の値を使用
        if (i < values.length) {
          record[mapping.masterColumn] = values[i];
        } else {
          record[mapping.masterColumn] = '';
        }
      }
      
      // 空白無視チェック（チェック無視の列は除外）
      if (!mapping.checkIgnore && ignoreBlankColumns.includes(mapping.masterColumn)) {
        const value = record[mapping.masterColumn];
        if (value === '' || value === null || value === undefined) {
          shouldSkip = true;
        }
      }
    });
    
    if (!shouldSkip) {
      records.push(record);
    }
  }
  
  return records;
}

/**
 * 設計書内で項目名を検索（キャッシュ配列版）
 */
function findItemInSheet(sheet, itemName) {
  const data = sheet.getDataRange().getValues();
  return findItemInData(data, itemName);
}

function findItemInData(data, itemName) {
  const searchName = String(itemName || '').toLowerCase();
  for (let row = 0; row < data.length; row++) {
    const rowArr = data[row] || [];
    for (let col = 0; col < rowArr.length; col++) {
      const cellValue = String(rowArr[col]).toLowerCase();
      if (cellValue === searchName) {
        return { row: row + 1, col: col + 1 };
      }
    }
  }
  return null;
}

/**
 * 指定方向にデータを取得（委譲版）
 */
function getDataFromDirection(sheet, startPos, direction, expandType) {
  const data = sheet.getDataRange().getValues();
  return getDataFromDirectionCached(data, startPos, direction, expandType, {});
}

/**
 * 指定方向にデータを取得（キャッシュ配列＋空白無視境界対応）
 */
function getDataFromDirectionCached(data, startPos, direction, expandType, bounds) {
  // 下方継承の場合は1セルのみ取得
  if (expandType === EXPAND_TYPES.INHERIT || expandType === '下方継承') {
    const rawValue = (data[startPos.row - 1] && data[startPos.row - 1][startPos.col - 1]);
    return [convertValueType(rawValue)];
  }

  const rows = data.length;
  const cols = rows > 0 ? data[0].length : 0;
  let allValues = [];

  // 既存フォールバック用の連続空白停止
  const MAX_CONSECUTIVE_EMPTY = 3;

  if (DIRECTIONS.DOWN.includes(direction) || direction === '▼' || direction === '↓') {
    const endRow = Math.min(bounds && bounds.downLastRow ? bounds.downLastRow : rows, rows);
    if (bounds && bounds.downLastRow) {
      for (let r = startPos.row; r <= endRow; r++) {
        const raw = (data[r - 1] && data[r - 1][startPos.col - 1]);
        allValues.push(raw === undefined ? '' : convertValueType(raw));
      }
      return allValues;
    }

    let consecutiveEmptyCount = 0;
    for (let r = startPos.row; r <= rows; r++) {
      const raw = (data[r - 1] && data[r - 1][startPos.col - 1]);
      if (raw === '' || raw === null || raw === undefined) {
        consecutiveEmptyCount++;
        allValues.push('');
        if (consecutiveEmptyCount >= MAX_CONSECUTIVE_EMPTY) break;
      } else {
        consecutiveEmptyCount = 0;
        allValues.push(convertValueType(raw));
      }
    }
  } else if (DIRECTIONS.RIGHT.includes(direction) || direction === '▶' || direction === '▶︎' || direction === '→') {
    const endCol = Math.min(bounds && bounds.rightLastCol ? bounds.rightLastCol : cols, cols);
    if (bounds && bounds.rightLastCol) {
      for (let c = startPos.col; c <= endCol; c++) {
        const raw = (data[startPos.row - 1] && data[startPos.row - 1][c - 1]);
        allValues.push(raw === undefined ? '' : convertValueType(raw));
      }
      return allValues;
    }

    let consecutiveEmptyCount = 0;
    for (let c = startPos.col; c <= cols; c++) {
      const raw = (data[startPos.row - 1] && data[startPos.row - 1][c - 1]);
      if (raw === '' || raw === null || raw === undefined) {
        consecutiveEmptyCount++;
        allValues.push('');
        if (consecutiveEmptyCount >= MAX_CONSECUTIVE_EMPTY) break;
      } else {
        consecutiveEmptyCount = 0;
        allValues.push(convertValueType(raw));
      }
    }
  }

  // 末尾の連続した空白を削除
  while (allValues.length > 0 && (allValues[allValues.length - 1] === '' || allValues[allValues.length - 1] === null || allValues[allValues.length - 1] === undefined)) {
    allValues.pop();
  }
  return allValues;
}

/**
 * ユニークキー情報を取得
 * @param {string} masterSheetName - マスターシート名
 * @returns {Object} ユニークキー情報
 */
function getUniqueKeyInfo(masterSheetName) {
  const ss = SpreadsheetApp.getActiveSpreadsheet();
  const checkData = getCheckManagementData(ss);
  
  for (let i = 1; i < checkData.data.length; i++) {
    if (checkData.data[i][0]) {
      const mappingSheet = getMappingSheetForDesign(checkData.data[i][1]);
      if (mappingSheet) {
        const mappingData = mappingSheet.getDataRange().getValues();
        const mappings = parseMappingData(mappingData);
        const targetMappings = mappings.filter(m => m.masterName === masterSheetName);
        
        // ユニークキーが設定されているかつチェック対象外でない列を取得
        const uniqueKeyColumns = targetMappings
          .filter(m => toBooleanValue(m.isUniqueKey) && !m.checkIgnore)
          .map(m => m.masterColumn);
        
        return {
          hasValidUniqueKey: uniqueKeyColumns.length > 0,
          uniqueKeyColumns: uniqueKeyColumns
        };
      }
    }
  }
  
  return {
    hasValidUniqueKey: false,
    uniqueKeyColumns: []
  };
}

/**
 * ユニークキーを使用したレコード比較（上書き候補検出対応版）
 * @param {Array} generatedRecords - 生成レコード
 * @param {Array} masterRecords - マスターレコード
 * @param {Array} masterHeaders - マスターヘッダー
 * @param {Array} uniqueKeyColumns - ユニークキー列
 * @param {Array} checkIgnoreColumns - チェック無視列
 * @param {Array} orderedHeaders - 順序付きヘッダー
 * @param {Array} actualRowNumbers - 実際のシート行番号配列
 * @returns {Object} エラー配列と上書き候補
 */
function compareWithUniqueKey(generatedRecords, masterRecords, masterHeaders, uniqueKeyColumns, checkIgnoreColumns, orderedHeaders, actualRowNumbers = null) {
  const errors = [];
  const overwriteCandidates = [];
  
  // マスターレコードをユニークキーでインデックス化
  const masterIndex = {};
  masterRecords.forEach((masterRecord, recordIndex) => {
    const keyValues = uniqueKeyColumns.map(col => {
      const colIndex = masterHeaders.indexOf(col);
      return colIndex >= 0 ? masterRecord[colIndex] : '';
    });
    const key = JSON.stringify(keyValues);
    
    if (!masterIndex[key]) {
      masterIndex[key] = [];
    }
    masterIndex[key].push({ 
      record: masterRecord, 
      index: recordIndex,
      actualRowNumber: actualRowNumbers ? actualRowNumbers[recordIndex] : recordIndex + 1
    });
  });
  
  // 生成レコードをチェック
  generatedRecords.forEach((genRecord, genIndex) => {
    const keyValues = uniqueKeyColumns.map(col => {
      const value = genRecord[col];
      return (value !== null && value !== undefined) ? value : '';
    });
    const key = JSON.stringify(keyValues);
    
    const matchingMasterRecords = masterIndex[key];
    
    if (!matchingMasterRecords || matchingMasterRecords.length === 0) {
      // ユニークキーが一致するレコードがない
      errors.push(`生成データ${genIndex + 1}: ユニークキー [${uniqueKeyColumns.join(', ')}] = [${keyValues.join(', ')}] に一致するマスターデータが見つかりません`);
    } else {
      // ユニークキーが一致するレコードがある場合、詳細比較
      const genArray = orderedHeaders
        .filter(h => !checkIgnoreColumns.includes(h))
        .map(h => {
          const value = genRecord[h];
          return (value !== null && value !== undefined) ? value : '';
        });
      
      const found = matchingMasterRecords.some(masterItem => {
        const filteredMasterRecord = filterMasterRecord(masterItem.record, masterHeaders, checkIgnoreColumns);
        return JSON.stringify(genArray) === JSON.stringify(filteredMasterRecord);
      });
      
      if (!found) {
        // データの詳細比較でエラー - 上書き候補として記録
        const masterRecord = matchingMasterRecords[0].record;
        const filteredMasterRecord = filterMasterRecord(masterRecord, masterHeaders, checkIgnoreColumns);
        
        const differences = [];
        const diffDetails = [];
        orderedHeaders.filter(h => !checkIgnoreColumns.includes(h)).forEach((header, idx) => {
          const genValue = genArray[idx];
          const masterValue = filteredMasterRecord[idx];
          if (genValue !== masterValue) {
            differences.push(`${header}: 生成[${genValue}] ≠ マスター[${masterValue}]`);
            diffDetails.push({
              column: header,
              generated: genValue,
              master: masterValue
            });
          }
        });
        
        // 上書き候補として記録
        overwriteCandidates.push({
          uniqueKey: keyValues.join(' | '),
          uniqueKeyColumns: uniqueKeyColumns,
          uniqueKeyValues: keyValues,
          generatedRecord: genRecord,
          masterRecord: masterRecord,
          masterRecordIndex: matchingMasterRecords[0].index,
          actualRowNumber: matchingMasterRecords[0].actualRowNumber, // 実際のシート行番号
          differences: diffDetails,
          generatedIndex: genIndex
        });
        
        errors.push(`生成データ${genIndex + 1}: ユニークキー一致するが詳細データが異なります [${uniqueKeyColumns.join(', ')}] = [${keyValues.join(', ')}] - 差分: ${differences.join(', ')}`);
      }
    }
  });
  
  return { errors, overwriteCandidates };
}

// ==================== データ比較・チェック ====================

/**
 * 生成データとマスターデータを照合
 * @returns {Object} チェック結果
 */
function checkGeneratedDataAgainstMaster() {
  const ss = SpreadsheetApp.getActiveSpreadsheet();
  const config = getMasterDataConfig();
  const checkSheet = ss.getSheetByName(SHEET_NAMES.CHECK_MANAGEMENT);
  const checkData = checkSheet.getDataRange().getValues();
  const checkHeaders = checkData[0];
  
  const masterUrl = getMasterUrl(checkData, checkHeaders);
  if (!masterUrl) {
    console.log('⚠️ チェック対象のマスターURLが見つかりません');
    return {};
  }
  
  try {
    const masterSS = SpreadsheetApp.openByUrl(masterUrl);
    const targetSheets = getTargetMasterSheets(checkData, checkHeaders);
    const checkResults = performComparison(ss, masterSS, targetSheets, config);
    updateCheckResults(checkSheet, checkHeaders, checkResults);
    return checkResults;
  } catch (error) {
    console.error(`❌ マスターデータへのアクセスエラー: ${error.message}`);
    updateErrorResults(checkSheet, checkHeaders, error.message);
    return { error: error.message };
  }
}

/**
 * ダイレクトチェック（高速版）
 * @returns {Object} チェック結果
 */
function performDirectCheck() {
  const ss = SpreadsheetApp.getActiveSpreadsheet();
  const config = getMasterDataConfig();
  const checkResults = {};
  
  try {
    const checkData = getCheckManagementData(ss);
    const designPairs = getDesignMappingPairs(checkData.data, checkData.headers);
    const masterUrl = checkData.data[1][checkData.headers.indexOf(COLUMN_NAMES.MASTER_URL)];
    const masterSS = SpreadsheetApp.openByUrl(masterUrl);
    
    const masterGroups = groupDesignsByMaster(ss, designPairs);
    const allErrors = [];
    
    Object.entries(masterGroups).forEach(([masterName, groupData]) => {
      const generatedRecords = collectDirectRecords(ss, masterName, groupData);
      const masterSheet = masterSS.getSheetByName(masterName);
      
      if (masterSheet) {
        const headers = groupData.mappings.map(m => m.masterColumn);
        const result = compareRecordsWithMaster(
          generatedRecords, masterSheet, masterName, config, headers
        );
        checkResults[masterName] = result;
        
        if (result.errors && result.errors.length > 0) {
          allErrors.push({ masterName, errorCount: result.errors.length });
        }
      } else {
        checkResults[masterName] = createErrorResult(masterName, generatedRecords.length);
        allErrors.push({ masterName, errorCount: 1 });
      }
    });
    
    updateDirectCheckResults(ss, designPairs, allErrors);
    
  } catch (error) {
    console.error('ダイレクトチェックエラー:', error);
    checkResults.error = error.message;
    updateErrorInCheckSheet(ss, error.message);
  }
  
  return checkResults;
}

/**
 * レコードとマスターデータを比較（ユニークキー対応版）
 * @param {Array} generatedRecords - 生成レコード
 * @param {Sheet} masterSheet - マスターシート
 * @param {string} masterSheetName - マスターシート名
 * @param {Object} config - 設定
 * @param {Array} orderedHeaders - ヘッダー順序
 * @returns {Object} 比較結果
 */
function compareRecordsWithMaster(generatedRecords, masterSheet, masterSheetName, config, orderedHeaders) {
  const errors = [];
  const masterConfig = config[masterSheetName] || { startRow: 1, startCol: 1 };
  
  const masterData = getMasterData(masterSheet, masterConfig);
  const checkIgnoreColumns = getCheckIgnoreColumns(masterSheetName);
  
  // ユニークキー情報を取得
  const uniqueKeyInfo = getUniqueKeyInfo(masterSheetName);
  
  const filteredHeaders = filterHeaders(orderedHeaders, masterData.headers, checkIgnoreColumns);
  
  if (!headersMatch(filteredHeaders.generated, filteredHeaders.master)) {
    errors.push(`ヘッダーが一致しません。生成: [${filteredHeaders.generated}] / マスター: [${filteredHeaders.master}]`);
  }
  
  // ユニークキーが有効な場合の詳細チェック
  if (uniqueKeyInfo.hasValidUniqueKey) {
    const uniqueKeyResult = compareWithUniqueKey(
      generatedRecords, 
      masterData.records, 
      masterData.headers, 
      uniqueKeyInfo.uniqueKeyColumns,
      checkIgnoreColumns,
      orderedHeaders
    );
    errors.push(...uniqueKeyResult.errors);
    
    // 上書き候補がある場合は結果に含める
    const result = {
      message: errors.length === 0 ? '✅ OK' : `❌ エラー (${errors.length}件)`,
      errors,
      generatedCount: generatedRecords.length,
      masterCount: masterData.records.length
    };
    
    if (uniqueKeyResult.overwriteCandidates && uniqueKeyResult.overwriteCandidates.length > 0) {
      result.overwriteCandidates = uniqueKeyResult.overwriteCandidates;
      result.canOverwrite = true;
    }
    
    return result;
  } else {
    // 従来通りのレコード比較
    generatedRecords.forEach((genRecord, index) => {
      const genArray = filteredHeaders.generated.map(h => {
        const value = genRecord[h];
        return (value !== null && value !== undefined) ? value : '';
      });
      
      const found = masterData.records.some(masterRecord => {
        const filteredMasterRecord = filterMasterRecord(masterRecord, masterData.headers, checkIgnoreColumns);
        return JSON.stringify(genArray) === JSON.stringify(filteredMasterRecord);
      });
      
      if (!found) {
        errors.push(`データ${index + 1}がマスターに存在しません: [${genArray.join(', ')}]`);
      }
    });
  }
  
  return {
    message: errors.length === 0 ? '✅ OK' : `❌ エラー (${errors.length}件)`,
    errors,
    generatedCount: generatedRecords.length,
    masterCount: masterData.records.length
  };
}

/**
 * 生成データとマスターデータを比較（シート版）
 * @param {Sheet} generatedSheet - 生成シート
 * @param {Sheet} masterSheet - マスターシート
 * @param {string} masterSheetName - マスターシート名
 * @param {Object} config - 設定
 * @returns {Object} 比較結果
 */
function compareData(generatedSheet, masterSheet, masterSheetName, config) {
  const errors = [];
  const masterConfig = config[masterSheetName] || { startRow: 1, startCol: 1 };
  
  // データ取得
  const generatedData = generatedSheet.getDataRange().getValues();
  if (generatedData.length < 2) {
    return { message: '⚠️ 生成データが空です', errors: ['生成データが見つかりません'] };
  }
  
  const masterData = getMasterData(masterSheet, masterConfig);
  const checkIgnoreColumns = getCheckIgnoreColumns(masterSheetName);
  
  // 生成データからシート名列（A列）を除外
  const generatedHeaders = generatedData[0].slice(1); // 最初の列（シート名）を除外
  const generatedRecords = generatedData.slice(1).map(row => row.slice(1)); // 各行の最初の列を除外
  
  // ヘッダー比較
  const filteredHeaders = filterHeaders(
    generatedHeaders, 
    masterData.headers, 
    checkIgnoreColumns
  );
  
  if (!headersMatch(filteredHeaders.generated, filteredHeaders.master)) {
    errors.push(`ヘッダーが一致しません。生成: [${filteredHeaders.generated}] / マスター: [${filteredHeaders.master}]`);
  }
  
  // レコード比較
  generatedRecords.forEach((genRecord, index) => {
    const filteredGenRecord = filterRecord(genRecord, generatedHeaders, checkIgnoreColumns);
    
    const found = masterData.records.some(masterRecord => {
      const filteredMasterRecord = filterMasterRecord(
        masterRecord, 
        masterData.headers, 
        checkIgnoreColumns
      );
      return recordsMatch(filteredGenRecord, filteredMasterRecord, filteredHeaders, !headersMatch(filteredHeaders.generated, filteredHeaders.master));
    });
    
    if (!found) {
      errors.push(`行${index + 2}のデータがマスターに存在しません: [${filteredGenRecord.join(', ')}]`);
    }
  });
  
  return {
    message: errors.length === 0 ? '✅ OK' : `❌ エラー (${errors.length}件)`,
    errors
  };
}

// ==================== ユーティリティ関数 ====================

/**
 * 値の型を適切に変換
 * @param {*} value - 変換対象の値
 * @returns {*} 変換後の値
 */
function convertValueType(value) {
  if (value === null || value === undefined || value === '') {
    return value;
  }
  
  if (typeof value === 'boolean') {
    return value;
  }
  
  if (typeof value === 'string') {
    const upperValue = value.toUpperCase();
    if (upperValue === 'TRUE') return true;
    if (upperValue === 'FALSE') return false;
    
    const trimmed = value.trim();
    if (trimmed !== '') {
      if (/^-?\d+$/.test(trimmed)) {
        const intValue = parseInt(trimmed, 10);
        if (!isNaN(intValue)) return intValue;
      } else if (/^-?\d+\.\d+$/.test(trimmed)) {
        const floatValue = parseFloat(trimmed);
        if (!isNaN(floatValue)) return floatValue;
      }
    }
  }
  
  return value;
}

/**
 * マッピングデータをパース
 * @param {Array} mappingData - マッピングデータ
 * @returns {Array} パース済みマッピング
 */
function parseMappingData(mappingData) {
  const headers = mappingData[0];
  const mappings = [];
  
  const indices = {
    itemName: headers.indexOf(MAPPING_COLUMNS.ITEM_NAME),
    direction: headers.indexOf(MAPPING_COLUMNS.DIRECTION),
    designRange: headers.indexOf(MAPPING_COLUMNS.DESIGN_RANGE),
    masterName: headers.indexOf(MAPPING_COLUMNS.MASTER_NAME),
    masterColumn: headers.indexOf(MAPPING_COLUMNS.MASTER_COLUMN),
    checkIgnore: headers.indexOf(MAPPING_COLUMNS.CHECK_IGNORE),
    uniqueKey: headers.indexOf(MAPPING_COLUMNS.UNIQUE_KEY),
    expandType: headers.indexOf(MAPPING_COLUMNS.EXPAND_TYPE),
    ignoreBlank: headers.indexOf(MAPPING_COLUMNS.IGNORE_BLANK)
  };
  
  for (let i = 1; i < mappingData.length; i++) {
    const row = mappingData[i];
    if (row[indices.itemName] || (indices.checkIgnore >= 0 && row[indices.checkIgnore])) {
      mappings.push({
        itemName: row[indices.itemName] || '',
        direction: indices.direction >= 0 ? row[indices.direction] : null,
        designRange: indices.designRange >= 0 ? row[indices.designRange] : null,
        masterName: row[indices.masterName],
        masterColumn: row[indices.masterColumn],
        checkIgnore: toBooleanValue(row[indices.checkIgnore]),
        isUniqueKey: row[indices.uniqueKey],
        expandType: row[indices.expandType],
        ignoreBlank: toBooleanValue(row[indices.ignoreBlank])
      });
    }
  }
  
  return mappings;
}

/**
 * boolean値に変換
 * @param {*} value - 変換対象
 * @returns {boolean} boolean値
 */
function toBooleanValue(value) {
  return value === true || 
         value === 'TRUE' || 
         value === 'true' || 
         value === 'True' ||
         value === 1 ||
         value === '1';
}

/**
 * 範囲から値を取得（空白セル対応版）
 * @param {Sheet} sheet - シート
 * @param {string} range - 範囲
 * @returns {Array} 値の配列
 */
function getRangeValuesFromSheet(sheet, range) {
  const normalizedRange = normalizeRange(range);
  try {
    const rng = sheet.getRange(normalizedRange);
    const vals2D = rng.getValues();
    const flat = [];
    for (let r = 0; r < vals2D.length; r++) {
      const rowArr = vals2D[r] || [];
      for (let c = 0; c < rowArr.length; c++) {
        flat.push(convertValueType(rowArr[c]));
      }
    }
    return flat;
  } catch (e) {
    // フォールバック（安全策）
    const values = [];
    if (normalizedRange.includes(':')) {
      const [startCell, endCell] = normalizedRange.split(':');
      const startPos = parseCellAddress(startCell);
      const endPos = parseCellAddress(endCell);
      for (let row = startPos.row; row <= endPos.row; row++) {
        const cellAddress = columnIndexToLetter(startPos.col) + row;
        values.push(getCellValueFromSheet(sheet, cellAddress));
      }
    } else {
      values.push(getCellValueFromSheet(sheet, normalizedRange));
    }
    return values;
  }
}

/**
 * 空白無視=trueのマッピングから、方向抽出の走査上限を決定
 */
function computeBoundsForIgnoreBlank(sheetData, masterMappings) {
  const rows = sheetData.length;
  const cols = rows > 0 ? sheetData[0].length : 0;
  let downLastRow = 0;
  let rightLastCol = 0;

  const targets = masterMappings.filter(m => !m.checkIgnore && m.ignoreBlank === true && m.direction);
  targets.forEach(m => {
    const itemPos = findItemInData(sheetData, m.itemName);
    if (!itemPos) return;
    const start = getDataStartPosition(itemPos, m.direction);
    if (DIRECTIONS.DOWN.includes(m.direction) || m.direction === '▼' || m.direction === '↓') {
      let last = 0;
      for (let r = start.row; r <= rows; r++) {
        const v = (sheetData[r - 1] && sheetData[r - 1][start.col - 1]);
        if (!(v === '' || v === null || v === undefined)) last = r;
      }
      if (last > downLastRow) downLastRow = last;
    } else if (DIRECTIONS.RIGHT.includes(m.direction) || m.direction === '▶' || m.direction === '▶︎' || m.direction === '→') {
      let last = 0;
      const rowArr = sheetData[start.row - 1] || [];
      for (let c = start.col; c <= cols; c++) {
        const v = rowArr[c - 1];
        if (!(v === '' || v === null || v === undefined)) last = c;
      }
      if (last > rightLastCol) rightLastCol = last;
    }
  });

  const bounds = {};
  if (downLastRow > 0) bounds.downLastRow = downLastRow;
  if (rightLastCol > 0) bounds.rightLastCol = rightLastCol;
  return bounds;
}

/**
 * セルから値を取得
 * @param {Sheet} sheet - シート
 * @param {string} cellAddress - セルアドレス
 * @returns {*} セルの値
 */
function getCellValueFromSheet(sheet, cellAddress) {
  const range = sheet.getRange(cellAddress);
  const rawValue = range.getValue();
  return convertValueType(rawValue);
}

/**
 * 範囲の正規化
 * @param {string} range - 範囲文字列
 * @returns {string} 正規化された範囲
 */
function normalizeRange(range) {
  let normalizedRange = range;
  
  if (range.includes(':')) {
    const parts = range.split(':');
    if (parts.length > 2) {
      normalizedRange = `${parts[0]}:${parts[parts.length - 1]}`;
    }
  }
  
  normalizedRange = normalizedRange.replace(/([A-Z])([A-Z]{2,})(\d+)/g, (match, first, rest, num) => {
    return first + rest.slice(-1) + num;
  });
  
  return normalizedRange;
}

/**
 * セルアドレスを解析
 * @param {string} cellAddress - セルアドレス
 * @returns {Object} 行列情報
 */
function parseCellAddress(cellAddress) {
  const match = cellAddress.match(/^([A-Z]+)(\d+)$/);
  if (!match) return { row: 0, col: 0 };
  
  const colLetter = match[1];
  const row = parseInt(match[2]);
  
  let col = 0;
  for (let i = 0; i < colLetter.length; i++) {
    col = col * 26 + (colLetter.charCodeAt(i) - 'A'.charCodeAt(0) + 1);
  }
  
  return { row, col: col - 1 };
}

/**
 * 列インデックスを文字に変換
 * @param {number} col - 列インデックス
 * @returns {string} 列文字
 */
function columnIndexToLetter(col) {
  let letter = '';
  col++;
  
  while (col > 0) {
    const mod = (col - 1) % 26;
    letter = String.fromCharCode(65 + mod) + letter;
    col = Math.floor((col - mod) / 26);
  }
  
  return letter;
}

/**
 * マスターシートを更新（シート名でマッチング）
 * @param {Spreadsheet} ss - スプレッドシート
 * @param {Object} masterGroups - マスターグループ
 */
function updateMasterSheets(ss, masterGroups) {
  Object.entries(masterGroups).forEach(([masterName, groupData]) => {
    console.log(`🔄 ${masterName}の更新処理を開始...`);
    
    const masterSheet = ss.getSheetByName(masterName);
    if (!masterSheet) {
      console.warn(`⚠️ ${masterName}シートが存在しないため、新規作成します`);
      generateMasterSheets(ss, { [masterName]: groupData });
      return;
    }
    
    // 既存データを取得
    const existingData = masterSheet.getDataRange().getValues();
    if (existingData.length < 2) {
      console.warn(`⚠️ ${masterName}に既存データがないため、新規生成します`);
      generateMasterSheets(ss, { [masterName]: groupData });
      return;
    }
    
    const existingHeaders = existingData[0];
    const existingRecords = existingData.slice(1);
    
    // 新しいデータを生成
    const recordsData = collectRecordsForMaster(ss, masterName, groupData);
    const newHeaders = ['シート名', ...getUniqueHeaders(groupData.mappings)];
    
    // 新しいデータをシート名でグループ化
    const newRecordsBySheet = {};
    recordsData.sourceSheetNames.forEach((sheetName, index) => {
      if (!newRecordsBySheet[sheetName]) {
        newRecordsBySheet[sheetName] = [];
      }
      newRecordsBySheet[sheetName].push(recordsData.records[index]);
    });
    
    // 既存データをシート名でグループ化
    const existingRecordsBySheet = {};
    existingRecords.forEach(existingRecord => {
      const sheetName = existingRecord[0]; // A列のシート名
      if (!existingRecordsBySheet[sheetName]) {
        existingRecordsBySheet[sheetName] = [];
      }
      existingRecordsBySheet[sheetName].push(existingRecord);
    });
    
    const updatedRecords = [];
    const processedSheets = new Set();
    
    // 新しいデータが存在するシートを処理
    Object.entries(newRecordsBySheet).forEach(([sheetName, newRecords]) => {
      newRecords.forEach(newRecord => {
        const updatedRow = [
          sheetName,
          ...getUniqueHeaders(groupData.mappings).map(header => {
            const value = newRecord[header];
            return (value !== null && value !== undefined) ? value : '';
          })
        ];
        updatedRecords.push(updatedRow);
      });
      processedSheets.add(sheetName);
      console.log(`  ✅ ${sheetName}のデータを更新（${newRecords.length}件）`);
    });
    
    // 新しいデータにないが既存データに存在するシートを保持
    Object.entries(existingRecordsBySheet).forEach(([sheetName, existingSheetRecords]) => {
      if (!processedSheets.has(sheetName)) {
        existingSheetRecords.forEach(existingRecord => {
          updatedRecords.push(existingRecord);
        });
        processedSheets.add(sheetName);
        console.log(`  📋 ${sheetName}のデータを保持（設計書に変更なし、${existingSheetRecords.length}件）`);
      }
    });
    
    // シートに書き戻し
    masterSheet.clear();
    const allValues = [newHeaders, ...updatedRecords];
    if (allValues.length > 0) {
      masterSheet.getRange(1, 1, allValues.length, allValues[0].length).setValues(allValues);
    }
    
    console.log(`✅ ${masterName}の更新が完了（${updatedRecords.length}件）`);
  });
}

/**
 * 上書き候補を収集
 * @param {Spreadsheet} ss - スプレッドシート
 * @param {Spreadsheet} masterSS - マスタースプレッドシート
 * @param {Object} config - 設定
 * @returns {Array} 上書き候補配列
 */
function collectOverwriteCandidates(ss, masterSS, config) {
  const allCandidates = [];
  const checkData = getCheckManagementData(ss);
  const designPairs = getDesignMappingPairs(checkData.data, checkData.headers);
  const masterGroups = groupDesignsByMaster(ss, designPairs);
  
  Object.entries(masterGroups).forEach(([masterName, groupData]) => {
    // ユニークキー情報を確認
    const uniqueKeyInfo = getUniqueKeyInfo(masterName);
    if (!uniqueKeyInfo.hasValidUniqueKey) {
      return; // ユニークキーが設定されていない場合はスキップ
    }
    
    const generatedRecords = collectDirectRecords(ss, masterName, groupData);
    const masterSheet = masterSS.getSheetByName(masterName);
    
    if (masterSheet && generatedRecords.length > 0) {
      const masterConfig = config[masterName] || { startRow: 1, startCol: 1 };
      const masterData = getMasterData(masterSheet, masterConfig);
      const checkIgnoreColumns = getCheckIgnoreColumns(masterName);
      const headers = groupData.mappings.map(m => m.masterColumn);
      
      const uniqueKeyResult = compareWithUniqueKey(
        generatedRecords,
        masterData.records,
        masterData.headers,
        uniqueKeyInfo.uniqueKeyColumns,
        checkIgnoreColumns,
        headers,
        masterData.actualRowNumbers // 実際のシート行番号を渡す
      );
      
      if (uniqueKeyResult.overwriteCandidates && uniqueKeyResult.overwriteCandidates.length > 0) {
        uniqueKeyResult.overwriteCandidates.forEach(candidate => {
          allCandidates.push({
            ...candidate,
            masterSheetName: masterName,
            masterSheet: masterSheet,
            masterConfig: masterConfig,
            masterHeaders: masterData.headers
          });
        });
      }
    }
  });
  
  return allCandidates;
}

/**
 * 上書き選択ダイアログを表示
 * @param {Array} candidates - 上書き候補
 * @param {Spreadsheet} masterSS - マスタースプレッドシート
 * @param {Object} config - 設定
 * @param {Date} startTime - 開始時刻
 */
function showOverwriteSelectionDialog(candidates, masterSS, config, startTime) {
  const htmlContent = createOverwriteSelectionHTML(candidates);
  
  const htmlOutput = HtmlService.createHtmlOutput(htmlContent)
    .setWidth(800)
    .setHeight(600);
  
  SpreadsheetApp.getUi().showModalDialog(htmlOutput, 'マスターデータ上書き選択');
}

/**
 * 上書き選択HTMLを生成
 * @param {Array} candidates - 上書き候補
 * @returns {string} HTML文字列
 */
function createOverwriteSelectionHTML(candidates) {
  return '<!DOCTYPE html>' +
    '<html>' +
    '<head>' +
      '<base target="_top">' +
      '<style>' +
        'body { font-family: Arial, sans-serif; padding: 20px; background-color: #f5f5f5; }' +
        '.container { background-color: white; border-radius: 8px; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }' +
        'h2 { color: #333; margin-bottom: 16px; }' +
        '.candidate { border: 1px solid #ddd; border-radius: 6px; margin-bottom: 12px; overflow: hidden; }' +
        '.candidate-header { background: #f8f9fa; padding: 12px; border-bottom: 1px solid #ddd; display: flex; align-items: center; }' +
        '.candidate-header input[type="checkbox"] { margin-right: 8px; }' +
        '.candidate-title { font-weight: bold; color: #495057; }' +
        '.candidate-details { padding: 12px; }' +
        '.unique-key { background: #e3f2fd; padding: 8px; border-radius: 4px; margin-bottom: 12px; }' +
        '.diff-table { width: 100%; border-collapse: collapse; margin-top: 8px; }' +
        '.diff-table th, .diff-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }' +
        '.diff-table th { background: #f1f3f4; font-weight: bold; }' +
        '.generated-value { background: #e8f5e8; }' +
        '.master-value { background: #fff3cd; }' +
        '.controls { margin-top: 20px; display: flex; gap: 12px; align-items: center; }' +
        'button { padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; }' +
        '.btn-primary { background: #007bff; color: white; }' +
        '.btn-primary:hover { background: #0056b3; }' +
        '.btn-secondary { background: #6c757d; color: white; }' +
        '.btn-secondary:hover { background: #545b62; }' +
        '.select-all { margin-right: 16px; }' +
      '</style>' +
    '</head>' +
    '<body>' +
      '<div class="container">' +
        '<h2>マスターデータ上書き候補選択</h2>' +
        '<p>ユニークキーが一致するが内容が異なるレコードが見つかりました。上書きするレコードを選択してください。</p>' +
        
        '<div class="controls">' +
          '<label class="select-all"><input type="checkbox" id="selectAll"> すべて選択</label>' +
          '<button class="btn-primary" onclick="executeOverwrite()">選択したレコードを上書き</button>' +
          '<button class="btn-secondary" onclick="google.script.host.close()">キャンセル</button>' +
        '</div>' +
        
        '<div id="candidates">' +
          candidates.map(function(candidate, index) {
            return '<div class="candidate">' +
              '<div class="candidate-header">' +
                '<input type="checkbox" id="candidate_' + index + '" value="' + index + '">' +
                '<span class="candidate-title">' + candidate.masterSheetName + ' - ユニークキー: ' + candidate.uniqueKey + '</span>' +
              '</div>' +
              '<div class="candidate-details">' +
                '<div class="unique-key">' +
                  '<strong>ユニークキー:</strong> ' + candidate.uniqueKeyColumns.join(', ') + ' = ' + candidate.uniqueKeyValues.join(' | ') +
                '</div>' +
                '<table class="diff-table">' +
                  '<thead>' +
                    '<tr><th>項目</th><th>生成データ</th><th>マスターデータ</th></tr>' +
                  '</thead>' +
                  '<tbody>' +
                    candidate.differences.map(function(diff) {
                      return '<tr>' +
                        '<td>' + diff.column + '</td>' +
                        '<td class="generated-value">' + diff.generated + '</td>' +
                        '<td class="master-value">' + diff.master + '</td>' +
                      '</tr>';
                    }).join('') +
                  '</tbody>' +
                '</table>' +
              '</div>' +
            '</div>';
          }).join('') +
        '</div>' +
        
        '<div class="controls">' +
          '<button class="btn-primary" onclick="executeOverwrite()">選択したレコードを上書き</button>' +
          '<button class="btn-secondary" onclick="google.script.host.close()">キャンセル</button>' +
        '</div>' +
      '</div>' +
      
      '<script>' +
        'const candidates = ' + JSON.stringify(candidates) + ';' +
        
        'document.getElementById("selectAll").addEventListener("change", function() {' +
          'const checkboxes = document.querySelectorAll("input[type=\\"checkbox\\"][id^=\\"candidate_\\"]");' +
          'checkboxes.forEach(function(cb) { cb.checked = this.checked; }.bind(this));' +
        '});' +
        
        'function executeOverwrite() {' +
          'const selectedIndexes = [];' +
          'const checkboxes = document.querySelectorAll("input[type=\\"checkbox\\"][id^=\\"candidate_\\"]:checked");' +
          'checkboxes.forEach(function(cb) { selectedIndexes.push(parseInt(cb.value)); });' +
          
          'if (selectedIndexes.length === 0) {' +
            'alert("上書きするレコードを選択してください。");' +
            'return;' +
          '}' +
          
          'const selectedCandidates = selectedIndexes.map(function(index) { return candidates[index]; });' +
          'google.script.run' +
            '.withSuccessHandler(function(result) {' +
              'alert("上書き完了: " + result.updatedCount + "件のレコードを更新しました。");' +
              'google.script.host.close();' +
            '})' +
            '.withFailureHandler(function(error) {' +
              'alert("エラー: " + error.message);' +
            '})' +
            '.executeMasterDataOverwrite(selectedCandidates);' +
        '}' +
      '</script>' +
    '</body>' +
    '</html>';
}

/**
 * マスターデータ上書きを実行
 * @param {Array} selectedCandidates - 選択された上書き候補
 * @returns {Object} 実行結果
 */
function executeMasterDataOverwrite(selectedCandidates) {
  try {
    const ss = SpreadsheetApp.getActiveSpreadsheet();
    const checkData = getCheckManagementData(ss);
    const masterUrl = getMasterUrl(checkData.data, checkData.headers);
    const masterSS = SpreadsheetApp.openByUrl(masterUrl);
    
    let updatedCount = 0;
    const backupInfo = [];
    
    // マスターシートごとにグループ化
    const candidatesBySheet = {};
    selectedCandidates.forEach(candidate => {
      if (!candidatesBySheet[candidate.masterSheetName]) {
        candidatesBySheet[candidate.masterSheetName] = [];
      }
      candidatesBySheet[candidate.masterSheetName].push(candidate);
    });
    
    // 各マスターシートを更新
    Object.entries(candidatesBySheet).forEach(([sheetName, candidates]) => {
      const masterSheet = masterSS.getSheetByName(sheetName);
      if (!masterSheet) return;
      
      candidates.forEach(candidate => {
        // 更新前の値をバックアップとして記録
        const masterConfig = candidate.masterConfig;
        const targetRow = candidate.actualRowNumber; // 実際のシート行番号を使用
        
        const backupRecord = {};
        candidate.masterHeaders.forEach((header, colIndex) => {
          const targetCol = masterConfig.startCol + colIndex;
          const currentValue = masterSheet.getRange(targetRow, targetCol).getValue();
          backupRecord[header] = currentValue;
        });
        
        backupInfo.push({
          sheetName: sheetName,
          row: targetRow,
          uniqueKey: candidate.uniqueKey,
          beforeValues: backupRecord
        });
        
        // マスターシートの該当行を更新
        candidate.masterHeaders.forEach((header, colIndex) => {
          const targetCol = masterConfig.startCol + colIndex;
          const newValue = candidate.generatedRecord[header];
          
          if (newValue !== null && newValue !== undefined) {
            masterSheet.getRange(targetRow, targetCol).setValue(newValue);
          }
        });
        
        updatedCount++;
        console.log(`✅ ${sheetName}のレコード（行${targetRow}、ユニークキー: ${candidate.uniqueKey}）を更新しました`);
      });
    });
    
    // バックアップ情報をログシートに記録（オプション）
    createOverwriteBackupLog(ss, backupInfo);
    
    return { success: true, updatedCount: updatedCount, backupInfo: backupInfo };
    
  } catch (error) {
    console.error('マスターデータ上書きエラー:', error);
    throw new Error('上書き処理中にエラーが発生しました: ' + error.message);
  }
}

/**
 * 上書きバックアップログを作成
 * @param {Spreadsheet} ss - スプレッドシート
 * @param {Array} backupInfo - バックアップ情報
 */
function createOverwriteBackupLog(ss, backupInfo) {
  try {
    let logSheet = ss.getSheetByName('上書きバックアップログ');
    if (!logSheet) {
      logSheet = ss.insertSheet('上書きバックアップログ');
      // ヘッダーを設定
      logSheet.getRange(1, 1, 1, 6).setValues([['日時', 'シート名', '行番号', 'ユニークキー', '更新前データ', '備考']]);
    }
    
    const timestamp = new Date();
    backupInfo.forEach(info => {
      const nextRow = logSheet.getLastRow() + 1;
      logSheet.getRange(nextRow, 1, 1, 6).setValues([[
        timestamp,
        info.sheetName,
        info.row,
        info.uniqueKey,
        JSON.stringify(info.beforeValues),
        '自動上書き'
      ]]);
    });
    
    console.log(`📝 上書きバックアップログに${backupInfo.length}件記録しました`);
  } catch (error) {
    console.warn('バックアップログ作成に失敗しました:', error.message);
  }
}

// ==================== ヘルパー関数 ====================

/**
 * チェック管理データを取得
 * @param {Spreadsheet} ss - スプレッドシート
 * @returns {Object} チェック管理データ
 */
function getCheckManagementData(ss) {
  const checkSheet = ss.getSheetByName(SHEET_NAMES.CHECK_MANAGEMENT);
  const checkData = checkSheet.getDataRange().getValues();
  const checkHeaders = checkData[0];
  
  return { sheet: checkSheet, data: checkData, headers: checkHeaders };
}

/**
 * マスターURLを取得
 * @param {Array} checkData - チェックデータ
 * @param {Array} checkHeaders - ヘッダー
 * @returns {string|null} マスターURL
 */
function getMasterUrl(checkData, checkHeaders) {
  const masterUrlIdx = checkHeaders.indexOf(COLUMN_NAMES.MASTER_URL);
  
  for (let i = 1; i < checkData.length; i++) {
    if (checkData[i][checkHeaders.indexOf(COLUMN_NAMES.CHECK)] && checkData[i][masterUrlIdx]) {
      return checkData[i][masterUrlIdx];
    }
  }
  
  return null;
}

/**
 * マスターデータを取得
 * @param {Sheet} masterSheet - マスターシート
 * @param {Object} config - 設定
 * @returns {Object} マスターデータ
 */
function getMasterData(masterSheet, config) {
  const masterAllData = masterSheet.getDataRange().getValues();
  const headers = masterAllData[config.startRow - 1].slice(config.startCol - 1);
  const records = [];
  const actualRowNumbers = []; // 実際のシート行番号を追跡
  
  for (let i = config.startRow; i < masterAllData.length; i++) {
    const row = masterAllData[i].slice(config.startCol - 1);
    if (row.some(cell => cell !== '' && cell !== null && cell !== undefined)) {
      records.push(row);
      actualRowNumbers.push(i + 1); // シート行番号（1ベース）
    }
  }
  
  return { headers, records, actualRowNumbers };
}

/**
 * チェック無視列を取得
 * @param {string} masterSheetName - マスターシート名
 * @returns {Array<string>} チェック無視列名
 */
function getCheckIgnoreColumns(masterSheetName) {
  const ss = SpreadsheetApp.getActiveSpreadsheet();
  const checkData = getCheckManagementData(ss);
  
  for (let i = 1; i < checkData.data.length; i++) {
    if (checkData.data[i][0]) {
      const mappingSheet = getMappingSheetForDesign(checkData.data[i][1]);
      if (mappingSheet) {
        const mappingData = mappingSheet.getDataRange().getValues();
        const mappings = parseMappingData(mappingData);
        const targetMappings = mappings.filter(m => m.masterName === masterSheetName);
        return targetMappings.filter(m => m.checkIgnore).map(m => m.masterColumn);
      }
    }
  }
  
  return [];
}

/**
 * ヘッダーをフィルタリング
 * @param {Array} generatedHeaders - 生成側ヘッダー
 * @param {Array} masterHeaders - マスター側ヘッダー
 * @param {Array} checkIgnoreColumns - 無視列
 * @returns {Object} フィルタ済みヘッダー
 */
function filterHeaders(generatedHeaders, masterHeaders, checkIgnoreColumns) {
  return {
    generated: generatedHeaders.filter(h => !checkIgnoreColumns.includes(h)),
    master: masterHeaders.filter(h => !checkIgnoreColumns.includes(h))
  };
}

/**
 * ヘッダーが一致するか確認
 * @param {Array} headers1 - ヘッダー1
 * @param {Array} headers2 - ヘッダー2
 * @returns {boolean} 一致するか
 */
function headersMatch(headers1, headers2) {
  return JSON.stringify(headers1) === JSON.stringify(headers2);
}

/**
 * レコードをフィルタリング
 * @param {Array} record - レコード
 * @param {Array} headers - ヘッダー
 * @param {Array} ignoreColumns - 無視列
 * @returns {Array} フィルタ済みレコード
 */
function filterRecord(record, headers, ignoreColumns) {
  const filtered = [];
  headers.forEach((header, idx) => {
    if (!ignoreColumns.includes(header)) {
      filtered.push(record[idx]);
    }
  });
  return filtered;
}

/**
 * マスターレコードをフィルタリング
 * @param {Array} record - レコード
 * @param {Array} headers - ヘッダー
 * @param {Array} ignoreColumns - 無視列
 * @returns {Array} フィルタ済みレコード
 */
function filterMasterRecord(record, headers, ignoreColumns) {
  return filterRecord(record, headers, ignoreColumns);
}

/**
 * レコードが一致するか確認
 * @param {Array} record1 - レコード1
 * @param {Array} record2 - レコード2
 * @param {Object} headers - ヘッダー情報
 * @param {boolean} useMapping - マッピングを使用するか
 * @returns {boolean} 一致するか
 */
function recordsMatch(record1, record2, headers, useMapping) {
  if (!useMapping) {
    return JSON.stringify(record1) === JSON.stringify(record2);
  }
  
  // ヘッダーが異なる場合の処理
  const commonHeaders = headers.generated.filter(h => headers.master.includes(h));
  const mapped1 = commonHeaders.map((h) => record1[headers.generated.indexOf(h)]);
  const mapped2 = commonHeaders.map((h) => record2[headers.master.indexOf(h)]);
  
  return JSON.stringify(mapped1) === JSON.stringify(mapped2);
}

// ==================== 結果更新関数 ====================

/**
 * チェック結果を更新
 * @param {Sheet} checkSheet - チェック管理シート
 * @param {Array} checkHeaders - ヘッダー
 * @param {Object} results - 結果
 */
function updateCheckResults(checkSheet, checkHeaders, results) {
  const currentTime = new Date();
  const lastCheckIdx = checkHeaders.indexOf(COLUMN_NAMES.LAST_CHECK);
  const resultIdx = checkHeaders.indexOf(COLUMN_NAMES.RESULT);
  
  const checkData = checkSheet.getDataRange().getValues();
  for (let i = 1; i < checkData.length; i++) {
    if (checkData[i][checkHeaders.indexOf(COLUMN_NAMES.CHECK)]) {
      checkSheet.getRange(i + 1, lastCheckIdx + 1).setValue(currentTime);
      
      const errorCount = Object.values(results).reduce((sum, r) => 
        sum + (r.errors ? r.errors.length : 0), 0
      );
      
      const message = errorCount === 0 ? '✅ OK' : 
        `❌ エラー (${Object.entries(results)
          .filter(([_, r]) => r.errors && r.errors.length > 0)
          .map(([name, r]) => `${name}: ${r.errors.length}件`)
          .join(', ')})`;
      
      checkSheet.getRange(i + 1, resultIdx + 1).setValue(message);
    }
  }
}

/**
 * エラー結果を更新
 * @param {Sheet} checkSheet - チェック管理シート
 * @param {Array} checkHeaders - ヘッダー
 * @param {string} errorMessage - エラーメッセージ
 */
function updateErrorResults(checkSheet, checkHeaders, errorMessage) {
  const currentTime = new Date();
  const lastCheckIdx = checkHeaders.indexOf(COLUMN_NAMES.LAST_CHECK);
  const resultIdx = checkHeaders.indexOf(COLUMN_NAMES.RESULT);
  
  const checkData = checkSheet.getDataRange().getValues();
  for (let i = 1; i < checkData.length; i++) {
    if (checkData[i][checkHeaders.indexOf(COLUMN_NAMES.CHECK)]) {
      checkSheet.getRange(i + 1, lastCheckIdx + 1).setValue(currentTime);
      checkSheet.getRange(i + 1, resultIdx + 1).setValue(`❌ エラー: ${errorMessage}`);
    }
  }
}

// ==================== メニュー関数 ====================

/**
 * メニュー：データ生成のみ（元からメニュー未登録。必要なら onOpen に追記してください）
 * 生成後に0件なら診断ダイアログを自動表示（NEW）
 */
function menuGenerateOnly() {
  const startTime = new Date();
  
  try {
    const ss = SpreadsheetApp.getActiveSpreadsheet();
    const targetSheets = getCheckTargetSheets(ss);
    
    if (targetSheets.length === 0) {
      throw new Error('チェック対象の設計書がありません');
    }
    
    const masterGroups = groupMappingsByMaster(targetSheets);
    generateMasterSheets(ss, masterGroups);

    // 追加：生成直後に診断（0件なら原因を表示）
    const diag = diagnoseWriteFailure(ss, { context: 'generateOnly' });
    if (diag.totalWritten === 0) {
      showDiagnosisDialog(diag, startTime);
    } else {
      showResultDialog('データ生成完了', '設計書からのデータ生成が完了しました。', [], startTime);
    }
  } catch (error) {
    showResultDialog('エラー', 'データ生成中にエラーが発生しました。', [error.message], startTime);
  }
}

/**
 * メニュー：チェックのみ
 */
function menuCheckOnly() {
  const startTime = new Date();
  const checkResults = checkGeneratedDataAgainstMaster();
  showCheckResultDialog('チェック完了', checkResults, startTime);
}

/**
 * メニュー：生成＋チェック
 * 生成後に0件なら診断ダイアログも表示（NEW）
 */
function menuGenerateAndCheck() {
  const startTime = new Date();
  
  try {
    const ss = SpreadsheetApp.getActiveSpreadsheet();

    const checkResults = convertDesignDocsToMasterFormat();
    showCheckResultDialog('生成＋チェック完了', checkResults, startTime);

    // 追加：生成結果が0件なら、チェックダイアログとは別に原因を表示
    const diag = diagnoseWriteFailure(ss, { context: 'generateAndCheck' });
    if (diag.totalWritten === 0) {
      showDiagnosisDialog(diag, startTime);
    }
  } catch (error) {
    showResultDialog('エラー', '処理中にエラーが発生しました。', [error.message], startTime);
  }
}

/**
 * メニュー：ダイレクトチェック
 */
function menuDirectCheck() {
  const startTime = new Date();
  const checkResults = performDirectCheck();
  showCheckResultDialog('ダイレクトチェック完了', checkResults, startTime);
}

/**
 * メニュー：生成データの更新
 * 更新後に0件なら診断ダイアログを表示（NEW）
 */
function menuUpdateGeneratedData() {
  const startTime = new Date();
  
  try {
    const ss = SpreadsheetApp.getActiveSpreadsheet();
    const targetSheets = getCheckTargetSheets(ss);
    
    if (targetSheets.length === 0) {
      throw new Error('チェック対象の設計書がありません');
    }
    
    const masterGroups = groupMappingsByMaster(targetSheets);
    updateMasterSheets(ss, masterGroups);

    // 追加：更新後に診断（全体0件なら原因表示）
    const diag = diagnoseWriteFailure(ss, { context: 'updateGeneratedData' });
    if (diag.totalWritten === 0) {
      showDiagnosisDialog(diag, startTime);
    } else {
      showResultDialog('データ更新完了', '生成データの更新が完了しました。', [], startTime);
    }
  } catch (error) {
    showResultDialog('エラー', 'データ更新中にエラーが発生しました。', [error.message], startTime);
  }
}

/**
 * メニュー：生成データの削除
 */
function menuDeleteGeneratedData() {
  const ui = SpreadsheetApp.getUi();
  const response = ui.alert(
    '確認',
    '生成されたマスターデータをすべて削除しますか？この操作は元に戻せません。',
    ui.ButtonSet.YES_NO
  );
  
  if (response === ui.Button.YES) {
    const startTime = new Date();
    
    try {
      const ss = SpreadsheetApp.getActiveSpreadsheet();
      const targetSheets = getCheckTargetSheets(ss);
      const masterGroups = groupMappingsByMaster(targetSheets);
      
      let deletedCount = 0;
      Object.keys(masterGroups).forEach(masterName => {
        const sheet = ss.getSheetByName(masterName);
        if (sheet) {
          ss.deleteSheet(sheet);
          deletedCount++;
          console.log(`🗑️ ${masterName}シートを削除しました`);
        }
      });
      
      showResultDialog('削除完了', `${deletedCount}個のマスターシートを削除しました。`, [], startTime);
    } catch (error) {
      showResultDialog('エラー', 'データ削除中にエラーが発生しました。', [error.message], startTime);
    }
  }
}

/**
 * メニュー：マスターデータ上書き
 */
function menuOverwriteMasterData() {
  const startTime = new Date();
  
  try {
    const ss = SpreadsheetApp.getActiveSpreadsheet();
    const config = getMasterDataConfig();
    const checkData = getCheckManagementData(ss);
    const masterUrl = getMasterUrl(checkData.data, checkData.headers);
    
    if (!masterUrl) {
      throw new Error('マスターURLが設定されていません');
    }
    
    const masterSS = SpreadsheetApp.openByUrl(masterUrl);
    const overwriteCandidates = collectOverwriteCandidates(ss, masterSS, config);
    
    if (overwriteCandidates.length === 0) {
      showResultDialog('情報', 'ユニークキーを使用した上書き可能な差分はありませんでした。', [], startTime);
      return;
    }
    
    showOverwriteSelectionDialog(overwriteCandidates, masterSS, config, startTime);
    
  } catch (error) {
    showResultDialog('エラー', 'マスターデータ上書き処理中にエラーが発生しました。', [error.message], startTime);
  }
}

// ==================== UI関数 ====================

/**
 * 結果ダイアログを表示
 * @param {string} title - タイトル
 * @param {string} message - メッセージ
 * @param {Array} errors - エラー
 * @param {Date} startTime - 開始時刻
 */
function showResultDialog(title, message, errors, startTime) {
  const duration = ((new Date() - startTime) / 1000).toFixed(2);
  
  let htmlContent = `
    <div style="font-family: Arial, sans-serif; padding: 20px;">
      <h2 style="color: #333;">${title}</h2>
      <p style="color: #666; margin-bottom: 20px;">${message}</p>
      <p style="color: #999; font-size: 14px;">処理時間: ${duration}秒</p>`;
  
  if (errors.length > 0) {
    htmlContent += `
      <h3 style="color: #d9534f; margin-top: 20px;">エラー詳細:</h3>
      <ul style="color: #d9534f;">`;
    errors.forEach(error => {
      htmlContent += `<li>${error}</li>`;
    });
    htmlContent += '</ul>';
  }
  
  htmlContent += '</div>';
  
  const htmlOutput = HtmlService.createHtmlOutput(htmlContent)
    .setWidth(500)
    .setHeight(400);
  
  SpreadsheetApp.getUi().showModalDialog(htmlOutput, title);
}

/**
 * チェック結果ダイアログを表示
 * @param {string} title - タイトル
 * @param {Object} checkResults - チェック結果
 * @param {Date} startTime - 開始時刻
 */
function showCheckResultDialog(title, checkResults, startTime) {
  const duration = ((new Date() - startTime) / 1000).toFixed(2);
  
  const reportData = [];
  let totalErrors = 0;
  
  Object.entries(checkResults).forEach(([sheetName, result]) => {
    if (result && result.errors) {
      totalErrors += result.errors.length;
      result.errors.forEach(error => {
        reportData.push({
          sheet: sheetName,
          status: 'エラー',
          detail: error
        });
      });
    } else if (result) {
      reportData.push({
        sheet: sheetName,
        status: 'OK',
        detail: '全データ一致'
      });
    }
  });
  
  const htmlContent = createCheckResultHTML(title, totalErrors, checkResults, reportData, duration);
  
  const htmlOutput = HtmlService.createHtmlOutput(htmlContent)
    .setWidth(600)
    .setHeight(500);
  
  SpreadsheetApp.getUi().showModalDialog(htmlOutput, title);
}

/**
 * チェック結果HTMLを生成
 */
function createCheckResultHTML(title, totalErrors, checkResults, reportData, duration) {
  return '<!DOCTYPE html>' +
    '<html>' +
    '<head>' +
      '<base target="_top">' +
      '<style>' +
        'body { font-family: Arial, sans-serif; padding: 20px; background-color: #f5f5f5; }' +
        '.container { background-color: white; border-radius: 8px; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }' +
        'h2 { color: #333; margin-bottom: 12px; }' +
        '.summary { ' + 
          'background-color: ' + (totalErrors === 0 ? '#d4edda' : '#f8d7da') + '; ' +
          'color: ' + (totalErrors === 0 ? '#155724' : '#721c24') + '; ' + 
          'padding: 12px; border-radius: 6px; margin-bottom: 16px; ' +
        '}' +
        '.meta { color: #999; font-size: 12px; margin-top: 8px; }' +
        '.controls { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; margin-bottom: 12px; }' +
        '.controls input[type="text"] { padding: 6px 8px; border: 1px solid #ddd; border-radius: 4px; min-width: 220px; }' +
        '.controls label { color: #555; font-size: 13px; }' +
        'button { ' +
          'background-color: #007bff; color: white; border: none; ' +
          'padding: 8px 12px; border-radius: 4px; cursor: pointer; ' +
        '}' +
        'button:hover { background-color: #0056b3; }' +
        '.secondary { background-color: #6c757d; }' +
        '.secondary:hover { background-color: #545b62; }' +
        '.ghost { background-color: transparent; color: #007bff; border: 1px solid #007bff; }' +
        '.ghost:hover { background-color: #e9f2ff; }' +
        '.badges { display: flex; gap: 6px; flex-wrap: wrap; margin: 8px 0; }' +
        '.badge { display: inline-block; padding: 2px 8px; font-size: 12px; border-radius: 999px; background: #eee; color: #555; }' +
        '.badge-ok { background: #d4edda; color: #155724; }' +
        '.badge-error { background: #f8d7da; color: #721c24; }' +
        '.results { margin-top: 8px; }' +
        '.sheet-card { background: #fff; border: 1px solid #eee; border-radius: 6px; margin-bottom: 8px; overflow: hidden; }' +
        '.sheet-header { display: flex; justify-content: space-between; align-items: center; padding: 10px 12px; cursor: pointer; background: #fafafa; }' +
        '.sheet-header:hover { background: #f3f7ff; }' +
        '.sheet-title { font-weight: bold; color: #007bff; }' +
        '.sheet-counts { color: #666; font-size: 12px; }' +
        '.status-ok { color: #28a745; font-weight: 600; }' +
        '.status-error { color: #dc3545; font-weight: 600; }' +
        '.details { display: none; padding: 10px 12px; border-top: 1px solid #eee; }' +
        '.error-list { margin: 0; padding-left: 18px; }' +
        '.error-list li { margin: 4px 0; color: #dc3545; }' +
        '.empty { color: #999; font-style: italic; }' +
        '.toolbar { display: flex; gap: 8px; margin-top: 10px; }' +
      '</style>' +
    '</head>' +
    '<body>' +
      '<div class="container">' +
        '<h2>' + title + '</h2>' +
        '<div class="summary" id="summaryBar"></div>' +
        '<div class="controls">' +
          '<label><input type="checkbox" id="onlyErrors"> エラーのみ表示</label>' +
          '<input type="text" id="searchBox" placeholder="キーワードで絞り込み (シート名/エラー内容)">' +
          '<div class="toolbar">' +
            '<button class="ghost" id="expandAll">すべて展開</button>' +
            '<button class="ghost" id="collapseAll">すべて折りたたみ</button>' +
            '<button id="downloadCsv">CSVダウンロード</button>' +
            '<button class="secondary" id="downloadJson">JSONダウンロード</button>' +
            '<button class="secondary" id="copyText">テキストをコピー</button>' +
          '</div>' +
        '</div>' +
        '<div class="badges" id="badgeBar"></div>' +
        '<div class="results" id="results"></div>' +
        '<div class="meta">処理時間: ' + duration + '秒</div>' +
      '</div>' +
      '<script>' +
        'const rawResults = ' + JSON.stringify(checkResults) + ';' +
        'const reportData = ' + JSON.stringify(reportData) + ';' +

        'function summarize(results) {' +
          'let sheets = 0, errorSheets = 0, okSheets = 0, totalErrs = 0;' +
          'for (const key in results) {' +
            'const r = results[key] || {};' +
            'const errs = (r.errors || []).length;' +
            'sheets++;' +
            'totalErrs += errs;' +
            'if (errs > 0) errorSheets++; else okSheets++;' +
          '}' +
          'return { sheets, errorSheets, okSheets, totalErrs };' +
        '}' +

        'function renderSummary() {' +
          'const s = summarize(rawResults);' +
          'const bar = document.getElementById("summaryBar");' +
          'const okText = s.errorSheets === 0 ? "すべてのチェックが正常に完了しました！" : s.totalErrs + "件のエラーが見つかりました";' +
          'bar.textContent = okText + " （対象シート: " + s.sheets + "、OK: " + s.okSheets + "、NG: " + s.errorSheets + "）";' +

          'const badge = document.getElementById("badgeBar");' +
          'badge.innerHTML = ' +
            '"<span class=\\"badge\\">対象: " + s.sheets + "</span>" +' +
            '"<span class=\\"badge badge-ok\\">OK: " + s.okSheets + "</span>" +' +
            '"<span class=\\"badge badge-error\\">NG: " + s.errorSheets + "</span>" +' +
            '"<span class=\\"badge\\">エラー合計: " + s.totalErrs + "</span>";' +
        '}' +

        'function cardHTML(sheetName, result) {' +
          'const errs = result.errors || [];' +
          'const statusClass = errs.length > 0 ? "status-error" : "status-ok";' +
          'const statusText = errs.length > 0 ? errs.length + "件のエラー" : "OK";' +
          'const counts = [];' +
          'if (typeof result.generatedCount !== "undefined") counts.push("生成: " + result.generatedCount + "件");' +
          'if (typeof result.masterCount !== "undefined") counts.push("マスター: " + result.masterCount + "件");' +
          'return "<div class=\\"sheet-card\\" data-has-errors=\\"" + (errs.length > 0) + "\\" data-name=\\"" + sheetName + "\\">" +' +
            '"<div class=\\"sheet-header\\" onclick=\\"toggleDetails(this)\\">" +' +
              '"<div class=\\"sheet-title\\">" + sheetName + "</div>" +' +
              '"<div>" +' +
                '"<span class=\\"" + statusClass + "\\">" + statusText + "</span>" +' +
                '(counts.length ? "<span class=\\"sheet-counts\\">（" + counts.join(", ") + "）</span>" : "") +' +
              '"</div>" +' +
            '"</div>" +' +
            '"<div class=\\"details\\">" +' +
              '(errs.length === 0' +
                '? "<div class=\\"empty\\">全データ一致</div>"' +
                ': "<ul class=\\"error-list\\">" + errs.map(function(e) { return "<li>" + escapeHtml(e) + "</li>"; }).join("") + "</ul>") +' +
            '"</div>" +' +
          '"</div>";' +
        '}' +

        'function escapeHtml(s) {' +
          'return String(s).replace(/[&<>\\"]/g, function(c) { ' +
            'return {"&":"&amp;","<":"&lt;",">":"&gt;","\\"":"&quot;"}[c];' +
          '});' +
        '}' +

        'function render() {' +
          'const onlyErrors = document.getElementById("onlyErrors").checked;' +
          'const q = document.getElementById("searchBox").value.trim().toLowerCase();' +
          'const container = document.getElementById("results");' +
          'container.innerHTML = "";' +
          'Object.entries(rawResults).forEach(function(entry) {' +
            'const name = entry[0];' +
            'const res = entry[1];' +
            'const errs = (res.errors || []).map(String);' +
            'const hasErr = errs.length > 0;' +
            'if (onlyErrors && !hasErr) return;' +
            'const haystack = (name + " " + errs.join(" ")).toLowerCase();' +
            'if (q && !haystack.includes(q)) return;' +
            'container.insertAdjacentHTML("beforeend", cardHTML(name, res));' +
          '});' +
        '}' +

        'function toggleDetails(headerEl) {' +
          'const details = headerEl.nextElementSibling;' +
          'details.style.display = details.style.display === "block" ? "none" : "block";' +
        '}' +

        'function expandAll() {' +
          'document.querySelectorAll(".details").forEach(function(d) { d.style.display = "block"; });' +
        '}' +
        'function collapseAll() {' +
          'document.querySelectorAll(".details").forEach(function(d) { d.style.display = "none"; });' +
        '}' +

        'function downloadCSV() {' +
          'let csv = "シート名,ステータス,詳細\\n";' +
          'reportData.forEach(function(row) {' +
            'csv += "\\"" + row.sheet + "\\",\\"" + row.status + "\\",\\"" + row.detail.replace(/"/g, "\\"\\"") + "\\"\\n";' +
          '});' +
          'const blob = new Blob([csv], { type: "text/csv;charset=utf-8;" });' +
          'const link = document.createElement("a");' +
          'link.href = URL.createObjectURL(blob);' +
          'link.download = "check_report_" + new Date().toISOString().slice(0,10) + ".csv";' +
          'link.click();' +
        '}' +

        'function downloadJSON() {' +
          'const blob = new Blob([JSON.stringify(rawResults, null, 2)], { type: "application/json" });' +
          'const link = document.createElement("a");' +
          'link.href = URL.createObjectURL(blob);' +
          'link.download = "check_report_" + new Date().toISOString().slice(0,10) + ".json";' +
          'link.click();' +
        '}' +

        'function copyText() {' +
          'const lines = [];' +
          'Object.entries(rawResults).forEach(function(entry) {' +
            'const name = entry[0];' +
            'const res = entry[1];' +
            'const errs = res.errors || [];' +
            'if (errs.length === 0) {' +
              'lines.push(name + ": OK");' +
            '} else {' +
              'lines.push(name + ": " + errs.length + "件のエラー");' +
              'errs.forEach(function(e) { lines.push("  - " + e); });' +
            '}' +
          '});' +
          'const text = lines.join("\\n");' +
          'navigator.clipboard.writeText(text).then(function() {' +
            'alert("コピーしました");' +
          '}, function() {' +
            'alert("コピーに失敗しました");' +
          '});' +
        '}' +

        'function bindUI() {' +
          'document.getElementById("onlyErrors").addEventListener("change", render);' +
          'document.getElementById("searchBox").addEventListener("input", render);' +
          'document.getElementById("expandAll").addEventListener("click", expandAll);' +
          'document.getElementById("collapseAll").addEventListener("click", collapseAll);' +
          'document.getElementById("downloadCsv").addEventListener("click", downloadCSV);' +
          'document.getElementById("downloadJson").addEventListener("click", downloadJSON);' +
          'document.getElementById("copyText").addEventListener("click", copyText);' +
        '}' +

        '(function init(){' +
          'renderSummary();' +
          'bindUI();' +
          'render();' +
        '})();' +
      '</script>' +
    '</body>' +
    '</html>';
}

// ==================== 追加のヘルパー関数（ダイレクトチェック用） ====================

/**
 * 設計書とマッピングのペアを取得
 * @param {Array} checkData - チェックデータ
 * @param {Array} checkHeaders - ヘッダー
 * @returns {Array} ペアの配列
 */
function getDesignMappingPairs(checkData, checkHeaders) {
  const pairs = [];
  const checkIdx = checkHeaders.indexOf(COLUMN_NAMES.CHECK);
  const sheetIdx = checkHeaders.indexOf(COLUMN_NAMES.SHEET_NAME);
  const mappingIdx = checkHeaders.indexOf(COLUMN_NAMES.MAPPING);
  
  for (let i = 1; i < checkData.length; i++) {
    if (checkData[i][checkIdx]) {
      pairs.push({
        rowIndex: i,
        designSheet: checkData[i][sheetIdx],
        mappingSheet: checkData[i][mappingIdx]
      });
    }
  }
  
  return pairs;
}

/**
 * 設計書をマスター名でグループ化
 * @param {Spreadsheet} ss - スプレッドシート
 * @param {Array} designPairs - 設計書ペア
 * @returns {Object} マスターグループ
 */
function groupDesignsByMaster(ss, designPairs) {
  const masterGroups = {};
  
  designPairs.forEach(pair => {
    const mappingSheet = ss.getSheetByName(pair.mappingSheet);
    if (!mappingSheet) return;
    
    const mappingData = mappingSheet.getDataRange().getValues();
    const mappings = parseMappingData(mappingData);
    
    mappings.forEach(mapping => {
      if (!masterGroups[mapping.masterName]) {
        masterGroups[mapping.masterName] = {
          mappings: [],
          designSheets: []
        };
      }
      
      const exists = masterGroups[mapping.masterName].mappings.some(m => 
        m.masterColumn === mapping.masterColumn && m.checkIgnore === mapping.checkIgnore
      );
      
      if (!exists) {
        masterGroups[mapping.masterName].mappings.push(mapping);
      }
      
      if (!masterGroups[mapping.masterName].designSheets.includes(pair.designSheet)) {
        masterGroups[mapping.masterName].designSheets.push(pair.designSheet);
      }
    });
  });
  
  return masterGroups;
}

/**
 * ダイレクトレコードを収集
 * @param {Spreadsheet} ss - スプレッドシート
 * @param {string} masterName - マスター名
 * @param {Object} groupData - グループデータ
 * @returns {Array} レコード配列
 */
function collectDirectRecords(ss, masterName, groupData) {
  const records = [];
  
  groupData.designSheets.forEach(sheetName => {
    const designSheet = ss.getSheetByName(sheetName);
    if (designSheet) {
      const mappingSheet = getMappingSheetForDesign(sheetName);
      const mappingData = mappingSheet.getDataRange().getValues();
      const mappings = parseMappingData(mappingData);
      const masterMappings = mappings.filter(m => m.masterName === masterName);
      
      const sheetRecords = extractRecordsFromDesignSheet(designSheet, masterMappings);
      records.push(...sheetRecords);
    }
  });
  
  return records;
}

/**
 * エラー結果を作成
 * @param {string} masterName - マスター名
 * @param {number} recordCount - レコード数
 * @returns {Object} エラー結果
 */
function createErrorResult(masterName, recordCount) {
  return {
    message: `❌ マスターシート「${masterName}」が見つかりません`,
    errors: [`マスターシート「${masterName}」が見つかりません`],
    generatedCount: recordCount,
    masterCount: 0
  };
}

/**
 * ダイレクトチェック結果を更新
 * @param {Spreadsheet} ss - スプレッドシート
 * @param {Array} designPairs - 設計書ペア
 * @param {Array} allErrors - 全エラー
 */
function updateDirectCheckResults(ss, designPairs, allErrors) {
  const checkSheet = ss.getSheetByName(SHEET_NAMES.CHECK_MANAGEMENT);
  const checkHeaders = checkSheet.getDataRange().getValues()[0];
  const lastCheckIdx = checkHeaders.indexOf(COLUMN_NAMES.LAST_CHECK);
  const resultIdx = checkHeaders.indexOf(COLUMN_NAMES.RESULT);
  const currentTime = new Date();
  
  designPairs.forEach(pair => {
    checkSheet.getRange(pair.rowIndex + 1, lastCheckIdx + 1).setValue(currentTime);
    
    const message = allErrors.length === 0 ? '✅ OK' : 
      `❌ エラー (${allErrors.map(e => `${e.masterName}: ${e.errorCount}件`).join(', ')})`;
    
    checkSheet.getRange(pair.rowIndex + 1, resultIdx + 1).setValue(message);
  });
}

/**
 * エラーをチェックシートに更新
 * @param {Spreadsheet} ss - スプレッドシート
 * @param {string} errorMessage - エラーメッセージ
 */
function updateErrorInCheckSheet(ss, errorMessage) {
  const checkData = getCheckManagementData(ss);
  const lastCheckIdx = checkData.headers.indexOf(COLUMN_NAMES.LAST_CHECK);
  const resultIdx = checkData.headers.indexOf(COLUMN_NAMES.RESULT);
  const currentTime = new Date();
  
  for (let i = 1; i < checkData.data.length; i++) {
    if (checkData.data[i][checkData.headers.indexOf(COLUMN_NAMES.CHECK)]) {
      checkData.sheet.getRange(i + 1, lastCheckIdx + 1).setValue(currentTime);
      checkData.sheet.getRange(i + 1, resultIdx + 1).setValue(`❌ エラー: ${errorMessage}`);
    }
  }
}

/**
 * 対象マスターシートを取得
 * @param {Array} checkData - チェックデータ
 * @param {Array} checkHeaders - ヘッダー
 * @returns {Set} マスターシート名のセット
 */
function getTargetMasterSheets(checkData, checkHeaders) {
  const sheets = new Set();
  const checkIdx = checkHeaders.indexOf(COLUMN_NAMES.CHECK);
  const sheetIdx = checkHeaders.indexOf(COLUMN_NAMES.SHEET_NAME);
  
  for (let i = 1; i < checkData.length; i++) {
    if (checkData[i][checkIdx]) {
      const mappingSheet = getMappingSheetForDesign(checkData[i][sheetIdx]);
      if (mappingSheet) {
        const mappingData = mappingSheet.getDataRange().getValues();
        const mappings = parseMappingData(mappingData);
        mappings.forEach(m => sheets.add(m.masterName));
      }
    }
  }
  
  return sheets;
}

/**
 * 比較を実行
 * @param {Spreadsheet} ss - スプレッドシート
 * @param {Spreadsheet} masterSS - マスタースプレッドシート
 * @param {Set} targetSheets - 対象シート
 * @param {Object} config - 設定
 * @returns {Object} 比較結果
 */
function performComparison(ss, masterSS, targetSheets, config) {
  const results = {};
  
  Array.from(targetSheets).forEach(sheetName => {
    console.log(`📊 【${sheetName}】シートのチェックを開始...`);
    
    try {
      const masterSheet = masterSS.getSheetByName(sheetName);
      if (!masterSheet) {
        throw new Error(`マスターシート「${sheetName}」が見つかりません`);
      }
      
      const generatedSheet = ss.getSheetByName(sheetName);
      if (!generatedSheet) {
        throw new Error(`生成されたシート「${sheetName}」が見つかりません`);
      }
      
      const result = compareData(generatedSheet, masterSheet, sheetName, config);
      results[sheetName] = {
        message: result.message,
        errors: result.errors,
        generatedCount: generatedSheet.getLastRow() - 1,
        masterCount: masterSheet.getLastRow() - (config[sheetName]?.startRow || 1)
      };
      
      if (result.errors.length > 0) {
        console.log(`  ❌ エラー: ${result.errors.length}件の不一致`);
      } else {
        console.log(`  ✅ 全てのデータが一致しました`);
      }
      
    } catch (error) {
      console.error(`  ❌ エラー: ${error.message}`);
      results[sheetName] = {
        message: `❌ エラー: ${error.message}`,
        errors: [error.message],
        generatedCount: 0,
        masterCount: 0
      };
    }
  });
  
  return results;
}

// ==================== 診断ユーティリティ（NEW） ====================

/**
 * 書き出し失敗（または0件）時の原因を推定して収集
 * @param {Spreadsheet} ss
 * @param {Object} options { context: string }
 * @returns {Object} diagnosis
 */
function diagnoseWriteFailure(ss, options = {}) {
  const diagnosis = {
    context: options.context || '',
    reasons: [],
    perMaster: {},
    targetSheets: [],
    masterNames: [],
    totalWritten: 0
  };

  // チェック管理シート確認
  const checkSheet = ss.getSheetByName(SHEET_NAMES.CHECK_MANAGEMENT);
  if (!checkSheet) {
    diagnosis.reasons.push({
      code: 'CFG-001',
      message: 'チェック対象管理シートが見つかりません。',
      hint: 'シート名「チェック対象管理」を作成してください。'
    });
    diagnosis.ok = false;
    return diagnosis;
  }

  // 対象設計書
  const targetSheets = getCheckTargetSheets(ss) || [];
  diagnosis.targetSheets = targetSheets.slice();
  if (targetSheets.length === 0) {
    diagnosis.reasons.push({
      code: 'CFG-002',
      message: 'チェック列がONの設計書が0件です。',
      hint: '「チェック対象管理」シートの「チェック」列をTRUEにしてください。また、「チェック対象管理」の列名が正しく入力されている事を確認してください。'
    });
  }

  // マスター候補
  const masterGroups = groupMappingsByMaster(targetSheets);
  const masterNames = Object.keys(masterGroups);
  diagnosis.masterNames = masterNames.slice();
  if (masterNames.length === 0) {
    diagnosis.reasons.push({
      code: 'MAP-001',
      message: 'マッピングからマスター名が1つも取得できませんでした。',
      hint: 'マッピング設定の「マスター名」「マスター列名」を確認してください。'
    });
  }

  // 設計書↔マッピングの妥当性チェック
  const seen = new Set();
  targetSheets.forEach(designName => {
    const designSheet = ss.getSheetByName(designName);
    if (!designSheet) {
      diagnosis.reasons.push({
        code: 'DATA-001',
        message: `設計書シート「${designName}」が見つかりません。`,
        hint: 'シート名の綴りや存在を確認してください。',
        where: designName
      });
      return;
    }

    const mappingSheet = getMappingSheetForDesign(designName);
    if (!mappingSheet) {
      diagnosis.reasons.push({
        code: 'MAP-002',
        message: `設計書「${designName}」に紐づくマッピングシートが見つかりません。`,
        hint: '「チェック対象管理」のマッピング列を確認、またはデフォルト/代替シートを用意してください。',
        where: designName
      });
      return;
    }

    const mappingData = mappingSheet.getDataRange().getValues();
    const mappings = parseMappingData(mappingData);
    if (!mappings || mappings.length === 0) {
      diagnosis.reasons.push({
        code: 'MAP-003',
        message: `マッピングが0件です（${mappingSheet.getName()}）。`,
        hint: 'マッピング行に「項目名」または「チェック無視(true)」等が入力されているか確認してください。',
        where: mappingSheet.getName()
      });
      return;
    }

    // 項目検索/範囲検証
    mappings.forEach(m => {
      if (!m.masterName) return;

      // 方向抽出：項目名が見つからない
      if (m.direction && m.itemName) {
        const pos = findItemInSheet(designSheet, m.itemName);
        if (!pos) {
          const key = `DATA-002|${designName}|${m.itemName}`;
          if (!seen.has(key)) {
            diagnosis.reasons.push({
              code: 'DATA-002',
              message: `項目名「${m.itemName}」が見つかりません（${designName}）。`,
              hint: '全角/半角、余白、表記揺れ（▶/▶︎）を確認してください。',
              where: designName,
              master: m.masterName
            });
            seen.add(key);
          }
        }
      }

      // 範囲抽出：A1形式の妥当性
      if (m.designRange) {
        const rng = normalizeRange(String(m.designRange));
        try {
          designSheet.getRange(rng); // 取得できなければ例外
        } catch (_e) {
          const key = `RNG-001|${designName}|${rng}`;
          if (!seen.has(key)) {
            diagnosis.reasons.push({
              code: 'RNG-001',
              message: `範囲指定が不正です「${rng}」（${designName}）。`,
              hint: 'A1記法（例: B3:B50 や C5:G5）にしてください。',
              where: designName,
              master: m.masterName
            });
            seen.add(key);
          }
        }
      }
    });
  });

  // 実際の書き出し状況（各マスターのシート行数≒ヘッダー除く件数）を確認
  const counts = countGeneratedRowsByMaster(ss, masterNames);
  diagnosis.perMaster = counts.byMaster;
  diagnosis.totalWritten = counts.total;

  // レコード0件のマスターに対して追加の推定
  masterNames.forEach(masterName => {
    const groupData = masterGroups[masterName];
    if (!groupData) return;

    // 0件だったら、抽出実行して「なぜ0か」を推定（ignoreBlankで全落ち等）
    const result = collectRecordsForMaster(ss, masterName, groupData);
    const hasIgnoreBlank = (groupData.mappings || []).some(m => m.ignoreBlank === true);

    if ((diagnosis.perMaster[masterName] || 0) === 0 && result.records.length === 0) {
      diagnosis.reasons.push({
        code: 'DATA-010',
        message: `マスター「${masterName}」に書き出すレコードが0件でした。`,
        hint: hasIgnoreBlank
          ? 'ignoreBlank=true の列が空のため全レコードが除外された可能性があります。データ行に値が入っているか確認してください。'
          : '項目未検出、または範囲が空かもしれません。項目名と範囲指定を見直してください。',
        master: masterName
      });
    }
  });

  // マスターURL（比較/上書き系で必要）
  const checkData = getCheckManagementData(ss);
  const masterUrl = getMasterUrl(checkData.data, checkData.headers);
  if (!masterUrl) {
    diagnosis.reasons.push({
      code: 'CFG-010',
      message: 'マスターURLが設定されていません。',
      hint: '「チェック対象管理」シートの「マスターURL」列にURLを設定してください。'
    });
  }

  diagnosis.ok = diagnosis.totalWritten > 0 || diagnosis.reasons.length === 0;
  return diagnosis;
}

/**
 * マスター別の書き出し件数を数える（ヘッダー1行を除外）
 * @param {Spreadsheet} ss
 * @param {Array<string>} masterNames
 * @returns {{byMaster: Object, total: number}}
 */
function countGeneratedRowsByMaster(ss, masterNames) {
  const byMaster = {};
  let total = 0;
  (masterNames || []).forEach(name => {
    const s = ss.getSheetByName(name);
    const count = s ? Math.max(0, s.getLastRow() - 1) : 0;
    byMaster[name] = count;
    total += count;
  });
  return { byMaster, total };
}

/**
 * 推定原因ダイアログを表示
 * @param {Object} diagnosis
 * @param {Date} startTime
 */
function showDiagnosisDialog(diagnosis, startTime) {
  const duration = ((new Date() - (startTime || new Date())) / 1000).toFixed(2);
  const zeroMasters = (diagnosis.masterNames || []).filter(n => (diagnosis.perMaster[n] || 0) === 0);

  const rowsHtml = (diagnosis.masterNames || []).map(n => {
    const c = diagnosis.perMaster[n] || 0;
    return `<tr><td>${n}</td><td style="text-align:right;">${c}</td></tr>`;
  }).join('');

  const reasonsHtml = (diagnosis.reasons || []).map(r => {
    const where = r.where ? `（場所: ${r.where}）` : '';
    const master = r.master ? ` / マスター: ${r.master}` : '';
    return `<li><code>${r.code}</code> ${r.message}${where}${master ? master : ''}<br><span style="color:#666;">ヒント: ${r.hint || '-'}</span></li>`;
  }).join('');

  const html = `
  <div style="font-family: Arial, sans-serif; padding: 20px;">
    <h2 style="margin:0 0 8px;">書き出し結果の診断</h2>
    <div style="background:${diagnosis.totalWritten>0 ? '#d4edda':'#f8d7da'}; color:${diagnosis.totalWritten>0 ? '#155724':'#721c24'}; padding:10px 12px; border-radius:6px; margin-bottom:12px;">
      ${diagnosis.totalWritten > 0
        ? `一部は書き出されています（合計 ${diagnosis.totalWritten} 件）。`
        : '書き出し件数が 0 件でした。推定原因を確認してください。'}
    </div>

    <div style="margin-bottom:12px; color:#666; font-size:12px;">
      対象設計書: ${diagnosis.targetSheets.length} / マスター候補: ${diagnosis.masterNames.length} / 処理時間: ${duration}秒
    </div>

    <h3 style="margin:12px 0 6px;">マスター別の状況</h3>
    <table style="width:100%; border-collapse:collapse;">
      <thead>
        <tr>
          <th style="text-align:left; border-bottom:1px solid #eee; padding:6px 0;">マスター名</th>
          <th style="text-align:right; border-bottom:1px solid #eee; padding:6px 0;">書き出し件数</th>
        </tr>
      </thead>
      <tbody>${rowsHtml || '<tr><td colspan="2" style="color:#999;">対象なし</td></tr>'}</tbody>
    </table>

    <h3 style="margin:16px 0 6px;">原因候補（推定）</h3>
    <ul style="padding-left:18px; margin:0;">
      ${reasonsHtml || '<li style="color:#999;">特に問題は検出されませんでした。</li>'}
    </ul>

    ${zeroMasters.length ? `<div style="margin-top:12px; color:#dc3545;">書き出し0件のマスター: ${zeroMasters.join(', ')}</div>` : ''}

    <div style="margin-top:16px; display:flex; gap:8px; flex-wrap:wrap;">
      <button onclick="google.script.host.close()" style="background:#007bff; color:#fff; border:none; padding:8px 12px; border-radius:4px; cursor:pointer;">閉じる</button>
    </div>
  </div>`;

  const out = HtmlService.createHtmlOutput(html).setWidth(640).setHeight(520);
  SpreadsheetApp.getUi().showModalDialog(out, '書き出し診断');
}

/**
 * 🩺 事前診断メニュー（書き出しは行わずに詰まりポイントを表示）
 */
function menuPreflightDiagnose() {
  const startTime = new Date();
  try {
    const ss = SpreadsheetApp.getActiveSpreadsheet();
    const diag = diagnoseWriteFailure(ss, { context: 'preflight' });
    showDiagnosisDialog(diag, startTime);
  } catch (e) {
    showResultDialog('エラー', '事前診断中にエラーが発生しました。', [e.message], startTime);
  }
}
