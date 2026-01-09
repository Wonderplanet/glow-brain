#!/usr/bin/env python3
"""
Parse Sequence.html file from operational specifications and generate master data CSV files
for GLOW game advent battles.
"""

import re
import csv
import sys
import os
from pathlib import Path

def clean_html_text(html):
    """Remove HTML tags and clean text"""
    text = re.sub(r'<[^>]+>', '', html)
    return text.strip()

def parse_sequence_html(html_path):
    """Parse the Sequence.html file and extract all relevant data"""
    
    with open(html_path, 'r', encoding='utf-8') as f:
        content = f.read()
    
    # Extract table rows
    rows = re.findall(r'<tr[^>]*>(.*?)</tr>', content, re.DOTALL)
    
    # Data structures
    data = {
        'stage_name': '',
        'stamina': '',
        'bgm_normal': '',
        'bgm_boss': '',
        'home_text': '',
        'gate_id_player': '',
        'gate_id_enemy': '',
        'gate_asset_enemy': '',
        'gate_hp_enemy': '',
        'release_key': '',
        'clear_coin': '',
        'leader_exp': '',
        'drops': [],
        'enemies': {},  # Use dict to deduplicate by ID
    }
    
    for i, row in enumerate(rows):
        cells = re.findall(r'<td[^>]*?>(.*?)</td>', row, re.DOTALL)
        clean_cells = [clean_html_text(cell) for cell in cells]
        
        # Skip empty rows
        if not any(clean_cells):
            continue
        
        # Extract stage basic info (row with stage name)
        if len(clean_cells) > 10 and clean_cells[1] == '誰の依頼だ？':
            data['stage_name'] = clean_cells[1]
            data['stamina'] = clean_cells[7]
            data['bgm_normal'] = clean_cells[8]
            data['bgm_boss'] = clean_cells[9]
            data['home_text'] = clean_cells[10]
        
        # Extract gate info
        if len(clean_cells) > 13 and clean_cells[1] == 'default':
            data['gate_id_player'] = clean_cells[1]
            data['gate_id_enemy'] = clean_cells[4]
            data['gate_asset_enemy'] = clean_cells[7]
            data['gate_hp_enemy'] = clean_cells[10]
            data['release_key'] = clean_cells[13]
        
        # Extract rewards  
        if len(clean_cells) > 3 and i > 15 and i < 25:
            if clean_cells[1].isdigit() and data['clear_coin'] == '':
                data['clear_coin'] = clean_cells[1]
                data['leader_exp'] = clean_cells[2]
        
        # Extract drop items
        if len(clean_cells) > 10 and clean_cells[1] == 'Random':
            drop = {
                'type': clean_cells[2],
                'id': clean_cells[4],
                'name': clean_cells[7],
                'count': clean_cells[8],
                'rate': clean_cells[10]
            }
            data['drops'].append(drop)
        
        # Extract enemy data
        for j, cell in enumerate(clean_cells):
            if cell.startswith(('e_you_', 'c_you_')) and '_advent_' in cell:
                enemy_id = cell
                # Extract enemy info if not already added
                if enemy_id not in data['enemies'] and j + 15 < len(clean_cells):
                    try:
                        enemy = {
                            'id': enemy_id,
                            'name': clean_cells[j+1],
                            'kind': clean_cells[j+2],
                            'color': clean_cells[j+3],
                            'role': clean_cells[j+4],
                            'ai_type': clean_cells[j+5],
                            'difficulty': clean_cells[j+6],
                            'hp': clean_cells[j+8],
                            'leader_point': clean_cells[j+9],
                            'attack': clean_cells[j+10],
                            'combo': clean_cells[j+11],
                        }
                        # Only add if has valid data
                        if enemy['hp'] and enemy['hp'] not in ['none', '\u200b', '']:
                            data['enemies'][enemy_id] = enemy
                    except (IndexError, ValueError):
                        pass
                break
    
    return data


