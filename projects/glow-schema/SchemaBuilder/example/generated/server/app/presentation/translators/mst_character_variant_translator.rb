class MstCharacterVariantTranslator
  def self.translate(mst_character_variant_model)
    view_model = MstCharacterVariantViewModel.new
    view_model.id = mst_character_variant_model.id
    view_model.mst_character_id = mst_character_variant_model.mst_character_id
    view_model.name = mst_character_variant_model.name
    view_model.asset_key = mst_character_variant_model.asset_key
    view_model.rarity = mst_character_variant_model.rarity
    view_model.emotion_element = mst_character_variant_model.emotion_element
    view_model.mst_leader_skill_id = mst_character_variant_model.mst_leader_skill_id
    view_model.mst_appeal_id = mst_character_variant_model.mst_appeal_id
    view_model.mst_support_skill_id = mst_character_variant_model.mst_support_skill_id
    view_model.special_attack_speech = mst_character_variant_model.special_attack_speech
    view_model.belong_unit_name = mst_character_variant_model.belong_unit_name
    view_model.hp_coef = mst_character_variant_model.hp_coef
    view_model.performance_coef = mst_character_variant_model.performance_coef
    view_model.heal_coef = mst_character_variant_model.heal_coef
    view_model.label_coef = mst_character_variant_model.label_coef
    view_model.released_increase_hp_value = mst_character_variant_model.released_increase_hp_value
    view_model.released_increase_performance_value = mst_character_variant_model.released_increase_performance_value
    view_model.released_increase_heal_value = mst_character_variant_model.released_increase_heal_value
    view_model.has_signature = mst_character_variant_model.has_signature
    view_model
  end
end
