class MstDailyMissionTranslator
  def self.translate(mst_daily_mission_model)
    view_model = MstDailyMissionViewModel.new
    view_model.day_of_week = mst_daily_mission_model.day_of_week
    view_model.mission_number = mst_daily_mission_model.mission_number
    view_model.category = mst_daily_mission_model.category
    view_model.description = mst_daily_mission_model.description
    view_model.goal_count = mst_daily_mission_model.goal_count
    view_model.prize = mst_daily_mission_model.prize
    view_model.mst_character_variant_id = mst_daily_mission_model.mst_character_variant_id
    view_model.mst_day_quest_id = mst_daily_mission_model.mst_day_quest_id
    view_model.mst_character_id = mst_daily_mission_model.mst_character_id
    view_model
  end
end
