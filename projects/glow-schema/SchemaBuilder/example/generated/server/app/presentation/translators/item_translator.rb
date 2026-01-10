class ItemTranslator
  def self.translate(item_model)
    view_model = ItemViewModel.new
    view_model.mst_item_id = item_model.mst_item_id
    view_model.amount = item_model.amount
    view_model
  end
end
