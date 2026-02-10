# ワークフローロジック設計書

## 概要

このドキュメントは、`masterdata-from-bizops-all` 統合スキルのワークフローロジックを詳細に設計します。

## 全体フロー図

```
┌─────────────────────────────────────────────────────────────┐
│ Step 1: 運営仕様書の解析                                      │
│ - ファイル名から機能カテゴリを推定                             │
│ - ファイル内容から該当する機能スキルを特定                     │
│ - 各機能スキルに必要なパラメータを抽出                         │
└─────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────┐
│ Step 2: 必要な機能の特定                                      │
│ - 解析結果から必要な機能スキルを特定                           │
│ - 14個の機能スキルから該当するものを選択                       │
└─────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────┐
│ Step 3: 依存関係の解析                                        │
│ - 各機能スキル間の依存関係を解析                               │
│ - アイテム → 報酬 → ミッション等の依存関係マップを作成         │
└─────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────┐
│ Step 4: 実行順序の決定                                        │
│ - 依存関係を考慮した実行順序を決定                             │
│ - トポロジカルソートで最適な順序を算出                         │
└─────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────┐
│ Step 5: 各機能スキルの順次実行                                │
│ - 決定した順序で各機能スキルを呼び出し                         │
│ - 実行結果（CSV、推測値レポート）を収集                        │
│ - エラーがあれば記録し、次の機能スキルへ                       │
└─────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────┐
│ Step 6: データ整合性の全体チェック                            │
│ - 外部キー整合性チェック                                       │
│ - ID採番の一貫性チェック                                       │
│ - 必須カラムの存在チェック                                     │
└─────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────┐
│ Step 7: 推測値レポートの統合                                  │
│ - 各機能スキルの推測値レポートを収集                           │
│ - 機能別にセクション分け                                       │
│ - 重要度でソート                                               │
└─────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────┐
│ Step 8: 全体レポートの出力                                    │
│ - 実行サマリー                                                 │
│ - 統合推測値レポート                                           │
│ - データ整合性チェック結果                                     │
│ - 次のステップ                                                 │
└─────────────────────────────────────────────────────────────┘
```

## Step 1: 運営仕様書の解析ロジック

### 1.1 ファイル名から機能カテゴリを推定

**ロジック**:
```
def analyze_file_category(filename):
    """
    ファイル名から機能カテゴリを推定する

    Args:
        filename: ファイル名（例: "ガチャ設計書_地獄楽_いいジャン祭.xlsx"）

    Returns:
        category: 機能カテゴリ（例: "gacha"）
    """

    # ファイル名に含まれるキーワードで判定
    keyword_map = {
        "ガチャ": "gacha",
        "gacha": "gacha",
        "ヒーロー": "hero",
        "hero": "hero",
        "キャラ": "hero",
        "character": "hero",
        "ミッション": "mission",
        "mission": "mission",
        "クエスト": "quest-stage",
        "quest": "quest-stage",
        "ステージ": "quest-stage",
        "stage": "quest-stage",
        "アイテム": "item",
        "item": "item",
        "報酬": "reward",
        "reward": "reward",
        "イベント": "event-basic",
        "event": "event-basic",
        "ショップ": "shop-pack",
        "shop": "shop-pack",
        "パック": "shop-pack",
        "pack": "shop-pack",
        "降臨": "advent-battle",
        "advent": "advent-battle",
        "PVP": "pvp",
        "pvp": "pvp",
        "ランクマッチ": "pvp",
        "原画": "artwork",
        "artwork": "artwork",
        "エンブレム": "emblem",
        "emblem": "emblem",
        "敵": "enemy-autoplayer",
        "enemy": "enemy-autoplayer",
        "自動行動": "enemy-autoplayer",
        "autoplayer": "enemy-autoplayer",
        "インゲーム": "ingame",
        "ingame": "ingame",
    }

    for keyword, category in keyword_map.items():
        if keyword in filename:
            return category

    return None  # 判定できない場合
```

### 1.2 ファイル内容から機能スキルを特定

