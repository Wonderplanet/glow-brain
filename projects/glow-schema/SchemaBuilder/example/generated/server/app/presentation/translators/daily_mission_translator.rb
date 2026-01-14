class DailyMissionTranslator
  def self.translate(daily_mission_model)
    view_model = DailyMissionViewModel.new
    view_model.mission_number = daily_mission_model.mission_number
    view_model.counter = daily_mission_model.counter
    view_model.playable_until = daily_mission_model.playable_until
    view_model.reward_receivable_until = daily_mission_model.reward_receivable_until
    view_model.reward_received = daily_mission_model.reward_received
    view_model
  end
end
