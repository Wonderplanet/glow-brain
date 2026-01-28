class MstPuzzleOpponentTranslator
  def self.translate(mst_puzzle_opponent_model)
    view_model = MstPuzzleOpponentViewModel.new
    view_model.id = mst_puzzle_opponent_model.id
    view_model.name = mst_puzzle_opponent_model.name
    view_model.emotion_element = mst_puzzle_opponent_model.emotion_element
    view_model.asset_key = mst_puzzle_opponent_model.asset_key
    view_model.hp_coef = mst_puzzle_opponent_model.hp_coef
    view_model.attack_coef = mst_puzzle_opponent_model.attack_coef
    view_model.defence_coef = mst_puzzle_opponent_model.defence_coef
    view_model.boss_coef = mst_puzzle_opponent_model.boss_coef
    view_model.hp_split_count = mst_puzzle_opponent_model.hp_split_count
    view_model.action_pattern_id = mst_puzzle_opponent_model.action_pattern_id
    view_model.action_count = mst_puzzle_opponent_model.action_count
    view_model
  end
end