**ロジック**:
```
def identify_skill_from_content(file_content):
    """
    ファイル内容から該当する機能スキルを特定する

    Args:
        file_content: ファイル内容（テキスト、Excel等）

    Returns:
        skill_name: 機能スキル名（例: "masterdata-from-bizops-gacha"）
    """

    # 内容に含まれるキーワードで判定（ファイル名で判定できない場合の補助）
    content_keyword_map = {
        "OprGacha": "gacha",
        "MstUnit": "hero",
        "MstMissionEvent": "mission",
        "MstQuest": "quest-stage",
        "MstItem": "item",
        "MstMissionReward": "reward",
        "MstEvent": "event-basic",
        "MstStoreProduct": "shop-pack",
        "MstAdventBattle": "advent-battle",
        "MstPvp": "pvp",
        "MstArtwork": "artwork",
        "MstEmblem": "emblem",
        "MstEnemyCharacter": "enemy-autoplayer",
        "MstInGame": "ingame",
    }

    for keyword, category in content_keyword_map.items():
        if keyword in file_content:
            return f"masterdata-from-bizops-{category}"

    return None
```

### 1.3 各機能スキルに必要なパラメータを抽出

**ロジック**:
```
def extract_parameters(file_content, skill_name):
    """
    ファイル内容から各機能スキルに必要なパラメータを抽出する

    Args:
        file_content: ファイル内容
        skill_name: 機能スキル名

    Returns:
        parameters: パラメータ辞書
    """

    # 各機能スキルごとに必要なパラメータが異なるため、
    # 機能スキルごとの抽出ロジックを実装

    if skill_name == "masterdata-from-bizops-gacha":
        # ガチャスキルのパラメータ抽出
        return {
            "opr_gacha_id": extract_gacha_id(file_content),
            "gacha_name": extract_gacha_name(file_content),
            "gacha_type": extract_gacha_type(file_content),
            "start_at": extract_start_at(file_content),
            "end_at": extract_end_at(file_content),
            # ...その他のパラメータ
        }

    elif skill_name == "masterdata-from-bizops-hero":
        # ヒーロースキルのパラメータ抽出
        return {
            "unit_id": extract_unit_id(file_content),
            "unit_name": extract_unit_name(file_content),
            "rarity": extract_rarity(file_content),
            # ...その他のパラメータ
        }

    # ...その他の機能スキル

    return {}
```

## Step 2: 必要な機能の特定ロジック

### 2.1 解析結果から必要な機能スキルを特定

**ロジック**:
```
def identify_required_skills(analyzed_files):
    """
    解析結果から必要な機能スキルを特定する

    Args:
        analyzed_files: 解析結果のリスト
            [
                {"filename": "ガチャ設計書.xlsx", "category": "gacha", "parameters": {...}},
                {"filename": "ヒーロー設計書.xlsx", "category": "hero", "parameters": {...}},
                ...
            ]

    Returns:
        required_skills: 必要な機能スキルのリスト
            [
                {"skill_name": "masterdata-from-bizops-gacha", "parameters": {...}},
                {"skill_name": "masterdata-from-bizops-hero", "parameters": {...}},
                ...
            ]
    """

    required_skills = []

    for file_info in analyzed_files:
        category = file_info["category"]
        if category:
            skill_name = f"masterdata-from-bizops-{category}"
            required_skills.append({
                "skill_name": skill_name,
                "parameters": file_info["parameters"],
                "source_file": file_info["filename"],
            })

    return required_skills
```

## Step 3: 依存関係の解析ロジック

### 3.1 依存関係マップの定義

