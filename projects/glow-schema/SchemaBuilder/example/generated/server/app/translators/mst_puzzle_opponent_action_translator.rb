class MstPuzzleOpponentActionTranslator
  def self.translate(mst_puzzle_opponent_action_model)
    view_model = MstPuzzleOpponentActionViewModel.new
    view_model.id = mst_puzzle_opponent_action_model.id
    view_model.pattern_id = mst_puzzle_opponent_action_model.pattern_id
    view_model.action_type = mst_puzzle_opponent_action_model.action_type
    view_model.filter_type = mst_puzzle_opponent_action_model.filter_type
    view_model.lower_limit = mst_puzzle_opponent_action_model.lower_limit
    view_model.upper_limit = mst_puzzle_opponent_action_model.upper_limit
    view_model.priority = mst_puzzle_opponent_action_model.priority
    view_model.skill_type = mst_puzzle_opponent_action_model.skill_type
    view_model.adoption_percentage = mst_puzzle_opponent_action_model.adoption_percentage
    view_model.min_interval = mst_puzzle_opponent_action_model.min_interval
    view_model.max_usage_count = mst_puzzle_opponent_action_model.max_usage_count
    view_model.duration = mst_puzzle_opponent_action_model.duration
    view_model.power_percentage = mst_puzzle_opponent_action_model.power_percentage
    view_model.bit_target = mst_puzzle_opponent_action_model.bit_target
    view_model.action_value = mst_puzzle_opponent_action_model.action_value
    view_model
  end
end
