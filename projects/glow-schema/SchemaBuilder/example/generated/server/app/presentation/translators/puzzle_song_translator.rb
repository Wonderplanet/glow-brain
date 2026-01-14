class PuzzleSongTranslator
  def self.translate(puzzle_song_model)
    view_model = PuzzleSongViewModel.new
    view_model.opponents = puzzle_song_model.opponents
    view_model
  end
end
