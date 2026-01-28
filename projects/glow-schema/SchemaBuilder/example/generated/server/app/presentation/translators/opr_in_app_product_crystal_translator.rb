class OprInAppProductCrystalTranslator
  def self.translate(opr_in_app_product_crystal_model)
    view_model = OprInAppProductCrystalViewModel.new
    view_model.id = opr_in_app_product_crystal_model.id
    view_model.opr_in_app_product_id = opr_in_app_product_crystal_model.opr_in_app_product_id
    view_model.paid_amount = opr_in_app_product_crystal_model.paid_amount
    view_model.free_amount = opr_in_app_product_crystal_model.free_amount
    view_model
  end
end
