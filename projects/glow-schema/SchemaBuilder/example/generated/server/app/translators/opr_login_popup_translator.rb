class OprLoginPopupTranslator
  def self.translate(opr_login_popup_model)
    view_model = OprLoginPopupViewModel.new
    view_model.id = opr_login_popup_model.id
    view_model.priority = opr_login_popup_model.priority
    view_model.transition_type = opr_login_popup_model.transition_type
    view_model.banner_path = opr_login_popup_model.banner_path
    view_model.start_at = opr_login_popup_model.start_at
    view_model.end_at = opr_login_popup_model.end_at
    view_model.opr_announcement_id = opr_login_popup_model.opr_announcement_id
    view_model.opr_gacha_id = opr_login_popup_model.opr_gacha_id
    view_model.opr_event_id = opr_login_popup_model.opr_event_id
    view_model
  end
end
