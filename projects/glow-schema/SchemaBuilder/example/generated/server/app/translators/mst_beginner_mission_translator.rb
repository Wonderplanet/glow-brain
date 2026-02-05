class MstBeginnerMissionTranslator
  def self.translate(mst_beginner_mission_model)
    view_model = MstBeginnerMissionViewModel.new
    view_model.mission_number = mst_beginner_mission_model.mission_number
    view_model.level = mst_beginner_mission_model.level
    view_model.category = mst_beginner_mission_model.category
    view_model.description = mst_beginner_mission_model.description
    view_model.goal_count = mst_beginner_mission_model.goal_count
    view_model.prize = mst_beginner_mission_model.prize
    view_model.mst_main_quest_id = mst_beginner_mission_model.mst_main_quest_id
    view_model.mst_main_story_episode_id = mst_beginner_mission_model.mst_main_story_episode_id
    view_model
  end
end
