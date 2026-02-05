class CharacterVariantTranslator
  def self.translate(character_variant_model)
    view_model = CharacterVariantViewModel.new
    view_model.id = character_variant_model.id
    view_model.mst_character_variant_id = character_variant_model.mst_character_variant_id
    view_model.level = character_variant_model.level
    view_model.exp_point = character_variant_model.exp_point
    view_model.limit_break_level = character_variant_model.limit_break_level
    view_model.limit_break_max_level = character_variant_model.limit_break_max_level
    view_model.increase_hp_value = character_variant_model.increase_hp_value
    view_model.increase_performance_value = character_variant_model.increase_performance_value
    view_model.increase_heal_value = character_variant_model.increase_heal_value
    view_model.appeal_level = character_variant_model.appeal_level
    view_model.support_skill_level = character_variant_model.support_skill_level
    view_model.graphic_released = character_variant_model.graphic_released
    view_model.current_graphic = character_variant_model.current_graphic
    view_model.obtained_at = character_variant_model.obtained_at
    view_model
  end
end