**依存関係グラフ**:
```
DEPENDENCY_GRAPH = {
    "item": [],  # 依存なし
    "hero": [],  # 依存なし
    "emblem": [],  # 依存なし
    "event-basic": [],  # 依存なし
    "reward": ["item", "hero", "emblem"],  # アイテム、ヒーロー、エンブレムに依存
    "gacha": ["hero"],  # ヒーローに依存
    "quest-stage": ["hero", "reward"],  # ヒーロー、報酬に依存
    "mission": ["reward"],  # 報酬に依存
    "advent-battle": ["reward", "event-basic"],  # 報酬、イベント基本設定に依存
    "pvp": ["event-basic"],  # イベント基本設定に依存
    "shop-pack": ["item"],  # アイテムに依存
    "artwork": [],  # 依存なし
    "enemy-autoplayer": [],  # 依存なし
    "ingame": ["hero"],  # ヒーローに依存
}
```

### 3.2 依存関係の解析

**ロジック**:
```
def analyze_dependencies(required_skills):
    """
    必要な機能スキルの依存関係を解析する

    Args:
        required_skills: 必要な機能スキルのリスト

    Returns:
        dependency_map: 依存関係マップ
            {
                "gacha": ["hero"],
                "mission": ["reward"],
                "reward": ["item", "hero", "emblem"],
                ...
            }
    """

    dependency_map = {}

    for skill_info in required_skills:
        skill_name = skill_info["skill_name"]
        category = skill_name.replace("masterdata-from-bizops-", "")

        # DEPENDENCY_GRAPHから依存関係を取得
        dependencies = DEPENDENCY_GRAPH.get(category, [])

        # 依存先が required_skills に含まれているもののみを抽出
        required_categories = [
            s["skill_name"].replace("masterdata-from-bizops-", "")
            for s in required_skills
        ]

        actual_dependencies = [
            dep for dep in dependencies
            if dep in required_categories
        ]

        dependency_map[category] = actual_dependencies

    return dependency_map
```

## Step 4: 実行順序の決定ロジック

### 4.1 トポロジカルソートで実行順序を決定

**ロジック**:
```
def determine_execution_order(dependency_map):
    """
    依存関係を考慮した実行順序を決定する（トポロジカルソート）

    Args:
        dependency_map: 依存関係マップ

    Returns:
        execution_order: 実行順序のリスト
            ["item", "hero", "emblem", "event-basic", "reward", "gacha", ...]
    """

    # トポロジカルソートのアルゴリズム（Kahn's algorithm）

    # 1. 各ノードの入次数を計算
    in_degree = {category: 0 for category in dependency_map}
    for category, dependencies in dependency_map.items():
        for dep in dependencies:
            in_degree[dep] = in_degree.get(dep, 0)
        for dep in dependencies:
            in_degree[category] += 1

    # 2. 入次数が0のノードをキューに追加
    queue = [category for category, degree in in_degree.items() if degree == 0]
    execution_order = []

    # 3. キューが空になるまで処理
    while queue:
        # 優先度順にソート（推奨実行順序を考慮）
        queue.sort(key=lambda x: RECOMMENDED_ORDER.index(x) if x in RECOMMENDED_ORDER else 99)

        # キューから取り出し
        current = queue.pop(0)
        execution_order.append(current)

        # 依存元のノードの入次数を減らす
        for category, dependencies in dependency_map.items():
            if current in dependencies:
                in_degree[category] -= 1
                if in_degree[category] == 0:
                    queue.append(category)

    # 4. 循環依存がある場合はエラー
    if len(execution_order) != len(dependency_map):
        raise ValueError("Circular dependency detected")

    return execution_order

# 推奨実行順序（依存関係がない場合の優先度）
RECOMMENDED_ORDER = [
    "item",
    "hero",
    "emblem",
    "event-basic",
    "reward",
    "gacha",
    "quest-stage",
    "mission",
    "advent-battle",
    "pvp",
    "shop-pack",
    "artwork",
    "enemy-autoplayer",
    "ingame",
]
```

## Step 5: 各機能スキルの呼び出しロジック

### 5.1 機能スキルの実行

