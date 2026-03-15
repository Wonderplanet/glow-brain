class PuzzleResultUnisonPointTranslator
  def self.translate(puzzle_result_unison_point_model)
    view_model = PuzzleResultUnisonPointViewModel.new
    view_model.mst_character_id = puzzle_result_unison_point_model.mst_character_id
    view_model.before_point = puzzle_result_unison_point_model.before_point
    view_model.after_point = puzzle_result_unison_point_model.after_point
    view_model
  end
end
