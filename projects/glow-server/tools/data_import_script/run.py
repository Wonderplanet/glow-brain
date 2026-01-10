#!/usr/bin/env python
# coding: utf-8

# # main

# In[283]:


# Setup
client2server_map = [
    {"client_data": "AttackElementDataList_必殺ワザ6種対応版", "db_schema": "mst_attacks", "column_name_map": {}},
    {"client_data": "CharacterDataList", "db_schema": "mst_units", "column_name_map": {}},
    {"client_data": "CharacterDataList", "db_schema": "mst_units_i18n", "column_name_map": {}},
    {"client_data": "EnemyCharacterDataList", "db_schema": "mst_enemy_characters", "column_name_map": {}},
    {"client_data": "EnemyCharacterDataList", "db_schema": "mst_enemy_characters_i18n", "column_name_map": {}},
    {"client_data": "EnemyOutpostDataList", "db_schema": "mst_enemy_out_posts", "column_name_map": {}},
    {"client_data": "ItemDataList", "db_schema": "mst_items", "column_name_map": {"type": "item_type"}},
    {"client_data": "ItemI18nDataList", "db_schema": "mst_items_i18n", "column_name_map": {}},
    {"client_data": "MstIdleIncentiveDataList", "db_schema": "mst_idle_incentives", "column_name_map": {}},
    {"client_data": "MstIdleIncentiveItemDataList", "db_schema": "mst_idle_incentive_items", "column_name_map": {}},
    {"client_data": "MstIdleIncentiveRewardDataList", "db_schema": "mst_idle_incentive_rewards", "column_name_map": {}},
    {"client_data": "MstKomaLineDataList", "db_schema": "mst_koma_lines", "column_name_map": {}, 
         "default_values": {
             "koma1_effect_type": "None", "koma2_effect_type": "None", "koma3_effect_type": "None", "koma4_effect_type": "None", 
             "koma1_effect_target_side": "All", "koma2_effect_target_side": "All", "koma3_effect_target_side": "All", "koma4_effect_target_side": "All"
         }
    },
    {"client_data": "MstOutpostEnhancementI18nDataList", "db_schema": "mst_outpost_enhancements_i18n", "column_name_map": {}},
    {"client_data": "MstOutpostEnhancementLevelI18nDataList", "db_schema": "mst_outpost_enhancement_levels_i18n", "column_name_map": {}},
    {"client_data": "MstOutpostEnhancementLevelsDataList", "db_schema": "mst_outpost_enhancement_levels", "column_name_map": {}},
    {"client_data": "MstOutpostEnhancementsDataList", "db_schema": "mst_outpost_enhancements", "column_name_map": {"outpost_enhance_type": "outpost_enhancement_type"}},
    {"client_data": "MstOutpostsDataList", "db_schema": "mst_outposts", "column_name_map": {"outpost_enhance_type": "outpost_enhance_type"}},
    {"client_data": "MstPackContentDataList", "db_schema": "mst_pack_contents", "column_name_map": {}},
    {"client_data": "MstPackDataList", "db_schema": "mst_packs", "column_name_map": {}, 
         "default_values": {"sale_condition": "NULL", "cost_amount": 0}, 
         "snake_case_enums": ["sale_condition", "cost_type"],
         "convert_values": {"pack_decoration": ["", "NULL"], "sale_hours": ["", "NULL"]}
    },
    {"client_data": "MstPackI18nDataList", "db_schema": "mst_packs_i18n", "column_name_map": {}},
    {"client_data": "MstPageDataList", "db_schema": "mst_pages", "column_name_map": {}},
    {"client_data": "MstQuestDataList", "db_schema": "mst_quests", "column_name_map": {}},
    {"client_data": "MstQuestI18nDataList", "db_schema": "mst_quests_i18n", "column_name_map": {}},
    {"client_data": "MstShopProductDataList", "db_schema": "mst_shop_items", "convert_values": {"cost_amount": ["", "NULL"]}},
    {"client_data": "MstStageI18nDataList", "db_schema": "mst_stages_i18n", "column_name_map": {}},
    {"client_data": "MstStoreProductDataList", "db_schema": "mst_store_products", "column_name_map": {}, 
         "db_ref_columns": {"product_id_ios": "mst_store_product_id", "product_id_android": "mst_store_product_id"}
    },
    {"client_data": "MstStoreProductDataList", "db_schema": "mst_store_products_i18n", "column_name_map": {},
         "db_ref_columns": {"price_ios": "price", "price_android": "price"}
    },
    {"client_data": "OprProductDataList", "db_schema": "opr_products", 
         "convert_values": {"purchasable_count": [-1, "NULL"], "paid_amount": ["", 0]}
    },
    {"client_data": "StageDataList", "db_schema": "mst_stages", "column_name_map": {}},
    {"client_data": "StageRewardGroupDataList", "db_schema": "mst_stage_reward_groups", "column_name_map": {}},
    {"client_data": "UserLevelDataList", "db_schema": "mst_user_levels", "column_name_map": {}},

    {"client_data": "MstFragmentBoxDataList", "db_schema": "mst_fragment_boxes", "column_name_map": {}},
    {"client_data": "MstFragmentBoxGroupDataList", "db_schema": "mst_fragment_box_groups", "column_name_map": {}},

    {"client_data": "MstMissionWeeklyI18nDataList", "db_schema": "mst_mission_weeklies_i18n"},
    {"client_data": "MstMissionAchievementDataList", "db_schema": "mst_mission_achievements", "convert_formats": {"criterion_type": "UpperCamelCase"}},
    {"client_data": "MstMissionAchievementDependencyDataList", "db_schema": "mst_mission_achievement_dependencies"},
    {"client_data": "MstMissionAchievementI18nDataList", "db_schema": "mst_mission_achievements_i18n"},
    {"client_data": "MstMissionDailyBonusDataList", "db_schema": "mst_mission_daily_bonuses", "column_name_map": {"mission_daily_bonus_type": "type"}},
    {"client_data": "MstMissionDailyDataList", "db_schema": "mst_mission_dailies", "convert_formats": {"criterion_type": "UpperCamelCase"}},
    {"client_data": "MstMissionDailyI18nDataList", "db_schema": "mst_mission_dailies_i18n"},
    {"client_data": "MstMissionRewardDataList", "db_schema": "mst_mission_rewards"},
    {"client_data": "MstMissionWeeklyDataList", "db_schema": "mst_mission_weeklies", "convert_formats": {"criterion_type": "UpperCamelCase"}},

    {"client_data": "MstConfigDataList", "db_schema": "mst_configs"},
    {"client_data": "MstUnitGradeCoefficientDataList", "db_schema": "mst_unit_grade_coefficients"},
    {"client_data": "MstUnitGradeUpDataList", "db_schema": "mst_unit_grade_ups"},
    {"client_data": "MstUnitLevelUpDataList", "db_schema": "mst_unit_level_ups"},
    {"client_data": "MstUnitRankUpDataList", "db_schema": "mst_unit_rank_ups"},
]

