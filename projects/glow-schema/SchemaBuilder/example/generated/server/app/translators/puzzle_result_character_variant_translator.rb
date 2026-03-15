class PuzzleResultCharacterVariantTranslator
  def self.translate(puzzle_result_character_variant_model)
    view_model = PuzzleResultCharacterVariantViewModel.new
    view_model.character_variant_id = puzzle_result_character_variant_model.character_variant_id
    view_model.exp_point = puzzle_result_character_variant_model.exp_point
    view_model.level = puzzle_result_character_variant_model.level
    view_model.obtained_exp = puzzle_result_character_variant_model.obtained_exp
    view_model
  end
end
