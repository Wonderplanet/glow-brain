class MstNormalMissionTranslator
  def self.translate(mst_normal_mission_model)
    view_model = MstNormalMissionViewModel.new
    view_model.mission_number = mst_normal_mission_model.mission_number
    view_model.level = mst_normal_mission_model.level
    view_model.category = mst_normal_mission_model.category
    view_model.description = mst_normal_mission_model.description
    view_model.goal_count = mst_normal_mission_model.goal_count
    view_model.prize = mst_normal_mission_model.prize
    view_model.mst_main_quest_id = mst_normal_mission_model.mst_main_quest_id
    view_model.mst_day_quest_id = mst_normal_mission_model.mst_day_quest_id
    view_model.mst_character_id = mst_normal_mission_model.mst_character_id
    view_model.character_variant_level = mst_normal_mission_model.character_variant_level
    view_model.totu_count = mst_normal_mission_model.totu_count
    view_model.mst_character_variant_id = mst_normal_mission_model.mst_character_variant_id
    view_model.mst_main_story_episode_id = mst_normal_mission_model.mst_main_story_episode_id
    view_model.mst_solo_story_episode_id = mst_normal_mission_model.mst_solo_story_episode_id
    view_model.mst_group_story_episode_id = mst_normal_mission_model.mst_group_story_episode_id
    view_model
  end
end
