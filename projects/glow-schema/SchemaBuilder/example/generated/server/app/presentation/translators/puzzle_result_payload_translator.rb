class PuzzleResultPayloadTranslator
  def self.translate(puzzle_result_payload_model)
    view_model = PuzzleResultPayloadViewModel.new
    view_model.defeated_opponent_ids = puzzle_result_payload_model.defeated_opponent_ids
    view_model.character_unison_points = puzzle_result_payload_model.character_unison_points
    view_model.song_done_turns = puzzle_result_payload_model.song_done_turns
    view_model.check_hash = puzzle_result_payload_model.check_hash
    view_model
  end
end
