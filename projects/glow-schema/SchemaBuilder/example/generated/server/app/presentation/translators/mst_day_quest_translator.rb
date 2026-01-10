class MstDayQuestTranslator
  def self.translate(mst_day_quest_model)
    view_model = MstDayQuestViewModel.new
    view_model.id = mst_day_quest_model.id
    view_model.name = mst_day_quest_model.name
    view_model.stamina_consumption = mst_day_quest_model.stamina_consumption
    view_model.recommended_level = mst_day_quest_model.recommended_level
    view_model.mst_day_quest_puzzle_stage_id = mst_day_quest_model.mst_day_quest_puzzle_stage_id
    view_model.dependency_mst_day_quest_id = mst_day_quest_model.dependency_mst_day_quest_id
    view_model.prize = mst_day_quest_model.prize
    view_model
  end
end
