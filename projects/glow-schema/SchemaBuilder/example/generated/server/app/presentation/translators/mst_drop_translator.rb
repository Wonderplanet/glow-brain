class MstDropTranslator
  def self.translate(mst_drop_model)
    view_model = MstDropViewModel.new
    view_model.id = mst_drop_model.id
    view_model.mst_puzzle_opponent_id = mst_drop_model.mst_puzzle_opponent_id
    view_model.mst_item_id = mst_drop_model.mst_item_id
    view_model.amount = mst_drop_model.amount
    view_model.relative_probability = mst_drop_model.relative_probability
    view_model
  end
end
