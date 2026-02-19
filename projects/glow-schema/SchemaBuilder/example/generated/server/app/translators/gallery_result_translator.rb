class GalleryResultTranslator
  def self.translate(gallery_result_model)
    view_model = GalleryResultViewModel.new
    view_model.updated_mission = gallery_result_model.updated_mission
    view_model
  end
end
