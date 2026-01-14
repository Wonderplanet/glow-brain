class ReceivedPresentBoxTranslator
  def self.translate(received_present_box_model)
    view_model = ReceivedPresentBoxViewModel.new
    view_model.received = received_present_box_model.received
    view_model.updated_mission = received_present_box_model.updated_mission
    view_model.updated_solo_stories = received_present_box_model.updated_solo_stories
    view_model
  end
end
