class BeginnerMissionTranslator
  def self.translate(beginner_mission_model)
    view_model = BeginnerMissionViewModel.new
    view_model.mission_number = beginner_mission_model.mission_number
    view_model.counter = beginner_mission_model.counter
    view_model.received_level = beginner_mission_model.received_level
    view_model
  end
end