def generate_advent_battle_csv(data, output_path):
    """Generate MstAdventBattle.csv"""
    
    # Read template to get structure
    template_path = 'projects/glow-masterdata/sheet_schema/MstAdventBattle.csv'
    
    if not os.path.exists(template_path):
        print(f"Warning: Template not found: {template_path}")
        return
    
    with open(template_path, 'r', encoding='utf-8') as f:
        reader = csv.reader(f)
        rows = list(reader)
    
    # rows[0] is memo, rows[1] is TABLE row, rows[2] is ENABLE row (headers)
    
    # Create data row
    advent_id = "quest_raid_you1_00001"
    event_id = "event_you_00001"
    in_game_id = data['gate_id_enemy']
    asset_key = data['gate_asset_enemy']
    
    data_row = [
        'e',  # ENABLE
        advent_id,  # id
        event_id,  # mst_event_id
        in_game_id,  # mst_in_game_id
        asset_key,  # asset_key
        'ScoreChallenge',  # advent_battle_type
        '500',  # initial_battle_point
        'AllEnemiesAndOutPost',  # score_addition_type
        '0.07',  # score_additional_coef
        'test',  # score_addition_target_mst_enemy_stage_parameter_id
        '',  # mst_stage_rule_group_id
        in_game_id,  # event_bonus_group_id
        '3',  # challengeable_count
        '2',  # ad_challengeable_count
        '',  # display_mst_unit_id1
        '',  # display_mst_unit_id2
        '',  # display_mst_unit_id3
        data['leader_exp'],  # exp
        data['clear_coin'],  # coin
        '"2026-02-01 15:00:00"',  # start_at
        '"2026-02-28 14:59:59"',  # end_at
        data['release_key'],  # release_key
        data['stage_name'],  # name.ja
        data['home_text'],  # boss_description.ja
    ]
    
    # Write output
    with open(output_path, 'w', encoding='utf-8', newline='') as f:
        writer = csv.writer(f)
        writer.writerow(rows[0])  # memo
        writer.writerow(rows[1])  # TABLE
        writer.writerow(rows[2])  # ENABLE (headers)
        writer.writerow(data_row)
    
    print(f"✓ Created: {output_path}")


def generate_enemy_stage_parameter_csv(data, output_path):
    """Generate MstEnemyStageParameter.csv"""
    
    template_path = 'projects/glow-masterdata/sheet_schema/MstEnemyStageParameter.csv'
    
    if not os.path.exists(template_path):
        print(f"Warning: Template not found: {template_path}")
        return
    
    with open(template_path, 'r', encoding='utf-8') as f:
        reader = csv.reader(f)
        rows = list(reader)
    
    # Write output
    with open(output_path, 'w', encoding='utf-8', newline='') as f:
        writer = csv.writer(f)
        writer.writerow(rows[0])  # memo
        writer.writerow(rows[1])  # TABLE
        writer.writerow(rows[2])  # ENABLE (headers)
        
        # Add each enemy as a row
        for enemy_id, enemy in data['enemies'].items():
            # Extract character ID from enemy ID (e.g., e_you_00001 or chara_you_00001)
            char_id = enemy_id.split('_advent_')[0].replace('e_', 'enemy_').replace('c_', 'chara_')
            
            data_row = [
                'e',  # ENABLE
                data['release_key'],  # release_key
                enemy_id,  # id
                char_id,  # mst_enemy_character_id
                enemy['kind'],  # character_unit_kind  
                enemy['role'],  # role_type
                enemy['color'],  # color
                '',  # sort_order
                enemy['hp'],  # hp
                enemy['combo'],  # damage_knock_back_count
                '45',  # move_speed
                '0.34',  # well_distance
                enemy['attack'],  # attack_power
                '6',  # attack_combo_cycle
                '',  # mst_unit_ability_id1
                enemy['leader_point'] if enemy['leader_point'] else '',  # drop_battle_point
                '',  # mstTransformationEnemyStageParameterId
                'None',  # transformationConditionType
                '',  # transformationConditionValue
            ]
            writer.writerow(data_row)
    
    print(f"✓ Created: {output_path} ({len(data['enemies'])} enemies)")


def main():
    if len(sys.argv) < 2:
        print("Usage: parse_sequence_html.py <path_to_Sequence.html>")
        sys.exit(1)
    
    html_path = sys.argv[1]
    
    if not os.path.exists(html_path):
        print(f"Error: File not found: {html_path}")
        sys.exit(1)
    
    print(f"Parsing: {html_path}")
    
    # Parse data
    data = parse_sequence_html(html_path)
    
    # Print summary
    print("\n=== Extracted Data ===")
    print(f"Stage Name: {data['stage_name']}")
    print(f"Release Key: {data['release_key']}")
    print(f"Enemies: {len(data['enemies'])}")
    print(f"Drops: {len(data['drops'])}")
    
    # Generate output files
    output_dir = Path(html_path).parent
    
    print("\n=== Generating CSV files ===")
    generate_advent_battle_csv(data, output_dir / 'MstAdventBattle.csv')
    generate_enemy_stage_parameter_csv(data, output_dir / 'MstEnemyStageParameter.csv')
    
    print("\n✓ Master data generation complete!")


if __name__ == '__main__':
    main()
