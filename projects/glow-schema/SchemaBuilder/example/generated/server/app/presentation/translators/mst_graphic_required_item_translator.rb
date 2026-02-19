class MstGraphicRequiredItemTranslator
  def self.translate(mst_graphic_required_item_model)
    view_model = MstGraphicRequiredItemViewModel.new
    view_model.id = mst_graphic_required_item_model.id
    view_model.mst_character_variant_id = mst_graphic_required_item_model.mst_character_variant_id
    view_model.mst_item_id = mst_graphic_required_item_model.mst_item_id
    view_model.amount = mst_graphic_required_item_model.amount
    view_model
  end
end