datetime_columns = [
    "start_date",
    "end_date",
    "start_at",
    "end_at"
];

# import
import openpyxl
import pandas as pd
import re
import os
import datetime
from IPython.display import display
import numpy as np

# function
def camel_to_snake(name):
    if name == 'NULL':
        return name
    
    # 先頭が大文字で始まる場合の処理
    name = re.sub('(.)([A-Z][a-z]+)', r'\1_\2', name)
    # 大文字と小文字の間の場合の処理
    return re.sub('([a-z0-9])([A-Z])', r'\1_\2', name).lower()

def snake_to_upper_camel(snake_str):
    # スネークケースをアッパーキャメルケースに変換
    components = snake_str.split('_')
    # 各コンポーネントの最初の文字を大文字にし、残りの文字はそのままで結合する
    return ''.join(x.capitalize() for x in components)

def adjust_datetime(dt):
    # # 設定する年の上限を定義
    # max_year = 2037
    # # 指定された日付が上限年を超えているかチェック
    # if dt.year > max_year:
    #     # 上限年を超えていれば2037年1月1日に設定
    #     dt = datetime.datetime(max_year, 1, 1)
    # return dt.strftime('%Y-%m-%d %H:%M:%S')
    try:
        if isinstance(dt, str):
            dt = datetime.datetime.strptime(dt, '%Y/%m/%d %H:%M:%S')
        
        # 設定する年の上限を定義
        max_year = 2037
        # 指定された日付が上限年を超えているかチェック
        if dt.year > max_year:
            # 上限年を超えていれば2037年1月1日に設定
            dt = datetime.datetime(max_year, 1, 1)
        return dt.strftime('%Y-%m-%d %H:%M:%S')
    except:
        # 日付データになってなかったりする場合
        # print('error in adjust_datetime')
        return dt

def bool2Int(v):
    return int(bool(v))

