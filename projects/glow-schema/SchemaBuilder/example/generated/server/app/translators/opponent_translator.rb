class OpponentTranslator
  def self.translate(opponent_model)
    view_model = OpponentViewModel.new
    view_model.id = opponent_model.id
    view_model.mst_opponent_id = opponent_model.mst_opponent_id
    view_model.mst_drop_id = opponent_model.mst_drop_id
    view_model
  end
end