**ロジック**:
```
def execute_skill(skill_name, parameters, source_file):
    """
    機能スキルを実行する

    Args:
        skill_name: 機能スキル名（例: "masterdata-from-bizops-gacha"）
        parameters: パラメータ辞書
        source_file: 運営仕様書ファイル名

    Returns:
        result: 実行結果
            {
                "success": True/False,
                "tables": ["OprGacha", "OprGachaI18n", ...],
                "record_count": 56,
                "csv_files": ["OprGacha.csv", "OprGachaI18n.csv", ...],
                "assumption_report": "...",
                "error": "...",  # エラーがある場合
            }
    """

    try:
        # 1. 機能スキルを呼び出す
        # （実際には、Skillツールを使って機能スキルを実行）

        # 2. 実行結果を収集
        result = {
            "success": True,
            "tables": extract_created_tables(),
            "record_count": count_records(),
            "csv_files": list_csv_files(),
            "assumption_report": extract_assumption_report(),
        }

        return result

    except Exception as e:
        # エラーが発生した場合
        return {
            "success": False,
            "error": str(e),
        }
```

### 5.2 実行結果の収集

**ロジック**:
```
def collect_results(execution_order, skill_results):
    """
    全機能スキルの実行結果を収集する

    Args:
        execution_order: 実行順序のリスト
        skill_results: 各機能スキルの実行結果

    Returns:
        summary: 実行サマリー
            {
                "total_skills": 10,
                "successful_skills": 9,
                "failed_skills": 1,
                "total_tables": 65,
                "total_records": 1234,
                "execution_time": "15分",
            }
    """

    total_skills = len(execution_order)
    successful_skills = sum(1 for r in skill_results if r["success"])
    failed_skills = total_skills - successful_skills
    total_tables = sum(len(r.get("tables", [])) for r in skill_results)
    total_records = sum(r.get("record_count", 0) for r in skill_results)

    return {
        "total_skills": total_skills,
        "successful_skills": successful_skills,
        "failed_skills": failed_skills,
        "total_tables": total_tables,
        "total_records": total_records,
    }
```

## Step 6: データ整合性チェックロジック

### 6.1 外部キー整合性チェック

**ロジック**:
```
def check_foreign_key_integrity(csv_files):
    """
    外部キー整合性をチェックする

    Args:
        csv_files: 作成されたCSVファイルのリスト

    Returns:
        errors: エラーリスト
            [
                {
                    "table": "MstMissionReward",
                    "column": "resource_id",
                    "value": "item_999",
                    "reference_table": "MstItem",
                    "error": "参照先が存在しません",
                },
                ...
            ]
    """

    errors = []

    # 外部キー関係の定義
    foreign_keys = [
        {
            "table": "MstMissionReward",
            "column": "resource_id",
            "reference_table": "MstItem",
            "reference_column": "id",
            "condition": "resource_type = 'Item'",
        },
        {
            "table": "MstMissionReward",
            "column": "resource_id",
            "reference_table": "MstUnit",
            "reference_column": "id",
            "condition": "resource_type = 'Unit'",
        },
        # ...その他の外部キー関係
    ]

    # 各外部キー関係をチェック
    for fk in foreign_keys:
        # CSVファイルを読み込み
        table_data = read_csv(fk["table"])
        reference_data = read_csv(fk["reference_table"])

        # 外部キー制約をチェック
        for row in table_data:
            if fk.get("condition"):
                # 条件がある場合（resource_type等）
                if not eval(fk["condition"].replace("resource_type", f"'{row['resource_type']}'")):
                    continue

            # 参照先に存在するかチェック
            if row[fk["column"]] not in reference_data[fk["reference_column"]]:
                errors.append({
                    "table": fk["table"],
                    "column": fk["column"],
                    "value": row[fk["column"]],
                    "reference_table": fk["reference_table"],
                    "error": "参照先が存在しません",
                })

    return errors
```

### 6.2 ID採番の一貫性チェック

**ロジック**:
```
def check_id_consistency(csv_files, release_key):
    """
    ID採番の一貫性をチェックする

    Args:
        csv_files: 作成されたCSVファイルのリスト
        release_key: リリースキー

    Returns:
        errors: エラーリスト
    """

    errors = []

    # 各CSVファイルをチェック
    for csv_file in csv_files:
        table_data = read_csv(csv_file)

        # リリースキーが統一されているかチェック
        if "release_key" in table_data.columns:
            for row in table_data:
                if row["release_key"] != release_key:
                    errors.append({
                        "table": csv_file,
                        "column": "release_key",
                        "expected": release_key,
                        "actual": row["release_key"],
                        "error": "リリースキーが不一致です",
                    })

    return errors
```

