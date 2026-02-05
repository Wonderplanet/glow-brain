class MusicalUnitEditResultTranslator
  def self.translate(musical_unit_edit_result_model)
    view_model = MusicalUnitEditResultViewModel.new
    view_model.updated_mission = musical_unit_edit_result_model.updated_mission
    view_model
  end
end
