class OprInAppProductItemTranslator
  def self.translate(opr_in_app_product_item_model)
    view_model = OprInAppProductItemViewModel.new
    view_model.id = opr_in_app_product_item_model.id
    view_model.opr_in_app_product_id = opr_in_app_product_item_model.opr_in_app_product_id
    view_model.mst_item_id = opr_in_app_product_item_model.mst_item_id
    view_model.amount = opr_in_app_product_item_model.amount
    view_model
  end
end
