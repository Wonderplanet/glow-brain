#!/usr/bin/env python3
"""
インゲームマスタデータ ID整合性チェックスクリプト

使用方法:
    python verify_id_integrity.py --dir {generated_dir_path}

出力:
    JSON形式のレポート（masterdata-csv-validatorと同フォーマット）

exitcode:
    0: 問題なし
    1: 問題あり（CRITICALまたはWARNING）
"""

import argparse
import json
import os
import sys

try:
    import duckdb
except ImportError:
    print(json.dumps({
        "error": "duckdb モジュールが見つかりません。pip install duckdb を実行してください。",
        "valid": False
    }, ensure_ascii=False, indent=2))
    sys.exit(1)


def load_csv(con, path, alias):
    """CSVをDuckDBに読み込む。ファイルが存在しない場合はNoneを返す。"""
    if not os.path.exists(path):
        return None
    try:
        con.execute(f"CREATE OR REPLACE VIEW {alias} AS SELECT * FROM read_csv('{path}', AUTO_DETECT=TRUE)")
        return alias
    except Exception as e:
        return None


def check_ingame_sequence_fk(con, issues):
    """MstInGame.mst_auto_player_sequence_set_id → MstAutoPlayerSequence.sequence_set_id"""
    try:
        result = con.execute("""
            SELECT i.id AS ingame_id, i.mst_auto_player_sequence_set_id
            FROM ingame i
            WHERE i.mst_auto_player_sequence_set_id IS NOT NULL
              AND i.mst_auto_player_sequence_set_id != ''
              AND i.mst_auto_player_sequence_set_id NOT IN (
                  SELECT DISTINCT CAST(sequence_set_id AS VARCHAR)
                  FROM sequence
                  WHERE sequence_set_id IS NOT NULL
              )
        """).fetchall()
        if result:
            for row in result:
                issues.append({
                    "check": "ingame_sequence_fk",
                    "severity": "CRITICAL",
                    "ingame_id": row[0],
                    "missing_value": row[1],
                    "message": f"MstInGame.mst_auto_player_sequence_set_id '{row[1]}' がMstAutoPlayerSequenceのsequence_set_idに存在しません"
                })
            return False
        return True
    except Exception as e:
        issues.append({"check": "ingame_sequence_fk", "severity": "ERROR", "message": str(e)})
        return False


def check_ingame_page_fk(con, issues):
    """MstInGame.mst_page_id → MstPage.id"""
    try:
        result = con.execute("""
            SELECT i.id AS ingame_id, i.mst_page_id
            FROM ingame i
            WHERE i.mst_page_id IS NOT NULL
              AND i.mst_page_id != ''
              AND i.mst_page_id NOT IN (
                  SELECT CAST(id AS VARCHAR) FROM page WHERE id IS NOT NULL
              )
        """).fetchall()
        if result:
            for row in result:
                issues.append({
                    "check": "ingame_page_fk",
                    "severity": "CRITICAL",
                    "ingame_id": row[0],
                    "missing_value": row[1],
                    "message": f"MstInGame.mst_page_id '{row[1]}' がMstPage.idに存在しません"
                })
            return False
        return True
    except Exception as e:
        issues.append({"check": "ingame_page_fk", "severity": "ERROR", "message": str(e)})
        return False


def check_ingame_outpost_fk(con, issues):
    """MstInGame.mst_enemy_outpost_id → MstEnemyOutpost.id"""
    try:
        result = con.execute("""
            SELECT i.id AS ingame_id, i.mst_enemy_outpost_id
            FROM ingame i
            WHERE i.mst_enemy_outpost_id IS NOT NULL
              AND i.mst_enemy_outpost_id != ''
              AND i.mst_enemy_outpost_id NOT IN (
                  SELECT CAST(id AS VARCHAR) FROM outpost WHERE id IS NOT NULL
              )
        """).fetchall()
        if result:
            for row in result:
                issues.append({
                    "check": "ingame_outpost_fk",
                    "severity": "CRITICAL",
                    "ingame_id": row[0],
                    "missing_value": row[1],
                    "message": f"MstInGame.mst_enemy_outpost_id '{row[1]}' がMstEnemyOutpost.idに存在しません"
                })
            return False
        return True
    except Exception as e:
        issues.append({"check": "ingame_outpost_fk", "severity": "ERROR", "message": str(e)})
        return False


def check_ingame_boss_fk(con, issues):
    """MstInGame.boss_mst_enemy_stage_parameter_id → MstEnemyStageParameter.id（空欄は許可）"""
    try:
        result = con.execute("""
            SELECT i.id AS ingame_id, i.boss_mst_enemy_stage_parameter_id
            FROM ingame i
            WHERE i.boss_mst_enemy_stage_parameter_id IS NOT NULL
              AND i.boss_mst_enemy_stage_parameter_id != ''
              AND i.boss_mst_enemy_stage_parameter_id NOT IN (
                  SELECT CAST(id AS VARCHAR) FROM parameter WHERE id IS NOT NULL
              )
        """).fetchall()
        if result:
            for row in result:
                issues.append({
                    "check": "ingame_boss_fk",
                    "severity": "CRITICAL",
                    "ingame_id": row[0],
                    "missing_value": row[1],
                    "message": f"MstInGame.boss_mst_enemy_stage_parameter_id '{row[1]}' がMstEnemyStageParameter.idに存在しません"
                })
            return False
        return True
    except Exception as e:
        issues.append({"check": "ingame_boss_fk", "severity": "ERROR", "message": str(e)})
        return False


