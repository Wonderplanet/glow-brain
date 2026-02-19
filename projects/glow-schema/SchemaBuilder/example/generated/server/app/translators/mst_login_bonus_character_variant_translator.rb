class MstLoginBonusCharacterVariantTranslator
  def self.translate(mst_login_bonus_character_variant_model)
    view_model = MstLoginBonusCharacterVariantViewModel.new
    view_model.mst_character_variant_id1 = mst_login_bonus_character_variant_model.mst_character_variant_id1
    view_model.mst_character_variant_id2 = mst_login_bonus_character_variant_model.mst_character_variant_id2
    view_model.login_bonus_category = mst_login_bonus_character_variant_model.login_bonus_category
    view_model.character1_voice_key = mst_login_bonus_character_variant_model.character1_voice_key
    view_model.character2_voice_key = mst_login_bonus_character_variant_model.character2_voice_key
    view_model.character1_animation_name = mst_login_bonus_character_variant_model.character1_animation_name
    view_model.character2_animation_name = mst_login_bonus_character_variant_model.character2_animation_name
    view_model.wait_time = mst_login_bonus_character_variant_model.wait_time
    view_model.text1 = mst_login_bonus_character_variant_model.text1
    view_model.text2 = mst_login_bonus_character_variant_model.text2
    view_model
  end
end
