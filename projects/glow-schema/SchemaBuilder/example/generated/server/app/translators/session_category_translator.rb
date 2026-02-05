class SessionCategoryTranslator
  def self.translate(session_category_model)
    view_model = SessionCategoryViewModel.new
    view_model.session_category = session_category_model.session_category
    view_model.opr_event_id = session_category_model.opr_event_id
    view_model.opr_solo_live_id = session_category_model.opr_solo_live_id
    view_model
  end
end
