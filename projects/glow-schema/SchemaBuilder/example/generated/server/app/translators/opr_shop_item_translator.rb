class OprShopItemTranslator
  def self.translate(opr_shop_item_model)
    view_model = OprShopItemViewModel.new
    view_model.opr_shop_id = opr_shop_item_model.opr_shop_id
    view_model.mst_item_id = opr_shop_item_model.mst_item_id
    view_model.amount = opr_shop_item_model.amount
    view_model.mst_character_variant_id = opr_shop_item_model.mst_character_variant_id
    view_model
  end
end
