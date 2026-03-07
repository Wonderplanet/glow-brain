class PuzzleEventResultTranslator
  def self.translate(puzzle_event_result_model)
    view_model = PuzzleEventResultViewModel.new
    view_model.base_add_event_point = puzzle_event_result_model.base_add_event_point
    view_model.obtain_event_point_percentage = puzzle_event_result_model.obtain_event_point_percentage
    view_model.point_up_time_percentage = puzzle_event_result_model.point_up_time_percentage
    view_model.obtained_event_point = puzzle_event_result_model.obtained_event_point
    view_model.result_event_point = puzzle_event_result_model.result_event_point
    view_model.extended_prizes = puzzle_event_result_model.extended_prizes
    view_model
  end
end
