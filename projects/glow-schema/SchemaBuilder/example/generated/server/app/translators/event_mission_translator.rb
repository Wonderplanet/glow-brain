class EventMissionTranslator
  def self.translate(event_mission_model)
    view_model = EventMissionViewModel.new
    view_model.mission_number = event_mission_model.mission_number
    view_model.counter = event_mission_model.counter
    view_model.playable_until = event_mission_model.playable_until
    view_model.reward_receivable_until = event_mission_model.reward_receivable_until
    view_model.received_level = event_mission_model.received_level
    view_model
  end
end
