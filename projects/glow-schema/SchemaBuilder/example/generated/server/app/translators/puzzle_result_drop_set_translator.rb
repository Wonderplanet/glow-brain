class PuzzleResultDropSetTranslator
  def self.translate(puzzle_result_drop_set_model)
    view_model = PuzzleResultDropSetViewModel.new
    view_model.number = puzzle_result_drop_set_model.number
    view_model.drops = puzzle_result_drop_set_model.drops
    view_model
  end
end
