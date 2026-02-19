class MstSupportSkillTranslator
  def self.translate(mst_support_skill_model)
    view_model = MstSupportSkillViewModel.new
    view_model.id = mst_support_skill_model.id
    view_model.name = mst_support_skill_model.name
    view_model
  end
end