## Step 7: 推測値レポート統合ロジック

### 7.1 推測値レポートの収集

**ロジック**:
```
def integrate_assumption_reports(skill_results):
    """
    各機能スキルの推測値レポートを統合する

    Args:
        skill_results: 各機能スキルの実行結果

    Returns:
        integrated_report: 統合推測値レポート（Markdown形式）
    """

    # 推測値を収集
    assumptions = []

    for skill_result in skill_results:
        skill_name = skill_result["skill_name"]
        report = skill_result.get("assumption_report", "")

        # レポートをパースして推測値を抽出
        parsed_assumptions = parse_assumption_report(report)

        for assumption in parsed_assumptions:
            assumption["skill"] = skill_name
            assumptions.append(assumption)

    # 重要度でソート
    assumptions.sort(key=lambda x: {"High": 0, "Medium": 1, "Low": 2}.get(x["priority"], 3))

    # Markdownレポートを生成
    report = generate_markdown_report(assumptions)

    return report

def parse_assumption_report(report_text):
    """
    推測値レポートをパースする

    Args:
        report_text: レポートテキスト

    Returns:
        assumptions: 推測値のリスト
            [
                {
                    "table": "OprGacha",
                    "column": "display_size",
                    "value": "Medium",
                    "reason": "運営仕様書に記載なし",
                    "priority": "High",
                },
                ...
            ]
    """

    # レポートテキストをパースして推測値を抽出
    # （実装は省略）

    return assumptions

def generate_markdown_report(assumptions):
    """
    統合推測値レポートをMarkdown形式で生成する

    Args:
        assumptions: 推測値のリスト

    Returns:
        report: Markdownレポート
    """

    report = "# 統合推測値レポート\n\n"
    report += "## 概要\n"
    report += f"- 総推測値数: {len(assumptions)}件\n"
    report += f"- High（要確認）: {sum(1 for a in assumptions if a['priority'] == 'High')}件\n"
    report += f"- Medium（推奨確認）: {sum(1 for a in assumptions if a['priority'] == 'Medium')}件\n"
    report += f"- Low（参考）: {sum(1 for a in assumptions if a['priority'] == 'Low')}件\n\n"

    # 機能別にグループ化
    grouped = {}
    for assumption in assumptions:
        skill = assumption["skill"]
        if skill not in grouped:
            grouped[skill] = []
        grouped[skill].append(assumption)

    # 機能別にレポートを生成
    for skill, skill_assumptions in grouped.items():
        report += f"## {skill}\n\n"

        for priority in ["High", "Medium", "Low"]:
            priority_assumptions = [a for a in skill_assumptions if a["priority"] == priority]
            if priority_assumptions:
                report += f"### {priority}\n"
                for assumption in priority_assumptions:
                    report += f"- {assumption['table']}.{assumption['column']}: {assumption['value']} ({assumption['reason']})\n"
                report += "\n"

    return report
```

## Step 8: 全体レポート出力ロジック

### 8.1 全体レポートの生成

