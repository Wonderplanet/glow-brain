class SoloLiveUserProfileTranslator
  def self.translate(solo_live_user_profile_model)
    view_model = SoloLiveUserProfileViewModel.new
    view_model.user_name = solo_live_user_profile_model.user_name
    view_model.user_id = solo_live_user_profile_model.user_id
    view_model.rank_level = solo_live_user_profile_model.rank_level
    view_model.description = solo_live_user_profile_model.description
    view_model.last_accessed_at = solo_live_user_profile_model.last_accessed_at
    view_model.character_variant = solo_live_user_profile_model.character_variant
    view_model.relationship = solo_live_user_profile_model.relationship
    view_model.solo_live_user_titles = solo_live_user_profile_model.solo_live_user_titles
    view_model
  end
end
