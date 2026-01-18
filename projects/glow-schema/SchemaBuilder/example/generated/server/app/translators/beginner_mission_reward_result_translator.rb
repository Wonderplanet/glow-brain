class BeginnerMissionRewardResultTranslator
  def self.translate(beginner_mission_reward_result_model)
    view_model = BeginnerMissionRewardResultViewModel.new
    view_model.received = beginner_mission_reward_result_model.received
    view_model.updated_mission = beginner_mission_reward_result_model.updated_mission
    view_model
  end
end
