class InAppPurchaseResultTranslator
  def self.translate(in_app_purchase_result_model)
    view_model = InAppPurchaseResultViewModel.new
    view_model.received = in_app_purchase_result_model.received
    view_model.in_app_purchase_history = in_app_purchase_result_model.in_app_purchase_history
    view_model.subscription_pass = in_app_purchase_result_model.subscription_pass
    view_model.updated_mission = in_app_purchase_result_model.updated_mission
    view_model.sum_currency = in_app_purchase_result_model.sum_currency
    view_model
  end
end
