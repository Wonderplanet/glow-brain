class MstDquestPstageOpponentTranslator
  def self.translate(mst_dquest_pstage_opponent_model)
    view_model = MstDquestPstageOpponentViewModel.new
    view_model.id = mst_dquest_pstage_opponent_model.id
    view_model.mst_day_quest_puzzle_stage_id = mst_dquest_pstage_opponent_model.mst_day_quest_puzzle_stage_id
    view_model.mst_puzzle_opponent_id = mst_dquest_pstage_opponent_model.mst_puzzle_opponent_id
    view_model.level = mst_dquest_pstage_opponent_model.level
    view_model.wave_number = mst_dquest_pstage_opponent_model.wave_number
    view_model
  end
end
