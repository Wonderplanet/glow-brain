class OprEventNormalQuestPuzzleStageTranslator
  def self.translate(opr_event_normal_quest_puzzle_stage_model)
    view_model = OprEventNormalQuestPuzzleStageViewModel.new
    view_model.id = opr_event_normal_quest_puzzle_stage_model.id
    view_model.tp = opr_event_normal_quest_puzzle_stage_model.tp
    view_model.user_exp = opr_event_normal_quest_puzzle_stage_model.user_exp
    view_model.entry = opr_event_normal_quest_puzzle_stage_model.entry
    view_model.main = opr_event_normal_quest_puzzle_stage_model.main
    view_model.reward_point = opr_event_normal_quest_puzzle_stage_model.reward_point
    view_model
  end
end
