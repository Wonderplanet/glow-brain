class MstInAppProductTranslator
  def self.translate(mst_in_app_product_model)
    view_model = MstInAppProductViewModel.new
    view_model.id = mst_in_app_product_model.id
    view_model.product_id = mst_in_app_product_model.product_id
    view_model.price = mst_in_app_product_model.price
    view_model
  end
end
