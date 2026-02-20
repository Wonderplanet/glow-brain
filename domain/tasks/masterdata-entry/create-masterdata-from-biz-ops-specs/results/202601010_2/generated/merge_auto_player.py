import csv

base = "/Users/junki.mizutani/Documents/workspace/glow/glow-brain-repos/glow-brain-hotei/"
output_file = base + "domain/tasks/masterdata-entry/create-masterdata-from-biz-ops-specs/results/202601010_2/generated/MstAutoPlayerSequence.csv"

input_files = [
    base + "domain/raw-data/google-drive/spread-sheet/GLOW/031_レベルデザイン/基礎設計シート/01_クエスト・ステージ/クエスト設計/イベントクエスト/【202601010】地獄楽 いいジャン祭/【1日1回】本能が告げている 危険だと/MstAutoPlayerSequence.csv",
    base + "domain/raw-data/google-drive/spread-sheet/GLOW/031_レベルデザイン/基礎設計シート/01_クエスト・ステージ/クエスト設計/イベントクエスト/【202601010】地獄楽 いいジャン祭/【ストーリー】必ず生きて帰る/MstAutoPlayerSequence.csv",
    base + "domain/raw-data/google-drive/spread-sheet/GLOW/031_レベルデザイン/基礎設計シート/01_クエスト・ステージ/クエスト設計/イベントクエスト/【202601010】地獄楽 いいジャン祭/【チャレンジ】死罪人と首切り役人設計/MstAutoPlayerSequence.csv",
    base + "domain/raw-data/google-drive/spread-sheet/GLOW/031_レベルデザイン/基礎設計シート/01_クエスト・ステージ/クエスト設計/イベントクエスト/【202601010】地獄楽 いいジャン祭/【高難度】手負いの獣は恐ろしいぞ/MstAutoPlayerSequence.csv",
    base + "domain/raw-data/google-drive/spread-sheet/GLOW/031_レベルデザイン/基礎設計シート/01_クエスト・ステージ/クエスト設計/イベントクエスト/【202601010】地獄楽 いいジャン祭/【降臨バトル】まるで 悪夢を見ているようだ_地獄楽/MstAutoPlayerSequence.csv",
]

headers = ["ENABLE","id","sequence_set_id","sequence_group_id","sequence_element_id","priority_sequence_element_id","condition_type","condition_value","action_type","action_value","action_value2","summon_count","summon_interval","summon_animation_type","summon_position","move_start_condition_type","move_start_condition_value","move_stop_condition_type","move_stop_condition_value","move_restart_condition_type","move_restart_condition_value","move_loop_count","is_summon_unit_outpost_damage_invalidation","last_boss_trigger","aura_type","death_type","enemy_hp_coef","enemy_attack_coef","enemy_speed_coef","override_drop_battle_point","defeated_score","action_delay","deactivation_condition_type","deactivation_condition_value","release_key"]

all_rows = []

for input_file in input_files:
    try:
        with open(input_file, 'r', encoding='utf-8') as f:
            reader = csv.DictReader(f)
            for row in reader:
                # sequence_set_idが存在し、シート名でないデータ行のみ
                if row.get('sequence_set_id') and row.get('sequence_set_id').strip():
                    all_rows.append(row)
    except Exception as e:
        print(f"Error reading {input_file}: {e}")

print(f"Total rows collected: {len(all_rows)}")

# 出力
with open(output_file, 'w', encoding='utf-8', newline='') as f:
    writer = csv.DictWriter(f, fieldnames=headers, extrasaction='ignore')
    writer.writeheader()

    for idx, row in enumerate(all_rows, start=1):
        row['ENABLE'] = 'e'

        # id列が空の場合、sequence_set_id + sequence_element_idで生成
        if not row.get('id') or not row.get('id').strip():
            seq_set = row.get('sequence_set_id', '')
            seq_elem = row.get('sequence_element_id', '')
            row['id'] = f"{seq_set}_{seq_elem}"

        # TRUE/FALSEを1/空文字に変換
        if row.get('is_summon_unit_outpost_damage_invalidation') == 'TRUE':
            row['is_summon_unit_outpost_damage_invalidation'] = '1'
        elif row.get('is_summon_unit_outpost_damage_invalidation') == 'FALSE':
            row['is_summon_unit_outpost_damage_invalidation'] = ''

        writer.writerow(row)

print(f"Successfully wrote {len(all_rows)} rows to {output_file}")
