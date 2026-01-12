class InAppPurchaseHistoryTranslator
  def self.translate(in_app_purchase_history_model)
    view_model = InAppPurchaseHistoryViewModel.new
    view_model.opr_in_app_product_id = in_app_purchase_history_model.opr_in_app_product_id
    view_model.total = in_app_purchase_history_model.total
    view_model
  end
end
