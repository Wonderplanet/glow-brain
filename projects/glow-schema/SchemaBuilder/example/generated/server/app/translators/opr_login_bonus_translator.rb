class OprLoginBonusTranslator
  def self.translate(opr_login_bonus_model)
    view_model = OprLoginBonusViewModel.new
    view_model.id = opr_login_bonus_model.id
    view_model.login_bonus_category = opr_login_bonus_model.login_bonus_category
    view_model.start_at = opr_login_bonus_model.start_at
    view_model.end_at = opr_login_bonus_model.end_at
    view_model.user_created_since = opr_login_bonus_model.user_created_since
    view_model.user_created_until = opr_login_bonus_model.user_created_until
    view_model.sleep_login_days = opr_login_bonus_model.sleep_login_days
    view_model.asset_key = opr_login_bonus_model.asset_key
    view_model
  end
end
