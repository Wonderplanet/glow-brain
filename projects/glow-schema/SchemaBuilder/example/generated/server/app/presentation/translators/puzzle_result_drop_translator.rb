class PuzzleResultDropTranslator
  def self.translate(puzzle_result_drop_model)
    view_model = PuzzleResultDropViewModel.new
    view_model.mst_item_id = puzzle_result_drop_model.mst_item_id
    view_model.amount = puzzle_result_drop_model.amount
    view_model
  end
end
