class SoloLiveSessionCreateResultTranslator
  def self.translate(solo_live_session_create_result_model)
    view_model = SoloLiveSessionCreateResultViewModel.new
    view_model.solo_live_session = solo_live_session_create_result_model.solo_live_session
    view_model.user_ap = solo_live_session_create_result_model.user_ap
    view_model
  end
end
