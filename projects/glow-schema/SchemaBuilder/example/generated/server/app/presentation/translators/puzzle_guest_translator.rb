class PuzzleGuestTranslator
  def self.translate(puzzle_guest_model)
    view_model = PuzzleGuestViewModel.new
    view_model.user_id = puzzle_guest_model.user_id
    view_model.user_name = puzzle_guest_model.user_name
    view_model.rank_level = puzzle_guest_model.rank_level
    view_model.relationship = puzzle_guest_model.relationship
    view_model.support_character_variant = puzzle_guest_model.support_character_variant
    view_model
  end
end
