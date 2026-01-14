class DailyMissionRewardResultTranslator
  def self.translate(daily_mission_reward_result_model)
    view_model = DailyMissionRewardResultViewModel.new
    view_model.received = daily_mission_reward_result_model.received
    view_model.updated_mission = daily_mission_reward_result_model.updated_mission
    view_model
  end
end