**ロジック**:
```
def generate_final_report(summary, integrated_report, integrity_errors):
    """
    全体レポートを生成する

    Args:
        summary: 実行サマリー
        integrated_report: 統合推測値レポート
        integrity_errors: データ整合性エラー

    Returns:
        final_report: 全体レポート（Markdown形式）
    """

    report = "# マスタデータ一括作成 実行レポート\n\n"

    # 実行サマリー
    report += "## 実行サマリー\n\n"
    report += f"- 実行した機能スキル: {summary['total_skills']}個\n"
    report += f"- 成功: {summary['successful_skills']}個\n"
    report += f"- 失敗: {summary['failed_skills']}個\n"
    report += f"- 作成したテーブル数: {summary['total_tables']}個\n"
    report += f"- 作成したレコード数: {summary['total_records']}件\n\n"

    # 統合推測値レポート
    report += integrated_report
    report += "\n"

    # データ整合性チェック結果
    report += "## データ整合性チェック結果\n\n"
    if not integrity_errors:
        report += "✅ データ整合性に問題はありません\n"
    else:
        report += f"⚠️ {len(integrity_errors)}件のエラーが見つかりました\n\n"
        for error in integrity_errors:
            report += f"- {error['table']}.{error['column']}: {error['error']}\n"
    report += "\n"

    # 次のステップ
    report += "## 次のステップ\n\n"
    report += "1. 統合推測値レポートを確認し、必要に応じて修正してください\n"
    report += "2. masterdata-csv-validatorスキルで検証してください\n"
    report += "3. DB投入前の最終チェックを実施してください\n"

    return report
```

## エラーハンドリング

### エラー発生時の処理

**ロジック**:
```
def handle_skill_error(skill_name, error, execution_order, current_index):
    """
    機能スキルでエラーが発生した場合の処理

    Args:
        skill_name: エラーが発生した機能スキル名
        error: エラー内容
        execution_order: 実行順序のリスト
        current_index: 現在の実行インデックス

    Returns:
        action: 次のアクション
            {
                "continue": True/False,  # 次の機能スキルに進むか
                "skip_dependent": ["mission", "quest-stage"],  # スキップする依存先
                "error_message": "...",
            }
    """

    # エラーをログに記録
    log_error(skill_name, error)

    # 依存先を確認
    category = skill_name.replace("masterdata-from-bizops-", "")
    dependent_skills = find_dependent_skills(category, execution_order[current_index + 1:])

    if dependent_skills:
        # 依存先がある場合、それらもスキップ
        return {
            "continue": True,
            "skip_dependent": dependent_skills,
            "error_message": f"{skill_name} で失敗しました。依存先 {dependent_skills} もスキップします。",
        }
    else:
        # 依存先がない場合、次の機能スキルに進む
        return {
            "continue": True,
            "skip_dependent": [],
            "error_message": f"{skill_name} で失敗しました。次の機能スキルに進みます。",
        }

def find_dependent_skills(category, remaining_skills):
    """
    指定されたカテゴリに依存する機能スキルを見つける

    Args:
        category: カテゴリ名
        remaining_skills: 残りの機能スキルリスト

    Returns:
        dependent_skills: 依存する機能スキルのリスト
    """

    dependent_skills = []

    for skill in remaining_skills:
        skill_category = skill.replace("masterdata-from-bizops-", "")
        dependencies = DEPENDENCY_GRAPH.get(skill_category, [])

        if category in dependencies:
            dependent_skills.append(skill_category)

    return dependent_skills
```

## 進捗レポート

### 進捗状況の表示

**ロジック**:
```
def report_progress(current_index, total_skills, skill_name, status):
    """
    進捗状況をレポートする

    Args:
        current_index: 現在のインデックス（0始まり）
        total_skills: 総機能スキル数
        skill_name: 機能スキル名
        status: ステータス（"実行中" / "完了" / "失敗"）
    """

    progress = f"[{current_index + 1}/{total_skills}] {skill_name} {status}"

    if status == "完了":
        # 完了時は結果も表示
        progress += f"（{result['table_count']}テーブル、{result['record_count']}レコード）"

    print(progress)
```

## まとめ

このワークフローロジックは、運営仕様書全体から全95テーブルのマスタデータを高精度に一括作成するための詳細設計です。

**主要な特徴**:
1. **運営仕様書の自動解析**: ファイル名・内容から機能を自動判定
2. **依存関係を考慮した実行順序**: トポロジカルソートで最適な順序を算出
3. **エラーハンドリング**: エラー発生時も可能な限り処理を継続
4. **データ整合性チェック**: 外部キー、ID採番等を自動検証
5. **推測値レポート統合**: 全機能スキルの推測値を統合して可視化
