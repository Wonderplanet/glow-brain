class PuzzleResultTranslator
  def self.translate(puzzle_result_model)
    view_model = PuzzleResultViewModel.new
    view_model.puzzle_session_id = puzzle_result_model.puzzle_session_id
    view_model.obtained_tp = puzzle_result_model.obtained_tp
    view_model.drop_sets = puzzle_result_model.drop_sets
    view_model.before_user = puzzle_result_model.before_user
    view_model.after_user = puzzle_result_model.after_user
    view_model.before_characters = puzzle_result_model.before_characters
    view_model.after_characters = puzzle_result_model.after_characters
    view_model.puzzle_result_unison_points = puzzle_result_model.puzzle_result_unison_points
    view_model.sent_present_box_flg = puzzle_result_model.sent_present_box_flg
    view_model
  end
end
