class NormalMissionRewardResultTranslator
  def self.translate(normal_mission_reward_result_model)
    view_model = NormalMissionRewardResultViewModel.new
    view_model.received = normal_mission_reward_result_model.received
    view_model.updated_mission = normal_mission_reward_result_model.updated_mission
    view_model
  end
end
