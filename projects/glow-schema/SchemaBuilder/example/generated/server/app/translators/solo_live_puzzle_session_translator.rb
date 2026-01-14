class SoloLivePuzzleSessionTranslator
  def self.translate(solo_live_puzzle_session_model)
    view_model = SoloLivePuzzleSessionViewModel.new
    view_model.id = solo_live_puzzle_session_model.id
    view_model.musical_unit = solo_live_puzzle_session_model.musical_unit
    view_model.puzzle_guest = solo_live_puzzle_session_model.puzzle_guest
    view_model.seed = solo_live_puzzle_session_model.seed
    view_model.drop = solo_live_puzzle_session_model.drop
    view_model.self_best_score = solo_live_puzzle_session_model.self_best_score
    view_model.turn_boost = solo_live_puzzle_session_model.turn_boost
    view_model.fever_boost = solo_live_puzzle_session_model.fever_boost
    view_model.appeal_bit_boost = solo_live_puzzle_session_model.appeal_bit_boost
    view_model.consume_ap_percentage = solo_live_puzzle_session_model.consume_ap_percentage
    view_model.obtain_tp_percentage = solo_live_puzzle_session_model.obtain_tp_percentage
    view_model.user_exp_percentage = solo_live_puzzle_session_model.user_exp_percentage
    view_model.drop_percentage = solo_live_puzzle_session_model.drop_percentage
    view_model
  end
end
