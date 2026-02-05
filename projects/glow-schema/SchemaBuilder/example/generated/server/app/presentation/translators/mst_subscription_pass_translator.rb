class MstSubscriptionPassTranslator
  def self.translate(mst_subscription_pass_model)
    view_model = MstSubscriptionPassViewModel.new
    view_model.id = mst_subscription_pass_model.id
    view_model.parent_mst_subscription_pass_id = mst_subscription_pass_model.parent_mst_subscription_pass_id
    view_model.opr_in_app_product_id = mst_subscription_pass_model.opr_in_app_product_id
    view_model.day_period = mst_subscription_pass_model.day_period
    view_model.max_subscription_day = mst_subscription_pass_model.max_subscription_day
    view_model
  end
end
