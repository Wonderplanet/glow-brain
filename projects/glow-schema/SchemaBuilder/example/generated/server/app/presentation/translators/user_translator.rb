class UserTranslator
  def self.translate(user_model)
    view_model = UserViewModel.new
    view_model.id = user_model.id
    view_model.nickname = user_model.nickname
    view_model.scenario_name = user_model.scenario_name
    view_model.gender = user_model.gender
    view_model.rank_level = user_model.rank_level
    view_model.user_exp = user_model.user_exp
    view_model.main_musical_unit_id = user_model.main_musical_unit_id
    view_model.favorite_character_variant = user_model.favorite_character_variant
    view_model.support_character_variant = user_model.support_character_variant
    view_model.crystal = user_model.crystal
    view_model.sum_currency = user_model.sum_currency
    view_model.my_id = user_model.my_id
    view_model.user_ap = user_model.user_ap
    view_model.birth_month = user_model.birth_month
    view_model.birth_day = user_model.birth_day
    view_model.created_at = user_model.created_at
    view_model.total_login_days = user_model.total_login_days
    view_model.description = user_model.description
    view_model.local_notification_enabled = user_model.local_notification_enabled
    view_model
  end
end
