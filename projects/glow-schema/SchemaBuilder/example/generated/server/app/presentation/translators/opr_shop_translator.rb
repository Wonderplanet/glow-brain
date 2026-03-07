class OprShopTranslator
  def self.translate(opr_shop_model)
    view_model = OprShopViewModel.new
    view_model.id = opr_shop_model.id
    view_model.opr_shop_category_id = opr_shop_model.opr_shop_category_id
    view_model.name = opr_shop_model.name
    view_model.description = opr_shop_model.description
    view_model.price = opr_shop_model.price
    view_model.price_type = opr_shop_model.price_type
    view_model.price_mst_item_id = opr_shop_model.price_mst_item_id
    view_model.is_sale = opr_shop_model.is_sale
    view_model.purchasable_count = opr_shop_model.purchasable_count
    view_model.banner_path = opr_shop_model.banner_path
    view_model.icon_asset_key = opr_shop_model.icon_asset_key
    view_model.sort_number = opr_shop_model.sort_number
    view_model.opr_event_id = opr_shop_model.opr_event_id
    view_model.start_at = opr_shop_model.start_at
    view_model.end_at = opr_shop_model.end_at
    view_model
  end
end
