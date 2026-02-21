class MstMainQuestTranslator
  def self.translate(mst_main_quest_model)
    view_model = MstMainQuestViewModel.new
    view_model.id = mst_main_quest_model.id
    view_model.mst_main_quest_chapter_id = mst_main_quest_model.mst_main_quest_chapter_id
    view_model.name = mst_main_quest_model.name
    view_model.stamina_consumption = mst_main_quest_model.stamina_consumption
    view_model.recommended_level = mst_main_quest_model.recommended_level
    view_model.mst_main_quest_puzzle_stage_id = mst_main_quest_model.mst_main_quest_puzzle_stage_id
    view_model.dependency_mst_main_quest_id = mst_main_quest_model.dependency_mst_main_quest_id
    view_model.prize = mst_main_quest_model.prize
    view_model.release_at = mst_main_quest_model.release_at
    view_model
  end
end
