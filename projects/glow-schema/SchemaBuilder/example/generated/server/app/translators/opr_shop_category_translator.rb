class OprShopCategoryTranslator
  def self.translate(opr_shop_category_model)
    view_model = OprShopCategoryViewModel.new
    view_model.id = opr_shop_category_model.id
    view_model.name = opr_shop_category_model.name
    view_model.asset_key = opr_shop_category_model.asset_key
    view_model.button_size = opr_shop_category_model.button_size
    view_model.sort_number = opr_shop_category_model.sort_number
    view_model
  end
end
