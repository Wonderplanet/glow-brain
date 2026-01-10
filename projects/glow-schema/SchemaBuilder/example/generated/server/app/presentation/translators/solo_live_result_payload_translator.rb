class SoloLiveResultPayloadTranslator
  def self.translate(solo_live_result_payload_model)
    view_model = SoloLiveResultPayloadViewModel.new
    view_model.turn_scores = solo_live_result_payload_model.turn_scores
    view_model.after_point_up_score = solo_live_result_payload_model.after_point_up_score
    view_model.check_hash = solo_live_result_payload_model.check_hash
    view_model
  end
end