# Process
queries = []
for target in client2server_map:
    client_data = target["client_data"]
    db_schema = target["db_schema"]
    column_name_map = target["column_name_map"] if "column_name_map" in target else {}
    default_values = target["default_values"] if "default_values" in target else {}
    db_ref_columns = target["db_ref_columns"] if "db_ref_columns" in target else {}
    snake_case_enums = target["snake_case_enums"] if "snake_case_enums" in target else []
    convert_values = target["convert_values"] if "convert_values" in target else {}
    convert_formats = target["convert_formats"] if "convert_formats" in target else {}

    base_db_table_name = db_schema.replace('_i18n', '')
    db_table_name_id_column_name = base_db_table_name[:-1] + '_id'
    
    client_data_path = os.path.join('client_data', client_data + '.xlsx')
    if not os.path.exists(client_data_path):
        # print('client data not found: {}'.format(client_data))
        continue
    client_df = pd.read_excel(client_data_path, na_filter=False)
    client_df.columns = [camel_to_snake(s) for s in client_df.columns]
    client_df = client_df.rename(columns=column_name_map)
    
    db_schema_path = os.path.join('db_schema', db_schema + '.csv')
    db_df = pd.read_csv(db_schema_path, na_filter=False)    

    data_count = len(client_df)

    # ダミーデータ値設定
    dummy_data = 1
    is_show = False
    dummy_message_lambda = lambda table_name, column_name, is_show: print('insert dummy data: {}.{}'.format(table_name, column_name)) if is_show and not column_name == 'release_key' else ""
        
    values = {};
    for k in db_df.columns:
        if k in client_df:
            column_values = client_df[k].values
            if k in default_values:
                column_values = [default_values[k] if v == '' else v for v in column_values]
            values[k] = column_values
        else:
            if k == 'id':
                values[k] = [i+1 for i in range(data_count)]
            # テーブル名_idデータを入れる
            elif k == db_table_name_id_column_name and 'id' in values:
                values[k] = values['id']
            elif k == db_table_name_id_column_name and not 'id' in values: 
                values[k] = [dummy_data for _ in range(data_count)]
                dummy_message_lambda(db_schema, k, is_show)
            # 言語情報をjaで追加
            elif k == 'language':
                values[k] = ['ja' for _ in range(data_count)]
            # 日付データを入れる
            elif k in ['start_date', 'start_at']:
                values[k] = ['2024-01-01 00:00:00' for _ in range(data_count)]
            elif k in ['end_date', 'end_at']:
                values[k] = ['2030-01-01 00:00:00' for _ in range(data_count)]
            # db_ref_columns
            elif k in db_ref_columns:
                values[k] = client_df[db_ref_columns[k]].values
            # どれにも該当しなければ、ダミーデータを入れる
            else:
                values[k] = [dummy_data for _ in range(data_count)]
                dummy_message_lambda(db_schema, k, is_show)
    df = pd.DataFrame(values)

    # 確認用
    # display(df.iloc[:1])
    # print(values)

    # 列ごとの特別対応
    for k in df.columns:
        # 日時の変換
        if k in datetime_columns:
            df[k] = df[k].map(adjust_datetime)

        value = df[k].iloc[0]
        if value in ['True', 'False'] or isinstance(value, (bool, np.bool_)):
            df[k] = df[k].map(bool2Int)

        # dbでenumがスネークケースのままのものがあったので変換
        if k in snake_case_enums:
            df[k] = df[k].map(camel_to_snake)

        # unsignedなど投入できないデータの場合に変換する
        if k in convert_values:
            converted = []
            convert_value = convert_values[k]
            for v in df[k].values:
                if v == convert_value[0]:
                    converted.append(convert_value[1])
                    continue
                converted.append(v)
            df[k] = converted

        # if k in convert_formats:
        #     df[k] = df[k].map(snake_to_upper_camel)

    # csv出力
    df.to_csv(os.path.join('outputs', db_schema + '.csv'), index=False)

    # Replaceクエリ出力
    values = ', \n'.join(['({})'.format(', '.join(['"{}"'.format(v) for v in row])) for index, row in df.iterrows()])
    values = values.replace('"NULL"', 'NULL')
    columns = ['`{}`'.format(s) for s in df.columns]
    query = 'REPLACE INTO {} ({}) \nVALUES {}'.format(db_schema, ', '.join(columns), values) + ";"
    queries.append(query)

with open('outputs/query.txt', 'w', encoding='utf-8') as f:
    f.write('\n\n'.join(queries))
    
print('completed');


# # playground

# In[254]:


df


# In[264]:


import numpy as np
isinstance(df['is_bonus'].iloc[0], (bool, np.bool_))


# In[206]:


bool('False')


# In[207]:


bool(1)


# In[208]:


bool(0)


# In[210]:


bool(True), bool(False)


# In[236]:


datetime.datetime.strptime('2024/01/01 9:00:00', '%Y/%m/%d %H:%M:%S')


# In[246]:


1 == True


# In[ ]:




