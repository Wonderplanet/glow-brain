class SoloLiveDeleteSessionTranslator
  def self.translate(solo_live_delete_session_model)
    view_model = SoloLiveDeleteSessionViewModel.new
    view_model.reward_type = solo_live_delete_session_model.reward_type
    view_model.present_box = solo_live_delete_session_model.present_box
    view_model.updated_mission = solo_live_delete_session_model.updated_mission
    view_model
  end
end
