class SoloLiveSessionTranslator
  def self.translate(solo_live_session_model)
    view_model = SoloLiveSessionViewModel.new
    view_model.opr_solo_live_id = solo_live_session_model.opr_solo_live_id
    view_model.puzzle = solo_live_session_model.puzzle
    view_model.status = solo_live_session_model.status
    view_model
  end
end
