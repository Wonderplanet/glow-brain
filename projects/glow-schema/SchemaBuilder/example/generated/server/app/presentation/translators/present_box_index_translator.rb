class PresentBoxIndexTranslator
  def self.translate(present_box_index_model)
    view_model = PresentBoxIndexViewModel.new
    view_model.present_boxes = present_box_index_model.present_boxes
    view_model.present_box_histories = present_box_index_model.present_box_histories
    view_model.deleted_count = present_box_index_model.deleted_count
    view_model
  end
end
