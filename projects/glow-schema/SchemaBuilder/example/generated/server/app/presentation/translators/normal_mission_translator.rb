class NormalMissionTranslator
  def self.translate(normal_mission_model)
    view_model = NormalMissionViewModel.new
    view_model.mission_number = normal_mission_model.mission_number
    view_model.counter = normal_mission_model.counter
    view_model.received_level = normal_mission_model.received_level
    view_model
  end
end
