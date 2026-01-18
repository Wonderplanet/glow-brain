class EnhanceResultTranslator
  def self.translate(enhance_result_model)
    view_model = EnhanceResultViewModel.new
    view_model.before_level = enhance_result_model.before_level
    view_model.after_level = enhance_result_model.after_level
    view_model.character_variant = enhance_result_model.character_variant
    view_model.tuning_point = enhance_result_model.tuning_point
    view_model.updated_mission = enhance_result_model.updated_mission
    view_model
  end
end
