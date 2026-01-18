class SoloLiveMusicalUnitTranslator
  def self.translate(solo_live_musical_unit_model)
    view_model = SoloLiveMusicalUnitViewModel.new
    view_model.user_id = solo_live_musical_unit_model.user_id
    view_model.main_mst_character_variant_id = solo_live_musical_unit_model.main_mst_character_variant_id
    view_model.number = solo_live_musical_unit_model.number
    view_model.support_mst_character_variant_id1 = solo_live_musical_unit_model.support_mst_character_variant_id1
    view_model.support_mst_character_variant_id2 = solo_live_musical_unit_model.support_mst_character_variant_id2
    view_model.support_mst_character_variant_id3 = solo_live_musical_unit_model.support_mst_character_variant_id3
    view_model
  end
end
