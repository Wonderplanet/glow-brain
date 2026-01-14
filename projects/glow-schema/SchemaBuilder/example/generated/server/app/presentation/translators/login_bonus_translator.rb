class LoginBonusTranslator
  def self.translate(login_bonus_model)
    view_model = LoginBonusViewModel.new
    view_model.opr_login_bonus_id = login_bonus_model.opr_login_bonus_id
    view_model.current_day = login_bonus_model.current_day
    view_model.received_at = login_bonus_model.received_at
    view_model
  end
end
