class SoloLiveCurrentRecordTranslator
  def self.translate(solo_live_current_record_model)
    view_model = SoloLiveCurrentRecordViewModel.new
    view_model.opr_solo_live_id = solo_live_current_record_model.opr_solo_live_id
    view_model.user_id = solo_live_current_record_model.user_id
    view_model.score = solo_live_current_record_model.score
    view_model.achieved_at = solo_live_current_record_model.achieved_at
    view_model
  end
end
