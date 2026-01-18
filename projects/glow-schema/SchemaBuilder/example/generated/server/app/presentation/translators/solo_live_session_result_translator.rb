class SoloLiveSessionResultTranslator
  def self.translate(solo_live_session_result_model)
    view_model = SoloLiveSessionResultViewModel.new
    view_model.obtained_tp = solo_live_session_result_model.obtained_tp
    view_model.before_user = solo_live_session_result_model.before_user
    view_model.after_user = solo_live_session_result_model.after_user
    view_model.before_characters = solo_live_session_result_model.before_characters
    view_model.after_characters = solo_live_session_result_model.after_characters
    view_model.drop = solo_live_session_result_model.drop
    view_model.sent_present_box_flg = solo_live_session_result_model.sent_present_box_flg
    view_model.after_rank = solo_live_session_result_model.after_rank
    view_model.after_point_up_score = solo_live_session_result_model.after_point_up_score
    view_model
  end
end
