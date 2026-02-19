class EventMissionRewardResultTranslator
  def self.translate(event_mission_reward_result_model)
    view_model = EventMissionRewardResultViewModel.new
    view_model.received = event_mission_reward_result_model.received
    view_model.updated_mission = event_mission_reward_result_model.updated_mission
    view_model
  end
end
