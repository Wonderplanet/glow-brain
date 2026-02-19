class MissionTranslator
  def self.translate(mission_model)
    view_model = MissionViewModel.new
    view_model.normal_missions = mission_model.normal_missions
    view_model.daily_missions = mission_model.daily_missions
    view_model.event_missions = mission_model.event_missions
    view_model.beginner_missions = mission_model.beginner_missions
    view_model
  end
end
