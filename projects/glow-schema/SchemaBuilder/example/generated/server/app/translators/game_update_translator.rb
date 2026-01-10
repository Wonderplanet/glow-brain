class GameUpdateTranslator
  def self.translate(game_update_model)
    view_model = GameUpdateViewModel.new
    view_model.login_bonuses = game_update_model.login_bonuses
    view_model.guest_tp_summary = game_update_model.guest_tp_summary
    view_model.updated_mission = game_update_model.updated_mission
    view_model.mini_stories = game_update_model.mini_stories
    view_model
  end
end
