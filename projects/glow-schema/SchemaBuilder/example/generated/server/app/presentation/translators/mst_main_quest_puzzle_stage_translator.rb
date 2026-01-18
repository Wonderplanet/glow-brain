class MstMainQuestPuzzleStageTranslator
  def self.translate(mst_main_quest_puzzle_stage_model)
    view_model = MstMainQuestPuzzleStageViewModel.new
    view_model.id = mst_main_quest_puzzle_stage_model.id
    view_model.tp = mst_main_quest_puzzle_stage_model.tp
    view_model.user_exp = mst_main_quest_puzzle_stage_model.user_exp
    view_model.entry = mst_main_quest_puzzle_stage_model.entry
    view_model.main = mst_main_quest_puzzle_stage_model.main
    view_model
  end
end
