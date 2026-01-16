# glow-brain-gemini 全ソースコード (Part 2)

生成日時: 2026-01-16 14:52:47

---

<!-- FILE: ./projects/glow-masterdata/MstStage.csv -->
## ./projects/glow-masterdata/MstStage.csv

```csv
ENABLE,id,mst_quest_id,mst_in_game_id,stage_number,recommended_level,cost_stamina,exp,coin,prev_mst_stage_id,mst_stage_tips_group_id,auto_lap_type,max_auto_lap_count,sort_order,asset_key,mst_stage_limit_status_id,release_key,mst_artwork_fragment_drop_group_id,start_at,end_at
e,develop_001,develop,develop_001,1,1,5,500,1000,,1,__NULL__,1,2147483647,develop_001,,999999999,__NULL__,"2024-01-01 0:00:00","2030-01-01 0:00:00"
e,develop_002,develop,develop_002,1,1,5,500,1000,,1,__NULL__,1,2147483647,develop_002,,999999999,__NULL__,"2024-01-01 0:00:00","2030-01-01 0:00:00"
e,develop_plan_test_stage001,plan_test_stage001,plan_test_stage001,1,1,5,500,1000,,1,__NULL__,1,2147483647,plan_test_stage001,,999999999,__NULL__,"2024-01-01 0:00:00","2030-01-01 0:00:00"
e,develop_plan_test_stage002,plan_test_stage002,plan_test_stage002,1,1,5,500,1000,,1,__NULL__,1,2147483647,plan_test_stage002,,999999999,__NULL__,"2024-01-01 0:00:00","2030-01-01 0:00:00"
e,develop_plan_test_stage003,plan_test_stage003,plan_test_stage003,1,1,5,500,1000,,1,__NULL__,1,2147483647,plan_test_stage003,,999999999,__NULL__,"2024-01-01 0:00:00","2030-01-01 0:00:00"
e,develop_plan_test_stage004,plan_test_stage004,plan_test_stage004,1,1,5,500,1000,,1,__NULL__,1,2147483647,plan_test_stage003,,999999999,__NULL__,"2024-01-01 0:00:00","2030-01-01 0:00:00"
e,develop_plan_test_stage004_red,plan_test_stage_004_red,plan_test_stage_004_red,1,1,5,500,1000,,1,__NULL__,1,2147483647,plan_test_stage_004_red,,999999999,__NULL__,"2024-01-01 0:00:00","2030-01-01 0:00:00"
e,develop_plan_test_stage005_blue,plan_test_stage_005_blue,plan_test_stage_005_blue,1,1,5,500,1000,,1,__NULL__,1,2147483647,plan_test_stage_005_blue,,999999999,__NULL__,"2024-01-01 0:00:00","2030-01-01 0:00:00"
e,develop_plan_test_stage006_yellow,plan_test_stage_006_yellow,plan_test_stage_006_yellow,1,1,5,500,1000,,1,__NULL__,1,2147483647,plan_test_stage_006_yellow,,999999999,__NULL__,"2024-01-01 0:00:00","2030-01-01 0:00:00"
e,develop_plan_test_stage007_green,plan_test_stage_007_green,plan_test_stage_007_green,1,1,5,500,1000,,1,__NULL__,1,2147483647,plan_test_stage_007_green,,999999999,__NULL__,"2024-01-01 0:00:00","2030-01-01 0:00:00"
e,develop_plan_test_stage008_colorless,plan_test_stage_008_colorless,plan_test_stage_008_colorless,1,1,5,500,1000,,1,__NULL__,1,2147483647,plan_test_stage_008_colorless,,999999999,__NULL__,"2024-01-01 0:00:00","2030-01-01 0:00:00"
e,plan_test_stage_powerup01,plan_test_stage_powerup01,plan_test_stage_powerup01,1,1,5,500,1000,,1,__NULL__,1,2147483647,plan_test_stage_powerup01,,999999999,__NULL__,"2024-01-01 0:00:00","2030-01-01 0:00:00"
e,plan_test_stage_powerdown01,plan_test_stage_powerdown01,plan_test_stage_powerdown01,1,1,5,500,1000,,1,__NULL__,1,2147483647,plan_test_stage_powerdown01,,999999999,__NULL__,"2024-01-01 0:00:00","2030-01-01 0:00:00"
e,plan_test_stage_slipdamage01,plan_test_stage_slipdamage01,plan_test_stage_slipdamage01,1,1,5,500,1000,,1,__NULL__,1,2147483647,plan_test_stage_slipdamage01,,999999999,__NULL__,"2024-01-01 0:00:00","2030-01-01 0:00:00"
e,plan_test_stage_gust01,plan_test_stage_gust01,plan_test_stage_gust01,1,1,5,500,1000,,1,__NULL__,1,2147483647,plan_test_stage_gust01,,999999999,__NULL__,"2024-01-01 0:00:00","2030-01-01 0:00:00"
e,plan_test_stage_poison01,plan_test_stage_poison01,plan_test_stage_poison01,1,1,5,500,1000,,1,__NULL__,1,2147483647,plan_test_stage_poison01,,999999999,__NULL__,"2024-01-01 0:00:00","2030-01-01 0:00:00"
e,plan_test_stage_normal01,plan_test_stage_normal01,plan_test_stage_normal01,1,1,5,500,1000,,1,__NULL__,1,2147483647,plan_test_stage_normal01,,999999999,__NULL__,"2024-01-01 0:00:00","2030-01-01 0:00:00"
e,plan_test_stage_normal01_02,plan_test_stage_normal01_02,plan_test_stage_normal01,1,1,5,500,1000,,1,__NULL__,1,2147483647,plan_test_stage_normal01,,999999999,__NULL__,"2024-01-01 0:00:00","2030-01-01 0:00:00"
e,plan_test_stage_normal01_03,plan_test_stage_normal01_03,plan_test_stage_normal01,1,1,5,500,1000,,1,__NULL__,1,2147483647,plan_test_stage_normal01,,999999999,__NULL__,"2024-01-01 0:00:00","2030-01-01 0:00:00"
e,plan_test_stage_normal01_04,plan_test_stage_normal01_04,plan_test_stage_normal01,1,1,5,500,1000,,1,__NULL__,1,2147483647,plan_test_stage_normal01,,999999999,__NULL__,"2024-01-01 0:00:00","2030-01-01 0:00:00"
e,tutorial_1,tutorial,tutorial_1,1,1,0,50,0,,1,__NULL__,1,997,general_coin,,202509010,__NULL__,"2024-01-01 0:00:00","2030-01-01 0:00:00"
e,tutorial_2,tutorial,tutorial_2,2,1,0,50,0,,1,__NULL__,1,998,general_coin,,202509010,__NULL__,"2024-01-01 0:00:00","2030-01-01 0:00:00"
e,tutorial_3,tutorial,tutorial_3,3,1,0,50,0,tutorial_2,1,__NULL__,1,999,general_coin,,202509010,__NULL__,"2024-01-01 0:00:00","2030-01-01 0:00:00"
e,normal_spy_00001,quest_main_spy_normal_1,normal_spy_00001,1,1,5,50,50,,1,AfterClear,5,1,spy_normal_01,,202509010,spy_a_0001,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,normal_spy_00002,quest_main_spy_normal_1,normal_spy_00002,2,1,5,50,50,normal_spy_00001,1,AfterClear,5,2,spy_normal_02,,202509010,spy_a_0002,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,normal_spy_00003,quest_main_spy_normal_1,normal_spy_00003,3,1,5,50,50,normal_spy_00002,1,AfterClear,5,3,spy_normal_03,,202509010,spy_a_0003,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,normal_spy_00004,quest_main_spy_normal_1,normal_spy_00004,4,1,5,50,50,normal_spy_00003,1,AfterClear,5,4,spy_normal_04,,202509010,spy_a_0004,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,normal_spy_00005,quest_main_spy_normal_1,normal_spy_00005,5,1,5,50,50,normal_spy_00004,1,AfterClear,5,5,spy_normal_05,,202509010,spy_a_0005,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,normal_spy_00006,quest_main_spy_normal_1,normal_spy_00006,6,1,5,50,50,normal_spy_00005,1,AfterClear,5,6,spy_normal_06,,202509010,spy_a_0006,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,hard_spy_00001,quest_main_spy_hard_1,hard_spy_00001,1,40,20,200,300,normal_spy_00006,1,AfterClear,5,7,spy_normal_01,,202509010,spy_b_0001,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,hard_spy_00002,quest_main_spy_hard_1,hard_spy_00002,2,40,20,200,300,hard_spy_00001,1,AfterClear,5,8,spy_normal_02,,202509010,spy_b_0002,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,hard_spy_00003,quest_main_spy_hard_1,hard_spy_00003,3,40,20,200,300,hard_spy_00002,1,AfterClear,5,9,spy_normal_03,,202509010,spy_b_0003,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,hard_spy_00004,quest_main_spy_hard_1,hard_spy_00004,4,40,20,200,300,hard_spy_00003,1,AfterClear,5,10,spy_normal_04,,202509010,spy_b_0004,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,hard_spy_00005,quest_main_spy_hard_1,hard_spy_00005,5,40,20,200,300,hard_spy_00004,1,AfterClear,5,11,spy_normal_05,,202509010,spy_b_0005,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,hard_spy_00006,quest_main_spy_hard_1,hard_spy_00006,6,40,20,200,300,hard_spy_00005,1,AfterClear,5,12,spy_normal_06,,202509010,spy_b_0006,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,veryhard_spy_00001,quest_main_spy_veryhard_1,veryhard_spy_00001,1,60,30,300,750,hard_spy_00006,1,__NULL__,1,13,spy_normal_01,,202509010,spy_c_0001,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,veryhard_spy_00002,quest_main_spy_veryhard_1,veryhard_spy_00002,2,60,30,300,750,veryhard_spy_00001,1,__NULL__,1,14,spy_normal_02,,202509010,spy_c_0002,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,veryhard_spy_00003,quest_main_spy_veryhard_1,veryhard_spy_00003,3,60,30,300,750,veryhard_spy_00002,1,__NULL__,1,15,spy_normal_03,,202509010,spy_c_0003,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,veryhard_spy_00004,quest_main_spy_veryhard_1,veryhard_spy_00004,4,60,30,300,750,veryhard_spy_00003,1,__NULL__,1,16,spy_normal_04,,202509010,spy_c_0004,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,veryhard_spy_00005,quest_main_spy_veryhard_1,veryhard_spy_00005,5,60,30,300,750,veryhard_spy_00004,1,__NULL__,1,17,spy_normal_05,,202509010,spy_c_0005,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,veryhard_spy_00006,quest_main_spy_veryhard_1,veryhard_spy_00006,6,60,30,300,750,veryhard_spy_00005,1,__NULL__,1,18,spy_normal_06,,202509010,spy_c_0006,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,normal_gom_00001,quest_main_gom_normal_2,normal_gom_00001,1,2,5,50,50,normal_spy_00006,1,AfterClear,5,19,gom_normal_01,,202509010,gom_a_0001,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,normal_gom_00002,quest_main_gom_normal_2,normal_gom_00002,2,2,5,50,50,normal_gom_00001,1,AfterClear,5,20,gom_normal_02,,202509010,gom_a_0002,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,normal_gom_00003,quest_main_gom_normal_2,normal_gom_00003,3,2,5,50,50,normal_gom_00002,1,AfterClear,5,21,gom_normal_03,,202509010,gom_a_0003,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,normal_gom_00004,quest_main_gom_normal_2,normal_gom_00004,4,2,5,50,50,normal_gom_00003,1,AfterClear,5,22,gom_normal_04,,202509010,gom_a_0004,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,normal_gom_00005,quest_main_gom_normal_2,normal_gom_00005,5,2,5,50,50,normal_gom_00004,1,AfterClear,5,23,gom_normal_05,,202509010,gom_a_0005,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,normal_gom_00006,quest_main_gom_normal_2,normal_gom_00006,6,2,5,50,50,normal_gom_00005,1,AfterClear,5,24,gom_normal_06,,202509010,gom_a_0006,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,hard_gom_00001,quest_main_gom_hard_2,hard_gom_00001,1,40,20,200,300,normal_gom_00006,1,AfterClear,5,25,gom_normal_01,,202509010,gom_b_0001,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,hard_gom_00002,quest_main_gom_hard_2,hard_gom_00002,2,40,20,200,300,hard_gom_00001,1,AfterClear,5,26,gom_normal_02,,202509010,gom_b_0002,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,hard_gom_00003,quest_main_gom_hard_2,hard_gom_00003,3,40,20,200,300,hard_gom_00002,1,AfterClear,5,27,gom_normal_03,,202509010,gom_b_0003,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,hard_gom_00004,quest_main_gom_hard_2,hard_gom_00004,4,40,20,200,300,hard_gom_00003,1,AfterClear,5,28,gom_normal_04,,202509010,gom_b_0004,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,hard_gom_00005,quest_main_gom_hard_2,hard_gom_00005,5,40,20,200,300,hard_gom_00004,1,AfterClear,5,29,gom_normal_05,,202509010,gom_b_0005,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,hard_gom_00006,quest_main_gom_hard_2,hard_gom_00006,6,40,20,200,300,hard_gom_00005,1,AfterClear,5,30,gom_normal_06,,202509010,gom_b_0006,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,veryhard_gom_00001,quest_main_gom_veryhard_2,veryhard_gom_00001,1,60,30,300,750,hard_gom_00006,1,__NULL__,1,31,gom_normal_01,,202509010,gom_c_0001,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,veryhard_gom_00002,quest_main_gom_veryhard_2,veryhard_gom_00002,2,60,30,300,750,veryhard_gom_00001,1,__NULL__,1,32,gom_normal_02,,202509010,gom_c_0002,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,veryhard_gom_00003,quest_main_gom_veryhard_2,veryhard_gom_00003,3,60,30,300,750,veryhard_gom_00002,1,__NULL__,1,33,gom_normal_03,,202509010,gom_c_0003,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,veryhard_gom_00004,quest_main_gom_veryhard_2,veryhard_gom_00004,4,60,30,300,750,veryhard_gom_00003,1,__NULL__,1,34,gom_normal_04,,202509010,gom_c_0004,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,veryhard_gom_00005,quest_main_gom_veryhard_2,veryhard_gom_00005,5,60,30,300,750,veryhard_gom_00004,1,__NULL__,1,35,gom_normal_05,,202509010,gom_c_0005,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,veryhard_gom_00006,quest_main_gom_veryhard_2,veryhard_gom_00006,6,60,30,300,750,veryhard_gom_00005,1,__NULL__,1,36,gom_normal_06,,202509010,gom_c_0006,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,normal_aka_00001,quest_main_aka_normal_3,normal_aka_00001,1,3,5,50,50,normal_gom_00006,1,AfterClear,5,37,aka_normal_01,,202509010,aka_a_0001,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,normal_aka_00002,quest_main_aka_normal_3,normal_aka_00002,2,3,5,50,50,normal_aka_00001,1,AfterClear,5,38,aka_normal_02,,202509010,aka_a_0002,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,normal_aka_00003,quest_main_aka_normal_3,normal_aka_00003,3,3,5,50,50,normal_aka_00002,1,AfterClear,5,39,aka_normal_03,,202509010,aka_a_0003,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,hard_aka_00001,quest_main_aka_hard_3,hard_aka_00001,1,40,20,200,300,normal_aka_00003,1,AfterClear,5,40,aka_normal_01,,202509010,aka_b_0001,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,hard_aka_00002,quest_main_aka_hard_3,hard_aka_00002,2,40,20,200,300,hard_aka_00001,1,AfterClear,5,41,aka_normal_02,,202509010,aka_b_0002,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,hard_aka_00003,quest_main_aka_hard_3,hard_aka_00003,3,40,20,200,300,hard_aka_00002,1,AfterClear,5,42,aka_normal_03,,202509010,aka_b_0003,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,veryhard_aka_00001,quest_main_aka_veryhard_3,veryhard_aka_00001,1,60,30,300,750,hard_aka_00003,1,__NULL__,1,43,aka_normal_01,,202509010,aka_c_0001,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,veryhard_aka_00002,quest_main_aka_veryhard_3,veryhard_aka_00002,2,60,30,300,750,veryhard_aka_00001,1,__NULL__,1,44,aka_normal_02,,202509010,aka_c_0002,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,veryhard_aka_00003,quest_main_aka_veryhard_3,veryhard_aka_00003,3,60,30,300,750,veryhard_aka_00002,1,__NULL__,1,45,aka_normal_03,,202509010,aka_c_0003,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,normal_glo1_00001,quest_main_glo1_normal_4,normal_glo1_00001,1,5,10,100,100,normal_aka_00003,1,AfterClear,5,46,general_fragment_00001,,202509010,__NULL__,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,normal_glo1_00002,quest_main_glo1_normal_4,normal_glo1_00002,2,5,10,100,100,normal_glo1_00001,1,AfterClear,5,47,general_fragment_00001,,202509010,__NULL__,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,normal_glo1_00003,quest_main_glo1_normal_4,normal_glo1_00003,3,5,10,100,100,normal_glo1_00002,1,AfterClear,5,48,general_diamond,,202509010,__NULL__,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,hard_glo1_00001,quest_main_glo1_hard_4,hard_glo1_00001,1,50,20,200,300,normal_glo1_00003,1,AfterClear,5,49,general_fragment_00002,,202509010,__NULL__,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,hard_glo1_00002,quest_main_glo1_hard_4,hard_glo1_00002,2,50,20,200,300,hard_glo1_00001,1,AfterClear,5,50,general_fragment_00002,,202509010,__NULL__,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,hard_glo1_00003,quest_main_glo1_hard_4,hard_glo1_00003,3,50,20,200,300,hard_glo1_00002,1,AfterClear,5,51,general_diamond,,202509010,__NULL__,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,veryhard_glo1_00001,quest_main_glo1_veryhard_4,veryhard_glo1_00001,1,60,30,300,750,hard_glo1_00003,1,__NULL__,1,52,general_diamond,,202509010,__NULL__,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,veryhard_glo1_00002,quest_main_glo1_veryhard_4,veryhard_glo1_00002,2,60,30,300,750,veryhard_glo1_00001,1,__NULL__,1,53,general_diamond,,202509010,__NULL__,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,veryhard_glo1_00003,quest_main_glo1_veryhard_4,veryhard_glo1_00003,3,60,30,300,750,veryhard_glo1_00002,1,__NULL__,1,54,general_diamond,,202509010,__NULL__,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,normal_dan_00001,quest_main_dan_normal_5,normal_dan_00001,1,7,10,100,100,normal_glo1_00003,1,AfterClear,5,55,dan_normal_01,,202509010,dan_a_0001,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,normal_dan_00002,quest_main_dan_normal_5,normal_dan_00002,2,7,10,100,100,normal_dan_00001,1,AfterClear,5,56,dan_normal_02,,202509010,dan_a_0002,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,normal_dan_00003,quest_main_dan_normal_5,normal_dan_00003,3,7,10,100,100,normal_dan_00002,1,AfterClear,5,57,dan_normal_03,,202509010,dan_a_0003,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,normal_dan_00004,quest_main_dan_normal_5,normal_dan_00004,4,7,10,100,100,normal_dan_00003,1,AfterClear,5,58,dan_normal_04,,202509010,dan_a_0004,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,normal_dan_00005,quest_main_dan_normal_5,normal_dan_00005,5,7,10,100,100,normal_dan_00004,1,AfterClear,5,59,dan_normal_05,,202509010,dan_a_0005,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,normal_dan_00006,quest_main_dan_normal_5,normal_dan_00006,6,7,10,100,100,normal_dan_00005,1,AfterClear,5,60,dan_normal_06,,202509010,dan_a_0006,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,hard_dan_00001,quest_main_dan_hard_5,hard_dan_00001,1,50,20,200,300,normal_dan_00006,1,AfterClear,5,61,dan_normal_01,,202509010,dan_b_0001,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,hard_dan_00002,quest_main_dan_hard_5,hard_dan_00002,2,50,20,200,300,hard_dan_00001,1,AfterClear,5,62,dan_normal_02,,202509010,dan_b_0002,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,hard_dan_00003,quest_main_dan_hard_5,hard_dan_00003,3,50,20,200,300,hard_dan_00002,1,AfterClear,5,63,dan_normal_03,,202509010,dan_b_0003,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,hard_dan_00004,quest_main_dan_hard_5,hard_dan_00004,4,50,20,200,300,hard_dan_00003,1,AfterClear,5,64,dan_normal_04,,202509010,dan_b_0004,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,hard_dan_00005,quest_main_dan_hard_5,hard_dan_00005,5,50,20,200,300,hard_dan_00004,1,AfterClear,5,65,dan_normal_05,,202509010,dan_b_0005,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,hard_dan_00006,quest_main_dan_hard_5,hard_dan_00006,6,50,20,200,300,hard_dan_00005,1,AfterClear,5,66,dan_normal_06,,202509010,dan_b_0006,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,veryhard_dan_00001,quest_main_dan_veryhard_5,veryhard_dan_00001,1,60,30,300,750,hard_dan_00006,1,__NULL__,1,67,dan_normal_01,,202509010,dan_c_0001,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,veryhard_dan_00002,quest_main_dan_veryhard_5,veryhard_dan_00002,2,60,30,300,750,veryhard_dan_00001,1,__NULL__,1,68,dan_normal_02,,202509010,dan_c_0002,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,veryhard_dan_00003,quest_main_dan_veryhard_5,veryhard_dan_00003,3,60,30,300,750,veryhard_dan_00002,1,__NULL__,1,69,dan_normal_03,,202509010,dan_c_0003,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,veryhard_dan_00004,quest_main_dan_veryhard_5,veryhard_dan_00004,4,60,30,300,750,veryhard_dan_00003,1,__NULL__,1,70,dan_normal_04,,202509010,dan_c_0004,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,veryhard_dan_00005,quest_main_dan_veryhard_5,veryhard_dan_00005,5,60,30,300,750,veryhard_dan_00004,1,__NULL__,1,71,dan_normal_05,,202509010,dan_c_0005,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,veryhard_dan_00006,quest_main_dan_veryhard_5,veryhard_dan_00006,6,60,30,300,750,veryhard_dan_00005,1,__NULL__,1,72,dan_normal_06,,202509010,dan_c_0006,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,normal_jig_00001,quest_main_jig_normal_6,normal_jig_00001,1,10,10,100,100,normal_dan_00006,1,AfterClear,5,73,jig_normal_01,,202509010,jig_a_0001,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,normal_jig_00002,quest_main_jig_normal_6,normal_jig_00002,2,10,10,100,100,normal_jig_00001,1,AfterClear,5,74,jig_normal_02,,202509010,jig_a_0002,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,normal_jig_00003,quest_main_jig_normal_6,normal_jig_00003,3,10,10,100,100,normal_jig_00002,1,AfterClear,5,75,jig_normal_03,,202509010,jig_a_0003,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,normal_jig_00004,quest_main_jig_normal_6,normal_jig_00004,4,10,10,100,100,normal_jig_00003,1,AfterClear,5,76,jig_normal_04,,202509010,jig_a_0004,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,normal_jig_00005,quest_main_jig_normal_6,normal_jig_00005,5,10,10,100,100,normal_jig_00004,1,AfterClear,5,77,jig_normal_05,,202509010,jig_a_0005,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,normal_jig_00006,quest_main_jig_normal_6,normal_jig_00006,6,10,10,100,100,normal_jig_00005,1,AfterClear,5,78,jig_normal_06,,202509010,jig_a_0006,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,hard_jig_00001,quest_main_jig_hard_6,hard_jig_00001,1,50,20,200,300,normal_jig_00006,1,AfterClear,5,79,jig_normal_01,,202509010,jig_b_0001,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,hard_jig_00002,quest_main_jig_hard_6,hard_jig_00002,2,50,20,200,300,hard_jig_00001,1,AfterClear,5,80,jig_normal_02,,202509010,jig_b_0002,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,hard_jig_00003,quest_main_jig_hard_6,hard_jig_00003,3,50,20,200,300,hard_jig_00002,1,AfterClear,5,81,jig_normal_03,,202509010,jig_b_0003,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,hard_jig_00004,quest_main_jig_hard_6,hard_jig_00004,4,50,20,200,300,hard_jig_00003,1,AfterClear,5,82,jig_normal_04,,202509010,jig_b_0004,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,hard_jig_00005,quest_main_jig_hard_6,hard_jig_00005,5,50,20,200,300,hard_jig_00004,1,AfterClear,5,83,jig_normal_05,,202509010,jig_b_0005,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,hard_jig_00006,quest_main_jig_hard_6,hard_jig_00006,6,50,20,200,300,hard_jig_00005,1,AfterClear,5,84,jig_normal_06,,202509010,jig_b_0006,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,veryhard_jig_00001,quest_main_jig_veryhard_6,veryhard_jig_00001,1,70,30,300,750,hard_jig_00006,1,__NULL__,1,85,jig_normal_01,,202509010,jig_c_0001,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,veryhard_jig_00002,quest_main_jig_veryhard_6,veryhard_jig_00002,2,70,30,300,750,veryhard_jig_00001,1,__NULL__,1,86,jig_normal_02,,202509010,jig_c_0002,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,veryhard_jig_00003,quest_main_jig_veryhard_6,veryhard_jig_00003,3,70,30,300,750,veryhard_jig_00002,1,__NULL__,1,87,jig_normal_03,,202509010,jig_c_0003,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,veryhard_jig_00004,quest_main_jig_veryhard_6,veryhard_jig_00004,4,70,30,300,750,veryhard_jig_00003,1,__NULL__,1,88,jig_normal_04,,202509010,jig_c_0004,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,veryhard_jig_00005,quest_main_jig_veryhard_6,veryhard_jig_00005,5,70,30,300,750,veryhard_jig_00004,1,__NULL__,1,89,jig_normal_05,,202509010,jig_c_0005,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,veryhard_jig_00006,quest_main_jig_veryhard_6,veryhard_jig_00006,6,70,30,300,750,veryhard_jig_00005,1,__NULL__,1,90,jig_normal_06,,202509010,jig_c_0006,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,normal_tak_00001,quest_main_tak_normal_7,normal_tak_00001,1,15,15,150,150,normal_jig_00006,1,AfterClear,5,91,tak_normal_01,,202509010,tak_a_0001,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,normal_tak_00002,quest_main_tak_normal_7,normal_tak_00002,2,15,15,150,150,normal_tak_00001,1,AfterClear,5,92,tak_normal_02,,202509010,tak_a_0002,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,normal_tak_00003,quest_main_tak_normal_7,normal_tak_00003,3,15,15,150,150,normal_tak_00002,1,AfterClear,5,93,tak_normal_03,,202509010,tak_a_0003,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,hard_tak_00001,quest_main_tak_hard_7,hard_tak_00001,1,60,30,300,450,normal_tak_00003,1,AfterClear,5,94,tak_normal_01,,202509010,tak_b_0001,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,hard_tak_00002,quest_main_tak_hard_7,hard_tak_00002,2,60,30,300,450,hard_tak_00001,1,AfterClear,5,95,tak_normal_02,,202509010,tak_b_0002,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,hard_tak_00003,quest_main_tak_hard_7,hard_tak_00003,3,60,30,300,450,hard_tak_00002,1,AfterClear,5,96,tak_normal_03,,202509010,tak_b_0003,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,veryhard_tak_00001,quest_main_tak_veryhard_7,veryhard_tak_00001,1,70,30,300,750,hard_tak_00003,1,__NULL__,1,97,tak_normal_01,,202509010,tak_c_0001,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,veryhard_tak_00002,quest_main_tak_veryhard_7,veryhard_tak_00002,2,70,30,300,750,veryhard_tak_00001,1,__NULL__,1,98,tak_normal_02,,202509010,tak_c_0002,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,veryhard_tak_00003,quest_main_tak_veryhard_7,veryhard_tak_00003,3,70,30,300,750,veryhard_tak_00002,1,__NULL__,1,99,tak_normal_03,,202509010,tak_c_0003,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,normal_glo2_00001,quest_main_glo2_normal_8,normal_glo2_00001,1,20,15,150,150,normal_tak_00003,1,AfterClear,5,100,general_fragment_00001,,202509010,__NULL__,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,normal_glo2_00002,quest_main_glo2_normal_8,normal_glo2_00002,2,20,15,150,150,normal_glo2_00001,1,AfterClear,5,101,general_fragment_00001,,202509010,__NULL__,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,normal_glo2_00003,quest_main_glo2_normal_8,normal_glo2_00003,3,20,15,150,150,normal_glo2_00002,1,AfterClear,5,102,general_diamond,,202509010,__NULL__,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,hard_glo2_00001,quest_main_glo2_hard_8,hard_glo2_00001,1,60,30,300,450,normal_glo2_00003,1,AfterClear,5,103,general_fragment_00002,,202509010,__NULL__,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,hard_glo2_00002,quest_main_glo2_hard_8,hard_glo2_00002,2,60,30,300,450,hard_glo2_00001,1,AfterClear,5,104,general_fragment_00002,,202509010,__NULL__,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,hard_glo2_00003,quest_main_glo2_hard_8,hard_glo2_00003,3,60,30,300,450,hard_glo2_00002,1,AfterClear,5,105,general_diamond,,202509010,__NULL__,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,veryhard_glo2_00001,quest_main_glo2_veryhard_8,veryhard_glo2_00001,1,70,30,300,750,hard_glo2_00003,1,__NULL__,1,106,general_diamond,,202509010,__NULL__,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,veryhard_glo2_00002,quest_main_glo2_veryhard_8,veryhard_glo2_00002,2,70,30,300,750,veryhard_glo2_00001,1,__NULL__,1,107,general_diamond,,202509010,__NULL__,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,veryhard_glo2_00003,quest_main_glo2_veryhard_8,veryhard_glo2_00003,3,70,30,300,750,veryhard_glo2_00002,1,__NULL__,1,108,general_diamond,,202509010,__NULL__,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,normal_chi_00001,quest_main_chi_normal_9,normal_chi_00001,1,25,15,150,150,normal_glo2_00003,1,AfterClear,5,109,chi_normal_01,,202509010,chi_a_0001,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,normal_chi_00002,quest_main_chi_normal_9,normal_chi_00002,2,25,15,150,150,normal_chi_00001,1,AfterClear,5,110,chi_normal_02,,202509010,chi_a_0002,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,normal_chi_00003,quest_main_chi_normal_9,normal_chi_00003,3,25,15,150,150,normal_chi_00002,1,AfterClear,5,111,chi_normal_03,,202509010,chi_a_0003,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,normal_chi_00004,quest_main_chi_normal_9,normal_chi_00004,4,25,15,150,150,normal_chi_00003,1,AfterClear,5,112,chi_normal_04,,202509010,chi_a_0004,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,normal_chi_00005,quest_main_chi_normal_9,normal_chi_00005,5,25,15,150,150,normal_chi_00004,1,AfterClear,5,113,chi_normal_05,,202509010,chi_a_0005,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,normal_chi_00006,quest_main_chi_normal_9,normal_chi_00006,6,25,15,150,150,normal_chi_00005,1,AfterClear,5,114,chi_normal_06,,202509010,chi_a_0006,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,hard_chi_00001,quest_main_chi_hard_9,hard_chi_00001,1,60,30,300,450,normal_chi_00006,1,AfterClear,5,115,chi_normal_01,,202509010,chi_b_0001,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,hard_chi_00002,quest_main_chi_hard_9,hard_chi_00002,2,60,30,300,450,hard_chi_00001,1,AfterClear,5,116,chi_normal_02,,202509010,chi_b_0002,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,hard_chi_00003,quest_main_chi_hard_9,hard_chi_00003,3,60,30,300,450,hard_chi_00002,1,AfterClear,5,117,chi_normal_03,,202509010,chi_b_0003,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,hard_chi_00004,quest_main_chi_hard_9,hard_chi_00004,4,60,30,300,450,hard_chi_00003,1,AfterClear,5,118,chi_normal_04,,202509010,chi_b_0004,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,hard_chi_00005,quest_main_chi_hard_9,hard_chi_00005,5,60,30,300,450,hard_chi_00004,1,AfterClear,5,119,chi_normal_05,,202509010,chi_b_0005,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,hard_chi_00006,quest_main_chi_hard_9,hard_chi_00006,6,60,30,300,450,hard_chi_00005,1,AfterClear,5,120,chi_normal_06,,202509010,chi_b_0006,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,veryhard_chi_00001,quest_main_chi_veryhard_9,veryhard_chi_00001,1,70,40,400,1000,hard_chi_00006,1,__NULL__,1,121,chi_normal_01,,202509010,chi_c_0001,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,veryhard_chi_00002,quest_main_chi_veryhard_9,veryhard_chi_00002,2,70,40,400,1000,veryhard_chi_00001,1,__NULL__,1,122,chi_normal_02,,202509010,chi_c_0002,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,veryhard_chi_00003,quest_main_chi_veryhard_9,veryhard_chi_00003,3,70,40,400,1000,veryhard_chi_00002,1,__NULL__,1,123,chi_normal_03,,202509010,chi_c_0003,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,veryhard_chi_00004,quest_main_chi_veryhard_9,veryhard_chi_00004,4,70,40,400,1000,veryhard_chi_00003,1,__NULL__,1,124,chi_normal_04,,202509010,chi_c_0004,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,veryhard_chi_00005,quest_main_chi_veryhard_9,veryhard_chi_00005,5,70,40,400,1000,veryhard_chi_00004,1,__NULL__,1,125,chi_normal_05,,202509010,chi_c_0005,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,veryhard_chi_00006,quest_main_chi_veryhard_9,veryhard_chi_00006,6,70,40,400,1000,veryhard_chi_00005,1,__NULL__,1,126,chi_normal_06,,202509010,chi_c_0006,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,normal_sur_00001,quest_main_sur_normal_10,normal_sur_00001,1,30,15,150,150,normal_chi_00006,1,AfterClear,5,127,sur_normal_01,,202509010,sur_a_0001,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,normal_sur_00002,quest_main_sur_normal_10,normal_sur_00002,2,30,15,150,150,normal_sur_00001,1,AfterClear,5,128,sur_normal_02,,202509010,sur_a_0002,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,normal_sur_00003,quest_main_sur_normal_10,normal_sur_00003,3,30,15,150,150,normal_sur_00002,1,AfterClear,5,129,sur_normal_03,,202509010,sur_a_0003,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,normal_sur_00004,quest_main_sur_normal_10,normal_sur_00004,4,30,15,150,150,normal_sur_00003,1,AfterClear,5,130,sur_normal_04,,202509010,sur_a_0004,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,normal_sur_00005,quest_main_sur_normal_10,normal_sur_00005,5,30,15,150,150,normal_sur_00004,1,AfterClear,5,131,sur_normal_05,,202509010,sur_a_0005,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,normal_sur_00006,quest_main_sur_normal_10,normal_sur_00006,6,30,15,150,150,normal_sur_00005,1,AfterClear,5,132,sur_normal_06,,202509010,sur_a_0006,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,hard_sur_00001,quest_main_sur_hard_10,hard_sur_00001,1,70,30,300,450,normal_sur_00006,1,AfterClear,5,133,sur_normal_01,,202509010,sur_b_0001,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,hard_sur_00002,quest_main_sur_hard_10,hard_sur_00002,2,70,30,300,450,hard_sur_00001,1,AfterClear,5,134,sur_normal_02,,202509010,sur_b_0002,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,hard_sur_00003,quest_main_sur_hard_10,hard_sur_00003,3,70,30,300,450,hard_sur_00002,1,AfterClear,5,135,sur_normal_03,,202509010,sur_b_0003,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,hard_sur_00004,quest_main_sur_hard_10,hard_sur_00004,4,70,30,300,450,hard_sur_00003,1,AfterClear,5,136,sur_normal_04,,202509010,sur_b_0004,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,hard_sur_00005,quest_main_sur_hard_10,hard_sur_00005,5,70,30,300,450,hard_sur_00004,1,AfterClear,5,137,sur_normal_05,,202509010,sur_b_0005,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,hard_sur_00006,quest_main_sur_hard_10,hard_sur_00006,6,70,30,300,450,hard_sur_00005,1,AfterClear,5,138,sur_normal_06,,202509010,sur_b_0006,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,veryhard_sur_00001,quest_main_sur_veryhard_10,veryhard_sur_00001,1,70,40,400,1000,hard_sur_00006,1,__NULL__,1,139,sur_normal_01,,202509010,sur_c_0001,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,veryhard_sur_00002,quest_main_sur_veryhard_10,veryhard_sur_00002,2,70,40,400,1000,veryhard_sur_00001,1,__NULL__,1,140,sur_normal_02,,202509010,sur_c_0002,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,veryhard_sur_00003,quest_main_sur_veryhard_10,veryhard_sur_00003,3,70,40,400,1000,veryhard_sur_00002,1,__NULL__,1,141,sur_normal_03,,202509010,sur_c_0003,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,veryhard_sur_00004,quest_main_sur_veryhard_10,veryhard_sur_00004,4,70,40,400,1000,veryhard_sur_00003,1,__NULL__,1,142,sur_normal_04,,202509010,sur_c_0004,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,veryhard_sur_00005,quest_main_sur_veryhard_10,veryhard_sur_00005,5,70,40,400,1000,veryhard_sur_00004,1,__NULL__,1,143,sur_normal_05,,202509010,sur_c_0005,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,veryhard_sur_00006,quest_main_sur_veryhard_10,veryhard_sur_00006,6,70,40,400,1000,veryhard_sur_00005,1,__NULL__,1,144,sur_normal_06,,202509010,sur_c_0006,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,normal_rik_00001,quest_main_rik_normal_11,normal_rik_00001,1,35,20,200,200,normal_sur_00006,1,AfterClear,5,145,rik_normal_01,,202509010,rik_a_0001,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,normal_rik_00002,quest_main_rik_normal_11,normal_rik_00002,2,35,20,200,200,normal_rik_00001,1,AfterClear,5,146,rik_normal_02,,202509010,rik_a_0002,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,normal_rik_00003,quest_main_rik_normal_11,normal_rik_00003,3,35,20,200,200,normal_rik_00002,1,AfterClear,5,147,rik_normal_03,,202509010,rik_a_0003,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,hard_rik_00001,quest_main_rik_hard_11,hard_rik_00001,1,70,30,300,450,normal_rik_00003,1,AfterClear,5,148,rik_normal_01,,202509010,rik_b_0001,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,hard_rik_00002,quest_main_rik_hard_11,hard_rik_00002,2,70,30,300,450,hard_rik_00001,1,AfterClear,5,149,rik_normal_02,,202509010,rik_b_0002,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,hard_rik_00003,quest_main_rik_hard_11,hard_rik_00003,3,70,30,300,450,hard_rik_00002,1,AfterClear,5,150,rik_normal_03,,202509010,rik_b_0003,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,veryhard_rik_00001,quest_main_rik_veryhard_11,veryhard_rik_00001,1,70,30,300,750,hard_rik_00003,1,__NULL__,1,151,rik_normal_01,,202509010,rik_c_0001,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,veryhard_rik_00002,quest_main_rik_veryhard_11,veryhard_rik_00002,2,70,30,300,750,veryhard_rik_00001,1,__NULL__,1,152,rik_normal_02,,202509010,rik_c_0002,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,veryhard_rik_00003,quest_main_rik_veryhard_11,veryhard_rik_00003,3,70,30,300,750,veryhard_rik_00002,1,__NULL__,1,153,rik_normal_03,,202509010,rik_c_0003,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,normal_glo3_00001,quest_main_glo3_normal_12,normal_glo3_00001,1,40,20,200,200,normal_rik_00003,1,AfterClear,5,154,general_fragment_00001,,202509010,__NULL__,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,normal_glo3_00002,quest_main_glo3_normal_12,normal_glo3_00002,2,40,20,200,200,normal_glo3_00001,1,AfterClear,5,155,general_fragment_00001,,202509010,__NULL__,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,normal_glo3_00003,quest_main_glo3_normal_12,normal_glo3_00003,3,40,20,200,200,normal_glo3_00002,1,AfterClear,5,156,general_diamond,,202509010,__NULL__,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,hard_glo3_00001,quest_main_glo3_hard_12,hard_glo3_00001,1,70,30,300,450,normal_glo3_00003,1,AfterClear,5,157,general_fragment_00002,,202509010,__NULL__,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,hard_glo3_00002,quest_main_glo3_hard_12,hard_glo3_00002,2,70,30,300,450,hard_glo3_00001,1,AfterClear,5,158,general_fragment_00002,,202509010,__NULL__,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,hard_glo3_00003,quest_main_glo3_hard_12,hard_glo3_00003,3,70,30,300,450,hard_glo3_00002,1,AfterClear,5,159,general_diamond,,202509010,__NULL__,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,veryhard_glo3_00001,quest_main_glo3_veryhard_12,veryhard_glo3_00001,1,80,40,400,1000,hard_glo3_00003,1,__NULL__,1,160,general_diamond,,202509010,__NULL__,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,veryhard_glo3_00002,quest_main_glo3_veryhard_12,veryhard_glo3_00002,2,80,40,400,1000,veryhard_glo3_00001,1,__NULL__,1,161,general_diamond,,202509010,__NULL__,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,veryhard_glo3_00003,quest_main_glo3_veryhard_12,veryhard_glo3_00003,3,80,40,400,1000,veryhard_glo3_00002,1,__NULL__,1,162,general_diamond,,202509010,__NULL__,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,normal_mag_00001,quest_main_mag_normal_13,normal_mag_00001,1,40,20,200,200,normal_glo3_00003,1,AfterClear,5,163,mag_normal_01,,202509010,mag_a_0001,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,normal_mag_00002,quest_main_mag_normal_13,normal_mag_00002,2,40,20,200,200,normal_mag_00001,1,AfterClear,5,164,mag_normal_02,,202509010,mag_a_0002,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,normal_mag_00003,quest_main_mag_normal_13,normal_mag_00003,3,40,20,200,200,normal_mag_00002,1,AfterClear,5,165,mag_normal_03,,202509010,mag_a_0003,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,normal_mag_00004,quest_main_mag_normal_13,normal_mag_00004,4,40,20,200,200,normal_mag_00003,1,AfterClear,5,166,mag_normal_04,,202509010,mag_a_0004,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,normal_mag_00005,quest_main_mag_normal_13,normal_mag_00005,5,40,20,200,200,normal_mag_00004,1,AfterClear,5,167,mag_normal_05,,202509010,mag_a_0005,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,normal_mag_00006,quest_main_mag_normal_13,normal_mag_00006,6,40,20,200,200,normal_mag_00005,1,AfterClear,5,168,mag_normal_06,,202509010,mag_a_0006,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,hard_mag_00001,quest_main_mag_hard_13,hard_mag_00001,1,70,40,400,600,normal_mag_00006,1,AfterClear,5,169,mag_normal_01,,202509010,mag_b_0001,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,hard_mag_00002,quest_main_mag_hard_13,hard_mag_00002,2,70,40,400,600,hard_mag_00001,1,AfterClear,5,170,mag_normal_02,,202509010,mag_b_0002,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,hard_mag_00003,quest_main_mag_hard_13,hard_mag_00003,3,70,40,400,600,hard_mag_00002,1,AfterClear,5,171,mag_normal_03,,202509010,mag_b_0003,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,hard_mag_00004,quest_main_mag_hard_13,hard_mag_00004,4,70,40,400,600,hard_mag_00003,1,AfterClear,5,172,mag_normal_04,,202509010,mag_b_0004,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,hard_mag_00005,quest_main_mag_hard_13,hard_mag_00005,5,70,40,400,600,hard_mag_00004,1,AfterClear,5,173,mag_normal_05,,202509010,mag_b_0005,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,hard_mag_00006,quest_main_mag_hard_13,hard_mag_00006,6,70,40,400,600,hard_mag_00005,1,AfterClear,5,174,mag_normal_06,,202509010,mag_b_0006,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,veryhard_mag_00001,quest_main_mag_veryhard_13,veryhard_mag_00001,1,80,40,400,1000,hard_mag_00006,1,__NULL__,1,175,mag_normal_01,,202509010,mag_c_0001,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,veryhard_mag_00002,quest_main_mag_veryhard_13,veryhard_mag_00002,2,80,40,400,1000,veryhard_mag_00001,1,__NULL__,1,176,mag_normal_02,,202509010,mag_c_0002,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,veryhard_mag_00003,quest_main_mag_veryhard_13,veryhard_mag_00003,3,80,40,400,1000,veryhard_mag_00002,1,__NULL__,1,177,mag_normal_03,,202509010,mag_c_0003,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,veryhard_mag_00004,quest_main_mag_veryhard_13,veryhard_mag_00004,4,80,40,400,1000,veryhard_mag_00003,1,__NULL__,1,178,mag_normal_04,,202509010,mag_c_0004,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,veryhard_mag_00005,quest_main_mag_veryhard_13,veryhard_mag_00005,5,80,40,400,1000,veryhard_mag_00004,1,__NULL__,1,179,mag_normal_05,,202509010,mag_c_0005,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,veryhard_mag_00006,quest_main_mag_veryhard_13,veryhard_mag_00006,6,80,40,400,1000,veryhard_mag_00005,1,__NULL__,1,180,mag_normal_06,,202509010,mag_c_0006,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,normal_sum_00001,quest_main_sum_normal_14,normal_sum_00001,1,45,20,200,200,normal_mag_00006,1,AfterClear,5,181,sum_normal_01,,202509010,sum_a_0001,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,normal_sum_00002,quest_main_sum_normal_14,normal_sum_00002,2,45,20,200,200,normal_sum_00001,1,AfterClear,5,182,sum_normal_02,,202509010,sum_a_0002,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,normal_sum_00003,quest_main_sum_normal_14,normal_sum_00003,3,45,20,200,200,normal_sum_00002,1,AfterClear,5,183,sum_normal_03,,202509010,sum_a_0003,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,normal_sum_00004,quest_main_sum_normal_14,normal_sum_00004,4,45,20,200,200,normal_sum_00003,1,AfterClear,5,184,sum_normal_04,,202509010,sum_a_0004,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,normal_sum_00005,quest_main_sum_normal_14,normal_sum_00005,5,45,20,200,200,normal_sum_00004,1,AfterClear,5,185,sum_normal_05,,202509010,sum_a_0005,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,normal_sum_00006,quest_main_sum_normal_14,normal_sum_00006,6,45,20,200,200,normal_sum_00005,1,AfterClear,5,186,sum_normal_06,,202509010,sum_a_0006,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,hard_sum_00001,quest_main_sum_hard_14,hard_sum_00001,1,75,40,400,600,normal_sum_00006,1,AfterClear,5,187,sum_normal_01,,202509010,sum_b_0001,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,hard_sum_00002,quest_main_sum_hard_14,hard_sum_00002,2,75,40,400,600,hard_sum_00001,1,AfterClear,5,188,sum_normal_02,,202509010,sum_b_0002,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,hard_sum_00003,quest_main_sum_hard_14,hard_sum_00003,3,75,40,400,600,hard_sum_00002,1,AfterClear,5,189,sum_normal_03,,202509010,sum_b_0003,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,hard_sum_00004,quest_main_sum_hard_14,hard_sum_00004,4,75,40,400,600,hard_sum_00003,1,AfterClear,5,190,sum_normal_04,,202509010,sum_b_0004,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,hard_sum_00005,quest_main_sum_hard_14,hard_sum_00005,5,75,40,400,600,hard_sum_00004,1,AfterClear,5,191,sum_normal_05,,202509010,sum_b_0005,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,hard_sum_00006,quest_main_sum_hard_14,hard_sum_00006,6,75,40,400,600,hard_sum_00005,1,AfterClear,5,192,sum_normal_06,,202509010,sum_b_0006,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,veryhard_sum_00001,quest_main_sum_veryhard_14,veryhard_sum_00001,1,80,40,400,1000,hard_sum_00006,1,__NULL__,1,193,sum_normal_01,,202509010,sum_c_0001,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,veryhard_sum_00002,quest_main_sum_veryhard_14,veryhard_sum_00002,2,80,40,400,1000,veryhard_sum_00001,1,__NULL__,1,194,sum_normal_02,,202509010,sum_c_0002,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,veryhard_sum_00003,quest_main_sum_veryhard_14,veryhard_sum_00003,3,80,40,400,1000,veryhard_sum_00002,1,__NULL__,1,195,sum_normal_03,,202509010,sum_c_0003,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,veryhard_sum_00004,quest_main_sum_veryhard_14,veryhard_sum_00004,4,80,40,400,1000,veryhard_sum_00003,1,__NULL__,1,196,sum_normal_04,,202509010,sum_c_0004,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,veryhard_sum_00005,quest_main_sum_veryhard_14,veryhard_sum_00005,5,80,40,400,1000,veryhard_sum_00004,1,__NULL__,1,197,sum_normal_05,,202509010,sum_c_0005,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,veryhard_sum_00006,quest_main_sum_veryhard_14,veryhard_sum_00006,6,80,40,400,1000,veryhard_sum_00005,1,__NULL__,1,198,sum_normal_06,,202509010,sum_c_0006,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,normal_kai_00001,quest_main_kai_normal_15,normal_kai_00001,1,45,25,250,250,normal_sum_00006,1,AfterClear,5,199,kai_normal_01,,202509010,kai_a_0001,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,normal_kai_00002,quest_main_kai_normal_15,normal_kai_00002,2,45,25,250,250,normal_kai_00001,1,AfterClear,5,200,kai_normal_02,,202509010,kai_a_0002,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,normal_kai_00003,quest_main_kai_normal_15,normal_kai_00003,3,45,25,250,250,normal_kai_00002,1,AfterClear,5,201,kai_normal_03,,202509010,kai_a_0003,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,normal_kai_00004,quest_main_kai_normal_15,normal_kai_00004,4,45,25,250,250,normal_kai_00003,1,AfterClear,5,202,kai_normal_04,,202509010,kai_a_0004,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,normal_kai_00005,quest_main_kai_normal_15,normal_kai_00005,5,45,25,250,250,normal_kai_00004,1,AfterClear,5,203,kai_normal_05,,202509010,kai_a_0005,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,normal_kai_00006,quest_main_kai_normal_15,normal_kai_00006,6,45,25,250,250,normal_kai_00005,1,AfterClear,5,204,kai_normal_06,,202509010,kai_a_0006,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,hard_kai_00001,quest_main_kai_hard_15,hard_kai_00001,1,80,40,400,600,normal_kai_00006,1,AfterClear,5,205,kai_normal_01,,202509010,kai_b_0001,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,hard_kai_00002,quest_main_kai_hard_15,hard_kai_00002,2,80,40,400,600,hard_kai_00001,1,AfterClear,5,206,kai_normal_02,,202509010,kai_b_0002,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,hard_kai_00003,quest_main_kai_hard_15,hard_kai_00003,3,80,40,400,600,hard_kai_00002,1,AfterClear,5,207,kai_normal_03,,202509010,kai_b_0003,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,hard_kai_00004,quest_main_kai_hard_15,hard_kai_00004,4,80,40,400,600,hard_kai_00003,1,AfterClear,5,208,kai_normal_04,,202509010,kai_b_0004,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,hard_kai_00005,quest_main_kai_hard_15,hard_kai_00005,5,80,40,400,600,hard_kai_00004,1,AfterClear,5,209,kai_normal_05,,202509010,kai_b_0005,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,hard_kai_00006,quest_main_kai_hard_15,hard_kai_00006,6,80,40,400,600,hard_kai_00005,1,AfterClear,5,210,kai_normal_06,,202509010,kai_b_0006,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,veryhard_kai_00001,quest_main_kai_veryhard_15,veryhard_kai_00001,1,80,40,400,1000,hard_kai_00006,1,__NULL__,1,211,kai_normal_01,,202509010,kai_c_0001,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,veryhard_kai_00002,quest_main_kai_veryhard_15,veryhard_kai_00002,2,80,40,400,1000,veryhard_kai_00001,1,__NULL__,1,212,kai_normal_02,,202509010,kai_c_0002,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,veryhard_kai_00003,quest_main_kai_veryhard_15,veryhard_kai_00003,3,80,40,400,1000,veryhard_kai_00002,1,__NULL__,1,213,kai_normal_03,,202509010,kai_c_0003,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,veryhard_kai_00004,quest_main_kai_veryhard_15,veryhard_kai_00004,4,80,40,400,1000,veryhard_kai_00003,1,__NULL__,1,214,kai_normal_04,,202509010,kai_c_0004,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,veryhard_kai_00005,quest_main_kai_veryhard_15,veryhard_kai_00005,5,80,40,400,1000,veryhard_kai_00004,1,__NULL__,1,215,kai_normal_05,,202509010,kai_c_0005,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,veryhard_kai_00006,quest_main_kai_veryhard_15,veryhard_kai_00006,6,80,40,400,1000,veryhard_kai_00005,1,__NULL__,1,216,kai_normal_06,,202509010,kai_c_0006,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,normal_glo4_00001,quest_main_glo4_normal_16,normal_glo4_00001,1,50,25,250,250,normal_kai_00006,1,AfterClear,5,217,general_fragment_00001,,202509010,__NULL__,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,normal_glo4_00002,quest_main_glo4_normal_16,normal_glo4_00002,2,50,25,250,250,normal_glo4_00001,1,AfterClear,5,218,general_fragment_00001,,202509010,__NULL__,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,normal_glo4_00003,quest_main_glo4_normal_16,normal_glo4_00003,3,50,25,250,250,normal_glo4_00002,1,AfterClear,5,219,general_diamond,,202509010,__NULL__,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,hard_glo4_00001,quest_main_glo4_hard_16,hard_glo4_00001,1,80,40,400,600,normal_glo4_00003,1,AfterClear,5,220,general_fragment_00002,,202509010,__NULL__,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,hard_glo4_00002,quest_main_glo4_hard_16,hard_glo4_00002,2,80,40,400,600,hard_glo4_00001,1,AfterClear,5,221,general_fragment_00002,,202509010,__NULL__,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,hard_glo4_00003,quest_main_glo4_hard_16,hard_glo4_00003,3,80,40,400,600,hard_glo4_00002,1,AfterClear,5,222,general_diamond,,202509010,__NULL__,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,veryhard_glo4_00001,quest_main_glo4_veryhard_16,veryhard_glo4_00001,1,80,40,400,1000,hard_glo4_00003,1,__NULL__,1,223,general_diamond,,202509010,__NULL__,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,veryhard_glo4_00002,quest_main_glo4_veryhard_16,veryhard_glo4_00002,2,80,40,400,1000,veryhard_glo4_00001,1,__NULL__,1,224,general_diamond,,202509010,__NULL__,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,veryhard_glo4_00003,quest_main_glo4_veryhard_16,veryhard_glo4_00003,3,80,40,400,1000,veryhard_glo4_00002,1,__NULL__,1,225,general_diamond,,202509010,__NULL__,"2025-05-01 12:00:00","2030-01-01 0:00:00"
e,normal_osh_00001,quest_main_osh_normal_17,normal_osh_00001,1,50,25,250,250,normal_glo4_00003,1,AfterClear,5,226,osh_normal_01,,202512020,osh_a_0001,"2026-01-01 00:00:00","2030-01-01 0:00:00"
e,normal_osh_00002,quest_main_osh_normal_17,normal_osh_00002,2,50,25,250,250,normal_osh_00001,1,AfterClear,5,227,osh_normal_02,,202512020,osh_a_0002,"2026-01-01 00:00:00","2030-01-01 0:00:00"
e,normal_osh_00003,quest_main_osh_normal_17,normal_osh_00003,3,50,25,250,250,normal_osh_00002,1,AfterClear,5,228,osh_normal_03,,202512020,osh_a_0003,"2026-01-01 00:00:00","2030-01-01 0:00:00"
e,hard_osh_00001,quest_main_osh_hard_17,hard_osh_00001,1,80,40,400,600,normal_osh_00003,1,AfterClear,5,229,osh_normal_01,,202512020,osh_b_0001,"2026-01-01 00:00:00","2030-01-01 0:00:00"
e,hard_osh_00002,quest_main_osh_hard_17,hard_osh_00002,2,80,40,400,600,hard_osh_00001,1,AfterClear,5,230,osh_normal_02,,202512020,osh_b_0002,"2026-01-01 00:00:00","2030-01-01 0:00:00"
e,hard_osh_00003,quest_main_osh_hard_17,hard_osh_00003,3,80,40,400,600,hard_osh_00002,1,AfterClear,5,231,osh_normal_03,,202512020,osh_b_0003,"2026-01-01 00:00:00","2030-01-01 0:00:00"
e,veryhard_osh_00001,quest_main_osh_veryhard_17,veryhard_osh_00001,1,80,50,500,1250,hard_osh_00003,1,__NULL__,1,232,osh_normal_01,,202512020,osh_c_0001,"2026-01-01 00:00:00","2030-01-01 0:00:00"
e,veryhard_osh_00002,quest_main_osh_veryhard_17,veryhard_osh_00002,2,80,50,500,1250,veryhard_osh_00001,1,__NULL__,1,233,osh_normal_02,,202512020,osh_c_0002,"2026-01-01 00:00:00","2030-01-01 0:00:00"
e,veryhard_osh_00003,quest_main_osh_veryhard_17,veryhard_osh_00003,3,80,50,500,1250,veryhard_osh_00002,1,__NULL__,1,234,osh_normal_03,,202512020,osh_c_0003,"2026-01-01 00:00:00","2030-01-01 0:00:00"
e,enhance_00001,quest_enhance_00001,enhance_00001,0,1,0,0,0,,1,__NULL__,1,120,01_dan_normal_1,,202509010,__NULL__,"2024-01-01 0:00:00","2030-01-01 0:00:00"
e,event_kai1_1day_00001,quest_event_kai1_1day,event_kai1_1day_00001,1,1,1,100,300,,1,__NULL__,1,1,general_diamond,,202509010,__NULL__,"2025-09-22 11:00:00","2025-10-06 03:59:59"
e,event_kai1_charaget01_00001,quest_event_kai1_charaget01,event_kai1_charaget01_00001,1,1,5,50,75,,1,__NULL__,1,1,event_kai1_00001,,202509010,event_kai_a_0001,"2025-09-22 11:00:00","2025-10-22 11:59:59"
e,event_kai1_charaget01_00002,quest_event_kai1_charaget01,event_kai1_charaget01_00002,2,3,5,50,75,event_kai1_charaget01_00001,1,__NULL__,1,2,general_diamond,,202509010,event_kai_a_0002,"2025-09-22 11:00:00","2025-10-22 11:59:59"
e,event_kai1_charaget01_00003,quest_event_kai1_charaget01,event_kai1_charaget01_00003,3,5,5,50,75,event_kai1_charaget01_00002,1,__NULL__,1,3,general_diamond,,202509010,event_kai_a_0003,"2025-09-22 11:00:00","2025-10-22 11:59:59"
e,event_kai1_charaget01_00004,quest_event_kai1_charaget01,event_kai1_charaget01_00004,4,5,7,70,105,event_kai1_charaget01_00003,1,__NULL__,1,4,general_diamond,,202509010,event_kai_a_0004,"2025-09-22 11:00:00","2025-10-22 11:59:59"
e,event_kai1_charaget01_00005,quest_event_kai1_charaget01,event_kai1_charaget01_00005,5,10,7,70,105,event_kai1_charaget01_00004,1,__NULL__,1,5,event_kai1_00003,,202509010,event_kai_a_0005,"2025-09-22 11:00:00","2025-10-22 11:59:59"
e,event_kai1_charaget01_00006,quest_event_kai1_charaget01,event_kai1_charaget01_00006,6,10,7,70,105,event_kai1_charaget01_00005,1,__NULL__,1,6,general_fragment_00001,,202509010,event_kai_a_0006,"2025-09-22 11:00:00","2025-10-22 11:59:59"
e,event_kai1_charaget01_00007,quest_event_kai1_charaget01,event_kai1_charaget01_00007,7,15,10,100,150,event_kai1_charaget01_00006,1,__NULL__,1,7,general_fragment_00002,,202509010,event_kai_a_0007,"2025-09-22 11:00:00","2025-10-22 11:59:59"
e,event_kai1_charaget01_00008,quest_event_kai1_charaget01,event_kai1_charaget01_00008,8,20,10,100,150,event_kai1_charaget01_00007,1,__NULL__,1,8,general_fragment_00003,,202509010,event_kai_a_0008,"2025-09-22 11:00:00","2025-10-22 11:59:59"
e,event_kai1_charaget02_00001,quest_event_kai1_charaget02,event_kai1_charaget02_00001,1,1,5,50,75,,1,__NULL__,1,1,event_kai1_00004,,202509010,event_kai_b_0001,"2025-09-29 12:00:00","2025-10-22 11:59:59"
e,event_kai1_charaget02_00002,quest_event_kai1_charaget02,event_kai1_charaget02_00002,2,3,5,50,75,event_kai1_charaget02_00001,1,__NULL__,1,2,general_diamond,,202509010,event_kai_b_0002,"2025-09-29 12:00:00","2025-10-22 11:59:59"
e,event_kai1_charaget02_00003,quest_event_kai1_charaget02,event_kai1_charaget02_00003,3,5,5,50,75,event_kai1_charaget02_00002,1,__NULL__,1,3,general_diamond,,202509010,event_kai_b_0003,"2025-09-29 12:00:00","2025-10-22 11:59:59"
e,event_kai1_charaget02_00004,quest_event_kai1_charaget02,event_kai1_charaget02_00004,4,5,7,70,105,event_kai1_charaget02_00003,1,__NULL__,1,4,general_diamond,,202509010,event_kai_b_0004,"2025-09-29 12:00:00","2025-10-22 11:59:59"
e,event_kai1_charaget02_00005,quest_event_kai1_charaget02,event_kai1_charaget02_00005,5,10,7,70,105,event_kai1_charaget02_00004,1,__NULL__,1,5,event_kai1_00006,,202509010,event_kai_b_0005,"2025-09-29 12:00:00","2025-10-22 11:59:59"
e,event_kai1_charaget02_00006,quest_event_kai1_charaget02,event_kai1_charaget02_00006,6,10,7,70,105,event_kai1_charaget02_00005,1,__NULL__,1,6,general_fragment_00001,,202509010,event_kai_b_0006,"2025-09-29 12:00:00","2025-10-22 11:59:59"
e,event_kai1_charaget02_00007,quest_event_kai1_charaget02,event_kai1_charaget02_00007,7,15,10,100,150,event_kai1_charaget02_00006,1,__NULL__,1,7,general_fragment_00002,,202509010,event_kai_b_0007,"2025-09-29 12:00:00","2025-10-22 11:59:59"
e,event_kai1_charaget02_00008,quest_event_kai1_charaget02,event_kai1_charaget02_00008,8,20,10,100,150,event_kai1_charaget02_00007,1,__NULL__,1,8,general_fragment_00003,,202509010,event_kai_b_0008,"2025-09-29 12:00:00","2025-10-22 11:59:59"
e,event_kai1_challenge01_00001,quest_event_kai1_challenge01,event_kai1_challenge01_00001,1,10,5,50,75,event_kai1_charaget01_00008,1,__NULL__,1,1,general_ticket_00002,,202509010,__NULL__,"2025-09-22 11:00:00","2025-10-22 11:59:59"
e,event_kai1_challenge01_00002,quest_event_kai1_challenge01,event_kai1_challenge01_00002,2,20,10,100,150,event_kai1_challenge01_00001,1,__NULL__,1,2,general_ticket_00002,,202509010,__NULL__,"2025-09-22 11:00:00","2025-10-22 11:59:59"
e,event_kai1_challenge01_00003,quest_event_kai1_challenge01,event_kai1_challenge01_00003,3,30,15,150,225,event_kai1_challenge01_00002,1,__NULL__,1,3,event_kai1_00007,,202509010,__NULL__,"2025-09-22 11:00:00","2025-10-22 11:59:59"
e,event_kai1_challenge01_00004,quest_event_kai1_challenge01,event_kai1_challenge01_00004,4,40,20,200,300,event_kai1_challenge01_00003,1,__NULL__,1,4,event_kai1_00008,,202509010,__NULL__,"2025-09-22 11:00:00","2025-10-22 11:59:59"
e,event_kai1_savage_00001,quest_event_kai1_savage,event_kai1_savage_00001,1,40,20,400,300,event_kai1_charaget01_00008,1,__NULL__,1,1,event_kai1_00009,,202509010,__NULL__,"2025-09-22 11:00:00","2025-10-22 11:59:59"
e,event_kai1_savage_00002,quest_event_kai1_savage,event_kai1_savage_00002,2,50,20,500,300,event_kai1_savage_00001,1,__NULL__,1,2,event_kai1_00010,,202509010,__NULL__,"2025-09-22 11:00:00","2025-10-22 11:59:59"
e,event_spy1_1day_00001,quest_event_spy1_1day,event_spy1_1day_00001,1,1,1,100,300,,1,__NULL__,1,1,general_diamond,,202510010,__NULL__,"2025-10-06 15:00:00","2025-10-22 03:59:59"
e,event_spy1_charaget01_00001,quest_event_spy1_charaget01,event_spy1_charaget01_00001,1,1,5,50,75,,1,__NULL__,1,1,event_spy1_00004,,202510010,event_spy_a_0001,"2025-10-06 15:00:00","2025-11-06 14:59:59"
e,event_spy1_charaget01_00002,quest_event_spy1_charaget01,event_spy1_charaget01_00002,2,3,5,50,75,event_spy1_charaget01_00001,1,__NULL__,1,2,general_diamond,,202510010,event_spy_a_0002,"2025-10-06 15:00:00","2025-11-06 14:59:59"
e,event_spy1_charaget01_00003,quest_event_spy1_charaget01,event_spy1_charaget01_00003,3,5,5,50,75,event_spy1_charaget01_00002,1,__NULL__,1,3,general_diamond,,202510010,event_spy_a_0003,"2025-10-06 15:00:00","2025-11-06 14:59:59"
e,event_spy1_charaget01_00004,quest_event_spy1_charaget01,event_spy1_charaget01_00004,4,5,7,70,105,event_spy1_charaget01_00003,1,__NULL__,1,4,general_diamond,,202510010,event_spy_a_0004,"2025-10-06 15:00:00","2025-11-06 14:59:59"
e,event_spy1_charaget01_00005,quest_event_spy1_charaget01,event_spy1_charaget01_00005,5,10,7,70,105,event_spy1_charaget01_00004,1,__NULL__,1,5,event_spy1_00006,,202510010,event_spy_a_0005,"2025-10-06 15:00:00","2025-11-06 14:59:59"
e,event_spy1_charaget01_00006,quest_event_spy1_charaget01,event_spy1_charaget01_00006,6,10,7,70,105,event_spy1_charaget01_00005,1,__NULL__,1,6,general_fragment_00001,,202510010,event_spy_a_0006,"2025-10-06 15:00:00","2025-11-06 14:59:59"
e,event_spy1_charaget01_00007,quest_event_spy1_charaget01,event_spy1_charaget01_00007,7,15,10,100,150,event_spy1_charaget01_00006,1,__NULL__,1,7,general_fragment_00002,,202510010,event_spy_a_0007,"2025-10-06 15:00:00","2025-11-06 14:59:59"
e,event_spy1_charaget01_00008,quest_event_spy1_charaget01,event_spy1_charaget01_00008,8,20,10,100,150,event_spy1_charaget01_00007,1,__NULL__,1,8,general_fragment_00003,,202510010,event_spy_a_0008,"2025-10-06 15:00:00","2025-11-06 14:59:59"
e,event_spy1_charaget02_00001,quest_event_spy1_charaget02,event_spy1_charaget02_00001,1,1,5,50,75,,1,__NULL__,1,1,event_spy1_00001,,202510010,event_spy_b_0001,"2025-10-13 15:00:00","2025-11-06 14:59:59"
e,event_spy1_charaget02_00002,quest_event_spy1_charaget02,event_spy1_charaget02_00002,2,3,5,50,75,event_spy1_charaget02_00001,1,__NULL__,1,2,general_diamond,,202510010,event_spy_b_0002,"2025-10-13 15:00:00","2025-11-06 14:59:59"
e,event_spy1_charaget02_00003,quest_event_spy1_charaget02,event_spy1_charaget02_00003,3,5,5,50,75,event_spy1_charaget02_00002,1,__NULL__,1,3,general_diamond,,202510010,event_spy_b_0003,"2025-10-13 15:00:00","2025-11-06 14:59:59"
e,event_spy1_charaget02_00004,quest_event_spy1_charaget02,event_spy1_charaget02_00004,4,5,7,70,105,event_spy1_charaget02_00003,1,__NULL__,1,4,general_diamond,,202510010,event_spy_b_0004,"2025-10-13 15:00:00","2025-11-06 14:59:59"
e,event_spy1_charaget02_00005,quest_event_spy1_charaget02,event_spy1_charaget02_00005,5,10,7,70,105,event_spy1_charaget02_00004,1,__NULL__,1,5,event_spy1_00003,,202510010,event_spy_b_0005,"2025-10-13 15:00:00","2025-11-06 14:59:59"
e,event_spy1_charaget02_00006,quest_event_spy1_charaget02,event_spy1_charaget02_00006,6,10,7,70,105,event_spy1_charaget02_00005,1,__NULL__,1,6,general_fragment_00001,,202510010,event_spy_b_0006,"2025-10-13 15:00:00","2025-11-06 14:59:59"
e,event_spy1_charaget02_00007,quest_event_spy1_charaget02,event_spy1_charaget02_00007,7,15,10,100,150,event_spy1_charaget02_00006,1,__NULL__,1,7,general_fragment_00002,,202510010,event_spy_b_0007,"2025-10-13 15:00:00","2025-11-06 14:59:59"
e,event_spy1_charaget02_00008,quest_event_spy1_charaget02,event_spy1_charaget02_00008,8,20,10,100,150,event_spy1_charaget02_00007,1,__NULL__,1,8,general_fragment_00003,,202510010,event_spy_b_0008,"2025-10-13 15:00:00","2025-11-06 14:59:59"
e,event_spy1_challenge01_00001,quest_event_spy1_challenge01,event_spy1_challenge01_00001,1,10,5,50,75,event_spy1_charaget01_00008,1,__NULL__,1,1,general_ticket_00002,,202510010,__NULL__,"2025-10-06 15:00:00","2025-11-06 14:59:59"
e,event_spy1_challenge01_00002,quest_event_spy1_challenge01,event_spy1_challenge01_00002,2,20,10,100,150,event_spy1_challenge01_00001,1,__NULL__,1,2,event_spy1_00008,,202510010,__NULL__,"2025-10-06 15:00:00","2025-11-06 14:59:59"
e,event_spy1_challenge01_00003,quest_event_spy1_challenge01,event_spy1_challenge01_00003,3,30,15,150,225,event_spy1_challenge01_00002,1,__NULL__,1,3,event_spy1_00009,,202510010,__NULL__,"2025-10-06 15:00:00","2025-11-06 14:59:59"
e,event_spy1_challenge01_00004,quest_event_spy1_challenge01,event_spy1_challenge01_00004,4,40,20,200,300,event_spy1_challenge01_00003,1,__NULL__,1,4,event_spy1_00007,,202510010,__NULL__,"2025-10-06 15:00:00","2025-11-06 14:59:59"
e,event_spy1_savage_00001,quest_event_spy1_savage,event_spy1_savage_00001,1,45,20,400,300,event_spy1_charaget01_00008,1,__NULL__,1,1,general_ticket_00002,,202510010,__NULL__,"2025-10-06 15:00:00","2025-11-06 14:59:59"
e,event_spy1_savage_00002,quest_event_spy1_savage,event_spy1_savage_00002,2,55,20,500,300,event_spy1_savage_00001,1,__NULL__,1,2,event_spy1_00010,,202510010,__NULL__,"2025-10-06 15:00:00","2025-11-06 14:59:59"
e,event_dan1_1day_00001,quest_event_dan1_1day,event_dan1_1day_00001,1,1,1,100,1000,,1,__NULL__,1,1,general_diamond,,202510020,__NULL__,"2025-10-22 15:00:00","2025-11-06 03:59:59"
e,event_dan1_charaget01_00001,quest_event_dan1_charaget01,event_dan1_charaget01_00001,1,1,5,50,75,,1,__NULL__,1,1,event_dan1_00001,,202510020,event_dan_a_0001,"2025-10-22 15:00:00","2025-11-25 14:59:59"
e,event_dan1_charaget01_00002,quest_event_dan1_charaget01,event_dan1_charaget01_00002,2,3,5,50,75,event_dan1_charaget01_00001,1,__NULL__,1,2,general_diamond,,202510020,event_dan_a_0002,"2025-10-22 15:00:00","2025-11-25 14:59:59"
e,event_dan1_charaget01_00003,quest_event_dan1_charaget01,event_dan1_charaget01_00003,3,5,5,50,75,event_dan1_charaget01_00002,1,__NULL__,1,3,general_diamond,,202510020,event_dan_a_0003,"2025-10-22 15:00:00","2025-11-25 14:59:59"
e,event_dan1_charaget01_00004,quest_event_dan1_charaget01,event_dan1_charaget01_00004,4,5,7,70,105,event_dan1_charaget01_00003,1,__NULL__,1,4,general_diamond,,202510020,event_dan_a_0004,"2025-10-22 15:00:00","2025-11-25 14:59:59"
e,event_dan1_charaget01_00005,quest_event_dan1_charaget01,event_dan1_charaget01_00005,5,10,7,70,105,event_dan1_charaget01_00004,1,__NULL__,1,5,event_dan1_00003,,202510020,event_dan_a_0005,"2025-10-22 15:00:00","2025-11-25 14:59:59"
e,event_dan1_charaget01_00006,quest_event_dan1_charaget01,event_dan1_charaget01_00006,6,10,7,70,105,event_dan1_charaget01_00005,1,__NULL__,1,6,general_fragment_00001,,202510020,event_dan_a_0006,"2025-10-22 15:00:00","2025-11-25 14:59:59"
e,event_dan1_charaget01_00007,quest_event_dan1_charaget01,event_dan1_charaget01_00007,7,15,10,100,150,event_dan1_charaget01_00006,1,__NULL__,1,7,general_fragment_00002,,202510020,event_dan_a_0007,"2025-10-22 15:00:00","2025-11-25 14:59:59"
e,event_dan1_charaget01_00008,quest_event_dan1_charaget01,event_dan1_charaget01_00008,8,20,10,100,150,event_dan1_charaget01_00007,1,__NULL__,1,8,general_fragment_00003,,202510020,event_dan_a_0008,"2025-10-22 15:00:00","2025-11-25 14:59:59"
e,event_dan1_charaget02_00001,quest_event_dan1_charaget02,event_dan1_charaget02_00001,1,1,5,50,75,,1,__NULL__,1,1,event_dan1_00004,,202510020,event_dan_b_0001,"2025-10-27 15:00:00","2025-11-25 14:59:59"
e,event_dan1_charaget02_00002,quest_event_dan1_charaget02,event_dan1_charaget02_00002,2,3,5,50,75,event_dan1_charaget02_00001,1,__NULL__,1,2,general_diamond,,202510020,event_dan_b_0002,"2025-10-27 15:00:00","2025-11-25 14:59:59"
e,event_dan1_charaget02_00003,quest_event_dan1_charaget02,event_dan1_charaget02_00003,3,5,5,50,75,event_dan1_charaget02_00002,1,__NULL__,1,3,general_diamond,,202510020,event_dan_b_0003,"2025-10-27 15:00:00","2025-11-25 14:59:59"
e,event_dan1_charaget02_00004,quest_event_dan1_charaget02,event_dan1_charaget02_00004,4,5,7,70,105,event_dan1_charaget02_00003,1,__NULL__,1,4,general_diamond,,202510020,event_dan_b_0004,"2025-10-27 15:00:00","2025-11-25 14:59:59"
e,event_dan1_charaget02_00005,quest_event_dan1_charaget02,event_dan1_charaget02_00005,5,10,7,70,105,event_dan1_charaget02_00004,1,__NULL__,1,5,event_dan1_00006,,202510020,event_dan_b_0005,"2025-10-27 15:00:00","2025-11-25 14:59:59"
e,event_dan1_charaget02_00006,quest_event_dan1_charaget02,event_dan1_charaget02_00006,6,10,7,70,105,event_dan1_charaget02_00005,1,__NULL__,1,6,general_fragment_00001,,202510020,event_dan_b_0006,"2025-10-27 15:00:00","2025-11-25 14:59:59"
e,event_dan1_charaget02_00007,quest_event_dan1_charaget02,event_dan1_charaget02_00007,7,15,10,100,150,event_dan1_charaget02_00006,1,__NULL__,1,7,general_fragment_00002,,202510020,event_dan_b_0007,"2025-10-27 15:00:00","2025-11-25 14:59:59"
e,event_dan1_charaget02_00008,quest_event_dan1_charaget02,event_dan1_charaget02_00008,8,20,10,100,150,event_dan1_charaget02_00007,1,__NULL__,1,8,general_fragment_00003,,202510020,event_dan_b_0008,"2025-10-27 15:00:00","2025-11-25 14:59:59"
e,event_dan1_challenge01_00001,quest_event_dan1_challenge01,event_dan1_challenge01_00001,1,20,10,100,150,event_dan1_charaget01_00008,1,__NULL__,1,1,general_ticket_00002,,202510020,__NULL__,"2025-10-22 15:00:00","2025-11-25 14:59:59"
e,event_dan1_challenge01_00002,quest_event_dan1_challenge01,event_dan1_challenge01_00002,2,30,15,150,225,event_dan1_challenge01_00001,1,__NULL__,1,2,event_dan1_00012,,202510020,__NULL__,"2025-10-22 15:00:00","2025-11-25 14:59:59"
e,event_dan1_challenge01_00003,quest_event_dan1_challenge01,event_dan1_challenge01_00003,3,30,15,150,225,event_dan1_challenge01_00002,1,__NULL__,1,3,event_dan1_00011,,202510020,__NULL__,"2025-10-22 15:00:00","2025-11-25 14:59:59"
e,event_dan1_challenge01_00004,quest_event_dan1_challenge01,event_dan1_challenge01_00004,4,40,20,200,300,event_dan1_challenge01_00003,1,__NULL__,1,4,event_dan1_00007,,202510020,__NULL__,"2025-10-22 15:00:00","2025-11-25 14:59:59"
e,event_dan1_savage_00001,quest_event_dan1_savage,event_dan1_savage_00001,1,50,25,500,250,event_dan1_charaget01_00008,1,__NULL__,1,1,event_dan1_00009,,202510020,__NULL__,"2025-10-22 15:00:00","2025-11-25 14:59:59"
e,event_dan1_savage_00002,quest_event_dan1_savage,event_dan1_savage_00002,2,50,25,500,250,event_dan1_savage_00001,1,__NULL__,1,2,event_dan1_00008,,202510020,__NULL__,"2025-10-22 15:00:00","2025-11-25 14:59:59"
e,event_mag1_1day_00001,quest_event_mag1_1day,event_mag1_1day_00001,1,1,1,100,1000,,1,__NULL__,1,1,general_diamond,,202511010,__NULL__,"2025-11-06 15:00:00","2025-11-25 03:59:59"
e,event_mag1_charaget01_00001,quest_event_mag1_charaget01,event_mag1_charaget01_00001,1,1,5,50,100,,1,__NULL__,1,1,event_mag1_00001,,202511010,event_mag_a_0001,"2025-11-06 15:00:00","2025-12-08 10:59:59"
e,event_mag1_charaget01_00002,quest_event_mag1_charaget01,event_mag1_charaget01_00002,2,3,5,50,100,event_mag1_charaget01_00001,1,__NULL__,1,2,general_diamond,,202511010,event_mag_a_0002,"2025-11-06 15:00:00","2025-12-08 10:59:59"
e,event_mag1_charaget01_00003,quest_event_mag1_charaget01,event_mag1_charaget01_00003,3,5,5,50,100,event_mag1_charaget01_00002,1,__NULL__,1,3,general_diamond,,202511010,event_mag_a_0003,"2025-11-06 15:00:00","2025-12-08 10:59:59"
e,event_mag1_charaget01_00004,quest_event_mag1_charaget01,event_mag1_charaget01_00004,4,5,7,70,150,event_mag1_charaget01_00003,1,__NULL__,1,4,general_diamond,,202511010,event_mag_a_0004,"2025-11-06 15:00:00","2025-12-08 10:59:59"
e,event_mag1_charaget01_00005,quest_event_mag1_charaget01,event_mag1_charaget01_00005,5,10,7,70,150,event_mag1_charaget01_00004,1,__NULL__,1,5,event_mag1_00003,,202511010,event_mag_a_0005,"2025-11-06 15:00:00","2025-12-08 10:59:59"
e,event_mag1_charaget01_00006,quest_event_mag1_charaget01,event_mag1_charaget01_00006,6,10,7,70,150,event_mag1_charaget01_00005,1,__NULL__,1,6,general_fragment_00001,,202511010,event_mag_a_0006,"2025-11-06 15:00:00","2025-12-08 10:59:59"
e,event_mag1_charaget01_00007,quest_event_mag1_charaget01,event_mag1_charaget01_00007,7,15,10,100,200,event_mag1_charaget01_00006,1,__NULL__,1,7,general_fragment_00002,,202511010,event_mag_a_0007,"2025-11-06 15:00:00","2025-12-08 10:59:59"
e,event_mag1_charaget01_00008,quest_event_mag1_charaget01,event_mag1_charaget01_00008,8,20,10,100,300,event_mag1_charaget01_00007,1,__NULL__,1,8,general_fragment_00003,,202511010,event_mag_a_0008,"2025-11-06 15:00:00","2025-12-08 10:59:59"
e,event_mag1_charaget02_00001,quest_event_mag1_charaget02,event_mag1_charaget02_00001,1,1,5,50,100,,1,__NULL__,1,1,event_mag1_00004,,202511010,event_mag_b_0001,"2025-11-12 15:00:00","2025-12-08 10:59:59"
e,event_mag1_charaget02_00002,quest_event_mag1_charaget02,event_mag1_charaget02_00002,2,3,5,50,100,event_mag1_charaget02_00001,1,__NULL__,1,2,general_diamond,,202511010,event_mag_b_0002,"2025-11-12 15:00:00","2025-12-08 10:59:59"
e,event_mag1_charaget02_00003,quest_event_mag1_charaget02,event_mag1_charaget02_00003,3,5,5,50,100,event_mag1_charaget02_00002,1,__NULL__,1,3,general_diamond,,202511010,event_mag_b_0003,"2025-11-12 15:00:00","2025-12-08 10:59:59"
e,event_mag1_charaget02_00004,quest_event_mag1_charaget02,event_mag1_charaget02_00004,4,5,7,70,150,event_mag1_charaget02_00003,1,__NULL__,1,4,general_diamond,,202511010,event_mag_b_0004,"2025-11-12 15:00:00","2025-12-08 10:59:59"
e,event_mag1_charaget02_00005,quest_event_mag1_charaget02,event_mag1_charaget02_00005,5,10,7,70,150,event_mag1_charaget02_00004,1,__NULL__,1,5,event_mag1_00006,,202511010,event_mag_b_0005,"2025-11-12 15:00:00","2025-12-08 10:59:59"
e,event_mag1_charaget02_00006,quest_event_mag1_charaget02,event_mag1_charaget02_00006,6,10,7,70,150,event_mag1_charaget02_00005,1,__NULL__,1,6,general_fragment_00001,,202511010,event_mag_b_0006,"2025-11-12 15:00:00","2025-12-08 10:59:59"
e,event_mag1_charaget02_00007,quest_event_mag1_charaget02,event_mag1_charaget02_00007,7,15,10,100,200,event_mag1_charaget02_00006,1,__NULL__,1,7,general_fragment_00002,,202511010,event_mag_b_0007,"2025-11-12 15:00:00","2025-12-08 10:59:59"
e,event_mag1_charaget02_00008,quest_event_mag1_charaget02,event_mag1_charaget02_00008,8,20,10,100,300,event_mag1_charaget02_00007,1,__NULL__,1,8,general_fragment_00003,,202511010,event_mag_b_0008,"2025-11-12 15:00:00","2025-12-08 10:59:59"
e,event_mag1_challenge01_00001,quest_event_mag1_challenge01,event_mag1_challenge01_00001,1,20,10,100,150,event_mag1_charaget01_00008,1,__NULL__,1,1,general_ticket_00002,,202511010,__NULL__,"2025-11-06 15:00:00","2025-12-08 10:59:59"
e,event_mag1_challenge01_00002,quest_event_mag1_challenge01,event_mag1_challenge01_00002,2,30,15,150,225,event_mag1_challenge01_00001,1,__NULL__,1,2,event_mag1_00008,,202511010,__NULL__,"2025-11-06 15:00:00","2025-12-08 10:59:59"
e,event_mag1_challenge01_00003,quest_event_mag1_challenge01,event_mag1_challenge01_00003,3,30,15,150,225,event_mag1_challenge01_00002,1,__NULL__,1,3,event_mag1_00011,,202511010,__NULL__,"2025-11-06 15:00:00","2025-12-08 10:59:59"
e,event_mag1_challenge01_00004,quest_event_mag1_challenge01,event_mag1_challenge01_00004,4,40,20,200,300,event_mag1_challenge01_00003,1,__NULL__,1,4,event_mag1_00007,,202511010,__NULL__,"2025-11-06 15:00:00","2025-12-08 10:59:59"
e,event_mag1_savage_00001,quest_event_mag1_savage,event_mag1_savage_00001,1,60,25,600,375,event_mag1_charaget01_00008,1,__NULL__,1,1,event_mag1_00009,,202511010,__NULL__,"2025-11-06 15:00:00","2025-12-08 10:59:59"
e,event_mag1_savage_00002,quest_event_mag1_savage,event_mag1_savage_00002,2,70,25,700,375,event_mag1_savage_00001,1,__NULL__,1,2,event_mag1_00010,,202511010,__NULL__,"2025-11-06 15:00:00","2025-12-08 10:59:59"
e,event_mag1_savage_00003,quest_event_mag1_savage,event_mag1_savage_00003,3,80,50,1000,400,event_mag1_savage_00002,1,__NULL__,1,2,general_diamond,,202511010,__NULL__,"2025-11-06 15:00:00","2025-12-08 10:59:59"
e,event_yuw1_1day_00001,quest_event_yuw1_1day,event_yuw1_1day_00001,1,1,1,100,1000,,1,__NULL__,1,1,general_diamond,,202511020,__NULL__,"2025-11-25 15:00:00","2025-12-08 03:59:59"
e,event_yuw1_charaget01_00001,quest_event_yuw1_charaget01,event_yuw1_charaget01_00001,1,10,5,50,100,,1,AfterClear,5,1,event_yuw1_00001,,202511020,event_yuw_a_0001,"2025-11-25 15:00:00","2025-12-31 23:59:59"
e,event_yuw1_charaget01_00002,quest_event_yuw1_charaget01,event_yuw1_charaget01_00002,2,10,5,50,100,event_yuw1_charaget01_00001,1,AfterClear,5,2,general_diamond,,202511020,event_yuw_a_0002,"2025-11-25 15:00:00","2025-12-31 23:59:59"
e,event_yuw1_charaget01_00003,quest_event_yuw1_charaget01,event_yuw1_charaget01_00003,3,15,5,50,100,event_yuw1_charaget01_00002,1,AfterClear,5,3,general_diamond,,202511020,event_yuw_a_0003,"2025-11-25 15:00:00","2025-12-31 23:59:59"
e,event_yuw1_charaget01_00004,quest_event_yuw1_charaget01,event_yuw1_charaget01_00004,4,15,7,70,150,event_yuw1_charaget01_00003,1,AfterClear,5,4,general_diamond,,202511020,event_yuw_a_0004,"2025-11-25 15:00:00","2025-12-31 23:59:59"
e,event_yuw1_charaget01_00005,quest_event_yuw1_charaget01,event_yuw1_charaget01_00005,5,20,7,70,150,event_yuw1_charaget01_00004,1,AfterClear,5,5,event_yuw1_00003,,202511020,event_yuw_a_0005,"2025-11-25 15:00:00","2025-12-31 23:59:59"
e,event_yuw1_charaget01_00006,quest_event_yuw1_charaget01,event_yuw1_charaget01_00006,6,20,7,70,150,event_yuw1_charaget01_00005,1,AfterClear,5,6,general_fragment_00001,,202511020,event_yuw_a_0006,"2025-11-25 15:00:00","2025-12-31 23:59:59"
e,event_yuw1_charaget01_00007,quest_event_yuw1_charaget01,event_yuw1_charaget01_00007,7,20,10,100,200,event_yuw1_charaget01_00006,1,AfterClear,5,7,general_fragment_00002,,202511020,event_yuw_a_0007,"2025-11-25 15:00:00","2025-12-31 23:59:59"
e,event_yuw1_charaget01_00008,quest_event_yuw1_charaget01,event_yuw1_charaget01_00008,8,30,10,100,300,event_yuw1_charaget01_00007,1,AfterClear,5,8,general_fragment_00003,,202511020,event_yuw_a_0008,"2025-11-25 15:00:00","2025-12-31 23:59:59"
e,event_yuw1_charaget02_00001,quest_event_yuw1_charaget02,event_yuw1_charaget02_00001,1,10,5,50,100,,1,AfterClear,5,1,event_yuw1_00004,,202511020,event_yuw_b_0001,"2025-12-01 15:00:00","2025-12-31 23:59:59"
e,event_yuw1_charaget02_00002,quest_event_yuw1_charaget02,event_yuw1_charaget02_00002,2,10,5,50,100,event_yuw1_charaget02_00001,1,AfterClear,5,2,general_diamond,,202511020,event_yuw_b_0002,"2025-12-01 15:00:00","2025-12-31 23:59:59"
e,event_yuw1_charaget02_00003,quest_event_yuw1_charaget02,event_yuw1_charaget02_00003,3,15,5,50,100,event_yuw1_charaget02_00002,1,AfterClear,5,3,general_diamond,,202511020,event_yuw_b_0003,"2025-12-01 15:00:00","2025-12-31 23:59:59"
e,event_yuw1_charaget02_00004,quest_event_yuw1_charaget02,event_yuw1_charaget02_00004,4,15,7,70,150,event_yuw1_charaget02_00003,1,AfterClear,5,4,general_diamond,,202511020,event_yuw_b_0004,"2025-12-01 15:00:00","2025-12-31 23:59:59"
e,event_yuw1_charaget02_00005,quest_event_yuw1_charaget02,event_yuw1_charaget02_00005,5,20,7,70,150,event_yuw1_charaget02_00004,1,AfterClear,5,5,event_yuw1_00006,,202511020,event_yuw_b_0005,"2025-12-01 15:00:00","2025-12-31 23:59:59"
e,event_yuw1_charaget02_00006,quest_event_yuw1_charaget02,event_yuw1_charaget02_00006,6,20,7,70,150,event_yuw1_charaget02_00005,1,AfterClear,5,6,general_fragment_00001,,202511020,event_yuw_b_0006,"2025-12-01 15:00:00","2025-12-31 23:59:59"
e,event_yuw1_charaget02_00007,quest_event_yuw1_charaget02,event_yuw1_charaget02_00007,7,20,10,100,200,event_yuw1_charaget02_00006,1,AfterClear,5,7,general_fragment_00002,,202511020,event_yuw_b_0007,"2025-12-01 15:00:00","2025-12-31 23:59:59"
e,event_yuw1_charaget02_00008,quest_event_yuw1_charaget02,event_yuw1_charaget02_00008,8,30,10,100,300,event_yuw1_charaget02_00007,1,AfterClear,5,8,general_fragment_00003,,202511020,event_yuw_b_0008,"2025-12-01 15:00:00","2025-12-31 23:59:59"
e,event_yuw1_challenge01_00001,quest_event_yuw1_challenge01,event_yuw1_challenge01_00001,1,30,10,100,150,event_yuw1_charaget01_00008,1,__NULL__,1,1,event_yuw1_00007,,202511020,__NULL__,"2025-11-25 15:00:00","2025-12-31 23:59:59"
e,event_yuw1_challenge01_00002,quest_event_yuw1_challenge01,event_yuw1_challenge01_00002,2,30,15,150,225,event_yuw1_challenge01_00001,1,__NULL__,1,2,event_yuw1_00008,,202511020,__NULL__,"2025-11-25 15:00:00","2025-12-31 23:59:59"
e,event_yuw1_challenge01_00003,quest_event_yuw1_challenge01,event_yuw1_challenge01_00003,3,40,15,150,225,event_yuw1_challenge01_00002,1,__NULL__,1,3,event_yuw1_00009,,202511020,__NULL__,"2025-11-25 15:00:00","2025-12-31 23:59:59"
e,event_yuw1_challenge01_00004,quest_event_yuw1_challenge01,event_yuw1_challenge01_00004,4,50,20,200,300,event_yuw1_challenge01_00003,1,__NULL__,1,4,event_yuw1_00010,,202511020,__NULL__,"2025-11-25 15:00:00","2025-12-31 23:59:59"
e,event_yuw1_savage_00001,quest_event_yuw1_savage,event_yuw1_savage_00001,1,70,25,700,375,event_yuw1_charaget01_00008,1,__NULL__,1,1,event_yuw1_00012,,202511020,__NULL__,"2025-11-25 15:00:00","2025-12-31 23:59:59"
e,event_yuw1_savage_00002,quest_event_yuw1_savage,event_yuw1_savage_00002,2,70,25,700,375,event_yuw1_savage_00001,1,__NULL__,1,2,event_yuw1_00011,,202511020,__NULL__,"2025-11-25 15:00:00","2025-12-31 23:59:59"
e,event_yuw1_savage_00003,quest_event_yuw1_savage,event_yuw1_savage_00003,3,80,50,1000,400,event_yuw1_savage_00002,1,__NULL__,1,3,general_diamond,,202511020,__NULL__,"2025-11-25 15:00:00","2025-12-31 23:59:59"
e,event_yuw1_savage02_00001,quest_event_yuw1_savage02,event_yuw1_savage02_00001,1,75,50,2500,2000,,1,__NULL__,1,1,general_diamond,,202512015,__NULL__,"2025-12-22 15:00:00","2025-12-31 23:59:59"
e,event_yuw1_savage02_00002,quest_event_yuw1_savage02,event_yuw1_savage02_00002,2,80,100,5000,5000,event_yuw1_savage02_00001,1,__NULL__,1,2,general_diamond,,202512015,__NULL__,"2025-12-22 15:00:00","2025-12-31 23:59:59"
e,event_sur1_1day_00001,quest_event_sur1_1day,event_sur1_1day_00001,1,1,1,100,1000,,1,__NULL__,1,1,general_diamond,,202512010,__NULL__,"2025-12-08 15:00:00","2025-12-31 23:59:59"
e,event_sur1_charaget01_00001,quest_event_sur1_charaget01,event_sur1_charaget01_00001,1,10,5,50,100,,1,AfterClear,5,1,event_sur1_00001,,202512010,event_sur_a_0001,"2025-12-08 15:00:00","2026-01-16 10:59:59"
e,event_sur1_charaget01_00002,quest_event_sur1_charaget01,event_sur1_charaget01_00002,2,10,5,50,100,event_sur1_charaget01_00001,1,AfterClear,5,2,general_diamond,,202512010,event_sur_a_0002,"2025-12-08 15:00:00","2026-01-16 10:59:59"
e,event_sur1_charaget01_00003,quest_event_sur1_charaget01,event_sur1_charaget01_00003,3,15,5,50,100,event_sur1_charaget01_00002,1,AfterClear,5,3,general_diamond,,202512010,event_sur_a_0003,"2025-12-08 15:00:00","2026-01-16 10:59:59"
e,event_sur1_charaget01_00004,quest_event_sur1_charaget01,event_sur1_charaget01_00004,4,15,7,70,150,event_sur1_charaget01_00003,1,AfterClear,5,4,general_diamond,,202512010,event_sur_a_0004,"2025-12-08 15:00:00","2026-01-16 10:59:59"
e,event_sur1_charaget01_00005,quest_event_sur1_charaget01,event_sur1_charaget01_00005,5,20,7,70,150,event_sur1_charaget01_00004,1,AfterClear,5,5,event_sur1_00003,,202512010,event_sur_a_0005,"2025-12-08 15:00:00","2026-01-16 10:59:59"
e,event_sur1_charaget01_00006,quest_event_sur1_charaget01,event_sur1_charaget01_00006,6,20,7,70,150,event_sur1_charaget01_00005,1,AfterClear,5,6,general_fragment_00001,,202512010,event_sur_a_0006,"2025-12-08 15:00:00","2026-01-16 10:59:59"
e,event_sur1_charaget01_00007,quest_event_sur1_charaget01,event_sur1_charaget01_00007,7,20,10,100,200,event_sur1_charaget01_00006,1,AfterClear,5,7,general_fragment_00002,,202512010,event_sur_a_0007,"2025-12-08 15:00:00","2026-01-16 10:59:59"
e,event_sur1_charaget01_00008,quest_event_sur1_charaget01,event_sur1_charaget01_00008,8,30,10,100,300,event_sur1_charaget01_00007,1,AfterClear,5,8,general_fragment_00003,,202512010,event_sur_a_0008,"2025-12-08 15:00:00","2026-01-16 10:59:59"
e,event_sur1_challenge01_00001,quest_event_sur1_challenge01,event_sur1_challenge01_00001,1,30,10,100,150,event_sur1_charaget01_00008,1,AfterClear,5,1,general_ticket_00002,,202512010,__NULL__,"2025-12-08 15:00:00","2026-01-16 10:59:59"
e,event_sur1_challenge01_00002,quest_event_sur1_challenge01,event_sur1_challenge01_00002,2,30,15,150,225,event_sur1_challenge01_00001,1,__NULL__,1,2,event_sur1_00009,,202512010,__NULL__,"2025-12-08 15:00:00","2026-01-16 10:59:59"
e,event_sur1_challenge01_00003,quest_event_sur1_challenge01,event_sur1_challenge01_00003,3,40,15,150,225,event_sur1_challenge01_00002,1,__NULL__,1,3,event_sur1_00007,,202512010,__NULL__,"2025-12-08 15:00:00","2026-01-16 10:59:59"
e,event_sur1_challenge01_00004,quest_event_sur1_challenge01,event_sur1_challenge01_00004,4,50,20,200,300,event_sur1_challenge01_00003,1,__NULL__,1,4,event_sur1_00008,,202512010,__NULL__,"2025-12-08 15:00:00","2026-01-16 10:59:59"
e,event_sur1_savage_00001,quest_event_sur1_savage,event_sur1_savage_00001,1,70,25,700,375,event_sur1_charaget01_00008,1,__NULL__,1,1,event_sur1_00011,,202512010,__NULL__,"2025-12-08 15:00:00","2026-01-16 10:59:59"
e,event_sur1_savage_00002,quest_event_sur1_savage,event_sur1_savage_00002,2,70,25,700,375,event_sur1_savage_00001,1,__NULL__,1,2,event_sur1_00012,,202512010,__NULL__,"2025-12-08 15:00:00","2026-01-16 10:59:59"
e,event_sur1_savage_00003,quest_event_sur1_savage,event_sur1_savage_00003,3,80,50,1000,400,event_sur1_savage_00002,1,__NULL__,1,3,general_diamond,,202512010,__NULL__,"2025-12-08 15:00:00","2026-01-16 10:59:59"
e,event_sur1_charaget02_00001,quest_event_sur1_charaget02,event_sur1_charaget02_00001,1,10,5,50,100,,1,AfterClear,5,1,event_sur1_00004,,202512010,event_sur_b_0001,"2025-12-15 15:00:00","2026-01-16 10:59:59"
e,event_sur1_charaget02_00002,quest_event_sur1_charaget02,event_sur1_charaget02_00002,2,10,5,50,100,event_sur1_charaget02_00001,1,AfterClear,5,2,general_diamond,,202512010,event_sur_b_0002,"2025-12-15 15:00:00","2026-01-16 10:59:59"
e,event_sur1_charaget02_00003,quest_event_sur1_charaget02,event_sur1_charaget02_00003,3,15,5,50,100,event_sur1_charaget02_00002,1,AfterClear,5,3,general_diamond,,202512010,event_sur_b_0003,"2025-12-15 15:00:00","2026-01-16 10:59:59"
e,event_sur1_charaget02_00004,quest_event_sur1_charaget02,event_sur1_charaget02_00004,4,15,7,70,150,event_sur1_charaget02_00003,1,AfterClear,5,4,general_diamond,,202512010,event_sur_b_0004,"2025-12-15 15:00:00","2026-01-16 10:59:59"
e,event_sur1_charaget02_00005,quest_event_sur1_charaget02,event_sur1_charaget02_00005,5,20,7,70,150,event_sur1_charaget02_00004,1,AfterClear,5,5,event_sur1_00006,,202512010,event_sur_b_0005,"2025-12-15 15:00:00","2026-01-16 10:59:59"
e,event_sur1_charaget02_00006,quest_event_sur1_charaget02,event_sur1_charaget02_00006,6,20,7,70,150,event_sur1_charaget02_00005,1,AfterClear,5,6,general_fragment_00001,,202512010,event_sur_b_0006,"2025-12-15 15:00:00","2026-01-16 10:59:59"
e,event_sur1_charaget02_00007,quest_event_sur1_charaget02,event_sur1_charaget02_00007,7,20,10,100,200,event_sur1_charaget02_00006,1,AfterClear,5,7,general_fragment_00002,,202512010,event_sur_b_0007,"2025-12-15 15:00:00","2026-01-16 10:59:59"
e,event_sur1_charaget02_00008,quest_event_sur1_charaget02,event_sur1_charaget02_00008,8,30,10,100,300,event_sur1_charaget02_00007,1,AfterClear,5,8,general_fragment_00003,,202512010,event_sur_b_0008,"2025-12-15 15:00:00","2026-01-16 10:59:59"
e,event_osh1_1day_00001,quest_event_osh1_1day,event_osh1_1day_00001,1,1,1,100,1000,,1,__NULL__,1,1,general_coin,,202512020,__NULL__,"2026-01-01 04:00:00","2026-01-16 03:59:59"
e,event_osh1_charaget02_00001,quest_event_osh1_charaget02,event_osh1_charaget02_00001,1,10,5,50,75,,1,AfterClear,5,1,general_fragment_00001,,202512020,event_osh_b_0001,"2026-01-05 15:00:00","2026-02-02 10:59:59"
e,event_osh1_charaget02_00002,quest_event_osh1_charaget02,event_osh1_charaget02_00002,2,20,10,100,150,event_osh1_charaget02_00001,1,AfterClear,5,2,general_fragment_00002,,202512020,event_osh_b_0002,"2026-01-05 15:00:00","2026-02-02 10:59:59"
e,event_osh1_charaget02_00003,quest_event_osh1_charaget02,event_osh1_charaget02_00003,3,35,15,150,225,event_osh1_charaget02_00002,1,AfterClear,5,3,general_fragment_00003,,202512020,event_osh_b_0003,"2026-01-05 15:00:00","2026-02-02 10:59:59"
e,event_osh1_charaget01_00001,quest_event_osh1_charaget01,event_osh1_charaget01_00001,1,10,5,50,100,,1,AfterClear,5,1,general_exchange_00001,,202512020,event_osh_a_0001,"2026-01-01 00:00:00","2026-02-02 10:59:59"
e,event_osh1_charaget01_00002,quest_event_osh1_charaget01,event_osh1_charaget01_00002,2,15,7,70,150,event_osh1_charaget01_00001,1,AfterClear,5,2,general_exchange_00001,,202512020,event_osh_a_0002,"2026-01-01 00:00:00","2026-02-02 10:59:59"
e,event_osh1_charaget01_00003,quest_event_osh1_charaget01,event_osh1_charaget01_00003,3,20,10,100,200,event_osh1_charaget01_00002,1,AfterClear,5,3,general_exchange_00001,,202512020,event_osh_a_0003,"2026-01-01 00:00:00","2026-02-02 10:59:59"
e,event_osh1_challenge01_00001,quest_event_osh1_challenge01,event_osh1_challenge01_00001,1,30,15,150,225,event_osh1_charaget01_00003,1,__NULL__,1,1,general_ticket_00002,,202512020,__NULL__,"2026-01-01 00:00:00","2026-02-02 10:59:59"
e,event_osh1_challenge01_00002,quest_event_osh1_challenge01,event_osh1_challenge01_00002,2,40,15,150,225,event_osh1_challenge01_00001,1,__NULL__,1,2,general_ticket_00002,,202512020,__NULL__,"2026-01-01 00:00:00","2026-02-02 10:59:59"
e,event_osh1_challenge01_00003,quest_event_osh1_challenge01,event_osh1_challenge01_00003,3,50,20,200,300,event_osh1_challenge01_00002,1,__NULL__,1,3,event_osh1_00007,,202512020,__NULL__,"2026-01-01 00:00:00","2026-02-02 10:59:59"
e,event_osh1_challenge01_00004,quest_event_osh1_challenge01,event_osh1_challenge01_00004,4,60,25,250,375,event_osh1_challenge01_00003,1,__NULL__,1,4,event_osh1_00011,,202512020,__NULL__,"2026-01-01 00:00:00","2026-02-02 10:59:59"
e,event_glo1_1day_00001,quest_event_glo1_1day,event_glo1_1day_00001,1,1,1,100,100,,1,__NULL__,1,1,general_diamond,,202512020,__NULL__,"2026-01-01 00:00:00","2026-01-05 14:59:59"
e,event_osh1_savage_00001,quest_event_osh1_savage,event_osh1_savage_00001,1,60,25,600,375,event_osh1_charaget01_00003,1,__NULL__,1,1,event_osh1_00009,,202512020,__NULL__,"2026-01-01 00:00:00","2026-02-02 10:59:59"
e,event_osh1_savage_00002,quest_event_osh1_savage,event_osh1_savage_00002,2,70,25,700,375,event_osh1_savage_00001,1,__NULL__,1,2,event_osh1_00010,,202512020,__NULL__,"2026-01-01 00:00:00","2026-02-02 10:59:59"
e,event_osh1_savage_00003,quest_event_osh1_savage,event_osh1_savage_00003,3,80,50,800,750,event_osh1_savage_00002,1,__NULL__,1,3,general_diamond,,202512020,__NULL__,"2026-01-01 00:00:00","2026-02-02 10:59:59"
e,event_jig1_1day_00001,quest_event_jig1_1day,event_jig1_1day_00001,1,1,1,100,1500,,1,__NULL__,1,1,general_diamond,,202601010,__NULL__,"2026-01-16 15:00:00","2026-02-2 03:59:59"
e,event_jig1_charaget02_00001,quest_event_jig1_charaget02,event_jig1_charaget02_00001,1,10,5,50,100,,1,AfterClear,5,1,event_jig1_00004,,202601010,event_jig_b_0001,"2026-01-21 15:00:00","2026-02-16 14:59:59"
e,event_jig1_charaget02_00002,quest_event_jig1_charaget02,event_jig1_charaget02_00002,2,15,5,50,100,event_jig1_charaget02_00001,1,AfterClear,5,2,general_diamond,,202601010,event_jig_b_0002,"2026-01-21 15:00:00","2026-02-16 14:59:59"
e,event_jig1_charaget02_00003,quest_event_jig1_charaget02,event_jig1_charaget02_00003,3,20,7,70,150,event_jig1_charaget02_00002,1,AfterClear,5,3,general_diamond,,202601010,event_jig_b_0003,"2026-01-21 15:00:00","2026-02-16 14:59:59"
e,event_jig1_charaget02_00004,quest_event_jig1_charaget02,event_jig1_charaget02_00004,4,20,7,70,150,event_jig1_charaget02_00003,1,AfterClear,5,4,general_diamond,,202601010,event_jig_b_0004,"2026-01-21 15:00:00","2026-02-16 14:59:59"
e,event_jig1_charaget02_00005,quest_event_jig1_charaget02,event_jig1_charaget02_00005,5,25,10,100,200,event_jig1_charaget02_00004,1,AfterClear,5,5,event_jig1_00006,,202601010,event_jig_b_0005,"2026-01-21 15:00:00","2026-02-16 14:59:59"
e,event_jig1_charaget02_00006,quest_event_jig1_charaget02,event_jig1_charaget02_00006,6,30,10,100,300,event_jig1_charaget02_00005,1,AfterClear,5,6,general_fragment_00003,,202601010,event_jig_b_0006,"2026-01-21 15:00:00","2026-02-16 14:59:59"
e,event_jig1_charaget01_00001,quest_event_jig1_charaget01,event_jig1_charaget01_00001,1,10,5,50,100,,1,AfterClear,5,1,event_jig1_00001,,202601010,event_jig_a_0001,"2026-01-16 15:00:00","2026-02-16 10:59:59"
e,event_jig1_charaget01_00002,quest_event_jig1_charaget01,event_jig1_charaget01_00002,2,15,5,50,100,event_jig1_charaget01_00001,1,AfterClear,5,2,general_diamond,,202601010,event_jig_a_0002,"2026-01-16 15:00:00","2026-02-16 10:59:59"
e,event_jig1_charaget01_00003,quest_event_jig1_charaget01,event_jig1_charaget01_00003,3,20,7,70,150,event_jig1_charaget01_00002,1,AfterClear,5,3,general_diamond,,202601010,event_jig_a_0003,"2026-01-16 15:00:00","2026-02-16 10:59:59"
e,event_jig1_charaget01_00004,quest_event_jig1_charaget01,event_jig1_charaget01_00004,4,20,7,70,150,event_jig1_charaget01_00003,1,AfterClear,5,4,general_diamond,,202601010,event_jig_a_0004,"2026-01-16 15:00:00","2026-02-16 10:59:59"
e,event_jig1_charaget01_00005,quest_event_jig1_charaget01,event_jig1_charaget01_00005,5,25,10,100,200,event_jig1_charaget01_00004,1,AfterClear,5,5,event_jig1_00002,,202601010,event_jig_a_0005,"2026-01-16 15:00:00","2026-02-16 10:59:59"
e,event_jig1_charaget01_00006,quest_event_jig1_charaget01,event_jig1_charaget01_00006,6,30,10,100,300,event_jig1_charaget01_00005,1,AfterClear,5,6,general_fragment_00003,,202601010,event_jig_a_0006,"2026-01-16 15:00:00","2026-02-16 10:59:59"
e,event_jig1_challenge01_00001,quest_event_jig1_challenge01,event_jig1_challenge01_00001,1,30,10,100,150,event_jig1_charaget01_00006,1,__NULL__,1,1,general_ticket_00002,,202601010,__NULL__,"2026-01-16 15:00:00","2026-02-16 10:59:59"
e,event_jig1_challenge01_00002,quest_event_jig1_challenge01,event_jig1_challenge01_00002,2,30,15,150,225,event_jig1_challenge01_00001,1,__NULL__,1,2,event_jig1_00009,,202601010,__NULL__,"2026-01-16 15:00:00","2026-02-16 10:59:59"
e,event_jig1_challenge01_00003,quest_event_jig1_challenge01,event_jig1_challenge01_00003,3,40,15,150,225,event_jig1_challenge01_00002,1,__NULL__,1,3,event_jig1_00008,,202601010,__NULL__,"2026-01-16 15:00:00","2026-02-16 10:59:59"
e,event_jig1_challenge01_00004,quest_event_jig1_challenge01,event_jig1_challenge01_00004,4,50,20,200,300,event_jig1_challenge01_00003,1,__NULL__,1,4,event_jig1_00007,,202601010,__NULL__,"2026-01-16 15:00:00","2026-02-16 10:59:59"
e,event_jig1_savage_00001,quest_event_jig1_savage,event_jig1_savage_00001,1,70,25,700,375,event_jig1_charaget01_00006,1,__NULL__,1,1,general_diamond,,202601010,__NULL__,"2026-01-16 15:00:00","2026-02-16 10:59:59"
e,event_jig1_savage_00002,quest_event_jig1_savage,event_jig1_savage_00002,2,70,25,700,375,event_jig1_savage_00001,1,__NULL__,1,2,general_diamond,,202601010,__NULL__,"2026-01-16 15:00:00","2026-02-16 10:59:59"
e,event_jig1_savage_00003,quest_event_jig1_savage,event_jig1_savage_00003,3,80,50,1000,400,event_jig1_savage_00002,1,__NULL__,1,3,event_jig1_00010,,202601010,__NULL__,"2026-01-16 15:00:00","2026-02-16 10:59:59"
e,event_you1_1day_00001,quest_event_you1_1day,event_you1_1day_00001,1,1,1,100,1500,,1,__NULL__,1,1,general_diamond,,202602010,__NULL__,"2026-02-02 15:00:00","2026-02-16 03:59:59"
e,event_you1_charaget01_00001,quest_event_you1_charaget01,event_you1_charaget01_00001,1,10,5,50,100,,1,AfterClear,5,1,event_you1_00001,,202602010,event_you_a_0001,"2026-02-02 15:00:00","2026-03-02 10:59:59"
e,event_you1_charaget01_00002,quest_event_you1_charaget01,event_you1_charaget01_00002,2,15,5,50,100,event_you1_charaget01_00001,1,AfterClear,5,2,general_diamond,,202602010,event_you_a_0002,"2026-02-02 15:00:00","2026-03-02 10:59:59"
e,event_you1_charaget01_00003,quest_event_you1_charaget01,event_you1_charaget01_00003,3,20,7,70,150,event_you1_charaget01_00002,1,AfterClear,5,3,general_diamond,,202602010,event_you_a_0003,"2026-02-02 15:00:00","2026-03-02 10:59:59"
e,event_you1_charaget01_00004,quest_event_you1_charaget01,event_you1_charaget01_00004,4,20,7,70,150,event_you1_charaget01_00003,1,AfterClear,5,4,general_diamond,,202602010,event_you_a_0004,"2026-02-02 15:00:00","2026-03-02 10:59:59"
e,event_you1_charaget01_00005,quest_event_you1_charaget01,event_you1_charaget01_00005,5,25,10,100,200,event_you1_charaget01_00004,1,AfterClear,5,5,event_you1_00002,,202602010,event_you_a_0005,"2026-02-02 15:00:00","2026-03-02 10:59:59"
e,event_you1_charaget01_00006,quest_event_you1_charaget01,event_you1_charaget01_00006,6,30,10,100,300,event_you1_charaget01_00005,1,AfterClear,5,6,general_fragment_00001,,202602010,event_you_a_0006,"2026-02-02 15:00:00","2026-03-02 10:59:59"
e,event_you1_charaget02_00001,quest_event_you1_charaget02,event_you1_charaget02_00001,1,10,5,50,100,,1,AfterClear,5,1,event_you1_00004,,202602010,event_you_b_0001,"2026-02-06 15:00:00","2026-03-02 10:59:59"
e,event_you1_charaget02_00002,quest_event_you1_charaget02,event_you1_charaget02_00002,2,15,5,50,100,event_you1_charaget02_00001,1,AfterClear,5,2,general_diamond,,202602010,event_you_b_0002,"2026-02-06 15:00:00","2026-03-02 10:59:59"
e,event_you1_charaget02_00003,quest_event_you1_charaget02,event_you1_charaget02_00003,3,20,7,70,150,event_you1_charaget02_00002,1,AfterClear,5,3,general_diamond,,202602010,event_you_b_0003,"2026-02-06 15:00:00","2026-03-02 10:59:59"
e,event_you1_charaget02_00004,quest_event_you1_charaget02,event_you1_charaget02_00004,4,20,7,70,150,event_you1_charaget02_00003,1,AfterClear,5,4,general_diamond,,202602010,event_you_b_0004,"2026-02-06 15:00:00","2026-03-02 10:59:59"
e,event_you1_charaget02_00005,quest_event_you1_charaget02,event_you1_charaget02_00005,5,25,10,100,200,event_you1_charaget02_00004,1,AfterClear,5,5,event_you1_00006,,202602010,event_you_b_0005,"2026-02-06 15:00:00","2026-03-02 10:59:59"
e,event_you1_charaget02_00006,quest_event_you1_charaget02,event_you1_charaget02_00006,6,30,10,100,300,event_you1_charaget02_00005,1,AfterClear,5,6,general_fragment_00001,,202602010,event_you_b_0006,"2026-02-06 15:00:00","2026-03-02 10:59:59"
e,event_you1_challenge_00001,quest_event_you1_challenge,event_you1_challenge_00001,1,30,10,100,150,event_you1_charaget01_00006,1,__NULL__,1,1,general_ticket_00002,,202602010,__NULL__,"2026-02-02 15:00:00","2026-03-02 10:59:59"
e,event_you1_challenge_00002,quest_event_you1_challenge,event_you1_challenge_00002,2,30,15,150,225,event_you1_challenge_00001,1,__NULL__,1,2,general_ticket_00002,,202602010,__NULL__,"2026-02-02 15:00:00","2026-03-02 10:59:59"
e,event_you1_challenge_00003,quest_event_you1_challenge,event_you1_challenge_00003,3,40,15,150,225,event_you1_challenge_00002,1,__NULL__,1,3,event_you1_00008,,202602010,__NULL__,"2026-02-02 15:00:00","2026-03-02 10:59:59"
e,event_you1_challenge_00004,quest_event_you1_challenge,event_you1_challenge_00004,4,50,20,200,300,event_you1_challenge_00003,1,__NULL__,1,4,event_you1_00007,,202602010,__NULL__,"2026-02-02 15:00:00","2026-03-02 10:59:59"
e,event_you1_savage_00001,quest_event_you1_savage,event_you1_savage_00001,1,70,25,700,375,event_you1_charaget01_00006,1,__NULL__,1,1,general_diamond,,202602010,__NULL__,"2026-02-02 15:00:00","2026-03-02 10:59:59"
e,event_you1_savage_00002,quest_event_you1_savage,event_you1_savage_00002,2,70,25,700,375,event_you1_savage_00001,1,__NULL__,1,2,general_diamond,,202602010,__NULL__,"2026-02-02 15:00:00","2026-03-02 10:59:59"
e,event_you1_savage_00003,quest_event_you1_savage,event_you1_savage_00003,3,80,50,1000,400,event_you1_savage_00002,1,__NULL__,1,3,event_you1_00009,,202602010,__NULL__,"2026-02-02 15:00:00","2026-03-02 10:59:59"
e,event_kim1_savage_00001,quest_event_kim1_savage,event_kim1_savage_00001,1,70,25,700,375,,1,__NULL__,1,1,general_diamond,,202602020,__NULL__,"2026-02-16 15:00:00","2026-03-16 10:59:59"
e,event_kim1_savage_00002,quest_event_kim1_savage,event_kim1_savage_00002,2,70,25,700,375,event_kim1_savage_00001,1,__NULL__,1,2,general_diamond,,202602020,__NULL__,"2026-02-16 15:00:00","2026-03-16 10:59:59"
e,event_kim1_savage_00003,quest_event_kim1_savage,event_kim1_savage_00003,3,80,50,1000,400,event_kim1_savage_00002,1,__NULL__,1,3,event_jig1_00010,,202602020,__NULL__,"2026-02-16 15:00:00","2026-03-16 10:59:59"
e,event_kim1_1day_00001,quest_event_kim1_1day,event_kim1_1day_00001,1,1,1,100,1500,,1,__NULL__,1,1,general_diamond,,202602020,__NULL__,"2026-02-16 15:00:00","2026-03-02 03:59:59"
e,event_kim1_charaget01_00001,quest_event_kim1_charaget01,event_kim1_charaget01_00001,1,5,5,50,100,,1,AfterClear,5,1,event_kim1_00005,,202602020,event_kim_a_0001,"2026-02-16 15:00:00","2026-03-16 10:59:59"
e,event_kim1_charaget01_00002,quest_event_kim1_charaget01,event_kim1_charaget01_00002,2,10,5,50,100,event_kim1_charaget01_00001,1,AfterClear,5,2,event_kim1_00005,,202602020,event_kim_a_0002,"2026-02-16 15:00:00","2026-03-16 10:59:59"
e,event_kim1_charaget01_00003,quest_event_kim1_charaget01,event_kim1_charaget01_00003,3,20,7,70,150,event_kim1_charaget01_00002,1,AfterClear,5,3,event_kim1_00005,,202602020,event_kim_a_0003,"2026-02-16 15:00:00","2026-03-16 10:59:59"
e,event_kim1_charaget01_00004,quest_event_kim1_charaget01,event_kim1_charaget01_00004,4,30,7,70,150,event_kim1_charaget01_00003,1,AfterClear,5,4,event_kim1_00005,,202602020,event_kim_a_0004,"2026-02-16 15:00:00","2026-03-16 10:59:59"
e,event_kim1_charaget02_00001,quest_event_kim1_charaget02,event_kim1_charaget02_00001,1,10,5,50,100,,1,AfterClear,5,1,general_diamond,,202602020,event_kim_b_0001,"2026-02-20 15:00:00","2026-03-16 10:59:59"
e,event_kim1_charaget02_00002,quest_event_kim1_charaget02,event_kim1_charaget02_00002,2,15,5,50,100,event_kim1_charaget02_00001,1,AfterClear,5,2,general_diamond,,202602020,event_kim_b_0002,"2026-02-20 15:00:00","2026-03-16 10:59:59"
e,event_kim1_charaget02_00003,quest_event_kim1_charaget02,event_kim1_charaget02_00003,3,20,7,70,150,event_kim1_charaget02_00002,1,AfterClear,5,3,general_diamond,,202602020,event_kim_b_0003,"2026-02-20 15:00:00","2026-03-16 10:59:59"
e,event_kim1_charaget02_00004,quest_event_kim1_charaget02,event_kim1_charaget02_00004,4,20,7,70,150,event_kim1_charaget02_00003,1,AfterClear,5,4,general_fragment_00001,,202602020,event_kim_b_0004,"2026-02-20 15:00:00","2026-03-16 10:59:59"
e,event_kim1_charaget02_00005,quest_event_kim1_charaget02,event_kim1_charaget02_00005,5,25,10,100,200,event_kim1_charaget02_00004,1,AfterClear,5,5,general_fragment_00002,,202602020,event_kim_b_0005,"2026-02-20 15:00:00","2026-03-16 10:59:59"
e,event_kim1_charaget02_00006,quest_event_kim1_charaget02,event_kim1_charaget02_00006,6,30,10,100,300,event_kim1_charaget02_00005,1,AfterClear,5,6,general_fragment_00003,,202602020,event_kim_b_0006,"2026-02-20 15:00:00","2026-03-16 10:59:59"
e,event_kim1_challenge_00001,quest_event_kim1_challenge,event_kim1_challenge_00001,1,30,10,100,150,,1,__NULL__,1,1,general_ticket_00002,,202602020,__NULL__,"2026-02-16 15:00:00","2026-03-16 10:59:59"
e,event_kim1_challenge_00002,quest_event_kim1_challenge,event_kim1_challenge_00002,2,30,15,150,225,event_kim1_challenge_00001,1,__NULL__,1,2,general_ticket_00002,,202602020,__NULL__,"2026-02-16 15:00:00","2026-03-16 10:59:59"
e,event_kim1_challenge_00003,quest_event_kim1_challenge,event_kim1_challenge_00003,3,40,15,150,225,event_kim1_challenge_00002,1,__NULL__,1,3,general_ticket_00002,,202602020,__NULL__,"2026-02-16 15:00:00","2026-03-16 10:59:59"
e,event_kim1_challenge_00004,quest_event_kim1_challenge,event_kim1_challenge_00004,4,50,20,200,300,event_kim1_challenge_00003,1,__NULL__,1,4,general_ticket_00002,,202602020,__NULL__,"2026-02-16 15:00:00","2026-03-16 10:59:59"
```

---

<!-- FILE: ./projects/glow-masterdata/MstStageI18n.csv -->
## ./projects/glow-masterdata/MstStageI18n.csv

```csv
ENABLE,release_key,id,mst_stage_id,language,name,category_name
e,999999999,develop_001_ja,develop_001,ja,テストステージ001,
e,999999999,develop_002_ja,develop_002,ja,テストステージ002,
e,999999999,develop_plan_test_stage001_ja,develop_plan_test_stage001,ja,プランテストステージ1,
e,999999999,develop_plan_test_stage002_ja,develop_plan_test_stage002,ja,プランテストステージ2,
e,999999999,develop_plan_test_stage003_ja,develop_plan_test_stage003,ja,プランテストステージ3,
e,999999999,develop_plan_test_stage004_ja,develop_plan_test_stage004,ja,プランテストステージ4,
e,999999999,develop_plan_test_stage004_red_ja,develop_plan_test_stage004_red,ja,プランテストステージ_Radtest,
e,999999999,develop_plan_test_stage005_blue_ja,develop_plan_test_stage005_blue,ja,プランテストステージ_Bluetest,
e,999999999,develop_plan_test_stage006_yellow_ja,develop_plan_test_stage006_yellow,ja,プランテストステージ_Yellowtest,
e,999999999,develop_plan_test_stage007_green_ja,develop_plan_test_stage007_green,ja,プランテストステージ_Greentest,
e,999999999,develop_plan_test_stage008_colorless_ja,develop_plan_test_stage008_colorless,ja,プランテストステージ_Colorless,
e,999999999,plan_test_stage_powerup01_ja,plan_test_stage_powerup01,ja,プランテストステージ_パワーアップコマ,
e,999999999,plan_test_stage_powerdown01_ja,plan_test_stage_powerdown01,ja,プランテストステージ_パワーダウンコマ,
e,999999999,plan_test_stage_slipdamage01_ja,plan_test_stage_slipdamage01,ja,プランテストステージ_ダメージコマ,
e,999999999,plan_test_stage_gust01_ja,plan_test_stage_gust01,ja,プランテストステージ_突風コマ,
e,999999999,plan_test_stage_poison01_ja,plan_test_stage_poison01,ja,プランテストステージ_毒コマ,
e,999999999,plan_test_stage_normal01_ja,plan_test_stage_normal01,ja,プランテストステージ_スタン,
e,999999999,plan_test_stage_normal01_02_ja,plan_test_stage_normal01_02,ja,プランテストステージ_氷結,
e,999999999,plan_test_stage_normal01_03_ja,plan_test_stage_normal01_03,ja,プランテストステージ_火傷,
e,999999999,plan_test_stage_normal01_04_ja,plan_test_stage_normal01_04,ja,プランテストステージ_ノックバック,
e,202509010,tutorial_1_ja,tutorial_1,ja,チュートリアルステージ1,
e,202509010,tutorial_2_ja,tutorial_2,ja,チュートリアルステージ2,
e,202509010,tutorial_3_ja,tutorial_3,ja,チュートリアルステージ3,
e,202509010,normal_spy_00001_ja,normal_spy_00001,ja,SPY×FAMILY,
e,202509010,normal_spy_00002_ja,normal_spy_00002,ja,SPY×FAMILY,
e,202509010,normal_spy_00003_ja,normal_spy_00003,ja,SPY×FAMILY,
e,202509010,normal_spy_00004_ja,normal_spy_00004,ja,SPY×FAMILY,
e,202509010,normal_spy_00005_ja,normal_spy_00005,ja,SPY×FAMILY,
e,202509010,normal_spy_00006_ja,normal_spy_00006,ja,SPY×FAMILY,
e,202509010,hard_spy_00001_ja,hard_spy_00001,ja,SPY×FAMILY,
e,202509010,hard_spy_00002_ja,hard_spy_00002,ja,SPY×FAMILY,
e,202509010,hard_spy_00003_ja,hard_spy_00003,ja,SPY×FAMILY,
e,202509010,hard_spy_00004_ja,hard_spy_00004,ja,SPY×FAMILY,
e,202509010,hard_spy_00005_ja,hard_spy_00005,ja,SPY×FAMILY,
e,202509010,hard_spy_00006_ja,hard_spy_00006,ja,SPY×FAMILY,
e,202509010,veryhard_spy_00001_ja,veryhard_spy_00001,ja,SPY×FAMILY,
e,202509010,veryhard_spy_00002_ja,veryhard_spy_00002,ja,SPY×FAMILY,
e,202509010,veryhard_spy_00003_ja,veryhard_spy_00003,ja,SPY×FAMILY,
e,202509010,veryhard_spy_00004_ja,veryhard_spy_00004,ja,SPY×FAMILY,
e,202509010,veryhard_spy_00005_ja,veryhard_spy_00005,ja,SPY×FAMILY,
e,202509010,veryhard_spy_00006_ja,veryhard_spy_00006,ja,SPY×FAMILY,
e,202509010,normal_gom_00001_ja,normal_gom_00001,ja,姫様“拷問”の時間です,
e,202509010,normal_gom_00002_ja,normal_gom_00002,ja,姫様“拷問”の時間です,
e,202509010,normal_gom_00003_ja,normal_gom_00003,ja,姫様“拷問”の時間です,
e,202509010,normal_gom_00004_ja,normal_gom_00004,ja,姫様“拷問”の時間です,
e,202509010,normal_gom_00005_ja,normal_gom_00005,ja,姫様“拷問”の時間です,
e,202509010,normal_gom_00006_ja,normal_gom_00006,ja,姫様“拷問”の時間です,
e,202509010,hard_gom_00001_ja,hard_gom_00001,ja,姫様“拷問”の時間です,
e,202509010,hard_gom_00002_ja,hard_gom_00002,ja,姫様“拷問”の時間です,
e,202509010,hard_gom_00003_ja,hard_gom_00003,ja,姫様“拷問”の時間です,
e,202509010,hard_gom_00004_ja,hard_gom_00004,ja,姫様“拷問”の時間です,
e,202509010,hard_gom_00005_ja,hard_gom_00005,ja,姫様“拷問”の時間です,
e,202509010,hard_gom_00006_ja,hard_gom_00006,ja,姫様“拷問”の時間です,
e,202509010,veryhard_gom_00001_ja,veryhard_gom_00001,ja,姫様“拷問”の時間です,
e,202509010,veryhard_gom_00002_ja,veryhard_gom_00002,ja,姫様“拷問”の時間です,
e,202509010,veryhard_gom_00003_ja,veryhard_gom_00003,ja,姫様“拷問”の時間です,
e,202509010,veryhard_gom_00004_ja,veryhard_gom_00004,ja,姫様“拷問”の時間です,
e,202509010,veryhard_gom_00005_ja,veryhard_gom_00005,ja,姫様“拷問”の時間です,
e,202509010,veryhard_gom_00006_ja,veryhard_gom_00006,ja,姫様“拷問”の時間です,
e,202509010,normal_aka_00001_ja,normal_aka_00001,ja,ラーメン赤猫,
e,202509010,normal_aka_00002_ja,normal_aka_00002,ja,ラーメン赤猫,
e,202509010,normal_aka_00003_ja,normal_aka_00003,ja,ラーメン赤猫,
e,202509010,hard_aka_00001_ja,hard_aka_00001,ja,ラーメン赤猫,
e,202509010,hard_aka_00002_ja,hard_aka_00002,ja,ラーメン赤猫,
e,202509010,hard_aka_00003_ja,hard_aka_00003,ja,ラーメン赤猫,
e,202509010,veryhard_aka_00001_ja,veryhard_aka_00001,ja,ラーメン赤猫,
e,202509010,veryhard_aka_00002_ja,veryhard_aka_00002,ja,ラーメン赤猫,
e,202509010,veryhard_aka_00003_ja,veryhard_aka_00003,ja,ラーメン赤猫,
e,202509010,normal_glo1_00001_ja,normal_glo1_00001,ja,"リミックスクエスト vol.1",
e,202509010,normal_glo1_00002_ja,normal_glo1_00002,ja,"リミックスクエスト vol.1",
e,202509010,normal_glo1_00003_ja,normal_glo1_00003,ja,"リミックスクエスト vol.1",
e,202509010,hard_glo1_00001_ja,hard_glo1_00001,ja,"リミックスクエスト vol.1",
e,202509010,hard_glo1_00002_ja,hard_glo1_00002,ja,"リミックスクエスト vol.1",
e,202509010,hard_glo1_00003_ja,hard_glo1_00003,ja,"リミックスクエスト vol.1",
e,202509010,veryhard_glo1_00001_ja,veryhard_glo1_00001,ja,"リミックスクエスト vol.1",
e,202509010,veryhard_glo1_00002_ja,veryhard_glo1_00002,ja,"リミックスクエスト vol.1",
e,202509010,veryhard_glo1_00003_ja,veryhard_glo1_00003,ja,"リミックスクエスト vol.1",
e,202509010,normal_dan_00001_ja,normal_dan_00001,ja,ダンダダン,
e,202509010,normal_dan_00002_ja,normal_dan_00002,ja,ダンダダン,
e,202509010,normal_dan_00003_ja,normal_dan_00003,ja,ダンダダン,
e,202509010,normal_dan_00004_ja,normal_dan_00004,ja,ダンダダン,
e,202509010,normal_dan_00005_ja,normal_dan_00005,ja,ダンダダン,
e,202509010,normal_dan_00006_ja,normal_dan_00006,ja,ダンダダン,
e,202509010,hard_dan_00001_ja,hard_dan_00001,ja,ダンダダン,
e,202509010,hard_dan_00002_ja,hard_dan_00002,ja,ダンダダン,
e,202509010,hard_dan_00003_ja,hard_dan_00003,ja,ダンダダン,
e,202509010,hard_dan_00004_ja,hard_dan_00004,ja,ダンダダン,
e,202509010,hard_dan_00005_ja,hard_dan_00005,ja,ダンダダン,
e,202509010,hard_dan_00006_ja,hard_dan_00006,ja,ダンダダン,
e,202509010,veryhard_dan_00001_ja,veryhard_dan_00001,ja,ダンダダン,
e,202509010,veryhard_dan_00002_ja,veryhard_dan_00002,ja,ダンダダン,
e,202509010,veryhard_dan_00003_ja,veryhard_dan_00003,ja,ダンダダン,
e,202509010,veryhard_dan_00004_ja,veryhard_dan_00004,ja,ダンダダン,
e,202509010,veryhard_dan_00005_ja,veryhard_dan_00005,ja,ダンダダン,
e,202509010,veryhard_dan_00006_ja,veryhard_dan_00006,ja,ダンダダン,
e,202509010,normal_jig_00001_ja,normal_jig_00001,ja,地獄楽,
e,202509010,normal_jig_00002_ja,normal_jig_00002,ja,地獄楽,
e,202509010,normal_jig_00003_ja,normal_jig_00003,ja,地獄楽,
e,202509010,normal_jig_00004_ja,normal_jig_00004,ja,地獄楽,
e,202509010,normal_jig_00005_ja,normal_jig_00005,ja,地獄楽,
e,202509010,normal_jig_00006_ja,normal_jig_00006,ja,地獄楽,
e,202509010,hard_jig_00001_ja,hard_jig_00001,ja,地獄楽,
e,202509010,hard_jig_00002_ja,hard_jig_00002,ja,地獄楽,
e,202509010,hard_jig_00003_ja,hard_jig_00003,ja,地獄楽,
e,202509010,hard_jig_00004_ja,hard_jig_00004,ja,地獄楽,
e,202509010,hard_jig_00005_ja,hard_jig_00005,ja,地獄楽,
e,202509010,hard_jig_00006_ja,hard_jig_00006,ja,地獄楽,
e,202509010,veryhard_jig_00001_ja,veryhard_jig_00001,ja,地獄楽,
e,202509010,veryhard_jig_00002_ja,veryhard_jig_00002,ja,地獄楽,
e,202509010,veryhard_jig_00003_ja,veryhard_jig_00003,ja,地獄楽,
e,202509010,veryhard_jig_00004_ja,veryhard_jig_00004,ja,地獄楽,
e,202509010,veryhard_jig_00005_ja,veryhard_jig_00005,ja,地獄楽,
e,202509010,veryhard_jig_00006_ja,veryhard_jig_00006,ja,地獄楽,
e,202509010,normal_tak_00001_ja,normal_tak_00001,ja,タコピーの原罪,
e,202509010,normal_tak_00002_ja,normal_tak_00002,ja,タコピーの原罪,
e,202509010,normal_tak_00003_ja,normal_tak_00003,ja,タコピーの原罪,
e,202509010,hard_tak_00001_ja,hard_tak_00001,ja,タコピーの原罪,
e,202509010,hard_tak_00002_ja,hard_tak_00002,ja,タコピーの原罪,
e,202509010,hard_tak_00003_ja,hard_tak_00003,ja,タコピーの原罪,
e,202509010,veryhard_tak_00001_ja,veryhard_tak_00001,ja,タコピーの原罪,
e,202509010,veryhard_tak_00002_ja,veryhard_tak_00002,ja,タコピーの原罪,
e,202509010,veryhard_tak_00003_ja,veryhard_tak_00003,ja,タコピーの原罪,
e,202509010,normal_glo2_00001_ja,normal_glo2_00001,ja,"リミックスクエスト vol.2",
e,202509010,normal_glo2_00002_ja,normal_glo2_00002,ja,"リミックスクエスト vol.2",
e,202509010,normal_glo2_00003_ja,normal_glo2_00003,ja,"リミックスクエスト vol.2",
e,202509010,hard_glo2_00001_ja,hard_glo2_00001,ja,"リミックスクエスト vol.2",
e,202509010,hard_glo2_00002_ja,hard_glo2_00002,ja,"リミックスクエスト vol.2",
e,202509010,hard_glo2_00003_ja,hard_glo2_00003,ja,"リミックスクエスト vol.2",
e,202509010,veryhard_glo2_00001_ja,veryhard_glo2_00001,ja,"リミックスクエスト vol.2",
e,202509010,veryhard_glo2_00002_ja,veryhard_glo2_00002,ja,"リミックスクエスト vol.2",
e,202509010,veryhard_glo2_00003_ja,veryhard_glo2_00003,ja,"リミックスクエスト vol.2",
e,202509010,normal_chi_00001_ja,normal_chi_00001,ja,チェンソーマン,
e,202509010,normal_chi_00002_ja,normal_chi_00002,ja,チェンソーマン,
e,202509010,normal_chi_00003_ja,normal_chi_00003,ja,チェンソーマン,
e,202509010,normal_chi_00004_ja,normal_chi_00004,ja,チェンソーマン,
e,202509010,normal_chi_00005_ja,normal_chi_00005,ja,チェンソーマン,
e,202509010,normal_chi_00006_ja,normal_chi_00006,ja,チェンソーマン,
e,202509010,hard_chi_00001_ja,hard_chi_00001,ja,チェンソーマン,
e,202509010,hard_chi_00002_ja,hard_chi_00002,ja,チェンソーマン,
e,202509010,hard_chi_00003_ja,hard_chi_00003,ja,チェンソーマン,
e,202509010,hard_chi_00004_ja,hard_chi_00004,ja,チェンソーマン,
e,202509010,hard_chi_00005_ja,hard_chi_00005,ja,チェンソーマン,
e,202509010,hard_chi_00006_ja,hard_chi_00006,ja,チェンソーマン,
e,202509010,veryhard_chi_00001_ja,veryhard_chi_00001,ja,チェンソーマン,
e,202509010,veryhard_chi_00002_ja,veryhard_chi_00002,ja,チェンソーマン,
e,202509010,veryhard_chi_00003_ja,veryhard_chi_00003,ja,チェンソーマン,
e,202509010,veryhard_chi_00004_ja,veryhard_chi_00004,ja,チェンソーマン,
e,202509010,veryhard_chi_00005_ja,veryhard_chi_00005,ja,チェンソーマン,
e,202509010,veryhard_chi_00006_ja,veryhard_chi_00006,ja,チェンソーマン,
e,202509010,normal_sur_00001_ja,normal_sur_00001,ja,魔都精兵のスレイブ,
e,202509010,normal_sur_00002_ja,normal_sur_00002,ja,魔都精兵のスレイブ,
e,202509010,normal_sur_00003_ja,normal_sur_00003,ja,魔都精兵のスレイブ,
e,202509010,normal_sur_00004_ja,normal_sur_00004,ja,魔都精兵のスレイブ,
e,202509010,normal_sur_00005_ja,normal_sur_00005,ja,魔都精兵のスレイブ,
e,202509010,normal_sur_00006_ja,normal_sur_00006,ja,魔都精兵のスレイブ,
e,202509010,hard_sur_00001_ja,hard_sur_00001,ja,魔都精兵のスレイブ,
e,202509010,hard_sur_00002_ja,hard_sur_00002,ja,魔都精兵のスレイブ,
e,202509010,hard_sur_00003_ja,hard_sur_00003,ja,魔都精兵のスレイブ,
e,202509010,hard_sur_00004_ja,hard_sur_00004,ja,魔都精兵のスレイブ,
e,202509010,hard_sur_00005_ja,hard_sur_00005,ja,魔都精兵のスレイブ,
e,202509010,hard_sur_00006_ja,hard_sur_00006,ja,魔都精兵のスレイブ,
e,202509010,veryhard_sur_00001_ja,veryhard_sur_00001,ja,魔都精兵のスレイブ,
e,202509010,veryhard_sur_00002_ja,veryhard_sur_00002,ja,魔都精兵のスレイブ,
e,202509010,veryhard_sur_00003_ja,veryhard_sur_00003,ja,魔都精兵のスレイブ,
e,202509010,veryhard_sur_00004_ja,veryhard_sur_00004,ja,魔都精兵のスレイブ,
e,202509010,veryhard_sur_00005_ja,veryhard_sur_00005,ja,魔都精兵のスレイブ,
e,202509010,veryhard_sur_00006_ja,veryhard_sur_00006,ja,魔都精兵のスレイブ,
e,202509010,normal_rik_00001_ja,normal_rik_00001,ja,トマトイプーのリコピン,
e,202509010,normal_rik_00002_ja,normal_rik_00002,ja,トマトイプーのリコピン,
e,202509010,normal_rik_00003_ja,normal_rik_00003,ja,トマトイプーのリコピン,
e,202509010,hard_rik_00001_ja,hard_rik_00001,ja,トマトイプーのリコピン,
e,202509010,hard_rik_00002_ja,hard_rik_00002,ja,トマトイプーのリコピン,
e,202509010,hard_rik_00003_ja,hard_rik_00003,ja,トマトイプーのリコピン,
e,202509010,veryhard_rik_00001_ja,veryhard_rik_00001,ja,トマトイプーのリコピン,
e,202509010,veryhard_rik_00002_ja,veryhard_rik_00002,ja,トマトイプーのリコピン,
e,202509010,veryhard_rik_00003_ja,veryhard_rik_00003,ja,トマトイプーのリコピン,
e,202509010,normal_glo3_00001_ja,normal_glo3_00001,ja,"リミックスクエスト vol.3",
e,202509010,normal_glo3_00002_ja,normal_glo3_00002,ja,"リミックスクエスト vol.3",
e,202509010,normal_glo3_00003_ja,normal_glo3_00003,ja,"リミックスクエスト vol.3",
e,202509010,hard_glo3_00001_ja,hard_glo3_00001,ja,"リミックスクエスト vol.3",
e,202509010,hard_glo3_00002_ja,hard_glo3_00002,ja,"リミックスクエスト vol.3",
e,202509010,hard_glo3_00003_ja,hard_glo3_00003,ja,"リミックスクエスト vol.3",
e,202509010,veryhard_glo3_00001_ja,veryhard_glo3_00001,ja,"リミックスクエスト vol.3",
e,202509010,veryhard_glo3_00002_ja,veryhard_glo3_00002,ja,"リミックスクエスト vol.3",
e,202509010,veryhard_glo3_00003_ja,veryhard_glo3_00003,ja,"リミックスクエスト vol.3",
e,202509010,normal_mag_00001_ja,normal_mag_00001,ja,株式会社マジルミエ,
e,202509010,normal_mag_00002_ja,normal_mag_00002,ja,株式会社マジルミエ,
e,202509010,normal_mag_00003_ja,normal_mag_00003,ja,株式会社マジルミエ,
e,202509010,normal_mag_00004_ja,normal_mag_00004,ja,株式会社マジルミエ,
e,202509010,normal_mag_00005_ja,normal_mag_00005,ja,株式会社マジルミエ,
e,202509010,normal_mag_00006_ja,normal_mag_00006,ja,株式会社マジルミエ,
e,202509010,hard_mag_00001_ja,hard_mag_00001,ja,株式会社マジルミエ,
e,202509010,hard_mag_00002_ja,hard_mag_00002,ja,株式会社マジルミエ,
e,202509010,hard_mag_00003_ja,hard_mag_00003,ja,株式会社マジルミエ,
e,202509010,hard_mag_00004_ja,hard_mag_00004,ja,株式会社マジルミエ,
e,202509010,hard_mag_00005_ja,hard_mag_00005,ja,株式会社マジルミエ,
e,202509010,hard_mag_00006_ja,hard_mag_00006,ja,株式会社マジルミエ,
e,202509010,veryhard_mag_00001_ja,veryhard_mag_00001,ja,株式会社マジルミエ,
e,202509010,veryhard_mag_00002_ja,veryhard_mag_00002,ja,株式会社マジルミエ,
e,202509010,veryhard_mag_00003_ja,veryhard_mag_00003,ja,株式会社マジルミエ,
e,202509010,veryhard_mag_00004_ja,veryhard_mag_00004,ja,株式会社マジルミエ,
e,202509010,veryhard_mag_00005_ja,veryhard_mag_00005,ja,株式会社マジルミエ,
e,202509010,veryhard_mag_00006_ja,veryhard_mag_00006,ja,株式会社マジルミエ,
e,202509010,normal_sum_00001_ja,normal_sum_00001,ja,サマータイムレンダ,
e,202509010,normal_sum_00002_ja,normal_sum_00002,ja,サマータイムレンダ,
e,202509010,normal_sum_00003_ja,normal_sum_00003,ja,サマータイムレンダ,
e,202509010,normal_sum_00004_ja,normal_sum_00004,ja,サマータイムレンダ,
e,202509010,normal_sum_00005_ja,normal_sum_00005,ja,サマータイムレンダ,
e,202509010,normal_sum_00006_ja,normal_sum_00006,ja,サマータイムレンダ,
e,202509010,hard_sum_00001_ja,hard_sum_00001,ja,サマータイムレンダ,
e,202509010,hard_sum_00002_ja,hard_sum_00002,ja,サマータイムレンダ,
e,202509010,hard_sum_00003_ja,hard_sum_00003,ja,サマータイムレンダ,
e,202509010,hard_sum_00004_ja,hard_sum_00004,ja,サマータイムレンダ,
e,202509010,hard_sum_00005_ja,hard_sum_00005,ja,サマータイムレンダ,
e,202509010,hard_sum_00006_ja,hard_sum_00006,ja,サマータイムレンダ,
e,202509010,veryhard_sum_00001_ja,veryhard_sum_00001,ja,サマータイムレンダ,
e,202509010,veryhard_sum_00002_ja,veryhard_sum_00002,ja,サマータイムレンダ,
e,202509010,veryhard_sum_00003_ja,veryhard_sum_00003,ja,サマータイムレンダ,
e,202509010,veryhard_sum_00004_ja,veryhard_sum_00004,ja,サマータイムレンダ,
e,202509010,veryhard_sum_00005_ja,veryhard_sum_00005,ja,サマータイムレンダ,
e,202509010,veryhard_sum_00006_ja,veryhard_sum_00006,ja,サマータイムレンダ,
e,202509010,normal_kai_00001_ja,normal_kai_00001,ja,怪獣８号,
e,202509010,normal_kai_00002_ja,normal_kai_00002,ja,怪獣８号,
e,202509010,normal_kai_00003_ja,normal_kai_00003,ja,怪獣８号,
e,202509010,normal_kai_00004_ja,normal_kai_00004,ja,怪獣８号,
e,202509010,normal_kai_00005_ja,normal_kai_00005,ja,怪獣８号,
e,202509010,normal_kai_00006_ja,normal_kai_00006,ja,怪獣８号,
e,202509010,hard_kai_00001_ja,hard_kai_00001,ja,怪獣８号,
e,202509010,hard_kai_00002_ja,hard_kai_00002,ja,怪獣８号,
e,202509010,hard_kai_00003_ja,hard_kai_00003,ja,怪獣８号,
e,202509010,hard_kai_00004_ja,hard_kai_00004,ja,怪獣８号,
e,202509010,hard_kai_00005_ja,hard_kai_00005,ja,怪獣８号,
e,202509010,hard_kai_00006_ja,hard_kai_00006,ja,怪獣８号,
e,202509010,veryhard_kai_00001_ja,veryhard_kai_00001,ja,怪獣８号,
e,202509010,veryhard_kai_00002_ja,veryhard_kai_00002,ja,怪獣８号,
e,202509010,veryhard_kai_00003_ja,veryhard_kai_00003,ja,怪獣８号,
e,202509010,veryhard_kai_00004_ja,veryhard_kai_00004,ja,怪獣８号,
e,202509010,veryhard_kai_00005_ja,veryhard_kai_00005,ja,怪獣８号,
e,202509010,veryhard_kai_00006_ja,veryhard_kai_00006,ja,怪獣８号,
e,202509010,normal_glo4_00001_ja,normal_glo4_00001,ja,"リミックスクエスト vol.4",
e,202509010,normal_glo4_00002_ja,normal_glo4_00002,ja,"リミックスクエスト vol.4",
e,202509010,normal_glo4_00003_ja,normal_glo4_00003,ja,"リミックスクエスト vol.4",
e,202509010,hard_glo4_00001_ja,hard_glo4_00001,ja,"リミックスクエスト vol.4",
e,202509010,hard_glo4_00002_ja,hard_glo4_00002,ja,"リミックスクエスト vol.4",
e,202509010,hard_glo4_00003_ja,hard_glo4_00003,ja,"リミックスクエスト vol.4",
e,202509010,veryhard_glo4_00001_ja,veryhard_glo4_00001,ja,"リミックスクエスト vol.4",
e,202509010,veryhard_glo4_00002_ja,veryhard_glo4_00002,ja,"リミックスクエスト vol.4",
e,202509010,veryhard_glo4_00003_ja,veryhard_glo4_00003,ja,"リミックスクエスト vol.4",
e,202512020,normal_osh_00001_ja,normal_osh_00001,ja,【推しの子】,
e,202512020,normal_osh_00002_ja,normal_osh_00002,ja,【推しの子】,
e,202512020,normal_osh_00003_ja,normal_osh_00003,ja,【推しの子】,
e,202512020,hard_osh_00001_ja,hard_osh_00001,ja,【推しの子】,
e,202512020,hard_osh_00002_ja,hard_osh_00002,ja,【推しの子】,
e,202512020,hard_osh_00003_ja,hard_osh_00003,ja,【推しの子】,
e,202512020,veryhard_osh_00001_ja,veryhard_osh_00001,ja,【推しの子】,
e,202512020,veryhard_osh_00002_ja,veryhard_osh_00002,ja,【推しの子】,
e,202512020,veryhard_osh_00003_ja,veryhard_osh_00003,ja,【推しの子】,
e,202509010,enhance_00001_ja,enhance_00001,ja,コイン獲得クエスト,
e,202509010,event_kai1_1day_00001_ja,event_kai1_1day_00001,ja,候補生としての入隊,
e,202509010,event_kai1_charaget01_00001_ja,event_kai1_charaget01_00001,ja,"気に入らねェ 気に入らねェ",
e,202509010,event_kai1_charaget01_00002_ja,event_kai1_charaget01_00002,ja,"気に入らねェ 気に入らねェ",
e,202509010,event_kai1_charaget01_00003_ja,event_kai1_charaget01_00003,ja,"気に入らねェ 気に入らねェ",
e,202509010,event_kai1_charaget01_00004_ja,event_kai1_charaget01_00004,ja,"気に入らねェ 気に入らねェ",
e,202509010,event_kai1_charaget01_00005_ja,event_kai1_charaget01_00005,ja,"気に入らねェ 気に入らねェ",
e,202509010,event_kai1_charaget01_00006_ja,event_kai1_charaget01_00006,ja,"気に入らねェ 気に入らねェ",
e,202509010,event_kai1_charaget01_00007_ja,event_kai1_charaget01_00007,ja,"気に入らねェ 気に入らねェ",
e,202509010,event_kai1_charaget01_00008_ja,event_kai1_charaget01_00008,ja,"気に入らねェ 気に入らねェ",
e,202509010,event_kai1_charaget02_00001_ja,event_kai1_charaget02_00001,ja,怪獣８号の引き渡しを命ずる,
e,202509010,event_kai1_charaget02_00002_ja,event_kai1_charaget02_00002,ja,怪獣８号の引き渡しを命ずる,
e,202509010,event_kai1_charaget02_00003_ja,event_kai1_charaget02_00003,ja,怪獣８号の引き渡しを命ずる,
e,202509010,event_kai1_charaget02_00004_ja,event_kai1_charaget02_00004,ja,怪獣８号の引き渡しを命ずる,
e,202509010,event_kai1_charaget02_00005_ja,event_kai1_charaget02_00005,ja,怪獣８号の引き渡しを命ずる,
e,202509010,event_kai1_charaget02_00006_ja,event_kai1_charaget02_00006,ja,怪獣８号の引き渡しを命ずる,
e,202509010,event_kai1_charaget02_00007_ja,event_kai1_charaget02_00007,ja,怪獣８号の引き渡しを命ずる,
e,202509010,event_kai1_charaget02_00008_ja,event_kai1_charaget02_00008,ja,怪獣８号の引き渡しを命ずる,
e,202509010,event_kai1_challenge01_00001_ja,event_kai1_challenge01_00001,ja,"戦場で 力を示してみせろ ヒヨコども",
e,202509010,event_kai1_challenge01_00002_ja,event_kai1_challenge01_00002,ja,"戦場で 力を示してみせろ ヒヨコども",
e,202509010,event_kai1_challenge01_00003_ja,event_kai1_challenge01_00003,ja,"戦場で 力を示してみせろ ヒヨコども",
e,202509010,event_kai1_challenge01_00004_ja,event_kai1_challenge01_00004,ja,"戦場で 力を示してみせろ ヒヨコども",
e,202509010,event_kai1_savage_00001_ja,event_kai1_savage_00001,ja,クラス『大怪獣』,
e,202509010,event_kai1_savage_00002_ja,event_kai1_savage_00002,ja,クラス『大怪獣』,
e,202510010,event_spy1_1day_00001_ja,event_spy1_1day_00001,ja,デイリークエスト,
e,202510010,event_spy1_charaget01_00001_ja,event_spy1_charaget01_00001,ja,ストーリークエスト1,
e,202510010,event_spy1_charaget01_00002_ja,event_spy1_charaget01_00002,ja,ストーリークエスト1,
e,202510010,event_spy1_charaget01_00003_ja,event_spy1_charaget01_00003,ja,ストーリークエスト1,
e,202510010,event_spy1_charaget01_00004_ja,event_spy1_charaget01_00004,ja,ストーリークエスト1,
e,202510010,event_spy1_charaget01_00005_ja,event_spy1_charaget01_00005,ja,ストーリークエスト1,
e,202510010,event_spy1_charaget01_00006_ja,event_spy1_charaget01_00006,ja,ストーリークエスト1,
e,202510010,event_spy1_charaget01_00007_ja,event_spy1_charaget01_00007,ja,ストーリークエスト1,
e,202510010,event_spy1_charaget01_00008_ja,event_spy1_charaget01_00008,ja,ストーリークエスト1,
e,202510010,event_spy1_charaget02_00001_ja,event_spy1_charaget02_00001,ja,ストーリークエスト2,
e,202510010,event_spy1_charaget02_00002_ja,event_spy1_charaget02_00002,ja,ストーリークエスト2,
e,202510010,event_spy1_charaget02_00003_ja,event_spy1_charaget02_00003,ja,ストーリークエスト2,
e,202510010,event_spy1_charaget02_00004_ja,event_spy1_charaget02_00004,ja,ストーリークエスト2,
e,202510010,event_spy1_charaget02_00005_ja,event_spy1_charaget02_00005,ja,ストーリークエスト2,
e,202510010,event_spy1_charaget02_00006_ja,event_spy1_charaget02_00006,ja,ストーリークエスト2,
e,202510010,event_spy1_charaget02_00007_ja,event_spy1_charaget02_00007,ja,ストーリークエスト2,
e,202510010,event_spy1_charaget02_00008_ja,event_spy1_charaget02_00008,ja,ストーリークエスト2,
e,202510010,event_spy1_challenge01_00001_ja,event_spy1_challenge01_00001,ja,チャレンジクエスト,
e,202510010,event_spy1_challenge01_00002_ja,event_spy1_challenge01_00002,ja,チャレンジクエスト,
e,202510010,event_spy1_challenge01_00003_ja,event_spy1_challenge01_00003,ja,チャレンジクエスト,
e,202510010,event_spy1_challenge01_00004_ja,event_spy1_challenge01_00004,ja,チャレンジクエスト,
e,202510010,event_spy1_savage_00001_ja,event_spy1_savage_00001,ja,高難易度クエスト,
e,202510010,event_spy1_savage_00002_ja,event_spy1_savage_00002,ja,高難易度クエスト,
e,202510020,event_dan1_1day_00001_ja,event_dan1_1day_00001,ja,デイリークエスト,
e,202510020,event_dan1_charaget01_00001_ja,event_dan1_charaget01_00001,ja,ストーリークエスト1,
e,202510020,event_dan1_charaget01_00002_ja,event_dan1_charaget01_00002,ja,ストーリークエスト1,
e,202510020,event_dan1_charaget01_00003_ja,event_dan1_charaget01_00003,ja,ストーリークエスト1,
e,202510020,event_dan1_charaget01_00004_ja,event_dan1_charaget01_00004,ja,ストーリークエスト1,
e,202510020,event_dan1_charaget01_00005_ja,event_dan1_charaget01_00005,ja,ストーリークエスト1,
e,202510020,event_dan1_charaget01_00006_ja,event_dan1_charaget01_00006,ja,ストーリークエスト1,
e,202510020,event_dan1_charaget01_00007_ja,event_dan1_charaget01_00007,ja,ストーリークエスト1,
e,202510020,event_dan1_charaget01_00008_ja,event_dan1_charaget01_00008,ja,ストーリークエスト1,
e,202510020,event_dan1_charaget02_00001_ja,event_dan1_charaget02_00001,ja,ストーリークエスト2,
e,202510020,event_dan1_charaget02_00002_ja,event_dan1_charaget02_00002,ja,ストーリークエスト2,
e,202510020,event_dan1_charaget02_00003_ja,event_dan1_charaget02_00003,ja,ストーリークエスト2,
e,202510020,event_dan1_charaget02_00004_ja,event_dan1_charaget02_00004,ja,ストーリークエスト2,
e,202510020,event_dan1_charaget02_00005_ja,event_dan1_charaget02_00005,ja,ストーリークエスト2,
e,202510020,event_dan1_charaget02_00006_ja,event_dan1_charaget02_00006,ja,ストーリークエスト2,
e,202510020,event_dan1_charaget02_00007_ja,event_dan1_charaget02_00007,ja,ストーリークエスト2,
e,202510020,event_dan1_charaget02_00008_ja,event_dan1_charaget02_00008,ja,ストーリークエスト2,
e,202510020,event_dan1_challenge01_00001_ja,event_dan1_challenge01_00001,ja,チャレンジクエスト,
e,202510020,event_dan1_challenge01_00002_ja,event_dan1_challenge01_00002,ja,チャレンジクエスト,
e,202510020,event_dan1_challenge01_00003_ja,event_dan1_challenge01_00003,ja,チャレンジクエスト,
e,202510020,event_dan1_challenge01_00004_ja,event_dan1_challenge01_00004,ja,チャレンジクエスト,
e,202510020,event_dan1_savage_00001_ja,event_dan1_savage_00001,ja,高難易度クエスト,
e,202510020,event_dan1_savage_00002_ja,event_dan1_savage_00002,ja,高難易度クエスト,
e,202511010,event_mag1_1day_00001_ja,event_mag1_1day_00001,ja,何とかやってます,
e,202511010,event_mag1_charaget01_00001_ja,event_mag1_charaget01_00001,ja,うちの美学,
e,202511010,event_mag1_charaget01_00002_ja,event_mag1_charaget01_00002,ja,うちの美学,
e,202511010,event_mag1_charaget01_00003_ja,event_mag1_charaget01_00003,ja,うちの美学,
e,202511010,event_mag1_charaget01_00004_ja,event_mag1_charaget01_00004,ja,うちの美学,
e,202511010,event_mag1_charaget01_00005_ja,event_mag1_charaget01_00005,ja,うちの美学,
e,202511010,event_mag1_charaget01_00006_ja,event_mag1_charaget01_00006,ja,うちの美学,
e,202511010,event_mag1_charaget01_00007_ja,event_mag1_charaget01_00007,ja,うちの美学,
e,202511010,event_mag1_charaget01_00008_ja,event_mag1_charaget01_00008,ja,うちの美学,
e,202511010,event_mag1_charaget02_00001_ja,event_mag1_charaget02_00001,ja,よく見てる,
e,202511010,event_mag1_charaget02_00002_ja,event_mag1_charaget02_00002,ja,よく見てる,
e,202511010,event_mag1_charaget02_00003_ja,event_mag1_charaget02_00003,ja,よく見てる,
e,202511010,event_mag1_charaget02_00004_ja,event_mag1_charaget02_00004,ja,よく見てる,
e,202511010,event_mag1_charaget02_00005_ja,event_mag1_charaget02_00005,ja,よく見てる,
e,202511010,event_mag1_charaget02_00006_ja,event_mag1_charaget02_00006,ja,よく見てる,
e,202511010,event_mag1_charaget02_00007_ja,event_mag1_charaget02_00007,ja,よく見てる,
e,202511010,event_mag1_charaget02_00008_ja,event_mag1_charaget02_00008,ja,よく見てる,
e,202511010,event_mag1_challenge01_00001_ja,event_mag1_challenge01_00001,ja,色々な魔法少女,
e,202511010,event_mag1_challenge01_00002_ja,event_mag1_challenge01_00002,ja,色々な魔法少女,
e,202511010,event_mag1_challenge01_00003_ja,event_mag1_challenge01_00003,ja,色々な魔法少女,
e,202511010,event_mag1_challenge01_00004_ja,event_mag1_challenge01_00004,ja,色々な魔法少女,
e,202511010,event_mag1_savage_00001_ja,event_mag1_savage_00001,ja,「怪異」現象,
e,202511010,event_mag1_savage_00002_ja,event_mag1_savage_00002,ja,「怪異」現象,
e,202511010,event_mag1_savage_00003_ja,event_mag1_savage_00003,ja,「怪異」現象,
e,202511020,event_yuw1_1day_00001_ja,event_yuw1_1day_00001,ja,はじめての撮影会,
e,202511020,event_yuw1_charaget01_00001_ja,event_yuw1_charaget01_00001,ja,コスプレをしに来たんだよ,
e,202511020,event_yuw1_charaget01_00002_ja,event_yuw1_charaget01_00002,ja,コスプレをしに来たんだよ,
e,202511020,event_yuw1_charaget01_00003_ja,event_yuw1_charaget01_00003,ja,コスプレをしに来たんだよ,
e,202511020,event_yuw1_charaget01_00004_ja,event_yuw1_charaget01_00004,ja,コスプレをしに来たんだよ,
e,202511020,event_yuw1_charaget01_00005_ja,event_yuw1_charaget01_00005,ja,コスプレをしに来たんだよ,
e,202511020,event_yuw1_charaget01_00006_ja,event_yuw1_charaget01_00006,ja,コスプレをしに来たんだよ,
e,202511020,event_yuw1_charaget01_00007_ja,event_yuw1_charaget01_00007,ja,コスプレをしに来たんだよ,
e,202511020,event_yuw1_charaget01_00008_ja,event_yuw1_charaget01_00008,ja,コスプレをしに来たんだよ,
e,202511020,event_yuw1_charaget02_00001_ja,event_yuw1_charaget02_00001,ja,俺はずっとオタクなだけです,
e,202511020,event_yuw1_charaget02_00002_ja,event_yuw1_charaget02_00002,ja,俺はずっとオタクなだけです,
e,202511020,event_yuw1_charaget02_00003_ja,event_yuw1_charaget02_00003,ja,俺はずっとオタクなだけです,
e,202511020,event_yuw1_charaget02_00004_ja,event_yuw1_charaget02_00004,ja,俺はずっとオタクなだけです,
e,202511020,event_yuw1_charaget02_00005_ja,event_yuw1_charaget02_00005,ja,俺はずっとオタクなだけです,
e,202511020,event_yuw1_charaget02_00006_ja,event_yuw1_charaget02_00006,ja,俺はずっとオタクなだけです,
e,202511020,event_yuw1_charaget02_00007_ja,event_yuw1_charaget02_00007,ja,俺はずっとオタクなだけです,
e,202511020,event_yuw1_charaget02_00008_ja,event_yuw1_charaget02_00008,ja,俺はずっとオタクなだけです,
e,202511020,event_yuw1_challenge01_00001_ja,event_yuw1_challenge01_00001,ja,幸せです…,
e,202511020,event_yuw1_challenge01_00002_ja,event_yuw1_challenge01_00002,ja,幸せです…,
e,202511020,event_yuw1_challenge01_00003_ja,event_yuw1_challenge01_00003,ja,幸せです…,
e,202511020,event_yuw1_challenge01_00004_ja,event_yuw1_challenge01_00004,ja,幸せです…,
e,202511020,event_yuw1_savage_00001_ja,event_yuw1_savage_00001,ja,これがこの世界の頂上,
e,202511020,event_yuw1_savage_00002_ja,event_yuw1_savage_00002,ja,これがこの世界の頂上,
e,202511020,event_yuw1_savage_00003_ja,event_yuw1_savage_00003,ja,これがこの世界の頂上,
e,202512015,event_yuw1_savage02_00001_ja,event_yuw1_savage02_00001,ja,クリスマスバトル!!,
e,202512015,event_yuw1_savage02_00002_ja,event_yuw1_savage02_00002,ja,クリスマスバトル!!,
e,202512010,event_sur1_1day_00001_ja,event_sur1_1day_00001,ja,"精兵と管理人	",
e,202512010,event_sur1_charaget01_00001_ja,event_sur1_charaget01_00001,ja,スレイブの誕生,
e,202512010,event_sur1_charaget01_00002_ja,event_sur1_charaget01_00002,ja,スレイブの誕生,
e,202512010,event_sur1_charaget01_00003_ja,event_sur1_charaget01_00003,ja,スレイブの誕生,
e,202512010,event_sur1_charaget01_00004_ja,event_sur1_charaget01_00004,ja,スレイブの誕生,
e,202512010,event_sur1_charaget01_00005_ja,event_sur1_charaget01_00005,ja,スレイブの誕生,
e,202512010,event_sur1_charaget01_00006_ja,event_sur1_charaget01_00006,ja,スレイブの誕生,
e,202512010,event_sur1_charaget01_00007_ja,event_sur1_charaget01_00007,ja,スレイブの誕生,
e,202512010,event_sur1_charaget01_00008_ja,event_sur1_charaget01_00008,ja,スレイブの誕生,
e,202512010,event_sur1_challenge01_00001_ja,event_sur1_challenge01_00001,ja,魔都防衛隊,
e,202512010,event_sur1_challenge01_00002_ja,event_sur1_challenge01_00002,ja,魔都防衛隊,
e,202512010,event_sur1_challenge01_00003_ja,event_sur1_challenge01_00003,ja,魔都防衛隊,
e,202512010,event_sur1_challenge01_00004_ja,event_sur1_challenge01_00004,ja,魔都防衛隊,
e,202512010,event_sur1_savage_00001_ja,event_sur1_savage_00001,ja,スレイブと組長,
e,202512010,event_sur1_savage_00002_ja,event_sur1_savage_00002,ja,スレイブと組長,
e,202512010,event_sur1_savage_00003_ja,event_sur1_savage_00003,ja,スレイブと組長,
e,202512010,event_sur1_charaget02_00001_ja,event_sur1_charaget02_00001,ja,隠れ里の戦い,
e,202512010,event_sur1_charaget02_00002_ja,event_sur1_charaget02_00002,ja,隠れ里の戦い,
e,202512010,event_sur1_charaget02_00003_ja,event_sur1_charaget02_00003,ja,隠れ里の戦い,
e,202512010,event_sur1_charaget02_00004_ja,event_sur1_charaget02_00004,ja,隠れ里の戦い,
e,202512010,event_sur1_charaget02_00005_ja,event_sur1_charaget02_00005,ja,隠れ里の戦い,
e,202512010,event_sur1_charaget02_00006_ja,event_sur1_charaget02_00006,ja,隠れ里の戦い,
e,202512010,event_sur1_charaget02_00007_ja,event_sur1_charaget02_00007,ja,隠れ里の戦い,
e,202512010,event_sur1_charaget02_00008_ja,event_sur1_charaget02_00008,ja,隠れ里の戦い,
e,202512020,event_osh1_1day_00001_ja,event_osh1_1day_00001,ja,ファンと推し合戦!,
e,202512020,event_osh1_charaget02_00001_ja,event_osh1_charaget02_00001,ja,ぴえヨンのブートクエスト,
e,202512020,event_osh1_charaget02_00002_ja,event_osh1_charaget02_00002,ja,ぴえヨンのブートクエスト,
e,202512020,event_osh1_charaget02_00003_ja,event_osh1_charaget02_00003,ja,ぴえヨンのブートクエスト,
e,202512020,event_osh1_charaget01_00001_ja,event_osh1_charaget01_00001,ja,芸能界へ!,
e,202512020,event_osh1_charaget01_00002_ja,event_osh1_charaget01_00002,ja,芸能界へ!,
e,202512020,event_osh1_charaget01_00003_ja,event_osh1_charaget01_00003,ja,芸能界へ!,
e,202512020,event_osh1_challenge01_00001_ja,event_osh1_challenge01_00001,ja,推しの子になってやる,
e,202512020,event_osh1_challenge01_00002_ja,event_osh1_challenge01_00002,ja,推しの子になってやる,
e,202512020,event_osh1_challenge01_00003_ja,event_osh1_challenge01_00003,ja,推しの子になってやる,
e,202512020,event_osh1_challenge01_00004_ja,event_osh1_challenge01_00004,ja,推しの子になってやる,
e,202512020,event_glo1_1day_00001_ja,event_glo1_1day_00001,ja,開運!ジャンブル運試し,
e,202512020,event_osh1_savage_00001_ja,event_osh1_savage_00001,ja,芸能界には才能が集まる,
e,202512020,event_osh1_savage_00002_ja,event_osh1_savage_00002,ja,芸能界には才能が集まる,
e,202512020,event_osh1_savage_00003_ja,event_osh1_savage_00003,ja,芸能界には才能が集まる,
e,202601010,event_jig1_1day_00001_ja,event_jig1_1day_00001,ja,"本能が告げている 危険だと",
e,202601010,event_jig1_charaget02_00001_ja,event_jig1_charaget02_00001,ja,朱印の者たち,
e,202601010,event_jig1_charaget02_00002_ja,event_jig1_charaget02_00002,ja,朱印の者たち,
e,202601010,event_jig1_charaget02_00003_ja,event_jig1_charaget02_00003,ja,朱印の者たち,
e,202601010,event_jig1_charaget02_00004_ja,event_jig1_charaget02_00004,ja,朱印の者たち,
e,202601010,event_jig1_charaget02_00005_ja,event_jig1_charaget02_00005,ja,朱印の者たち,
e,202601010,event_jig1_charaget02_00006_ja,event_jig1_charaget02_00006,ja,朱印の者たち,
e,202601010,event_jig1_charaget01_00001_ja,event_jig1_charaget01_00001,ja,必ず生きて帰る,
e,202601010,event_jig1_charaget01_00002_ja,event_jig1_charaget01_00002,ja,必ず生きて帰る,
e,202601010,event_jig1_charaget01_00003_ja,event_jig1_charaget01_00003,ja,必ず生きて帰る,
e,202601010,event_jig1_charaget01_00004_ja,event_jig1_charaget01_00004,ja,必ず生きて帰る,
e,202601010,event_jig1_charaget01_00005_ja,event_jig1_charaget01_00005,ja,必ず生きて帰る,
e,202601010,event_jig1_charaget01_00006_ja,event_jig1_charaget01_00006,ja,必ず生きて帰る,
e,202601010,event_jig1_challenge01_00001_ja,event_jig1_challenge01_00001,ja,死罪人と首切り役人,
e,202601010,event_jig1_challenge01_00002_ja,event_jig1_challenge01_00002,ja,死罪人と首切り役人,
e,202601010,event_jig1_challenge01_00003_ja,event_jig1_challenge01_00003,ja,死罪人と首切り役人,
e,202601010,event_jig1_challenge01_00004_ja,event_jig1_challenge01_00004,ja,死罪人と首切り役人,
e,202601010,event_jig1_savage_00001_ja,event_jig1_savage_00001,ja,手負いの獣は恐ろしいぞ,
e,202601010,event_jig1_savage_00002_ja,event_jig1_savage_00002,ja,手負いの獣は恐ろしいぞ,
e,202601010,event_jig1_savage_00003_ja,event_jig1_savage_00003,ja,手負いの獣は恐ろしいぞ,
e,202602010,event_you1_1day_00001_ja,event_you1_1day_00001,ja,お遊戯の時間です,
e,202602010,event_you1_charaget01_00001_ja,event_you1_charaget01_00001,ja,先輩は敬いたまえ,
e,202602010,event_you1_charaget01_00002_ja,event_you1_charaget01_00002,ja,先輩は敬いたまえ,
e,202602010,event_you1_charaget01_00003_ja,event_you1_charaget01_00003,ja,先輩は敬いたまえ,
e,202602010,event_you1_charaget01_00004_ja,event_you1_charaget01_00004,ja,先輩は敬いたまえ,
e,202602010,event_you1_charaget01_00005_ja,event_you1_charaget01_00005,ja,先輩は敬いたまえ,
e,202602010,event_you1_charaget01_00006_ja,event_you1_charaget01_00006,ja,先輩は敬いたまえ,
e,202602010,event_you1_charaget02_00001_ja,event_you1_charaget02_00001,ja,兄を助けてくれないか?,
e,202602010,event_you1_charaget02_00002_ja,event_you1_charaget02_00002,ja,兄を助けてくれないか?,
e,202602010,event_you1_charaget02_00003_ja,event_you1_charaget02_00003,ja,兄を助けてくれないか?,
e,202602010,event_you1_charaget02_00004_ja,event_you1_charaget02_00004,ja,兄を助けてくれないか?,
e,202602010,event_you1_charaget02_00005_ja,event_you1_charaget02_00005,ja,兄を助けてくれないか?,
e,202602010,event_you1_charaget02_00006_ja,event_you1_charaget02_00006,ja,兄を助けてくれないか?,
e,202602010,event_you1_challenge_00001_ja,event_you1_challenge_00001,ja,世界一安全な幼稚園,
e,202602010,event_you1_challenge_00002_ja,event_you1_challenge_00002,ja,世界一安全な幼稚園,
e,202602010,event_you1_challenge_00003_ja,event_you1_challenge_00003,ja,世界一安全な幼稚園,
e,202602010,event_you1_challenge_00004_ja,event_you1_challenge_00004,ja,世界一安全な幼稚園,
e,202602010,event_you1_savage_00001_ja,event_you1_savage_00001,ja,正義だけじゃ何も守れない,
e,202602010,event_you1_savage_00002_ja,event_you1_savage_00002,ja,正義だけじゃ何も守れない,
e,202602010,event_you1_savage_00003_ja,event_you1_savage_00003,ja,正義だけじゃ何も守れない,
e,202602020,event_kim1_savage_00001_ja,event_kim1_savage_00001,ja,"DEAD OR LOVE",
e,202602020,event_kim1_savage_00002_ja,event_kim1_savage_00002,ja,"DEAD OR LOVE",
e,202602020,event_kim1_savage_00003_ja,event_kim1_savage_00003,ja,"DEAD OR LOVE",
e,202602020,event_kim1_1day_00001_ja,event_kim1_1day_00001,ja,恋は盲目,
e,202602020,event_kim1_charaget01_00001_ja,event_kim1_charaget01_00001,ja,キスゾンビ♡パニック,
e,202602020,event_kim1_charaget01_00002_ja,event_kim1_charaget01_00002,ja,キスゾンビ♡パニック,
e,202602020,event_kim1_charaget01_00003_ja,event_kim1_charaget01_00003,ja,キスゾンビ♡パニック,
e,202602020,event_kim1_charaget01_00004_ja,event_kim1_charaget01_00004,ja,キスゾンビ♡パニック,
e,202602020,event_kim1_charaget02_00001_ja,event_kim1_charaget02_00001,ja,最高の恋愛パートナー,
e,202602020,event_kim1_charaget02_00002_ja,event_kim1_charaget02_00002,ja,最高の恋愛パートナー,
e,202602020,event_kim1_charaget02_00003_ja,event_kim1_charaget02_00003,ja,最高の恋愛パートナー,
e,202602020,event_kim1_charaget02_00004_ja,event_kim1_charaget02_00004,ja,最高の恋愛パートナー,
e,202602020,event_kim1_charaget02_00005_ja,event_kim1_charaget02_00005,ja,最高の恋愛パートナー,
e,202602020,event_kim1_charaget02_00006_ja,event_kim1_charaget02_00006,ja,最高の恋愛パートナー,
e,202602020,event_kim1_challenge_00001_ja,event_kim1_challenge_00001,ja,恋太郎ファミリー,
e,202602020,event_kim1_challenge_00002_ja,event_kim1_challenge_00002,ja,恋太郎ファミリー,
e,202602020,event_kim1_challenge_00003_ja,event_kim1_challenge_00003,ja,恋太郎ファミリー,
e,202602020,event_kim1_challenge_00004_ja,event_kim1_challenge_00004,ja,恋太郎ファミリー,
```

---

<!-- FILE: ./projects/glow-masterdata/MstUnit.csv -->
## ./projects/glow-masterdata/MstUnit.csv

```csv
ENABLE,id,fragment_mst_item_id,role_type,color,attack_range_type,unit_label,has_specific_rank_up,mst_series_id,asset_key,rarity,sort_order,summon_cost,summon_cool_time,special_attack_initial_cool_time,special_attack_cool_time,min_hp,max_hp,damage_knock_back_count,move_speed,well_distance,min_attack_power,max_attack_power,mst_unit_ability_id1,ability_unlock_rank1,mst_unit_ability_id2,ability_unlock_rank2,mst_unit_ability_id3,ability_unlock_rank3,is_encyclopedia_special_attack_position_right,release_key
e,chara_dan_00001,piece_dan_00001,Defense,Red,Short,PremiumR,0,dan,chara_dan_00001,R,1,160,335,175,995,910,9100,1,30,0.16,320,3200,,0,,0,,0,0,202509010
e,chara_dan_00002,piece_dan_00002,Attack,Blue,Middle,PremiumUR,0,dan,chara_dan_00002,UR,1,985,1010,530,1045,2760,27600,2,75,0.34,4800,48000,ability_dan_00002_01,0,ability_dan_00002_02,4,,0,0,202509010
e,chara_dan_00101,piece_dan_00101,Attack,Red,Middle,PremiumSSR,0,dan,chara_dan_00101,SSR,1,330,255,120,520,690,6900,2,35,0.39,1040,10400,ability_dan_00101_01,0,,0,,0,0,202509010
e,chara_gom_00001,piece_gom_00001,Defense,Green,Short,PremiumUR,0,gom,chara_gom_00001,UR,1,650,1750,750,1505,5450,54500,1,20,0.16,640,6400,ability_gom_00001_01,0,ability_gom_00001_02,4,,0,0,202509010
e,chara_gom_00101,piece_gom_00101,Technical,Yellow,Short,PremiumSR,0,gom,chara_gom_00101,SR,1,325,255,130,860,690,6900,3,35,0.31,720,7200,ability_gom_00101_01,2,,0,,0,0,202509010
e,chara_gom_00201,piece_gom_00201,Attack,Yellow,Long,DropR,0,gom,chara_gom_00201,R,1,555,365,190,955,1000,10000,2,30,0.52,3720,37200,,0,,0,,0,0,202509010
e,chara_sur_00001,piece_sur_00001,Support,Green,Short,PremiumR,0,sur,chara_sur_00001,R,1,360,240,125,695,650,6500,1,35,0.27,750,7500,,0,,0,,0,0,202509010
e,chara_jig_00001,piece_jig_00001,Technical,Red,Short,PremiumUR,0,jig,chara_jig_00001,UR,1,985,1010,530,1510,2760,27600,3,50,0.26,2710,27100,ability_jig_00001_01,0,ability_jig_00001_02,4,,0,0,202509010
e,chara_jig_00101,piece_jig_00101,Attack,Red,Short,PremiumSSR,0,jig,chara_jig_00101,SSR,1,620,545,260,615,1490,14900,2,40,0.29,1740,17400,ability_jig_00101_01,0,,0,,0,0,202509010
e,chara_jig_00201,piece_jig_00201,Technical,Green,Middle,DropSSR,0,jig,chara_jig_00201,SSR,1,685,455,240,1235,1240,12400,3,40,0.41,2270,22700,ability_jig_00201_01,0,,0,,0,0,202509010
e,chara_jig_00301,piece_jig_00301,Defense,Green,Short,PremiumSR,0,jig,chara_jig_00301,SR,1,320,705,370,940,1920,19200,1,30,0.21,610,6100,ability_jig_00301_01,2,,0,,0,0,202509010
e,chara_kai_00101,piece_kai_00101,Technical,Blue,Long,PremiumSR,0,kai,chara_kai_00101,SR,1,845,375,195,1010,1020,10200,3,30,0.61,4840,48400,ability_kai_00101_01,2,,0,,0,0,202509010
e,chara_kai_00301,piece_kai_00301,Defense,Yellow,Short,PremiumSSR,0,kai,chara_kai_00301,SSR,1,535,1005,525,920,2740,27400,1,40,0.21,1430,14300,ability_kai_00301_01,0,,0,,0,0,202509010
e,chara_kai_00001,piece_kai_00001,Attack,Yellow,Short,PremiumR,0,kai,chara_kai_00001,R,1,210,185,100,650,500,5000,2,40,0.24,550,5500,,0,,0,,0,0,202509010
e,chara_kai_00002,piece_kai_00002,Attack,Green,Short,PremiumUR,0,kai,chara_kai_00002,UR,2,1000,1025,535,880,2800,28000,2,50,0.24,3850,38500,ability_kai_00002_01,0,ability_kai_00002_02,4,,0,0,202509010
e,chara_dos_00001,piece_dos_00001,Support,Blue,Long,PremiumSSR,0,dos,chara_dos_00001,SSR,1,375,125,135,1120,340,3400,3,30,0.62,1480,14800,ability_dos_00001_01,0,,0,,0,0,202509010
e,chara_chi_00001,piece_chi_00001,Attack,Green,Short,PremiumR,0,chi,chara_chi_00001,R,1,260,225,120,395,620,6200,2,35,0.29,680,6800,,0,,0,,0,0,202509010
e,chara_sum_00001,piece_sum_00001,Technical,Blue,Long,PremiumR,0,sum,chara_sum_00001,R,1,515,225,120,1190,620,6200,3,30,0.61,2710,27100,,0,,0,,0,0,202509010
e,chara_sum_00201,piece_sum_00201,Attack,Blue,Short,PremiumSR,0,sum,chara_sum_00201,SR,1,485,430,225,545,1170,11700,2,45,0.24,1260,12600,ability_sum_00201_01,2,,0,,0,0,202509010
e,chara_sur_00101,piece_sur_00101,Attack,Green,Middle,PremiumUR,0,sur,chara_sur_00101,UR,1,530,405,215,660,1110,11100,2,40,0.34,2070,20700,ability_sur_00101_01,0,ability_sur_00101_02,4,,0,0,202509010
e,chara_sum_00101,piece_sum_00101,Support,Blue,Middle,PremiumUR,0,sum,chara_sum_00101,UR,1,620,310,165,1610,930,9300,2,60,0.42,1570,15700,ability_sum_00101_01,0,ability_sum_00101_02,4,,0,0,202509010
e,chara_sur_00201,piece_sur_00201,Attack,Blue,Short,PremiumSR,0,sur,chara_sur_00201,SR,1,315,280,145,440,760,7600,2,40,0.24,820,8200,ability_sur_00201_01,2,,0,,0,0,202509010
e,chara_sur_00301,piece_sur_00301,Defense,Yellow,Short,PremiumSR,0,sur,chara_sur_00301,SR,1,480,1005,525,830,2740,27400,2,40,0.16,960,9600,ability_sur_00301_01,2,,0,,0,0,202509010
e,chara_tak_00001,piece_tak_00001,Defense,Yellow,Short,PremiumUR,0,tak,chara_tak_00001,UR,1,390,775,405,1405,2110,21100,1,25,0.16,320,3200,ability_tak_00001_01,0,ability_tak_00001_02,4,,0,0,202509010
e,chara_chi_00201,piece_chi_00201,Attack,Green,Short,PremiumSSR,0,chi,chara_chi_00201,SSR,1,870,765,400,725,2090,20900,2,35,0.24,2260,22600,ability_chi_00201_01,0,,0,,0,0,202509010
e,chara_chi_00301,piece_chi_00301,Attack,Blue,Middle,PremiumSSR,0,chi,chara_chi_00301,SSR,1,420,325,170,590,880,8800,2,45,0.34,1640,16400,ability_chi_00301_01,0,,0,,0,0,202509010
e,chara_chi_00002,piece_chi_00002,Technical,Yellow,Short,PremiumUR,0,chi,chara_chi_00002,UR,1,850,560,295,545,1530,15300,3,40,0.31,1840,18400,ability_chi_00002_01,0,ability_chi_00002_02,4,,0,0,202509010
e,chara_rik_00001,piece_rik_00001,Attack,Red,Short,PremiumR,0,rik,chara_rik_00001,R,1,705,625,300,655,1700,17000,2,45,0.24,1970,19700,,0,,0,,0,0,202509010
e,chara_aka_00101,piece_aka_00101,Technical,Red,Short,PremiumR,0,aka,chara_aka_00101,R,1,325,255,130,640,690,6900,3,30,0.26,720,7200,,0,,0,,0,0,202509010
e,chara_aka_00001,piece_aka_00001,Defense,Red,Short,PremiumR,0,aka,chara_aka_00001,R,1,560,1170,610,1345,3190,31900,1,30,0.16,1120,11200,,0,,0,,0,0,202509010
e,chara_spy_00101,piece_spy_00101,Attack,Yellow,Long,PremiumUR,0,spy,chara_spy_00101,UR,1,985,720,380,1410,1970,19700,3,40,0.59,9600,96000,ability_spy_00101_01,0,ability_spy_00101_02,4,,0,0,202509010
e,chara_spy_00201,piece_spy_00201,Attack,Red,Middle,PremiumUR,0,spy,chara_spy_00201,UR,1,650,570,275,1470,1560,15600,2,45,0.34,3170,31700,ability_spy_00201_01,0,ability_spy_00201_02,4,,0,0,202509010
e,chara_spy_00401,piece_spy_00401,Defense,Green,Short,DropSR,1,spy,chara_spy_00401,SR,1,470,1035,540,1180,2820,28200,1,30,0.21,940,9400,ability_spy_00401_01,2,,0,,0,0,202510010
e,chara_spy_00501,piece_spy_00501,Defense,Blue,Short,PremiumUR,0,spy,chara_spy_00501,UR,1,540,1305,680,1395,3560,35600,1,35,0.24,420,4200,ability_spy_00501_01,0,ability_spy_00501_02,4,,0,0,202510010
e,chara_rik_00101,piece_rik_00101,Support,Red,Middle,DropR,0,rik,chara_rik_00101,R,1,610,335,175,1000,920,9200,2,35,0.42,1530,15300,,0,,0,,0,0,202509010
e,chara_bat_00001,piece_bat_00001,Attack,Red,Long,PremiumSR,0,bat,chara_bat_00001,SR,1,220,125,100,575,330,3300,3,45,0.59,1860,18600,ability_bat_00001_01,2,,0,,0,0,202509010
e,chara_bat_00101,piece_bat_00101,Defense,Red,Middle,PremiumSR,0,bat,chara_bat_00101,SR,1,625,1240,650,1450,3380,33800,1,45,0.34,1260,12600,ability_bat_00101_01,2,,0,,0,0,202509010
e,chara_mag_00001,piece_mag_00001,Attack,Blue,Long,PremiumUR,0,mag,chara_mag_00001,UR,1,980,720,375,1405,1960,19600,3,45,0.59,9570,95700,ability_mag_00001_01,0,ability_mag_00001_02,4,,0,0,202509010
e,chara_mag_00101,piece_mag_00101,Attack,Yellow,Middle,PremiumSR,0,mag,chara_mag_00101,SR,1,725,560,295,775,1530,15300,2,50,0.39,2840,28400,ability_mag_00101_01,2,,0,,0,0,202509010
e,chara_yuw_00001,piece_yuw_00001,Attack,Yellow,Middle,PremiumUR,0,yuw,chara_yuw_00001,UR,1,785,475,250,715,1300,13000,2,35,0.39,3420,34200,ability_yuw_00001_01,0,ability_yuw_00001_02,4,,0,0,202509010
e,chara_yuw_00101,piece_yuw_00101,Technical,Green,Short,PremiumUR,0,yuw,chara_yuw_00101,UR,1,740,570,295,1750,1550,15500,3,35,0.26,1630,16300,ability_yuw_00101_01,0,ability_yuw_00101_02,4,,0,0,202509010
e,chara_aha_00001,piece_aha_00001,Defense,Yellow,Short,PremiumSR,0,aha,chara_aha_00001,SR,1,280,585,305,955,1600,16000,1,30,0.21,560,5600,ability_aha_00001_01,2,,0,,0,0,202509010
e,chara_aha_00101,piece_aha_00101,Support,Yellow,Short,PremiumSSR,0,aha,chara_aha_00101,SSR,1,620,410,215,1130,1120,11200,2,35,0.27,1310,13100,ability_aha_00101_01,0,,0,,0,0,202509010
e,chara_ron_00001,piece_ron_00001,Technical,Blue,Middle,PremiumSSR,0,ron,chara_ron_00001,SSR,1,290,190,100,565,520,5200,3,35,0.36,960,9600,ability_ron_00001_01,0,,0,,0,0,202509010
e,chara_ron_00101,piece_ron_00101,Defense,Blue,Middle,PremiumR,0,ron,chara_ron_00101,R,1,210,415,215,800,1130,11300,1,30,0.29,420,4200,,0,,0,,0,0,202509010
e,chara_kai_00201,piece_kai_00201,Attack,Blue,Long,PremiumUR,0,kai,chara_kai_00201,UR,1,975,715,375,1400,1950,19500,3,40,0.59,9510,95100,ability_kai_00201_01,0,ability_kai_00201_02,4,,0,0,202509010
e,chara_kai_00401,piece_kai_00401,Attack,Blue,Short,PremiumSSR,0,kai,chara_kai_00401,SSR,1,700,695,360,690,1890,18900,2,50,0.24,1340,13400,ability_kai_00401_01,0,,0,,0,0,202509010
e,chara_kai_00501,piece_kai_00501,Attack,Yellow,Short,DropSR,1,kai,chara_kai_00501,SR,1,875,675,355,680,1840,18400,2,25,0.29,1840,18400,ability_kai_00501_01,2,,0,,0,0,202509010
e,chara_kai_00601,piece_kai_00601,Defense,Green,Middle,DropSR,1,kai,chara_kai_00601,SR,1,225,475,245,790,1290,12900,1,45,0.34,460,4600,ability_kai_00601_01,2,,0,,0,0,202509010
e,chara_sur_00401,piece_sur_00401,Special,Colorless,None,DropSR,0,sur,chara_sur_00401,SR,1,350,0,0,0,0,0,,0,,20,20,,0,,0,,0,0,202509010
e,chara_spy_00001,piece_spy_00001,Special,Colorless,None,PremiumUR,0,spy,chara_spy_00001,UR,1,500,0,0,0,0,0,,0,,50,50,,0,,0,,0,0,202509010
e,chara_spy_00301,piece_spy_00301,Special,Colorless,None,DropSR,1,spy,chara_spy_00301,SR,1,250,0,0,0,0,0,,0,,10,10,,0,,0,,0,0,202510010
e,chara_dos_00101,piece_dos_00101,Special,Colorless,None,PremiumSSR,0,dos,chara_dos_00101,SSR,1,400,0,0,0,0,0,,0,,20,20,,0,,0,,0,0,202509010
e,chara_dan_00201,piece_dan_00201,Support,Blue,Short,DropSR,1,dan,chara_dan_00201,SR,1,420,255,135,940,760,7600,2,35,0.27,890,8900,ability_dan_00201_01,2,,0,,0,0,202510020
e,chara_dan_00202,piece_dan_00202,Attack,Green,Middle,PremiumUR,0,dan,chara_dan_00202,UR,1,480,370,195,630,1010,10100,2,45,0.39,1880,18800,ability_dan_00202_01,0,ability_dan_00202_02,4,,0,1,202510020
e,chara_dan_00301,piece_dan_00301,Defense,Yellow,Short,DropSR,1,dan,chara_dan_00301,SR,1,350,735,385,1440,2000,20000,1,35,0.16,700,7000,ability_dan_00301_01,2,,0,,0,0,202510020
e,chara_mag_00201,piece_mag_00201,Attack,Red,Long,PremiumUR,0,mag,chara_mag_00201,UR,1,275,260,100,710,500,5000,3,45,0.49,2160,21600,ability_mag_00201_01,0,ability_mag_00201_02,4,,0,0,202511010
e,chara_mag_00301,piece_mag_00301,Defense,Green,Middle,PremiumSSR,0,mag,chara_mag_00301,SSR,1,525,985,515,1080,2690,26900,1,45,0.34,1850,18500,ability_mag_00301_01,0,,0,,0,0,202511010
e,chara_mag_00401,piece_mag_00401,Technical,Red,Short,DropSR,1,mag,chara_mag_00401,SR,1,375,290,150,1175,790,7900,3,45,0.26,830,8300,ability_mag_00401_01,2,,0,,0,0,202511010
e,chara_mag_00501,piece_mag_00501,Special,Colorless,None,DropSR,1,mag,chara_mag_00501,SR,1,350,0,0,0,0,0,,0,,15,15,,0,,0,,0,0,202511010
e,chara_yuw_00201,piece_yuw_00201,Technical,Blue,Short,PremiumSSR,0,yuw,chara_yuw_00201,SSR,1,535,415,215,870,1130,11300,3,35,0.31,1180,11800,ability_yuw_00201_01,0,,0,,0,0,202511020
e,chara_yuw_00301,piece_yuw_00301,Support,Blue,Short,PremiumUR,0,yuw,chara_yuw_00301,UR,1,515,310,390,400,930,9300,2,35,0.27,1400,14000,ability_yuw_00301_01,0,ability_yuw_00301_02,4,,0,0,202511020
e,chara_yuw_00401,piece_yuw_00401,Defense,Red,Short,PremiumUR,0,yuw,chara_yuw_00401,UR,1,840,1535,550,1175,4280,42800,1,35,0.21,3540,35400,ability_yuw_00401_01,0,ability_yuw_00401_02,4,,0,0,202511020
e,chara_yuw_00501,piece_yuw_00501,Support,Red,Short,DropSR,1,yuw,chara_yuw_00501,SR,1,475,315,165,765,860,8600,2,35,0.27,1010,10100,ability_yuw_00501_01,2,,0,,0,0,202511020
e,chara_yuw_00601,piece_yuw_00601,Defense,Yellow,Short,DropSR,1,yuw,chara_yuw_00601,SR,1,445,935,490,1100,2550,25500,1,35,0.21,660,6600,ability_yuw_00601_01,2,,0,,0,0,202511020
e,chara_sur_00501,piece_sur_00501,Technical,Yellow,Long,PremiumUR,0,sur,chara_sur_00501,UR,1,665,365,265,1140,1000,10000,3,35,0.51,4380,43800,ability_sur_00501_01,0,ability_sur_00501_02,4,,0,0,202512010
e,chara_sur_00601,piece_sur_00601,Technical,Red,Middle,PremiumSSR,0,sur,chara_sur_00601,SSR,1,445,295,230,905,800,8000,3,40,0.41,1470,14700,ability_sur_00601_01,0,,0,,0,0,202512010
e,chara_sur_00701,piece_sur_00701,Attack,Blue,Short,DropSR,1,sur,chara_sur_00701,SR,1,570,380,445,660,1030,10300,2,50,0.24,910,9100,ability_sur_00701_01,2,,0,,0,0,202512010
e,chara_sur_00801,piece_sur_00801,Defense,Yellow,Short,DropSR,1,sur,chara_sur_00801,SR,1,725,1510,750,1530,4140,41400,1,45,0.16,1460,14600,ability_sur_00801_01,2,,0,,0,0,202512010
e,chara_yuw_00102,piece_yuw_00102,Support,Green,Short,FestivalUR,0,yuw,chara_yuw_00102,UR,1,765,675,855,1000,1840,18400,2,35,0.32,1550,15500,ability_yuw_00102_01,0,ability_yuw_00102_02,4,ability_yuw_00102_03,6,0,202512015
e,chara_osh_00001,piece_osh_00001,Technical,Red,Middle,FestivalUR,0,osh,chara_osh_00001,UR,1,900,790,640,1770,2160,21600,3,35,0.41,3520,35200,ability_osh_00001_01,0,ability_osh_00001_02,4,ability_osh_00001_03,6,0,202512020
e,chara_osh_00101,piece_osh_00101,Special,Colorless,None,PremiumUR,0,osh,chara_osh_00101,UR,1,750,0,0,0,0,0,,0,,50,50,,0,,0,,0,0,202512020
e,chara_osh_00201,piece_osh_00201,Attack,Red,Short,PremiumSSR,0,osh,chara_osh_00201,SSR,1,535,500,240,1455,1370,13700,2,35,0.29,1770,17700,ability_osh_00201_01,0,,0,,0,0,202512020
e,chara_osh_00301,piece_osh_00301,Support,Yellow,Short,PremiumSSR,0,osh,chara_osh_00301,SSR,1,350,270,170,1300,890,8900,2,35,0.27,630,6300,ability_osh_00301_01,0,,0,,0,0,202512020
e,chara_osh_00401,piece_osh_00401,Technical,Green,Short,PremiumSSR,0,osh,chara_osh_00401,SSR,1,370,275,225,640,700,7000,3,30,0.32,870,8700,ability_osh_00401_01,0,,0,,0,0,202512020
e,chara_osh_00501,piece_osh_00501,Technical,Blue,Short,PremiumSSR,0,osh,chara_osh_00501,SSR,1,335,320,165,1795,870,8700,3,35,0.31,630,6300,ability_osh_00501_01,0,,0,,0,0,202512020
e,chara_osh_00601,piece_osh_00601,Defense,Green,Short,DropSR,1,osh,chara_osh_00601,SR,1,800,1720,750,1265,5280,52800,1,47,0.17,960,9600,ability_osh_00601_01,2,,0,,0,0,202512020
e,chara_jig_00401,piece_jig_00401,Technical,Colorless,Short,PremiumUR,0,jig,chara_jig_00401,UR,1,1000,770,655,1140,2100,21000,3,30,0.31,2500,25000,ability_jig_00401_01,0,ability_jig_00401_02,4,,0,0,202601010
e,chara_jig_00501,piece_jig_00501,Support,Green,Short,PremiumSSR,0,jig,chara_jig_00501,SSR,1,605,425,395,1740,1160,11600,2,30,0.32,1200,12000,ability_jig_00501_01,0,,0,,0,0,202601010
e,chara_jig_00601,piece_jig_00601,Defense,Blue,Short,DropSR,1,jig,chara_jig_00601,SR,1,440,920,480,1195,2510,25100,1,35,0.21,880,8800,ability_jig_00601_01,2,,0,,0,0,202601010
e,chara_jig_00701,piece_jig_00701,Special,Colorless,None,DropSR,1,jig,chara_jig_00701,SR,1,650,0,0,0,0,0,,0,,15,15,,0,,0,,0,0,202601010
e,chara_you_00001,piece_you_00001,Attack,Red,Middle,PremiumUR,0,you,chara_you_00001,UR,1,960,740,355,890,2020,20200,2,45,0.34,4040,40400,ability_you_00001_01,0,ability_you_00001_02,4,,0,0,202602010
e,chara_you_00101,piece_you_00101,Technical,Yellow,Short,PremiumSSR,0,you,chara_you_00101,SSR,1,750,515,350,1465,1400,14000,3,40,0.26,2300,23000,ability_you_00101_01,0,,0,,0,0,202602010
e,chara_you_00201,piece_you_00201,Technical,Green,Middle,DropSR,1,you,chara_you_00201,SR,1,540,355,235,1120,970,9700,3,35,0.36,1790,17900,ability_you_00201_01,2,,0,,0,0,202602010
e,chara_you_00301,piece_you_00301,Attack,Blue,Short,DropSR,1,you,chara_you_00301,SR,1,735,650,340,670,1770,17700,2,35,0.29,1910,19100,ability_you_00301_01,2,,0,,0,0,202602010
e,chara_kim_00001,piece_kim_00001,Defense,Blue,Short,PremiumUR,0,kim,chara_kim_00001,UR,1,935,1680,920,1815,5060,50600,1,35,0.21,2980,29800,ability_kim_00001_01,0,ability_kim_00001_02,4,,0,0,202602020
e,chara_kim_00101,piece_kim_00101,Attack,Green,Short,PremiumSSR,0,kim,chara_kim_00101,SSR,1,725,720,375,635,1960,19600,2,35,0.24,2180,21800,ability_kim_00101_01,0,,0,,0,0,202602020
e,chara_hut_00001,piece_hut_00001,Defense,Green,Short,PremiumUR,0,hut,chara_hut_00001,UR,1,955,1725,1020,1675,5310,53100,1,40,0.21,3210,32100,ability_hut_00001_01,0,ability_hut_00001_02,4,,0,0,202603010
```

---

<!-- FILE: ./projects/glow-masterdata/MstUnitI18n.csv -->
## ./projects/glow-masterdata/MstUnitI18n.csv

```csv
ENABLE,release_key,id,mst_unit_id,language,name,description,detail
e,202509010,chara_dan_00001_ja,chara_dan_00001,ja,オカルン,"幽霊は信じていないが、宇宙人は信じている、怪異現象オタクの男子高校生。\n初心で鈍感が故に不器用な言動も目立つが、仲間のためならば危険なことにも立ち向かう勇気を持っている。",必殺ワザで自身が受けるダメージをカットできるディフェンスキャラ!
e,202509010,chara_dan_00002_ja,chara_dan_00002,ja,"ターボババアの霊力 オカルン","オカルンがターボババアの呪いを受けて変身した姿。\n髪は白髪で逆立ち、口元にはマスクが浮かび上がる。脅威のスピードで動きまわり、限界を突破する“本気”を使うことができる。\nしかし普段とは異なり気だるくネガティブな性格になってしまう。",素早く前線に到着し、連続攻撃の必殺ワザで複数の相手にダメージを与えることもできるアタックキャラ!
e,202509010,chara_dan_00101_ja,chara_dan_00101,ja,モモ,"宇宙人は信じていないが幽霊は信じている、霊媒師の家系の\n女子高生。セルポ星人にさらわれた時に超能力に目覚める。困っている人を見過ごせない優しい一面もあり、誰にも裏表なく接することができる。\n憧れの人のような硬派な男性が好きと自称しており、オカルンと憧れの人との共通点を知ってときめいてしまうことも。",必殺ワザで複数の相手にダメージを与え、さらに相手をノックバックさせることができるぞ!
e,202509010,chara_gom_00001_ja,chara_gom_00001,ja,"囚われの王女 姫様","王女にして、国王軍第三騎士団の“騎士団長”。\n数々の戦場を生き抜き、多くの武勲をあげてきたが、現在は魔王軍に囚われ、拷問を受ける日々を送っている。品行方正で誇り高く見えるが、実際は日々の“拷問”に尽く屈してしまっている。",耐久性抜群のディフェンスキャラ!必殺ワザと特性でより高い耐久を発揮!
e,202509010,chara_gom_00101_ja,chara_gom_00101,ja,トーチャー・トルチュール,"魔王軍の最高位拷問官にして、牢獄の責任者。\n最年少で最高位拷問官の地位に上り詰めた“拷問”の天才。\n国王軍の情報を得るために様々な方法で姫様に対して“拷問”を行っている。食欲を掻き立てる料理や美味しそうに食事をする姿で姫様を屈服させることが得意。",必殺ワザで、攻撃DOWNを相手に付与しながら戦うテクニカルキャラ!
e,202509010,chara_gom_00201_ja,chara_gom_00201,ja,クロル,"魔王軍の一級戦闘員であり、上級拷問官。\n明るくギャルのような性格。多種多様な動物を飼育しており、猛獣などを意のままに操ることができる猛獣使い。白熊のキュイや犬など、可愛い動物と共に“拷問”を行う。すこしマニアックな趣向もしばしば見られる。",遠距離からの攻撃を得意とするアタックキャラ!必殺ワザで複数の相手に攻撃できるぞ!
e,202509010,chara_sur_00001_ja,chara_sur_00001,ja,"和倉 優希","魔都に迷い込んだ高校3年生。\n醜鬼に襲われたところを羽前 京香に助けられ、奴隷になる。雑草のような生命力を持っている。家事全般が得意で、魔防隊の管理人を務めることになる。七番組隊員からの理不尽な扱いにもめげず優しく接する人物。",必殺ワザで同じコマにいる緑属性の味方の攻撃をUPするぞ!
e,202509010,chara_jig_00001_ja,chara_jig_00001,ja,がらんの画眉丸,"“がらんの画眉丸”として畏れられていた元石隠れ衆最強の忍。\n血も涙もないがらんどうと呼ばれていたが、里の長の娘と結婚し、愛に触れ心を取り戻すも、死罪人として囚われてしまう。無罪放免となり愛する妻の元へ帰るため、処刑人・山田浅ェ門 佐切と不老不死の仙薬探しに赴く。",火傷付与と状態異常軽減を兼ね揃えているテクニカルキャラ!
e,202509010,chara_jig_00101_ja,chara_jig_00101,ja,"山田浅ェ門 佐切","山田浅ェ門・試一刀流十二位。\n処刑執行人を代々務める山田家の娘。女性ながら剣技に優れているが、幼少の頃より首切り浅と罵られて過ごしてきた。“首斬りの業”に向き合い、苦しんでいたが、画眉丸との出会いや、島での出来事により人として成長していく。",必殺ワザで緑属性の相手に対して大ダメージを与える!
e,202509010,chara_jig_00201_ja,chara_jig_00201,ja,杠,"“傾主の杠”との呼び名を持つくのいち。\n常に己の保身を第一に考え、その為には他者をも利用する。冷酷に見えるが、画眉丸たちに協力するなどフレンドリーな一面も。仙薬探しに参加したのは、ただ生きて帰りたいから。粘膜を駆使する忍術を使用する。",必殺ワザで複数体の相手にダメージを与え、相手を毒状態にするテクニカルキャラ!
e,202509010,chara_jig_00301_ja,chara_jig_00301,ja,"山田浅ェ門 仙汰","山田浅ェ門・試一刀流五位。\n杠の監視役。勤勉で博識だが、気弱な侍。“首斬りの業”に悩んでおり、自身の正当性を宗教を学ぶことに求めたが、逆にその勤勉性が評価され、段位が高くなった。懐に植物学や宗教学、蘭学などの冊子を所持している。",前線で味方を守るディフェンスキャラ!さらに連続攻撃で強さを発揮!
e,202509010,chara_kai_00101_ja,chara_kai_00101,ja,"市川 レノ","新米防衛隊員。\n日比野 カフカの怪獣専門清掃業者時代の後輩であり、共に防衛隊の道へと進んだ良き相棒。冷静沈着で真面目な性格だが、負けず嫌いで努力を惜しまない熱い一面を持つ。\n日比野 カフカを尊敬し、彼の秘密を知りながらも信頼を寄せている。",遠距離からの連続攻撃と相手を氷結状態にする必殺ワザで戦うテクニカルキャラ!
e,202509010,chara_kai_00301_ja,chara_kai_00301,ja,"四ノ宮 キコル","新米防衛隊隊員。\n日本防衛隊長官を父に持ち、16歳でカルフォルニア討伐大学を飛び級で最年少主席卒業したエリート。市川 レノ曰く「アグレッシブで高圧的な性格」。怪獣１０号による立川基地襲撃の際には専用武器の斧を振い、遊軍として目覚ましい活躍を見せた。",範囲攻撃とスタン効果を持つ必殺ワザで、相手の動きを封じつつ攻撃する!
e,202509010,chara_kai_00001_ja,chara_kai_00001,ja,"日比野 カフカ","32歳の新米防衛隊員。\n防衛隊員を目指すも夢破れ、燻る日々を過ごしていたが、\n市川 レノに背を押され、苦難の末に夢を掴み取った。候補生としての入隊だったが、怪獣専門清掃業者時代の知識を駆使し、討伐に貢献。正隊員へ昇格した。",必殺ワザで自身の攻撃をUPするアタックキャラ!
e,202509010,chara_kai_00002_ja,chara_kai_00002,ja,"隠された英雄の姿 怪獣８号","謎の生物に寄生され、怪獣化した日比野 カフカの姿。\n人々を守るために拳をふるい、怪獣を討伐する。\n防衛隊発足以来初の未討伐怪獣として日本中から追われていたが、怪獣１０号による立川基地襲撃の際、仲間の危機を救うために人前で変身。その正体を明かすこととなった。",必殺ワザの連続範囲攻撃で圧倒的ダメージを誇るアタッカー!さらに、ノックバックで前線を押し上げることができるぞ!
e,202509010,chara_dos_00001_ja,chara_dos_00001,ja,"冬木 美波","北陵高等学校に通う女子高生。\nマイナス8度の極寒の中でも生足、スマホを触るため手袋なしのなまらめんこい生粋の道産子ギャル。冬の北海道に引っ越してきたばかりの四季 翼と偶然遭遇した。仲間意識と押しが強いが、見た目に反してピュアな一面もある。","前線を支える遠距離サポート!攻撃UPで戦況を有利に導くことができるぞ!                                                                                                                                                        "
e,202509010,chara_chi_00001_ja,chara_chi_00001,ja,デンジ,"相棒のポチタをその身に宿す、『チェンソーの悪魔』の 少年。\n 借金返済のためにこき使われるド底辺の暮らしをしていたためか、食欲や性欲といったシンプルな欲求に正直。",必殺ワザで黄属性の相手に対して大ダメージを与えることができる!
e,202509010,chara_sum_00001_ja,chara_sum_00001,ja,"網代 慎平","調理師専門学校に通う17歳の少年。\n幼馴染の小舟 潮の葬儀に参列するために、2年ぶりに故郷の日都ヶ島(ひとがしま)へ帰省したところ、同じ夏を繰り返すループに巻き込まれてしまう。冷静に思考するためにフカンして考えることを心がけている。",必殺ワザで連続攻撃と相手の攻撃をDOWNするテクニカルキャラ!
e,202509010,chara_sum_00201_ja,chara_sum_00201,ja,"小舟 澪","高校１年生で、小舟 潮の実の妹。\n黒い髪に父親譲りの青い瞳、日焼けした褐色肌。２年ぶりに故郷の日都ヶ島(ひとがしま)へ帰った網代 慎平に対して、姉を亡くした直後でもいつもと変わらない様子で接する健気な性格をしている。\n運動神経が良く、水泳部に所属している。",相手をノックバックする必殺ワザを持つ近距離アタッカー!
e,202509010,chara_sur_00101_ja,chara_sur_00101,ja,"誇り高き魔都の剣姫 羽前 京香","魔防隊七番組の組長。\n真面目な性格で、日々鍛錬を欠かさない。醜鬼に故郷を滅ぼされた過去から、醜鬼の絶滅を目標としている。能力は「無窮の鎖(スレイブ)」。奴隷にした生命体の力を引き出し行使できる能力で、現在は魔都で助けた和倉 優希を使役している。",必殺ワザの範囲攻撃で複数の相手にダメージを与えることもできるぞ!特性も強力!
e,202509010,chara_sum_00101_ja,chara_sum_00101,ja,"影のウシオ 小舟 潮","17歳の女子高校生で、網代 慎平の幼馴染。\nフランス人の父譲りの金髪、青い瞳を持ち、明るくはつらつとした性格をしている。島の人のことを大切に思っており、正義感も強い。網代 慎平と共に影の脅威から島の住人や仲間たちを守るために、何度も同じ夏を繰り返す。","前線の維持に特化したサポートキャラ!必殺ワザで味方の被ダメージを軽減できるぞ!                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                          "
e,202509010,chara_sur_00201_ja,chara_sur_00201,ja,"東 日万凛","魔防隊七番組の副組長であり、組長の羽前 京香に心酔している。\nプライドが高く男嫌い。そのため和倉 優希の存在を良く思っておらず、強くあたることもあるが、戦闘時の強さは認めている。能力は「青雲の志(ラーニング)」。他人の能力を学び自身でも使用できる。",前線で活躍するアタッカー!範囲攻撃の必殺ワザで集団戦に強いぞ!
e,202509010,chara_sur_00301_ja,chara_sur_00301,ja,"駿河 朱々","魔防隊七番組の隊員。\n好奇心旺盛な性格で、平凡な家庭の生まれだが刺激を求めて魔防隊に入隊した。和倉 優希には挑発的な態度をとることも多い。身体の大きさを変化させる「玉体革命（パラダイムシフト）」で醜鬼を圧倒する。",必殺ワザのノックバックで前線を押し上げながら戦うディフェンスキャラ!
e,202509010,chara_tak_00001_ja,chara_tak_00001,ja,"ハッピー星からの使者 タコピー","宇宙にハッピーを広めるため旅をするハッピー星人。\nハッピーな思考の持ち主で、人間の行動や考えには疎い。故郷を離れて地球へ降り立つも、遭難。地球人の女の子、久世しずかに助けられ、ハッピー道具を使い彼女を喜ばせるために奮闘する。",耐久力に優れ、味方を守る壁役!ダメージカットの必殺ワザと強力な特性で前線の維持に貢献できるぞ!
e,202509010,chara_chi_00201_ja,chara_chi_00201,ja,"早川 アキ","公安4課に所属するデビルハンター。\nマキマの忠実な部下。過去の悲惨な出来事から悪魔を強く憎んでおり、悪魔をこの世から駆逐するという強い思いでデビルハンターになった。",必殺ワザで複数の相手にダメージを与えることができるぞ!
e,202509010,chara_chi_00301_ja,chara_chi_00301,ja,パワー,"『血の悪魔』の魔人。\n魔人でありながら、公安4課に所属するデビルハンター。嘘つきでわがままで、自分の都合の良いようにしか考えない性格をしている。自分の血を固めて武器にすることができる。また、疲れるが他人の血を操って止血も可能。",必殺ワザで赤属性の相手に大ダメージを与えることができるぞ!さらに、複数の相手にダメージを与えることができるぞ!
e,202509010,chara_chi_00002_ja,chara_chi_00002,ja,"悪魔が恐れる悪魔 チェンソーマン","相棒のポチタの命と引き換えに『チェンソーの悪魔』として蘇ったデンジの姿。頭や腕など体から複数のチェンソーの刃が飛び出している。\n 胸から出ているスターターロープを引っ張ることで変身し、\n チェンソーマンになると深い傷を負っていても復活・蘇生することができる。",近距離での単体攻撃に特化したテクニカルキャラ!連続攻撃と体力吸収の必殺ワザで攻守を両立して戦うことができるぞ!
e,202509010,chara_rik_00001_ja,chara_rik_00001,ja,リコピン,"不思議な場所キュートピアに住む、トマトの苗から生まれたトイプードル。身長も体重もトマト7つぶんのとっても元気な3歳の男の子だが、健康診断の結果があまり良くなかったため、甘いものを控えるよう医者に言われている。\nインスタグラムもやってるからみんなフォローしてね★",必殺ワザで連続攻撃する近距離アタックキャラ!
e,202509010,chara_aka_00101_ja,chara_aka_00101,ja,文蔵,"ラーメン赤猫の店長でメイン調理を担当。\nラーメン屋台「あかねこ」をやっていた店主から店と味を引き継ぎ、佐々木と共にラーメン赤猫を開業する。職人気質で、口数は少ないが気配りのできる猫。",必殺ワザで連続攻撃と、相手を火傷状態にするテクニカルキャラ!
e,202509010,chara_aka_00001_ja,chara_aka_00001,ja,佐々木,"ラーメン赤猫のCEO、接客・レジ・経理担当。\n従業員の労働環境に気を遣うデキる猫。文蔵とは幼馴染で、野良猫時代を経験したのちに人間に拾われる。その後文蔵を口説き落とし、ラーメン赤猫を開業する。",必殺ワザで複数体の相手に攻撃できるディフェンスキャラ!
e,202509010,chara_spy_00101_ja,chara_spy_00101,ja,"<黄昏> ロイド",東西平和の実現のために活動する西国(ウェスタリス)きっての敏腕諜報員(エージェント)。名前も過去も捨て、戦争を回避するために東国(オスタニア)にて諜報活動を行なっている。現在は超難関任務「オペレーション<梟>(ストリクス)」に従事。ロイド・フォージャーという名前と肩書は、この任務のために用意された仮初めのもの。,遠距離アタックキャラ!さらに、必殺ワザで青属性の相手には大ダメージを与えることができるぞ!
e,202509010,chara_spy_00201_ja,chara_spy_00201,ja,"<いばら姫> ヨル","東国(オスタニア)の暗殺組織「ガーデン」の殺し屋。\n上長の命令で母国に害を成すと判断されたものを抹殺する。\n殺し屋は裏の顔で、普段は市役所の事務員として働く。\n世間に溶け込むためロイド・フォージャーの案に乗って仮初めの夫婦に。",高い攻撃性能を誇り、必殺ワザで大ダメージを与えつつ自身の攻撃もUPするぞ!
e,202510010,chara_spy_00401_ja,chara_spy_00401,ja,フランキー・フランクリン,"表の顔は普通のタバコ屋だが、<黄昏>も一目置く情報屋という裏の顔を持つ人物。\n東国(オスタニア)に住んではいるが、国の空気を好ましく思っておらず、西国(ウェスタリス)の諜報員にも協力する。\n手先が器用で、発明家としても優秀だ。",近距離での単体攻撃に優れたディフェンスキャラ!必殺ワザで相手にダメージを与えるぞ!
e,202510010,chara_spy_00501_ja,chara_spy_00501,ja,"姉を想う盲愛 ユーリ・ブライア","東国(オスタニア)の外務省に勤務するヨル・フォージャーの弟。\n外交官は仮の姿で、実際は入省して1年ほどで国家保安局に\n抜擢され、秘密警察として働いている。階位は少尉。\nヨル・フォージャーへの愛情が強く、\n義兄のロイド・フォージャーを強く敵視する。",必殺ワザのダメージカットで前線を維持するディフェンスキャラ!
e,202509010,chara_rik_00101_ja,chara_rik_00101,ja,"甘戸 めめ","不思議な場所キュートピアに迷い込んだ、普通の中学生の女の子。\nコンパクトミラーでキュートピアと現実の世界を行き来することができる。自分が納得がいかないことは絶対にゆずらない頑固な面もあるが、かわいいぬいぐるみが大好きな優しい女の子。",必殺ワザで、自身の攻撃と自身と同じコマにいる赤属性の味方の攻撃をUPするぞ!
e,202509010,chara_bat_00001_ja,chara_bat_00001,ja,"清峰 葉流火","都立小手指高校の1年生。\n口数が少なくクールな性格で、野球一筋。140キロを越える剛速球を投げる抜群のフィジカルに加え、バッティングでも非凡な才能を見せる。中学時代は要 圭とバッテリーを組み、宝谷シニアでは「天才バッテリー」として名を馳せた。",遠距離から攻撃ができるアタックキャラ!緑属性の相手に大ダメージを与えることができるぞ!
e,202509010,chara_bat_00101_ja,chara_bat_00101,ja,"要 圭","都立小手指高校1年。\n中学時代は清峰 葉流火（きよみね はるか）とバッテリーを組み、宝谷シニアの天才バッテリーと呼ばれていた。どんな球も捕球し、キレたリードで勝利をもぎ取る智将捕手だったが、現在は記憶喪失で野球は素人同然。性格も、かつての智将時代とはかけ離れている。",近、中距離での耐久戦に優れたディフェンスキャラ!必殺ワザのダメージカットで相手から受けるダメージを軽減できるぞ!
e,202509010,chara_mag_00001_ja,chara_mag_00001,ja,"新人魔法少女 桜木 カナ","「株式会社マジルミエ」の新人魔法少女。\n就職活動の面接中に駆けつけた魔法少女・越谷 仁美に惚れ込み、魔法少女への一歩を踏み出した。脅威的な記憶力を持っており、一度聞いたことや読んだものは忘れない。徹底的な事前準備を行う努力家。",遠距離からの通常攻撃が強力!さらに、必殺ワザの範囲攻撃で複数の相手にダメージを与えることができるぞ!
e,202509010,chara_mag_00101_ja,chara_mag_00101,ja,"越谷 仁美","ベンチャー企業「株式会社マジルミエ」に所属する魔法少女。\n普段着はジャージで口も悪いが、魔法少女としては天才的なセンスを持っている。現場での素早い判断力や抜群のフィジカルを駆使して怪異退治に当たる。ホーキの操作は感覚的なため、人への説明は壊滅的に下手。",必殺ワザの範囲攻撃で複数の相手を同時に攻撃ができるぞ!
e,202509010,chara_yuw_00001_ja,chara_yuw_00001,ja,"リリエルに捧ぐ愛 天乃 リリサ","高校1年生。恋愛に鈍感でちょっとドジなオタク。\n「好きなことを堂々とするのがカッコいい」と思っている。観る人にキャラが現実にいると思わせるようなコスプレをすることに情熱を注いでいる。大好きなリリエルのコスプレROMを作るために、漫画研究部に入部した。",必殺ワザで複数の相手にダメージを与えることもできるぞ!さらに、必殺ワザで青属性の相手には大ダメージを与えることができるぞ!
e,202509010,chara_yuw_00101_ja,chara_yuw_00101,ja,"コスプレに託す乙女心 橘 美花莉","高校1年生。\n天乃 リリサと同じクラスの人気モデル。非常に一途な性格で、幼馴染である奥村 正宗に密かに10年間の片思いをしている。3次元女子の気持ちに鈍感なせいで気付かないでいる奥村 正宗の気を引くために、天乃 リリサと共にコスプレをすることに。",必殺ワザで相手の攻撃をDOWNさせるテクニカルキャラ!特性も強力!
e,202509010,chara_aha_00001_ja,chara_aha_00001,ja,ライドウ,"ごく普通の男子高校生。\n当初、隣の席の阿波連さんには距離を感じていたが、学校生活を共にする中で距離を縮めた。予測不能な彼女の行動に驚きつつも、それを受け入れている。高校での目標は、友だち100人作ること。",コストが低く召喚しやすい近距離ディフェンスキャラ!
e,202509010,chara_aha_00101_ja,chara_aha_00101,ja,"阿波連 れいな","「小さくて」、「物静か」な女子高校生。\n他人との距離をはかるのが苦手で、その行動は予測不能。ある日、教室で隣の席に座るライドウくんに消しゴムを拾ってもらって以降、彼との距離は急接近し…⁉︎",必殺ワザで自身と黄属性の味方の攻撃をUP!
e,202509010,chara_ron_00001_ja,chara_ron_00001,ja,"鴨乃橋 ロン","家に引きこもり世捨て人の生活を送っている青年。\n探偵養成学校BLUEで、解けない謎はない天才と呼ばれていたが、とある事件が原因でBLUEから追放された。探偵行為を禁止されていたが、一色 都々丸をパートナーに推理を再開する。",必殺ワザで相手をスタン状態にすることができるぞ!
e,202509010,chara_ron_00101_ja,chara_ron_00101,ja,"一色 都々丸","警視庁捜査一課の刑事。\n後先考えずに真っ先に体が動いてしまう性格で、課の中でお荷物扱いされていた。\n ピュアでマヌケなところを買われ、鴨乃橋 ロンの代わりに探偵役をすることに。鴨乃橋 ロンからはトトと呼ばれている。",中距離からの単体攻撃を得意とするディフェンスキャラ!
e,202509010,chara_kai_00201_ja,chara_kai_00201,ja,"第三部隊隊長 亜白 ミナ","日本防衛隊第三部隊隊長であり、防衛隊屈指の実力者。\n幼い頃、自身の住む街が怪獣に襲撃されたことをきっかけに、幼馴染の日比野 カフカと共に防衛隊員を志した。狙撃武器での大型怪獣の討伐を得意としており、防衛隊の中でもトップクラスの人気と実力を誇っている。",遠距離から通常攻撃での単体攻撃と必殺ワザでの範囲攻撃を使い分ける器用なアタッカー!
e,202509010,chara_kai_00401_ja,chara_kai_00401,ja,"保科 宗四郎","防衛隊第三部隊副隊長。\n室町時代から続く怪獣討伐の家系で、刀を用いた近接戦闘のスペシャリスト。選抜試験でカフカを「光るものがある」と評価し、自らの小隊に候補生として入隊させた。しかし、実際はカフカに違和感を抱いており、監視するためでもあった。",近距離で素早く攻撃し、必殺ワザの連続攻撃で大ダメージ!
e,202509010,chara_kai_00501_ja,chara_kai_00501,ja,"四ノ宮 功","日本防衛隊長官。\n怪獣２号の唯一の適合者で、高齢ではあるが、圧倒的なパワーで怪獣状態のカフカと渡り合う。\n四ノ宮 キコルの実の父親にあたり、娘に対して常に完璧であり続け、他の追随を許さない圧倒的な存在になることを求めている。",必殺ワザで複数体の相手にダメージを与えられるアタックキャラ!
e,202509010,chara_kai_00601_ja,chara_kai_00601,ja,"古橋 伊春","防衛隊隊員で、カフカやキコルたちと同期。\n負けず嫌いで言葉はやや荒いが情に厚いムードメーカー。ライバル視しているレノが頭角を表していく様子に焦りを感じながらも努力を絶やさない、真面目で熱い一面を持つ。",必殺ワザで自身の攻撃をUPできるディフェンスキャラ!
e,202509010,chara_sur_00401_ja,chara_sur_00401,ja,"大川村 寧","魔防隊七番組の隊員。\n隊最年少の11歳で小学生。魔防隊所属ながら非戦闘要員。優しい性格で、先に入隊した上司として和倉 優希を気にかける。能力は「きっと見つける」。千里眼のような力で索敵し、隊を補佐する。","必殺ワザで次回発動するJUMBLE RUSHのダメージをUPすることができるぞ!"
e,202509010,chara_spy_00001_ja,chara_spy_00001,ja,"わくわく アーニャ","孤児院に預けられていた少女。\nロイド・フォージャーに引き取られ、娘として暮らしている。実は他人の心を読むことができる超能力者で、周囲の人にはそのことを隠している。スパイと殺し屋との共同生活にわくわくしている。",必殺ワザで特定のコマにいる味方を一気に回復!さらに味方の攻撃をUPすることができ、万能に活躍できるぞ!
e,202510010,chara_spy_00301_ja,chara_spy_00301,ja,ダミアン・デズモンド,"ドノバン・デズモンドの息子。\n学園では親の権力を笠に着て傲慢な態度を取っているが、根は優しく誇り高い少年。アーニャ・フォージャーにぶっ飛ばされて以来、気になっている。“プランB”成功の鍵となる標的。",必殺ワザで特定のコマにいる味方アタックキャラの攻撃をUP!特定のタイミングで火力を上げたい時に活躍!
e,202509010,chara_dos_00101_ja,chara_dos_00101,ja,"秋野 沙友理","北陵高等学校に通う、黒髪清楚系ギャル。\nゲームが得意だが恥ずかしがり屋な性格。スキー授業で冬木 美波に助けられ、友達となる。四季 翼のことを意識しているが、冬木 美波との友情も大事にしたいと思っている。",特定のコマにいる味方ディフェンスキャラの被ダメージをカット!味方の体力がピンチな時に活躍する!
e,202510020,chara_dan_00201_ja,chara_dan_00201,ja,アイラ,"オカルンやモモと同じ高校に通う女子高生で、自他共に認める美少女。\nオカルンの金の玉を拾ったことで能力が開花してしまう。普段は天然な美少女を演じているが、一度思い込むと周りの声が聞こえなくなってしまう、頑固な一面もある。",必殺ワザで自身と青属性の味方の被ダメージをカットできるぞ!
e,202510020,chara_dan_00202_ja,chara_dan_00202,ja,"アクさらの愛 アイラ","アクロバティックさらさらの炎を体内に取り入れたことにより、能力を得たアイラの姿。\n平常時より髪の毛が著しく伸び、自由に操ることができる他、バレエを踊るような優雅さで足技を駆使して戦う。アクロバティックさらさらのような口の悪いお嬢様口調で喋る。",中距離からの攻撃を得意とするアタックキャラ!必殺ワザの連続攻撃で単体の相手にダメージを与えるぞ!
e,202510020,chara_dan_00301_ja,chara_dan_00301,ja,"招き猫 ターボババア","神出鬼没で、全国各地で暴れ回ってた近代妖怪。\nオカルン達に倒された後消えたと思われていたが、実はオカルンの中に隠れていた。今は力をオカルンの中に残し、意識のみが招き猫の中に入って動き回っている。\n現在はモモの家で暮らしている。",耐久力特化のディフェンスキャラ!必殺ワザで自身が受けるダメージをカットできるぞ!
e,202511010,chara_mag_00201_ja,chara_mag_00201,ja,"絶対効率の体現者 土刃 メイ","業界大手・『アスト株式会社』に所属する魔法少女。\n効率を重視し、最小の手数で最大の成果を上げる機械のような精度で結果を積み上げ、社内でもトップの納品数を誇っている。感情をほとんど表に出さず、常に無表情で業務にあたるその戦い方には一切の無駄がない。",遠距離からの連続範囲攻撃とノックバックで前線を押し上げることができるアタックキャラ!さらに、特定の条件で自身の攻撃をUPするぞ!
e,202511010,chara_mag_00301_ja,chara_mag_00301,ja,"葵 リリー","大手化粧品メーカー『ミヤコ堂』の魔法少女。\n芯の強さと気品を併せ持つ美の体現者。魔法少女は素敵な仕事であることを伝えたいと考えている。株式会社マジルミエとの協働業務にて、桜木 カナとバディを組み、優雅かつ的確に怪異の対処にあたった。",中距離で戦うディフェンスキャラ!必殺ワザで自身が受けるダメージをカットできるぞ!
e,202511010,chara_mag_00401_ja,chara_mag_00401,ja,"槇野 あかね","新技術の研修のため『株式会社マジルミエ』へ出向してきた、『アプダ株式会社』に所属する魔法少女。\n上昇志向で、仕事熱心な性格。業務に関する新しくて良いものは取り入れたい、選択肢は沢山持っておきたいといった思いを持っている。",必殺ワザの攻撃DOWNを駆使して戦うテクニカルキャラ!
e,202511010,chara_mag_00501_ja,chara_mag_00501,ja,"重本 浩司","『株式会社マジルミエ』の社長で、魔法少女絶対主義者。\n会社では常に魔法少女のコスプレをしており、初見の人を驚かせている。新人の桜木 カナに重大な局面を任せたり、社員の成長を素直に喜んだりと、従業員を信頼した経営のできる人物。",必殺ワザで特定のコマにいる味方アタックキャラへのダメージをカットするぞ!
e,202511020,chara_yuw_00201_ja,chara_yuw_00201,ja,"羽生 まゆり","奥村 正宗たちの通う高校の新任教師にして、漫画研究部の顧問。以前はコスプレ四天王まゆらとして活動していた。引退していたが、コスプレや、コスプレイヤーのことは愛しており、何かと協力は惜しまない良き顧問教師。",必殺ワザで複数の相手の攻撃をDOWNして、戦況をコントロールするテクニカルキャラ！
e,202511020,chara_yuw_00301_ja,chara_yuw_00301,ja,"勇気を纏うコスプレ 乃愛","高校1年生。カメラおじさんのブログに5人の新星レイヤーとして選出されたうちの1人。友達が欲しくてコスプレを始めるが、極度の人見知りで苦戦していた。天乃 リリサとの出会いから自身のトラウマと向き合い、一緒にコスプレをする友人となった。",チームの火力を引き上げるサポートキャラ！必殺ワザの攻撃UPでチーム全体を優位に導くことができるぞ！
e,202511020,chara_yuw_00401_ja,chara_yuw_00401,ja,"伝えたいウチの想い 喜咲 アリア","高校生のギャル。アウトドア派で誰にでもフレンドリー。距離を詰めるのが非常に早い。コスプレを始めた理由は、父親に今でも「ヴァル戦」が好きだと伝えたいから。コスプレ初心者だったが、天乃 リリサに指導されるうちにコスプレにハマり出す。",必殺ワザで単体の相手に大ダメージを与え、ノックバックさせることができる!さらに、特定の条件で自身の被ダメージをカットできるぞ!
e,202511020,chara_yuw_00501_ja,chara_yuw_00501,ja,753♡,コスプレ四天王にして、プロのコスプレイヤー。誰よりもコスプレとコスプレイヤーを愛していると自負しており、非常にプライドが高い。衣装の圧倒的なクオリティと、キャラクターの知名度や流行感、本人の人気を活かし、トップレイヤーとしての実力を発揮している。,必殺ワザで自身と赤属性の味方のダメージをカットできるぞ!
e,202511020,chara_yuw_00601_ja,chara_yuw_00601,ja,"奥村 正宗","高校2年生。漫画研究部の唯一の部員にして部長。\n母親が突然いなくなったり、姉に疎まれたりした過去から3次元の女性に苦手意識を持つようになり、自他の恋愛感情に鈍感。「2次元は俺の嫁」と豪語するほどのリリエル好きでオタク。",必殺ワザで自身が受けるダメージをカットできるディフェンスキャラ!相手の攻撃に合わせた必殺ワザの発動がカギ!
e,202512010,chara_sur_00501_ja,chara_sur_00501,ja,"空間を操る六番組組長 出雲 天花","魔防隊六番組の組長。和倉 優希を「奴隷クン」と呼び、何としても手に入れようとする。能力は「天御鳥命」(アメノミトリ)で、空間を操作することができ、瞬間移動や応用した戦闘が可能。冷静沈着で子供の頃から優等生。順風満帆な人生を送っている。",遠距離から、広範囲の必殺ワザで相手をスタン状態にすることができるぞ!
e,202512010,chara_sur_00601_ja,chara_sur_00601,ja,"東 八千穂","魔防隊六番隊・副組長。東の家名にこだわりを持っており、非常にプライドが高い。妹の東 日万凛のことを溺愛しているが、なかなか素直に伝えることができずにいる。能力は「東の辰刻」(ゴールデンアワー)で、ポーズを決めると時を止めたり戻したりすることが可能。",中距離からの必殺ワザで複数の相手をスタン状態にすることができるぞ!
e,202512010,chara_sur_00701_ja,chara_sur_00701,ja,"和倉 青羽","魔都に現れた人型醜鬼。正体は魔都災害によって行方不明になっていた優希の姉。京香の故郷を襲った一本角の醜鬼を従えている。魔防隊を敵だと断言し、彼女と同じく醜鬼となった銭函 ココと湯野 波音達と共に魔防隊と戦おうとする。",必殺ワザで複数の相手に連続攻撃するアタックキャラ!素早い攻撃で先陣を切って攻め込むぞ!
e,202512010,chara_sur_00801_ja,chara_sur_00801,ja,"無窮の鎖 和倉 優希","和倉 優希がスレイブとして変身した姿。醜鬼に似ている姿で、首には鎖付きの首輪がついている。使役する主人は優希の背に乗り、鎖によって操縦することが可能。変身を解くと、変身中の働きによって主人からの「褒美」が与えられる。",必殺ワザで単体の相手に攻撃することができるぞ！
e,202512015,chara_yuw_00102_ja,chara_yuw_00102,ja,"愛届ける聖夜のサンタ 橘 美花莉","橘 美花莉がサンタコスチュームに身を包んだ姿。抱えた袋には溢れんばかりの愛が詰まっている。漫画研究部のクリスマスパーティーでは、コスプレ衣装作りに尽力した天乃 リリサへ冬用の私服を贈り、パーティー後に行き場を失った奥村 正宗を自宅でもてなすなど、皆に愛を届けた。",広範囲の必殺ワザで相手にダメージを与えつつ、オブジェクトをランダムな位置に生成することができるぞ!オブジェクトごとの効果をうまく活用して、バトルを有利に進めよう!
e,202512020,chara_osh_00001_ja,chara_osh_00001,ja,"B小町不動のセンター アイ","苺プロダクションのアイドルグループ・B小町の絶対的エース、不動のセンターで究極の美少女。16歳で星野 アクアと星野 ルビーを出産するためにアイドル活動を一時休止。復帰後はドラマや映画に出演したり、モデルやラジオのアシスタントなど幅広く活動し、順調に芸能界を駆け上がっていた。",味方の必殺ワザ発動までの時間を短縮できるぞ!さらに、広範囲の必殺ワザで、複数の相手の攻撃をDOWNできるテクニカルキャラ!
e,202512020,chara_osh_00101_ja,chara_osh_00101,ja,"復讐を誓う片星 星野 アクア","苺プロダクションに所属する俳優で、陽東高校一般科1年生。アクアは通称で本名は星野 愛久愛海(あくあまりん)。子供時代に芸能デビューし、一時休止を挟んで現在は復帰している。母親であるアイを大切に思い、クールな性格で勉強もできるが、復讐のためには冷酷になる一面も持つ。",選択したコマと一つ先までのコマにいる敵にダメージを与えるぞ!さらに味方の再召喚時間を短縮!
e,202512020,chara_osh_00201_ja,chara_osh_00201,ja,"星野 ルビー","苺プロダクション所属のアイドルで、再始動した新生B小町のメンバー。陽東高校芸能科1年生。ルビーは通称で本名は星野 瑠美衣(るびい)。明るい性格で、母親であるアイのような輝くアイドルになることを夢に見続け、何度もオーディションを受け続けていた。",必殺ワザで自身の攻撃をUPするアタックキャラ!
e,202512020,chara_osh_00301_ja,chara_osh_00301,ja,MEMちょ,"ネットで人気のある、バズらせのプロとして活躍するユーチューバーにしてインフルエンサー。星野 アクアに新生B小町に誘われ、一度は諦めたアイドルの道を歩み出した。B小町の配信やMV撮影、動画編集など、インフルエンサーとしてのスキルや人脈を駆使し、B小町を成功へ導く。",必殺ワザで自身と黄属性の味方のダメージをカットできるぞ!
e,202512020,chara_osh_00401_ja,chara_osh_00401,ja,"有馬 かな","陽東高校芸能科2年生。かつて10秒で泣ける天才子役として\n一世を風靡したが、その後は下火になっていた。芸能界の先輩としてのプライドが高く、子役として共演した星野 アクアのことを意識している。苺プロに加入し、女優からアイドルへ転身、新生B小町のセンターとして活動することとなる。",必殺ワザで複数の相手をスタン状態にすることができるぞ!
e,202512020,chara_osh_00501_ja,chara_osh_00501,ja,"黒川 あかね","高校2年生。劇団ララライに所属する若きエース。分析力が非常に高く、まるで役をその身に降ろすかのように演じる。恋愛リアリティショー「今からガチ恋始めます」へ出演するも、演出に翻弄されSNSで炎上。自殺未遂をしてしまうほど追い詰められるも星野 アクアによって助けられる。",必殺ワザで複数の相手の攻撃をDOWNするテクニカルキャラ!
e,202512020,chara_osh_00601_ja,chara_osh_00601,ja,ぴえヨン,苺プロダクションに所属する年収1億円の覆面筋トレ系ユーチューバー。ひよこを模した丸い覆面に、パンツ一枚の姿をしており、小中学生から人気を集めている。新生B小町を宣伝するためにコラボをすることになり、1時間のぴえヨンブートダンスを決行する。,前線で味方を守るディフェンスキャラ!さらに連続攻撃で強さを発揮!
e,202601010,chara_jig_00401_ja,chara_jig_00401,ja,"賊王 亜左 弔兵衛","“賊王”の呼び名で、伊予の山奥に賊の村をもつ傑士。\n強さこそが全てだと信じ、目的のためには手段を選ばない冷酷さと高い適応能力、賊をまとめ上げる天賦の才能で、圧倒的な戦闘力を誇る。唯一の身内で大切に思っている実の弟の山田浅ェ門 桐馬と共に数々の状況を潜り抜けてきた。",必殺ワザで複数の相手を弱体化させつつ、体力も吸収するテクニカルキャラ!さらに、特定の条件で性能が変化するぞ!
e,202601010,chara_jig_00501_ja,chara_jig_00501,ja,"山田浅ェ門 桐馬","山田浅ェ門・試一刀流、段位未定。\n亜左 弔兵衛の実の弟で監視役。兄を助けるために山田家に入門し、わずか1か月で代行免許を得る天稟を持つ。冷静沈着で知的な一面を持ちながらも、その身を賭してでも兄を守ろうとする強い覚悟を持っている。",前線を支えるサポート!攻撃UPとダメージカットで戦況を有利に導くことができるぞ!
e,202601010,chara_jig_00601_ja,chara_jig_00601,ja,"民谷 巌鉄斎","“八洲無双の剣龍”と呼ばれる剣豪。\n藩主の屋敷にあった門扉の龍を切ったことにより、不敬罪に問われ死罪人となった。天下に轟く偉業を成し、後世に語り継がれる名前を残すことによって真の不老不死になることを史上の目標にしている。",味方を守りつつ、必殺ワザの攻撃でチームの火力に貢献するぞ!
e,202601010,chara_jig_00701_ja,chara_jig_00701,ja,メイ,"天仙の劣等種のために蓬莱から追放された少女。\n見た目に反した超人的な力を発揮する。島で起こっていることを知っていながら、向き合ってこなかった後悔に苛まれており、島を出て普通に暮らすために仙薬を探す画眉丸たちと行動を共にする。",特定のコマにいる味方を回復!
e,202602010,chara_you_00001_ja,chara_you_00001,ja,"元殺し屋の新人教諭 リタ","“世界一安全な幼稚園”と言われる「ブラック幼稚園」たんぽぽ組の新人教諭で、元殺し屋。\n一見普通の教員だが、かつては伝説の殺し屋として恐れられていた。抜群の戦闘能力と危機察知能力で幼稚園に日々襲来する殺し屋たちから園児を守っている。夢はイケメンの彼氏をつくること。",必殺ワザで連続攻撃する中距離アタックキャラ!緑属性の相手に対して大ダメージを与えることもできるぞ!
e,202602010,chara_you_00101_ja,chara_you_00101,ja,ルーク,"“世界一安全な幼稚園”と言われる「ブラック幼稚園」きく組の教諭で、元警官。\n少女まんがが好きで、恋愛において最も楽しいのは片想いの時間と考えている。幼稚園内で両想いフラグが成立しそうになるたびに絶妙なタイミングで恋の進展を邪魔しに現れる。",必殺ワザで複数体の相手にダメージを与え、相手を毒状態にするテクニカルキャラ!
e,202602010,chara_you_00201_ja,chara_you_00201,ja,ダグ,"“世界一安全な幼稚園”と言われる「ブラック幼稚園」たんぽぽ組の教諭で、リタの先輩。\n詐欺師として、誰も信用せず、独りで生きてきたが、リタに命を救われ恋に落ちる。鋭い勘と優しい性格を併せ持ち、園児たちにも好かれている。",必殺ワザの攻撃DOWNを駆使して戦うテクニカルキャラ!
e,202602010,chara_you_00301_ja,chara_you_00301,ja,ハナ,"“世界一安全な幼稚園”と言われる「ブラック幼稚園」たんぽぽ組の新人教諭。\nリタの後輩で、爆弾を使用して戦う。殺し屋一族・ブラッドリー一家の末娘。殺し屋としてバディを組んでいた兄を助けて欲しいとリタ達に依頼する。",必殺ワザで赤属性の相手に大ダメージを与えることができるぞ!
e,202602020,chara_kim_00001_ja,chara_kim_00001,ja,dev,dev,広範囲の必殺ワザで複数の相手にダメージを与え、ノックバックさせることができるぞ!さらに、特定の条件で自身の被ダメージをカットする!
e,202602020,chara_kim_00101_ja,chara_kim_00101,ja,dev,dev,必殺ワザで複数の相手に連続攻撃するぞ!さらに、黄属性の相手には大ダメージを与えることができるぞ!
e,202603010,chara_hut_00001_ja,chara_hut_00001,ja,dev,dev,必殺ワザで複数の相手にダメージを与え、ノックバックさせることができるぞ!さらに、特定の条件で自身の被ダメージをカットする!
```

---

<!-- FILE: ./projects/glow-masterdata/OprGacha.csv -->
## ./projects/glow-masterdata/OprGacha.csv

```csv
ENABLE,id,gacha_type,upper_group,enable_ad_play,enable_add_ad_play_upper,ad_play_interval_time,multi_draw_count,multi_fixed_prize_count,daily_play_limit_count,total_play_limit_count,daily_ad_limit_count,total_ad_limit_count,prize_group_id,fixed_prize_group_id,appearance_condition,unlock_condition_type,unlock_duration_hours,start_at,end_at,display_information_id,dev-qa_display_information_id,display_gacha_caution_id,gacha_priority,release_key
e,Tutorial_001,Tutorial,,,,__NULL__,10,1,__NULL__,__NULL__,0,__NULL__,Tutorial_001,fixd_Tutorial_001,Always,None,__NULL__,"2025-04-01 04:00:00","2038-01-01 00:00:00",,,,100,202509010
e,Special_001,Premium,Special_001,1,1,__NULL__,10,1,__NULL__,__NULL__,1,__NULL__,Special_001,fixd_Special_001,Always,None,__NULL__,"2025-04-01 04:00:00","2038-01-01 00:00:00",,,93ce5e4c-6cd3-41e0-bb23-0f49b8e0dcce,3,202509010
e,Pickup_kai_001,Pickup,Pickup_kai_001,,,__NULL__,10,1,__NULL__,__NULL__,0,__NULL__,Pickup_kai_001,fixd_Pickup_kai_001,Always,None,__NULL__,"2025-09-24 14:00:00","2025-10-22 11:59:59",9ea21da9-3bfe-4ecd-8f90-df4f446c23af,9ea21da9-3bfe-4ecd-8f90-df4f446c23af,1796f733-8ee6-41b4-af4e-44c2118e50e4,50,202509010
e,Premiummedal_001,Medal,Premiummedal_001,,,__NULL__,10,0,__NULL__,__NULL__,0,__NULL__,Premiummedal_001,__NULL__,Always,None,__NULL__,"2025-04-01 04:00:00","2038-01-01 00:00:00",,,,1,202509010
e,UR_10Ticket_001,Ticket,,,,__NULL__,10,1,__NULL__,__NULL__,0,__NULL__,UR_10Ticket_001,fixd_UR_10Ticket_001,HasTicket,None,__NULL__,"2025-09-24 14:00:00","2038-01-01 00:00:00",eb55ce65-d9a3-4f43-8656-e0c70cd20f92,eb55ce65-d9a3-4f43-8656-e0c70cd20f92,9a54609a-1bb7-4b9f-85b0-57974c950290,10,202509010
e,StartDash_001,PaidOnly,,,,__NULL__,10,1,__NULL__,10,0,__NULL__,Special_001,fixd_UR_10Ticket_001,Always,MainPartTutorialComplete,72,"2025-04-01 04:00:00","2038-01-01 00:00:00",7276eb05-9f9c-4615-83c4-05908e8dc1d5,7276eb05-9f9c-4615-83c4-05908e8dc1d5,dd2c0a61-3be3-478c-bfe6-481c54dd6834,100,202509010
e,StartDash_Ticket_001,Ticket,,,,__NULL__,10,1,__NULL__,__NULL__,0,__NULL__,Special_001,fixd_UR_10Ticket_001,HasTicket,None,__NULL__,"2025-04-01 04:00:00","2038-01-01 00:00:00",9eabd624-11c6-4245-9198-947037023c5a,9eabd624-11c6-4245-9198-947037023c5a,dd2c0a61-3be3-478c-bfe6-481c54dd6834,100,202509010
e,Pickup_spy_001,Pickup,Pickup_spy_001,,,__NULL__,10,1,__NULL__,__NULL__,0,__NULL__,Pickup_spy_001,fixd_Pickup_spy_001,Always,None,__NULL__,"2025-10-06 15:00:00","2025-11-06 14:59:59",61a2d189-64ef-4df9-89fa-390b80f6e9d4,61a2d189-64ef-4df9-89fa-390b80f6e9d4,df73d632-45e0-41e0-8a68-8473d987c3eb,52,202510010
e,Pickup_spy_002,Pickup,Pickup_spy_002,,,__NULL__,10,1,__NULL__,__NULL__,0,__NULL__,Pickup_spy_002,fixd_Pickup_spy_002,Always,None,__NULL__,"2025-10-06 15:00:00","2025-11-06 14:59:59",97d07eef-63ea-4262-a249-7816e7c4a64f,97d07eef-63ea-4262-a249-7816e7c4a64f,c134dedb-6ee5-4fda-932c-8646a46cd694,51,202510010
e,Pickup_mag_001,Pickup,Pickup_mag_001,,,__NULL__,10,1,__NULL__,__NULL__,0,__NULL__,Pickup_mag_001,fixd_Pickup_mag_001,Always,None,__NULL__,"2025-11-06 15:00:00","2025-12-08 10:59:59",10c6a187-bd25-4172-bd1d-4c5865f7bdd7,10c6a187-bd25-4172-bd1d-4c5865f7bdd7,ac27c4fd-d14f-4005-b82d-ee73692c0efc,56,202511010
e,Pickup_mag_002,Pickup,Pickup_mag_002,,,__NULL__,10,1,__NULL__,__NULL__,0,__NULL__,Pickup_mag_002,fixd_Pickup_mag_002,Always,None,__NULL__,"2025-11-06 15:00:00","2025-12-08 10:59:59",73716732-e550-4e9a-aaa2-81bb42562b79,73716732-e550-4e9a-aaa2-81bb42562b79,2e1c1a70-b50f-4c08-a930-ec6af07c5b2d,55,202511010
e,Pickup_dan_001,Pickup,Pickup_dan_001,,,__NULL__,10,1,__NULL__,__NULL__,0,__NULL__,Pickup_dan_001,fixd_Pickup_dan_001,Always,None,__NULL__,"2025-10-22 15:00:00","2025-11-25 14:59:59",f8ec5595-1270-4c9e-aa6d-e9de74e0c787,f8ec5595-1270-4c9e-aa6d-e9de74e0c787,e4299f65-1e1e-4cfb-8ba3-3cfc98c1c5f0,54,202510020
e,Pickup_dan_002,Pickup,Pickup_dan_002,,,__NULL__,10,1,__NULL__,__NULL__,0,__NULL__,Pickup_dan_002,fixd_Pickup_dan_002,Always,None,__NULL__,"2025-10-22 15:00:00","2025-11-25 14:59:59",8f3b6cc1-973e-4494-89fc-5cc5860e6a74,8f3b6cc1-973e-4494-89fc-5cc5860e6a74,d325c14b-9978-4670-8bd6-b4d62b0576e2,53,202510020
e,Pickup_yuw_001,Pickup,Pickup_yuw_001,,,__NULL__,10,1,__NULL__,__NULL__,0,__NULL__,Pickup_yuw_001,fixd_Pickup_yuw_001,Always,None,__NULL__,"2025-11-25 15:00:00","2025-12-31 23:59:59",122f9d35-8c11-4a18-97ad-8f6448c2c070,122f9d35-8c11-4a18-97ad-8f6448c2c070,c7f62f9f-c63e-4b64-b74b-7d8fb2dcd304,58,202511020
e,Pickup_yuw_002,Pickup,Pickup_yuw_002,,,__NULL__,10,1,__NULL__,__NULL__,0,__NULL__,Pickup_yuw_002,fixd_Pickup_yuw_002,Always,None,__NULL__,"2025-11-25 15:00:00","2025-12-31 23:59:59",70c2276f-c745-4d5e-9790-5873e3db47a2,70c2276f-c745-4d5e-9790-5873e3db47a2,0888b9ec-b7a7-4d42-9173-4c1b1fd2a71c,57,202511020
e,Pickup_sur_001,Pickup,Pickup_sur_001,,,__NULL__,10,1,__NULL__,__NULL__,0,__NULL__,Pickup_sur_001,fixd_Pickup_sur_001,Always,None,__NULL__,"2025-12-08 12:00:00","2026-01-16 10:59:59",3480dc7a-263c-4f0f-a095-d6b7df43c464,3480dc7a-263c-4f0f-a095-d6b7df43c464,34c4134a-0cf8-4d5b-845b-6e79d3ecbb9b,60,202512010
e,Pickup_sur_002,Pickup,Pickup_sur_002,,,__NULL__,10,1,__NULL__,__NULL__,0,__NULL__,Pickup_sur_002,fixd_Pickup_sur_002,Always,None,__NULL__,"2025-12-08 12:00:00","2026-01-16 10:59:59",5e4a04f0-5f8d-4862-8a24-83224aaf9034,5e4a04f0-5f8d-4862-8a24-83224aaf9034,7703619e-c5c8-4ba7-bac8-cf9d83f48c5e,59,202512010
e,Fest_Xmas_001,Festival,Fest_Xmas_001,,,__NULL__,10,1,__NULL__,__NULL__,0,__NULL__,Fest_Xmas_001,fixd_Fest_Xmas_001,Always,None,__NULL__,"2025-12-22 12:00:00","2026-1-16 10:59:59",49b769a8-7c35-4b6f-adab-f735dbd7d414,49b769a8-7c35-4b6f-adab-f735dbd7d414,d2abc57a-0f40-4c50-8712-a4cf26f74abe,200,202512015
e,Fest_osh_001,Festival,Fest_osh_001,,,__NULL__,10,1,__NULL__,__NULL__,0,__NULL__,Fest_osh_001,fixd_Fest_osh_001,Always,None,__NULL__,"2026-01-01 00:00:00","2026-02-02 10:59:59",bf27b739-f73b-436e-b390-b49de978ecff,bf27b739-f73b-436e-b390-b49de978ecff,6573bfaf-84a7-408b-856d-00982d19a5f1,200,202512020
e,Pickup_osh_001,Pickup,Pickup_osh_001,,,__NULL__,10,1,__NULL__,__NULL__,0,__NULL__,Pickup_osh_001,fixd_Pickup_osh_001,Always,None,__NULL__,"2026-01-01 00:00:00","2026-02-02 10:59:59",88fd0eb1-cf57-4965-b895-fe4333131605,88fd0eb1-cf57-4965-b895-fe4333131605,cc5775e1-c6d2-41a2-957c-a4d518612ffd,61,202512020
e,UR_newyear_001,PaidOnly,,,,__NULL__,10,1,__NULL__,10,0,__NULL__,UR_newyear_001,fixd_UR_newyear_001,Always,None,__NULL__,"2026-01-01 00:00:00","2026-02-02 10:59:59",4b3bb1a9-b8ca-49db-aa8b-bea35f980b0d,4b3bb1a9-b8ca-49db-aa8b-bea35f980b0d,7034f47b-7760-4817-a186-a3dabbfec499,100,202512020
e,UR_newyear_Ticket_001,Ticket,,,,__NULL__,10,1,__NULL__,__NULL__,0,__NULL__,UR_newyear_001,fixd_UR_newyear_001,HasTicket,None,__NULL__,"2026-01-01 00:00:00","2038-01-01 00:00:00",,,7034f47b-7760-4817-a186-a3dabbfec499,100,202512020
e,gasho_001,Medal,gasho_001,,,__NULL__,10,0,__NULL__,__NULL__,0,__NULL__,gasho_001,__NULL__,HasTicket,None,__NULL__,"2026-01-01 00:00:00","2038-01-01 00:00:00",aafa6881-9b13-44f5-b002-4a40a732313a,aafa6881-9b13-44f5-b002-4a40a732313a,f9d2ac5f-a5ca-491e-970e-27aee0cb84bc,64,202512020
e,SSRticket_osh_001,Ticket,SSRticket_osh_001,,,__NULL__,10,0,__NULL__,__NULL__,0,__NULL__,SSRticket_osh_001,__NULL__,HasTicket,None,__NULL__,"2026-01-01 00:00:00","2038-01-01 00:00:00",0f759878-517e-48f8-b9be-bf4ce4bbd8d6,0f759878-517e-48f8-b9be-bf4ce4bbd8d6,30fa81a0-ac08-4cbd-92ec-1620bfc9ea43,62,202512020
e,Pickup_jig_001,Pickup,Pickup_jig_001,,,__NULL__,10,1,__NULL__,__NULL__,0,__NULL__,Pickup_jig_001,fixd_Pickup_jig_001,Always,None,__NULL__,"2026-01-16 12:00:00","2026-02-16 10:59:59",84b93bca-1b92-42df-9d6e-3a593fa76a69,84b93bca-1b92-42df-9d6e-3a593fa76a69,16d9cd62-8b4a-44c5-922a-6a6b7889ce06,66,202601010
e,Pickup_jig_002,Pickup,Pickup_jig_002,,,__NULL__,10,1,__NULL__,__NULL__,0,__NULL__,Pickup_jig_002,fixd_Pickup_jig_002,Always,None,__NULL__,"2026-01-16 12:00:00","2026-02-16 10:59:59",1c1d7df8-a984-4043-a38d-4463932ba6f7,1c1d7df8-a984-4043-a38d-4463932ba6f7,37543db3-0f5c-4128-993e-883a723f0232,65,202601010
e,Pickup_you_001,Pickup,Pickup_you_001,,,__NULL__,10,1,__NULL__,__NULL__,0,__NULL__,Pickup_you_001,fixd_Pickup_you_001,Always,None,__NULL__,"2026-02-02 15:00:00","2026-03-02 10:59:59",,,c91097a2-58c3-47bc-b8f1-ff214597bf56,66,202602010
e,Pickup_Valentine_001,Pickup,Pickup_Valentine_001,,,__NULL__,10,1,__NULL__,__NULL__,0,__NULL__,Pickup_Valentine_001,fixd_Pickup_Valentine_001,Always,None,__NULL__,"2026-02-10 15:00:00","2026-03-02 10:59:59",,,a6b95b16-9cd2-4f5d-96b3-da97eb257251,67,202602010
```

---

<!-- FILE: ./projects/glow-masterdata/OprGachaI18n.csv -->
## ./projects/glow-masterdata/OprGachaI18n.csv

```csv
ENABLE,release_key,id,opr_gacha_id,language,name,description,max_rarity_upper_description,pickup_upper_description,fixed_prize_description,banner_url,logo_asset_key,logo_banner_url,gacha_background_color,gacha_banner_size
e,202509010,Tutorial_001_ja,Tutorial_001,ja,チュートリアルガシャ,"引き直し可能！！\nUR1体とSSR以下9体が出現する10連ガシャ！",,,UR1体確定,tutorial_00001,tutorial_00001,,Yellow,SizeL
e,202509010,Special_001_ja,Special_001,ja,スペシャルガシャ,少年ジャンプ＋のキャラが大集合!,URキャラ1体確定！,,SR以上1体確定,special_00001,special_00001,,Blue,SizeL
e,202509010,Pickup_kai_001_ja,Pickup_kai_001,ja,"怪獣８号 いいジャン祭ピックアップガシャ","「亜白 ミナ」の出現率UP中!",,ピックアップURキャラ1体確定!,SR以上1体確定,kai_00001,pickup_00001,,Yellow,SizeL
e,202509010,Premiummedal_001_ja,Premiummedal_001,ja,プレミアムメダルガシャ,"プレミアムメダル限定キャラと\n強化アイテムをGET!",,,,medal_00001,medal_00001,,Yellow,SizeL
e,202509010,UR_10Ticket_001_ja,UR_10Ticket_001,ja,UR1体確定10連ガシャ,URキャラ1体確定!,URキャラ1体確定,,UR1体確定,UR_Ticket_00001,ur_10_00001,,Blue,SizeL
e,202509010,StartDash_001_ja,StartDash_001,ja,72時間限定スタートダッシュガシャ,"有償プリズム1,500個で引ける！\nURキャラ1体確定！",URキャラ1体確定,,UR1体確定,startdash_00001,startdash_00001,,Purple,SizeL
e,202509010,StartDash_Ticket_001_ja,StartDash_Ticket_001,ja,72時間限定スタートダッシュガシャ,URキャラ1体確定!,URキャラ1体確定,,UR1体確定,startdash_00001,startdash_00001,,Purple,SizeL
e,202510010,Pickup_spy_001_ja,Pickup_spy_001,ja,"SPY×FAMILY いいジャン祭ピックアップガシャ A","「姉を想う盲愛 ユーリ・ブライア」の出現率UP中!",,ピックアップURキャラ1体確定!,SR以上1体確定,spy_00001,pickup_a_00001,,Yellow,SizeL
e,202510010,Pickup_spy_002_ja,Pickup_spy_002,ja,"SPY×FAMILY いいジャン祭ピックアップガシャ B","「<黄昏> ロイド」と「<いばら姫>ヨル」\nと「わくわく アーニャ」の出現率UP中!",,ピックアップURキャラ1体確定!,SR以上1体確定,spy_00002,pickup_b_00001,,Yellow,SizeL
e,202511010,Pickup_mag_001_ja,Pickup_mag_001,ja,"株式会社マジルミエ いいジャン祭ピックアップガシャ A","「絶対効率の体現者 土刃 メイ」と\n「葵 リリー」の出現率UP中!",,ピックアップURキャラ1体確定!,SR以上1体確定,mag_00001,pickup_a_00001,,Yellow,SizeL
e,202511010,Pickup_mag_002_ja,Pickup_mag_002,ja,"株式会社マジルミエ いいジャン祭ピックアップガシャ B","「新人魔法少女 桜木 カナ」と\n「葵 リリー」の出現率UP中!",,ピックアップURキャラ1体確定!,SR以上1体確定,mag_00002,pickup_b_00001,,Yellow,SizeL
e,202510020,Pickup_dan_001_ja,Pickup_dan_001,ja,"ダンダダン いいジャン祭ピックアップガシャ A","「アクさらの愛 アイラ」と\n「モモ」の出現率UP中!",,ピックアップURキャラ1体確定!,SR以上1体確定,dan_00001,pickup_a_00001,,Yellow,SizeL
e,202510020,Pickup_dan_002_ja,Pickup_dan_002,ja,"ダンダダン いいジャン祭ピックアップガシャ B","「ターボババアの霊力 オカルン」と\n「モモ」の出現率UP中!",,ピックアップURキャラ1体確定!,SR以上1体確定,dan_00002,pickup_b_00001,,Yellow,SizeL
e,202511020,Pickup_yuw_001_ja,Pickup_yuw_001,ja,"2.5次元の誘惑 いいジャン祭ピックアップガシャ A","2.5次元の誘惑から新URキャラ2体と\n新SSRキャラ1体の出現率UP中!",,ピックアップURキャラ1体確定!,SR以上1体確定,yuw_00001,pickup_a_00001,,Yellow,SizeL
e,202511020,Pickup_yuw_002_ja,Pickup_yuw_002,ja,"2.5次元の誘惑 いいジャン祭ピックアップガシャ B","2.5次元の誘惑からURキャラ2体と\n新SSRキャラ1体の出現率UP中!",,ピックアップURキャラ1体確定!,SR以上1体確定,yuw_00002,pickup_b_00001,,Yellow,SizeL
e,202512010,Pickup_sur_001_ja,Pickup_sur_001,ja,"魔都精兵のスレイブ いいジャン祭ピックアップガシャ A","「空間を操る六番組組長 出雲 天花」と\n「東 八千穂」の出現率UP中!",,ピックアップURキャラ1体確定!,SR以上1体確定,sur_00001,pickup_a_00001,,Yellow,SizeL
e,202512010,Pickup_sur_002_ja,Pickup_sur_002,ja,"魔都精兵のスレイブ いいジャン祭ピックアップガシャ B","「誇り高き魔都の剣姫 羽前 京香」と\n「東 八千穂」の出現率UP中!",,ピックアップURキャラ1体確定!,SR以上1体確定,sur_00002,pickup_b_00001,,Yellow,SizeL
e,202512015,Fest_Xmas_001_ja,Fest_Xmas_001,ja,クリスマスDXフェスガシャ,"「愛届ける聖夜のサンタ 橘 美花莉」\nの出現率UP中!",,ピックアップURキャラ1体確定!,SSR以上1体確定,glo_00001,fes_00001,,Yellow,SizeL
e,202512020,Fest_osh_001_ja,Fest_osh_001,ja,正月DXフェスガシャ,"「B小町不動のセンター アイ」の出現率UP中!",,ピックアップURキャラ1体確定!,SSR以上1体確定,glo_00002,fes_00001,,Yellow,SizeL
e,202512020,Pickup_osh_001_ja,Pickup_osh_001,ja,"【推しの子】 いいジャン祭ピックアップガシャ","【推しの子】から新URキャラ1体と\n新SSRキャラ4体の出現率UP中!",,ピックアップURキャラ1体確定!,SR以上1体確定,osh_00001,pickup_00001,,Yellow,SizeL
e,202512020,UR_newyear_001_ja,UR_newyear_001,ja,2026年正月記念！UR1体確定ガシャ,"有償プリズム1,500個で引ける！\nURキャラ1体確定！",URキャラ1体確定,,UR1体確定,glo_00003,,,Purple,SizeL
e,202512020,UR_newyear_Ticket_001_ja,UR_newyear_Ticket_001,ja,2026年正月記念！UR1体確定ガシャ,URキャラ1体確定！,URキャラ1体確定,,UR1体確定,glo_00003,,,Purple,SizeL
e,202512020,gasho_001_ja,gasho_001,ja,賀正ガシャ2026,賀正ガシャチケット引ける!,,,,glo_00005,,,Yellow,SizeL
e,202512020,SSRticket_osh_001_ja,SSRticket_osh_001,ja,【推しの子】SSR確定チケットガシャ,【推しの子】作品のSSRキャラのみ出現！,,SSR確定！,,osh_00002,,,Yellow,SizeL
e,202601010,Pickup_jig_001_ja,Pickup_jig_001,ja,"地獄楽 いいジャン祭ピックアップガシャ A","「賊王 亜左 弔兵衛」と\n「山田浅ェ門 桐馬」の出現率UP中!",,ピックアップURキャラ1体確定!,SR以上1体確定,jig_00001,pickup_a_00001,,Yellow,SizeL
e,202601010,Pickup_jig_002_ja,Pickup_jig_002,ja,"地獄楽 いいジャン祭ピックアップガシャ B","「がらんの画眉丸」と\n「山田浅ェ門 桐馬」の出現率UP中!",,ピックアップURキャラ1体確定!,SR以上1体確定,jig_00002,pickup_b_00001,,Yellow,SizeL
e,202602010,Pickup_you_001_ja,Pickup_you_001,ja,"幼稚園WARS いいジャン祭ピックアップガシャ","「元殺し屋の新人教諭 リタ」と\n「ルーク」の出現率UP中!",,ピックアップURキャラ1体確定!,SR以上1体確定,you_00001,pickup_00001,,Yellow,SizeL
e,202602010,Pickup_Valentine_001_ja,Pickup_Valentine_001,ja,バレンタインガシャ,"バレンタインガシャチケットでも引ける!\n女の子のみ登場ガシャ!",,ピックアップURキャラ1体確定!,SSR以上1体確定,glo_00006,,,Yellow,SizeL
```

---

<!-- FILE: ./projects/glow-masterdata/OprProduct.csv -->
## ./projects/glow-masterdata/OprProduct.csv

```csv
ENABLE,id,mst_store_product_id,product_type,purchasable_count,paid_amount,display_priority,start_date,end_date,release_key
e,1,1,Diamond,__NULL__,80,6,"2024-09-22 12:00:00","2034-01-01 00:00:00",202509010
e,2,2,Diamond,__NULL__,240,5,"2024-09-22 12:00:00","2034-01-01 00:00:00",202509010
e,3,3,Diamond,__NULL__,510,4,"2024-09-22 12:00:00","2034-01-01 00:00:00",202509010
e,4,4,Diamond,__NULL__,1560,3,"2024-09-22 12:00:00","2034-01-01 00:00:00",202509010
e,5,5,Diamond,__NULL__,2600,2,"2024-09-22 12:00:00","2034-01-01 00:00:00",202509010
e,6,6,Diamond,__NULL__,5400,1,"2024-09-22 12:00:00","2034-01-01 00:00:00",202509010
e,7,7,Diamond,3,160,106,"2025-09-24 14:00:00","2025-10-31 11:59:59",202509010
e,8,8,Diamond,3,480,105,"2025-09-24 14:00:00","2025-10-31 11:59:59",202509010
e,9,9,Diamond,1,1020,104,"2025-09-24 14:00:00","2025-10-31 11:59:59",202509010
e,10,10,Diamond,1,3120,103,"2025-09-24 14:00:00","2025-10-31 11:59:59",202509010
e,11,11,Diamond,1,5200,102,"2025-09-24 14:00:00","2025-10-31 11:59:59",202509010
e,12,12,Diamond,1,10800,101,"2025-09-24 14:00:00","2025-10-31 11:59:59",202509010
e,13,13,Pack,1,,20,"2025-09-24 14:00:00","2034-01-01 00:00:00",202509010
e,14,14,Pack,1,,19,"2025-09-24 14:00:00","2034-01-01 00:00:00",202509010
e,15,15,Pack,1,,18,"2025-09-24 14:00:00","2034-01-01 00:00:00",202509010
e,16,16,Pack,1,,17,"2025-09-24 14:00:00","2025-10-06 11:59:59",202509010
e,17,17,Pass,__NULL__,,1,"2025-09-22 11:00:00","2034-01-01 00:00:00",202509010
e,18,18,Pack,__NULL__,,16,"2025-09-22 11:00:00","2034-01-01 00:00:00",202509010
e,19,19,Pack,1,,17,"2025-10-06 15:00:00","2025-10-22 14:59:59",202510010
e,20,20,Pack,1,,17,"2025-10-22 15:00:00","2025-11-06 14:59:59",202510020
e,21,21,Pack,1,,17,"2025-11-06 15:00:00","2025-11-25 14:59:59",202511010
e,22,22,Pack,1,,17,"2025-11-25 15:00:00","2025-12-08 10:59:59",202511020
e,23,23,Diamond,1,2000,102,"2025-11-21 15:00:00","2025-12-01 23:59:59",202511020
e,24,24,Diamond,1,7500,101,"2025-11-21 15:00:00","2025-12-01 23:59:59",202511020
e,25,25,Pack,1,,23,"2025-11-21 15:00:00","2025-12-01 23:59:59",202511020
e,26,26,Pack,1,,22,"2025-11-21 15:00:00","2025-12-01 23:59:59",202511020
e,27,27,Pack,1,,21,"2025-11-21 15:00:00","2025-12-01 23:59:59",202511020
e,28,28,Pack,1,,23,"2025-12-22 15:00:00","2025-12-31 23:59:59",202512015
e,29,29,Pack,1,,22,"2025-12-22 15:00:00","2025-12-31 23:59:59",202512015
e,30,30,Pack,1,,21,"2025-12-22 15:00:00","2025-12-31 23:59:59",202512015
e,31,31,Diamond,1,520,104,"2025-12-22 15:00:00","2025-12-31 23:59:59",202512015
e,32,32,Diamond,1,1680,103,"2025-12-22 15:00:00","2025-12-31 23:59:59",202512015
e,33,33,Diamond,1,2820,102,"2025-12-22 15:00:00","2025-12-31 23:59:59",202512015
e,34,34,Diamond,1,5580,101,"2025-12-22 15:00:00","2025-12-31 23:59:59",202512015
e,35,35,Pack,1,,18,"2025-12-08 15:00:00","2025-12-31 23:59:59",202512010
e,36,36,Pack,1,,17,"2025-12-08 15:00:00","2025-12-31 23:59:59",202512010
e,37,37,Pack,1,,18,"2026-01-01 00:00:00","2026-01-16 10:59:59",202512020
e,38,38,Diamond,1,320,106,"2026-01-01 00:00:00","2026-01-15 10:59:59",202512020
e,39,39,Diamond,1,1680,105,"2026-01-01 00:00:00","2026-01-15 10:59:59",202512020
e,40,40,Diamond,1,3220,104,"2026-01-01 00:00:00","2026-01-15 10:59:59",202512020
e,41,41,Diamond,1,5280,103,"2026-01-01 00:00:00","2026-01-15 10:59:59",202512020
e,42,42,Diamond,1,7500,102,"2026-01-01 00:00:00","2026-01-15 10:59:59",202512020
e,43,43,Diamond,1,12000,101,"2026-01-01 00:00:00","2026-01-15 10:59:59",202512020
e,44,44,Pack,1,,24,"2026-01-01 00:00:00","2026-01-15 10:59:59",202512020
e,45,45,Pack,2,,23,"2026-01-01 00:00:00","2026-01-15 10:59:59",202512020
e,46,46,Pack,1,,22,"2026-01-01 00:00:00","2026-01-15 10:59:59",202512020
e,47,47,Pack,1,,21,"2026-01-01 00:00:00","2026-01-15 10:59:59",202512020
e,48,48,Pass,1,,23,"2026-01-01 00:00:00","2026-01-15 03:59:59",202512020
e,49,49,Pack,1,,0,"2025-12-10 15:00:00","2026-01-08 23:59:59",202512015
e,50,50,Pack,1,,18,"2026-01-16 15:00:00","2026-02-02 10:59:59",202601010
e,51,51,Pack,1,,17,"2026-01-01 00:00:00","2026-02-02 10:59:59",202512020
e,52,52,Pack,1,,17,"2026-02-02 15:00:00","2026-02-28 23:59:59",202601010
e,53,53,Pack,1,,18,"2026-02-02 12:00:00","2026-02-16 10:59:59",202602010
e,54,54,Pack,1,,30,"2026-02-10 15:00:00","2026-03-02 10:59:59",202602010
e,55,55,Pack,1,,29,"2026-02-10 15:00:00","2026-03-02 10:59:59",202602010
e,56,56,Pack,1,,28,"2026-02-10 15:00:00","2026-03-02 10:59:59",202602010
e,57,57,Pack,1,,27,"2026-02-10 15:00:00","2026-03-02 10:59:59",202602010
e,58,58,Pack,1,,26,"2026-02-10 15:00:00","2026-03-02 10:59:59",202602010
e,59,59,Pack,1,,25,"2026-02-10 15:00:00","2026-03-02 10:59:59",202602010
e,60,60,Pack,1,,24,"2026-02-10 15:00:00","2026-03-02 10:59:59",202602010
e,61,61,Pack,1,,23,"2026-02-02 15:00:00","2026-03-02 10:59:59",202602010
e,62,62,Pack,1,,22,"2026-02-02 15:00:00","2026-03-02 10:59:59",202602010
e,63,63,Pack,1,,21,"2026-02-09 15:00:00","2026-02-16 10:59:59",202602010
e,64,64,Diamond,1,300,106,"2026-01-26 15:00:00","2026-02-02 10:59:59",202601010
e,65,65,Diamond,1,620,105,"2026-01-26 15:00:00","2026-02-02 10:59:59",202601010
e,66,66,Diamond,1,1580,104,"2026-01-26 15:00:00","2026-02-02 10:59:59",202601010
e,67,67,Diamond,1,3200,103,"2026-01-26 15:00:00","2026-02-02 10:59:59",202601010
e,68,68,Diamond,1,6300,102,"2026-01-26 15:00:00","2026-02-02 10:59:59",202601010
e,69,69,Pack,1,,18,"2026-02-16 15:00:00","2026-03-02 10:59:59",202602020
e,70,70,Pack,1,,17,"2026-03-02 15:00:00","2026-03-31 10:59:59",202602020
e,71,71,Pack,1,,30,"2026-03-10 15:00:00","2026-03-31 10:59:59",202602020
e,72,72,Pack,1,,29,"2026-03-10 15:00:00","2026-03-31 10:59:59",202602020
e,73,73,Pack,1,,28,"2026-03-10 15:00:00","2026-03-31 10:59:59",202602020
e,74,74,Pack,1,,27,"2026-03-10 15:00:00","2026-03-31 10:59:59",202602020
e,75,75,Pack,1,,26,"2026-03-10 15:00:00","2026-03-31 10:59:59",202602020
e,76,76,Pack,1,,25,"2026-03-02 15:00:00","2026-03-31 10:59:59",202602020
e,77,77,Pack,1,,24,"2026-03-02 15:00:00","2026-03-31 10:59:59",202602020
e,78,78,Pack,1,,23,"2026-02-20 15:00:00","2026-02-27 10:59:59",202602020
```

---

<!-- FILE: ./projects/glow-masterdata/OprProductI18n.csv -->
## ./projects/glow-masterdata/OprProductI18n.csv

```csv
ENABLE,release_key,id,opr_product_id,language,asset_key
e,202509010,1_ja,1,ja,
e,202509010,2_ja,2,ja,
e,202509010,3_ja,3,ja,
e,202509010,4_ja,4,ja,
e,202509010,5_ja,5,ja,
e,202509010,6_ja,6,ja,
e,202509010,7_ja,7,ja,increase_prism_00001
e,202509010,8_ja,8,ja,increase_prism_00002
e,202509010,9_ja,9,ja,increase_prism_00003
e,202509010,10_ja,10,ja,increase_prism_00004
e,202509010,11_ja,11,ja,increase_prism_00005
e,202509010,12_ja,12,ja,increase_prism_00006
e,202509010,13_ja,13,ja,
e,202509010,14_ja,14,ja,
e,202509010,15_ja,15,ja,
e,202509010,16_ja,16,ja,
e,202509010,17_ja,17,ja,
e,202509010,18_ja,18,ja,
e,202510010,19_ja,19,ja,
e,202510020,20_ja,20,ja,
e,202511010,21_ja,21,ja,
e,202511020,22_ja,22,ja,
e,202511020,23_ja,23,ja,increase_prism_00004
e,202511020,24_ja,24,ja,increase_prism_00006
e,202511020,25_ja,25,ja,
e,202511020,26_ja,26,ja,
e,202511020,27_ja,27,ja,
e,202512015,28_ja,28,ja,
e,202512015,29_ja,29,ja,
e,202512015,30_ja,30,ja,
e,202512015,31_ja,31,ja,increase_prism_00003
e,202512015,32_ja,32,ja,increase_prism_00004
e,202512015,33_ja,33,ja,increase_prism_00005
e,202512015,34_ja,34,ja,increase_prism_00005
e,202512010,35_ja,35,ja,
e,202512010,36_ja,36,ja,
e,202512020,37_ja,37,ja,
e,202512020,38_ja,38,ja,increase_prism_00002
e,202512020,39_ja,39,ja,increase_prism_00004
e,202512020,40_ja,40,ja,increase_prism_00005
e,202512020,41_ja,41,ja,increase_prism_00005
e,202512020,42_ja,42,ja,increase_prism_00006
e,202512020,43_ja,43,ja,increase_prism_00006
e,202512020,44_ja,44,ja,
e,202512020,45_ja,45,ja,
e,202512020,46_ja,46,ja,
e,202512020,47_ja,47,ja,
e,202512020,48_ja,48,ja,
e,202512015,49_ja,49,ja,
e,202601010,50_ja,50,ja,
e,202512020,51_ja,51,ja,
e,202601010,52_ja,52,ja,
e,202602010,53_ja,53,ja,
e,202602010,54_ja,54,ja,
e,202602010,55_ja,55,ja,
e,202602010,56_ja,56,ja,
e,202602010,57_ja,57,ja,
e,202602010,58_ja,58,ja,
e,202602010,59_ja,59,ja,
e,202602010,60_ja,60,ja,
e,202602010,61_ja,61,ja,
e,202602010,62_ja,62,ja,
e,202602010,63_ja,63,ja,
e,202601010,64_ja,64,ja,increase_prism_00002
e,202601010,65_ja,65,ja,increase_prism_00003
e,202601010,66_ja,66,ja,increase_prism_00004
e,202601010,67_ja,67,ja,increase_prism_00005
e,202601010,68_ja,68,ja,increase_prism_00006
e,202602020,69_ja,69,ja,
e,202602020,70_ja,70,ja,
e,202602020,71_ja,71,ja,
e,202602020,72_ja,72,ja,
e,202602020,73_ja,73,ja,
e,202602020,74_ja,74,ja,
e,202602020,75_ja,75,ja,
e,202602020,76_ja,76,ja,
e,202602020,77_ja,77,ja,
e,202602020,78_ja,78,ja,
```

---

<!-- FILE: ./projects/glow-masterdata/sheet_schema/MstAdventBattle.csv -->
## ./projects/glow-masterdata/sheet_schema/MstAdventBattle.csv

```csv
memo
TABLE,MstAdventBattle,MstAdventBattle,MstAdventBattle,MstAdventBattle,MstAdventBattle,MstAdventBattle,MstAdventBattle,MstAdventBattle,MstAdventBattle,MstAdventBattle,MstAdventBattle,MstAdventBattle,MstAdventBattle,MstAdventBattle,MstAdventBattle,MstAdventBattle,MstAdventBattle,MstAdventBattle,MstAdventBattle,MstAdventBattle,MstAdventBattle,MstAdventBattleI18n,MstAdventBattleI18n
ENABLE,id,mst_event_id,mst_in_game_id,asset_key,advent_battle_type,initial_battle_point,score_addition_type,score_additional_coef,score_addition_target_mst_enemy_stage_parameter_id,mst_stage_rule_group_id,event_bonus_group_id,challengeable_count,ad_challengeable_count,display_mst_unit_id1,display_mst_unit_id2,display_mst_unit_id3,exp,coin,start_at,end_at,release_key,name.ja,boss_description.ja
```

---

<!-- FILE: ./projects/glow-masterdata/sheet_schema/MstArtwork.csv -->
## ./projects/glow-masterdata/sheet_schema/MstArtwork.csv

```csv
memo
TABLE,MstArtwork,MstArtwork,MstArtwork,MstArtwork,MstArtwork,MstArtwork,MstArtworkI18n,MstArtworkI18n
ENABLE,id,mst_series_id,outpost_additional_hp,asset_key,sort_order,release_key,name.ja,description.ja
```

---

<!-- FILE: ./projects/glow-masterdata/sheet_schema/MstComebackBonus.csv -->
## ./projects/glow-masterdata/sheet_schema/MstComebackBonus.csv

```csv
memo,,,,,,
TABLE,MstComebackBonus,MstComebackBonus,MstComebackBonus,MstComebackBonus,MstComebackBonus,MstComebackBonus
ENABLE,id,release_key,mst_comeback_bonus_schedule_id,login_day_count,mst_daily_bonus_reward_group_id,sort_order
```

---

<!-- FILE: ./projects/glow-masterdata/sheet_schema/MstDailyBonusReward.csv -->
## ./projects/glow-masterdata/sheet_schema/MstDailyBonusReward.csv

```csv
memo,,,,,,
TABLE,MstDailyBonusReward,MstDailyBonusReward,MstDailyBonusReward,MstDailyBonusReward,MstDailyBonusReward,MstDailyBonusReward
ENABLE,id,release_key,group_id,resource_type,resource_id,resource_amount
```

---

<!-- FILE: ./projects/glow-masterdata/sheet_schema/MstEnemyCharacter.csv -->
## ./projects/glow-masterdata/sheet_schema/MstEnemyCharacter.csv

```csv
memo,,,,,,,,,,フレーバーチェック
TABLE,MstEnemyCharacter,MstEnemyCharacter,MstEnemyCharacter,MstEnemyCharacter,MstEnemyCharacter,MstEnemyCharacter,MstEnemyCharacter,MstEnemyCharacterI18n,MstEnemyCharacterI18n
ENABLE,release_key,id,mst_series_id,asset_key,is_phantomized,is_displayed_encyclopedia,mst_attack_hit_onomatopeia_group_id,name.ja,description.ja
```

---

<!-- FILE: ./projects/glow-masterdata/sheet_schema/MstEvent.csv -->
## ./projects/glow-masterdata/sheet_schema/MstEvent.csv

```csv
memo
TABLE,MstEvent,MstEvent,MstEvent,MstEvent,MstEvent,MstEvent,MstEvent,MstEvent,MstEventI18n,MstEventI18n
ENABLE,id,mst_series_id,is_displayed_series_logo,is_displayed_jump_plus,start_at,end_at,asset_key,release_key,name.ja,balloon.ja
```

---

<!-- FILE: ./projects/glow-masterdata/sheet_schema/MstItem.csv -->
## ./projects/glow-masterdata/sheet_schema/MstItem.csv

```csv
memo
TABLE,MstItem,MstItem,MstItem,MstItem,MstItem,MstItem,MstItem,MstItem,MstItem,MstItem,MstItem,MstItem,MstItemI18n,MstItemI18n
ENABLE,id,type,group_type,rarity,asset_key,effect_value,sort_order,start_date,end_date,release_key,item_type,destination_opr_product_id,name.ja,description.ja
```

---

<!-- FILE: ./projects/glow-masterdata/sheet_schema/MstMissionAchievement.csv -->
## ./projects/glow-masterdata/sheet_schema/MstMissionAchievement.csv

```csv
memo,,,,,,,,,,,,,
TABLE,MstMissionAchievement,MstMissionAchievement,MstMissionAchievement,MstMissionAchievement,MstMissionAchievement,MstMissionAchievement,MstMissionAchievement,MstMissionAchievement,MstMissionAchievement,MstMissionAchievement,MstMissionAchievement,MstMissionAchievement,MstMissionAchievementI18n
ENABLE,id,release_key,criterion_type,criterion_value,criterion_count,unlock_criterion_type,unlock_criterion_value,unlock_criterion_count,group_key,mst_mission_reward_group_id,sort_order,destination_scene,description.ja
```

---

<!-- FILE: ./projects/glow-masterdata/sheet_schema/MstMissionAchievementDependency.csv -->
## ./projects/glow-masterdata/sheet_schema/MstMissionAchievementDependency.csv

```csv
memo,,,,,
TABLE,MstMissionAchievementDependency,MstMissionAchievementDependency,MstMissionAchievementDependency,MstMissionAchievementDependency,MstMissionAchievementDependency
ENABLE,id,release_key,group_id,mst_mission_achievement_id,unlock_order
```

---

<!-- FILE: ./projects/glow-masterdata/sheet_schema/MstMissionBeginner.csv -->
## ./projects/glow-masterdata/sheet_schema/MstMissionBeginner.csv

```csv
memo,,,,,,,,,,,,,
TABLE,MstMissionBeginner,MstMissionBeginner,MstMissionBeginner,MstMissionBeginner,MstMissionBeginner,MstMissionBeginner,MstMissionBeginner,MstMissionBeginner,MstMissionBeginner,MstMissionBeginner,MstMissionBeginner,MstMissionBeginnerI18n,MstMissionBeginnerI18n
ENABLE,id,release_key,criterion_type,criterion_value,criterion_count,unlock_day,group_key,bonus_point,mst_mission_reward_group_id,sort_order,destination_scene,title.ja,description.ja
```

---

<!-- FILE: ./projects/glow-masterdata/sheet_schema/MstMissionDaily.csv -->
## ./projects/glow-masterdata/sheet_schema/MstMissionDaily.csv

```csv
memo,,,,,,,,,,,
TABLE,MstMissionDaily,MstMissionDaily,MstMissionDaily,MstMissionDaily,MstMissionDaily,MstMissionDaily,MstMissionDaily,MstMissionDaily,MstMissionDaily,MstMissionDaily,MstMissionDailyI18n
ENABLE,id,release_key,criterion_type,criterion_value,criterion_count,group_key,bonus_point,mst_mission_reward_group_id,sort_order,destination_scene,description.ja
```

---

<!-- FILE: ./projects/glow-masterdata/sheet_schema/MstMissionDailyBonus.csv -->
## ./projects/glow-masterdata/sheet_schema/MstMissionDailyBonus.csv

```csv
memo,,,,,,
TABLE,MstMissionDailyBonus,MstMissionDailyBonus,MstMissionDailyBonus,MstMissionDailyBonus,MstMissionDailyBonus,MstMissionDailyBonus
ENABLE,id,release_key,mission_daily_bonus_type,login_day_count,mst_mission_reward_group_id,sort_order
```

---

<!-- FILE: ./projects/glow-masterdata/sheet_schema/MstMissionEvent.csv -->
## ./projects/glow-masterdata/sheet_schema/MstMissionEvent.csv

```csv
memo,,,,,,,,,,,,,,
TABLE,MstMissionEvent,MstMissionEvent,MstMissionEvent,MstMissionEvent,MstMissionEvent,MstMissionEvent,MstMissionEvent,MstMissionEvent,MstMissionEvent,MstMissionEvent,MstMissionEvent,MstMissionEvent,MstMissionEvent,MstMissionEventI18n
ENABLE,id,release_key,mst_event_id,criterion_type,criterion_value,criterion_count,unlock_criterion_type,unlock_criterion_value,unlock_criterion_count,group_key,mst_mission_reward_group_id,sort_order,destination_scene,description.ja
```

---

<!-- FILE: ./projects/glow-masterdata/sheet_schema/MstMissionEventDaily.csv -->
## ./projects/glow-masterdata/sheet_schema/MstMissionEventDaily.csv

```csv
memo,,,,,,,,,,,
TABLE,MstMissionEventDaily,MstMissionEventDaily,MstMissionEventDaily,MstMissionEventDaily,MstMissionEventDaily,MstMissionEventDaily,MstMissionEventDaily,MstMissionEventDaily,MstMissionEventDaily,MstMissionEventDaily,MstMissionEventDailyI18n
ENABLE,id,release_key,mst_event_id,criterion_type,criterion_value,criterion_count,group_key,mst_mission_reward_group_id,sort_order,destination_scene,description.ja
```

---

<!-- FILE: ./projects/glow-masterdata/sheet_schema/MstMissionEventDailyBonus.csv -->
## ./projects/glow-masterdata/sheet_schema/MstMissionEventDailyBonus.csv

```csv
memo,,,,,,,
TABLE,MstMissionEventDailyBonus,MstMissionEventDailyBonus,MstMissionEventDailyBonus,MstMissionEventDailyBonus,MstMissionEventDailyBonus,MstMissionEventDailyBonus,MstMissionEventDailyBonus
ENABLE,id,release_key,mst_mission_event_daily_bonus_schedule_id,login_day_count,mst_mission_reward_group_id,sort_order,備考
```

---

<!-- FILE: ./projects/glow-masterdata/sheet_schema/MstMissionEventDailyBonusSchedule.csv -->
## ./projects/glow-masterdata/sheet_schema/MstMissionEventDailyBonusSchedule.csv

```csv
memo
TABLE,MstMissionEventDailyBonusSchedule,MstMissionEventDailyBonusSchedule,MstMissionEventDailyBonusSchedule,MstMissionEventDailyBonusSchedule,MstMissionEventDailyBonusSchedule
ENABLE,id,release_key,mst_event_id,start_at,end_at
```

---

<!-- FILE: ./projects/glow-masterdata/sheet_schema/MstMissionEventDependency.csv -->
## ./projects/glow-masterdata/sheet_schema/MstMissionEventDependency.csv

```csv
memo,,,,,,
TABLE,MstMissionEventDependency,MstMissionEventDependency,MstMissionEventDependency,MstMissionEventDependency,MstMissionEventDependency,MstMissionEventDependency
ENABLE,id,release_key,group_id,mst_mission_event_id,unlock_order,備考
```

---

<!-- FILE: ./projects/glow-masterdata/sheet_schema/MstMissionLimitedTerm.csv -->
## ./projects/glow-masterdata/sheet_schema/MstMissionLimitedTerm.csv

```csv
memo,,,,,,,,,,,,,
TABLE,MstMissionLimitedTerm,MstMissionLimitedTerm,MstMissionLimitedTerm,MstMissionLimitedTerm,MstMissionLimitedTerm,MstMissionLimitedTerm,MstMissionLimitedTerm,MstMissionLimitedTerm,MstMissionLimitedTerm,MstMissionLimitedTerm,MstMissionLimitedTerm,MstMissionLimitedTerm,MstMissionLimitedTermI18n
ENABLE,id,release_key,progress_group_key,criterion_type,criterion_value,criterion_count,mission_category,mst_mission_reward_group_id,sort_order,destination_scene,start_at,end_at,description.ja
```

---

<!-- FILE: ./projects/glow-masterdata/sheet_schema/MstMissionLimitedTermDependency.csv -->
## ./projects/glow-masterdata/sheet_schema/MstMissionLimitedTermDependency.csv

```csv
memo,,,,,,メモ
TABLE,MstMissionLimitedTermDependency,MstMissionLimitedTermDependency,MstMissionLimitedTermDependency,MstMissionLimitedTermDependency,MstMissionLimitedTermDependency,
ENABLE,id,release_key,group_id,mst_mission_limited_term_id,unlock_order,
```

---

<!-- FILE: ./projects/glow-masterdata/sheet_schema/MstMissionReward.csv -->
## ./projects/glow-masterdata/sheet_schema/MstMissionReward.csv

```csv
memo
TABLE,MstMissionReward,MstMissionReward,MstMissionReward,MstMissionReward,MstMissionReward,MstMissionReward,MstMissionReward,MstMissionReward
ENABLE,id,release_key,group_id,resource_type,resource_id,resource_amount,sort_order,備考
```

---

<!-- FILE: ./projects/glow-masterdata/sheet_schema/MstMissionWeekly.csv -->
## ./projects/glow-masterdata/sheet_schema/MstMissionWeekly.csv

```csv
memo,,,,,,,,,,,
TABLE,MstMissionWeekly,MstMissionWeekly,MstMissionWeekly,MstMissionWeekly,MstMissionWeekly,MstMissionWeekly,MstMissionWeekly,MstMissionWeekly,MstMissionWeekly,MstMissionWeekly,MstMissionWeeklyI18n
ENABLE,id,release_key,criterion_type,criterion_value,criterion_count,group_key,bonus_point,mst_mission_reward_group_id,sort_order,destination_scene,description.ja
```

---

<!-- FILE: ./projects/glow-masterdata/sheet_schema/MstPack.csv -->
## ./projects/glow-masterdata/sheet_schema/MstPack.csv

```csv
memo
TABLE,MstPack,MstPack,MstPack,MstPack,MstPack,MstPack,MstPack,MstPack,MstPack,MstPack,MstPack,MstPack,MstPack,MstPack,MstPack,MstPack,MstPackI18n
ENABLE,id,product_sub_id,discount_rate,sale_condition,sale_condition_value,sale_hours,is_display_expiration,pack_type,tradable_count,cost_type,is_first_time_free,cost_amount,is_recommend,asset_key,pack_decoration,release_key,name.ja
```

---

<!-- FILE: ./projects/glow-masterdata/sheet_schema/MstQuest.csv -->
## ./projects/glow-masterdata/sheet_schema/MstQuest.csv

```csv
memo
TABLE,MstQuest,MstQuest,MstQuest,MstQuest,MstQuest,MstQuest,MstQuest,MstQuest,MstQuest,MstQuest,MstQuestI18n,MstQuestI18n,MstQuestI18n
ENABLE,id,quest_type,mst_event_id,sort_order,asset_key,start_date,end_date,quest_group,difficulty,release_key,name.ja,category_name.ja,flavor_text.ja
```

---

<!-- FILE: ./projects/glow-masterdata/sheet_schema/MstShopItem.csv -->
## ./projects/glow-masterdata/sheet_schema/MstShopItem.csv

```csv
memo,,,,,,,,,,,,,商品内容
TABLE,MstShopItem,MstShopItem,MstShopItem,MstShopItem,MstShopItem,MstShopItem,MstShopItem,MstShopItem,MstShopItem,MstShopItem,MstShopItem,MstShopItem
ENABLE,id,shop_type,cost_type,cost_amount,is_first_time_free,tradable_count,resource_type,resource_id,resource_amount,start_date,end_date,release_key
```

---

<!-- FILE: ./projects/glow-masterdata/sheet_schema/MstShopPass.csv -->
## ./projects/glow-masterdata/sheet_schema/MstShopPass.csv

```csv
memo
TABLE,MstShopPass,MstShopPass,MstShopPass,MstShopPass,MstShopPass,MstShopPass,MstShopPass,MstShopPassI18n
ENABLE,id,opr_product_id,is_display_expiration,pass_duration_days,asset_key,shop_pass_cell_color,release_key,name.ja
```

---

<!-- FILE: ./projects/glow-masterdata/sheet_schema/MstStage.csv -->
## ./projects/glow-masterdata/sheet_schema/MstStage.csv

```csv
memo
TABLE,MstStage,MstStage,MstStage,MstStage,MstStage,MstStage,MstStage,MstStage,MstStage,MstStage,MstStage,MstStage,MstStage,MstStage,MstStage,MstStage,MstStage,MstStage,MstStage,MstStageI18n,MstStageI18n
ENABLE,id,mst_quest_id,mst_in_game_id,stage_number,recommended_level,cost_stamina,exp,coin,prev_mst_stage_id,mst_stage_tips_group_id,auto_lap_type,max_auto_lap_count,sort_order,asset_key,mst_stage_limit_status_id,release_key,mst_artwork_fragment_drop_group_id,start_at,end_at,name.ja,category_name.ja
```

---

<!-- FILE: ./projects/glow-masterdata/sheet_schema/MstUnit.csv -->
## ./projects/glow-masterdata/sheet_schema/MstUnit.csv

```csv
memo,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,フレーバーチェック
TABLE,MstUnit,MstUnit,MstUnit,MstUnit,MstUnit,MstUnit,MstUnit,MstUnit,MstUnit,MstUnit,MstUnit,MstUnit,MstUnit,MstUnit,MstUnit,MstUnit,MstUnit,MstUnit,MstUnit,MstUnit,MstUnit,MstUnit,MstUnit,MstUnit,MstUnit,MstUnit,MstUnit,MstUnit,MstUnit,MstUnit,MstUnitI18n,MstUnitI18n,,MstUnitI18n
ENABLE,id,fragment_mst_item_id,role_type,color,attack_range_type,unit_label,has_specific_rank_up,mst_series_id,asset_key,rarity,sort_order,summon_cost,summon_cool_time,special_attack_initial_cool_time,special_attack_cool_time,min_hp,max_hp,damage_knock_back_count,move_speed,well_distance,min_attack_power,max_attack_power,mst_unit_ability_id1,ability_unlock_rank1,mst_unit_ability_id2,ability_unlock_rank2,mst_unit_ability_id3,ability_unlock_rank3,is_encyclopedia_special_attack_position_right,release_key,name.ja,description.ja,,detail.ja
```

---

<!-- FILE: ./projects/glow-masterdata/sheet_schema/OprProduct.csv -->
## ./projects/glow-masterdata/sheet_schema/OprProduct.csv

```csv
memo,,,,,,,,,,,商品内容,iOSプロダクトID,AndroidプロダクトID,iOS価格,Android価格
TABLE,OprProduct,OprProduct,OprProduct,OprProduct,OprProduct,OprProduct,OprProduct,OprProduct,OprProduct,OprProductI18n
ENABLE,id,mst_store_product_id,product_type,purchasable_count,paid_amount,display_priority,start_date,end_date,release_key,asset_key.ja
```

---

<!-- FILE: ./projects/glow-server/admin/app/Constants/MissionCriterionType.php -->
## ./projects/glow-server/admin/app/Constants/MissionCriterionType.php

```php
<?php

declare(strict_types=1);

namespace App\Constants;

use App\Domain\Mission\Enums\MissionCriterionType as ApiMissionCriterionType;
use Illuminate\Support\Collection;

enum MissionCriterionType: string
{
    case NONE = ApiMissionCriterionType::NONE->value;

    // ミッション
    case MISSION_CLEAR_COUNT = ApiMissionCriterionType::MISSION_CLEAR_COUNT->value;
    case SPECIFIC_MISSION_CLEAR_COUNT = ApiMissionCriterionType::SPECIFIC_MISSION_CLEAR_COUNT->value;
    case MISSION_BONUS_POINT = ApiMissionCriterionType::MISSION_BONUS_POINT->value;

    // ステージ
    case SPECIFIC_QUEST_CLEAR = ApiMissionCriterionType::SPECIFIC_QUEST_CLEAR->value;
    case SPECIFIC_STAGE_CLEAR_COUNT = ApiMissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT->value;
    case QUEST_CLEAR_COUNT = ApiMissionCriterionType::QUEST_CLEAR_COUNT->value;
    case STAGE_CLEAR_COUNT = ApiMissionCriterionType::STAGE_CLEAR_COUNT->value;
    case SPECIFIC_STAGE_CHALLENGE_COUNT = ApiMissionCriterionType::SPECIFIC_STAGE_CHALLENGE_COUNT->value;

    case SPECIFIC_UNIT_STAGE_CLEAR_COUNT = ApiMissionCriterionType::SPECIFIC_UNIT_STAGE_CLEAR_COUNT->value;
    case SPECIFIC_UNIT_STAGE_CHALLENGE_COUNT = ApiMissionCriterionType::SPECIFIC_UNIT_STAGE_CHALLENGE_COUNT->value;
    case SPECIFIC_TRIBE_UNIT_STAGE_CLEAR_COUNT = ApiMissionCriterionType::SPECIFIC_TRIBE_UNIT_STAGE_CLEAR_COUNT->value;
    case SPECIFIC_TRIBE_UNIT_STAGE_CHALLENGE_COUNT = ApiMissionCriterionType::SPECIFIC_TRIBE_UNIT_STAGE_CHALLENGE_COUNT->value;

    // インゲーム
    case DEFEAT_ENEMY_COUNT = ApiMissionCriterionType::DEFEAT_ENEMY_COUNT->value;
    case DEFEAT_BOSS_ENEMY_COUNT = ApiMissionCriterionType::DEFEAT_BOSS_ENEMY_COUNT->value;
    case SPECIFIC_ENEMY_DISCOVERY_COUNT = ApiMissionCriterionType::SPECIFIC_ENEMY_DISCOVERY_COUNT->value;
    case ENEMY_DISCOVERY_COUNT = ApiMissionCriterionType::ENEMY_DISCOVERY_COUNT->value;
    case SPECIFIC_SERIES_ENEMY_DISCOVERY_COUNT = ApiMissionCriterionType::SPECIFIC_SERIES_ENEMY_DISCOVERY_COUNT->value;

    // ログイン
    case LOGIN_COUNT = ApiMissionCriterionType::LOGIN_COUNT->value;
    case LOGIN_CONTINUE_COUNT = ApiMissionCriterionType::LOGIN_CONTINUE_COUNT->value;
    case DAYS_FROM_UNLOCKED_MISSION = ApiMissionCriterionType::DAYS_FROM_UNLOCKED_MISSION->value;

    // ユーザー
    case USER_LEVEL = ApiMissionCriterionType::USER_LEVEL->value;
    case ICON_CHANGE = ApiMissionCriterionType::ICON_CHANGE->value;
    case EMBLEM_CHANGE = ApiMissionCriterionType::EMBLEM_CHANGE->value;
    case TUTORIAL_COMPLETED = ApiMissionCriterionType::TUTORIAL_COMPLETED->value;
    case COIN_COLLECT = ApiMissionCriterionType::COIN_COLLECT->value;
    case COIN_USED_COUNT = ApiMissionCriterionType::COIN_USED_COUNT->value;

    // 図鑑
    case BOOK_EMBLEM_COUNT = ApiMissionCriterionType::BOOK_EMBLEM_COUNT->value;
    case BOOK_UNIT_COUNT = ApiMissionCriterionType::BOOK_UNIT_COUNT->value;

    // ユニット
    case UNIT_LEVEL = ApiMissionCriterionType::UNIT_LEVEL->value;
    case UNIT_LEVEL_UP_COUNT = ApiMissionCriterionType::UNIT_LEVEL_UP_COUNT->value;
    case SPECIFIC_UNIT_LEVEL = ApiMissionCriterionType::SPECIFIC_UNIT_LEVEL->value;
    case SPECIFIC_UNIT_RANK_UP_COUNT = ApiMissionCriterionType::SPECIFIC_UNIT_RANK_UP_COUNT->value;
    case SPECIFIC_UNIT_GRADE_UP_COUNT = ApiMissionCriterionType::SPECIFIC_UNIT_GRADE_UP_COUNT->value;
    case UNIT_ACQUIRED_COUNT = ApiMissionCriterionType::UNIT_ACQUIRED_COUNT->value;

    // ゲート
    case OUTPOST_ENHANCE_COUNT = ApiMissionCriterionType::OUTPOST_ENHANCE_COUNT->value;
    case SPECIFIC_OUTPOST_ENHANCE_LEVEL = ApiMissionCriterionType::SPECIFIC_OUTPOST_ENHANCE_LEVEL->value;
    case OUTPOST_KOMA_CHANGE = ApiMissionCriterionType::OUTPOST_KOMA_CHANGE->value;

    // システム
    case REVIEW_COMPLETE = ApiMissionCriterionType::REVIEW_COMPLETED->value;
    case FOLLOW_COMPLETED = ApiMissionCriterionType::FOLLOW_COMPLETED->value;
    case ACCOUNT_COMPLETED = ApiMissionCriterionType::ACCOUNT_COMPLETED->value;
    case IAA_COUNT = ApiMissionCriterionType::IAA_COUNT->value;
    case ACCESS_WEB = ApiMissionCriterionType::ACCESS_WEB->value;

    // ガチャ
    case SPECIFIC_GACHA_DRAW_COUNT = ApiMissionCriterionType::SPECIFIC_GACHA_DRAW_COUNT->value;
    case GACHA_DRAW_COUNT = ApiMissionCriterionType::GACHA_DRAW_COUNT->value;

    // アイテム
    case SPECIFIC_ITEM_COLLECT = ApiMissionCriterionType::SPECIFIC_ITEM_COLLECT->value;

    // 放置収益
    case IDLE_INCENTIVE_COUNT = ApiMissionCriterionType::IDLE_INCENTIVE_COUNT->value;
    case IDLE_INCENTIVE_QUICK_COUNT = ApiMissionCriterionType::IDLE_INCENTIVE_QUICK_COUNT->value;

    public function label(): string
    {
        return match ($this) {
            self::MISSION_CLEAR_COUNT => 'ミッションをY個クリアする',
            self::SPECIFIC_MISSION_CLEAR_COUNT => '指定したミッショングループXの内でY個クリアする',
            self::MISSION_BONUS_POINT => 'ミッションボーナスポイントをY個集める(ミッションの累計ボーナスポイントエリアの設定)',
            self::SPECIFIC_QUEST_CLEAR => '指定クエストXをクリアする',
            self::SPECIFIC_STAGE_CLEAR_COUNT => '指定ステージXをY回クリア',
            self::QUEST_CLEAR_COUNT => '通算クエストクリア回数がY回に到達',
            self::STAGE_CLEAR_COUNT => '通算ステージクリア回数がY回に到達',
            self::SPECIFIC_STAGE_CHALLENGE_COUNT => '指定ステージXにY回挑戦する',
            self::SPECIFIC_UNIT_STAGE_CLEAR_COUNT => '指定したユニットを編成して指定したステージを Y回クリア',
            self::SPECIFIC_UNIT_STAGE_CHALLENGE_COUNT => '指定したユニットを編成して指定したステージに Y回挑戦',
            self::SPECIFIC_TRIBE_UNIT_STAGE_CLEAR_COUNT => 'SpecificTribeUnitStageClearCount', //TODO 決まった際に対応
            self::SPECIFIC_TRIBE_UNIT_STAGE_CHALLENGE_COUNT => 'SpecificTribeUnitStageChallengeCount', //TODO 決まった際に対応
            self::DEFEAT_ENEMY_COUNT => 'インゲームで敵をY体撃破',
            self::DEFEAT_BOSS_ENEMY_COUNT => 'インゲームで強敵をY体撃破',
            self::SPECIFIC_ENEMY_DISCOVERY_COUNT => 'インゲームで指定敵キャラXをY体発見',
            self::ENEMY_DISCOVERY_COUNT => 'インゲームで敵キャラをY体発見,',
            self::SPECIFIC_SERIES_ENEMY_DISCOVERY_COUNT => '指定作品Xの敵キャラをY体発見',
            self::LOGIN_COUNT => '通算ログインがY日に到達',
            self::LOGIN_CONTINUE_COUNT => '連続ログインがY日目に到達',
            self::DAYS_FROM_UNLOCKED_MISSION => 'DaysFromUnlockedMission',//TODO 決まった際に対応
            self::USER_LEVEL => '全ユニットの内でいずれかがLv.Yに到達',
            self::ICON_CHANGE => 'EmblemChange', //TODO 決まった際に対応
            self::EMBLEM_CHANGE => 'EmblemChange', //TODO 決まった際に対応
            self::TUTORIAL_COMPLETED => 'チュートリアルをクリア',
            self::COIN_COLLECT => 'コインをX枚使用した',
            self::COIN_USED_COUNT => 'コインをX枚使用した',
            self::BOOK_EMBLEM_COUNT => 'BookEmblemCount', //TODO 決まった際に対応
            self::BOOK_UNIT_COUNT => 'BookUnitCount', //TODO 決まった際に対応
            self::UNIT_LEVEL => '全ユニットの内でいずれかがLv.Yに到達',
            self::UNIT_LEVEL_UP_COUNT => 'ユニットのレベルアップをY回する',
            self::SPECIFIC_UNIT_LEVEL => '指定ユニットがLv.Yに到達',
            self::SPECIFIC_UNIT_RANK_UP_COUNT => '指定したユニットのランクアップ回数がY回以上',
            self::SPECIFIC_UNIT_GRADE_UP_COUNT => '指定したユニットのグレードアップ回数がY回以上',
            self::UNIT_ACQUIRED_COUNT => 'ユニットをY体入手しよう',
            self::OUTPOST_ENHANCE_COUNT => 'ゲートをX回以上強化',
            self::SPECIFIC_OUTPOST_ENHANCE_LEVEL => '指定したゲート強化項目がLvYに到達する',
            self::OUTPOST_KOMA_CHANGE => 'OutpostKomaChange', //TODO 決まった際に対応
            self::REVIEW_COMPLETE => 'ストアレビューを記載',
            self::FOLLOW_COMPLETED => '公式X（エックス）をフォローする',
            self::ACCOUNT_COMPLETED => 'アカウント連携を行う',
            self::IAA_COUNT => '広告視聴をY回する',
            self::SPECIFIC_GACHA_DRAW_COUNT => '指定ガシャXをY回引く',
            self::GACHA_DRAW_COUNT => '通算でガチャをY回引く',
            self::SPECIFIC_ITEM_COLLECT => '指定アイテムをX個集める',
            self::IDLE_INCENTIVE_COUNT => '探索をY回する',
            self::IDLE_INCENTIVE_QUICK_COUNT => 'クイック探索をY回する',
            self::ACCESS_WEB => 'Webアクセスでミッションクリア'
        };
    }

    public static function labels(): Collection
    {
        $cases = self::cases();
        $labels = collect();
        foreach ($cases as $case) {
            if ($case === self::NONE) {
                continue;
            }
            $labels->put($case->value, $case->label());
        }
        return $labels;
    }
}
```

---

<!-- FILE: ./projects/glow-server/api/app/Domain/Mission/Enums/MissionCriterionType.php -->
## ./projects/glow-server/api/app/Domain/Mission/Enums/MissionCriterionType.php

```php
<?php

declare(strict_types=1);

namespace App\Domain\Mission\Enums;

use App\Domain\Mission\Entities\Criteria\AccessWebCriterion;
use App\Domain\Mission\Entities\Criteria\AccountCompletedCriterion;
use App\Domain\Mission\Entities\Criteria\AdventBattleChallengeCountCriterion;
use App\Domain\Mission\Entities\Criteria\AdventBattleScoreCriterion;
use App\Domain\Mission\Entities\Criteria\AdventBattleTotalScoreCriterion;
use App\Domain\Mission\Entities\Criteria\ArtworkCompletedCountCriterion;
use App\Domain\Mission\Entities\Criteria\CoinCollectCriterion;
use App\Domain\Mission\Entities\Criteria\CoinUsedCountCriterion;
use App\Domain\Mission\Entities\Criteria\DaysFromUnlockedMissionCriterion;
use App\Domain\Mission\Entities\Criteria\DefeatBossEnemyCountCriterion;
use App\Domain\Mission\Entities\Criteria\DefeatEnemyCountCriterion;
use App\Domain\Mission\Entities\Criteria\EmblemAcquiredCountCriterion;
use App\Domain\Mission\Entities\Criteria\EnemyDiscoveryCountCriterion;
use App\Domain\Mission\Entities\Criteria\FollowCompletedCriterion;
use App\Domain\Mission\Entities\Criteria\GachaDrawCountCriterion;
use App\Domain\Mission\Entities\Criteria\IaaCountCriterion;
use App\Domain\Mission\Entities\Criteria\IdleIncentiveCountCriterion;
use App\Domain\Mission\Entities\Criteria\IdleIncentiveQuickCountCriterion;
use App\Domain\Mission\Entities\Criteria\LoginContinueCountCriterion;
use App\Domain\Mission\Entities\Criteria\LoginCountCriterion;
use App\Domain\Mission\Entities\Criteria\MissionBonusPointCriterion;
use App\Domain\Mission\Entities\Criteria\MissionClearCountCriterion;
use App\Domain\Mission\Entities\Criteria\OutpostEnhanceCountCriterion;
use App\Domain\Mission\Entities\Criteria\PvpChallengeCountCriterion;
use App\Domain\Mission\Entities\Criteria\PvpWinCountCriterion;
use App\Domain\Mission\Entities\Criteria\QuestClearCountCriterion;
use App\Domain\Mission\Entities\Criteria\ReviewCompletedCriterion;
use App\Domain\Mission\Entities\Criteria\SpecificArtworkCompletedCountCriterion;
use App\Domain\Mission\Entities\Criteria\SpecificEmblemAcquiredCountCriterion;
use App\Domain\Mission\Entities\Criteria\SpecificEnemyDiscoveryCountCriterion;
use App\Domain\Mission\Entities\Criteria\SpecificGachaDrawCountCriterion;
use App\Domain\Mission\Entities\Criteria\SpecificItemCollectCriterion;
use App\Domain\Mission\Entities\Criteria\SpecificMissionClearCountCriterion;
use App\Domain\Mission\Entities\Criteria\SpecificOutpostEnhanceLevelCriterion;
use App\Domain\Mission\Entities\Criteria\SpecificQuestClearCriterion;
use App\Domain\Mission\Entities\Criteria\SpecificSeriesArtworkCompletedCountCriterion;
use App\Domain\Mission\Entities\Criteria\SpecificSeriesEmblemAcquiredCountCriterion;
use App\Domain\Mission\Entities\Criteria\SpecificSeriesEnemyDiscoveryCountCriterion;
use App\Domain\Mission\Entities\Criteria\SpecificSeriesUnitAcquiredCountCriterion;
use App\Domain\Mission\Entities\Criteria\SpecificStageChallengeCountCriterion;
use App\Domain\Mission\Entities\Criteria\SpecificStageClearCountCriterion;
use App\Domain\Mission\Entities\Criteria\SpecificUnitAcquiredCountCriterion;
use App\Domain\Mission\Entities\Criteria\SpecificUnitGradeUpCountCriterion;
use App\Domain\Mission\Entities\Criteria\SpecificUnitLevelCriterion;
use App\Domain\Mission\Entities\Criteria\SpecificUnitRankUpCountCriterion;
use App\Domain\Mission\Entities\Criteria\SpecificUnitStageChallengeCountCriterion;
use App\Domain\Mission\Entities\Criteria\SpecificUnitStageClearCountCriterion;
use App\Domain\Mission\Entities\Criteria\StageClearCountCriterion;
use App\Domain\Mission\Entities\Criteria\UnitAcquiredCountCriterion;
use App\Domain\Mission\Entities\Criteria\UnitLevelCriterion;
use App\Domain\Mission\Entities\Criteria\UnitLevelUpCountCriterion;
use App\Domain\Mission\Entities\Criteria\UserLevelCriterion;
use Illuminate\Support\Collection;

enum MissionCriterionType: string
{
    case NONE = 'None';

    // ミッション
    case MISSION_CLEAR_COUNT = 'MissionClearCount';
    case SPECIFIC_MISSION_CLEAR_COUNT = 'SpecificMissionClearCount';
    case MISSION_BONUS_POINT = 'MissionBonusPoint';

    // ステージ
    case SPECIFIC_QUEST_CLEAR = 'SpecificQuestClear';
    case SPECIFIC_STAGE_CLEAR_COUNT = 'SpecificStageClearCount';
    case QUEST_CLEAR_COUNT = 'QuestClearCount';
    case STAGE_CLEAR_COUNT = 'StageClearCount';
    case SPECIFIC_STAGE_CHALLENGE_COUNT = 'SpecificStageChallengeCount';

    case SPECIFIC_UNIT_STAGE_CLEAR_COUNT = 'SpecificUnitStageClearCount';
    case SPECIFIC_UNIT_STAGE_CHALLENGE_COUNT = 'SpecificUnitStageChallengeCount';
    case SPECIFIC_TRIBE_UNIT_STAGE_CLEAR_COUNT = 'SpecificTribeUnitStageClearCount';
    case SPECIFIC_TRIBE_UNIT_STAGE_CHALLENGE_COUNT = 'SpecificTribeUnitStageChallengeCount';

    // インゲーム
    case DEFEAT_ENEMY_COUNT = 'DefeatEnemyCount';
    case DEFEAT_BOSS_ENEMY_COUNT = 'DefeatBossEnemyCount';
    case SPECIFIC_SERIES_ENEMY_DISCOVERY_COUNT = 'SpecificSeriesEnemyDiscoveryCount';
    case ENEMY_DISCOVERY_COUNT = 'EnemyDiscoveryCount';
    case SPECIFIC_ENEMY_DISCOVERY_COUNT = 'SpecificEnemyDiscoveryCount';

    // ログイン
    case LOGIN_COUNT = 'LoginCount';
    case LOGIN_CONTINUE_COUNT = 'LoginContinueCount';
    case DAYS_FROM_UNLOCKED_MISSION = 'DaysFromUnlockedMission';

    // ユーザー
    case USER_LEVEL = 'UserLevel';
    case ICON_CHANGE = 'IconChange';
    case TUTORIAL_COMPLETED = 'TutorialCompleted';
    case COIN_COLLECT = 'CoinCollect';
    case COIN_USED_COUNT = 'CoinUsedCount';

    // 図鑑
    case SPECIFIC_SERIES_ARTWORK_COMPLETED_COUNT = 'SpecificSeriesArtworkCompletedCount';
    case ARTWORK_COMPLETED_COUNT = 'ArtworkCompletedCount';
    case SPECIFIC_ARTWORK_COMPLETED_COUNT = 'SpecificArtworkCompletedCount';

    // ユニット
    case UNIT_LEVEL = 'UnitLevel';
    case UNIT_LEVEL_UP_COUNT = 'UnitLevelUpCount';
    case SPECIFIC_UNIT_LEVEL = 'SpecificUnitLevel';
    case SPECIFIC_UNIT_RANK_UP_COUNT = 'SpecificUnitRankUpCount';
    case SPECIFIC_UNIT_GRADE_UP_COUNT = 'SpecificUnitGradeUpCount';
    case UNIT_ACQUIRED_COUNT = 'UnitAcquiredCount';
    case SPECIFIC_SERIES_UNIT_ACQUIRED_COUNT = 'SpecificSeriesUnitAcquiredCount';
    case SPECIFIC_UNIT_ACQUIRED_COUNT = 'SpecificUnitAcquiredCount';

    // ゲート
    case OUTPOST_ENHANCE_COUNT = 'OutpostEnhanceCount';
    case SPECIFIC_OUTPOST_ENHANCE_LEVEL = 'SpecificOutpostEnhanceLevel';
    case OUTPOST_KOMA_CHANGE = 'OutpostKomaChange';

    // システム
    case REVIEW_COMPLETED = 'ReviewCompleted';
    case FOLLOW_COMPLETED = 'FollowCompleted';
    case ACCOUNT_COMPLETED = 'AccountCompleted';
    case IAA_COUNT = 'IaaCount';
    case ACCESS_WEB = 'AccessWeb';

    // ガチャ
    case SPECIFIC_GACHA_DRAW_COUNT = 'SpecificGachaDrawCount';
    case GACHA_DRAW_COUNT = 'GachaDrawCount';

    // アイテム
    case SPECIFIC_ITEM_COLLECT = 'SpecificItemCollect';

    // エンブレム
    case SPECIFIC_SERIES_EMBLEM_ACQUIRED_COUNT = 'SpecificSeriesEmblemAcquiredCount';
    case EMBLEM_ACQUIRED_COUNT = 'EmblemAcquiredCount';
    case SPECIFIC_EMBLEM_ACQUIRED_COUNT = 'SpecificEmblemAcquiredCount';

    // 放置収益
    case IDLE_INCENTIVE_COUNT = 'IdleIncentiveCount';
    case IDLE_INCENTIVE_QUICK_COUNT = 'IdleIncentiveQuickCount';

    // 降臨バトル
    case ADVENT_BATTLE_CHALLENGE_COUNT = 'AdventBattleChallengeCount';
    case ADVENT_BATTLE_TOTAL_SCORE = 'AdventBattleTotalScore';
    case ADVENT_BATTLE_SCORE = 'AdventBattleScore';

    // PVP
    case PVP_CHALLENGE_COUNT = 'PvpChallengeCount';
    case PVP_WIN_COUNT = 'PvpWinCount';

    /**
     * Criterionクラスへのマッピング
     *
     * @return string|null
     */
    public function getCriterionClass(): ?string
    {
        return match ($this) {
                // ミッション
            self::MISSION_CLEAR_COUNT => MissionClearCountCriterion::class,
            self::SPECIFIC_MISSION_CLEAR_COUNT => SpecificMissionClearCountCriterion::class,
            self::MISSION_BONUS_POINT => MissionBonusPointCriterion::class,
                // ステージ
            self::SPECIFIC_STAGE_CLEAR_COUNT => SpecificStageClearCountCriterion::class,
            self::STAGE_CLEAR_COUNT => StageClearCountCriterion::class,
            self::SPECIFIC_STAGE_CHALLENGE_COUNT => SpecificStageChallengeCountCriterion::class,
            self::SPECIFIC_QUEST_CLEAR => SpecificQuestClearCriterion::class,
            self::SPECIFIC_UNIT_STAGE_CHALLENGE_COUNT => SpecificUnitStageChallengeCountCriterion::class,
            self::SPECIFIC_UNIT_STAGE_CLEAR_COUNT => SpecificUnitStageClearCountCriterion::class,
            self::QUEST_CLEAR_COUNT => QuestClearCountCriterion::class,
                // インゲーム
            self::DEFEAT_ENEMY_COUNT => DefeatEnemyCountCriterion::class,
            self::DEFEAT_BOSS_ENEMY_COUNT => DefeatBossEnemyCountCriterion::class,
            self::SPECIFIC_SERIES_ENEMY_DISCOVERY_COUNT => SpecificSeriesEnemyDiscoveryCountCriterion::class,
            self::ENEMY_DISCOVERY_COUNT => EnemyDiscoveryCountCriterion::class,
            self::SPECIFIC_ENEMY_DISCOVERY_COUNT => SpecificEnemyDiscoveryCountCriterion::class,
                // ログイン
            self::LOGIN_COUNT => LoginCountCriterion::class,
            self::LOGIN_CONTINUE_COUNT => LoginContinueCountCriterion::class,
            self::DAYS_FROM_UNLOCKED_MISSION => DaysFromUnlockedMissionCriterion::class,
                // ユーザー
            self::COIN_COLLECT => CoinCollectCriterion::class,
            self::COIN_USED_COUNT => CoinUsedCountCriterion::class,
            self::USER_LEVEL => UserLevelCriterion::class,
                // 図鑑
            self::SPECIFIC_SERIES_ARTWORK_COMPLETED_COUNT => SpecificSeriesArtworkCompletedCountCriterion::class,
            self::ARTWORK_COMPLETED_COUNT => ArtworkCompletedCountCriterion::class,
            self::SPECIFIC_ARTWORK_COMPLETED_COUNT => SpecificArtworkCompletedCountCriterion::class,
                // ユニット
            self::UNIT_LEVEL => UnitLevelCriterion::class,
            self::SPECIFIC_UNIT_LEVEL => SpecificUnitLevelCriterion::class,
            self::UNIT_LEVEL_UP_COUNT => UnitLevelUpCountCriterion::class,
            self::UNIT_ACQUIRED_COUNT => UnitAcquiredCountCriterion::class,
            self::SPECIFIC_UNIT_RANK_UP_COUNT => SpecificUnitRankUpCountCriterion::class,
            self::SPECIFIC_UNIT_GRADE_UP_COUNT => SpecificUnitGradeUpCountCriterion::class,
            self::SPECIFIC_UNIT_ACQUIRED_COUNT => SpecificUnitAcquiredCountCriterion::class,
            self::SPECIFIC_SERIES_UNIT_ACQUIRED_COUNT => SpecificSeriesUnitAcquiredCountCriterion::class,
                // ゲート
            self::OUTPOST_ENHANCE_COUNT => OutpostEnhanceCountCriterion::class,
            self::SPECIFIC_OUTPOST_ENHANCE_LEVEL => SpecificOutpostEnhanceLevelCriterion::class,
                // システム
            self::REVIEW_COMPLETED => ReviewCompletedCriterion::class,
            self::FOLLOW_COMPLETED => FollowCompletedCriterion::class,
            self::ACCOUNT_COMPLETED => AccountCompletedCriterion::class,
            self::IAA_COUNT => IaaCountCriterion::class,
            self::ACCESS_WEB => AccessWebCriterion::class,
                // ガチャ
            self::SPECIFIC_GACHA_DRAW_COUNT => SpecificGachaDrawCountCriterion::class,
            self::GACHA_DRAW_COUNT => GachaDrawCountCriterion::class,
                // アイテム
            self::SPECIFIC_ITEM_COLLECT => SpecificItemCollectCriterion::class,
                // エンブレム
            self::SPECIFIC_SERIES_EMBLEM_ACQUIRED_COUNT => SpecificSeriesEmblemAcquiredCountCriterion::class,
            self::EMBLEM_ACQUIRED_COUNT => EmblemAcquiredCountCriterion::class,
            self::SPECIFIC_EMBLEM_ACQUIRED_COUNT => SpecificEmblemAcquiredCountCriterion::class,
                // 放置収益
            self::IDLE_INCENTIVE_COUNT => IdleIncentiveCountCriterion::class,
            self::IDLE_INCENTIVE_QUICK_COUNT => IdleIncentiveQuickCountCriterion::class,
                // 降臨バトル
            self::ADVENT_BATTLE_CHALLENGE_COUNT => AdventBattleChallengeCountCriterion::class,
            self::ADVENT_BATTLE_TOTAL_SCORE => AdventBattleTotalScoreCriterion::class,
            self::ADVENT_BATTLE_SCORE => AdventBattleScoreCriterion::class,
                // PVP
            self::PVP_CHALLENGE_COUNT => PvpChallengeCountCriterion::class,
            self::PVP_WIN_COUNT => PvpWinCountCriterion::class,
            // その他
            default => null,
        };
    }

    /**
     * 複合ミッションの集計対象としてカウントして良いかどうかを判定する
     * true: カウントに含めて良い, false: カウントから除外する
     *
     * @param string $criterionType
     * @return boolean
     */
    public static function isCountableForCompositeMission(string $criterionType): bool
    {
        switch ($criterionType) {
            case MissionCriterionType::MISSION_CLEAR_COUNT->value:
            case MissionCriterionType::SPECIFIC_MISSION_CLEAR_COUNT->value:
            case MissionCriterionType::MISSION_BONUS_POINT->value:
                return false;
            default:
                return true;
        }
    }

    /**
     * 新規マスタ追加時に、即時達成判定が必要なCriterionTypeのリストを返す
     * @return Collection<self>
     */
    public static function needInstantClearTypes(): Collection
    {
        return collect([
            MissionCriterionType::SPECIFIC_UNIT_LEVEL,
            MissionCriterionType::SPECIFIC_UNIT_RANK_UP_COUNT,
            MissionCriterionType::SPECIFIC_UNIT_GRADE_UP_COUNT,
        ]);
    }
}
```

---

<!-- FILE: ./projects/glow-server/api/app/Domain/Resource/Enums/RewardType.php -->
## ./projects/glow-server/api/app/Domain/Resource/Enums/RewardType.php

```php
<?php

declare(strict_types=1);

namespace App\Domain\Resource\Enums;

enum RewardType: string
{
    case COIN = 'Coin';
    case FREE_DIAMOND = 'FreeDiamond';
    case STAMINA = 'Stamina';
    case ITEM = 'Item';
    case EMBLEM = 'Emblem';
    case EXP = 'Exp';
    case UNIT = 'Unit';
    case PAID_DIAMOND = 'PaidDiamond';
    case ARTWORK = 'Artwork';

    public function label(): string
    {
        return match ($this) {
            self::COIN => 'コイン',
            self::FREE_DIAMOND => '無償プリズム',
            self::STAMINA => 'スタミナ',
            self::ITEM => 'アイテム',
            self::EMBLEM => 'エンブレム',
            self::EXP => '経験値',
            self::UNIT => 'キャラ',
            self::PAID_DIAMOND => '有償プリズム',
            self::ARTWORK => '原画',
        };
    }

    /**
     * resource_idの指定が必須のタイプかどうか
     * true: 必須, false: 不要
     */
    public function hasResourceId(): bool
    {
        return match ($this) {
            self::COIN => false,
            self::FREE_DIAMOND => false,
            self::STAMINA => false,
            self::ITEM => true,
            self::EMBLEM => true,
            self::EXP => false,
            self::UNIT => true,
            self::PAID_DIAMOND => false,
            self::ARTWORK => true,
        };
    }
}
```

---

