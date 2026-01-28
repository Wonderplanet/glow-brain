class UserProfileUpdateResultTranslator
  def self.translate(user_profile_update_result_model)
    view_model = UserProfileUpdateResultViewModel.new
    view_model.updated_mission = user_profile_update_result_model.updated_mission
    view_model
  end
end
