class PointRankingEventPuzzleResultTranslator
  def self.translate(point_ranking_event_puzzle_result_model)
    view_model = PointRankingEventPuzzleResultViewModel.new
    view_model.puzzle_result = point_ranking_event_puzzle_result_model.puzzle_result
    view_model.puzzle_event_result = point_ranking_event_puzzle_result_model.puzzle_event_result
    view_model
  end
end