def check_sequence_set_id_consistency(con, issues):
    """全MstAutoPlayerSequenceのsequence_set_idが同一値か"""
    try:
        result = con.execute("""
            SELECT COUNT(DISTINCT CAST(sequence_set_id AS VARCHAR)) AS distinct_count,
                   MIN(CAST(sequence_set_id AS VARCHAR)) AS first_id,
                   MAX(CAST(sequence_set_id AS VARCHAR)) AS last_id
            FROM sequence
            WHERE sequence_set_id IS NOT NULL AND CAST(sequence_set_id AS VARCHAR) != ''
        """).fetchone()
        if result and result[0] > 1:
            issues.append({
                "check": "sequence_set_id_consistency",
                "severity": "WARNING",
                "distinct_count": result[0],
                "message": f"MstAutoPlayerSequenceに {result[0]} 種類のsequence_set_idが混在しています（{result[1]} 〜 {result[2]}）"
            })
            return False
        return True
    except Exception as e:
        issues.append({"check": "sequence_set_id_consistency", "severity": "ERROR", "message": str(e)})
        return False


def check_sequence_action_value_fk(con, issues):
    """action_type=SummonEnemy の action_value → MstEnemyStageParameter.id"""
    try:
        result = con.execute("""
            SELECT s.id, s.sequence_element_id, s.action_value
            FROM sequence s
            WHERE s.action_type = 'SummonEnemy'
              AND s.action_value IS NOT NULL
              AND CAST(s.action_value AS VARCHAR) != ''
              AND CAST(s.action_value AS VARCHAR) NOT IN (
                  SELECT CAST(id AS VARCHAR) FROM parameter WHERE id IS NOT NULL
              )
        """).fetchall()
        if result:
            for row in result:
                issues.append({
                    "check": "sequence_action_value_fk",
                    "severity": "CRITICAL",
                    "sequence_id": str(row[0]),
                    "sequence_element_id": str(row[1]),
                    "missing_value": str(row[2]),
                    "message": f"MstAutoPlayerSequence（id={row[0]}, element={row[1]}）のaction_value '{row[2]}' がMstEnemyStageParameter.idに存在しません"
                })
            return False
        return True
    except Exception as e:
        issues.append({"check": "sequence_action_value_fk", "severity": "ERROR", "message": str(e)})
        return False


def main():
    parser = argparse.ArgumentParser(
        description="インゲームマスタデータのID整合性チェック"
    )
    parser.add_argument(
        "--dir",
        required=True,
        help="生成済みCSVが格納されたディレクトリパス"
    )
    args = parser.parse_args()

    generated_dir = args.dir.rstrip("/")

    if not os.path.isdir(generated_dir):
        print(json.dumps({
            "error": f"ディレクトリが見つかりません: {generated_dir}",
            "valid": False
        }, ensure_ascii=False, indent=2))
        sys.exit(1)

    # 各CSVファイルのパス
    files = {
        "ingame": os.path.join(generated_dir, "MstInGame.csv"),
        "sequence": os.path.join(generated_dir, "MstAutoPlayerSequence.csv"),
        "page": os.path.join(generated_dir, "MstPage.csv"),
        "outpost": os.path.join(generated_dir, "MstEnemyOutpost.csv"),
        "parameter": os.path.join(generated_dir, "MstEnemyStageParameter.csv"),
    }

    # 必須ファイルの存在確認
    missing_files = [name for name, path in files.items() if not os.path.exists(path)]
    if missing_files:
        print(json.dumps({
            "error": f"必須CSVファイルが見つかりません: {', '.join(missing_files)}",
            "valid": False,
            "missing_files": missing_files
        }, ensure_ascii=False, indent=2))
        sys.exit(1)

    con = duckdb.connect()

    # CSVをビューとして登録
    loaded = {}
    for alias, path in files.items():
        result = load_csv(con, path, alias)
        loaded[alias] = result is not None

    issues = []
    checks = {}

    # 各チェックを実行
    checks["ingame_sequence_fk"] = check_ingame_sequence_fk(con, issues)
    checks["ingame_page_fk"] = check_ingame_page_fk(con, issues)
    checks["ingame_outpost_fk"] = check_ingame_outpost_fk(con, issues)
    checks["ingame_boss_fk"] = check_ingame_boss_fk(con, issues)
    checks["sequence_set_id_consistency"] = check_sequence_set_id_consistency(con, issues)
    checks["sequence_action_value_fk"] = check_sequence_action_value_fk(con, issues)

    con.close()

    # 結果集計
    critical_issues = [i for i in issues if i.get("severity") == "CRITICAL"]
    warning_issues = [i for i in issues if i.get("severity") == "WARNING"]
    is_valid = len(critical_issues) == 0

    report = {
        "check": "id_integrity",
        "valid": is_valid,
        "directory": generated_dir,
        "checks": checks,
        "issues": issues,
        "summary": {
            "total_issues": len(issues),
            "critical_issues": len(critical_issues),
            "warnings": len(warning_issues)
        }
    }

    print(json.dumps(report, ensure_ascii=False, indent=2))
    sys.exit(0 if is_valid else 1)


if __name__ == "__main__":
    main()
