class PresentBoxHistoryTranslator
  def self.translate(present_box_history_model)
    view_model = PresentBoxHistoryViewModel.new
    view_model.id = present_box_history_model.id
    view_model.message = present_box_history_model.message
    view_model.prize = present_box_history_model.prize
    view_model.opened_at = present_box_history_model.opened_at
    view_model.expire_at = present_box_history_model.expire_at
    view_model
  end
end
