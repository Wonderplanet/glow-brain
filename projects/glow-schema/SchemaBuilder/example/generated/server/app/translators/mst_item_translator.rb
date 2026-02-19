class MstItemTranslator
  def self.translate(mst_item_model)
    view_model = MstItemViewModel.new
    view_model.id = mst_item_model.id
    view_model.name = mst_item_model.name
    view_model.asset_key = mst_item_model.asset_key
    view_model.category = mst_item_model.category
    view_model.power = mst_item_model.power
    view_model.description = mst_item_model.description
    view_model.rarity = mst_item_model.rarity
    view_model
  end
end
