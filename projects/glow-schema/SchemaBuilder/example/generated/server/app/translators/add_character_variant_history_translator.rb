class AddCharacterVariantHistoryTranslator
  def self.translate(add_character_variant_history_model)
    view_model = AddCharacterVariantHistoryViewModel.new
    view_model.character_variant_id = add_character_variant_history_model.character_variant_id
    view_model.mst_character_variant_id = add_character_variant_history_model.mst_character_variant_id
    view_model.limit_break_level = add_character_variant_history_model.limit_break_level
    view_model.memory_duplicate_point = add_character_variant_history_model.memory_duplicate_point
    view_model
  end
end
