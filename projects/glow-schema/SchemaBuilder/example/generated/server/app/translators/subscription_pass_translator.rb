class SubscriptionPassTranslator
  def self.translate(subscription_pass_model)
    view_model = SubscriptionPassViewModel.new
    view_model.opr_in_app_product_id = subscription_pass_model.opr_in_app_product_id
    view_model.current_day = subscription_pass_model.current_day
    view_model.stock = subscription_pass_model.stock
    view_model.start_at = subscription_pass_model.start_at
    view_model.end_at = subscription_pass_model.end_at
    view_model
  end
end
