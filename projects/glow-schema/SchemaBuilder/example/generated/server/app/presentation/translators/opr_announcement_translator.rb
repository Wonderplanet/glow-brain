class OprAnnouncementTranslator
  def self.translate(opr_announcement_model)
    view_model = OprAnnouncementViewModel.new
    view_model.id = opr_announcement_model.id
    view_model.priority = opr_announcement_model.priority
    view_model.tab_type = opr_announcement_model.tab_type
    view_model.label_type = opr_announcement_model.label_type
    view_model.banner_path = opr_announcement_model.banner_path
    view_model.title = opr_announcement_model.title
    view_model.start_at = opr_announcement_model.start_at
    view_model.detail_path = opr_announcement_model.detail_path
    view_model.opr_event_id = opr_announcement_model.opr_event_id
    view_model
  end
end
