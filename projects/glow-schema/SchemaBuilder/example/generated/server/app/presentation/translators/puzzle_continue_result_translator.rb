class PuzzleContinueResultTranslator
  def self.translate(puzzle_continue_result_model)
    view_model = PuzzleContinueResultViewModel.new
    view_model.crystal = puzzle_continue_result_model.crystal
    view_model
  end
end
