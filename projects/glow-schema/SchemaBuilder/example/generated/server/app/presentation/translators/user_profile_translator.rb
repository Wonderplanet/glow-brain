class UserProfileTranslator
  def self.translate(user_profile_model)
    view_model = UserProfileViewModel.new
    view_model.user_name = user_profile_model.user_name
    view_model.favorite_mst_character_variant = user_profile_model.favorite_mst_character_variant
    view_model.support_character_variant = user_profile_model.support_character_variant
    view_model.relationship = user_profile_model.relationship
    view_model.rank_level = user_profile_model.rank_level
    view_model.last_accessed_at = user_profile_model.last_accessed_at
    view_model.description = user_profile_model.description
    view_model
  end
end
