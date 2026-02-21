class PresentBoxTranslator
  def self.translate(present_box_model)
    view_model = PresentBoxViewModel.new
    view_model.id = present_box_model.id
    view_model.message = present_box_model.message
    view_model.prize = present_box_model.prize
    view_model.sent_at = present_box_model.sent_at
    view_model.expire_at = present_box_model.expire_at
    view_model
  end
end
