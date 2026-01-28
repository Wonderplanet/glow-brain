class PuzzleResultUserTranslator
  def self.translate(puzzle_result_user_model)
    view_model = PuzzleResultUserViewModel.new
    view_model.user_exp = puzzle_result_user_model.user_exp
    view_model.rank_level = puzzle_result_user_model.rank_level
    view_model
  end
end
