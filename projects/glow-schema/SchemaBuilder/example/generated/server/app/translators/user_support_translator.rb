class UserSupportTranslator
  def self.translate(user_support_model)
    view_model = UserSupportViewModel.new
    view_model.id = user_support_model.id
    view_model.my_id = user_support_model.my_id
    view_model.terminal_move_id = user_support_model.terminal_move_id
    view_model.terminal_move_password = user_support_model.terminal_move_password
    view_model.shop_birth_year = user_support_model.shop_birth_year
    view_model.shop_birth_month = user_support_model.shop_birth_month
    view_model.terms_of_service_version = user_support_model.terms_of_service_version
    view_model.monthly_purchase_amount = user_support_model.monthly_purchase_amount
    view_model
  end
end
