class PuzzleSessionTranslator
  def self.translate(puzzle_session_model)
    view_model = PuzzleSessionViewModel.new
    view_model.id = puzzle_session_model.id
    view_model.mst_or_opr_puzzle_stage_id = puzzle_session_model.mst_or_opr_puzzle_stage_id
    view_model.musical_unit = puzzle_session_model.musical_unit
    view_model.puzzle_guest = puzzle_session_model.puzzle_guest
    view_model.seed = puzzle_session_model.seed
    view_model.songs = puzzle_session_model.songs
    view_model.consume_ap_percentage = puzzle_session_model.consume_ap_percentage
    view_model.obtain_tp_percentage = puzzle_session_model.obtain_tp_percentage
    view_model.user_exp_percentage = puzzle_session_model.user_exp_percentage
    view_model.drop_percentage = puzzle_session_model.drop_percentage
    view_model
  end
end
