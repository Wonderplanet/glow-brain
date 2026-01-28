class OprEventNquestPstageOpponentTranslator
  def self.translate(opr_event_nquest_pstage_opponent_model)
    view_model = OprEventNquestPstageOpponentViewModel.new
    view_model.id = opr_event_nquest_pstage_opponent_model.id
    view_model.opr_event_normal_quest_puzzle_stage_id = opr_event_nquest_pstage_opponent_model.opr_event_normal_quest_puzzle_stage_id
    view_model.mst_puzzle_opponent_id = opr_event_nquest_pstage_opponent_model.mst_puzzle_opponent_id
    view_model.level = opr_event_nquest_pstage_opponent_model.level
    view_model.wave_number = opr_event_nquest_pstage_opponent_model.wave_number
    view_model
  end
end
