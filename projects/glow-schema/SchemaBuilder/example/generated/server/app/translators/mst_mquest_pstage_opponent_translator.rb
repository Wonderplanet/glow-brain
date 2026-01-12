class MstMquestPstageOpponentTranslator
  def self.translate(mst_mquest_pstage_opponent_model)
    view_model = MstMquestPstageOpponentViewModel.new
    view_model.id = mst_mquest_pstage_opponent_model.id
    view_model.mst_main_quest_puzzle_stage_id = mst_mquest_pstage_opponent_model.mst_main_quest_puzzle_stage_id
    view_model.mst_puzzle_opponent_id = mst_mquest_pstage_opponent_model.mst_puzzle_opponent_id
    view_model.level = mst_mquest_pstage_opponent_model.level
    view_model.wave_number = mst_mquest_pstage_opponent_model.wave_number
    view_model
  end
end
