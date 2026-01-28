class SoloLiveUserTitleTranslator
  def self.translate(solo_live_user_title_model)
    view_model = SoloLiveUserTitleViewModel.new
    view_model.opr_solo_live_id = solo_live_user_title_model.opr_solo_live_id
    view_model.mst_character_variant_id = solo_live_user_title_model.mst_character_variant_id
    view_model.rank = solo_live_user_title_model.rank
    view_model.score = solo_live_user_title_model.score
    view_model
  end
end
