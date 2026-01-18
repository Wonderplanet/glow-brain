class AddItemHistoryTranslator
  def self.translate(add_item_history_model)
    view_model = AddItemHistoryViewModel.new
    view_model.mst_item_id = add_item_history_model.mst_item_id
    view_model.amount = add_item_history_model.amount
    view_model
  end
end
