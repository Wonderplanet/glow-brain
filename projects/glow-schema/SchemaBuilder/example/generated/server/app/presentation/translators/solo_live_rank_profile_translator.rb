class SoloLiveRankProfileTranslator
  def self.translate(solo_live_rank_profile_model)
    view_model = SoloLiveRankProfileViewModel.new
    view_model.user_name = solo_live_rank_profile_model.user_name
    view_model.user_id = solo_live_rank_profile_model.user_id
    view_model.rank_level = solo_live_rank_profile_model.rank_level
    view_model.description = solo_live_rank_profile_model.description
    view_model.last_accessed_at = solo_live_rank_profile_model.last_accessed_at
    view_model.character_variant = solo_live_rank_profile_model.character_variant
    view_model.rank = solo_live_rank_profile_model.rank
    view_model.score = solo_live_rank_profile_model.score
    view_model
  end
end
