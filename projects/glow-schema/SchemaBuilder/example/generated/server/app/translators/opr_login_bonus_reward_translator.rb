class OprLoginBonusRewardTranslator
  def self.translate(opr_login_bonus_reward_model)
    view_model = OprLoginBonusRewardViewModel.new
    view_model.opr_login_bonus_id = opr_login_bonus_reward_model.opr_login_bonus_id
    view_model.day = opr_login_bonus_reward_model.day
    view_model.prize = opr_login_bonus_reward_model.prize
    view_model
  end
end
