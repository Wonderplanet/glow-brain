class UserApTranslator
  def self.translate(user_ap_model)
    view_model = UserApViewModel.new
    view_model.base_ap = user_ap_model.base_ap
    view_model.below_max_at = user_ap_model.below_max_at
    view_model
  end
end
