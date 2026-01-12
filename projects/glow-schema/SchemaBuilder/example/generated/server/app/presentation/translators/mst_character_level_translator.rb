class MstCharacterLevelTranslator
  def self.translate(mst_character_level_model)
    view_model = MstCharacterLevelViewModel.new
    view_model.id = mst_character_level_model.id
    view_model.level = mst_character_level_model.level
    view_model.required_exp_point = mst_character_level_model.required_exp_point
    view_model
  end
end
