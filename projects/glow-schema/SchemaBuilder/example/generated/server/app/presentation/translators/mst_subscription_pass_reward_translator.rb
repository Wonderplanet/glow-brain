class MstSubscriptionPassRewardTranslator
  def self.translate(mst_subscription_pass_reward_model)
    view_model = MstSubscriptionPassRewardViewModel.new
    view_model.id = mst_subscription_pass_reward_model.id
    view_model.mst_subscription_pass_id = mst_subscription_pass_reward_model.mst_subscription_pass_id
    view_model.day = mst_subscription_pass_reward_model.day
    view_model.prize = mst_subscription_pass_reward_model.prize
    view_model
  end
end
