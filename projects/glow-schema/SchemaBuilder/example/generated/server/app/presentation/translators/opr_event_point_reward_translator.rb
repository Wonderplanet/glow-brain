class OprEventPointRewardTranslator
  def self.translate(opr_event_point_reward_model)
    view_model = OprEventPointRewardViewModel.new
    view_model.opr_event_id = opr_event_point_reward_model.opr_event_id
    view_model.target_point = opr_event_point_reward_model.target_point
    view_model.extended_prize = opr_event_point_reward_model.extended_prize
    view_model
  end
end
