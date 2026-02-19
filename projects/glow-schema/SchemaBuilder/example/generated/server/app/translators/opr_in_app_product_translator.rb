class OprInAppProductTranslator
  def self.translate(opr_in_app_product_model)
    view_model = OprInAppProductViewModel.new
    view_model.id = opr_in_app_product_model.id
    view_model.product_type = opr_in_app_product_model.product_type
    view_model.mst_in_app_product_id = opr_in_app_product_model.mst_in_app_product_id
    view_model.start_at = opr_in_app_product_model.start_at
    view_model.end_at = opr_in_app_product_model.end_at
    view_model.is_sale = opr_in_app_product_model.is_sale
    view_model.icon_asset_key = opr_in_app_product_model.icon_asset_key
    view_model.banner_path = opr_in_app_product_model.banner_path
    view_model.limit_amount = opr_in_app_product_model.limit_amount
    view_model.description = opr_in_app_product_model.description
    view_model.sort_number = opr_in_app_product_model.sort_number
    view_model
  end
end
